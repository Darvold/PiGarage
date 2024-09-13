@extends('layouts.mainChairman', ['ProfileApplicationToCoopStyles' => ['applicationToCoop.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanConnectCoop.index')}}" class="">Поиск кооперативов</a>
        <a href="{{route('MyApplicationToCoop.index')}}" class="active">Мои заявки</a>
    </div>
    <div class="main_body">
        <div class="inf_text">
            <span>Если вам нужно изменить данные о гараже/гаражах или добавить
                новый гараж после отправки заявки, отмените текущую заявку и подайте
                новую на странице <a href="{{ route('ChairmanConnectCoop.index') }}" class="" style="color: blue;">Поиск кооперативов</a>.</span>
        </div>
        <div class="main_scroll">
            @forelse($userApplication as $app)
                <div class="main_block_application">
                    <div class="head_inf_app">
                        <div class="head_block_application">
                            <span>Гаражный кооператив: {{$app->name}}</span>
                        </div>
                        <div class="main_body_application">
                            <span>Председатель: {{$app->fio}}</span>
                            <span>Адрес: {{$app->address}}</span>
                            @if($app->status == 'pending')
                                <span>Статус: <span class="span_status_pending">в ожидании</span></span>
                            @elseif($app->status == 'delete')
                                <span>Статус: <span class="span_status_delete">отменена</span></span>
                            @elseif($app->status == 'rejected')
                                <span>Статус: <span class="span_status_delete">отклонена председателем</span></span>
                            @elseif($app->status == 'accepted')
                                <span>Статус: <span class="span_status_accepted">Принята</span></span>
                            @endif
                        </div>
                        <div class="block_button">
                            <div class="center_hight">
                                <span>Дата отправки: {{$app->send_date}}</span>
                                @if($app->status == 'pending')
                                    <form method="post" action="{{route('MyApplicationToCoopPost.store')}}">
                                        @csrf
                                        <input type="hidden" name="number_app" value="{{$app->id_application}}">
                                        <input type="hidden" name="id_coop" value="{{$app->id_coop}}">
                                        <input type="hidden" name="value" value="delete">
                                        <button type="submit" style="border: 2px solid red;">Отменить заявку</button>
                                    </form>
                                @elseif($app->status == 'delete')
                                    <form method="post" action="{{route('MyApplicationToCoopPost.store')}}">
                                        @csrf
                                        <input type="hidden" name="number_app" value="{{$app->id_application}}">
                                        <input type="hidden" name="id_coop" value="{{$app->id_coop}}">
                                        <input type="hidden" name="value" value="pending">
                                        <button type="submit" style="border: 2px solid green;">Возобновить</button>
                                    </form>
                                @elseif($app->status == 'accepted')
                                    <span>Заявка принята!</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Проверяем наличие гаражей в текущей заявке -->
                    <div class="garages_user_inf">
                        <div style="display: flex;">
                            <span class="garages_sent">Гаражи: {{ !empty($app->garages) ? '' : 'без гаража' }}</span>
                            @if(!empty($app->garages))
                                <div class="head_main_right_block">
                                    @php $countGarage = 0; @endphp
                                    @foreach($app->garages as $garage)
                                        @php $countGarage++; @endphp
                                        <button class="garage-btn" data-garage="{{$countGarage}}">Гараж №{{$countGarage}}</button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(!empty($app->garages))
                            <!-- Детальная информация по каждому гаражу -->
                            <div class="garage_inf">
                                @php $countGarage = 0; @endphp
                                @foreach($app->garages as $garage)
                                    @php $countGarage++; @endphp
                                    <div class="block_input active" data-garage="{{$countGarage}}" id="block_input_{{$countGarage}}">
                                        <div class="right_block_input">
                                            <label>Номер счётчика: </label>
                                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_meter" required maxlength="20" value="{{$garage['number_meter']}}">
                                        </div>
                                        <div class="right_block_input">
                                            <label>Номер гаража:</label>
                                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage" required maxlength="20" value="{{$garage['number_garage']}}">
                                        </div>
                                        <div class="right_block_input">
                                            <label>Номер блока:</label>
                                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_block" required maxlength="20" value="{{$garage['number_block']}}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="no_applications">
                    <p>У вас пока нет заявок на присоединение к кооперативу.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.main_block_application').each(function () {
            const $applicationBlock = $(this);
            const $garageBtns = $applicationBlock.find('.garage-btn');
            const $garageBlocks = $applicationBlock.find('.block_input');

            // Первоначально активировать первый гаражный блок в текущей заявке
            $garageBlocks.first().addClass('active');
            $garageBlocks.not(':first').hide();

            $garageBtns.first().css({
                'color': 'white',
                'background-color': 'rgb(19, 105, 192)',
                'border': '3px solid rgb(19, 105, 192)'
            });

            // Обработка клика на кнопку выбора гаража в текущей заявке
            $garageBtns.on('click', function () {
                const garageId = $(this).data('garage');
                const $activeBlock = $applicationBlock.find('.block_input.active');

                // Если нажата кнопка, соответствующая уже активному блоку, ничего не делаем
                if ($activeBlock.attr('id') === `block_input_${garageId}`) {
                    return;
                }

                // Скрыть текущий активный блок и убрать активный класс
                $activeBlock.removeClass('active').hide();

                // Показать выбранный блок и добавить активный класс
                $applicationBlock.find(`#block_input_${garageId}`).addClass('active').show();

                // Обновить стили кнопок
                $garageBtns.css({
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
        });
    });
</script>

{{-- <form method="post" action="">
           @csrf
           <input type="hidden" name="">
           <button type="submit" class="">Изменить данные</button>
       </form> --}}
@endsection
