@extends('layouts.mainChairman', ['ProfileCreateCoopStyles' => ['createCoop.css', 'map_marker.css']])

@section('profile')
    <script
            src="https://api-maps.yandex.ru/2.1?apikey=aa1a4f1a-2153-49ec-bbc8-db91be38ff21&load=package.full&lang=ru_RU"></script>
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
            <a href="{{route('ChairmanCreateMyCoop.index')}}" class="active">Создать новый кооператив</a>
        </div>
        <div class="main_body">
            <div class="center_width_container">
                <div class="left_block">
                    <div class="information_text">
                        <span>Создать кооператив – быстро и легко!</span>
                        <ol style="margin-top: 10px; margin-left: 15px;">
                            <li>Напишите название кооператива, его общий счётчик и количество гаражных блоков.</li>
                            <li>Установите расположение кооператива на карте России, кликнув на "Добавить метку".</li>
                            <li>Подайте заявку, нажав на кнопку "Отправить заявку".</li>
                            <li>Дождитесь, пока администратор примет вашу заявку.</li>
                        </ol>
                    </div>
                    <div class="block_input">
                        <form id="myForm" method="POST" action="{{ route('ChairmanSendingDataCreateCoop.store')}}">
                            @csrf
                            <div class="left_block_label_and_input">
                                <label>Название кооператива:</label>
                                <input type="text" name="name" title="Введите минимум 3 символа" id="markerName"
                                       value="{{ old('name') }}" required>
                                <br>
                                <label>Номер общего счётчика:</label>
                                <input type="number" pattern="[0-9]{1,10}" title="Только цифры" name="number_meter"
                                       oninput="this.value=this.value.replace(/\D/g,'')"
                                       value="{{ old('number_meter') }}" required>
                                <br>
                                <label>Количество гаражных блоков (мин. 1 | макс. 10):</label>
                                <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage_blocks" maxlength="2" max="10" oninput="this.value=this.value.replace(/\D/g,'')"
                                       value="{{ old('number_garage_blocks') }}" required>

                                <input type="hidden" name="latitude" value="">
                                <input type="hidden" name="longitude" value="">

                                <input type="hidden" name="city" value="">
                                <input type="hidden" name="address" value="">
                            </div>
                            <div class="button_marker">
                                <a href="{{route('ChairmanMyCoop.index')}}">Вернуться назад</a>
                                <button class="add_marker" id="add-marker-btn">Добавить метку</button>
                                <button type="submit" class="save_marker" id="save-marker-btn">Отправить заявку</button>
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
            </div>
            @include('PagesForChairman.profile.js.mapJS')
        </div>
    </div>
    <script>
        document.getElementById('add-marker-btn').addEventListener('click', function (event) {
            event.preventDefault(); // Предотвращаем стандартное поведение кнопки
        });
    </script>

@endsection
