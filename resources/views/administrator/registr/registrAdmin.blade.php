<!DOCTYPE html>
<html  lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $cssPath = asset('css/LoginAndRegisterUser/');
    @endphp
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/media_form.css') }}">
    {{--<link rel="stylesheet" href="{{ asset('css/LoginAndRegisterUser/background_reg.css') }}">--}}
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap" rel="stylesheet">
    <title>Регистрация</title>

</head>
<body>

<div class="center">
    <form method="POST" action="{{route('AdminRegistrAdd.store')}}" class="Reg_Form">
        @csrf
        <span class="Text-Label-border-bottom">
				<span class="Text-Label">RegistrAdminUser</span>
			</span>
        <div class="form-group">
            <label for="login">Логин</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/user.svg') }}" class="icon" alt="">
                <input type="text" name="login" placeholder="/MyLogin" value="{{ old('login') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Придумайте пароль</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon" alt="">
                <input type="password" name="password" placeholder="Менее 6 символов" required>
        </div>
        <div class="form-group">
            <label for="password">Подтверждение пароля</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon" alt="">
                <input type="password" name="password_confirm" placeholder="Менее 6 символов" required>
        </div>

        <button type="submit">Зарегистрировать</button>
{{--        <div class="Button-Back">
            <a href="{{route('welcome.index')}}" class="Button_a"><img src="{{ asset('css/LoginAndRegisterUser/icons/back.svg') }}" class="icon-back"></a>
            <a href="{{route('login.index')}}" class="Button_b">Авторизация</a>
        </div>--}}
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
{{--<script src="{{ asset('js/NumberMask/imask.js') }}"></script>--}}

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

</body>
</html>
