<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PiGarage - Регистрация</title>
    <link rel="stylesheet" href="{{asset('css/userAuth/auth.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/logo.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{asset('css/template/white_theme.css')}}">
</head>
<body>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/select/select2.min.js') }}"></script>
    <script src="{{ asset('js/select/ru.js') }}"></script>
    <script src="{{ asset('js/select/russian-cities.js') }}"></script>
    <script src="{{ asset('js/NumberMask/imask.js') }}"></script>
    @include('template.msg')
    <div class="login-container">
        <!-- Левая часть - описание системы -->
        <div class="login-left">
            <div class="main-jump">
                <a href="{{route('welcome.index')}}">< Главная</a>
            </div>
            @include('template.logo')
            <div class="system-description">
                <h1>Присоединяйтесь к сообществу</h1>
                <p>Создайте аккаунт и получите полный доступ ко всем возможностям управления вашим гаражным кооперативом.</p>

                <div class="benefits-list">
                    <div class="benefit">
                        <i class="fas fa-shield-alt"></i>
                        <div class="benefit-content">
                            <h4>Безопасный доступ</h4>
                            <p>Защита ваших данных с помощью современного шифрования</p>
                        </div>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-bell"></i>
                        <div class="benefit-content">
                            <h4>Уведомления</h4>
                            <p>Своевременные напоминания о платежах и новостях кооператива</p>
                        </div>
                    </div>
                {{-- <div class="benefit">
                    <i class="fas fa-mobile-alt"></i>
                    <div class="benefit-content">
                        <h4>Мобильный доступ</h4>
                        <p>Управляйте кооперативом с любого устройства</p>
                    </div>
                </div> --}}
                {{-- <div class="benefit">
                    <i class="fas fa-headset"></i>
                    <div class="benefit-content">
                        <h4>Поддержка 24/7</h4>
                        <p>Наша команда всегда готова помочь с любыми вопросами</p>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Правая часть - форма авторизации -->
    <div class="login-right">
        <div class="login-form-container">
            <div class="form-header">
                <h2>Регистрация</h2>
            </div>

            <form class="login-form" id="loginForm" method="post" action="{{route('registr.store')}}">
                @csrf
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" data-mask="phone" placeholder="+7" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">ФИО</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="fio" name="fio" value="{{ old('fio') }}"placeholder="" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="region">Регион</label>
                    <div class="input-with-icon">
                        <select class="js-select-region" name="region">
                            <option>Выберите регион</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Введите пароль (мин. 6)" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Подтвердите пароль</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Введите пароль (мин. 6)" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Зарегестрироваться</button>

                <div class="login-footer">
                    <p>Уже есть учетная запись? <a href="{{route('login.index')}}" class="register-link">Войти</a></p>
                </div>
            </form>
        </div>

        <div class="login-footer-right">
            <p>&copy; 2023 PiGarage. Все права защищены.</p>
            <div class="footer-links">
                <a href="#">Политика конфиденциальности</a>
                <a href="#">Условия использования</a>
                <a href="#">Поддержка</a>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('js/authUser/auth.js')}}"></script>
</body>
</html>
