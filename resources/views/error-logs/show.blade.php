<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лог ошибки #{{ $log->id }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .card {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .badge-error {
            background-color: #dc3545;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .trace-item {
            border-bottom: 1px solid #dee2e6;
            padding: 8px 0;
            font-family: monospace;
            font-size: 12px;
        }

        .trace-item:last-child {
            border-bottom: none;
        }

        /* Стили для отображения кода файла */
        .code-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 12px; /* Уменьшили размер шрифта */
            line-height: 1.0; /* Уменьшили межстрочный интервал */
        }

        .code-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
            font-weight: bold;
        }

        .code-content {
            overflow-x: auto;
            min-height: 100px;
        }

        .code-table {
            width: 100%;
            border-collapse: collapse;
        }

        .code-table tr:hover {
            background-color: #f5f5f5;
        }

        .line-number {
            background-color: #f8f9fa;
            color: #6c757d;
            text-align: right;
            padding: 1px 10px 1px 5px; /* Уменьшили padding */
            border-right: 1px solid #dee2e6;
            min-width: 20px; /* Уменьшили минимальную ширину */
            user-select: none;
            white-space: nowrap;
            font-size: 11px; /* Уменьшили размер шрифта для номеров строк */
        }

        .line-code {
            padding: 1px 10px; /* Уменьшили padding */
            white-space: pre;
            font-size: 12px; /* Уменьшили размер шрифта */
        }

        .error-line {
            background-color: #fff5f5 !important;
            color: #dc3545;
        }

        .error-line .line-number {
            background-color: #ffe6e6;
            color: #dc3545;
            font-weight: bold;
            border-right: 2px solid #dc3545;
        }

        .error-line .line-code {
            background-color: #ffe6e6;
            color: #dc3545;
            font-weight: bold;
        }

        /* Подсветка строки при наведении */
        .code-table tr:hover .line-number {
            background-color: #e9ecef;
        }

        .json-container {
            max-height: 300px;
            overflow-y: auto;
        }

        .file-path {
            font-family: monospace;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="fas fa-exclamation-circle text-danger"></i>
            Лог ошибки #{{ $log->id }}
        </h1>
        <div>
            <a href="{{ route('error-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>
    </div>

    <!-- Основная информация -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Основная информация</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Уровень:</th>
                            <td>
                                @if($log->level == 'error')
                                    <span class="badge bg-danger">ERROR</span>
                                @elseif($log->level == 'warning')
                                    <span class="badge bg-warning text-dark">WARNING</span>
                                @else
                                    <span class="badge bg-info">INFO</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Код ошибки:</th>
                            <td><code class="text-danger">{{ $log->code ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Исключение:</th>
                            <td><code>{{ $log->exception ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Сообщение:</th>
                            <td>
                                <pre class="mb-0 alert alert-danger"
                                     style="white-space: pre-wrap; word-break: break-word; max-width: 600px;">{{ $log->message }}
                                </pre>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Файл:</th>
                            <td>
                                @if($log->file)
                                    <div class="file-path">{{ $log->file }}</div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Строка:</th>
                            <td>
                                @if($log->line)
                                    <span class="badge bg-danger">{{ $log->line }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Fingerprint:</th>
                            <td><code>{{ $log->fingerprint ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Повторений:</th>
                            <td>{{ $log->occurrences }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Отображение кода файла (если есть) -->
    @if($fileContent && $log->file)
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-code"></i>
                    Код файла: <span class="font-monospace">{{ $log->file }}</span>
                </h5>
                <div>
                    <span class="badge bg-danger">
                        <i class="fas fa-exclamation-triangle"></i> Строка {{ $log->line }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="code-container">
                    <div class="code-content">
                        <table class="code-table">
                            @foreach($fileContent as $lineData)
                                <tr class="{{ $lineData['is_error_line'] ? 'error-line' : '' }}"
                                    id="line-{{ $lineData['number'] }}">
                                    <td class="line-number">{{ $lineData['number'] }}</td>
                                    <td class="line-code">
                                        {!! htmlspecialchars($lineData['content'], ENT_QUOTES, 'UTF-8') !!}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle"></i>
                    @php
                        $firstLine = array_first($fileContent)['number'];
                        $lastLine = array_last($fileContent)['number'];
                        $totalLines = count($fileContent);
                    @endphp
                    Показаны строки с {{ $firstLine }} по {{ $lastLine }} (всего {{ $totalLines }} строк).
                    @if($firstLine > 1 || $lastLine < $totalLines)
                        <br><small>Файл содержит больше строк. Для полного просмотра откройте файл в редакторе.</small>
                    @endif
                </div>
            </div>
        </div>
    @elseif($log->file && !file_exists($log->file))
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Файл не найден</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-file-exclamation"></i>
                    Файл <code>{{ $log->file }}</code> не существует на сервере.
                    @if(str_contains($log->file, '/vendor/'))
                        <br><small>Это файл пакета или зависимости. Файлы vendor обычно не доступны в
                            production.</small>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Информация о запросе -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Информация о запросе</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Метод:</th>
                            <td>{{ $log->method ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">URL:</th>
                            <td>{{ $log->url ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Маршрут:</th>
                            <td>{{ $log->route ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Контроллер:</th>
                            <td>{{ $log->controller ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Статус:</th>
                            <td>
                                @if($log->status_code)
                                    @if($log->status_code >= 500)
                                        <span class="badge bg-danger">{{ $log->status_code }}</span>
                                    @elseif($log->status_code >= 400)
                                        <span class="badge bg-warning text-dark">{{ $log->status_code }}</span>
                                    @else
                                        <span class="badge bg-info">{{ $log->status_code }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">IP адрес:</th>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">User Agent:</th>
                            <td><small>{{ $log->user_agent ?? '—' }}</small></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Session ID:</th>
                            <td><code>{{ $log->session_id ?? '—' }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Данные запроса -->
            @if($log->request_data)
                <div class="mt-3">
                    <h6><i class="fas fa-database"></i> Данные запроса:</h6>
                    <div class="json-container">
                        <pre
                            class="mb-0">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif

            <!-- Заголовки запроса -->
            @if($log->request_headers)
                <div class="mt-3">
                    <h6><i class="fas fa-heading"></i> Заголовки запроса:</h6>
                    <div class="json-container">
                        <pre
                            class="mb-0">{{ json_encode($log->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Пользователь и система -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-user-cog"></i> Пользователь и система</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Пользователь:</th>
                            <td>
                                @if($log->user_id)
                                    <span class="badge bg-primary">ID: {{ $log->user_id }}</span>
                                @else
                                    <span class="text-muted">Гость</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Окружение:</th>
                            <td>{{ $log->environment }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Версия приложения:</th>
                            <td>{{ $log->app_version ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Дата создания:</th>
                            <td>
                                <i class="far fa-calendar"></i> {{ $log->created_at->format('d.m.Y') }}
                                <i class="far fa-clock ms-2"></i> {{ $log->created_at->format('H:i:s') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Трассировка стека -->
    @if($log->trace || $log->trace_array)
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-code-branch"></i> Трассировка стека</h5>
            </div>
            <div class="card-body">
                @if($log->trace_array)
                    @foreach($log->trace_array as $index => $trace)
                        <div class="trace-item">
                            <strong>#{{ $index + 1 }}</strong>
                            @if(is_array($trace))
                                @if(isset($trace['file']))
                                    <br>
                                    @if(isset($trace['line']))
                                        <span class="text-primary">
                                            <i class="fas fa-file-code"></i>
                                            {{ $trace['file'] }}:{{ $trace['line'] }}
                                        </span>
                                    @else
                                        <code>{{ $trace['file'] }}</code>
                                    @endif
                                @endif
                                @if(isset($trace['class']))
                                    <br><small class="text-muted">
                                        {{ $trace['class'] }}{{ $trace['type'] ?? '' }}{{ $trace['function'] ?? '' }}()
                                    </small>
                                @endif
                            @else
                                <br><code>{{ $trace }}</code>
                            @endif
                        </div>
                    @endforeach
                @elseif($log->trace)
                    <pre>{{ $log->trace }}</pre>
                @endif
            </div>
        </div>
    @endif

    <!-- Действия -->
    <div class="card">
        <div class="card-body text-center">
            <form action="{{ route('error-logs.destroy', $log->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Удалить эту запись?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Удалить эту ошибку
                </button>
            </form>

            <a href="{{ route('error-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> К списку ошибок
            </a>

            @if($log->file && $log->line && $fileContent)
                <button onclick="scrollToErrorLine()" class="btn btn-primary">
                    <i class="fas fa-search"></i> Найти строку {{ $log->line }}
                </button>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Прокрутка к строке ошибки
    function scrollToErrorLine() {
        const errorLine = document.querySelector('.error-line');
        if (errorLine) {
            // Прокручиваем к строке с отступом сверху
            errorLine.scrollIntoView({behavior: 'smooth', block: 'center'});

            // Добавляем анимацию подсветки
            errorLine.style.backgroundColor = '#ffcccc';
            setTimeout(() => {
                errorLine.style.backgroundColor = '';
            }, 2000);
        }
    }

    // Автоматически прокручиваем к строке ошибки при загрузке страницы
    @if($log->file && $log->line && $fileContent)
    window.addEventListener('load', function () {
        setTimeout(scrollToErrorLine, 300);
    });
    @endif

    // Подсветка синтаксиса для PHP кода (исправленная версия)
    function highlightPHPSyntax() {
        const codeLines = document.querySelectorAll('.line-code');
        codeLines.forEach(line => {
            let text = line.textContent; // Используем textContent вместо innerHTML

            // Заменяем только чистый текст, не затрагивая уже существующий HTML
            let result = text;

            // Простая подсветка PHP тегов
            result = result.replace(/&lt;\?php/g, '<span class="text-primary">&lt;?php</span>');
            result = result.replace(/&lt;\?=/g, '<span class="text-primary">&lt;?=</span>');
            result = result.replace(/&lt;\?/g, '<span class="text-primary">&lt;?</span>');
            result = result.replace(/\?&gt;/g, '<span class="text-primary">?&gt;</span>');

            // Подсветка ключевых слов PHP (только если это отдельные слова)
            const phpKeywords = [
                'function', 'class', 'interface', 'trait', 'namespace', 'use',
                'if', 'else', 'elseif', 'endif', 'for', 'foreach', 'while',
                'do', 'switch', 'case', 'break', 'continue', 'return',
                'try', 'catch', 'finally', 'throw', 'new', 'instanceof',
                'public', 'private', 'protected', 'static', 'abstract',
                'final', 'const', 'var', 'global', 'echo', 'print',
                'require', 'require_once', 'include', 'include_once',
                'true', 'false', 'null', 'array', 'string', 'int',
                'float', 'bool', 'void', 'object', 'mixed'
            ];

            phpKeywords.forEach(keyword => {
                const regex = new RegExp(`\\b${keyword}\\b`, 'gi');
                result = result.replace(regex, `<span class="text-primary">${keyword}</span>`);
            });

            // Подсветка строк в кавычках (но не внутри уже подсвеченных тегов)
            result = result.replace(/(['"])(.*?)\1/g, '<span class="text-success">$1$2$1</span>');

            // Подсветка комментариев
            result = result.replace(/\/\/.*$/g, '<span class="text-muted">$&</span>');
            result = result.replace(/#.*$/g, '<span class="text-muted">$&</span>');

            // Подсветка переменных (но не внутри строк)
            result = result.replace(/(?<!['"])?(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/g, '<span class="text-info">$1</span>');

            // Только если что-то изменилось, обновляем содержимое
            if (result !== text) {
                line.innerHTML = result;
            }
        });
    }

    // Применяем подсветку после загрузки
    document.addEventListener('DOMContentLoaded', function () {
        highlightPHPSyntax();

        // Прокрутка к строке ошибки
        function scrollToErrorLine() {
            const errorLine = document.querySelector('.error-line');
            if (errorLine) {
                errorLine.scrollIntoView({behavior: 'smooth', block: 'center'});
                errorLine.style.backgroundColor = '#ffcccc';
                setTimeout(() => {
                    errorLine.style.backgroundColor = '';
                }, 2000);
            }
        }

        // Автопрокрутка
        @if($log->file && $log->line && $fileContent)
        setTimeout(scrollToErrorLine, 300);
        @endif
    });
</script>
</body>
</html>
