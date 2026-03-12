@extends('layouts.mainUser', ['MyCoops_CreateStyle' => ['myCoops_Create.css', 'map_marker.css']])
@section('pages')
<script
src="https://api-maps.yandex.ru/2.1?apikey=aa1a4f1a-2153-49ec-bbc8-db91be38ff21&load=package.full&lang=ru_RU"></script>

<!-- Основной контент -->
<div class="main-content-create">
    <div class="page-header">
        <h1 class="page-title">Заявка на новый кооператив</h1>
        <a href="{{route('myCoops.index')}}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Вернуться назад
        </a>
    </div>

    <div class="create-cooperative-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-plus-circle"></i> Создать кооператив – быстро и легко!
            </h2>
            <p class="card-subtitle">Заполните форму ниже для создания нового гаражного кооператива</p>
        </div>
        <form method="post" action="{{route('myCoops_Create.store')}}">
            @csrf
            <div class="create-form">
                <!-- Шаг 1: Основная информация -->
                <div class="form-step">
                    <h3 class="step-title">
                        <span class="step-number">1</span> Основная информация
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="coop-name">Название кооператива:</label>
                        <div class="input-with-icon">
                            <input type="text" id="coop-name" required class="form-control" name="name" placeholder="Введите название кооператива">
                            <i class="input-icon fas fa-home"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="block-count">Количество гаражных блоков:</label>
                        <div class="input-with-icon">
                            <input type="number" required id="counter-number" name="number_garage_blocks" class="form-control" min="1" max="10" value="1">
                            <i class="input-icon fas fa-hashtag"></i>
                        </div>
                    </div>
                    <!-- Чекбокс для создания без метки на карте -->
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="no-map-marker" name="no_map_marker" class="checkbox-input">
                            <label for="no-map-marker" class="checkbox-label">
                                <span class="checkbox-custom"></span>
                                Создать кооператив без метки на карте
                                <span class="checkbox-hint">(Будет создан только личный номер кооператива)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Шаг 2: Расположение -->
                <div class="form-step">
                    <h3 class="step-title">
                        <span class="step-number">2</span> Расположение кооператива
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Установите расположение кооператива на карте России:</label>
                        <div class="map-placeholder" id="map-placeholder">
                            <div class="map" id="map" style="width: 100%; height: 100%;"></div>

                        </div>
                    </div>
                </div>

                <!-- Шаг 3: Подтверждение -->
                <div class="form-step">
                    <input type="hidden" name="city">
                    <input type="hidden" name="address">
                    <input type="hidden" name="latitude">
                    <input type="hidden" name="longitude">
                    <div class="error_text_map" style="display: none;">
                        <span id="coordinates"></span>
                    </div>
                    <div class="error_text_map_script">
                        <span id="coordinates2"></span>
                    </div>
                    {{-- <h3 class="step-title">
                        <span class="step-number">3</span> Подтверждение и отправка
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="additional-info">Дополнительная информация (необязательно):</label>
                        <textarea id="additional-info" class="form-control" rows="4" placeholder="Любая дополнительная информация о кооперативе"></textarea>
                    </div> --}}
                </div>
            </div>
            <!-- Кнопки действий -->
            <div class="action-buttons">
                <button type="button" class="btn btn-primary" id="add-marker-btn">
                    <i class="fas fa-map-marker-alt"></i> Добавить метку
                </button>

                <button type="submit" class="btn btn-accent">
                    <i class="fas fa-paper-plane"></i> Отправить заявку
                </button>
            </div>
    </form>
    <!-- Информационная панель -->
    <div class="info-panel">
        <h3 class="info-title">
            <i class="fas fa-info-circle"></i> Что происходит после отправки заявки?
        </h3>
        <div class="info-content">
            <p>После нажатия кнопки "Отправить заявку" ваша заявка будет направлена администратору системы. Вам нужно будет дождаться её одобрения. После подтверждения администратором ваш кооператив появится в системе, и вы сможете начать приглашать участников, управлять счетами и использовать все функции PIGarage.</p>
            <p style="margin-top: 10px;">Обычно рассмотрение заявки занимает не более 24 часов.</p>
        </div>
    </div>
</div>

</div>
{{--@include('layouts.mapJS')--}}
<script type="module">
    import YandexMapManager from '{{ asset('js/jscore/mapjs/YandexMapManager.js') }}';
    import CooperativeSearch from '{{ asset('js/jscore/mapjs/CooperativeSearch.js') }}';
    ymaps.ready(() => {
    // Инициализация менеджера карты
        const mapManager = new YandexMapManager({
            userIconPath: '{{ asset("icons/map/marker_AI.png") }}',
            coopIconPath: '{{ asset("icons/map/GroupMarker.png") }}',
            clusterIconPath: '{{ asset("icons/map/claster.png") }}',
            ajaxUrl: '{{ route("ChairmanSelectCoopsAJAX.index") }}',
            csrfToken: '{{ csrf_token() }}'
        });

    // Инициализация поиска кооперативов
        const coopSearch = new CooperativeSearch({
            searchUrl: '{{ route("ChairmanConnectCoop.index") }}',
            csrfToken: '{{ csrf_token() }}'
        });

    // Обработка клика на кнопку "Показать на карте"
        $(document).on('click', 'button[data-coords]', function() {
            const coords = $(this).data('coords').split(',');
            mapManager.centerOnCoordinates(parseFloat(coords[0]), parseFloat(coords[1]));
        });

    // Слушаем событие выбора кооператива
        $(document).on('coopSelected', (event, coopId) => {
            console.log('Кооператив выбран:', coopId);
        // Дополнительная логика при выборе кооператива
        });
    });
</script>
@endsection
