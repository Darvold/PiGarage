<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link rel="stylesheet" href="{{asset('css/HeadBlock/head.css')}}">
    <link rel="stylesheet" href="{{asset('css/NavigationBlock/left_block.css')}}">
    <link rel="stylesheet" href="{{asset('css/Body/scroll.css')}}">
    @php
        $profileStyles = [
            'ProfileUserStyles' => 'PagesForChairman/profile/',
            'ProfileGarageStyles' => 'PagesForChairman/garage/',
            'ProfileCreateGarageStyles' => 'PagesForChairman/createGarage/',
            'myGaragePivotTableStyles' => 'PagesForChairman/myGaragePivotTable/',
            'ProfileCoopStyles' => 'PagesForChairman/myCoop/',
            'ProfileCreateCoopStyles' => 'PagesForChairman/createCoop/',
            'ProfileConnectCoopStyles' => 'PagesForChairman/connectCoop/',
            'ProfileCoopPivotTableStyles' => 'PagesForChairman/myCoopPivotTable/',
            'ApplicationsStyles' => 'PagesForChairman/applications/',
            'MessagesStyles' => 'PagesForChairman/messages/',
            'MessagesMetersStyles' => 'PagesForChairman/messagesMeters/',
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
<div class="Head_border">
    <div class="Head_content">
        <span class="Logotip"><img src="{{asset('icons/user/logo.svg')}}" alt="Логотип" class="svg">PiGarage</span>
        <div class="Block_Text_User" id="arrow_img">
            <span class="Text_user">Председатель</span>
            <img src="{{asset('image/user/defaultUser.jpg')}}" alt="профиль" class="image_profile">
            <div class="block_arrow">
                <div class="block_arrow_botton">
                    <img src="{{asset('icons/user/arrowToLeft.svg')}}" alt="стрелка" class="arrow" id="arrow">
                </div>
                <div id="hidden_element" class="div_block_to_arrow div_block_to_arrow_hidden">
                    <div class="flex_block">
                        <div class="text_button">
                            <div class="left_block_icon">
                                <img src="{{asset('icons/user/user.svg')}}" alt="пользователь">
                            </div>
                            <div class="right_block_text">
                                <a href="" class="button_a">Профиль</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex_block">
                        <div class="text_button">
                            <div class="left_block_icon">
                                <img src="{{asset('icons/user/cooperative.svg')}}" alt="кооператив">
                            </div>
                            <div class="right_block_text">
                                <a href="" class="button_a">Кооперативы</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex_block">
                        <div class="text_button">
                            <div class="left_block_icon">
                                <img src="{{asset('icons/user/settings.svg')}}" alt="настройки">
                            </div>
                            <div class="right_block_text">
                                <a href="" class="button_a">Настройки</a>
                            </div>
                        </div>
                    </div>
                    <div class="flex_block">
                        <div class="text_button">
                            <div class="left_block_icon">
                                <img src="{{asset('icons/user/exit.svg')}}" alt="выход">
                            </div>
                            <div class="right_block_text">
                                <form action="{{route('LogoutAdmin.store', ['idAdmin' => Auth::id()])}}" method="POST">
                                    @csrf
                                    <button type="submit" class="button_b">Выход</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        const arrow = $('#arrow');
        const arrow_img = $('#arrow_img');
        const hiddenElement = $('#hidden_element');

        // Обработчик для arrow и arrow_img
        arrow.add(arrow_img).click(function () {
            event.stopPropagation();
            arrow.toggleClass('arrow_down');
            if (hiddenElement.is(':visible')) {
                hiddenElement.slideUp(400); // Скрытие элемента вверх за 0.6 секунды (600 миллисекунд)
            } else {
                hiddenElement.slideDown(400); // Отображение элемента сверху за 0.6 секунды (600 миллисекунд)
            }
        });

        $(document).click(function (event) {
            if (!arrow.is(event.target) && !arrow_img.is(event.target) && !hiddenElement.is(event.target)) {
                arrow.removeClass('arrow_down');
                hiddenElement.slideUp(400); // Скрытие элемента вверх за 0.6 секунды (600 миллисекунд)
            }
        });

        hiddenElement.click(function (event) {
            event.stopPropagation();
        });
    });
</script>

<div class="flex_block_main">
    <div class="left_block_navigation">
        <div class="text_button_navigation">
            <div class="left_block_icon_navigation">
                <img src="{{asset('icons/user/user.svg')}}" alt="профиль">
            </div>
            <div class="right_block_text_navigation">
                <a href="">Профиль</a>
            </div>
        </div>
        <div class="text_button_navigation">
            <div class="left_block_icon_navigation">
                <img src="" alt="кооператив">
            </div>
            <div class="right_block_text_navigation">
                <a href="">Кооперативы</a>
            </div>
        </div>
        <div class="text_button_navigation">
            <div class="left_block_icon_navigation">
                <img src="{{asset('icons/user/message.svg')}}" alt="сообщения">
            </div>
            <div class="right_block_text_navigation">
                <a href="">Сообщения</a>
            </div>
        </div>
        <div class="text_button_navigation">
            <div class="left_block_icon_navigation">
                <img src="{{asset('icons/user/logo.svg')}}" alt="гаражи">
            </div>
            <div class="right_block_text_navigation">
                <a href="">Мои гаражи</a>
            </div>
        </div>
        <div class="text_button_navigation">
            <div class="left_block_icon_navigation">
                <img src="{{asset('icons/user/applications.svg')}}" alt="гаражи">
            </div>
            <div class="right_block_text_navigation">
                <a href="">Заявки</a>
            </div>
        </div>
    </div>
    @yield('profileAdmin')
</div>

</body>
</html>

