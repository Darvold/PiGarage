@extends('layouts.mainChairman', ['ProfileCoopPivotTableStyles' => ['myCoopPivotTable.css', 'scroll_coop.css']])

@section('profile')
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}" class="active">{{$coopData->name}}</a>
    </div>
    <div class="main_head_tools">
        <div class="block_information_link">
            <a href="{{route('coopMeters.index', ['idCoop' => $coopData -> id_coop])}}">История/замена счётчика</a>
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
        </div>
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
                    <a href="{{route('ChairmanMyCoopPaymentOther.index', ['idCoop' => $idCoop])}}">Внести оплату сборов</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
