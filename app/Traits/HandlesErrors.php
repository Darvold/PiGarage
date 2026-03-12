<?php
// app/Traits/HandlesErrors.php
namespace App\Traits;

use App\Services\ErrorLoggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

trait HandlesErrors
{
    /**
     * Обработка исключений с логированием
     */
    protected function handleException(Throwable $e, string $redirectRoute = null, array $context = [])
    {
        // Логируем ошибку
        $errorLog = ErrorLoggerService::log($e, array_merge([
            'controller' => class_basename($this),
        ], $context));

        // Пишем в Laravel лог
        Log::error($e->getMessage(), [
            'error_id' => $errorLog->id,
            'controller' => class_basename($this),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        // Определяем тип ответа
        if (request()->expectsJson() || request()->is('api/*')) {
            return $this->jsonErrorResponse($e, $errorLog);
        }

        // Web ответ
        return $this->webErrorResponse($e, $redirectRoute, $errorLog);
    }

    /**
     * JSON ответ при ошибке
     */
    private function jsonErrorResponse(Throwable $e, $errorLog): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getUserFriendlyMessage($e),
            'error_id' => config('app.env') === 'local' ? $errorLog->id : null,
        ];

        // В development режиме добавляем детали
        if (config('app.env') === 'local') {
            $response['debug'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
        }

        $statusCode = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : 500;

        return response()->json($response, $statusCode);
    }

    /**
     * Web ответ при ошибке
     */
    private function webErrorResponse(Throwable $e, ?string $redirectRoute, $errorLog)
    {
        $message = $this->getUserFriendlyMessage($e);

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)
                ->with('error', $message)
                ->with('error_id', config('app.env') === 'local' ? $errorLog->id : null)
                ->withInput();
        }

        return redirect()->back()
            ->with('error', $message)
            ->with('error_id', config('app.env') === 'local' ? $errorLog->id : null)
            ->withInput();
    }

    /**
     * Пользовательские сообщения об ошибках
     */
    private function getUserFriendlyMessage(Throwable $e): string
    {
        // Кастомные сообщения для разных типов ошибок
        $messages = [
            \Illuminate\Validation\ValidationException::class =>
                'Проверьте правильность введенных данных.',

            \Illuminate\Auth\Access\AuthorizationException::class =>
                'У вас недостаточно прав для выполнения этого действия.',

            \Illuminate\Database\Eloquent\ModelNotFoundException::class =>
                'Запрашиваемый ресурс не найден.',

            \Illuminate\Database\QueryException::class =>
                'Произошла ошибка при работе с базой данных.',

            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class =>
                'Страница не найдена.',

            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class =>
                'Метод запроса не поддерживается.',
        ];

        foreach ($messages as $exceptionClass => $message) {
            if ($e instanceof $exceptionClass) {
                return $message;
            }
        }

        // Сообщение по умолчанию
        return config('app.env') === 'production'
            ? 'Что-то пошло не так. Пожалуйста, попробуйте позже.'
            : $e->getMessage();
    }

    /**
     * Быстрая обработка с логированием
     */
    protected function safeExecute(callable $callback, $redirectRoute = null, array $context = [])
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            return $this->handleException($e, $redirectRoute, $context);
        }
    }
}
