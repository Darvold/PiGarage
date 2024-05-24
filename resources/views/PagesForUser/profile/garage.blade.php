@extends('layouts.profileUser', ['ProfileGarageStyles' => ['garage.css', 'scroll.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileUser.index', ['id' => Auth::id()])}}">Мой профиль</a>
        <a href="{{route('UserGarage.index', ['id' => Auth::id()])}}" class="active">Мои гаражи</a>
        <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Мои кооперативы</a>
    </div>
    <div class="main_body_garage">
        <div class="scroll" id="scrollContainer">
            <div style="display: flex; width: 100%;">
                <div style="white-space: nowrap; display: flex; min-width: 1350px; border: 1px solid black;">
                    @if($garages->count() == 0)
                    Добавьте гараж!!!
                    @else
                    @foreach($garages as $garage)
                    <div class="block_garage">
                        <div class="block_image_garage">
                            <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="">
                        </div>
                        <div class="block_information_garage">
                            <div class="block_text_garage">
                                <span>Номер счётчика: {{$garage->number_meter}}</span>
                                <span>Город: </span>
                                <span>Кооператив: </span>
                                <div class="button_garage">
                                    <a href="{{route('myUserGaragePivotTable.index', ['id' => Auth::id(), 'idGarage' => $garage->id_garage])}}">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
        <a href="{{route('UserCreateGarage.index', ['id' => Auth::id()])}}" class="add_new_garage">Добавить новый гараж</a>
    </div>
</div>
@endsection
