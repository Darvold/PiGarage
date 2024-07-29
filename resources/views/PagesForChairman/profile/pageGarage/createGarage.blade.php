@extends('layouts.profileChairman', ['ProfileCreateGarageStyles' => ['createGarage.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanGarage.index')}}">Мои гаражи</a>
        <a href="{{route('ChairmanCreateGarage.index')}}" class="active">Создание гаража</a>
    </div>
    <div class="main_body_create_garage">
        <form method="post" action="{{route('ChairmanCreatingGarage.store')}}" class="form_create">
            @csrf
            <div class="form_block">
                <div class="image_block_garage">
                    <div class="Button_update_img">
                        <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="аватар">
                    </div>
                </div>
                <div class="left_block_text">
                    <label>Номер счётчика: </label>
                    <input type="number" name="number_meter" required inputmode="none">
                </div>
                <div class="right_block_input">
                    <label>Номер гаража:</label>
                    <input type="number" name="number_garage" required inputmode="none">
                </div>
                <div class="right_block_input">
                    <label for="cooperative">Кооператив:</label>
                    <select id="cooperative" class="cooperative_list" name="id_coop" required>
                        <option value="">Выберите ваш кооператив</option>
                        @foreach($coops as $coop)
                        <option value="{{$coop->id_coop}}">{{$coop->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="right_block_input">
                    <label>Номер блока:</label>
                    <select id="blocks" class="cooperative_list" name="id_block" required>
                        <option value="">Выберите сначала кооператив</option>
                    </select>
                    <input type="hidden" name="number_block" required value="">
                </div>
                <div style="width: 100%; display: flex; justify-content: center;">
                    <a href="{{route('ChairmanGarage.index')}}" class="back_a">Вернуться
                    назад</a>
                    <button type="submit" class="button_form">Отправить запрос</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        function sendAjaxRequestGarage(idCoopValue) {
            $.ajax({
                url: '{{ route('ChairmanCreateGarage.index')}}',
                type: "GET",
                data: {
                    idCoop: idCoopValue,
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    let coopMessages = response.blocksForGarage;
                    let $blocksSelect = $('#blocks');
                        // Очищаем предыдущие option
                    $blocksSelect.empty();
                    var html = `<option value="">Выберите номер блока</option>`;
                        // Перебираем массив и обновляем содержимое на странице
                    if (coopMessages !== null && coopMessages.length > 0) {
                        coopMessages.forEach(function (coopMessage) {
                            html += `<option value="${coopMessage.id_block}">${coopMessage.number_block}</option>`;

                        });
                        $blocksSelect.append(html);
                    } else {
                        var html = `<option value="">У кооператива нет гаражных блоков</option>`;
                        $blocksSelect.append(html);
                    }
                },
                error: function (error) {
                    var html = `<option value="">Что-то пошло не так, повторите попытку позже</option>`;
                    $blocksSelect.append(html);
                }
            });
        }
            // Добавляем обработчик события изменения значения в элементе select
        $('#cooperative').change(function () {
                // Получаем выбранное значение
            var selectedCoopId = $(this).val();
                // Ваш код обработки выбранного значения, например, вывод в консоль
            console.log("Выбран кооператив с ID: " + selectedCoopId);
            sendAjaxRequestGarage(selectedCoopId);
                // Дополнительные действия, которые вы хотите выполнить при выборе
                // Например, отправка AJAX-запроса или изменение других элементов страницы
        });
        $('#blocks').change(function () {
                // Получаем выбранное значение
            var selectedBlockId = $(this).val();
            var selectedBlockNumber = $(this).find('option:selected').text();
            $('input[name="number_block"]').val(selectedBlockNumber);
                // Ваш код обработки выбранного значения, например, вывод в консоль
            console.log("Выбран блок с ID: " + selectedBlockId);
            console.log("Выбран блок с номером: " + selectedBlockNumber);
                // Дополнительные действия, которые вы хотите выполнить при выборе
                // Например, отправка AJAX-запроса или изменение других элементов страницы
        });
    });
</script>
@endsection
