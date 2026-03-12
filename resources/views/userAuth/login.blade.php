<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PiGarage - Авторизация</title>
    <link rel="stylesheet" href="{{asset('css/userAuth/auth.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/logo.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{asset('css/template/white_theme.css')}}">
</head>
<body>
@include('template.msg')
<script src="{{ asset('js/NumberMask/imask.js') }}"></script>
<div class="login-container">
    <!-- Левая часть - описание системы -->
    <div class="login-left">
        <div class="main-jump">
            <a href="{{route('welcome.index')}}">< Главная</a>
        </div>
        @include('template.logo')

        <div class="system-description">
            <h1>Управление гаражным кооперативом</h1>
            <p>PiGarage — современная платформа для автоматизации учета, коммунальных платежей и управления гаражными кооперативами.</p>

            <div class="features-list">
                <div class="feature">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Учет платежей и взносов</span>
                </div>
                <div class="feature">
                    <i class="fas fa-table"></i>
                    <span>Контроль показаний счетчиков</span>
                </div>
                <div class="feature">
                    <i class="fas fa-tools"></i>
                    <span>Учет ремонтных работ</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Аналитика и отчетность</span>
                </div>
            </div>
        </div>

        <div class="statistics">
            <div class="stat-item">
                <div class="stat-number">150+</div>
                <div class="stat-label">гаражей</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">94%</div>
                <div class="stat-label">своевременных платежей</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">доступ к системе</div>
            </div>
        </div>
    </div>

    <!-- Правая часть - форма авторизации -->
    <div class="login-right">
        <div class="login-form-container">
            <div class="form-header">
                <h2>Вход в систему</h2>
                <p>Введите ваши учетные данные для доступа</p>
            </div>

            <form method="post" action="{{route('login.store')}}" class="login-form" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" data-mask="phone" placeholder="+7" required>
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

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Запомнить меня
                    </label>
                    <a href="#" class="forgot-password">Забыли пароль?</a>
                </div>

                <button type="submit" class="btn-login">Войти в систему</button>

                <div class="login-footer">
                    <p>Нет учетной записи? <a href="{{route('registr.index')}}" class="register-link">Зарегистрируйтесь</a></p>
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
