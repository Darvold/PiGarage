<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>PiGarage</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="{{ asset('css/PiGarageWelcome/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/PiGarageWelcome/media_855.css') }}">
</head>
<body>
<div class="Title-log">
    <span class="Title">PiGarage</span>
</div>
<div class="Title-box">
    <span class="Title">Как вы желаете войти?</span>
</div>
<div class="two-button">
    <div class="button_center">
        <a href="{{ route('login.index')}}">Авторизоваться</a>
    </div>
</div>
<div class="Title-box-2">
    <span class="Title">Зарегестрироваться как:</span>
</div>
<div class="two-button-2">
    <div class="button_center">
        <a href="{{ route('registr.index')}}">Зарегистрироваться</a>
    </div>
</div>
</body>
</html>
