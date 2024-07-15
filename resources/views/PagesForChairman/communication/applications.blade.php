@extends('layouts.profileChairman', ['ApplicationsStyles' => ['applications.css', 'scroll_coop.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('Applications.index', ['id' => Auth::id()])}}" class="active">Заявки</a>
        <a href="{{route('ApplicationsReject.index', ['id' => Auth::id()])}}">Отклонённые заявки</a>
    </div>
    <div class="main_body">
        <div class="left_block">
            <span class="my_coop">Мои кооперативы</span>
            <div class="list_my_coop">
            @foreach($coops as $coop)
            <?php for ($i=0; $i < 1; $i++) { ?>
            <form class="myForm" data-id-coop="{{$coop->id_coop}}" method="GET" action="">
                @csrf
                <input type="hidden" name="id_coop" id="id_coop_input" value="{{$coop -> id_coop}}">
                <button type="submit" class="button_coop_array" data-id-coop="{{$coop->id_coop}}">
                    <div class="block_coop">
                        <div class="img_center_left_icon">
                            <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="кооператив">
                        </div>
                        <div class="right_text_left_column">
                            <span>
                                {{$coop -> name}}
                            </span>
                        </div>
                        <span class="active_click" data-coop-id="{{$coop->id_coop}}"></span>
                    </div>
                </button>
            </form>
            <?php } ?>
            @endforeach
          </div>
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
                    1) "Заявки от пользователей" – те, кто желает присоединиться к вашему кооперативу; <br> <br>
                2) "Запросы от участников" – запросы от своих участников кооператива, которые желают присоединить свой гараж, к вашему кооперативу.</span>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
     function sendAjaxRequestUser() {
        $.ajax({
            url: '{{ route('Applications.index', ['id' => Auth::id()]) }}',
            type: "GET",
            data: {
                idCoop: idCoopValue,
                idMessage: 1,
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
                let garageMessages = response.garagesUsers;
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
                        <span class="right_text_fio">${coopMessage.user_fio}</span>`;
                        if (garageMessages !== null && garageMessages.length > 0) {
                            html += `<div class="block_garages">`
                            garageMessages.forEach(function (garageMessages, index) {
                                html += `<div class="block_garage_inf">
                                 <div class="block_message">
                            <div class="error-message" style="display: none;"></div>
                            <div class="success-message" style="display: none;"></div>
                            </div>
                                        <span class="absolute_span_number_garage">Гараж  ${index + 1}:</span>
                                <span name="number_meter">Номер счётчика: ${garageMessages.number_meter}</span>
                                <span>Номер гаража: <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage" id="tariff_value" maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')" value="${garageMessages.number_garage}"></span>
                                <span>Номер блока: <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_block" id="tariff_value" maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')" value="${garageMessages.number_block}"></span>
                                <div class="checkbox-wrapper-13">
                                <label for="c1-13">Принять</label>
                                <input id="c1-13" type="checkbox" checked="checked">
                                </div>
                                <span class="border-line"></span></div>`
                            });
                            html += `</div>`
                        } else {
                            `<div class="block_garages">
                            <span>Нет информации, возможно что-то пошло не так</span>
                            </div>`
                        }

                        html +=  `<div class="space_between"></div>
                        <div class="display_form">
                        <!-- Принять заявку -->
                        <form id="formAddUser_${coopMessage.user_id}" class="myFormAddUser" method="POST" action="">
                        @csrf
                        <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                        <input type="hidden" name="coop_id" value="${idCoopValue}">
                        <button type="submit" class="button_green">Принять</button>
                        </form>
                        <!-- Отменить заявку -->
                        <form id="formCancelUser_${coopMessage.user_id}" class="myFormCancelUser" method="POST" action="">
                        @csrf
                        <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                        <input type="hidden" name="coop_id" value="${idCoopValue}">
                        <button type="submit" class="button_red">Отменить</button>
                        </form>
                        <!-- Отклонить заявку -->
                        <form id="formRejectUser_${coopMessage.user_id}" class="myFormRejectUser" method="POST" action="">
                        <input type="hidden" name="user_id" value="${coopMessage.user_id}">
                        <input type="hidden" name="coop_id" value="${idCoopValue}">
                        <input type="hidden" name="ip_massage" value="3">
                        <button type="submit" class="button_red">Отклонить заявку</button>
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
                console.log(error);
                        // Обработка ошибки
                isSubmitMyForm === false;
            }
        });
}

function sendAjaxRequestParticipant() {
    $.ajax({
        url: '{{ route('Applications.index', ['id' => Auth::id()]) }}',
        type: "GET",
        data: {
            idCoop: idCoopValue,
            idMessage: 2,
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
                $('.buttons_ajax_user, .buttons_ajax_participant').prop("disabled", false);
                $(this).prop("disabled", true);
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
            function messageBlock(idUser) {
                $(`#formMessage_${idUser}`).find('.block_garage_inf').each(function(index) {
                    let check = $(this).find('input[type="checkbox"]').prop('checked');
                    if (check) {
                        $(this).find('.success-message').html(`<span class="text_message">Принят</span>`).slideDown(500);
                    } else {
                        $(this).find('.error-message').html(`<span class="text_message">Не принят</span>`).slideDown(500);
                    }
                });
            }
                let garages = [];
                let hasEmptyFields = false;
                function arrayGarage() {
                    garages = [];
                    $('.block_garage_inf').each(function (index, element) {
                        let meterNumber = $(element).find('span[name="number_meter"]').text().trim().match(/:.*$/)[0].substring(1).trim();
                        let garageNum = $(element).find('input[name="number_garage"]').val().trim();
                        let blockNumber = $(element).find('input[name="number_block"]').val().trim();
                        let checkbox = $(element).find('input[type="checkbox"]');

                        if (!meterNumber || !garageNum || !blockNumber) {
                            hasEmptyFields = true;
                            return false; // Прерываем each
                        }

                        if (checkbox.is(':checked')) {
                            let garageData = {
                                'number_meter': meterNumber,
                                'number_garage': garageNum,
                                'number_block': blockNumber,
                                'checked': true,
                            };

                            garages.push(garageData);
                        } else {
                            let garageData = {
                                'number_meter': meterNumber,
                                'number_garage': garageNum,
                                'number_block': blockNumber,
                                'checked': false,
                            };

                            garages.push(garageData);
                        }
                    });
                }

            $(document).on('submit', '.myFormAddUser', function (e) {
                e.preventDefault();
                arrayGarage();
                console.log(garages);
                let userIdValue = $(this).find('input[name="user_id"]').val();
                let idCoopValue = $(this).find('input[name="coop_id"]').val();
                $.ajax({
                    url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                    type: "POST",
                    data: {
                        idUser: userIdValue,
                        idCoop: idCoopValue,
                        garageData: garages,
                        ipMessage: 1,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $(`[id^="formAddUser_${response.idUser}"], [id^="formRejectUser_${response.idUser}"]`).hide();
                        $(`#formCancelUser_${response.idUser}`).show();
                        messageBlock(response.idUser);
    
                        },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
            $(document).on('submit', '.myFormCancelUser', function (e) {
                e.preventDefault();
                arrayGarage();
                let userIdValue = $(this).find('input[name="user_id"]').val();
                let idCoopValue = $(this).find('input[name="coop_id"]').val();
                 $(`#formMessage_${userIdValue}`).find('.success-message, .error-message').slideUp(500, function() {
                    $(this).html('');
                });
                $.ajax({
                    url: "{{ route('ApplicationsPost.store', ['id' => Auth::id()])}}",
                    type: "POST",
                    data: {
                        idUser: userIdValue,
                        idCoop: idCoopValue,
                        garageData: garages,
                        ipMessage: 2,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $(`[id^="formAddUser_${response.idUser}"], [id^="formRejectUser_${response.idUser}"]`).show();
                        $(`#formCancelUser_${response.idUser}`).hide();

                    },
                    error: function (error) {
                        console.log(error);
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
