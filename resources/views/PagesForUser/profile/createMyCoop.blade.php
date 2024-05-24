@extends('layouts.profileChairman', ['ProfileCreateCoopStyles' => ['createCoop.css', 'map_marker.css']])

@section('profile')
<script
    src="https://api-maps.yandex.ru/2.1?apikey=aa1a4f1a-2153-49ec-bbc8-db91be38ff21&load=package.full&lang=ru_RU"></script>
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileChairman.index', ['id' => Auth::id()])}}">Мой профиль</a>
        <a href="{{route('ChairmanGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
        <a href="{{route('ChairmanCreateMyCoop.index', ['id' => Auth::id()])}}" class="active">Создание
            кооператива</a>
    </div>
    <div class="main_body">
        <div class="center_width_container">
            <div class="left_block">
                <div class="information_text">
                    <span style="font-size: 32px; font-weight: bold; ">Создание кооператива - быстро и легко!</span>
                    <ol style="margin-top: 10px; margin-left: 15px;">
                        <li>Напишите название кооператива и его общий счётчик.</li>
                        <li>Установите расположение кооператива на карте, кликнув на "Добавить метку".</li>
                        <li>Подайте заявку, нажав на кнопку "Отправить заявку".</li>
                        <li>Дождитесь, пока администратор примет вашу заявку.</li>
                    </ol>
                </div>
                <div class="block_input">
                    <form id="myForm" method="POST" action="{{ route('ChairmanSendingDataCreateCoop.store', ['id' => Auth::id()]) }}">
                        @csrf
                        <div class="left_block_label_and_input">
                            <input type="hidden" name="user_id" value="{{Auth::id()}}">

                            <label>Название кооператива:</label>
                            <input type="text" name="name" id="markerName" required>
                            <br>
                            <label>Номер общего счётчика:</label>
                            <input type="text" name="number_meter" id="numberMeter" required>

                            <input type="hidden" name="latitude" value="">
                            <input type="hidden" name="longitude" value="">

                            <input type="hidden" name="city" value="">
                            <input type="hidden" name="address" value="">

                        </div>
                        <div class="button_marker">
                            <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Вернуться назад</a>
                            <button class="add_marker" id="add-marker-btn">Добавить метку</button>
                            <button type="submit" class="save_marker" id="save-marker-btn" >Отправить заявку</button>
                        </div>
                    </form>

                    <div class="error_text_map" style="display: none;"><span id="coordinates"></span>
                    </div>
                    <div class="error_text_map_script"><span id="coordinates2"></span>
                    </div>
                </div>
            </div>
            <div class="right_block">
                <div class="map" id="map" style="width: 100%; height: 100%;"></div>
            </div>
            <script src="{{ asset('js/map/map.js') }}"></script>
        </div>
        @include('PagesForChairman.profile.map')
    </div>
</div>
<script>
    document.getElementById('add-marker-btn').addEventListener('click', function(event) {
        event.preventDefault(); // Предотвращаем стандартное поведение кнопки
    });
</script>
<script>
    // Используйте событие input для отслеживания ввода
    $('#numberMeter').on('input', function () {
        // Оставляем только цифры
        $(this).val($(this).val().replace(/\D/g, ''));
    });
</script>
@endsection
