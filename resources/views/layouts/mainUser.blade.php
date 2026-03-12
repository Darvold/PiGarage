<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PiGarage</title>
    <link rel="stylesheet" href="{{asset('css/template/white_theme.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/layouts_button_a_other.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/logo.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/left_block.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/head_main.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    @php
        $profileStyles = [
        'ProfileUserStyles' => 'PagesForUsers/profile/',
        'MyCoopsStyle' => 'PagesForUsers/myCoops/',
        'MyCoops_CreateStyle' => 'PagesForUsers/myCoops_Create/',
        ];
    @endphp

    @foreach ($profileStyles as $styleVar => $stylePath)
        @if (isset($$styleVar) && is_array($$styleVar))
            @foreach ($$styleVar as $style)
                <link rel="stylesheet" href="{{ asset('css/' . $stylePath . $style) }}">
            @endforeach
        @endif
    @endforeach
    
</head>
<body>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
@include('template.msg')
<script src="{{ asset('js/NumberMask/imask.js') }}"></script>
<body>
<div class="dashboard-container">
        <!-- Левая панель -->
        <aside class="sidebar">
            <div class="sidebar-header">
                @include('template.logo')
            </div>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <span>{{$userCurrent->short_fi}}</span>
                    </div>
                    <div class="user-details">
                        <h4>{{$userCurrent->short_fio}}</h4>
                        <p>{{$userCurrent->formatted_phone}}</p>
                    </div>
                    <form method="post" action="{{route('logout.store')}}">
                        @csrf
                        <button type="submit" style="all: unset;">
                            <div class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                        </button>
                    </form>

                </div>
            </div>
            <nav class="sidebar-nav">

                <div class="nav-section">
                    <h3 class="nav-section-title">Основное</h3>
                    <a href="{{route('profileUser.index')}}" class="nav-item active">
                        <i class="fas fa-user"></i>
                        <span>Профиль</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">Кооперативы</h3>
                    <a href="" class="nav-item">
                        <i class="fas fa-warehouse"></i>
                        <span>Мои гаражи</span>
                    </a>
                    <a href="{{route('myCoops.index')}}" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Мои кооперативы</span>
                        <span class="badge">4</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-file-alt"></i>
                        <span>Заявки</span>
                        <span class="badge badge-warning">1</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-search"></i>
                        <span>Поиск кооперативов</span>
                    </a>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">ИНСТРУМЕНТЫ</h3>
                    <a href="#" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Настройки</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Основной контент -->
        <main class="main-content">
            <!-- Верхняя панель -->
            <header class="top-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Поиск клиентов, проектов, сделок...">
                    <button class="search-filter">
                        <i class="fas fa-sliders-h"></i>
                        Фильтры
                    </button>
                </div>

                <div class="header-actions">
                    <button class="action-btn notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-question-circle"></i>
                    </button>
                    <div class="theme-toggle">
                        <input type="checkbox" id="themeSwitch" hidden>
                        <label for="themeSwitch" class="theme-label">
                            <i class="fas fa-sun"></i>
                            <i class="fas fa-moon"></i>
                        </label>
                    </div>
                </div>
            </header>

            <!-- Контент -->
            <div class="content-wrapper">
                @yield('pages')
            </div>
        </main>
    </div>
        <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/select/select2.min.js') }}"></script>
    <script src="{{ asset('js/select/ru.js') }}"></script>
    <script src="{{ asset('js/select/russian-cities.js') }}"></script>
    <script src="{{ asset('js/NumberMask/imask.js') }}"></script>
</body>
<script>
// Получаем текущий путь из URL
function getFirstPathSegment() {
    try {
        const urlObj = new URL(window.location.href);
        return urlObj.pathname.replace(/^\/+|\/+$/g, '').split('/')[0] || '';
    } catch (e) {
        return '';
    }
}

// Основная функция активации ссылки
function activateNavItem() {
    const currentPath = getFirstPathSegment(); // Получаем "myCoops"
    
    // Находим все ссылки
    const navItems = document.querySelectorAll('a');
    
    // Убираем класс active у всех ссылок
    navItems.forEach(item => {
        item.classList.remove('active');
    });
    
    // Добавляем класс active к соответствующей ссылке
    navItems.forEach(item => {
        // Извлекаем путь из href
        const href = item.getAttribute('href');
        if (href && href.includes(currentPath)) {
            item.classList.add('active');
        }
    });
}

// Запускаем при загрузке страницы
document.addEventListener('DOMContentLoaded', activateNavItem);

    /*маска номера телефона*/
document.addEventListener('DOMContentLoaded', () => {

    const inputElement = document.querySelector('[data-mask="phone"]')
    const maskOptions = { // создаем объект параметров
        mask: '+{7}(000)000-00-00' // задаем единственный параметр mask
    }
    IMask(inputElement, maskOptions) // запускаем плагин с переданными параметрами
})

$('.js-select-region').select2({
    placeholder: "Выберите регион",
    language: "ru"
});

if (typeof cities !== 'undefined' && Array.isArray(cities)) {
    populateRegions(cities);
} else {
    console.error('Данные о городах не загружены или данные не являются массивом.');
}

    // Функция для заполнения <select> регионами
function populateRegions(data) {
    const selectRegion = $('.js-select-region');
    const uniqueSubjects = new Set();

    data.forEach(item => {
        uniqueSubjects.add(item.subject);
    });

    // Преобразуем Set в массив и сортируем
    const sortedSubjects = Array.from(uniqueSubjects).sort((a, b) => {
        return a.localeCompare(b, 'ru', { sensitivity: 'base' });
    });

    // Добавляем опции в отсортированном порядке
    sortedSubjects.forEach(subject => {
        const option = $('<option></option>').val(subject).text(subject);
        selectRegion.append(option);
    });
}
    // Обработчик события 'change' для выбора региона
$('.js-select-region').on('change', function () {
    selectedRegion = $(this).val();
});
</script>
