<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/media_form.css') }}">
    {{--<link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/background_login.css') }}">--}}
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap" rel="stylesheet">
    <title>Авторизация</title>

</head>
<body>
<div class="center">
    <form method="post" action="{{route('loginUser.store')}}" class="Reg_Form">
        @csrf
        <span class="Text-Label-border-bottom">
				<span class="Text-Label">Авторизация</span>
			</span>

        <div class="form-group">
            <label for="email">Почта</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/email.svg') }}" class="icon" alt="">
            <input type="email" name="email" placeholder="PiGarage.2023@mail.ru" value="{{old('email')}}" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon">
            <input type="password" name="password" placeholder="Менее 6 символов" required>
        </div>


        <div class="form-group-check">
            <div class="form-group-radio">
                <label>Я </br>участник</label>
                <input type="radio" class="radio" name="user_role" value="participant" required>
            </div>
            <div class="form-group-radio">
                <label>Я </br>председатель</label>
                <input type="radio" class="radio" name="user_role" value="chairperson" required>
                <input type="hidden" name="id_al" id="id_al" value="{{ old('id_al') }}">
            </div>
        </div>

        <button type="submit">Отправить</button>
        <div class="Button-Back">
            <a href="{{route('welcome.index')}}" class="Button_a"><img src="{{ asset('css/LoginAndRegisterUser/icons/back.svg') }}" class="icon-back"></a>
            <a href="{{route('registr.index')}}" class="Button_b">Регистрация</a>
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
<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
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
    document.addEventListener('DOMContentLoaded', () => {
        const participantRadio = document.querySelector('input[name="user_role"][value="participant"]');
        const chairpersonRadio = document.querySelector('input[name="user_role"][value="chairperson"]');
        const id_alInput = document.getElementById('id_al');

        participantRadio.addEventListener('change', () => {
            if (participantRadio.checked) {
                id_alInput.value = 1;
            }
        });

        chairpersonRadio.addEventListener('change', () => {
            if (chairpersonRadio.checked) {
                id_alInput.value = 2;
            }
        });
    });
</script>
</body>
</html>
