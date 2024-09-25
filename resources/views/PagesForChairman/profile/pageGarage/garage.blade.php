@extends('layouts.mainChairman', ['ProfileGarageStyles' => ['garage.css', 'scroll.css']])
@section('profile')
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ChairmanGarage.index')}}" class="active">Мои гаражи</a>
        </div>
        <div class="main_body_garage">
            <div class="scroll" id="scrollContainer">
                <div style="display: flex; width: 100%;">
                    <div class="garage_scroll_x" style="">
                            @forelse($garages as $garage)
                                    <?php
                                    $coopName = $garage->coop_name;
                                    if (mb_strlen($coopName) > 20) {
                                        $coopName = mb_substr($coopName, 0, 20) . '...';
                                    }
                                    ?>
                                <div class="block_garage">
                                    <div class="block_image_garage">
                                        <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="">
                                    </div>
                                    <div class="block_information_garage">
                                        <div class="block_text_garage">
                                            <span>Номер гаража: {{$garage->number_garage}}</span>
                                            <span>Номер ряда: {{$garage->number_block}}</span>
                                            @if (strpos($garage->coop_city, ',') !== false)
                                                    <?php $cityParts = explode(',', $garage->coop_city); ?>
                                                <span>Город: {{ trim($cityParts[0]) }}</span>
                                            @else
                                                <span>Город: {{$garage->coop_city}}</span>
                                            @endif
                                            <span>Кооператив: {{$coopName}}</span>
                                            <div class="button_garage">
                                                <a href="{{route('myGaragePivotTable.index', ['idGarage' => $garage->id_garage])}}">Подробнее</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="not_garages">
                                    <style type="text/css">
                                        .garage_scroll_x {
                                            border: none;
                                        }
                                    </style>
                                    <span>Добавьте новый гараж</span>
                                </div>
                            @endforelse
                    </div>
                </div>
            </div>
            <a href="{{route('ChairmanCreateGarage.index')}}" class="add_new_garage">Добавить
                новый гараж</a>
        </div>
    </div>
@endsection
