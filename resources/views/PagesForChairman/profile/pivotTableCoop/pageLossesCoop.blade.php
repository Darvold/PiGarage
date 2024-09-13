@extends('layouts.mainChairman', ['ProfileCoopLosses' => ['losses.css', 'scroll.css']])

@section('profile')
    @php
        $months = ['01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь', '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'];
        $numberMonths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        $groupedByMonth = [];

        // Перебираем массив данных и группируем по месяцам
        foreach ($valueRates as $valueRate) {
            $month = explode('-', $valueRate['date_indication'])[1];
            if (!isset($groupedByMonth[$month])) {
                $groupedByMonth[$month] = [];
            }
            $groupedByMonth[$month][] = $valueRate;
        }
    @endphp
    <div class="main_block_coop">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
            <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
            <a href="{{route('ChairmanMyCoopRate.index', ['idCoop' => $idCoop])}}" class="active">Тарифы</a>
        </div>
        <div class="main_body">
            <div class="right_block">
                <div class="text_inf">
                    <span>Общая таблица потери электричества для гаражного кооператива</span>
                </div>
                <div class="table_kw_change">
                    <div class="head_block_table">
                        <div class="button_form_ajax">
                            <div class="block_1">
                                <span>Таблица потерь</span>
                                <button class="last_year"><</button>
                                <span class="year">{{$year}}</span>
                                <button class="next_year">></button>
                            </div>
                        </div>
                    </div>
                    <div class="main_block_table">
                        @foreach ($numberMonths as $i => $monthNumber)
                            @php
                                $paddedMonth = str_pad($monthNumber, 2, '0', STR_PAD_LEFT);
                                $monthName = $months[$monthNumber];
                                $formId = 'form_month_' . $i;
                            @endphp
                            <form method="post"
                                  action="{{ route('ChairmanMyCoopLossesPost.store', ['idCoop' => $idCoop]) }}"
                                  class="form_month" id="{{ $formId }}">
                                @csrf
                                <input type="hidden" class="id_year" name="id_year" value="{{$year}}">
                                <input type="hidden" class="id_month_number" name="id_month_number"
                                       value="{{ $monthNumber }}">
                                <div class="month">
                                    <span>{{ $monthName }}</span>
                                </div>
                                <div>
                                    @if (isset($groupedByMonth[$monthNumber]))
                                        @foreach ($groupedByMonth[$monthNumber] as $valueRate)
                                            <input type="tel" pattern="[1-9][0-9]?" name="losses_value"
                                                   id="tariff_value"
                                                   maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')"
                                                   value="{{ $valueRate['losses_value'] }}">
                                        @endforeach
                                    @else
                                        <input type="tel" pattern="[1-9][0-9]?" name="losses_value" id="losses_value"
                                               maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')"
                                               value="">
                                    @endif
                                    <span>%</span>
                                    <button type="submit" class="monthButton">Сохранить</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('.next_year').prop("disabled", true);
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий
            let currentTime = new Date().getTime();
            let idBlockValue;
            let currentYear;
            let blockNumber;
            let currentYearInput = {{$year}};
            // Создаем массивы месяцев и их номеров
            let months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            let numberMonths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

            function sendAjaxRequestBlockKw(currentYearInput) {
                $.ajax({
                    url: '{{ route('ChairmanMyCoopLosses.index', ['idCoop' => $idCoop]) }}',
                    type: "GET",
                    data: {
                        numberYear: currentYearInput,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('.main_block_table').empty();
                        // Блокируем новую кнопку
                        let currentButton = $(`.button_block[data-id-button='${idBlockValue}']`);
                        currentButton.prop("disabled", true);
                        currentButton.css({
                            border: '3px solid #1369c0',
                        });
                        // Сохраняем текущую кнопку как предыдущую
                        previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                        lastClickTime = new Date().getTime();
                        clickCount++;

                        let blockMessages = response.blockMessages;
                        let year = response.year;
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
                            var monthName = months[i];
                            var formId = 'form_month_' + i;

                            // Создаем общую структуру формы с CSRF-токеном
                            var html = `<form method="post" action="{{ route('ChairmanMyCoopLossesPost.store', ['idCoop' => $idCoop]) }}" class="form_month" id="${formId}">
                            @csrf
                            <input type="hidden" class="id_year" name="id_year" value="${year}">
                            <input type="hidden" class="id_month_number" name="id_month_number" value="${monthNumber}">
                            <div class="month">
                                <span>${monthName}</span>
                            </div>
                            <div>`;
                            if (groupedByMonth[monthNumber]) {
                                // Если для текущего месяца есть данные, то выводим их
                                var monthData = groupedByMonth[monthNumber];
                                monthData.forEach(function (blockMessage) {
                                    html += `
                                    <input type="tel" pattern="[0-9]*[.]?[0-9]+" name="losses_value" id="tariff_value"
                                    maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')" value="${blockMessage.losses_value}">`;
                                });
                            } else {
                                // Если для текущего месяца нет данных, то создаем пустую форму
                                html += `
                                <input type="tel" pattern="[0-9]*[.]?[0-9]+" name="losses_value" id="tariff_value"
                                maxlength="2" oninput="this.value=this.value.replace(/\D/g,'')" value="">`;
                            }

                            html += `<span>%</span>
                                <button type="submit" class="monthButton">Сохранить</button>
                                </div></form>`;
                            $('.main_block_table').append(html);
                        }
                    },
                    error: function (error) {
                        $('.main_block_table').text('Что-то пошло не так, повторите попытку позже').css({
                            'font-size': '22px',
                        });
                    }
                });
            }

            $('.last_year, .next_year').click(function (e) {

                let currentTime = new Date().getTime();
                let timeDifference = currentTime - lastClickTime;
                currentYear = parseInt($('.year').text());
                $('.main_block_table').empty();
                $('.main_block_table').html('Подождите, запрос выполняется...').css({
                    'font-size': '22px',
                });
                if (timeDifference < 2000 && clickCount > 5) {
                    $('.main_block_table').empty();
                    // Отображаем сообщение об ошибке
                    $(".main_block_table").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

                    // Блокируем кнопки на 3 секунды
                    $('.last_year, .next_year').prop("disabled", true);

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
                        sendAjaxRequestBlockKw(currentYearInput);
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
                        sendAjaxRequestBlockKw(currentYearInput);
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
                        sendAjaxRequestBlockKw(currentYearInput, blockNumber);
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
        });
    </script>
@endsection
