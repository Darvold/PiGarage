@extends('layouts.mainChairman', ['MessagesMetersStyles' => ['messagesMeters.css', 'scroll.css']])

@section('profile')
@php($months = ['01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
'05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
'10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'])
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
            <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
            <a href="{{route('MessagesMeters.index', ['idCoop' => $idCoop])}}" class="active">Показания участников</a>
        </div>
        <div class="main_body">
            <div class="right_two_block">
                <div class="top_block">
                    <div class="buttons">
                        <button data-id="pending">Новые показания (Н)
                        </button>
                        <button data-id="accepted">Принятые показания</button>
                        <button data-id="canceled">Отклонённые показания</button>
                    </div>
                    <div class="button_form_ajax">
                        <div class="block_1">
                            <button class="last_year"><</button>
                            <span class="year">{{$year}}</span>
                            <button class="next_year">></button>
                        </div>
                    </div>
                    <div class="scroll_garage">
                        @forelse($blocks as $block)
                        <button class="button_number_garage" data-id-block={{$block->id_block}}>
                            Гаражный ряд №{{$block->number_block}}
                            @if($block->meter_readings_users_count > 0)
                            ({{$block->meter_readings_users_count}} Н)
                            @endif
                        </button>
                        @empty
                        <span>Вам необходимо создать гаражный блок!</span>
                        @endforelse
                    </div>
                </div>
                <div class="flex_block_2">
                    <div class="right_block">
                        <span class="text_error_year">Выберите вариант показаний, гаражый блок и месяц для получения показаний</span>
                    </div>
                    <div class="container_mouth">
                        @foreach ($months as $month => $key)
                        <button class="id_month" data-month="{{$month}}">{{$key}}</button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.last_year, .next_year').click(function (e) {
                if (!isSubmitMyBlock) {
                    $('.right_block').empty().html(`<span class="text_error_year">Выберите гаражый блок и месяц для получения показаний</span>`);
                    return false;
                }
                let currentTime = new Date().getTime();
                let timeDifference = currentTime - lastClickTime;
                currentYearInput = parseInt($('.year').text());
            $('.right_block').empty();
            $('.right_block').html('Подождите, запрос выполняется...');
            if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
                $(".right_block").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

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

                    sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);
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
                        sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);
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
                        sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);
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

            $('.container_mouth button').css({
                backgroundColor: 'white',
                color: 'black'
            });
            $(this).css({
                backgroundColor: '#1C82E7',
                color: 'white'
            });
            $('.last_year, .next_year, .id_month').prop("disabled", true);
            idMonth = $(this).data('month');
            $('.right_block').empty();
            $('.right_block').html('Подождите, запрос выполняется...');
            let currentTime = new Date().getTime();
            let timeDifference = currentTime - lastClickTime;
                        // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
            if (timeDifference < 2000 && clickCount > 5) {
                            // Отображаем сообщение об ошибке
                $(".right_block").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                            // Блокируем кнопки на 3 секунды
                $('.last_year, .next_year, .id_month').prop("disabled", true);

                setTimeout(function () {
                    $('.last_year, .next_year, .id_month').prop("disabled", false);
                    if (currentYearInput === 2023) {
                        $('.last_year').prop("disabled", true);
                    } else {
                        $('.last_year').prop("disabled", false);
                    }
                    if (!isSubmitMyBlock) {
                        $('.right_block').empty().html(`<span class="text_error_year">Выберите гаражый блок и месяц для получения показаний</span>`);
                        return false;
                    }
                    sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);

                    clickCount = 0;

                }, 3000);

            } else {
                $('.last_year, .next_year, .id_month').prop("disabled", false);
                // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
                if (timeDifference >= 1000) {
                    clickCount = 0;
                }
                // Снимаем блокировку с предыдущей кнопки, если она существует
                if (previousButton) {
                    previousButtonMouth.prop("disabled", false);
                }

                if (!isSubmitMyBlock) {
                    $('.right_block').empty().html(`<span class="text_error_year">Выберите гаражый блок и месяц для получения показаний</span>`);
                    return false;
                }
                sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);

            }
        });

        $('.id_month[data-month={{$currentMonth}}]').css({
            backgroundColor: '#1C82E7',
            color: 'white',
        });

            let isSubmitMyBlock = false;
            let isSubmitMonth = true;
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий
            let idBlockValue;
            let currentMonth = {{$currentMonth}};
            let idMonth = currentMonth < 10 ? '0' + currentMonth : currentMonth.toString();
            let currentButtonMonth;
            let previousButtonMouth;
            let currentYearInput = {{$year}};
            let buttonIdClick = null;
            let buttonClickDataId = 0;
            $('.buttons button').on('click', function () {
                $('.buttons button').removeClass('clicked');
                $('.buttons button').prop("disabled", false);
                $(this).prop("disabled", true);
                $('.right_block').html('Подождите, запрос выполняется...');
            let buttonClick = $(this).addClass('clicked');
            buttonClickDataId = $(this).data('id');
            buttonIdClick = true;
            sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);

            });

// Глобальный объект для хранения оригинальных значений форм
            let originalMeterValues = [];
            function sendAjaxRequestBlockKw(idBlockValue, year, idMonth, buttonClickDataId) {
                console.log(buttonClickDataId);
                if (!buttonIdClick) {
                    $('.right_block').empty();
                    $('.right_block').text("Выберите вариант показания!");
                    $('.right_block').css({
                                fontSize: '25px',
                            });
                    return false;
                }
                if (!idBlockValue) {
                    $('.right_block').empty();
                    $('.right_block').text("Выберите гаражный ряд!");
                    $('.right_block').css({
                                fontSize: '25px',
                            });
                    return false;
                }
                let month = idMonth ? idMonth : {{$currentMonth}};
                if (month.toString().length === 1) {
                    month = '0' + month;
                }
                $.ajax({
                    url: '{{ route('MessagesMeters.index', ['idCoop' => $idCoop]) }}',
                        type: "GET",
                        data: {
                            id_block: idBlockValue,
                            year: year ? year : {{$year}},
                            month: month,
                            typeReadings: buttonClickDataId,
                            _token: '{{ csrf_token() }}',
                        },
                    success: function (response) {
                        $('.id_month').prop("disabled", false);
                        currentButtonMonth = $(`.id_month[data-month='${idMonth}']`).prop("disabled", true);
                        currentButtonMonth.css({
                            backgroundColor: '#1C82E7',
                            color: 'white',
                        });
                        previousButtonMouth = currentButtonMonth;
                        let error = response.error;
                        if (error) {
                            $('.right_block').html(error);
                            return;
                        }
                        originalMeterValues = [];
                        $('.right_block').empty();
                        // Блокируем новую кнопку
                        let currentButton = $(`.button_number_garage[data-id-block='${idBlockValue}']`);
                        currentButton.prop("disabled", true);
                        currentButton.css({
                            border: '3px solid #1369c0',
                        });
                        // Сохраняем текущую кнопку как предыдущую
                        previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                        lastClickTime = new Date().getTime();
                        clickCount++;

                        let blockMessages = response.metersReadings;
                        const currentFolderPaths = response.folderPaths.current; // Пути к фотографиям текущих показаний
                        const oldFolderPaths = response.folderPaths.old; // Пути к фотографиям прошлых показаний
                        if (blockMessages.length <= 0) {
                            $('.right_block').html("На данный момент показаний от участников нет");
                            $('.right_block').css({
                                fontSize: '25px',
                            });
                            return;
                        }
                        blockMessages.forEach((reading, index) => {
                            // Проверяем наличие гаража и пользователя перед обращением к свойствам
                            const fio = reading.garages[0].user.fio;
                            const metersReadings = reading.kw_meter;
                            const sendDate = reading.send_date;
                            const idReading = reading.id_reading;
                            const garageNumber = reading.garages[0].number_garage;
                            // Форматирование даты с использованием словесного представления месяца
                            const formattedDate = moment(sendDate).locale('ru').format('D MMMM YYYY HH:mm:ss');
                            let readingData = {
                                fio: fio,
                                metersReadings: metersReadings,
                                formattedDate: formattedDate,
                                idReading: idReading,
                                garageNumber: garageNumber,
                            };
                            if (response.typeReadings === "pending") {
                            // Генерация HTML-кода для отображения текущих и прошлых показаний
                            var html = `<div class="block_applications" data-value-id="${idReading}">
                                <div class="head_block">
                                    <div class="img_center_right">
                                        <img src="{{asset('image/user/defaultUserMinSize.jpg')}}" alt="Пользователь">
                                    </div>
                                    <div class="right_text_right_block">
                                        <div style="display: flex;">
                                            <div class="text_right_span">
                                                <form method="POST" action="" class="form_input">
                                                    @csrf
                                                    <input type="hidden" name="idMessage" value="0">
                                                    <div class="flex_head_block">
                                                        <div class="left_block_span">
                                                            <span class="fio">${fio}</span>
                                                            <span>Номер гаража: ${garageNumber}</span>
                                                            <div class="span_div_flex">
                                                                <span>Показания (кВт):</span>
                                                                <input type="number" class="input_numbers" name="inputNumbers" id="numberMeter" value="${metersReadings}" inputmode="none">
                                                            </div>
                                                        </div>
                                                        <div class="img_meter">
                                                            ${currentFolderPaths[index]} <!-- Текущее фото -->
                                                        </div>
                                                    </div>
                                                    <div class="display_flex_button">
                                                        <button type="submit" data-title="Фото счётчика" id="lightbox-image-${index}" data-lightbox="image-${index}" data-index="${index}" class="button_img">Смотреть текущее фото</button>
                                                        <button type="submit" class="button_green" id="button_green-${index}">Принять</button>
                                                    </div>
                                                </form>
                                                <div class="flex_bottom_block">
                                                    <form method="POST" action="" class="form_delete">
                                                        @csrf
                                                        <input type="hidden" name="idMessage" value="1">
                                                        <button type="submit" class="button_red" id="button_red-${index}">Отклонить</button>
                                                    </form>
                                                    <span class="formattedDate">${formattedDate}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="old_inf">`
                                } else if (response.typeReadings === "accepted") {
                                    var html = `<div class="block_applications" data-value-id="${idReading}">
                                <div class="head_block">
                                    <div class="img_center_right">
                                        <img src="{{asset('image/user/defaultUserMinSize.jpg')}}" alt="Пользователь">
                                    </div>
                                    <div class="right_text_right_block">
                                        <div style="display: flex;">
                                            <div class="text_right_span">
                                                <form method="POST" action="" class="form_update">
                                                    @csrf
                                                    <input type="hidden" name="idMessage" value="2">
                                                    <div class="flex_head_block">
                                                        <div class="left_block_span">
                                                            <span class="fio">${fio}</span>
                                                            <span>Номер гаража: ${garageNumber}</span>
                                                            <div class="span_div_flex">
                                                                <span>Показания (кВт):</span>
                                                                <input type="number" class="input_numbers" name="inputNumbers" id="numberMeter" value="${metersReadings}" inputmode="none">
                                                            </div>
                                                        </div>
                                                        <div class="img_meter">
                                                            ${currentFolderPaths[index]} <!-- Текущее фото -->
                                                        </div>
                                                    </div>
                                                    <div class="display_flex_button">
                                                        <button type="submit" data-title="Фото счётчика" id="lightbox-image-${index}" data-lightbox="image-${index}" data-index="${index}" class="button_img">Смотреть текущее фото</button>
                                                        <button type="submit" class="button_update" id="button_green-${index}">Изменить</button>
                                                    </div>
                                                </form>
                                                <div class="flex_bottom_block">
                                                    <form method="POST" action="" class="form_delete">
                                                        @csrf
                                                        <input type="hidden" name="idMessage" value="1">
                                                        <button type="submit" class="button_red" id="button_red-${index}">Отклонить</button>
                                                    </form>
                                                    <span class="formattedDate">${formattedDate}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="old_inf">`
                                }
                                if (oldFolderPaths[index] == null) {
                                html += `<div class="old_inf_block">
                                        <span>Прошлых показаний нет или не найдено</span>
                                    </div>`
                                } else {
                                    html += `<div class="old_inf_block">
                                        <span>Прошлое принятое показание: <span class="old_time_text"></span></span>
                                        <span>Показания (кВт): <span class="old_kw_meter"></span></span>
                                    </div>
                                     <div class="img_meter_old old">
                                        ${oldFolderPaths[index]}
                                    </div>`
                                }
                                html += `</div>
                                <div class="block_message">
                                    <div class="error-message" style="display: none;"></div>
                                    <div class="success-message" style="display: none;"></div>
                                </div>
                            </div>`;
                            // Добавляем HTML-код на страницу
                            $('.right_block').append(html);
                            const dateOld = $(`.block_applications[data-value-id="${idReading}"] .img_meter_old a[data-date]`).data('date');
                            const kwMeterOld = $(`.block_applications[data-value-id="${idReading}"] .img_meter_old a[data-kw-meter]`).data('kw-meter');
                            if (dateOld && kwMeterOld) {
                                const formattedOldDate = moment(dateOld).locale('ru').format('D MMMM YYYY');
                                $(`.block_applications[data-value-id="${idReading}"]`).find('.old_time_text').text(`${formattedOldDate}`);
                                $(`.block_applications[data-value-id="${idReading}"]`).find('.old_kw_meter').text(`${kwMeterOld}`);
                            }
                            // Обновляем данные для текущих и прошлых показаний
                            originalMeterValues.push(readingData);

                        });
                    },
                    error: function (error) {
                        let textError = error.responseJSON.error;
                        $('.right_block').text(textError);
                        $('.right_block').css({
                            fontSize: '25px',
                        });
                    }
                });
            }
            $('.button_number_garage').click(function (e) {
                $('.button_number_garage').css({
                    backgroundColor: 'white',
                    color: 'black',
                });
                $(this).css({
                    backgroundColor: '#1369c0',
                    color: 'white',
                });
                idBlockValue = $(this).data('id-block');
                isSubmitMyBlock = true;
                $('.right_block').empty();
                $('.right_block').html('Подождите, запрос выполняется...');
                $('.right_block').css({
                    fontSize: '25px',
                });
                let currentTime = new Date().getTime();
                let timeDifference = currentTime - lastClickTime;
                // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
                if (timeDifference < 2000 && clickCount > 5) {
                    $('.right_block').empty();
                    // Отображаем сообщение об ошибке
                    $(".right_block").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                    // Блокируем кнопки на 3 секунды
                    $('.button_number_garage').prop("disabled", true);

                    setTimeout(function () {
                        $('.button_number_garage').prop("disabled", false);

                        sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);
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
                        sendAjaxRequestBlockKw(idBlockValue, currentYearInput, idMonth, buttonClickDataId);
                    }
                }
            });

            // Функция поиска объекта по idReading
            function findObjectByIdReading(idReading, fio, formattedDate) {
                return $.grep(originalMeterValues, function (item) {
                    return item.idReading == idReading && item.fio == fio && item.formattedDate == formattedDate;
                })[0]; // Возвращает первый найденный объект или undefined, если ничего не найдено
            }

            function messageBlock(text, form, id) {
                if (id == 2) {
                    // Отображаем уведомление
                form.closest('.block_applications').find('.success-message').html(text).slideDown(500);
                // Задержка перед скрытием уведомления (например, 3 секунды)
                setTimeout(function () {
                    form.closest('.block_applications').find('.success-message').slideUp(500);
                }, 4000);
                return false;
                }
                // Отображаем уведомление
                form.closest('.block_applications').find('.error-message').html(text).slideDown(500);
                // Задержка перед скрытием уведомления (например, 3 секунды)
                setTimeout(function () {
                    form.closest('.block_applications').find('.error-message').slideUp(500);
                }, 4000);
            }

            $(document).on('submit', '.form_input, .form_delete, .form_update', function (e) {
                e.preventDefault();
                $('.button_green, .button_red, .button_update').prop("disabled", true);

                var form = $(this); // Получаем текущую форму

                var idMessage = form.find('input[name="idMessage"]').val();

                var numberMeter = form.closest('.text_right_span').find('input[name="inputNumbers"]').val();
                var fio = form.closest('.block_applications').find('.fio').text();
                var formattedDate = form.closest('.text_right_span').find('.formattedDate').text();
                var idReading = form.closest('.block_applications').attr('data-value-id');
                // Дополнительная проверка на тип переменной idMessage
                if (idMessage !== "0" && idMessage !== "1" && idMessage !== "2") {
                    $('.button_green, .button_red, .button_update').prop("disabled", false);
                    messageBlock("Не удалось отправить запрос", form)
                    return;
                }

                // Проверка, содержит ли строка только цифры и может начинаться с "+" или "-"
                var isValidNumber = /^(?!0)\d{1,15}$/;

                if (!isValidNumber.test(numberMeter)) {
                    messageBlock("В показателях содержутся недопустимые символы", form)
                    $('.button_green, .button_red, .button_update').prop("disabled", false);
                    return;
                }
                // Вызываем функцию для проверки наличия формы с такими же параметрами
                if (checkDuplicateForm(idMessage, fio, formattedDate, idReading, form)) {
                    messageBlock("Не удалось отправить запрос", form)
                    $('.button_green, .button_red, .button_update').prop("disabled", false);
                    return;
                }

                function checkDuplicateForm(idMessage, fio, formattedDate, idReading, currentForm) {
                    var isDuplicate = false;

                    var formClass = (idMessage == 0) 
                    ? '.form_input' 
                    : (idMessage == 1) 
                    ? '.form_delete' 
                    : (idMessage == 2) 
                    ? '.form_update' 
                    : '';
                    if (formClass) {
                        currentForm.closest('.right_block').find(formClass).not(currentForm).each(function () {
                            var existingForm = $(this);

                            if (existingForm.length > 0) {
                                var existingFio = existingForm.closest('.text_right_span').find('.fio').text();
                                var existingFormattedDate = existingForm.closest('.text_right_span').find('.formattedDate').text();
                                var existingIdReading = existingForm.closest('.block_applications').attr('data-value-id');

                                if (
                                    existingFio === fio &&
                                    existingFormattedDate === formattedDate &&
                                    existingIdReading === idReading
                                ) {
                                    isDuplicate = true;
                                    return false;
                                }
                            }
                        });
                    } else {
                        messageBlock("Не удалось отправить запрос", form)
                        $('.button_green, .button_red, .button_update').prop("disabled", false);
                    }
                    return isDuplicate;
                }

                // Поиск объекта по idReading в массиве originalMeterValues
                var foundObject = findObjectByIdReading(idReading, fio, formattedDate);

                if (foundObject) {
                    // Ваш код обработки найденного объекта перед отправкой AJAX-запроса
                    var valueToSend = foundObject.idReading;
                    // Получение значения inputNumbers из той же формы
                    if (!valueToSend) {
                        messageBlock("Не удалось отправить запрос", form)
                        $('.button_green, .button_red, .button_update').prop("disabled", false);
                    }
                } else {
                    messageBlock("Не удалось отправить запрос", form)
                    $('.button_green, .button_red, .button_update').prop("disabled", false);
                    return;
                }

                $.ajax({
                    url: '{{ route('MessagesMetersPost.store', ['idCoop' => $idCoop]) }}',
                    type: "POST",
                    data: {
                        idMessage: idMessage,
                        numberMeter: numberMeter,
                        idReading: valueToSend,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        let blockMessages = response.response;
                        $('.button_green, .button_red, .button_update').prop("disabled", true);
                        // Блокируем кнопки
                        if (blockMessages == 'error') {
                            messageBlock("Произошла ошибка на сервере. Повторите попытку позже", form)
                            // Откладываем разблокировку кнопок после 4 секунд
                            setTimeout(function () {
                                // Разблокировка кнопок
                                $('.button_green, .button_red, .button_update').prop("disabled", false);
                            }, 4000);
                        }
                        setTimeout(function () {
                            // Разблокировка кнопок
                            $('.button_green, .button_red, .button_update').prop("disabled", false);
                        }, 4000);
                        if (response.idMessage == 2) {
                            messageBlock("Успешно обновлено!", form, 2);
                            return false;
                        }
                        $(`.block_applications[data-value-id="${blockMessages}"]`).fadeOut(500, function () {
                            $(this).remove();
                        });
                    },
                    error: function (error) {
                        let textError = error.responseJSON.error;
                        $('.right_block').text(textError);
                        $('.right_block').css({
                            fontSize: '25px',
                        });
                    }
                });
            });
        });
    </script>
@endsection
{{-- success: function (response) {
                    let blockMessages = response.response;
                    let idBlockValue = response.idBlock; // ID блока, в котором было принято сообщение
                    let updatedCount = response.updatedCount; // Обновленное количество сообщений в блоке

                    $('.button_green, .button_red').prop("disabled", true);

                    if (blockMessages == 'error') {
                        messageBlock("Произошла ошибка на сервере. Повторите попытку позже", form);
                        setTimeout(function () {
                            $('.button_green, .button_red').prop("disabled", false);
                        }, 4000);
                    } else {
                        // Убираем сообщение с экрана
                        $(`.block_applications[data-value-id="${blockMessages}"]`).fadeOut(500, function () {
                            $(this).remove();
                        });

                        // Обновляем количество сообщений на кнопке блока
                        let button = $(`.button_number_garage[data-id-block='${idBlockValue}']`);
                        let buttonText = button.text().trim();

                        // Извлекаем текст кнопки и обновляем счетчик сообщений
                        if (updatedCount > 0) {
                            button.text(`Гаражный блок №${idBlockValue} (${updatedCount})`);
                        } else {
                            button.text(`Гаражный блок №${idBlockValue}`);
                        }
                    } --}}
