@extends('layouts.mainChairman', ['ProfileCoopStyles' => ['myCoop.css', 'scroll_coop.css']])

@section('profile')
    <div class="main_block_coop">
        <div class="main_head">
            <a href="{{route('ProfileChairman.index', ['id' => Auth::id()])}}">Мой профиль</a>
            <a href="{{route('ChairmanGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
            <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}" class="active">Мои кооперативы</a>
        </div>
        <div class="main_body">
            @forelse($myCoops as $myCoop)
                <div class="flex_block_content">
                    <div class="block_coop">
                        <div class="image_block_user">
                            <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="кооператив">
                            <div class="Button_update_img">
                                <a class="Update_img" href="">Изменить фото</a>
                            </div>
                        </div>
                        <div class="information_coop">
                            <div class="Float_right">
                                <div class="Position_coop_information">
                                    <span class="Coop_Name">Кооператив: {{$myCoop -> name}}</span>
                                    <div class="Coop_information">
                                        <span>Общий счётчик №: {{$myCoop -> number_meter}}</span>
                                        @if (strpos($myCoop->city, ',') !== false)
                                            <span>Город: {{ substr($myCoop->city, 0, strpos($myCoop->city, ',')) }}</span>
                                        @else
                                            <span>Город: {{ $myCoop->city }}</span>
                                        @endif
                                        <span>Дата регистрации кооператива: {{ \Carbon\Carbon::parse($myCoop->data_create)->format('d.m.Y') }}</span>
                                    </div>
                                    <div style="height: 30px;">
                                        <a href="{{route('ChairmanMyCoopPivotTable.index', ['id' => Auth::id(), 'Name' => $myCoop -> name, 'idCoop' => $myCoop -> id_coop])}}"
                                           class="Button_table">Сводная таблица</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="Float_right_coop_information">
                        <span class="Additional_information">Дополнительная <br>информация</span>
                        <a href="">Участники (26)</a>
                        <a href="">Настройки</a>
                        <a href="{{route('MessagesMeters.index', ['id' => Auth::id()])}}">Счётчики участников</a>
                        <a href="">Заявки на вступления (2)</a>
                    </div>
                </div>
            @empty
                <div class="else_block_array">У вас нет ни одного кооператива!</div>
            @endforelse


            <div class="crate_coop">
                <a href="{{route('ChairmanCreateMyCoop.index', ['id' => Auth::id()])}}">Создать новый кооператив</a>
            </div>
        </div>
    </div>
@endsection
