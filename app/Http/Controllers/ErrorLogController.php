<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    /**
     * Показать таблицу логов ошибок
     */
    public function index(Request $request)
    {
        $query = ErrorLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('controller', 'like', "%{$search}%")
                    ->orWhere('route', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level') && $request->level !== 'all') {
            $query->where('level', $request->level);
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->status_code);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);

        $logs = $query->paginate(20);
        $levels = ErrorLog::select('level')->distinct()->pluck('level');

        $stats = [
            'total' => ErrorLog::count(),
            'today' => ErrorLog::whereDate('created_at', today())->count(),
            'errors' => ErrorLog::where('level', 'error')->count(),
            'warnings' => ErrorLog::where('level', 'warning')->count(),
        ];

        return view('error-logs.index', compact('logs', 'levels', 'stats'));
    }

    /**
     * Показать детали одной ошибки
     */
    public function show($id)
    {
        $log = ErrorLog::findOrFail($id);

        // Получаем код файла, если есть путь и файл существует
        $fileContent = null;
        $errorLine = null;

        if ($log->file && file_exists($log->file)) {
            $errorLine = $log->line;
            $fileContent = $this->getFileContentWithContext($log->file, $errorLine);
        }

        return view('error-logs.show', compact('log', 'fileContent', 'errorLine'));
    }

    /**
     * Получить содержимое файла с контекстом вокруг строки ошибки
     */
    private function getFileContentWithContext($filePath, $errorLine, $contextLines = 10)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $lines = file($filePath);
        $totalLines = count($lines);

        // Показываем больше контекста или весь файл, если он небольшой
        if ($totalLines <= 100) {
            // Для маленьких файлов показываем весь файл
            $startLine = 1;
            $endLine = $totalLines;
        } else {
            // Для больших файлов показываем больше контекста
            $startLine = max(1, $errorLine - $contextLines);
            $endLine = min($totalLines, $errorLine + $contextLines);
        }

        $content = [];
        for ($i = $startLine; $i <= $endLine; $i++) {
            $lineContent = $lines[$i - 1] ?? '';
            // Убираем лишние символы
            $lineContent = rtrim($lineContent);
            $content[$i] = [
                'number' => $i,
                'content' => $lineContent,
                'is_error_line' => ($i == $errorLine)
            ];
        }

        return $content;
    }

    /**
     * Удалить лог ошибки
     */
    public function destroy($id)
    {
        $log = ErrorLog::findOrFail($id);
        $log->delete();

        return redirect()->route('error-logs.index')
            ->with('success', 'Лог ошибки удален');
    }

    /**
     * Очистить все логи
     */
    public function clearAll()
    {
        ErrorLog::truncate();

        return redirect()->route('error-logs.index')
            ->with('success', 'Все логи ошибок очищены');
    }
}
