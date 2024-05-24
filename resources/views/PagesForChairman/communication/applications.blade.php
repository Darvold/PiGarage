@extends('layouts.profileChairman', ['ApplicationsStyles' => ['applications.css', 'scroll_coop.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('Applications.index', ['id' => Auth::id()])}}" class="active">Заявки</a>
        <a href="{{route('ApplicationsReject.index', ['id' => Auth::id()])}}">Отклонённые заявки</a>
    </div>
    <div class="main_body">
        <div class="left_block">
            @foreach($coops as $coop)
            <form class="myForm" data-id-coop="{{$coop->id_coop}}" method="GET" action="">
                @csrf
                <input type="hidden" name="id_coop" id="id_coop_input" value="{{$coop -> id_coop}}">
                <button type="submit" class="button_coop_array" data-id-coop="{{$coop->id_coop}}">
                    <div class="block_coop">
                        <div class="img_center_left">
                            <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="кооператив">
                        </div>
                        <div class="right_text">
                            <span>
                                {{$coop -> name}}
                            </span>
                        </div>
                        <span class="active_click" data-coop-id="{{$coop->id_coop}}"></span>
                    </div>
                </button>
            </form>
            @endforeach
        </div>
        <div class="right_two_contains">
            <div class="buttons_ajax">
                <button class="buttons_ajax_user" data-type="user">Заявки от пользователей</button>
                <button class="buttons_ajax_participant" data-type="participant">Запросы от участников</button>
            </div>
            <div class="right_block">
                <div class="messageArray">

                </div>
                <span class="messages_container">Нажмите на нужный кооператив, а после выбор запроса: <br> <br>
                            1) "Заявки от пользователей" - те, кто желает присоединиться к вашему кооперативу; <br> <br>
                            2) "Запросы от участников" - запросы от своих участников кооператива, которые желают присоединить свой гараж, к вашему кооперативу.</span>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
       function sendAjaxRequestUser() {
            $.ajax({
                url: '{{ route('ApplicationsMessage.index', ['id' => Auth::id()]) }}',
                type: "GET",
                data: {
                    idCoop: idCoopValue,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    $('.messages_container').empty();
                        // Блокируем новую кнопку
                    let currentButton = $(`.button_coop_array[data-id-coop='${idCoopValue}']`);
                    currentButton.prop("disabled", true);
                        // Сохраняем текущую кнопку как предыдущую
                    previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                    lastClickTime = new Date().getTime();
                    clickCount++;

                    $(this).find('.button_coop_array').css({
                        border: '3px solid #1369c0',
                    }).text();
                    let coopMessages = response.coopMessages;
                        // Перебираем массив и обновляем содержимое на странице
                    if (coopMessages !== null && coopMessages.length > 0) {
                        coopMessages.forEach(function (coopMessage) {
                                // Ваш код для создания HTML-элементов на основе данных coopMessage
                            var html = `
                    <div class="block_applications" id="formMessage_${coopMessage.user_id}">
                        <div class="img_center_left">
                            <img src="{{asset('image/user/DefaultUser.jpg')}}" alt="кооператив">
                        </div>
                        <div class="right_text">
                            <span>${coopMessage.user_fio}</span>
                            <div class="display_form">
                                <!-- Принять заявку -->
                                <form id="formAddUser_${coopMessage.user_id}" class="myFormAddUser" method="POST" action="">
                                    @csrf
                                    <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                                    <input type="hidden" name="coop_id" value="${idCoopValue}">
                                    <input type="hidden" name="ip_massage" value="1">
                                    <button type="submit" class="button_green">Принять</button>
                                </form>
                                <!-- Отменить заявку -->
                                <form id="formCancelUser_${coopMessage.user_id}" class="myFormCancelUser" method="POST" action="">
                                    @csrf
                                    <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                                    <input type="hidden" name="coop_id" value="${idCoopValue}">
                                    <input type="hidden" name="ip_massage" value="2">
                                    <button type="submit" class="button_red">Отменить</button>
                                </form>
                                <!-- Отклонить заявку -->
                                <form id="formRejectUser_${coopMessage.user_id}" class="myFormRejectUser" method="POST" action="">
                                    <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                                    <input type="hidden" name="coop_id" value="${idCoopValue}">
                                    <input type="hidden" name="ip_massage" value="3">
                                    <button type="submit" class="button_red">Отклонить</button>
                                </form>
                            </div>
                        </div>
                    </div>`;
                            $('.messageArray').append(html);
                        });
                    } else {
                            // Выводим сообщение о пустом массиве
                        $('.messageArray').html('<div class="coopMessagesEmpty">Заявок от пользователей пока что нет</div>');
                    }
                    $('.myFormCancelUser').hide();
                    isSubmitMyForm === false;
                },
                error: function (error) {
                        // Обработка ошибки
                    isSubmitMyForm === false;
                }
            });
}
   function sendAjaxRequestParticipant() {
            $.ajax({
                url: '{{ route('ApplicationsMessageGarage.index', ['id' => Auth::id()]) }}',
                type: "GET",
                data: {
                    idCoop: idCoopValue,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    $('.messages_container').empty();
                    // Блокируем новую кнопку
                    let currentButton = $(`.button_coop_array[data-id-coop='${idCoopValue}']`);
                    currentButton.prop("disabled", true);
                    // Сохраняем текущую кнопку как предыдущую
                    previousButton = currentButton;
                    // Обновляем время последнего нажатия и увеличиваем счетчик
                    lastClickTime = new Date().getTime();
                    clickCount++;

                    $(this).find('.button_coop_array').css({
                        border: '3px solid #1369c0',
                    }).text();
                    let coopMessages = response.coopMessages;
                    // Перебираем массив и обновляем содержимое на странице
                    if (coopMessages !== null && coopMessages.length > 0) {
                        coopMessages.forEach(function (coopMessage) {
                            // Ваш код для создания HTML-элементов на основе данных coopMessage
                            const html = `
            <div class="block_applications_garage" id="formMessageGarage_${coopMessage.user_id}">
                <div class="img_center_left">
                    <img src="{{asset('image/user/DefaultUser.jpg')}}" alt="кооператив">
                </div>
                <div class="right_text_garage">
                    <span>${coopMessage.user_fio}</span>
                    <div class="inline_span"></div>
                    <div class="right_bottom_text">
                        <span>Номер гаража: ${coopMessage.user_number_garage}</span>
                        <span>Номер блока: ${coopMessage.user_number_block}</span>
                    </div>
                    <div class="display_form_garage">
                        <!-- Принять заявку -->
                        <form id="formAddGarage_${coopMessage.user_id}" class="myFormAddGarage" method="POST" action="">
                            @csrf
                            <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                            <input type="hidden" name="coop_id" value="${idCoopValue}">

                            <input type="hidden" name="number_garage" value="${coopMessage.user_number_garage}">
                            <input type="hidden" name="number_block" value="${coopMessage.user_number_block}">
                            <input type="hidden" name="number_meter" value="${coopMessage.user_number_meter}">
                            <input type="hidden" name="id_block" value="${coopMessage.user_id_block}">

                            <input type="hidden" name="ip_massage" value="4">
                            <button type="submit" class="button_green">Принять</button>
                        </form>
                        <!-- Отклонить заявку -->
                        <form id="formRejectGarage_${coopMessage.user_id}" class="myFormRejectGarage" method="POST" action="">
                            <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                            <input type="hidden" name="coop_id" value="${idCoopValue}">
                            <input type="hidden" name="ip_massage" value="5">
                            <button type="submit" class="button_red">Отклонить</button>
                        </form>
                    </div>
                </div>
            </div>
        `;
                            $('.messageArray').append(html);
                        });
                    } else {
                        // Выводим сообщение о пустом массиве
                        $('.messageArray').html('<div class="coopMessagesEmpty">Запросов от пользователей пока что нет</div>');
                    }
                    $('.myFormCancelUser').hide();
                    isSubmitMyForm === false;
                },
                error: function (error) {
                    // Обработка ошибки
                    isSubmitMyForm === false;
                }
            });
        }
            let id_applications; // Переменная для хранения значения
            let isSubmitMyForm = false;
            let isSubmitButton = false;
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий

            $('.buttons_ajax_user, .buttons_ajax_participant').click(function (e) {
                $('.buttons_ajax_user, .buttons_ajax_participant').css({
                    border: '',
                    backgroundColor: '#EDEDEDFF',
                    color: 'black',
                });

                $(this).css({
                    border: '',
                    backgroundColor: '#1369c0',
                    color: 'white',
                });

                id_applications = ($(this).hasClass('buttons_ajax_user')) ? 1 : 2;

                isSubmitButton = true;
            });

            $('.myForm').click(function (e) {
                e.preventDefault();
                // Очистка предыдущего HTML
                $('.messageArray').empty();
                $('.active_click').empty();

                $('.button_coop_array').css({
                    border: '',
                    color: 'black'
                });
                // Установка стилей для текущей формы
                $(this).find('.button_coop_array').css({
                    border: '3px solid #1369c0',
                });
                idCoopValue = $(this).data('id-coop');
                isSubmitMyForm = true;
            });
            let idCoopValue;


            $('.myForm, .buttons_ajax_user, .buttons_ajax_participant').click(function (e) {
                $('.messageArray').empty();
                $('.active_click').empty();
                let currentTime = new Date().getTime();
                let timeDifference = currentTime - lastClickTime;
                // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
                if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
                    $(".messages_container").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                    // Блокируем кнопки на 3 секунды
                    $('.button_coop_array, .buttons_ajax_user, .buttons_ajax_participant').prop("disabled", true);

                    setTimeout(function () {
                        $('.button_coop_array, .buttons_ajax_user, .buttons_ajax_participant').prop("disabled", false);

                        if (id_applications === 1) {
                            sendAjaxRequestUser();
                        }
                        if (id_applications === 2) {
                            sendAjaxRequestParticipant();
                        }
                        clickCount = 0;

                    }, 3000);

                } else {
                    // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
                    if (timeDifference >= 1000) {
                        clickCount = 0;
                    }

                    e.preventDefault();
                    // Снимаем блокировку с предыдущей кнопки, если она существует
                    if (previousButton) {
                        previousButton.prop("disabled", false);
                    }

                    if (isSubmitMyForm === true && isSubmitButton === true/* && id_applications === 1*/) {
                        if (id_applications === 1) {
                            sendAjaxRequestUser();
                        }
                        if (id_applications === 2) {
                            sendAjaxRequestParticipant();
                        }
                    } /*else {
                        $('.messageArray').html('<div class="coopMessagesEmpty"></div>');
                    }*/

                }
            });


            $(document).on('submit', '.myFormAddUser', function (e) {
                e.preventDefault();
                let userIdValue = $(this).find('input[name="user_id"]').val();
                let idCoopValue = $(this).find('input[name="coop_id"]').val();
                let ipMessage = $(this).find('input[name="ip_massage"]').val();
                $.ajax({
                    url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                    type: "POST",
                    data: {
                        idUser: userIdValue,
                        idCoop: idCoopValue,
                        ipMessage: ipMessage,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        /*           console.log('Пользователь ID:' + response.idUser);
                                   console.log('Кооператив ID:' + response.idCoop);
                                   console.log('Заявка номер 1');*/
                        // Скрываем все формы для данного пользователя
                        $(`[id^="formAddUser_${response.idUser}"], [id^="formRejectUser_${response.idUser}"]`).hide();
                        // Показываем форму отмены принятия для данного пользователя и кооператива
                        $(`#formCancelUser_${response.idUser}`).show();
                    },
                    error: function (error) {
                        console.log('ошибка');
                    }
                });
            });
            $(document).on('submit', '.myFormCancelUser', function (e) {
                e.preventDefault();

                let userIdValue = $(this).find('input[name="user_id"]').val();
                let idCoopValue = $(this).find('input[name="coop_id"]').val();
                let ipMessage = $(this).find('input[name="ip_massage"]').val();

                $.ajax({
                    url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                    type: "POST",
                    data: {
                        idUser: userIdValue,
                        idCoop: idCoopValue,
                        ipMessage: ipMessage,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        /*      console.log('Пользователь ID:' + response.idUser);
                              console.log('Кооператив ID:' + response.idCoop);
                              console.log('Заявка номер 2');*/
                        $(`[id^="formAddUser_${response.idUser}"], [id^="formRejectUser_${response.idUser}"]`).show();
                        // Показываем форму отмены принятия для данного пользователя и кооператива
                        $(`#formCancelUser_${response.idUser}`).hide();

                        // Дополнительная логика для обработки полученных данных
                    },
                    error: function (error) {
                        console.log('ошибка');
                    }
                });
            });
            $(document).on('submit', '.myFormRejectUser', function (e) {
                e.preventDefault();

                let userIdValue = $(this).find('input[name="user_id"]').val();
                let idCoopValue = $(this).find('input[name="coop_id"]').val();
                let ipMessage = $(this).find('input[name="ip_massage"]').val();

                $.ajax({
                    url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                    type: "POST",
                    data: {
                        idUser: userIdValue,
                        idCoop: idCoopValue,
                        ipMessage: ipMessage,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        /*      console.log('Пользователь ID:' + response.idUser);
                              console.log('Кооператив ID:' + response.idCoop);
                              console.log('Заявка номер 3');*/
                        $(`#formMessage_${response.idUser}`).fadeOut(500, function () {
                            $(this).remove();
                        });
                        // Дополнительная логика для обработки полученных данных
                    },
                    error: function (error) {
                        console.log('ошибка');
                    }
                });
            });
        $(document).on('submit', '.myFormAddGarage', function (e) {
            e.preventDefault();
            let userIdValue = $(this).find('input[name="user_id"]').val();
            let idCoopValue = $(this).find('input[name="coop_id"]').val();
            let ipMessage = $(this).find('input[name="ip_massage"]').val();

            let numberGarage = $(this).find('input[name="number_garage"]').val();
            let numberBlock = $(this).find('input[name="number_block"]').val();
            let numberMeter = $(this).find('input[name="number_meter"]').val();
            let id_block = $(this).find('input[name="id_block"]').val();

            $.ajax({
                url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                type: "POST",
                data: {
                    idUser: userIdValue,
                    idCoop: idCoopValue,
                    idBlock: id_block,
                    ipMessage: ipMessage,
                    numberGarage: numberGarage,
                    numberBlock: numberBlock,
                    numberMeter: numberMeter,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    /*           console.log('Пользователь ID:' + response.idUser);
                               console.log('Кооператив ID:' + response.idCoop);
                               console.log('Заявка номер 1');*/
                    // Скрываем все формы для данного пользователя
                    $(`#formMessageGarage_${response.idUser}`).fadeOut(500, function () {
                        $(this).remove();
                    });
                },
                error: function (error) {
                    console.log('ошибка');
                    console.log('номер счётчика ' + error.numberMeter);
                    console.log('ID пользователя ' + error.user_id);
                    console.log('ID сообщения ' + error.ip_message);
                }
            });
        });
        $(document).on('submit', '.myFormRejectGarage', function (e) {
            e.preventDefault();

            let userIdValue = $(this).find('input[name="user_id"]').val();
            let idCoopValue = $(this).find('input[name="coop_id"]').val();
            let ipMessage = $(this).find('input[name="ip_massage"]').val();

            $.ajax({
                url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                type: "POST",
                data: {
                    idUser: userIdValue,
                    idCoop: idCoopValue,
                    ipMessage: ipMessage,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    /*      console.log('Пользователь ID:' + response.idUser);
                          console.log('Кооператив ID:' + response.idCoop);
                          console.log('Заявка номер 3');*/
                    $(`#formMessageGarage_${response.idUser}`).fadeOut(500, function () {
                        $(this).remove();
                    });
                    // Дополнительная логика для обработки полученных данных
                },
                error: function (error) {
                    console.log('ошибка');
                }
            });
        });
        });

</script>
@endsection
