<?php
    $garages = App\Data\GaragesData::get();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PiGarage</title>
    <link rel="stylesheet" href="{{asset('css/welcome.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/logo.css')}}">
    <link rel="stylesheet" href="{{asset('css/template/white_theme.css')}}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
</head>
<body>
@include('template.msg')
<!-- Шапка -->
<header>
    <div class="container header-container">
        @include('template.logo')
        <div class="nav-links">
            <a href="#" class="nav-link">Главная</a>
            <a href="#" class="nav-link">О нас</a>
            <a href="#" class="nav-link">Услуги</a>
            <a href="#" class="nav-link">Контакты</a>
            <a href="#" class="nav-link">Документы</a>

            <div class="auth-buttons">
                <a href="{{route('login.index')}}" class="btn btn-login" id="loginBtn">Вход</a>
                <a href="{{route('registr.index')}}" class="btn btn-register" id="registerBtn">Регистрация</a>
            </div>
        </div>
    </div>
</header>

<!-- Основной баннер -->
<section class="hero">
    <div class="container">
        <h1>Управление гаражным кооперативом стало проще</h1>
        <p>PiGarage — современная платформа для автоматизации учета, коммунальных платежей и управления гаражными кооперативами. Присоединяйтесь к сообществу владельцев гаражей!</p>
        <a href="#" class="btn btn-hero">Подробнее о возможностях</a>
    </div>
</section>

<!-- Возможности -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Наши возможности</h2>
            <p>PiGarage предоставляет все необходимое для эффективного управления гаражным кооперативом</p>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3>Учет платежей</h3>
                <p>Автоматизированный учет членских взносов, коммунальных платежей и других сборов с формированием квитанций</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-table"></i>
                </div>
                <h3>Показания счетчиков</h3>
                <p>Удобный ввод и контроль показаний счетчиков электроэнергии, воды и других коммунальных услуг</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3>Учет работ</h3>
                <p>Ведение журнала ремонтных работ, заявок от владельцев гаражей и планирование обслуживания</p>
            </div>
        </div>

        <!-- Таблица показаний -->
        <div class="readings-container">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>Гараж №</th>
                        <th>Владелец</th>
                        <th>Электричество (кВт·ч)</th>
                        <th>Вода (м³)</th>
                        <th>Последний платеж</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($garages as $garage): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($garage['number']); ?></td>
                        <td><?php echo htmlspecialchars($garage['owner']); ?></td>
                        <td><?php echo htmlspecialchars($garage['electricity']); ?></td>
                        <td><?php echo htmlspecialchars($garage['water']); ?></td>
                        <td><?php echo htmlspecialchars($garage['last_payment']); ?></td>
                        <td>
                <span class="status <?php echo htmlspecialchars($garage['status']); ?>">
                    <?php echo htmlspecialchars($garage['status_text']); ?>
                </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Статистика -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">156</div>
                <div class="stat-label">Гаражей в системе</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">94%</div>
                <div class="stat-label">Своевременных платежей</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">28</div>
                <div class="stat-label">Выполненных заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">12</div>
                <div class="stat-label">Лет на рынке</div>
            </div>
        </div>

        <!-- Как это работает -->
        <div class="section-title">
            <h2>Как это работает</h2>
            <p>Всего несколько шагов к эффективному управлению вашим гаражным кооперативом</p>
        </div>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Регистрация</h3>
                <p>Председатель кооператива регистрирует организацию в системе и приглашает участников</p>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <h3>Добавление данных</h3>
                <p>Владельцы гаражей вносят данные о своих объектах и показания счетчиков</p>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <h3>Автоматизация расчетов</h3>
                <p>Система автоматически рассчитывает платежи и формирует квитанции</p>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <h3>Контроль и отчетность</h3>
                <p>Полный контроль платежей, заявок и формирование финансовой отчетности</p>
            </div>
        </div>
    </div>
</section>

<!-- Футер -->
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>PiGarage</h3>
                <p>Современная платформа для управления гаражными кооперативами. Автоматизация учета, платежей и коммунальных услуг.</p>
            </div>

            <div class="footer-column">
                <h3>Быстрые ссылки</h3>
                <ul class="footer-links">
                    <li><a href="#">О компании</a></li>
                    <li><a href="#">Тарифы</a></li>
                    <li><a href="#">Документация</a></li>
                    <li><a href="#">Блог</a></li>
                    <li><a href="#">Контакты</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Контакты</h3>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt"></i> Москва, ул. Гаражная, 15</li>
                    <li><i class="fas fa-phone"></i> +7 (495) 123-45-67</li>
                    <li><i class="fas fa-envelope"></i> info@pigarage.ru</li>
                    <li><i class="fas fa-clock"></i> Пн-Пт: 9:00-18:00</li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            &copy; 2023 PiGarage. Все права защищены.
        </div>
    </div>
</footer>

<script>
    // Добавляем интерактивность строкам таблицы
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            // Убираем выделение с других строк
            document.querySelectorAll('tbody tr').forEach(r => {
                r.style.backgroundColor = '';
            });

            // Выделяем текущую строку
            this.style.backgroundColor = 'rgba(74, 111, 165, 0.1)';

            // Получаем данные из строки
            const garageNum = this.cells[0].textContent;
            const owner = this.cells[1].textContent;

            // Показываем информацию о гараже (в реальном приложении здесь может быть открытие модального окна)
            console.log(`Выбран гараж №${garageNum}, владелец: ${owner}`);
        });
    });

    // Анимация при прокрутке
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.08)';
        }
    });

    // Обновление времени в реальном времени для демонстрации
    function updateDateTime() {
        const now = new Date();
        const dateTimeStr = now.toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Если бы у нас был элемент для отображения времени, мы бы его обновили
        // document.getElementById('currentDateTime').textContent = dateTimeStr;
    }

    // Обновляем время каждую минуту
    setInterval(updateDateTime, 60000);
    updateDateTime(); // Первоначальный вызов
</script>
</body>
</html>
