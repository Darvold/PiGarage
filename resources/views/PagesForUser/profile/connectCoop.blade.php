@extends('layouts.profileUser', ['ProfileConnectCoopStyles' => ['connectCoop.css', 'map_marker.css']])

@section('profile')
<script src="https://api-maps.yandex.ru/2.1?apikey=aa1a4f1a-2153-49ec-bbc8-db91be38ff21&load=package.full&lang=ru_RU"></script>
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileUser.index', ['id' => Auth::id()])}}">Мой профиль</a>
        <a href="{{route('UserGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
        <a href="{{route('ChairmanCreateMyCoop.index', ['id' => Auth::id()])}}" class="active">Присоединение к кооперативу</a>
    </div>
    <div class="main_body">
        <div class="center_width_container">
            <div class="left_block">
              <div class="information_text">
                <span style="font-size: 32px; font-weight: bold; ">Присоединение к кооперативу - быстро и легко!</span>
                <ol style="margin-top: 10px; margin-left: 15px;">
                  <li>Найдите свой город на карте.</li>
                  <li>Кликните на метку своего кооператива.</li>
                  <li>Нажмите "Отправить заявку" в окне информации.</li>
                  <li>Ожидайте, пока председатель примет вашу заявку.</li>
                  <span style="font-size: 24px;"> Примечание: если вашего кооператива нет на карте, значит председатель ещё не создал его на сайте.</span>
              </ol>
          </div>
          <div class="block_input">
            <div class="left_block_label_and_input">
          </div>
{{--          <div class="button_marker">
            <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Вернуться назад</a>
        </div>--}}
            <div class="error_text_map" style="display: none;"><span  id="coordinates"></span>
            </div>
            <div class="error_text_map_script"><span  id="coordinates2"></span>
            </div>
    </div>
</div>
<div class="right_block">
    <div class="map" id="map" style="width: 100%; height: 100%;"></div>
</div>
<script src="{{ asset('js/map/map.js') }}"></script>
</div>
@include('PagesForUser.profile.map')
</div>
</div>
@endsection
