@extends('layouts.profileChairman', ['ProfileCoopPayment' => ['payment.css', 'scroll.css']])

@section('profile')
@php($months = ['01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
'05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
'10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'])
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
        <a href="{{route('ChairmanMyCoopRate.index', ['idCoop' => $idCoop])}}" class="active">Оплата</a>
    </div>
    <div class="main_body">
        <div class="flex_container">
            <div class="container_mouth">
                @foreach ($months as $month => $key)
                <button class="id_month" data-month="{{$month}}">{{$key}}</button>
                @endforeach
            </div>
            <div class="main_table">
                <div class="head_table_block">
                    <div class="button_form_ajax">
                        <div class="block_1">
                            <span>Таблица оплаты</span>
                            <button class="last_year"><</button>
                            <span class="year">{{$year}}</span>
                            <button class="next_year">></button>
                        </div>
                    </div>
                </div>
                <div class="body_table_block">
                    @foreach($usersCoop as $user)
                    <?php
                    $randomNumber = rand(0, 999);
                    $randomLetter = chr(rand(97, 122));
                    $randomNumberFormatted = sprintf("%03d", $randomNumber);
                    $randomString = $randomLetter.$randomNumberFormatted;
                    ?>
                    <form method="post" action="" class="form_payment"
                    id="stringNumber{{$randomString}}Code{{$user->id}}{{$randomLetter}}PiPgarageNumber">
                    @csrf
                    <div class="block_payment">
                        <div class="blocks_flex_content">
                            <div class="image_avatar_block">
                                <img src="{{asset('image/user/defaultUser.jpg')}}">
                            </div>
                            <div class="flex_column_container">
                                <div class="head_container">
                                    <span>{{$user->fio}}</span>
                                </div>
                                <div class="body_container">
                                    <span>Оплатил:</span>
                                    <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="tariff_value"
                                    id="tariff_value"
                                    maxlength="10"
                                    oninput="this.value=this.value.replace(/\D/g,'')"
                                    value="{{ $user->payment_value }}">
                                    <span>Руб.</span>
                                </div>
                                <div class="footer_container">
                                    <button type="submit" class="button_save">Сохранить</button>
                                </div>
                            </div>
                        </div>
                        <div class="block_message">
                            <div class="error-message" style="display: none;"></div>
                            <div class="success-message" style="display: none;"></div>
                        </div>
                    </div>
                </form>
                @endforeach

            </div>
        </div>
        <div class="flex_container_right_block">
            <div class="right_head_block">
                <div class="input_search_block">
                    <input type="text" name="search_fio" id="search_fio" placeholder="Фамилия / Имя / Отчество">
                    <!--  <button id="find_in_list">Найти</button> -->
                </div>
                <div class="block_2">
                    <span>Список участников: </span>
                    <button id="minus"><</button>
                    <span id="number_list"></span>
                    <button id="plus">></button>
                    <button id="reset" title="Сбросить выделенное"><img src="{{asset('icons/user/reset.svg')}}"></button>
                </div>
                <div class="block_3">
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-1" checked="checked" name="payment" type="radio" style="display: none;" />
                        <label class="rbx" for="rbx-1">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Смешанно (По умолчанию)</span>
                        </label>
                    </div>
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-2" name="payment" type="radio" style="display: none;" />
                        <label class="rbx" for="rbx-2">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Оплатили</span>
                        </label>
                    </div>
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-3" name="payment" type="radio" style="display: none;" />
                        <label class="rbx" for="rbx-3">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Не оплатили</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="container_users">
                @forelse($usersCoop as $user)         
                <div class="flex_fio_check">
                    <div class="fio_user">
                        <button data-id="{{$user->id}}">{{$user->fio}}</button>
                    </div>
                    <div class="checkbox-wrapper-4">
                      <input class="inp-cbx" id="morning{{$user->id}}" type="checkbox"/>
                      <label class="cbx" for="morning{{$user->id}}"><span>
                          <svg width="12px" height="10px">
                            <use xlink:href="#check-4"></use>
                        </svg></span><span><!-- Text --></span></label>
                        <svg class="inline-svg">
                            <symbol id="check-4" viewbox="0 0 12 10">
                              <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                          </symbol>
                      </svg>
                  </div>
              </div>
              @empty
              <span class="empty_message">В кооперативе нет участников</span>
              @endforelse
          </div>
      </div>
  </div>
</div>
</div>
<script>
    $(document).ready(function () {

    });
</script>

<script>
    $(document).ready(function () {
        let currentPage = 1;
        let usersPerPage = 8;
        let selectedId = "rbx-1";
        let $usersContainer = $('.container_users');
        let $usersButtons = $usersContainer.find('.flex_fio_check');
        let totalPages = Math.ceil($usersButtons.length / usersPerPage);
        let usersSelected = [];
        let $filteredButtons = $usersButtons; 
            // Функция для показа пользователей на текущей странице
        function showPage(page) {
            let startIndex = (page - 1) * usersPerPage;
            let endIndex = startIndex + usersPerPage;
        $usersButtons.hide(); // Сначала скрываем все кнопки
        $filteredButtons.slice(startIndex, endIndex).show(); // Показываем отфильтрованные кнопки для текущей страницы
        $('#number_list').text(page + '/' + totalPages);
    }
            // Инициализация показа первой страницы
    showPage(currentPage);

            // Обработчик нажатия кнопки вперед
    $('#plus').click(function () {
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
        }
    });

            // Обработчик нажатия кнопки назад
    $('#minus').click(function () {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });
    $('.block_3 .inp-rbx').change(function() {
        selectedId = $(this).attr('id');
            //var selectedText = $('label[for="' + selectedId + '"] span:last-child').text();
        findUsers(selectedId, usersSelected);

    });
   // Обработчик ввода в поле поиска
    $('#search_fio').on('input', function () {
        var searchValue = $(this).val().toLowerCase();
        $filteredButtons = $usersButtons.filter(function () {
            return $(this).find('button').text().toLowerCase().includes(searchValue);
        });
        totalPages = Math.ceil($filteredButtons.length / usersPerPage);
        currentPage = 1;
        showPage(currentPage);
    });
    // Обработчик нажатия кнопки сброса
    $('#reset').click(function() {
        $('.flex_fio_check button').css({
            backgroundColor: '',
            color: 'black',
        });
        $('.body_table_block .form_payment').show();
        usersSelected.length = 0;
        $('#search_fio').val('');
        $filteredButtons = $usersButtons;
        totalPages = Math.ceil($filteredButtons.length / usersPerPage);
        currentPage = 1;
        $('.inp-cbx').prop('checked', false);
        $('#rbx-1').prop('checked', true);
        selectedId = 'rbx-1';
        showPage(currentPage);
    });
    $(document).on('click', '.container_users button', function(event) {
        $('.flex_fio_check button').css({
            backgroundColor: '',
            color: 'black',
        });
        if ($('.inp-cbx').is(':checked')) {
            $('.inp-cbx:checked').closest('.flex_fio_check').find('button').css({
                backgroundColor: '#33B168',
                color: 'white'
            });
        }
        let fioUser = $(this).text();
        usersSelected.push(fioUser);
        findUsers(selectedId, usersSelected);
        let index = usersSelected.indexOf(fioUser);
        if (index !== -1) {
            usersSelected.splice(index, 1);
        }
        $(this).closest('.flex_fio_check').find('button').css({
            backgroundColor: '#33B168',
            color: 'white',
        });
    });

    function findUsers(selectedId, usersSelected) {
    // Сначала скрываем все формы
        $('.body_table_block .form_payment').hide();
        $('.flex_fio_check button').css({
            backgroundColor: '',
            color: 'black',
        });

        if ($('.inp-cbx').is(':checked')) {
            $('.inp-cbx:checked').closest('.flex_fio_check').find('button').css({
                backgroundColor: '#33B168',
                color: 'white'
            });
        }
    // Перебираем каждую форму
        $('.body_table_block .form_payment').each(function() {
            let fioUser = $(this).find('.head_container span').text();
            let $paymentInput = $(this).find('[name="tariff_value"]');
            let paymentValue = $paymentInput.val();
            if (paymentValue === "0") {
                $paymentInput.val(null);
                paymentValue = '';
            }
        // Показываем формы, если нет выбранных пользователей
            if (usersSelected.length === 0) {
                $('.flex_fio_check button').css({
                    backgroundColor: '',
                    color: 'black',
                });
                if (selectedId === "rbx-1") {
                    $(this).show();
                } else if (selectedId === "rbx-2" && paymentValue) {
                    $(this).show();
                } else if (selectedId === "rbx-3" && !paymentValue) {
                    $(this).show();
                }
            } else {
            // Показываем формы, соответствующие выбранным пользователям
                if (usersSelected.includes(fioUser)) {
                    if (selectedId === "rbx-1") {
                        $(this).show();
                    } else if (selectedId === "rbx-2" && paymentValue) {
                        $(this).show();
                    } else if (selectedId === "rbx-3" && !paymentValue) {
                        $(this).show();
                    }
                }
            }
        });
    }

    $('.inp-cbx').on('change', function() {
        let css = $(this).closest('.flex_fio_check').find('button');
        let fioUser = $(this).closest('.flex_fio_check').find('button').text();
        if ($(this).is(':checked')) {
            css.css({
                backgroundColor: '#33B168',
                color: 'white',
            });
            usersSelected.push(fioUser);
        } else {
            css.css({
                backgroundColor: '',
                color: 'black',
            });
            let index = usersSelected.indexOf(fioUser);
            if (index !== -1) {
                usersSelected.splice(index, 1);
            }
        }
        findUsers(selectedId, usersSelected);
    });


    function searchPaymentValue() {
        $('.container_users .flex_fio_check .fio_user span').remove();
        $('.body_table_block .form_payment').each(function() {
            let fioUser = $(this).find('.head_container span').text();
            let paymentValue = $(this).find('[name="tariff_value"]').val();
            if (paymentValue) {
                $('.container_users .flex_fio_check').each(function() {
                    let userFio = $(this).find('.fio_user button').text();
                    if (userFio === fioUser && paymentValue !== "0") {
                        $(this).find('.fio_user').append(`<span>Оплатил: ${paymentValue}</span>`);
                    }
                });
            }
        });
    }
    searchPaymentValue();
    $('.next_year').prop("disabled", true);
    $('.id_month[data-month="{{$monthNow}}"]').prop("disabled", true);
    let isSubmitMyBlock = false;
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий
            let currentTime = new Date().getTime();
            let timeDifference = currentTime - lastClickTime;
            let currentYear;
            let idMonth = '0' + {{$monthNow}};
            let currentYearInput = {{$year}};

            $('.id_month[data-month={{$monthNow}}]').css({
                backgroundColor: '#1C82E7',
                color: 'white',
            });

            var currentMonth = new Date().getMonth(); // Получаем текущий месяц (от 0 до 11)
            // Создаем массивы месяцев и их номеров
            let months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            let numberMonths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

            function sendAjaxRequestPayment(currentYearInput, idMonth) {
                $.ajax({
                    url: '{{ route('ChairmanMyCoopPayment.index', ['idCoop' => $idCoop]) }}',
                    type: "GET",
                    data: {
                        id_year: currentYearInput,
                        id_month_number: idMonth,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('.body_table_block').empty();
                        // Блокируем новую кнопку
                        $('.id_month').prop("disabled", false);
                        let currentButton = $(`.id_month[data-month='${response.monthNow}']`).prop("disabled", true);

                        currentButton.css({
                            backgroundColor: '#1C82E7',
                            color: 'white',
                        });
                        // Сохраняем текущую кнопку как предыдущую
                        previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                        lastClickTime = new Date().getTime();
                        clickCount++;


                        let blockMessages = response.blockMessages;


                        // Перебираем все месяцы
                        for (var i = 0; i < blockMessages.length; i++) {
                            var randomString = getRandomString();
                            var formId = 'stringNumber' + randomString + 'Code' + blockMessages[i].id + randomString.charAt(0) + 'PiPgarageNumber';
                            // Создаем общую структуру формы с CSRF-токеном
                            var html = `<form method="post" action="" class="form_payment" id="${formId}">
                            @csrf
                            <div class="block_payment">
                            <div class="blocks_flex_content">
                            <div class="image_avatar_block">
                            <img src="{{asset('image/user/defaultUser.jpg')}}">
                            </div>
                            <div class="flex_column_container">
                            <div class="head_container">
                            <span>${blockMessages[i].fio}</span>
                            </div>
                            <div class="body_container">
                            <span>Оплатил:</span>
                            <input type="text" pattern="[0-9]{1,10}"  title="Только цифры" name="tariff_value" id="tariff_value"
                            maxlength="10" oninput="this.value=this.value.replace(/\\D/g,'')"
                            value="${blockMessages[i].payment_value ? blockMessages[i].payment_value : ''}">
                            <span>Руб.</span>
                            </div>
                            <div class="footer_container">
                            <button type="submit" class="button_save">Сохранить</button>
                            </div>
                            </div>
                            </div>
                            <div class="block_message">
                            <div class="error-message" style="display: none;"></div>
                            <div class="success-message" style="display: none;"></div>
                            </div>
                            </div>
                            </form>`;
                            $('.body_table_block').append(html);

                        }
                        findUsers(selectedId, usersSelected);
                        searchPaymentValue();
                    },
                    error: function (error) {
                        $('.body_table_block').text('Что-то пошло не так, повторите попытку позже');
                    }
                });
            }

            $('.last_year, .next_year').click(function (e) {
                let currentTime = new Date().getTime();


                let timeDifference = currentTime - lastClickTime;
                currentYearInput = parseInt($('.year').text());

                $('.body_table_block').empty();
                $('.body_table_block').html('Подождите, запрос выполняется...');
                if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
                    $(".body_table_block").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

                    // Блокируем кнопки на 3 секунды
                    $('.last_year, .next_year, .id_month').prop("disabled", true);

                    setTimeout(function () {
                        $('.next_year').prop("disabled", false);
                        if (currentYearInput === 2023) {
                            $('.last_year').prop("disabled", true);
                        } else {
                            $('.last_year').prop("disabled", false);
                        }
                        if (currentYearInput === {{$year}} || currentYearInput >= {{$year}}) {
                            $('.next_year').prop("disabled", true);
                        } else {
                            $('.next_year').prop("disabled", false);
                        }

                        sendAjaxRequestPayment(currentYearInput, idMonth);
                    }, 3000);
                    clickCount = 0;
                } else {
                    // Сбрасываем счетчик, если прошло более 1 секунды с предыдущего нажатия
                    if (timeDifference >= 500) {
                        clickCount = 0;
                    }
                    clickCount++;
                    if ($(this).hasClass('last_year')) {
                        // Если нажата кнопка "last_year"
                        currentYear = Math.max(currentYearInput - 1, 2022); // Ограничение до 2020
                        currentYearInput = Math.max(currentYearInput - 1, 2022); // Ограничение до 2020
                        sendAjaxRequestPayment(currentYearInput, idMonth);
                        if (currentYearInput === 2023) {
                            $('.last_year').prop("disabled", true);
                        } else {
                            $('.last_year').prop("disabled", false);
                        }
                        $('.next_year').prop("disabled", false);
                    } else {
                        $('.last_year').prop("disabled", false);
                        // Если нажата кнопка "next_year"
                        currentYear = currentYearInput + 1;
                        currentYearInput = currentYearInput + 1;
                        sendAjaxRequestPayment(currentYearInput, idMonth);
                        if (currentYearInput === {{$year}} || currentYearInput >= {{$year}}) {
                            $('.next_year').prop("disabled", true);
                        } else {
                            $('.next_year').prop("disabled", false);
                        }
                        $('.last_year').prop("disabled", false);

                    }
                    return $('.year').text(currentYear) + currentYearInput;

                }
            });


            $('.id_month').click(function (e) {
                e.preventDefault();
                $('.id_month').css({
                    backgroundColor: 'white',
                    color: 'black'
                });
                $(this).css({
                    backgroundColor: '#1C82E7',
                    color: 'white'
                });
                idMonth = $(this).data('month');
                isSubmitMyBlock = true;
                $('.body_table_block').empty();
                $('.body_table_block').html('Подождите, запрос выполняется...');
                let currentTime = new Date().getTime();
                let timeDifference = currentTime - lastClickTime;
                // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
                if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
                    $(".body_table_block").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                    // Блокируем кнопки на 3 секунды
                    $('.last_year, .next_year, .id_month').prop("disabled", true);

                    setTimeout(function () {
                        $('.last_year, .next_year, .id_month').prop("disabled", false);
                        if (currentYearInput === 2023) {
                            $('.last_year').prop("disabled", true);
                        } else {
                            $('.last_year').prop("disabled", false);
                        }
                        sendAjaxRequestPayment(currentYearInput, idMonth);
                        clickCount = 0;

                    }, 3000);

                } else {
                    // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
                    if (timeDifference >= 1000) {
                        clickCount = 0;
                    }
                    // Снимаем блокировку с предыдущей кнопки, если она существует
                    if (previousButton) {
                        previousButton.prop("disabled", false);
                    }

                    if (isSubmitMyBlock === true) {
                        sendAjaxRequestPayment(currentYearInput, idMonth);
                    }
                }
            });

            function messageBlock(text, form, bool) {
                if (bool == false) {
                    form.closest('.form_payment').find('.error-message').html(text).slideDown(500);

                    setTimeout(function () {
                        form.closest('.form_payment').find('.error-message').slideUp(500);
                        form.closest('.form_payment').find('.error-message').html();
                        form.closest('.form_payment').find('.button_save').prop("disabled", false);
                    }, 3000);
                } else {
                    form.closest('.form_payment').find('.success-message').html(text).slideDown(500);

                    setTimeout(function () {
                        form.closest('.form_payment').find('.success-message').slideUp(500);
                        form.closest('.form_payment').find('.success-message').html();
                        form.closest('.form_payment').find('.button_save').prop("disabled", false);
                    }, 3000);
                }
            }

            function sendAjaxRequestPaymentPost(paymentValue, numbers, currentYearInput, idMonth, form) {
                $.ajax({
                    url: '{{ route('ChairmanMyCoopPaymentPost.store', ['idCoop' => $idCoop]) }}',
                    type: "POST",
                    data: {
                        id_user: numbers,
                        payment_value: paymentValue,
                        id_year: currentYearInput,
                        id_month_number: idMonth,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        if (response.error) {
                            messageBlock("Не удалось отправить запрос", form, false);
                            return;
                        }
                        messageBlock(response.success, form, true);
                        searchPaymentValue();
                    },
                    error: function (error) {
                        $('.body_table_block').text('Что-то пошло не так, повторите попытку позже');
                    }
                });
            }
            function getRandomString() {
                var randomNumber = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                var randomLetter = String.fromCharCode(97 + Math.floor(Math.random() * 26));
                return randomLetter + randomNumber;
            }
            $(document).on('submit', '.form_payment', function (e) {
                e.preventDefault();
                let form = $(this);
                form.closest('.form_payment').find('.button_save').prop("disabled", true);
                let paymentValue = $(this).closest('.form_payment').find('[name="tariff_value"]').val();
                let idUser = $(this).closest('.form_payment').find('[name="user_id"]').val();

  // Получение числовой части id
                let formId = form.attr('id');
                let match = formId.match(/stringNumber(\w+)Code(\d+)/);
                let numbers = null;

    // Если числовая часть найдена, извлекаем её
                if (match && match.length > 2) {
                    numbers = match[2];
                }


                sendAjaxRequestPaymentPost(paymentValue, numbers, currentYearInput, idMonth, form);
/*
        isSubmitMyBlock = true;
        $('.body_table_block').empty();
        $('.body_table_block').html('Подождите, запрос выполняется...');
        let currentTime = new Date().getTime();
        let timeDifference = currentTime - lastClickTime;
            // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
        if (timeDifference < 2000 && clickCount > 5) {
                // Отображаем сообщение об ошибке
            $(".body_table_block").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                // Блокируем кнопки на 3 секунды
            $('.last_year, .next_year, .id_month').prop("disabled", true);

            setTimeout(function () {
                $('.last_year, .next_year, .id_month').prop("disabled", false);
                if (currentYearInput === 2023) {
                    $('.last_year').prop("disabled", true);
                } else {
                    $('.last_year').prop("disabled", false);
                }
                sendAjaxRequestPayment(currentYearInput, idMonth);
                clickCount = 0;

            }, 3000);

        } else {
                // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
            if (timeDifference >= 1000) {
                clickCount = 0;
            }
                // Снимаем блокировку с предыдущей кнопки, если она существует
            if (previousButton) {
                previousButton.prop("disabled", false);
            }
            e.preventDefault();

            if (isSubmitMyBlock === true) {
                sendAjaxRequestPayment(currentYearInput, idMonth);
            }
        }*/
            });
        });
    </script>
    @endsection
