<?php
// app/Services/ErrorLoggerService.php
namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class ErrorLoggerService
{
    /**
     * Логирование ошибки
     */
    public static function log(Throwable $exception, array $context = []): ErrorLog
    {
        try {
            $request = request();
            $user = Auth::user();

            // Собираем базовые данные
            $data = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
                'level' => self::determineLevel($exception),

                'controller' => self::getControllerName(),
                'method' => $request?->method(),
                'route' => $request?->route()?->getName(),
                'url' => $request?->fullUrl(),
                'status_code' => method_exists($exception, 'getStatusCode')
                    ? $exception->getStatusCode()
                    : 500,

                'user_id' => $user?->id,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'session_id' => session()->getId(),

                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'trace_array' => $exception->getTrace(),

                'environment' => config('app.env'),
                'app_version' => config('app.version', '1.0.0'),

                'fingerprint' => self::generateFingerprint($exception),
            ];

            // Добавляем данные запроса (без чувствительных данных)
            if ($request) {
                $data['request_data'] = self::sanitizeRequestData($request->all());
                $data['request_headers'] = self::sanitizeHeaders($request->headers->all());
            }

            // Добавляем контекст
            $data = array_merge($data, $context);

            // Проверяем есть ли такая же ошибка
            $existing = ErrorLog::where('fingerprint', $data['fingerprint'])
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($existing) {
                // Увеличиваем счетчик повторений
                $existing->increment('occurrences');
                return $existing;
            }

            // Создаем новую запись
            return ErrorLog::create($data);

        } catch (\Exception $e) {
            // Если не удалось залогировать в БД, пишем в файл
            Log::error('Failed to log error to database: ' . $e->getMessage(), [
                'original_error' => $exception->getMessage()
            ]);

            throw $e; // или вернуть null
        }
    }

    /**
     * Определение уровня ошибки
     */
    private static function determineLevel(Throwable $exception): string
    {
        $code = $exception->getCode();

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 'validation';
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 'authorization';
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 'not_found';
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $statusCode = $exception->getStatusCode();

            if ($statusCode >= 500) return 'error';
            if ($statusCode >= 400) return 'warning';
            return 'info';
        }

        // По умолчанию
        return $code >= 500 || $code == 0 ? 'error' : 'warning';
    }

    /**
     * Получение имени контроллера
     */
    private static function getControllerName(): ?string
    {
        $route = request()->route();

        if ($route && $route->getActionName() !== 'Closure') {
            return class_basename($route->getController());
        }

        return null;
    }

    /**
     * Генерация уникального отпечатка ошибки
     */
    private static function generateFingerprint(Throwable $exception): string
    {
        $fingerprintData = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'exception' => get_class($exception),
            'controller' => self::getControllerName(),
        ];

        return md5(serialize($fingerprintData));
    }

    /**
     * Очистка данных запроса (удаление паролей и токенов)
     */
    private static function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token',
            'api_token', 'access_token', 'secret', 'credit_card',
            'cvv', 'ssn', 'passport'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***HIDDEN***';
            }

            // Рекурсивно для массивов
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = self::sanitizeRequestData($value);
                }
            }
        }

        return $data;
    }

    /**
     * Очистка заголовков
     */
    private static function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization', 'cookie', 'php-auth-pw',
            'php-auth-user', 'x-csrf-token', 'x-xsrf-token'
        ];

        foreach ($sensitiveHeaders as $header) {
            $headerLower = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) === $headerLower) {
                    $headers[$key] = ['***HIDDEN***'];
                }
            }
        }

        return $headers;
    }

    /**
     * Быстрое логирование с контекстом
     */
    public static function quickLog(string $message, string $level = 'error', array $context = []): ErrorLog
    {
        $exception = new \Exception($message);

        return self::log($exception, array_merge($context, [
            'level' => $level,
            'code' => 0,
        ]));
    }
}
