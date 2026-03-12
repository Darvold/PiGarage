<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логи ошибок</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .badge-error { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-info { background-color: #17a2b8; }
        .log-message {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
            cursor: pointer;
        }
        .stats-box {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            color: white;
            text-align: center;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-exclamation-triangle text-danger"></i> Логи ошибок</h1>
                <div>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> На главную
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-box bg-danger">
                <h3>{{ $stats['total'] }}</h3>
                <p>Всего ошибок</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-box bg-warning">
                <h3>{{ $stats['today'] }}</h3>
                <p>Сегодня</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-box bg-danger">
                <h3>{{ $stats['errors'] }}</h3>
                <p>Критические</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-box bg-info">
                <h3>{{ $stats['warnings'] }}</h3>
                <p>Предупреждения</p>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Фильтры</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('error-logs.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Поиск..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="level" class="form-select">
                            <option value="all">Все уровни</option>
                            @foreach($levels as $level)
                                <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                    {{ ucfirst($level) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="number"
                               name="status_code"
                               class="form-control"
                               placeholder="Код статуса"
                               value="{{ request('status_code') }}">
                    </div>

                    <div class="col-md-2">
                        <input type="date"
                               name="date_from"
                               class="form-control"
                               value="{{ request('date_from') }}"
                               placeholder="С даты">
                    </div>

                    <div class="col-md-2">
                        <input type="date"
                               name="date_to"
                               class="form-control"
                               value="{{ request('date_to') }}"
                               placeholder="По дату">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Кнопки действий -->
            <div class="mt-3">
                <form action="{{ route('error-logs.clear-all') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Очистить ВСЕ логи? Это действие нельзя отменить.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-broom"></i> Очистить все
                    </button>
                </form>

                <a href="{{ route('error-logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Сбросить фильтры
                </a>

                <div class="btn-group float-end">
                    <a href="?sort=created_at&order=desc"
                       class="btn btn-outline-secondary {{ request('sort') == 'created_at' && request('order') == 'desc' ? 'active' : '' }}">
                        <i class="fas fa-sort-amount-down"></i> Сначала новые
                    </a>
                    <a href="?sort=created_at&order=asc"
                       class="btn btn-outline-secondary {{ request('sort') == 'created_at' && request('order') == 'asc' ? 'active' : '' }}">
                        <i class="fas fa-sort-amount-up"></i> Сначала старые
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">Уровень</th>
                        <th width="120">Код</th>
                        <th>Сообщение</th>
                        <th width="150">Контроллер</th>
                        <th width="100">Статус</th>
                        <th width="120">Пользователь</th>
                        <th width="150">Дата</th>
                        <th width="100">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $log)
                        <tr onclick="window.location='{{ route('error-logs.show', $log->id) }}'"
                            style="cursor: pointer;">
                            <td><strong>#{{ $log->id }}</strong></td>
                            <td>
                                @if($log->level == 'error')
                                    <span class="badge bg-danger">ERROR</span>
                                @elseif($log->level == 'warning')
                                    <span class="badge bg-warning text-dark">WARNING</span>
                                @else
                                    <span class="badge bg-info">INFO</span>
                                @endif
                            </td>
                            <td>
                                @if($log->file)
                                    <small title="{{ $log->file }}:{{ $log->line }}">
                                        <i class="fas fa-file-code text-info"></i>
                                        {{ basename($log->file) }}:{{ $log->line }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="log-message" title="{{ $log->message }}">
                                    {{ \Illuminate\Support\Str::limit($log->message, 70) }}
                                </div>
                            </td>
                            <td>
                                <small>{{ $log->controller ?: '—' }}</small>
                            </td>
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
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->user_id)
                                    <span class="badge bg-primary">#{{ $log->user_id }}</span>
                                @else
                                    <span class="text-muted">Гость</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $log->created_at->format('d.m.Y H:i') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                    <a href="{{ route('error-logs.show', $log->id) }}"
                                       class="btn btn-outline-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('error-logs.destroy', $log->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Удалить эту запись?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="mb-0">Логов ошибок не найдено</p>
                                <small class="text-muted">Все работает отлично!</small>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Пагинация -->
        @if($logs->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted">
                            Показано с {{ $logs->firstItem() }} по {{ $logs->lastItem() }} из {{ $logs->total() }} записей
                        </p>
                    </div>
                    <div>
                        {{ $logs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Информация -->
    <div class="alert alert-info mt-4">
        <h5><i class="fas fa-info-circle"></i> Информация</h5>
        <ul class="mb-0">
            <li>Нажмите на строку для быстрого просмотра деталей</li>
            <li>Двойной клик откроет полную информацию об ошибке</li>
            <li>Для поиска можно использовать часть текста сообщения, код ошибки или имя контроллера</li>
        </ul>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Автообновление страницы каждые 30 секунд (опционально)
    // setInterval(() => {
    //     if (!document.hidden) {
    //         window.location.reload();
    //     }
    // }, 30000);

    // Быстрый поиск при вводе (с задержкой)
    let searchTimeout;
    document.querySelector('input[name="search"]').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.form.submit();
        }, 500);
    });

    // Показывать уведомление при успешном действии
    @if(session('success'))
    alert('{{ session('success') }}');
    @endif
</script>
</body>
</html>
