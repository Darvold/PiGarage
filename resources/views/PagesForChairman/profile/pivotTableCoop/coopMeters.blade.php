@extends('layouts.mainChairman', ['ProfileGarageStylesMeters' => ['garageMeters.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanGarage.index')}}">Мои гаражи</a>
        <a href="{{route('myGaragePivotTable.index', ['idGarage' => $garage->id_garage])}}">Номергаража: {{$garage->number_garage}}</a>
        <a href="{{route('garageMeters.index', ['idGarage' => $garage->id_garage])}}" class="active">Счётчики</a>
    </div>
    <div class="main_body">
        <div class="flex_column">
            <div style="margin-bottom: 10px;">
                <form method="post" action="{{route('garageMeters.store', ['idGarage' => $garage->id_garage])}}" class="create_new_meter">
                    <div class="input_block">
                        @csrf
                        <span>Номер счётчика: </span>
                        <input type="tel" name="meter_number" required/>
                        <input type="hidden" name="idPost" value="0">
                    </div>
                    <div class="button_block">
                        <button class="submit">Добавить новый счётчик</button>
                    </div>
                </form>
            </div>
            <div class="span_absolute">
                <span class="my_meters">Мои счётчики</span>
            </div>
            <div class="list_number_meter">
                @forelse($meters as $meter)
                <div class="block_meter">
                    <div class="head_meter">
                        <div class="meter_number_span">
                            <span>Номер счётчика: </span>
                            <input type="tel" name="meter_number" value="{{$meter->meter_number}}" />
                        </div>
                        @if($meter->active == 1)
                        <span class="status">Статус: <span class="true">активный</span></span>
                        @else
                        <span class="status">Статус: <span class="false">Не активный</span></span>
                        @endif
                        <span>Дата создания: {{$meter->creation_date}}</span>
                    </div>
                    <div class="body_meter">
                        <form method="post" action="{{route('garageMeters.store', ['idGarage' => $garage->id_garage])}}" class="form_update">
                            @csrf
                            <input type="hidden" name="meter_number" value="">
                            <input type="hidden" name="idPost" value="1">
                            <input type="hidden" name="number_id" value="{{$meter->id_meter_number}}">
                            <button type="submit">Изменить номер</button>
                        </form>
                    </div>
                </div>
                @empty
                <span>Добавьте номер счётчика</span>
                @endforelse
            </div>
        </div>
        <div class="text_memo">
            <span style="font-size: 23px;">Памятка (обязательно к ознакомлению!)</span>
            <br>
            <span style="font-size: 23px;">
                Для отправки показаний необходимо, чтобы у гаража был указан свой номер счётчика. 
                В противном случае, показания не удастся отправить председателю гаражного кооператива.
                <br><br>
                Все показания привязываются к вашему номеру счётчика. Если вы добавляете новый номер счётчика, последующие показания будут привязываться к новому номеру (статус станет "активный"). Добавляйте новый номер счётчика только в случае замены счётчика в вашем гараже, чтобы новые показания начинались с 0 кВт.
                <br><br>
                Если вы ошиблись в цифрах номера счётчика при добавлении нового, вы всегда можете изменить номер активного счётчика.
                <br></br>
                Нельзя добавить больше 3 счётчиков в год.
            </span>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.form_update').on('submit', function(e) {
        e.preventDefault();
        // Используем closest для поиска input в ближайшем элементе с классом meter_number_span
        let meterNumber = $(this).closest('.block_meter').find('.meter_number_span input[name="meter_number"]').val();
        $(this).find('input[name="meter_number"]').val(meterNumber);
        this.submit();
    });
});

</script>
@endsection
