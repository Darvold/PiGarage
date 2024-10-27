@extends('layouts.mainChairman', ['ProfileCoopStyles' => ['myCoop.css', 'scroll_coop.css']])

@section('profile')
    <div class="main_block_coop">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}" class="active">Мои кооперативы</a>
            <a href="{{route('ChairmanCreateMyCoop.index')}}">Создать кооператив</a>
        </div>
        <div class="main_body">
            @forelse($myCoops as $myCoop)
                <div class="flex_block_content">
                    <div class="block_coop">
                        <div class="image_block_user">
                            <img src="{{asset('image/user/defaultGarageMinSize.jpg')}}" alt="кооператив">
                            <!-- <div class="Button_update_img">
                                <a class="Update_img" href="">Изменить фото</a>
                            </div> -->
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
                                    <div class="Button_table_position">
                                        <div style="height: 30px;">
                                            <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $myCoop -> id_coop])}}"
                                               class="Button_table">Сводная таблица</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="Float_right_coop_information">
                        <span class="Additional_information">Дополнительная <br>информация</span>
                        <a href="{{route('ChairmanMyCoopParticipants.index', ['idCoop' => $myCoop -> id_coop])}}">Участники
                            ({{ $myCoop->user_and_coop_count }})</a>
                        <!-- <a href="{{route('ChairmanMyCoopBlocks.index', ['idCoop' => $myCoop -> id_coop])}}">Гаражные блоки</a> -->
                        <a href="{{route('MessagesMeters.index', ['idCoop' => $myCoop -> id_coop])}}">Показания
                            участников</a>
                    </div>
                </div>
            @empty
                <div class="else_block_array">У вас нет ни одного кооператива!</div>
            @endforelse

        </div>
    </div>
@endsection
