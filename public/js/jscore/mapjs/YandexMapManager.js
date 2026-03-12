import MapFunc from './MapFunc.js';
export default class YandexMapManager {
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
        // "Подмешиваем" методы миксина в экземпляр
        Object.assign(this, MapFunc);
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
        this.elements.noMapMarker = $('#no-map-marker');
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


    // Проверяем состояние чекбокса при загрузке
        this.updateAddMarkerButtonState();

    // Добавляем обработчик изменений чекбокса
        this.elements.noMapMarker.on('change', () => {
            this.updateAddMarkerButtonState();
        });

    // Кнопка добавления метки
        this.elements.addMarkerBtn.on('click', () => this.enterAddMarkerMode());

    // Кнопка сохранения метки
        if (this.elements.saveMarkerBtn.length) {
            this.elements.saveMarkerBtn.on('click', (e) => this.saveMarker(e));
        }
    }

        // Новый метод для управления состоянием кнопки
    updateAddMarkerButtonState() {
    // is(':checked') возвращает boolean
        const isNoMapMarkerChecked = this.elements.noMapMarker.is(':checked');

    // Если чекбокс ОТМЕЧЕН (no_map_marker = true), то ОТКЛЮЧАЕМ кнопку
    // Если чекбокс НЕ отмечен (no_map_marker = false), то ВКЛЮЧАЕМ кнопку
        if (isNoMapMarkerChecked) {
            this.elements.addMarkerBtn.prop('disabled', true);
        } else {
            this.elements.addMarkerBtn.prop('disabled', false);
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

