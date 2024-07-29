@php
$regions = array(
"Алтайский край", "Амурская область","Архангельская область",
"Астраханская область","Белгородская область","Брянская область",
"Владимирская область","Волгоградская область","Вологодская область",
"Воронежская область","город Москва","город Санкт-Петербург",
"город Севастополь","Еврейская автономная область","Забайкальский край",
"Ивановская область","Иркутская область","Кабардино-Балкарская Республика",
"Калининградская область","Калужская область","Камчатский край",
"Карачаево-Черкесская Республика","Кемеровская область","Кировская область",
"Костромская область","Краснодарский край","Красноярский край",
"Курганская область","Курская область","Ленинградская область",
"Липецкая область","Магаданская область","Московская область",
"Мурманская область","Ненецкий автономный округ","Нижегородская область",
"Новгородская область","Новосибирская область","Омская область",
"Оренбургская область","Орловская область","Пензенская область","Пермский край",
"Приморский край","Псковская область","Республика Адыгея","Республика Алтай",
"Республика Башкортостан","Республика Бурятия","Республика Дагестан",
"Республика Ингушетия","Республика Калмыкия","Республика Карелия",
"Республика Коми","Республика Крым","Республика Марий Эл","Республика Мордовия",
"Республика Саха (Якутия)","Республика Северная Осетия — Алания",
"Республика Татарстан","Республика Тыва","Республика Хакасия",
"Ростовская область","Рязанская область","Самарская область",
"Саратовская область","Сахалинская область","Свердловская область",
"Смоленская область","Ставропольский край","Тамбовская область",
"Тверская область","Томская область","Тульская область","Тюменская область",
"Удмуртская Республика","Ульяновская область","Хабаровский край",
"Ханты-Мансийский автономный округ — Югра","Челябинская область",
"Чеченская Республика","Чувашская Республика","Чукотский автономный округ",
"Ямало-Ненецкий автономный округ","Ярославская область"
);
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/media_form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/PagesForChairman/connectCoop/select2.min.css') }}">
    {{--<link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/background_reg.css') }}">--}}
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap" rel="stylesheet">
    <title>Регистрация</title>

</head>
<body>
    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/NumberMask/imask.js') }}"></script>
    <script src="{{ asset('js/select/select2.min.js') }}"></script>
    <script src="{{ asset('js/select/ru.js') }}"></script>
    <script src="{{ asset('js/select/russian-cities.js') }}"></script>
    <div class="center">
        <form method="POST" action="{{route('registrationAddUser.store')}}" class="Reg_Form">
            @csrf
            <span class="Text-Label-border-bottom">
                <span class="Text-Label">Регистрация</span>
            </span>
            <div class="form-group">
                <label for="fio">ФИО</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/user.svg') }}" class="icon">
                <input type="text" name="fio" placeholder="Иванов Иван Иванович" value="{{ old('fio') }}" required>
            </div>
            <div class="form-group">
                <label for="phone">Телефон</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/phone.svg') }}" class="icon">
                <input type="text" data-mask="phone" id="phone" class="phone" value="{{ old('phone') }}" required placeholder="+7">
                <input type="hidden" name="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Почта</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/email.svg') }}" class="icon">
                <input type="email" name="email" placeholder="PiGarage.2024@mail.ru" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Придумайте пароль</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon">
                <input type="password" name="password" placeholder="Менее 6 символов (макс. 255)" required>
            </div>
            <div class="form-group">
                <label for="password">Подтверждение пароля</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon">
                <input type="password" name="password_confirmation" placeholder="Менее 6 символов (макс. 255)" required>
            </div>

            <div class="form-group-check">
                <div class="form-group-radio">
                    <label>Я <br> участник</label>
                    <input type="radio" class="radio" name="user_role" value="participant" required @if(old('user_role') == 'participant') checked @endif>
                </div>
                <div class="form-group-radio">
                    <label>Я <br> председатель</label>
                    <input type="radio" class="radio" name="user_role" value="chairperson" required @if(old('user_role') == 'chairperson') checked @endif>
                </div>
                <input type="hidden" name="id_al" id="id_al" value="{{ old('id_al') }}">
            </div>

            <div class="form-group-region">
                <label for="region">Регион</label>
                <img src="{{ asset('css/LoginAndRegisterUser/icons/point.svg') }}" class="icon">
                <div class="select_wrp">
            <!-- <select id="region" name="region" required class="Region">
                <option value="">Выберите регион</option>
                @foreach ($regions as $region)
                    <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                @endforeach
            </select> -->
            <select class="js-select-region" name="region" placeholder="Выберите регион" required>
                <option value="">Выберите регион</option>
                <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
            </select>
        </div>
    </div>

    <button type="submit">Отправить</button>
    <div class="Button-Back">
        <a href="{{route('welcome.index')}}" class="Button_a"><img src="{{ asset('css/LoginAndRegisterUser/icons/back.svg') }}" class="icon-back"></a>
        <a href="{{route('login.index')}}" class="Button_b">Авторизация</a>
    </div>
</form>
</div>
<div class="form-msg">
    @if (session('success'))
    <div class="notification-content">
        <div class="notification"> {{ session('success') }}</div>
    </div>

    @elseif (session('error'))
    <div class="notification-content">
        <div class="notification"> {{ session('error') }}</div>
    </div>

    @endif
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputElement = document.querySelector('[data-mask="phone"]')
        const maskOptions = { // создаем объект параметров
            mask: '+{7}(000)000-00-00' // задаем единственный параметр mask
        }
        IMask(inputElement, maskOptions) // запускаем плагин с переданными параметрами
    })
</script>
<script>
$(document).ready(function() {
    const participantRadio = $('input[name="user_role"][value="participant"]');
    const chairpersonRadio = $('input[name="user_role"][value="chairperson"]');
    const id_alInput = $('#id_al');

    participantRadio.change(function() {
        if (participantRadio.is(':checked')) {
            id_alInput.val(1);
        }
    });

    chairpersonRadio.change(function() {
        if (chairpersonRadio.is(':checked')) {
            id_alInput.val(2);
        }
    });
});

</script>
<script>
    $(document).ready(function() {
        $(".notification").css('top', '-100px'); // Скрываем уведомление за пределами видимой области
        setTimeout(function() {
            $(".notification").animate({top: 20}, 500, function() {
                setTimeout(function() {
                    $(".notification").animate({top: '-100px'}, 500, function() {
                        $(this).remove();
                    });
                }, 6000); // 3 секунды
            });
        }, 0); // Задержка перед появлением
    });
</script>
<script>
    $(document).ready(function () {
        $('button[type="submit"]').click(function(e) {
            e.preventDefault();
            let phoneInput = $('#phone').val();
            let onlyDigits = phoneInput.replace(/\D/g, '');
            $('input[name="phone"]').val(onlyDigits);
            $(this).closest('form').submit();
        });

        $('#load-more').on('click', function () {
            noEmty = false;
            ajaxSelectCoop();
        });

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

            uniqueSubjects.forEach(subject => {
                const option = $('<option></option>').val(subject).text(subject);
                selectRegion.append(option);
            });
        }

    // Обработчик события 'change' для выбора региона
        $('.js-select-region').on('change', function () {
        selectedRegion = $(this).val();
    });
    });

</script>
</body>
</html>
