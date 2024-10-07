@extends('layouts.mainChairman', ['ProfileCoopBlocksStyles' => ['pageBlocks.css', 'scroll_blocks.css', 'coopMeters.css']])

@section('profile')
<script>
 $(document).ready(function () {
    $(document).on('change', '#fileInput', function () {
        let fileUrl = URL.createObjectURL(this.files[0]);
        let imgTag = `<a href="${fileUrl}" class="input_img_user" data-lightbox="image" data-title="Загруженное изображение">
        <img src="${fileUrl}" alt="Загруженное изображение" id="lightbox-image">
        <img src="{{asset('icons/user/check_mark.svg')}}" class="check" alt="">
        </a>`;
        $('.img').html(imgTag);
    });
});
</script>
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
        <a href="{{route('ChairmanMyCoopBlocks.index', ['idCoop' => $idCoop])}}" class="active">Гаражные ряды</a>
    </div>
    <div class="main_body">
        <div class="left_block">
            <div class="flex_container">
                <span class="span_text">Мои гаражные ряды:</span>
                <div class="main_garages_block">
                    @foreach($blocks as $block)
                    <form method="GET" action="" class="myBlock" data-id-block="{{$block->id_block}}">
                        <input type="hidden" name="block_number" value="{{$block->number_block}}">
                        <button type="submit" class="button_block" data-id-button="{{$block->id_block}}">
                            <span class="span_number_block">Гаражный ряд №{{$block->number_block}}</span>
                            <span class="span_users_block"> <img src="{{asset('icons/user/users.svg')}}" alt="Пользователи" class="users" id="users">: {{ $block->garage_count }}</span>
                        </button>
                    </form>
                    @endforeach
                    <form method="post" action="{{route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop])}}">
                        @csrf
                        <input type="hidden" name="id_coop" value="{{$idCoop}}">
                        <input type="hidden" name="id_message" value="1">
                        <button class="button_new_block" type="submit">
                            <span class="new_span_number_block">Добавить новый ряд</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="right_block">
            <form method="post" action="{{route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop])}}" class="default_form_kw">
                @csrf
                <span>По умолчанию:</span>
                <input type="hidden" name="id_message" value="2">
                <input type="hidden" name="id_block" value="">
                <input type="tel" pattern="[1-9][0-9]?" name="default_kw" maxlength="2"
                oninput="this.value=this.value.replace(/\D/g,'')" value="">
                <span>%</span>
                <button type="submit" id="button_form_default_kw">Сохранить</button>
            </form>
            <div class="table_kw_change">
                <div class="head_block_table">
                    <div class="button_form_ajax">
                        <div class="block_1">
                            <span>Таблица потерь</span>
                            <button class="last_year"><</button>
                            <span class="year">{{session('id_year') ?? date('Y')}}</span>
                            <button class="next_year">></button>
                        </div>
                        <div class="block_2">
                            <span class="number_block_output">Гаражный ряд: №</span>
                        </div>
                    </div>
                </div>
                <div class="main_block_table">
                    <div class="table_kw_mouth">

                    </div>
                    <span class="request_fail"></span>
                </div>
            </div>
            <span class="text_month_3" style="font-size: 40px;"></span>
        </div>
        <div class="block_meter_one">
            <div class="meter_indication">
                <div class="head_indication">
                    <div class="block_1">
                        <span>Показания</span>
                        <button class="last_year"><</button>
                        <span class="year">{{session('id_year') ?? date('Y')}}</span>
                        <button class="next_year">></button>
                    </div>
                    <div class="block_2">
                        @php
                        $months = [
                            "01" => "Январь", "02" => "Февраль", "03" => "Март", "04" => "Апрель", "05" => "Май", "06" => "Июнь",
                            "07" => "Июль", "08" => "Август", "09" => "Сентябрь", "10" => "Октябрь", "11" => "Ноябрь", "12" => "Декабрь"
                        ];
                        $currentMonth = session('id_month_number') ?? date('m');
                        @endphp

                        <select name="months" id="months">
                            @foreach ($months as $key => $month)
                            <option value="{{ $key }}" @if($key == $currentMonth) selected @endif>
                                {{ $month }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="block_4">
                    <span class="number_block_output">Гаражный ряд: №</span>
                </div>
                <div class="body_indication">
                    <span class="request_fail_img"></span>
                </div>
            </div>
            <div class="flex_column">
                <div style="margin-bottom: 10px;">
                    <form method="post" action="{{ route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop]) }}" class="create_new_meter">
                        <div class="input_block">
                            @csrf
                            <div>
                                <span>Номер счётчика: </span>
                                <input type="tel" name="meter_number" required/>
                            </div>
                            <div>
                                <span>Прошлые показания кВт: </span>
                                <input type="tel" name="initially_kw" placeholder="Если нет, то впишите 0" required/>
                            </div>
                            <input type="hidden" name="idPost" value="0">
                            <input type="hidden" name="id_message" value="5">
                            <input type="hidden" class="id_block_create_meter" name="id_block" value="">
                            <input type="hidden" class="id_year_create_meter" name="id_year" value="{{session('id_month_number') ?? date('Y')}}">
                            <input type="hidden" class="id_month_number" name="id_month_number" value="{{session('id_month_number') ?? date('m')}}">
                        </div>
                        <div class="button_block">
                            <button class="submit">Добавить новый счётчик</button>
                        </div>
                    </form>
                </div>
                <div class="span_absolute">
                    <span class="my_meters">Мои счётчики</span>
                </div>
                <div class="list_number_meter">

                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        let garageBlock = $('.main_garages_block');
        if (garageBlock.css('height') !== '690px') {
            garageBlock.css('border', 'none');
        } else {
            garageBlock.css('border', '1px solid black');
            garageBlock.css('overflow-y', 'auto');
        }
    });
</script>
<script>
    $(document).ready(function () {
        let isSubmitMyBlock = false;
    let previousButton; // Переменная для хранения предыдущей кнопки
    let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
    let clickCount = 0; // Счетчик нажатий
    let currentTime = new Date().getTime();
    let timeDifference = currentTime - lastClickTime;
    let idBlockValue;
    let currentYear;
    let blockNumber;
    let currentYearInput = {{session('id_year') ?? date('Y')}};
    let selectedMonth = $('#months').val() ?? {{$currentMonth}};
                // Обработчик события изменения селекта
    $('#months').on('change', function() {
        selectedMonth = $(this).val();
        sendAjaxRequestBlockImg (currentYearInput, idBlockValue, selectedMonth)
    });
    var currentMonth = new Date().getMonth(); // Получаем текущий месяц (от 0 до 11)
            // Создаем массивы месяцев и их номеров
    let months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    let numberMonths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    $('.id_year_create_meter').val(currentYearInput);
    function sendAjaxRequestBlockKw(currentYearInput, idBlockValue) {
        $('.id_year_create_meter').val(currentYearInput);
        $.ajax({
            url: '{{ route('ChairmanMyCoopBlocks.index', ['idCoop' => $idCoop]) }}',
            type: "GET",
            data: {
                id_block: idBlockValue,
                id_coop: {{$idCoop}},
                numberYear: currentYearInput,
                id_message: 1,
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                $('.list_number_meter, .table_kw_mouth, .request_fail').empty();
                        // Блокируем новую кнопку
                let currentButton = $(`.button_block[data-id-button='${idBlockValue}']`);
                currentButton.prop("disabled", true);
                currentButton.css({
                    border: '3px solid #1369c0',
                });
                                                // Извлекаем текст из кнопки
                let buttonText = currentButton.find('.span_number_block').text(); // Получаем текст внутри span

                        // Используем регулярное выражение для извлечения числа
                let numberMatch = buttonText.match(/№(\d+)/);
                        // Сохраняем текущую кнопку как предыдущую
                previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                lastClickTime = new Date().getTime();
                clickCount++;

                $('.number_block_output').text('Гаражный ряд: №' + numberMatch[1]);

                let blockMessages = response.blockMessages;
                let default_kw = response.defaultKW;
                var groupedByMonth = {};

                if (blockMessages !== null && blockMessages.length > 0) {
                            // Перебираем массив и группируем по месяцам
                    blockMessages.forEach(function (blockMessage) {
                        var month = blockMessage.date_indication.split('-')[1];
                        if (!groupedByMonth[month]) {
                            groupedByMonth[month] = [];
                        }
                        groupedByMonth[month].push(blockMessage);
                    });
                }

                // Перебираем все месяцы
                for (var i = 0; i < months.length; i++) {
                    var monthNumber = numberMonths[i];
                    var paddedMonth = monthNumber.padStart(2, '0');
                    var monthName = months[i];
                    var formId = 'form_month_' + i;

                    // Создаем общую структуру формы с CSRF-токеном
                    var html = `<form method="post" action="{{ route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop]) }}" class="form_month" id="${formId}">
                    @csrf
                    <input type="hidden" class="id_message" name="id_message" value="3">
                    <input type="hidden" class="id_block" name="id_block" value="${idBlockValue}">
                    <input type="hidden" class="id_year" name="id_year" value="${currentYearInput}">
                    <input type="hidden" class="id_month_number" name="id_month_number" value="${monthNumber}">`;

                    if (groupedByMonth[monthNumber]) {
                        // Если для текущего месяца есть данные, то выводим их
                        var monthData = groupedByMonth[monthNumber];
                        monthData.forEach(function (blockMessage) {
                            html += `<div class="month">
                                <span>${monthName}</span>
                            </div>
                            <div>
                                <input type="tel" pattern="[1-9][0-9]?" name="lossesNumber" id="lossesNumber"
                                maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')" value="${blockMessage.percent_kw}">
                                <span>%</span>
                                <button type="submit" class="monthButton">Сохранить</button>
                                </div>`;
                            });
                    } else {
                        // Если для текущего месяца нет данных, то создаем пустую форму
                        html += `<div class="month">
                        <span>${monthName}</span>
                        </div>
                        <div>
                        <input type="tel" pattern="[1-9][0-9]?" name="lossesNumber" id="lossesNumber"
                        maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')" value="${default_kw.default_kw ? default_kw.default_kw : ''}">
                        <span>%</span>
                        <button type="submit" class="monthButton">Сохранить</button>
                        </div>`;
                    }

                    html += `</form>`;
                    $('.table_kw_mouth').append(html);
                }
                for (var i = 0; i < response.messageMeters.length; i++) {
                    var meter = response.messageMeters[i]; // Получаем текущий элемент
                    var htmlMeters = `<div class="block_meter">
                                <form method="post" action="{{ route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop]) }}" class="form_update">
                            <div class="head_meter">
                                <div class="meter_number_span">
                                    <div>
                                        <span>Номер счётчика: </span>
                                        <input type="tel" name="meter_number" value="${meter.meter_number}" />
                                    </div>
                                    <div>
                                        <span>Прошлые показания: </span>
                                        <input type="tel" name="initially_kw" value="${meter.initially_kw}" />
                                    </div>
                                </div>
                                <span class="status">Статус: <span class="${meter.active ? 'true' : 'false'}">${meter.active ? 'активный' : 'неактивный'}</span></span>
                                <span>Дата создания: ${meter.creation_date}</span>
                            </div>
                            <div class="body_meter">
                                    @csrf
                                    <input type="hidden" class="id_block" name="id_block" value="${idBlockValue}">
                                    <input type="hidden" class="id_year" name="id_year" value="${currentYearInput}">
                                    <input type="hidden" name="number_id" value="${meter.id_meter_number}">
                                    <input type="hidden" name="idPost" value="1">
                                    <input type="hidden" class="id_month_number" name="id_month_number" value="${selectedMonth}">
                                    <input type="hidden" name="id_message" value="5">
                                    <button type="submit">Изменить данные</button>
                            </div>
                                </form>
                        </div>`;
                    $('.list_number_meter').append(htmlMeters);
                }

            },
            error: function (error) {
                $('.list_number_meter, .table_kw_mouth, .request_fail').empty();
                $('.request_fail').text('Что-то пошло не так, повторите попытку позже');
            }
        });
}
$('.last_year, .next_year').click(function (e) {
    if (isSubmitMyBlock) {
        let currentTime = new Date().getTime();
        let timeDifference = currentTime - lastClickTime;
        currentYear = parseInt($('.year').text());
        $('.table_kw_mouth, .request_fail').empty();
        $('.table_kw_mouth, .list_number_meter, .list_number_meter').html('Подождите, запрос выполняется...');
        if (timeDifference < 2000 && clickCount > 5) {
                        // Отображаем сообщение об ошибке
            $(".request_fail").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

                        // Блокируем кнопки на 3 секунды
            $('.last_year, .next_year, .button_block').prop("disabled", true);

            setTimeout(function () {
                $('.next_year, .button_block').prop("disabled", false);
                if (currentYearInput === 2023) {
                    $('.last_year').prop("disabled", true);
                } else {
                    $('.last_year').prop("disabled", false);
                }
                sendAjaxRequestBlockKw(currentYearInput, idBlockValue)
                sendAjaxRequestBlockImg(currentYearInput, idBlockValue, selectedMonth)
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
                sendAjaxRequestBlockKw(currentYearInput, blockNumber);
                sendAjaxRequestBlockImg(currentYearInput, idBlockValue, selectedMonth)
                if (currentYearInput === 2023) {
                    $('.last_year').prop("disabled", true);
                } else {
                    $('.last_year').prop("disabled", false);
                }
            } else {
                            // Если нажата кнопка "next_year"
                currentYear = currentYearInput + 1;
                currentYearInput = currentYearInput + 1;
                sendAjaxRequestBlockKw(currentYearInput, idBlockValue)
                sendAjaxRequestBlockImg(currentYearInput, idBlockValue, selectedMonth)
                $('.last_year').prop("disabled", false);
            }

            return $('.year').text(currentYear) + currentYearInput;

        }
    } else {
        $(".request_fail").text("Сначала выберите гаражный ряд");
    }
});


$('.myBlock').click(function (e) {
    e.preventDefault();
    $('.button_block').css({
        border: '',
        color: 'black'
    });
    $('.button_block').prop("disabled", false);
    idBlockValue = $(this).data('id-block');
    blockNumber = $(this).find('input[name="block_number"]').val();
    $('.id_block_create_meter').val(blockNumber);
    isSubmitMyBlock = true;
    $('.table_kw_mouth, .request_fail, .list_number_meter').empty();
    $('.table_kw_mouth, .list_number_meter').html('Подождите, запрос выполняется...');
    let currentTime = new Date().getTime();
    let timeDifference = currentTime - lastClickTime;
                // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
    if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
        $(".request_fail").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                    // Блокируем кнопки на 3 секунды
        $('.last_year, .next_year, .button_block').prop("disabled", true);

        setTimeout(function () {
            $('.last_year, .next_year, .button_block').prop("disabled", false);
            if (currentYearInput === 2023) {
                $('.last_year').prop("disabled", true);
            } else {
                $('.last_year').prop("disabled", false);
            }
            sendAjaxRequestBlockKw(currentYearInput, idBlockValue)
            sendAjaxRequestBlockImg(currentYearInput, idBlockValue, selectedMonth)
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
            sendAjaxRequestBlockKw(currentYearInput, idBlockValue)
            sendAjaxRequestBlockImg (currentYearInput, idBlockValue, selectedMonth)
        }
    }
});
$('#button_form_default_kw').click(async function (e) {
    e.preventDefault(); // Предотвращаем отправку формы
    if (!idBlockValue) {
        $(".request_fail").text("Сначала выберите гаражный ряд");
        return false
    }
    $('input[name="id_message"]').val(2);
    $('input[name="id_block"]').val(idBlockValue);
    $('#button_form_default_kw').closest('form').submit();

});
$(document).on('click', '.monthButton', async function (e) {
    e.preventDefault(); // Предотвращаем отправку формы
    var form = $(this).closest('.form_month');
    if (!idBlockValue || !currentYearInput) {
        return false;
    }
    form.find('.id_message').val(3);
    form.find('.id_block').val(idBlockValue);
    form.find('.id_year').val(currentYearInput);
    form.submit();
});

            //     $(document).on('click', '#monthButton', async function (e) {
            /*$('#monthButton').click(async function (e) {
e.preventDefault(); // Предотвращаем отправку формы
if (!idBlockValue) {
    return false;
}
$('#id_message').val(3);
$('#id_block').val(idBlockValue);
            // Используем метод submit() для отправки формы
$('#monthButton').closest('form').submit();
        });*/
@if(session('id_block') != null)
idBlockValue = {{ session('id_block') }};
currentYearInput = {{ session('id_year') ?? 'new Date().getFullYear()' }};
selectedMonth = {{ session('id_month_number') ?? 'new Date().getMonth() + 1' }};
isSubmitMyBlock = true;
if (currentYearInput === 2023) {
    $('.last_year').prop("disabled", true);
} 
sendAjaxRequestBlockKw(currentYearInput, idBlockValue);
sendAjaxRequestBlockImg (currentYearInput, idBlockValue, selectedMonth);
@endif
function sendAjaxRequestBlockImg (currentYearInput, idBlockValue, selectedMonth) {
    $('.body_indication').empty();
    $('.body_indication').append(`<span style="font-size: 23px">Загрузка...</span>`);
    $.ajax({
        url: '{{ route('ChairmanMyCoopBlocks.index', ['idCoop' => $idCoop]) }}',
        type: "GET",
        data: {
            id_block: idBlockValue,
            id_coop: {{$idCoop}},
            numberYear: currentYearInput,
            month: selectedMonth,
            id_message: 2,
            _token: '{{ csrf_token() }}',
        },
        success: function (response) {
            $('.body_indication').empty();
                // Блокируем новую кнопку
            let currentButton = $(`.button_block[data-id-button='${idBlockValue}']`);
                                                // Извлекаем текст из кнопки
                        let buttonText = currentButton.find('.span_number_block').text(); // Получаем текст внутри span
                        // Используем регулярное выражение для извлечения числа
                        let numberMatch = buttonText.match(/№(\d+)/);
                        // Сохраняем текущую кнопку как предыдущую
                        previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                        lastClickTime = new Date().getTime();
                        clickCount++;
                        $('.number_block_output').text('Гаражный ряд: №' + numberMatch[1]);
                        let kw_meter = response.kw_meter;
                        let imgHtml = response.imgHtml;
                        let html_img = `<div class="block_indication">
                        <form method="post" enctype="multipart/form-data" action="{{ route('ChairmanMyCoopNewBlocks.store', ['idCoop' => $idCoop]) }}" id="meter_indication_img">
                        @csrf
                        <div class="head_block_indication">
                        <span>Показания кВт: </span>
                        <input type="tel" name="kw_meter" value="${kw_meter ?? ''}" required/>
                        <input type="hidden" class="id_message" name="id_message" value="4">
                        <input type="hidden" class="id_block" name="id_block" value="${idBlockValue}">
                        <input type="hidden" class="id_year" name="id_year" value="${currentYearInput}">
                        <input type="hidden" class="id_month_number" name="id_month_number" value="${selectedMonth}">
                        </div>
                        <div class="img_block">
                        <input accept="image/png, image/jpg, image/jpeg" type="file" id="fileInput" class="file_img" name="img_meter" inputmode="none">
                        <div class="flex_img">
                        <button type="submit">Сохранить</button>
                        <div class="img">
                        ${imgHtml ?? ''}
                        </div>
                        </div>
                        </div>
                        </form>
                        <span style="font-size: 19px">При указании значения 0, <br> показания удаляются</span>
                        </div>`;
                        $('.body_indication').append(html_img);
                    },
                    error: function (error) {
                        let errorText = error.responseJSON.error
                        $('.request_fail_img').html(`<span style="font-size: 23px">${errorText}</span>`);
                    }
                });
}
});
</script>
@endsection
