<script type="text/javascript">
    class YandexMapManager {
        constructor(config) {
        // Конфигурация
            this.config = {
                mapContainerId: 'map',
                initialCenter: [56.24260923707676, 62.56439128155105],
                initialZoom: 2,
                userIconPath: config.userIconPath || '',
                coopIconPath: config.coopIconPath || '',
                clusterIconPath: config.clusterIconPath || '',
                ajaxUrl: config.ajaxUrl || '',
                csrfToken: config.csrfToken || '',
                ...config
            };

        // Состояние
            this.state = {
                map: null,
                clusterer: null,
                userMarker: null,
                customMarker: null,
                markers: [],
                existingMarkerKeys: new Set(),
                isAddingMarkerMode: false,
                currentCoords: null,
                savedCoords: null,
                savedCity: '',
                savedAddress: '',
                currentCoopId: null,
                boundsUpdateTimeout: null,
                previousBounds: null,
                isMarkersVisible: true,
                isUserMarkerVisible: true,
                zoom: 2
            };

        // DOM элементы
            this.elements = {
                loadingIndicator: null,
                userMarkerToggle: null,
                markersToggle: null,
                coordinatesDisplay: null,
                addMarkerBtn: null,
                saveMarkerBtn: null
            };

        // Макеты меток
            this.layouts = {
                userCoop: null,
                user: null,
                coop: null,
                cluster: null
            };

            this.initialize();
        }

        async initialize() {
            try {
                await ymaps.ready();
                this.initMap();
                this.createLayouts();
                await this.createControls();
                this.setupEventListeners();
                this.loadUserLocation();
                console.log('YandexMapManager инициализирован');
            } catch (error) {
                console.error('Ошибка инициализации карты:', error);
            }
        } 
        initMap() {
            this.state.map = new ymaps.Map(this.config.mapContainerId, {
                center: this.config.initialCenter,
                zoom: this.config.initialZoom,
                controls: ['zoomControl', 'fullscreenControl']
            });

            this.initClusterer();
        }

        initClusterer() {
            this.layouts.cluster = ymaps.templateLayoutFactory.createClass(
                `<div class="custom-cluster-icon">
                <img class="cluster-icon-img" src="${this.config.clusterIconPath}"/>
                <span class="claster-text">$[properties.geoObjects.length]</span>
            </div>`
            );

            this.state.clusterer = new ymaps.Clusterer({
                clusterIconLayout: this.layouts.cluster,
                clusterIconShape: {
                    type: 'Rectangle',
                    coordinates: [[-25, -25], [25, 25]]
                },
                groupByCoordinates: false,
                clusterDisableClickZoom: true,
                clusterHideIconOnBalloonOpen: false,
                geoObjectHideIconOnBalloonOpen: false
            });

            this.state.map.geoObjects.add(this.state.clusterer);
        }

        createLayouts() {
            this.layouts.userCoop = ymaps.templateLayoutFactory.createClass(
                `<div class="custom-placemark-layout">
                <img class="custom-icon" src="${this.config.userIconPath}" alt="Marker"/>
                <div class="custom-caption-user-coop">
                    <div class="custom-caption-text">Моя_метка</div>
                </div>
            </div>`
            );

            this.layouts.user = ymaps.templateLayoutFactory.createClass(
                `<div class="custom-placemark-layout-user">
                <img class="custom-icon" src="${this.config.userIconPath}" alt="Marker"/>
            </div>`
            );
        }

        createControls() {
            return new Promise((resolve) => {
                const controlHtml = `
            <div id="map-controls" class="map-controls">
                <label class="map_controls_label_left">
                    <input type="checkbox" id="user-marker-toggle" class="checked_button" checked>
                    Мое местоположение
                    <img class="custom-icon-control" src="${this.config.userIconPath}" alt="Marker"/>
                </label>
                <label class="map_controls_label_right">
                    <input type="checkbox" id="markers-toggle" class="checked_button" checked>
                    Метки
                    <img class="custom-icon-control-coop" src="${this.config.coopIconPath}" alt="Marker"/>
                </label>
                <label class="map_controls_label_inf">
                    <span id="loading"></span>
                </label>
                </div>`;
                const customControl = new ymaps.control.Button({
                    data: { content: controlHtml },
                    options: {
                        maxWidth: [250, 400],
                        float: 'none',
                        position: { top: 10, left: 10 }
                    }
                });

                this.state.map.controls.add(customControl);
                    // Ждем пока контрол отрисуется
                setTimeout(() => {
                    this.cacheDomElements();
                    resolve();
                }, 100);
            });
        }

        cacheDomElements() {
            this.elements.loadingIndicator = $('#loading');
            this.elements.userMarkerToggle = $('#user-marker-toggle');
            this.elements.markersToggle = $('#markers-toggle');
            this.elements.coordinatesDisplay = $('#coordinates2');
            this.elements.addMarkerBtn = $('#add-marker-btn');
            this.elements.saveMarkerBtn = $('#save-marker-btn');
        }

        setupEventListeners() {
        // Обработка изменения границ карты
            this.state.map.events.add('boundschange', (event) => {
                this.handleBoundsChange(event);
            });

        // Переключение видимости меток
            this.elements.markersToggle.on('change', () => {
                this.toggleMarkersVisibility();
            });

        // Переключение видимости метки пользователя
            this.elements.userMarkerToggle.on('change', () => {
                this.toggleUserMarkerVisibility();
            });

        // Кнопка добавления метки
            if (this.elements.addMarkerBtn.length) {
                this.elements.addMarkerBtn.on('click', () => this.enterAddMarkerMode());
            }

        // Кнопка сохранения метки
            if (this.elements.saveMarkerBtn.length) {
                this.elements.saveMarkerBtn.on('click', (e) => this.saveMarker(e));
            }
        }

        handleBoundsChange(event) {
            const newBounds = this.state.map.getBounds();
            const newZoom = event.get('newZoom') || this.state.map.getZoom();

    // Сохраняем текущий зум
            this.state.zoom = newZoom;

            if (this.shouldUpdateMarkers(newBounds, newZoom)) {
                this.state.previousBounds = newBounds;

                this.elements.loadingIndicator.text('Загрузка...');
                if (this.state.boundsUpdateTimeout) {
                    clearTimeout(this.state.boundsUpdateTimeout);
                }

                if (this.state.isMarkersVisible) {
                    this.state.boundsUpdateTimeout = setTimeout(() => {
                        if (this.state.isMarkersVisible) {
                    // Проверяем зум перед загрузкой меток
                            if (newZoom >= 10) {
                                this.updateMarkers(newBounds, newZoom);
                            } else {
                        // Очищаем метки и показываем сообщение
                                this.clearMarkers();
                                this.elements.loadingIndicator.text('Приблизьте карту, чтобы появились метки');

                        // Опционально: скрываем подписи если зум маленький
                                this.adjustCaptionVisibility(newZoom);
                            }
                        }
                    }, 1000);
                } else {
                    this.elements.loadingIndicator.text('Метки отключены');
                }
            }

    // Всегда обновляем видимость подписей при изменении зума
            this.adjustCaptionVisibility(newZoom);
        }
        clearMarkers() {
            this.state.clusterer.removeAll();
            // Можно также очистить массив меток
            this.state.markers = [];
            this.state.existingMarkerKeys.clear();
        }
        shouldUpdateMarkers(newBounds, newZoom) {
            if (!this.state.previousBounds) return true;

            const tolerance = 0.009;
            const [prevSW, prevNE] = this.state.previousBounds;
            const [newSW, newNE] = newBounds;

            return Math.abs(prevSW[0] - newSW[0]) > tolerance ||
            Math.abs(prevSW[1] - newSW[1]) > tolerance ||
            Math.abs(prevNE[0] - newNE[0]) > tolerance ||
            Math.abs(prevNE[1] - newNE[1]) > tolerance ||
            this.state.zoom !== newZoom;
        }

        async updateMarkers(bounds, zoom) {
            // Дополнительная проверка зума
            if (zoom < 10) {
                this.elements.loadingIndicator.text('Приблизьте карту, чтобы появились метки');
                return;
            }

            try {
                const response = await $.ajax({
                    url: this.config.ajaxUrl,
                    method: 'GET',
                    data: {
                        _token: this.config.csrfToken,
                        bounds: bounds
                    }
                });

                if (response.cooperatives && response.cooperatives.length > 0) {
                    this.processCooperativeData(response.cooperatives, zoom);
                } else {
                    console.log('Нет данных о кооперативах.');
                    this.elements.loadingIndicator.text('');
                }

                this.elements.loadingIndicator.text('');
            } catch (error) {
                this.elements.loadingIndicator.text('Ошибка!');
                console.error('Ошибка загрузки меток:', error);
            }
        }

        processCooperativeData(cooperatives, zoom) {
            const newMarkers = [];

            cooperatives.forEach(cooperative => {
                const key = `${cooperative.latitude}_${cooperative.longitude}`;

                if (!this.state.existingMarkerKeys.has(key)) {
                    const marker = this.createCooperativeMarker(cooperative);
                    this.state.markers.push(marker);
                    this.state.existingMarkerKeys.add(key);
                    newMarkers.push(marker);
                }
            });

            this.refreshClusterer(newMarkers, zoom);
        }

        createCooperativeMarker(cooperative) {
            const trimmedCaption = cooperative.name.length > 20 
            ? cooperative.name.substring(0, 20) + '...' 
            : cooperative.name;

            const layout = ymaps.templateLayoutFactory.createClass(
                `<div class="custom-placemark-layout-coop">
                <img class="custom-icon" src="${this.config.coopIconPath}" alt="Marker"/>
                <div class="custom-caption">
                    <div class="custom-caption-text">${trimmedCaption}</div>
                </div>
            </div>`
            );

            const balloonContent = `
            <div class="Text_balun_user_inf">
                <p>Председатель: ${cooperative.fio}</p>
            </div>`;

            const marker = new ymaps.Placemark(
                [cooperative.latitude, cooperative.longitude],
                {
                    balloonContentHeader: cooperative.name,
                    balloonContentBody: balloonContent,
                    iconCaption: cooperative.name,
                    id_coop: cooperative.id
                },
                {
                    iconLayout: layout,
                    iconImageHref: this.config.coopIconPath,
                    iconImageSize: [35, 35],
                    iconImageOffset: [-17, -17],
                    iconShape: {
                        type: 'Circle',
                        coordinates: [0, -5],
                        radius: 19
                    }
                }
                );

            marker.events.add('click', (e) => this.onMarkerClick(e));
            return marker;
        }

        refreshClusterer(newMarkers, zoom) {
            this.state.clusterer.removeAll();
            this.state.clusterer.add(this.state.markers);

            setTimeout(() => {
                this.state.markers.forEach(marker => {
                    $('.custom-placemark-layout-coop').addClass('visible');
                });
            }, 100);

            this.adjustCaptionVisibility(zoom);
        }

        adjustCaptionVisibility(zoom) {
            // Для подписей меток (zoom >= 14)
            const captionDisplay = zoom >= 14 ? 'block' : 'none';

                // Для меток вообще (zoom >= 10)
            const markersVisible = zoom >= 10;

            setTimeout(() => {
             // Управляем подписями
                $('.custom-caption-text').css('display', captionDisplay);

                 // Если зум меньше 10, скрываем все метки
                if (!markersVisible && this.state.isMarkersVisible) {
                    $('.custom-placemark-layout-coop').css('opacity', '0.3');
                    $('.custom-placemark-layout-coop').removeClass('visible');
                } else if (markersVisible && this.state.isMarkersVisible) {
                    $('.custom-placemark-layout-coop').css('opacity', '1');
                    $('.custom-placemark-layout-coop').addClass('visible');
                }
            }, 150);
        }

        toggleMarkersVisibility() {
            this.state.isMarkersVisible = this.elements.markersToggle.is(':checked');

            if (this.state.isMarkersVisible) {
                // Проверяем зум перед показом меток
                if (this.state.zoom >= 10) {
                    this.showAllMarkers();
                    this.elements.loadingIndicator.text('');
                } else {
                    this.elements.loadingIndicator.text('Приблизьте карту, чтобы появились метки');
                }
            } else {
                this.hideAllMarkers();
            }
        }

        showAllMarkers() {
            this.state.clusterer.add(this.state.markers);
            this.adjustCaptionVisibility(this.state.zoom);

            setTimeout(() => {
                this.state.markers.forEach(marker => {
                    $('.custom-placemark-layout').addClass('visible');
                });
            }, 100);
        }

        hideAllMarkers() {
            this.state.clusterer.removeAll();
            this.elements.loadingIndicator.text('Метки отключены');
        }

        toggleUserMarkerVisibility() {
            this.state.isUserMarkerVisible = this.elements.userMarkerToggle.is(':checked');
            if (this.state.userMarker) {
                this.state.userMarker.options.set('visible', this.state.isUserMarkerVisible);
            }
        }

        loadUserLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => this.addUserMarker(position.coords),
                    (error) => console.warn('Не удалось получить местоположение:', error)
                    );
            }
        }

        addUserMarker(coords) {
            this.state.userMarker = new ymaps.Placemark(
                [coords.latitude, coords.longitude],
                {
                    balloonContentBody: '',
                    balloonContentFooter: "Моё местоположение"
                },
                {
                    iconLayout: this.layouts.user,
                    iconImageHref: this.config.userIconPath,
                    iconImageSize: [35, 35],
                    iconImageOffset: [-17, -17],
                    iconShape: {
                        type: 'Circle',
                        coordinates: [0, -5],
                        radius: 19
                    }
                }
                );

            this.state.map.geoObjects.add(this.state.userMarker);
            this.state.map.setCenter([coords.latitude, coords.longitude], 14);
        }

        enterAddMarkerMode() {
            if (this.state.isAddingMarkerMode) return;

            this.state.isAddingMarkerMode = true;
            this.removeExistingCustomMarker();

            this.elements.coordinatesDisplay
            .text('Теперь нажмите на место на карте, где будет располагаться ваш кооператив')
            .hide().fadeIn(300);

            this.state.map.events.once('click', (e) => this.handleMapClickForMarker(e));
        }

        removeExistingCustomMarker() {
            if (this.state.customMarker) {
                this.state.map.geoObjects.remove(this.state.customMarker);
                this.state.customMarker = null;
            }
        }

        async handleMapClickForMarker(e) {
            if (!this.state.isAddingMarkerMode) return;

            const coords = e.get('coords');
            this.state.currentCoords = coords;
            this.state.savedCoords = coords;

            this.createCustomMarker(coords);

            try {
                const { address, city } = await this.geocodeCoordinates(coords);
                this.updateAddressFields(address, city);
                this.updateCoordinateFields(coords);
            } catch (error) {
                console.error('Ошибка геокодирования:', error);
            }

            this.state.isAddingMarkerMode = false;
        }

        createCustomMarker(coords) {
            this.state.customMarker = new ymaps.Placemark(
                coords,
                {
                    balloonContentBody: '',
                    balloonContentFooter: '<span style="font-size: 16px; font-weight: bold;">Ваша новая метка, местоположение кооператива</span>',
                    iconCaption: 'Ваша_метка'
                },
                {
                    iconLayout: this.layouts.userCoop,
                    iconImageHref: this.config.userIconPath,
                    iconImageSize: [35, 35],
                    iconImageOffset: [-17, -17],
                    iconShape: {
                        type: 'Circle',
                        coordinates: [0, -5],
                        radius: 19
                    }
                }
                );

            this.state.map.geoObjects.add(this.state.customMarker);
        }

        async geocodeCoordinates(coords) {
            return new Promise((resolve, reject) => {
                ymaps.geocode(coords).then((res) => {
                    const firstGeoObject = res.geoObjects.get(0);
                    const address = firstGeoObject.getAddressLine();
                    const city = firstGeoObject.getLocalities().join(', ');

                    this.state.savedAddress = address;
                    this.state.savedCity = city;

                    this.elements.coordinatesDisplay
                    .html(`Адрес: ${address}<br>Город: ${city}`)
                    .hide().fadeIn(300);

                    resolve({ address, city });
                }).catch(reject);
            });
        }

        updateAddressFields(address, city) {
            $('input[name="city"]').val(city);
            $('input[name="address"]').val(address);
        }

        updateCoordinateFields(coords) {
            $('input[name="latitude"]').val(coords[0]);
            $('input[name="longitude"]').val(coords[1]);
        }

        async saveMarker(e) {
            e.preventDefault();

            if (!this.validateForm()) {
                return false;
            }

            if (!this.state.savedCoords) {
                this.showMessage('Метка не была добавлена. Пожалуйста, добавьте метку на карту.');
                return false;
            }

            try {
                const { address, city } = await this.geocodeCoordinates(this.state.savedCoords);
                this.updateAddressFields(address, city);
                $('#myForm').submit();
            } catch (error) {
                this.showMessage('Произошла ошибка при получении данных.');
            }
        }

        validateForm() {
            const nameField = $('input[name="name"]');
            const numberMeterField = $('input[name="number_meter"]');
            let isValid = true;

            if (!nameField.val()) {
                this.highlightField(nameField, 'Обязательное поле!');
                isValid = false;
            }

            if (!numberMeterField.val()) {
                this.highlightField(numberMeterField, 'Обязательное поле!');
                isValid = false;
            }

            if (!isValid) {
                this.showMessage('Заполните все обязательные поля.');
                this.temporarilyDisableSaveButton();
            }

            return isValid;
        }

        highlightField(field, placeholder) {
            field.attr('placeholder', placeholder)
            .css('border', '2px solid red')
            .animate({ borderColor: '' }, 3500, () => {
             field.removeAttr('placeholder').css('border', '');
         });
        }

        showMessage(message) {
            this.elements.coordinatesDisplay.html(message).hide().fadeIn(200);
        }

        temporarilyDisableSaveButton() {
            $('.save_marker').prop('disabled', true);
            setTimeout(() => {
                $('.save_marker').prop('disabled', false);
            }, 3500);
        }

        onMarkerClick(e) {
            const coopId = e.get('target').properties.get('id_coop');
            this.state.currentCoopId = coopId;
            console.log("Выбран кооператив с ID:", coopId);

        // Можно вызвать кастомное событие
            $(document).trigger('coopSelected', [coopId]);

            return coopId;
        }

    // Публичные методы для управления картой
        centerOnCoordinates(lat, lon, zoom = 15) {
            if (this.state.map) {
                this.state.map.setCenter([lat, lon], zoom);
            }
        }

        clearAllMarkers() {
            this.state.clusterer.removeAll();
            this.state.markers = [];
            this.state.existingMarkerKeys.clear();
        }

        destroy() {
            if (this.state.boundsUpdateTimeout) {
                clearTimeout(this.state.boundsUpdateTimeout);
            }

        // Удаляем все обработчики событий
            this.elements.markersToggle.off('change');
            this.elements.userMarkerToggle.off('change');
            this.elements.addMarkerBtn.off('click');
            this.elements.saveMarkerBtn.off('click');

        // Уничтожаем карту
            if (this.state.map) {
                this.state.map.destroy();
            }
        }
    }

// Класс для поиска кооперативов
    class CooperativeSearch {
        constructor(config) {
            this.config = {
                searchUrl: config.searchUrl || '',
                csrfToken: config.csrfToken || '',
                ...config
            };

            this.state = {
                offset: 0,
                hasMore: true,
                isLoading: false
            };

            this.init();
        }

        init() {
            $(document).on('click', '.head_search_coop button', () => this.search());
            $(document).on('click', '#load-more', () => this.loadMore());
        }

        async search() {
            const nameCoopSelect = $('.head_search_coop_input input[name="name_coop"]').val();

            if (nameCoopSelect.length < 3) {
                this.showMessage('Название слишком короткое');
                return;
            }

            this.state.offset = 0;
            this.state.hasMore = true;

            await this.performSearch(nameCoopSelect, true);
        }

        async loadMore() {
            if (this.state.isLoading || !this.state.hasMore) return;

            const nameCoopSelect = $('.head_search_coop_input input[name="name_coop"]').val();
            await this.performSearch(nameCoopSelect, false);
        }

        async performSearch(query, clearResults = true) {
            this.state.isLoading = true;

            if (clearResults) {
                $('.block_name_result_coop').empty();
                $('.block_name_result_coop').append('<span class="span_text">Выполняем запрос...</span>');
            }

            try {
                const response = await $.ajax({
                    url: this.config.searchUrl,
                    method: 'GET',
                    data: {
                        _token: this.config.csrfToken,
                        idMessage: 1,
                        nameCoop: query,
                        offset: this.state.offset
                    }
                });

                this.processSearchResults(response, clearResults);
            } catch (error) {
                this.handleSearchError(error);
            } finally {
                this.state.isLoading = false;
            }
        }

        processSearchResults(response, clearResults) {
            if (clearResults) {
                $('.block_name_result_coop').empty();
            }

            if (response.blockMessages && response.blockMessages.length > 0) {
                response.blockMessages.forEach(coop => {
                    const html = `
                    <div class="inf_coop">
                        <span>Название: ${coop.name}</span>
                        <span>Председатель: ${coop.fio}</span>
                        <span>Местонахождение: ${coop.address}</span>
                        <button data-coords="${coop.latitude},${coop.longitude}">
                            Показать на карте
                        </button>
                    </div>`;
                    $('.block_name_result_coop').append(html);
                });

                this.state.offset += response.blockMessages.length;
                this.state.hasMore = response.hasMore;

                $('#load-more').toggle(this.state.hasMore);
            } else {
                this.showMessage('Ошибка, похоже нет кооперативов с заданными параметрами');
            }
        }

        handleSearchError(error) {
            const errorCount = parseInt($('.span_text').data('error-count') || 0) + 1;

            $('.span_text').data('error-count', errorCount);
            this.showMessage(`Ошибка поиска ${errorCount > 1 ? `(${errorCount})` : ''}`);
        }

        showMessage(message) {
            $('.block_name_result_coop .span_text').remove();
            $('.block_name_result_coop').append(`<span class="span_text">${message}</span>`);
        }
    }
</script>

