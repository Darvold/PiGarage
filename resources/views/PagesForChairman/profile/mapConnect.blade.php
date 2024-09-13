<script type="text/javascript">
ymaps.ready(init);
let myMap, myCollection, currentPlacemark, myPlacemark, newZoom;
    let updateTimeout; // Переменная для хранения идентификатора таймаута
let currentBounds; // Переменная для хранения текущих границ карты
let currentZoom; // Переменная для хранения текущего уровня зума
let latitude = null;
let longitude = null;
let coords = null;
let savedCoords = null;
let savedCity = '';
let savedAddress = '';
let id_coop = null;
let savedIdCoop = null;
// Массив для хранения ключей существующих меток
let existingMarkers = [];
let geoObjects = [];
let CustomPlacemarkLayoutZoom14;
// Глобальный объект для хранения меток по их координатам
let geoObjectsMap = {};
let previousBounds = null; // Сохранение предыдущих границ карты
let newMarkers = [];
let geoObjectsArray;
let appendRemoveMark = true;
function init() {

    // Если в базе данных есть данные о метках, то создаем карту и добавляем метки
    // Создаем шаблон макета метки для зума >= 14
    CustomPlacemarkUserCoop = ymaps.templateLayoutFactory.createClass(
        '<div class="custom-placemark-layout">' +
        '<img class="custom-icon" src="{{asset('icons/map/markerUser.svg')}}" alt="Marker"/>' +
        '<div class="custom-caption-user-coop">' +
        '<div class="custom-caption-text">Моя_метка</div>' +
        '</div>',
    );
    CustomPlacemarkUser = ymaps.templateLayoutFactory.createClass(
        '<div class="custom-placemark-layout-user">' +
        '<img class="custom-icon" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>',
    );


    myMap = new ymaps.Map("map", {
        center: [56.24260923707676, 62.56439128155105],
        zoom: 2,
        controls: ['zoomControl', 'fullscreenControl'/*, 'geolocationControl',*/ /*'searchControl'*/],
        searchControlProvider: 'yandex#search'
    }, {
        searchControlProvider: 'yandex#search'
    });
    // Создаем пользовательский элемент управления
    customControl = new ymaps.control.Button({
        data: {
            content: `
            <div id="map-controls" class="map-controls">
            <label class="map_controls_label_left">
            <input type="checkbox" id="user-marker-toggle" class="checked_button" checked>Мое местоположение
            <img class="custom-icon-control" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>
            </label>
            <label class="map_controls_label_right">
            <input type="checkbox" id="markers-toggle" class="checked_button" checked>Метки
            <img class="custom-icon-control-coop" src="{{asset('icons/map/GroupMarker.png')}}" alt="Marker"/>
            </label>
            <label class="map_controls_label_inf">
            <span id="loading"></span>
            </label>
            </div>`,

        },
        options: {
            maxWidth: [250, 400],
            float: 'none',
            position: {
                top: 10,
                left: 10,
            },
        },
    });

    // Добавляем пользовательский элемент управления на карту
    myMap.controls.add(customControl);
    /*   <label class="map_controls_label_inf">
<span class="region_inf"></span>
<span>&nbsp;|&nbsp;</span>
<span class="city_inf"></span>
</label> //Это html нужен если подключён автоопределение региона и города, сам код закоментирован ниже
*/

// Обработчик события изменения границ видимой области карты
    myMap.events.add('boundschange', function (event) {

        // Определяем регион и город по координатам центра видимой области карты
        let mapCenter = myMap.getCenter();
        // Получаем новый зум из события или текущий зум карты
        newZoom = event.get('newZoom') || myMap.getZoom();
        // Получаем границы видимой области карты
        let newBounds = myMap.getBounds();
        $('#loading').text('Загрузка...');
        // Проверяем, если границы карты или зум изменились
        if (currentBounds !== newBounds || currentZoom !== newZoom) {
            currentBounds = newBounds;
            currentZoom = newZoom;

            // Если таймаут уже запущен, очищаем его
            if (updateTimeout) {
                clearTimeout(updateTimeout);
            }

            // Устанавливаем новый таймаут для загрузки данных
            if (appendRemoveMark == true) {
                updateTimeout = setTimeout(function () {
                    if (newZoom >= 10 && appendRemoveMark == true) {
                        updateMarkers(newBounds, newZoom);
                        $('#loading').text('');
                    } else {
                        $('#loading').text('Приблизьте карту, что бы появились метки');
                        clusterer.removeAll();
                    }
                }, 1000); // Задержка в 1 секунду
            } else {
                $('#loading').text('Метки отключены');
            }
        }

        // Обработчик события изменения чекбокса "Показать мое местоположение"
        $('#user-marker-toggle').on('change', function () {
            if ($(this).is(':checked')) {
                showUserMarker();
            } else {
                hideUserMarker();
            }
        });
// Обработчик события изменения чекбокса "Показать метки"
        $('#markers-toggle').on('change', function (newZoom) {
            if ($(this).is(':checked')) {
                showAllMarkers(newZoom);
            } else {
                hideAllMarkers();
            }
        });
        ymaps.geocode(mapCenter).then(function (res) {
            let firstGeoObject = res.geoObjects.get(0);
            if (firstGeoObject) {
                let addressDetails = firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.AddressDetails');
                let region = '';
                let city = '';

                if (addressDetails && addressDetails.Country) {
                    let administrativeArea = addressDetails.Country.AdministrativeArea;
                    if (administrativeArea) {
                        region = administrativeArea.AdministrativeAreaName || '';
                        if (administrativeArea.Locality) {
                            city = administrativeArea.Locality.LocalityName || '';
                        } else if (administrativeArea.SubAdministrativeArea && administrativeArea.SubAdministrativeArea.Locality) {
                            city = administrativeArea.SubAdministrativeArea.Locality.LocalityName || '';
                        }
                    }
                }

                // Обновляем информацию о регионе и городе
                $('.region_inf').text(`Регион: ${region}`);
                $('.city_inf').text(`Город: ${city}`);
            }
        });
    });
    // Создаем коллекцию для хранения меток
    myCollection = new ymaps.GeoObjectCollection();


    // Создайте кастомный макет кластера
    const customClusterLayout = ymaps.templateLayoutFactory.createClass(
        '<div class="custom-cluster-icon">' +
        '<img class="cluster-icon-img" src="{{asset('icons/map/claster.png')}}"/>' +
        '<span class="claster-text">$[properties.geoObjects.length]</span>' +
        '</div>'
    );

// Создайте кластеризатор с кастомным макетом
    const clusterer = new ymaps.Clusterer({
        clusterIconLayout: customClusterLayout, // Назначьте кастомный макет кластера
        clusterIconShape: {
            type: 'Rectangle', // Макет кластера - прямоугольник, чтобы задать размеры
            coordinates: [[-25, -25], [25, 25]] // Размер прямоугольника (в пикселях)
        },
        groupByCoordinates: false,
        clusterDisableClickZoom: true,
        clusterHideIconOnBalloonOpen: false,
        geoObjectHideIconOnBalloonOpen: false
    });

    // Добавляем пользовательский элемент управления на карту
    myMap.controls.add(customControl);

// Функция для скрытия всех меток

    function hideAllMarkers() {
        clusterer.removeAll();
        appendRemoveMark = false;
    }

// Функция для показа всех меток
    function showAllMarkers(newZoom) {
        clusterer.add(newMarkers);
        showAndHideCaptionText(newZoom);
        setTimeout(() => {
            newMarkers.forEach(marker => {
                $('.custom-placemark-layout').addClass('visible');
            });
        }, 100);
        appendRemoveMark = true;
    }

    function showAndHideCaptionText(newZoom) {
        if (newZoom >= 14) {
            setTimeout(function () {
                $('.custom-caption-text').css('display', 'block');
            }, 150);
        } else {
            setTimeout(function () {
                $('.custom-caption-text').css('display', 'none');
            }, 150);
        }
    }

    // Функция для показа метки пользователя
    function showUserMarker() {
        if (!currentPlacemarkUser) return;
        currentPlacemarkUser.options.set('visible', true);
    }

    // Функция для скрытия метки пользователя
    function hideUserMarker() {
        if (!currentPlacemarkUser) return;
        currentPlacemarkUser.options.set('visible', false);
    }
function onPlacemarkClick(e) {
        // Получение id_coop из data-атрибута метки
        id_coop = e.get('target').properties.get('id_coop');
        savedIdCoop = id_coop;
        return savedIdCoop;
    }

// Функция для обновления меток на карте
    function updateMarkers(bounds, newZoom) {
        // Проверка на минимальное изменение границ карты
        if (previousBounds && areBoundsSimilar(previousBounds, bounds)) {
            return; // Если границы не изменились существенно, не выполняем запрос
        }

        // Сохраняем текущие границы как предыдущие
        previousBounds = bounds;
        $.ajax({
            url: '{{ route('ChairmanSelectCoopsAJAX.index') }}',
            method: 'GET',
            data: {
                _token: '{{ csrf_token() }}',
                bounds: bounds
            },
            success: function (response) {
                let cooperatives = response.cooperatives;
                let applicationStatus = response.applicationStatus;
                if (cooperatives.length > 0) {
                    cooperatives.forEach(function (cooperative) {
                        let latitude = cooperative.latitude;
                        let longitude = cooperative.longitude;
                        let iconCaption = cooperative.name || '';
                        let trimmedCaption = iconCaption.length > 20 ? iconCaption.substring(0, 20) + '...' : iconCaption;
                        let key = `${latitude}_${longitude}`; // Создаем уникальный ключ на основе координат

                        // Проверяем, если метка уже существует в массиве ключей
                        if (!existingMarkers.includes(key)) {
                            // Создаем шаблон макета метки
                            let CustomPlacemarkLayoutZoom14 = ymaps.templateLayoutFactory.createClass(
                                `<div class="custom-placemark-layout-coop">
                            <img class="custom-icon" src="{{ asset('icons/map/GroupMarker.png') }}" alt="Marker"/>
                            <div class="custom-caption">
                            <div class="custom-caption-text">
                            ${trimmedCaption}
                            </div>
                            </div>
                            </div>`
                            );

                            // Генерация контента балуна на клиенте
                            let balloonContent = `
                            <div class="Text_balun_user_inf">
                            <p>Председатель: ${cooperative.fio}</p>
                            <p>Количество гаражных блоков: ${cooperative.amount_garage_block_count}</p>
                            ${getApplicationStatusContent(applicationStatus, cooperative.user_id, cooperative.id_coop)}
                            </div>`;

                            // Создаем метку
                            let myPlacemark = new ymaps.Placemark([latitude, longitude], {
                                id_coop: cooperative.id_coop,
                                balloonContentHeader: cooperative.name,
                                balloonContentBody: balloonContent,
                                iconCaption: iconCaption
                            }, {
                                iconLayout: CustomPlacemarkLayoutZoom14,
                                iconImageHref: "{{ asset('icons/map/GroupMarker.png') }}",
                                iconImageSize: [35, 35],
                                iconImageOffset: [-17, -17],
                                iconShape: {
                                    type: 'Circle',
                                    coordinates: [0, -5],
                                    radius: 19
                                }
                            });
                            myPlacemark.events.add('click', onPlacemarkClick);

                            // Добавляем метку в коллекцию, глобальный объект и массив ключей
                            myCollection.add(myPlacemark);
                            geoObjectsMap[key] = myPlacemark;
                            existingMarkers.push(key); // Добавляем ключ в массив существующих меток
                            newMarkers.push(myPlacemark); // Сохраняем новую метку в массиве

                        }
                    });

                    // Очистка старых меток
                    clusterer.removeAll();

                    // Обновляем кластеризатор только с новыми метками
                    clusterer.add(newMarkers);

                    // Добавляем кластеризатор на карту
                    myMap.geoObjects.add(clusterer);
                    setTimeout(() => {
                        newMarkers.forEach(marker => {
                            $('.custom-placemark-layout-coop').addClass('visible');
                        });
                    }, 100);

                    $('#loading').text('');
                    showAndHideCaptionText(newZoom);
                } else {
                    console.log('Нет данных о кооперативах.');
                }
            },
            error: function (error) {
                $('#loading').text('Ошибка!');
                {{--console.log('Ошибка:', error);--}}
            }
        });
    }

// Функция для генерации контента заявки на основе данных
    function getApplicationStatusContent(statuses, userId, id_coop) {
        let foundApplication = false;
        let content = '';
        if ({{Auth::id()}} == userId) {
            content = `<div class="Text_balun_user_inf">
            <span>Мой кооператив</span>
            </div>`
            foundApplication = true;
        }
        if (statuses.length > 0) {
            statuses.forEach(function (status) {
                if ({{Auth::id()}} && status.id_coop == id_coop && status.status == 'pending') {
                    content = `
                    <form method="POST" id="requestForm" action="{{ route('JoinTheCoopOrCansel.store') }}" class="Button_send">
                    @csrf
                    <input type="hidden" name="id_coop" value="${id_coop}"/>
                    <input type="hidden" name="id_message" value="2"/>
                    <button type="submit" class="button_send_app" style="border: 2px solid #E61818FF">Отменить заявку</button>
                    </form>`;
                    foundApplication = true;
                } else if ({{Auth::id()}} && status.id_coop == id_coop && status.status == 'accepted') {
                   content = '<span>Вы приняты!</span>';
                   foundApplication = true;
               } else if ({{Auth::id()}} && status.id_coop == id_coop && status.status == 'rejected') {
                   content = '<span>Отклонено председателем</span>';
                   foundApplication = true;
               }

           });
        }
    // Если заявка не найдена, показываем форму для отправки новой заявки
        if (!foundApplication) {
        content = `
        <form method="POST" id="requestForm" action="{{ route('JoinTheCoopOrCansel.store') }}" class="Button_send">
        @csrf
        <input type="hidden" name="id_coop" value="${id_coop}"/>
            <input type="hidden" name="id_message" value="1"/>
            <button type="submit" class="button_send_app">Отправить заявку</button>
        </form>`;
    }
    return content;
}


// Функция для сравнения текущих и предыдущих границ карты
    function areBoundsSimilar(bounds1, bounds2, tolerance = 0.009) {
        return Math.abs(bounds1[0][0] - bounds2[0][0]) < tolerance &&
            Math.abs(bounds1[0][1] - bounds2[0][1]) < tolerance &&
            Math.abs(bounds1[1][0] - bounds2[1][0]) < tolerance &&
            Math.abs(bounds1[1][1] - bounds2[1][1]) < tolerance;
    }


    // Получаем местоположение пользователя
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            let userCoords = [position.coords.latitude, position.coords.longitude];
            addUserMarker(userCoords);
        });
    }
    function addUserMarker(coords) {
        currentPlacemarkUser = new ymaps.Placemark(coords, {
            balloonContentBody: ``,
            balloonContentFooter: "Моё местоположение",
        }, {

            iconLayout: CustomPlacemarkUser,
            // Указываем путь к изображению и размеры иконки
            iconImageHref: "{{asset('icons/map/marker_AI.png')}}",
            iconImageSize: [35, 35],
            iconImageOffset: [-17, -17],

            iconShape: {
                type: 'Circle', // Используем тип фигуры - круг
                coordinates: [0, -5], // Центр окружности (используем смещение для коррекции)
                radius: 19 // Радиус окружности (в пикселях) - можно задать другой размер
            },
        });

        myCollection.add(currentPlacemarkUser);
        myMap.geoObjects.add(currentPlacemarkUser);
        myMap.setCenter(coords, 14); // Задайте начальный зум 14 для метки пользователя
    }
}
    $(document).on('click', '.button_send_app', async function (e) {
        e.preventDefault(); // Предотвращаем отправку формы
        let idMess = $(this).closest('.Button_send').find('[name="id_message"]').val();
        let isChecked = $('#c1-13').prop('checked');
        // Обновление input в текущей форме
        $(this).closest('#requestForm').find('input[name="id_coop"]').val(savedIdCoop);
        if (idMess === '1') {
            // Определяем селекторы для классов, id и name
            const selectors = [
                '.main_right_block',
                '.head_main_right_block',
                '.delete_garage',
                '#block_input_1',
                'input[name="number_meter"]',
                'input[name="number_garage"]',
                'input[name="number_block"]'
                ];

            // Функция для проверки существования элемента
            function elementExists(selector) {
                return $(selector).length > 0;
            }

            // Проверяем каждый селектор
            let allExist = true;
            for (let i = 0; i < selectors.length; i++) {
                if (!elementExists(selectors[i])) {
                    allExist = false;
                    console.error('Element not found:', selectors[i]);
                    break;
                }
            }
            // Если не все элементы найдены, выводим ошибку
            if (!allExist) {
                $('.error_msg span').remove();
                $('.error_msg').append('<span>Что-то пошло не так, попробуйте перезагрузить страницу</span>');

                // Скроллим к элементу .error_msg
                $('html, body').animate({
                    scrollTop: $('.error_msg').offset().top - 300
                }, 500, function () {
                    // Добавляем класс для изменения CSS на 1 секунду
                    let spanElement = $('.error_msg span');
                    spanElement.addClass('highlight');

                    // Убираем класс с плавной анимацией через 1 секунду
                    setTimeout(function () {
                        spanElement.removeClass('highlight');
                    }, 1000);
                });

                return false;
            }

            let garages = [];
            let hasEmptyFields = false;

            $('.block_input').each(function (index, element) {
                let garageNumber = $(element).closest('.main_right_block').find('.garage-btn').data('garage');
                let meterNumber = $(element).find('input[name="number_meter"]').val();
                let garageNum = $(element).find('input[name="number_garage"]').val();
                let blockNumber = $(element).find('input[name="number_block"]').val();
                if (!meterNumber || !garageNum || !blockNumber) {
                    hasEmptyFields = true;
                    return false; // Прерываем each
                }
                let garageData = {
                    'number_meter': meterNumber,
                    'number_garage': garageNum,
                    'number_block': blockNumber
                };

                garages.push(garageData);
            });
            if (isChecked) {
                hasEmptyFields = false;
            }
            if (hasEmptyFields) {
                $('.error_msg').empty();
                $('.error_msg').append('<span>Необходимо ввести все данные гаража/гаражей</span>');
                // Скроллим к элементу .error_msg
                $('html, body').animate({
                    scrollTop: $('.error_msg').offset().top - 300
                }, 500, function () {
                    // Добавляем класс для изменения CSS на 1 секунду
                    let spanElement = $('.error_msg span');
                    spanElement.addClass('highlight');
                    $('.block_input').each(function () {
                        var isEmpty = false;

                        // Проверка всех инпутов внутри блока
                        $(this).find('input').each(function () {
                            if ($(this).val() === '') {
                                $(this).addClass('error_msg_input');
                                isEmpty = true; // Устанавливаем флаг, если инпут пустой
                            }
                        });

                        // Если есть пустой инпут, подсвечиваем соответствующую кнопку
                        if (isEmpty) {
                            var garageId = $(this).data('garage'); // Предполагаем, что у блока также есть data-garage
                            $('.garage-btn[data-garage="' + garageId + '"]').addClass('garage-btn-emty');
                        }
                    });
                    setTimeout(function () {
                        spanElement.removeClass('highlight');
                        $('.block_input input').removeClass('error_msg_input');
                        $('.garage-btn').removeClass('garage-btn-emty');
                    }, 1700);
                });
                return false; // Останавливаем внешнюю функцию-обработчик
            }

               // Добавляем скрытое поле в форму
            let hiddenFieldCheck = $('<input>', {
                type: 'hidden',
                name: 'notGarage',
                value: isChecked
            });
            $('#requestForm').find('[name="notGarage"]').remove();
            $('#requestForm').append(hiddenFieldCheck);


            let jsonData = JSON.stringify(garages);
            let hiddenField = $('<input>', {
                type: 'hidden',
                name: 'garageData',
                value: jsonData
            });
            $('#requestForm').find('[name="garageData"]').remove();
            $('#requestForm').append(hiddenField);

        }
        // Отправляем форму
        $('#requestForm').submit();
    });

        let delayTimer; // Переменная для хранения таймера задержки
        $('.head_search_coop button').click(function () {
            nameCoopSelect = $('.head_search_coop_input').find('input[name="name_coop"]').val();

            if (nameCoopSelect.length >= 3 || (selectedRegion && selectedCity)) {
                $('.head_search_coop button').prop("disabled", true);
                // Очищаем блок с результатами и добавляем сообщение "Выполняем запрос..."
                $('.block_name_result_coop').empty();
                $('.block_name_result_coop').prepend(`<span class="span_text">Выполняем запрос...</span>`);
                offset = 0;
                // Если уже есть установленный таймер, очищаем его
                clearTimeout(delayTimer);

                // Устанавливаем новый таймер задержки
                delayTimer = setTimeout(function () {
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
                            blockMessages = response.blockMessages;
                            if (noEmty) {
                                $('.block_name_result_coop').empty();
                            }
                            noEmty = true;
                            if (Array.isArray(blockMessages) && blockMessages.length > 0) {
                                $('#load-more').css({
                                    'display': 'block',
                                });
                                for (var i = 0; i < blockMessages.length; i++) {
                                    $('.block_name_result_coop .span_text').remove();
                                    html = `<div class="inf_coop">
                                    <span>Название: ${blockMessages[i].name}</span>
                                    <span>Председатель: ${blockMessages[i].fio}</span>
                                    <span>Местонахождение: ${blockMessages[i].address}</span>
                                    <button data-id-point="${blockMessages[i].latitude},${blockMessages[i].longitude}">Показать на карте</button>
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
                                $('#load-more').hide();
                                $('.block_name_result_coop').prepend(`<span class="span_text">Ошибка, похоже нет кооперативов с заданными параметрами. Введённое название: ${nameCoopSelect}</span>`);
                            }
                        },
                        error: function (error) {
                            const textError = error.responseJSON.error;
                            $('.block_name_result_coop .span_text').remove();
                            $('.block_name_result_coop').prepend(`<span class="span_text">${textError} ${countError ? '(' + countError + ')' : ''}</span>`);
                            countError += 1;
                        }
                    });
                }, 2000);
                $('.head_search_coop button').prop("disabled", false);
            } else {
                $('.block_name_result_coop .span_text').remove();
                $('.block_name_result_coop').prepend('<span class="span_text">Название слишком короткое</span>');
            }
        });
$(document).on('click', 'button[data-id-point]', function () {
    var coordinates = $(this).data('id-point').split(',');
    var lat = parseFloat(coordinates[0]);
    var lon = parseFloat(coordinates[1]);
    if (myMap) {
        myMap.setCenter([lat, lon], 15);
    } else {
        console.error('Карта не инициализирована.');
    }
});
        </script>
