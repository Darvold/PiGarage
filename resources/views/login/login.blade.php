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
    <script src="{{ asset('js/NumberMask/imask.js') }}"></script>
<div class="center">
    <form method="post" action="{{route('loginUser.store')}}" class="Reg_Form">
        @csrf
        <span class="Text-Label-border-bottom">
				<span class="Text-Label">Авторизация</span>
			</span>
        <div class="form-group">
            <label for="phone">Телефон</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/phone.svg') }}" class="icon" alt="">
            <input type="text" data-mask="phone" id="phone" class="phone" value="{{ old('phone') }}" required placeholder="+7">
            <input type="hidden" name="phone" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <img src="{{ asset('css/LoginAndRegisterUser/icons/password.svg') }}" class="icon">
            <input type="password" name="password" placeholder="" required>
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
            <div class="notification success"> {{ session('success') }}</div>
        </div>
    @elseif (session('error'))
        <div class="notification-content">
            <div class="notification error"> {{ session('error') }}</div>
        </div>
    @elseif (session('info'))
        <div class="notification-content">
            <div class="notification info"> {{ session('info') }}</div>
        </div>
    @endif
</div>
<script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('button[type="submit"]').click(function(e) {
            e.preventDefault();
            let phoneInput = $('#phone').val();
            let onlyDigits = phoneInput.replace(/\D/g, '');
            $('input[name="phone"]').val(onlyDigits);
            $(this).closest('form').submit();
        });
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
        const inputElement = document.querySelector('[data-mask="phone"]')
        const maskOptions = { // создаем объект параметров
            mask: '+{7}(000)000-00-00' // задаем единственный параметр mask
        }
        IMask(inputElement, maskOptions) // запускаем плагин с переданными параметрами
    })
</script>
</body>
</html>
