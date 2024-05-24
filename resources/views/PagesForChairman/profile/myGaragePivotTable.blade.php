@extends('layouts.profileChairman', ['myGaragePivotTableStyles' => ['myGaragePivotTable.css', 'scroll_coop.css']])
@section('profile')
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanGarage.index')}}">Мои гаражи</a>
        <a href="{{route('myGaragePivotTable.index', ['idGarage' => $garage->id_garage])}}">Номер гаража: {{$garage->number_garage}}</a>
    </div>
    <div class="main_head_tools">
        <div class="block_information_link">
            <a>История замена счётчиков</a>
            <a>Все участники кооператива</a>
        </div>
        <div class="right_block_preferences">
            <a class="Button_right">Настройки гаража</a>
        </div>
    </div>
    <div class="main_body">
        <div class="block_information_link">
            <a>Сменить номер счётчик</a>
            <a href="{{ route('ChairmanSubmitIndicationsGarage.index', ['idGarage' => $garage->id_garage, 'numberGarage' => $garage->number_garage])}}">Передать показания председателю</a>
        </div>
        <div class="year_buttons">
            <button id="prev-year-btn" class="Button_correct_left">&lt;</button>
            <span id="current-year" class="Number_year">2023</span>
            <button id="next-year-btn" class="Button_correct_right">&gt;</button>
        </div>

        @include('PagesForChairman.profile.pivotTableGarage.tableOne')
        {{-- <div class="center">
        </div>
        <div class="flex_two_table">
            <div class="table_two">
                @include('PagesForChairman.profile.pivotTableCoop.tableTwo')
            </div>
            <div class="block_information_two_table">
                <span>Инструменты заполнения сводной таблицы сборов</span>
                <button>Установить общий сбор</button>
                <button>Установить сбор на хозяйственные нужны</button>
                <button>Установить сбор на ремонт электросети</button>
            </div>
        </div>--}}
    </div>
</div>
@endsection
