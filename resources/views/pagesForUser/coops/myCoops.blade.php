@extends('layouts.mainUser', ['MyCoopsStyle' => ['myCoops.css']])
@section('pages')
<div class="cooperatives-page">
<!--        <header class="page-header">
            <h1 class="page-title">PiGarage</h1>
            <p class="page-subtitle">Управление гаражными кооперативами</p>
        </header>-->

        <div class="cooperatives-container">
            <!-- Боковая панель -->
            <aside class="cooperatives-sidebar">
                <div class="profile-section">
                    <h3>Профиль</h3>
                    <ul class="sidebar-nav">
                        <li><a href="#"><i class="fas fa-warehouse"></i>Гаражи участников</a></li>
                        <li><a href="{{route('myCoops.index')}}"><i class="fas fa-users"></i> Мои кооперативы</a></li>
                        <li><a href="#"><i class="fas fa-file-alt"></i> Заявки <span class="badge">1</span></a></li>
                        <li><a href="#"><i class="fas fa-search"></i> Поиск кооперативов</a></li>
                    </ul>
                </div>

                <div class="profile-section">
                    <h3>Дополнительно</h3>
                    <a href="{{route('myCoops_Create.index')}}"class="create-cooperative-btn">
                        <i class="fas fa-plus-circle"></i> Создать кооператив
                    </a>
                    <a class="create-cooperative-btn">
                        <i class="fas fa-plus-circle"></i> Создать гараж
                    </a>
                </div>
            </aside>

            <!-- Основной контент -->
            <main class="cooperatives-main">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> Список кооперативов
                </h2>

                <div class="cooperatives-list">
                    @foreach($myCoops as $coop)
                    <article class="cooperative-card">
                        <div class="cooperative-header">
                            <h3 class="cooperative-name">Кооператив: {{$coop['name']}}</h3>
                            <span class="cooperative-status {{ $coop->status_class }}">{{ $coop->status_ru }}</span>
                        </div>

                        <div class="cooperative-details">
                            <div class="detail-item">
                                <span class="detail-label">Номер кооператива:</span>
                                <span class="detail-value empty">{{$coop['personal_number']}}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Город</span>
                                <span class="detail-value">{{ $coop['city'] != null ? explode(',', $coop['city'])[0] : 'Не указан' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Дата регистрации</span>
                                <span class="detail-value">{{$coop->created_at->format('d.m.Y')}}</span>
                            </div>
                        </div>

                        <div class="union-table">
                            <h4 class="union-title">Сводная таблица</h4>
                            <div class="union-links">
                                <a href="#" class="union-link">
                                    <i class="fas fa-info-circle"></i> Дополнительная информация
                                </a>
                                <a href="#" class="union-link">
                                    <i class="fas fa-user-friends"></i> Участники <span class="badge">{{$coop->user_and_coop_count }}</span>
                                </a>
                                <a href="#" class="union-link">
                                    <i class="fas fa-chart-line"></i> Показания участников
                                </a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                    <hr class="cooperative-divider">

                </div>
            </main>
        </div>
    </div>

<script>

</script>
@endsection
