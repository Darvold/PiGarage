@extends('layouts.profileChairman', ['ProfileConnectCoopStyles' => ['connectCoop.css', 'map_marker.css', 'select2.min.css']])

@section('profile')
<script src="https://api-maps.yandex.ru/2.1?apikey=aa1a4f1a-2153-49ec-bbc8-db91be38ff21&load=package.full&lang=ru_RU"></script>
<script src="{{ asset('js/select/select2.min.js') }}"></script>
<script src="{{ asset('js/select/ru.js') }}"></script>
<script src="{{ asset('js/select/russian-cities.js') }}"></script>
<div class="main_block">
<div class="main_head">
    <a href="{{route('ChairmanConnectCoop.index', ['id' => Auth::id()])}}" class="active">Поиск кооперативов</a>
    <a href="{{route('MyApplicationToCoop.index', ['id' => Auth::id()])}}" class="">Мои заявки</a>
</div>
<div class="main_body">
    <div class="center_widthe_container">
        <div class="left_block">
            <div class="information_text">
                <span style="font-size: 27px; font-weight: bold; ">Присоединение к кооперативу - быстро и легко!</span>
                <ol style="margin-top: 10px; margin-left: 15px;">
                    <li>Введите данные своего гаража.</li>
                    <li>Найдите свой кооператив с помощью карты / поисковика.</li>
                    <li>Нажмите на метку своего кооператива.</li>
                    <li>Нажмите "Отправить заявку" в окне информации.</li>
                    <li>Ожидайте, пока председатель примет вашу заявку.</li>
                    <span style="font-size: 24px;"> Примечание: если вашего кооператива нет на карте, значит председатель ещё не создал его на сайте.</span>
                </ol>
            </div>
        </div>
        @php
            $garageData = session('garageData', []);
            $error = session('error');
            $counter = 1;
            $counterGarageBtn = 1;
        @endphp
        <div class="main_right_block">
            <div class="head_main_right_block">
                @if($garageData)
                    @foreach($garageData as $gData)
                        <button class="garage-btn" data-garage="{{$counterGarageBtn}}">Гараж
                            №{{$counterGarageBtn}}<span class="delete_garage"><img
                                        src="{{asset('icons/user/cross.svg')}}"></span></button>
                        @php
                            $counterGarageBtn++;
                        @endphp
                    @endforeach

                @else
                    <button class="garage-btn" data-garage="1">Гараж №1<span class="delete_garage"><img
                                    src="{{asset('icons/user/cross.svg')}}"></span></button>
                @endif

                <button id="addGarageBtn">Добавить</button>
            </div>
            @if($garageData)
                @foreach($garageData as $gData)
                    <div class="block_input active" id="block_input_{{ $counter }}">
                        <div class="right_block_input">
                            <label>Номер счётчика: </label>
                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_meter"
                                   required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                                   value="{{ $gData['number_meter'] }}">
                        </div>
                        <div class="right_block_input">
                            <label>Номер гаража:</label>
                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage"
                                   required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                                   value="{{ $gData['number_garage'] }}">
                        </div>
                        <div class="right_block_input">
                            <label>Номер блока:</label>
                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_block"
                                   required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                                   value="{{ $gData['number_block'] }}">
                        </div>
                    </div>
                    @php
                        $counter++;
                    @endphp
                @endforeach
                <div class="error_msg">
                    <span>{{ $error }}</span>
                </div>
            @else
                <div class="block_input active" id="block_input_1">
                    <div class="right_block_input">
                        <label>Номер счётчика: </label>
                        <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_meter"
                               required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                               value="">
                    </div>
                    <div class="right_block_input">
                        <label>Номер гаража:</label>
                        <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage"
                               required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                               value="">
                    </div>
                    <div class="right_block_input">
                        <label>Номер блока:</label>
                        <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_block"
                               required maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"
                               value="">
                    </div>
                </div>
                <div class="error_msg">

                </div>
            @endif
        </div>
    </div>
    <div class="flex_search_coop_and_map">
        <div class="search_coop_main">
            <div class="head_search_coop">
                <div class="head_search_coop_input">
                    <input type="text" id="fname" name="name_coop" pattern=".{3,}" placeholder="Название кооператива" required>
                    <button>Найти</button>
                </div>
                <div class="flex_region_and_city_select">
                    <div class="select_wrp">
                        <select class="js-select-region" name="region" placeholder="Выберите регион"
                                style="min-width: 230px;">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="select_wrp">
                        <select class="js-select-city" name="city" placeholder="Выберите город"
                                style="min-width: 230px;">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="result_all_coop">
                <div class="block_name_result_coop">
                    <span class="span_text">Введите название гаражного кооператива</span>
                </div>
                <div class="button_more_result">
                    <button id="load-more" style="display: none;">Показать ещё</button>
                </div>
            </div>
        </div>
        <div class="right_block">
            <div class="map" id="map" style="width: 100%; height: 100%;"></div>
        </div>
    </div>
    <script>
        let selectedRegion = null;
        let selectedCity = null;
        let offset = 0;
        let noEmty = true;
        let countError = 0;
        let nameCoopSelect;
        $(document).ready(function () {
            $('#load-more').on('click', function () {
                noEmty = false;
                ajaxSelectCoop();
            });

            $('.js-select-region').select2({
                placeholder: "Выберите регион",
                language: "ru"
            });

            $('.js-select-city').select2({
                placeholder: "Выберите город",
                language: "ru"
            });

            if (typeof cities !== 'undefined') {
                populateRegions(cities);
            } else {
                console.error('Данные о городах не загружены.');
            }


            // Функция для заполнения <select> регионами
            function populateRegions(data) {
                const selectRegion = $('.js-select-region');
                const uniqueSubjects = new Set();

                data.forEach(item => {
                    uniqueSubjects.add(item.subject);
                });

                uniqueSubjects.forEach(subject => {
                    const option = $('<option></option>').val(subject).text(subject);
                    selectRegion.append(option);
                });
            }

            // Функция для заполнения <select> городами
            function populateCities(region) {
                const selectCity = $('.js-select-city');
                selectCity.empty().append('<option value=""></option>'); // Очищаем предыдущие значения

                const citiesInRegion = cities.filter(item => item.subject === region);
                citiesInRegion.forEach(city => {
                    const option = $('<option></option>').val(city.name).text(city.name);
                    selectCity.append(option);
                });
            }

            // Обработчик события 'change' для выбора региона
            $('.js-select-region').on('change', function () {
                selectedRegion = $(this).val(); // Сохраняем выбранный регион
                populateCities(selectedRegion);
                selectedCity = null; // Сбрасываем выбор города при смене региона
                $('.js-select-city').val(null).trigger('change'); // Очищаем выбор города в интерфейсе
            });

            // Обработчик события 'change' для выбора города
            $('.js-select-city').on('change', function () {
                selectedCity = $(this).val(); // Сохраняем выбранный город
                ajaxSelectCoop();
            });

            function ajaxSelectCoop() {
                if (selectedRegion && selectedCity) {
                    if (noEmty) {
                    nameCoopSelect = $('.head_search_coop_input').find('input[name="name_coop"]').val();
                    }
                    $.ajax({
                        url: "{{route('ChairmanConnectCoop.index')}}",
                        method: "GET",
                        data: {
                            _token: "{{ csrf_token() }}",
                            idMessage: 1,
                            selectedRegion: selectedRegion,
                            selectedCity: selectedCity,
                            nameCoop: nameCoopSelect,
                            offset: offset,
                        },
                        success: function (response) {
                            let blockMessages = response.blockMessages;
                            if (noEmty) {
                                $('.block_name_result_coop').empty();
                            }
                            noEmty = true;
                            if (Array.isArray(blockMessages) && blockMessages.length > 0) {
                                $('.block_name_result_coop .span_text').remove();
                                $('#load-more').css({
                                    'display': 'block',
                                });
                                for (var i = 0; i < blockMessages.length; i++) {
                                    html = `<div class="inf_coop">
                            <span>Название: ${blockMessages[i].name}</span>
                            <span>Председатель: ${blockMessages[i].fio}</span>
                            <span>Местонахождение: ${blockMessages[i].address}</span>
                            <button data-id-point="${blockMessages[i].id_point}">Показать на карте</button>
                            </div>`;
                                    $('.block_name_result_coop').append(html);
                                }
                                offset += blockMessages.length;
                                countError = 0;
                                // Проверка, есть ли ещё записи
                                if (!response.hasMore) {
                                    $('#load-more').hide();
                                }
                            } else {
                                $('.block_name_result_coop .span_text').remove();
                                $('.block_name_result_coop').append(`<span class="span_text">Ошибка, похоже нет кооперативов с заданными параметрами</span>`);
                            }
                        },
                        error: function (error) {
                            const textError = error.responseJSON.error;
                            $('.block_name_result_coop .span_text').remove();
                            $('.block_name_result_coop').append(`<span class="span_text"${textError} ${countError ? '(' + countError + ')' : ''}</span>`);
                            countError += 1;
                        }
                    });
                }
            }
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.block_input').first().addClass('active');
            $('.block_input').not(':first').css('display', 'none');
            let lengthGarageBtn = $('.garage-btn');
            let garageCount = lengthGarageBtn.length;
            $(document).on('click', '.garage-btn', function (event) {
                const garageId = $(this).data('garage');

                const activeBlock = $('.block_input.active');
                // Если нажата кнопка, соответствующая уже активному блоку, ничего не делаем
                if (activeBlock.attr('id') === `block_input_${garageId}`) {
                    return;
                }
                activeBlock.removeClass('active').hide();
                // Показываем выбранный блок
                $(`#block_input_${garageId}`).addClass('active').show();
                // Изменяем стили кнопок
                $('.garage-btn').css({
                    'color': 'black',
                    'background-color': 'white',
                    'border': '3px solid rgb(19, 105, 192)'
                });
                $(this).css({
                    'color': 'white',
                    'background-color': 'rgb(19, 105, 192)',
                    'border': '3px solid rgb(19, 105, 192)'
                });
            });


            $(document).on('click', '#addGarageBtn', function (event) {
                garageCount++;
                // Hide the current active block
                $('.block_input.active').removeClass('active').hide();
                // Create a new block_input
                const newBlock = `
    <div class="block_input active" id="block_input_${garageCount}">
    <div class="right_block_input">
    <label>Номер счётчика: </label>
    <input type="number" name="number_meter" required inputmode="none">
    </div>
    <div class="right_block_input">
    <label>Номер гаража:</label>
    <input type="number" name="number_garage" required inputmode="none">
    </div>
    <div class="right_block_input">
    <label>Номер блока:</label>
    <input type="number" name="number_block" required inputmode="none">
    </div>
    </div>
    `;
                $(newBlock).insertBefore('.error_msg');

                // Create a new button for the new garage
                const newButton = `<button class="garage-btn" data-garage="${garageCount}">Гараж №${garageCount}<span class="delete_garage"><img src="{{asset('icons/user/cross.svg')}}"><span></button>`;
                $('#addGarageBtn').before(newButton);

                $('.garage-btn').css({
                    'color': 'black', // Цвет текста кнопок
                    'background-color': 'white', // Фон кнопок
                    'border': '3px solid rgb(19, 105, 192)' // Граница кнопок
                });
                $(`[data-garage="${garageCount}"]`).css({
                    'color': 'white',
                    'background-color': 'rgb(19, 105, 192)',
                    'border': '3px solid rgb(19, 105, 192)'
                });
                if (garageCount === 3) {
                    $('#addGarageBtn').hide();
                }
            });
            $(document).on('click', '.delete_garage', function (event) {
                event.stopPropagation();
                const garageButton = $(this).closest('.garage-btn');
                const garageId = garageButton.data('garage');

                $(`#block_input_${garageId}`).remove();
                garageButton.remove();
                garageCount--;
                // Пересчитать data-garage и id всех кнопок и блоков
                $('.garage-btn').each(function (index) {
                    $(this).data('garage', index + 1);
                    $(this).attr('data-garage', index + 1); // Обновить атрибут data-garage
                    const textNode = $(this).contents().filter(function () {
                        return this.nodeType === 3; // Узел текста
                    })[0];
                    // Обновляем текст узла
                    if (textNode) {
                        textNode.nodeValue = `Гараж №${index + 1}`;
                    }
                    if (garageCount !== 3) {
                        $('.head_main_right_block #addGarageBtn').show();
                    }
                });

                $('.block_input').each(function (index) {
                    $(this).attr('id', `block_input_${index + 1}`);
                });

                // Обновить переменную garageCount
                garageCount = $('.garage-btn').length;
            });
        });

    </script>
    @include('PagesForChairman.profile.mapConnect')
</div>
</div>
@endsection
