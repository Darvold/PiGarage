@extends('layouts.profileChairman', ['MessagesMetersStyles' => ['messagesMeters.css', 'scroll.css']])

@section('profile')
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
            <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
            <a href="{{route('MessagesMeters.index', ['idCoop' => $idCoop])}}" class="active">Показания участников</a>
        </div>
        <div class="main_body">
            <div class="right_two_block">
                <div class="top_block">
                    <div class="left_block_svg">
                        <img class="svg" onclick="leftScroll()" src="{{asset('icons/user/buttonLeft.svg')}}"
                             alt="Влево">
                    </div>
                    <div class="scroll_garage">
                        @forelse($blocks as $block)
                            <button class="button_number_garage" data-id-block={{$block->id_block}}>Гаражный блок
                                №{{$block->number_block}}</button>
                        @empty
                            <span>Вам необходимо создать гаражный блок!</span>
                        @endforelse
                    </div>
                    <div class="right_block_svg">
                        <img class="svg" onclick="rightScroll()" src="{{asset('icons/user/buttonRight.svg')}}"
                             alt="Вправо">
                    </div>
                </div>

                <div class="flex_block_2">
                    <div class="right_block">
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        function leftScroll() {
            const left = $(".scroll_garage");
            left.animate({scrollLeft: '-=300'}, 300);
        }

        function rightScroll() {
            const right = $(".scroll_garage");
            right.animate({scrollLeft: '+=300'}, 300);
        }

        var buttonCount = $('.scroll_garage button[data-id-block]').length;
        if (buttonCount <= 6) {
            $('.svg, .left_block_svg, .right_block_svg').css({
                'display': 'none',
            });
        } else {
            $('.svg, .left_block_svg, .right_block_svg').css({
                'display': 'block',
            });
        }

        $(document).ready(function () {
            let isSubmitMyBlock = false;
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий
            let idBlockValue;
// Глобальный объект для хранения оригинальных значений форм
            let originalMeterValues = [];

            function sendAjaxRequestBlockKw(idBlockValue) {
                $.ajax({
                    url: '{{ route('MessagesMeters.index', ['idCoop' => $idCoop]) }}',
                    type: "GET",
                    data: {
                        id_block: idBlockValue,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
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
                        let blockPath = response.folderPaths;

                        if (blockMessages.length <= 0) {
                            $('.right_block').html("На данный момент показаний от участников нет");
                            $('.right_block').css({
                                fontSize: '25px',
                            });
                            return;
                        }

                        blockMessages.forEach((reading, index) => {
                            // Проверяем наличие гаража и пользователя перед обращением к свойствам
                            // FIO пользователя гаража
                            const fio = reading.garages[0].user.fio;
                            const metersReadings = reading.kw_meter;
                            const sendDate = reading.send_date;
                            const idReading = reading.id_reading;
                            // Номер гаража
                            const garageNumber = reading.garages[0].number_garage;
// Форматирование даты с использованием словесного представления месяца
                            const formattedDate = moment(sendDate).locale('ru').format('D MMMM YYYY HH:mm:ss');
                            let readingData = {
                                fio: fio,
                                metersReadings: metersReadings,
                                formattedDate: formattedDate,
                                idReading: idReading,
                                garageNumber: garageNumber,
                                image: garageNumber,
                                // Добавьте другие свойства, если необходимо
                            };
                            // Далее вы можете использовать полученные значения fio и garageNumber по вашему усмотрению
                            var html = `<div class="block_applications" data-value-id="${idReading}">

    <div class="img_center_right">
        <img src="{{asset('image/user/DefaultUser.jpg')}}" alt="Пользователь">
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
                                <span>Показатели (кВт):</span>
                                <input type="number" class="input_numbers" name="inputNumbers" id="numberMeter" value="${metersReadings}" inputmode="none">
                            </div>
                        </div>
                        <div class="img_meter">
                            ${blockPath[index]}
                        </div>
                    </div>
                    <div class="display_flex_button">
                        <button type="submit" data-title="Фото счётчика" id="lightbox-image-${index}" data-lightbox="image-${index}" data-index="${index}" class="button_img">Смотреть фото</button>
                        <button type="submit" class="button_green" id="button_green">Принять</button>
                    </div>
                </form>
                <div class="flex_bottom_block">
                    <form method="POST" action="" class="form_delete">
                        @csrf
                            <input type="hidden" name="idMessage" value="1">
                    <button type="submit" class="button_red" id="button_red">Отклонить</button>
                </form>
                <span class="formattedDate">${formattedDate}</span>
                </div>
            </div>
        </div>
    </div>
   <div class="block_message">
   <div class="error-message" style="display: none;"></div>
   </div>
</div>
`; // ваш HTML код

                            $('.right_block').append(html);
                            originalMeterValues.push(readingData);
                        });
                        console.log(originalMeterValues);
                    },
                    error: function (error) {
                        $('.right_block').text('Что-то пошло не так, повторите попытку позже');
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

                        sendAjaxRequestBlockKw(idBlockValue);
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
                        sendAjaxRequestBlockKw(idBlockValue);
                    }
                }
            });

            // Функция поиска объекта по idReading
            function findObjectByIdReading(idReading, fio, formattedDate) {
                return $.grep(originalMeterValues, function (item) {
                    return item.idReading == idReading && item.fio == fio && item.formattedDate == formattedDate;
                })[0]; // Возвращает первый найденный объект или undefined, если ничего не найдено
            }
            function messageBlock(text, form) {
                // Отображаем уведомление
                form.closest('.block_applications').find('.error-message').html(text).slideDown(500);
                // Задержка перед скрытием уведомления (например, 3 секунды)
                setTimeout(function () {
                    form.closest('.block_applications').find('.error-message').slideUp(500);
                }, 4000);
            }
            $(document).on('submit', '.form_input, .form_delete', function (e) {
                e.preventDefault();
                $('.button_green, .button_red').prop("disabled", true);

                var form = $(this); // Получаем текущую форму

                var idMessage = form.find('input[name="idMessage"]').val();

                var numberMeter = form.closest('.text_right_span').find('input[name="inputNumbers"]').val();
                var fio = form.closest('.block_applications').find('.fio').text();
                var formattedDate = form.closest('.text_right_span').find('.formattedDate').text();
                var idReading = form.closest('.block_applications').attr('data-value-id');
                // Дополнительная проверка на тип переменной idMessage
                if (idMessage !== "0" && idMessage !== "1") {
                    $('.button_green, .button_red').prop("disabled", false);
                    messageBlock("Не удалось отправить запрос", form)
                    return;
                }

                // Проверка, содержит ли строка только цифры и может начинаться с "+" или "-"
                var isValidNumber = /^(?!0)\d{1,15}$/;

                if (!isValidNumber.test(numberMeter)) {
                    messageBlock("В показателях содержутся недопустимые символы", form)
                    $('.button_green, .button_red').prop("disabled", false);
                    return;
                }
                // Вызываем функцию для проверки наличия формы с такими же параметрами
                if (checkDuplicateForm(idMessage, fio, formattedDate, idReading, form)) {
                    // Выводим сообщение об ошибке
                    messageBlock("Не удалось отправить запрос", form)
                    $('.button_green, .button_red').prop("disabled", false);
                    return;
                }

                function checkDuplicateForm(idMessage, fio, formattedDate, idReading, currentForm) {
                    var isDuplicate = false;

                    var formClass = (idMessage == 0) ? '.form_input' : (idMessage == 1) ? '.form_delete' : '';

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
                        $('.button_green, .button_red').prop("disabled", false);
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
                        $('.button_green, .button_red').prop("disabled", false);
                    }
                } else {
                    messageBlock("Не удалось отправить запрос", form)
                    $('.button_green, .button_red').prop("disabled", false);
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
                        $('.button_green, .button_red').prop("disabled", true);
                        // Блокируем кнопки
                        if (blockMessages == 'error') {
                            messageBlock("Произошла ошибка на сервере. Повторите попытку позже", form)
                            // Откладываем разблокировку кнопок после 4 секунд
                            setTimeout(function () {
                                // Разблокировка кнопок
                                $('.button_green, .button_red').prop("disabled", false);
                            }, 4000);
                        }
                        setTimeout(function () {
                            // Разблокировка кнопок
                            $('.button_green, .button_red').prop("disabled", false);
                        }, 500);
                        $(`.block_applications[data-value-id="${blockMessages}"]`).fadeOut(500, function () {
                            $(this).remove();
                        });
                    },
                    error: function (error) {
                        $('.right_block').empty();
                        $('.right_block').text('Что-то пошло не так, повторите попытку позже');
                        $('.right_block').css({
                            fontSize: '25px',
                        });
                    }
                });
            });
            lightbox.option({
                'resizeDuration': 100,
                'wrapAround': true,
                'fadeDuration': 300,
                'imageFadeDuration': 300,
            })
        });
    </script>
@endsection
