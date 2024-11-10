@extends('layouts.mainChairman', ['ProfileCoopPivotTableStyles' => ['myCoopPivotTable.css', 'scroll_coop.css']])

@section('profile')
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index', ['idCoop' => $idCoop])}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}" class="active">{{$coopData->name}}</a>
    </div>
    <div class="main_head_tools">
        <div class="block_information_link">
            <a href="{{route('coopMeters.index', ['idCoop' => $coopData -> id_coop])}}">История/замена общего счётчика</a>
            <a href="{{route('ChairmanMyCoopParticipants.index', ['idCoop' => $coopData -> id_coop])}}">Все
            участники</a>
            <a href="{{route('MessagesMeters.index', ['idCoop' => $coopData -> id_coop])}}">Показания участников</a>
            <a href="{{route('ChairmanMyCoopBlocks.index', ['idCoop' => $idCoop])}}">Гаражные ряды/потери</a>
        </div>
        <button class="Button_right">Настройки кооператива</button>
    </div>
    <div class="main_body">
        <div class="block_information_link">
            <a href="{{route('ChairmanMyCoopRate.index', ['idCoop' => $idCoop])}}">Установить тариф</a>
            <a href="{{route('ChairmanMyCoopLosses.index', ['idCoop' => $idCoop])}}">Установить потери</a>
            <a href="{{route('ChairmanMyCoopPayment.index', ['idCoop' => $idCoop])}}">Внести оплату участников</a>
            <a href="{{route('ChairmanMyCoopGeneralCounter.index', ['idCoop' => $idCoop])}}">Показания общего счётчика</a>
        </div>
        <button id="load-data">Загрузить данные</button>
        <div class="year_buttons">
            <button id="prev-year-btn" class="Button_correct_left">&lt;</button>
            <span id="current-year" class="Number_year">2023</span>
            <button id="next-year-btn" class="Button_correct_right">&gt;</button>
        </div>

        @include('PagesForChairman.profile.pivotTableCoop.tableOne')
        <div class="center">
        </div>
        <div class="flex_two_table">
            <div class="table_two">
                @include('PagesForChairman.profile.pivotTableCoop.tableTwo')
            </div>
            <div class="block_information_two_table">
                <div class="block_information_link">
                    <a href="{{route('ChairmanMyCoopPaymentOther.index', ['idCoop' => $idCoop])}}">Внести/установить оплату сборов</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#load-data').on('click', function() {
        $.ajax({
            url: '{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}', // URL к методу контроллера
            method: 'GET',
            data: {
                   _token: '{{ csrf_token() }}',
            },
            success: function(response) {
                console.log(response.data);
                $('#table_one tbody').empty();

                // Объект с названиями месяцев на русском
                const monthsNames = {
                    '01': 'Январь',
                    '02': 'Февраль',
                    '03': 'Март',
                    '04': 'Апрель',
                    '05': 'Май',
                    '06': 'Июнь',
                    '07': 'Июль',
                    '08': 'Август',
                    '09': 'Сентябрь',
                    '10': 'Октябрь',
                    '11': 'Ноябрь',
                    '12': 'Декабрь'
                };

                // Массив с порядком месяцев
                const monthsOrder = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

                // Проходим по порядку месяцев
                monthsOrder.forEach(function(month) {
                    const data = response.data[month];
                    
                    // Если данные для месяца есть, выводим их; если нет, добавляем пустую строку или значение по умолчанию
                    $('#table_one tbody').append(`
                        <tr>
                            <td>${monthsNames[month]}</td>
                            <td>${data ? data.meter_readings : ''}</td>
                            <td>${data ? data.kw_garages : ''}</td>
                            <td>${data ? data.losses : ''}</td>
                            <td>${data ? data.kw_with_losses : ''}</td>
                            <td>${data ? data.tariff : ''}</td>
                            <td>${data ? data.total_cost : ''}</td>
                            <td>${data ? data.paid : ''}</td>
                            <td>${data ? data.debt : ''}</td>
                        </tr>
                    `);
                });
            },
            error: function(error) {
                let textError = error.responseJSON.error;
                console.error(textError);
            }
        });
    });
});
</script>
@endsection
