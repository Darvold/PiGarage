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
                // Очистка существующих данных
                $('#table_one tbody').empty();

                // Заполнение таблицы данными
                $.each(response.data, function(month, data) {
                    $('#table_one tbody').append(`
                        <tr>
                            <td>${month}</td>
                            <td>${data.meter_readings}</td>
                            <td>${data.kw_garages}</td>
                            <td>${data.losses}</td>
                            <td>${data.kw_with_losses}</td>
                            <td>${data.tariff}</td>
                            <td>${data.total_cost}</td>
                            <td>${data.paid}</td>
                            <td>${data.debt}</td>
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
