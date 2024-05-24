@extends('layouts.profileUser', ['myGaragePivotTableStyles' => ['myGaragePivotTable.css', 'scroll_coop.css']])
@section('profile')
    <div class="main_block_coop">
        <div class="main_head">
            <a href="{{route('ProfileUser.index', ['id' => Auth::id()])}}">Мой профиль</a>
            <a href="{{route('UserGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
            <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}" class="active">Номер счётчика: {{$garage->number_meter}}</a>
        </div>
        <div class="main_head_tools">
            <div class="buttons_information">
                <button>История замена счётчиков</button>
                <button>Все участники кооператива</button>
            </div>
            <button class="Button_right">Настройки гаража</button>
        </div>
        <div class="main_body">
            <div class="block_information">
                <button>Сменить номер счётчик</button>
                <button>Передать показания председателю</button>
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
