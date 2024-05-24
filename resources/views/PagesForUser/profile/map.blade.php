<script type="text/javascript">
    ymaps.ready(init);
    let myMap, myCollection, currentPlacemark;
    let myPlacemark;
    let latitude = null;
    let longitude = null;
    let coords = null;
    let savedCoords = null;
    let savedCity = '';
    let savedAddress = '';
    let CustomPlacemarkLayoutZoom14, CustomPlacemarkLayoutLessThanZoom14;

    function init() {
        <?php if (!empty($cooperatives)): ?>
        // Если в базе данных есть данные о метках, то создаем карту и добавляем метки
        // Создаем шаблон макета метки для зума >= 14
        CustomPlacemarkCoopUser = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/markerUser.svg')}}" alt="Marker"/>' +
            '<div class="custom-caption-user-coop">' +
            '<div class="custom-caption-text">Моя_метка</div>' +
            '</div>',
        );
        CustomPlacemarkUser = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>',
        );

        myMap = new ymaps.Map("map", {
            center: [{{ $cooperative->id_point }}],
            zoom: 13,
            controls: ['zoomControl', 'fullscreenControl'/*, 'geolocationControl',*/ /*'searchControl'*/],
            searchControlProvider: 'yandex#search'
        }, {
            searchControlProvider: 'yandex#search'
        });
        // Создаем пользовательский элемент управления
        customControl = new ymaps.control.Button({
            data: {
                content: '<div id="map-controls" class="map-controls">' +
                    '<label class="map_controls_label_left">' +
                    '<input type="checkbox" id="user-marker-toggle" class="checked_button" checked> Показать мое местоположение' + '<img class="custom-icon-control" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>' +
                    '</label>' +
                    '<label class="map_controls_label_right">' +
                    '<input type="checkbox" id="markers-toggle" class="checked_button" checked> Показать метки' + '<img class="custom-icon-control-coop" src="{{asset('icons/map/GroupMarker.png')}}" alt="Marker"/>' +
                    '</label>' +
                    '</div>',
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

        // Создаем коллекцию для хранения меток
        myCollection = new ymaps.GeoObjectCollection();
// Обработчик события изменения зума карты
        myMap.events.add('boundschange', function (event) {
            let newZoom = event.get('newZoom');
            // Обработчик события изменения чекбокса "Показать мое местоположение"
            $('#user-marker-toggle').on('change', function () {
                if ($(this).is(':checked')) {
                    showUserMarker();
                } else {
                    hideUserMarker();
                }
            });
// Обработчик события изменения чекбокса "Показать метки"
            $('#markers-toggle').on('change', function () {
                if ($(this).is(':checked')) {
                    showAllMarkers();
                } else {
                    hideAllMarkers();
                }
            });
            if (newZoom >= 14) {
                myCollection.options.set('iconLayout', CustomPlacemarkLayoutZoom14);
                $('.custom-caption-text').css('display', 'block');
            } else {
                myCollection.options.set('iconLayout', CustomPlacemarkLayoutLessThanZoom14);
                $('.custom-caption-text').css('display', 'none');
            }
        });
// Функция для скрытия всех меток
        function hideAllMarkers() {
            clusterer.removeAll();
        }

// Функция для показа всех меток
        function showAllMarkers() {
            clusterer.add(geoObjects);
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

        // Получаем местоположение пользователя
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                let userCoords = [position.coords.latitude, position.coords.longitude];
                addUserMarker(userCoords);
            });
        }


        const geoObjects = [];
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
        // Проходим по массиву $cooperatives и создаем метки на карте
        @foreach ($cooperatives as $mapMarker)
            <?php
            // Сокращаем подпись, если символов больше 15
            $iconCaption = $mapMarker['name'];
            if (mb_strlen($iconCaption) > 20) {
                $iconCaption = mb_substr($iconCaption, 0, 20) . '...';
            }
            ?>
        // Создаем шаблон макета метки для зума >= 14
        CustomPlacemarkLayoutZoom14 = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/GroupMarker.png')}}" alt="Marker"/>' +
            '<div class="custom-caption">' +
            '<div class="custom-caption-text"><?php echo $iconCaption; ?></div>' +
            '</div>'
        );

            myPlacemark = new ymaps.Placemark([<?php echo $mapMarker['id_point']; ?>], {
            balloonContentHeader: '<?php echo $mapMarker['name']; ?>',
            balloonContentBody: `
            <div class="Text_balun_user_inf">
            <p>Председатель: <?php echo $mapMarker["fio"]; ?></p>
            </div>
            <form method="POST" action="{{route('JoinTheCoop.store', ['id' => Auth::id()])}}" class="Button_send">
            @csrf
            <input type="hidden" name="id_coop" value="<?php echo $mapMarker["id_coop"]; ?>" />
            <input type="hidden" name="user_id" value="{{Auth::id()}}">
            <button type="submit" class="button_send_app">Отправить заявку</button>
            </form>
            `,
            balloonContentFooter: "Отправьте заявку чтобы присоединиться",
            iconCaption: '<?php echo $iconCaption; ?>'
        }, {
            // Подключаем кастомный макет метки
            iconLayout: CustomPlacemarkLayoutZoom14,

            // Указываем путь к изображению и размеры иконки
            iconImageHref: "{{asset('icons/map/GroupMarker.png')}}",
            iconImageSize: [35, 35],
            iconImageOffset: [-17, -17],

            iconShape: {
                type: 'Circle', // Используем тип фигуры - круг
                coordinates: [0, -5], // Центр окружности (используем смещение для коррекции)
                radius: 19 // Радиус окружности (в пикселях) - можно задать другой размер
            },
        });

        myCollection.add(myPlacemark); // Добавьте каждую метку в коллекцию myCollection
        geoObjects.push(myPlacemark); // Добавьте каждую метку в коллекцию geoObjects
        @endforeach

        // Добавьте метки в кластеризатор
        clusterer.add(geoObjects);

// Добавьте кластеризатор на карту
        myMap.geoObjects.add(clusterer);
/*<p>Номер телефона: <?php echo $mapMarker["phone"]; ?></p>*/
// Добавьте коллекцию всех меток на карту
        myMap.geoObjects.add(myCollection);

        <?php else: ?>
        // Создаем шаблон макета метки для зума >= 14
        CustomPlacemarkLayoutZoom14 = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/GroupMarker.png')}}" alt="Marker"/>' +
            '<div class="custom-caption">' +
            '<div class="custom-caption-text">{{ $mapMarker['name']  }}</div>' +
            '</div>'
        );
        // Создаем шаблон макета метки для зума >= 14
        CustomPlacemarkCoopUser = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/markerUser.svg')}}" alt="Marker"/>' +
            '<div class="custom-caption-user-coop">' +
            '<div class="custom-caption-text">{{ $mapMarker['name']  }}</div>' +
            '</div>',
        );
        CustomPlacemarkUser = ymaps.templateLayoutFactory.createClass(
            '<div class="custom-placemark-layout">' +
            '<img class="custom-icon" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>',
        );

        // Если база данных не содержит данных о метках, то создаем карту с некоторыми значениями по умолчанию
        myMap = new ymaps.Map("map", {
            center: [55.753930, 37.620795], // Координаты Москвы (значения по умолчанию)
            zoom: 8,
            controls: ['zoomControl', 'fullscreenControl'/*, 'geolocationControl',*/ /*'searchControl'*/],
            searchControlProvider: 'yandex#search'
        }, {
            searchControlProvider: 'yandex#search'
        });
        // Создаем коллекцию для хранения меток
        myCollection = new ymaps.GeoObjectCollection();
        // Создаем пользовательский элемент управления
        let customControl = new ymaps.control.Button({
            data: {
                content: '<div id="map-controls" class="map-controls">' +
                    '<label class="map_controls_label_left">' +
                    '<input type="checkbox" id="user-marker-toggle" class="checked_button" checked> Показать мое местоположение' + '<img class="custom-icon-control" src="{{asset('icons/map/marker_AI.png')}}" alt="Marker"/>' +
                    '</label>' +
                    '<label class="map_controls_label_right">' +
                    '<input type="checkbox" id="markers-toggle" class="checked_button" checked> Показать метки' + '<img class="custom-icon-control-coop" src="{{asset('icons/map/GroupMarker.png')}}" alt="Marker"/>' +
                    '</label>' +
                    '</div>',
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
// Добавьте коллекцию всех меток на карту
        myMap.geoObjects.add(myCollection);
        // Добавляем пользовательский элемент управления на карту
        myMap.controls.add(customControl);

        // Обработчик события изменения чекбокса "Показать мое местоположение"
        $('#user-marker-toggle').on('change', function () {
            if ($(this).is(':checked')) {
                showUserMarker();
            } else {
                hideUserMarker();
            }
        });
// Обработчик события изменения чекбокса "Показать метки"
        $('#markers-toggle').on('change', function () {
            if ($(this).is(':checked')) {
                showAllMarkers();
            } else {
                hideAllMarkers();
            }
        });

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

        // Получаем местоположение пользователя
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                let userCoords = [position.coords.latitude, position.coords.longitude];
                addUserMarker(userCoords);
            });
        }

        <?php endif; ?>
    }
    // Получаем текущее значение зума из события
    /*myMap.events.add('boundschange', function (event) {
      const currentZoom = event.get('newZoom');
      console.log("Текущий зум карты:", currentZoom);
    });*/
    // Функция для скрытия подписей на метках

    // Функция для добавления метки пользователя на карту
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
        myMap.setCenter(coords, 14); // Задайте начальный зум 14 для метки пользователя
    }


    // Функция для получения региона и города по координатам с помощью геокодирования
    function getRegionAndCityFromCoordinates(coords) {
        return new Promise((resolve, reject) => {
            ymaps.geocode(coords).then(function (res) {
                let firstGeoObject = res.geoObjects.get(0);
                savedAddress = firstGeoObject.getAddressLine();
                savedCity = firstGeoObject.getLocalities().join(', ');

                // Вывод региона и города в консоль
                console.log('Адрес:', savedAddress);
                console.log('Город:', savedCity);

                // Установка текста в элементе с id "coordinates2"
                $('#coordinates2').html("Адрес: " + savedAddress + "<br>Город: " + savedCity);

                // Резолвим Promise с объектом, содержащим адрес и город
                resolve({ address: savedAddress, city: savedCity });
            }).catch(error => {
                // В случае ошибки реджектим Promise
                reject(error);
            });
        });
    }

    $('#add-marker-btn').click(function () {
        let coordinatesDiv = document.getElementById('coordinates2');
        coordinatesDiv.textContent = 'Теперь нажмите на место на карте, где будет распологаться ваш кооператив';

    });

    // Флаг для блокировки кнопки
    let addingMarkerInProgress = false;

    // Обработчик события на кнопку "Добавить метку"
    $('#add-marker-btn').click(function () {
        if (addingMarkerInProgress) {
            return; // Если процесс добавления уже идет, ничего не делаем
        }
        if (currentPlacemark) {
            myCollection.remove(currentPlacemark);
        }
        // Блокируем кнопку
        addingMarkerInProgress = true;

        // Помечаем, что режим добавления метки активирован
        let addingMode = true;

        // Включаем режим добавления метки на карту
        myMap.events.once('click', function (e) {
            if (addingMode) {
                let coords = e.get('coords');

                // Создание метки
                currentPlacemark = new ymaps.Placemark(coords, {
                    balloonContentBody: ``,
                    balloonContentFooter: '<span style="font-size: 16px; font-weight: bold;">Ваша новая метка, местоположение кооператива</span>',
                    iconCaption: 'Ваша_метка',
                }, {

                    iconLayout: CustomPlacemarkCoopUser,
                    // Указываем путь к изображению и размеры иконки
                    iconImageHref: "../Icons/markerUser.svg",
                    iconImageSize: [35, 35],
                    iconImageOffset: [-17, -17],

                    iconShape: {
                        type: 'Circle', // Используем тип фигуры - круг
                        coordinates: [0, -5], // Центр окружности (используем смещение для коррекции)
                        radius: 19 // Радиус окружности (в пикселях) - можно задать другой размер
                    },
                });
                // Добавление метки на карту
                myCollection.add(currentPlacemark);

                // Получение региона и города по координатам
                getRegionAndCityFromCoordinates(coords)
                .then(result => {
                    // Сохраняем аддрес и город
                    $('input[name="city"]').val(result.city);
                    $('input[name="address"]').val(result.address);

                });
                savedCoords = coords; // Сохраняем координаты
                // Сбрасываем флаг режима добавления метки после первого клика
                addingMode = false;

                // Сохраняем координаты в скрытых полях формы
                $('input[name="latitude"]').val(coords[0]);
                $('input[name="longitude"]').val(coords[1]);



                // Снимаем блокировку с кнопки
                addingMarkerInProgress = false;
            }
        });
    });
    $('#save-marker-btn').click(function () {

        // Проверяем, добавлена ли метка перед отправкой формы
        if (!$('input[name="latitude"]').val() || !$('input[name="longitude"]').val()) {
            // Выводим сообщение или предпринимаем другие действия
            $('#coordinates2').text('Метка не была добавлена. Пожалуйста, добавьте метку на карту.');
            return false; // Останавливаем отправку формы
        }

        // ... ваш код отправки формы ...
    });
    // Обработчик события на кнопку "Сохранить метку"
/*    $('#save-marker-btn').click(function () {
        let markerName = $('#markerName').val();
        let numberMeter = $('#numberMeter').val();
        if (!currentPlacemark) {
            // Если метка не была добавлена, выводим сообщение об ошибке
            $('#coordinates').text('Метка не была добавлена. Пожалуйста, добавьте метку на карту.');
            $('.error_text_map').css('display', 'block'); // Показываем родительский элемент
            return;
        }
        if (!markerName) {
            // Если название метки не было введено, выводим сообщение об ошибке
            $('#coordinates').text('Введите название кооператива.');
            $('.error_text_map').css('display', 'block'); // Показываем родительский элемент
            return;
        }
        if (numberMeter.trim().length === 0) {
            // Если название метки не было введено, выводим сообщение об ошибке
            $('#coordinates').text('Введите номер общего счётчика.');
            $('.error_text_map').css('display', 'block'); // Показываем родительский элемент
            return;
        }
        // Отдельные переменные для x и y координат
        let latitude = savedCoords[0];
        let longitude = savedCoords[1];*/

        // Отправляем координаты на сервер через AJAX-запрос для сохранения
/*        $.ajax({
            url: "{{ route('ChairmanSendingDataCreateCoop.store', ['id' => Auth::id()]) }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                latitude: JSON.stringify(latitude),
                longitude: JSON.stringify(longitude),
                markerName: markerName,
                address: savedAddress,
                city: savedCity
            },
            success: function (response) {
                // 'response' теперь является объектом JSON
/!*                console.log(response.message);
                console.log(response.markerName);
                console.log(response.address);
                console.log(response.city);
                console.log(response.latitude);
                console.log(response.longitude);*!/
                // Перенаправляем пользователя после успешного AJAX-запроса
                // Добавляем сообщение в сессию
                $(".form-msg").html('<div class="notification-content"><div class="notification">'+ response.message +'</div></div>');

                // Перезагружаем страницу
                setTimeout(function(){
                    location.reload();
                }, 2000); // Ждем 2 секунды перед перезагрузкой
            },
            error: function (xhr, status, error) {
                console.error('Произошла ошибка при сохранении координат: ' + error);
            }
        });*/
/*    });*/
</script>

