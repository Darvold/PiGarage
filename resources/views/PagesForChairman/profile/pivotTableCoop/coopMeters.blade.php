@extends('layouts.mainChairman', ['ProfileCoopStylesMeters' => ['coopMeters.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
        <a href="{{route('coopMeters.index', ['idCoop' => $idCoop])}}" class="active">Счётчики</a>
    </div>
    <div class="main_body">
        <div class="flex_column">
            <div style="margin-bottom: 10px;">
                <form method="post" action="{{route('coopMetersPost.store', ['idCoop' => $idCoop])}}" class="create_new_meter">
                    <div class="input_block">
                        @csrf
                        <div>
                            <span>Номер счётчика: </span>
                            <input type="tel" name="meter_number" required/>
                        </div>
                        <div>
                            <span>Прошлые показания кВт: </span>
                            <input type="tel" name="initially_kw" placeholder="Если нет, то впишите 0" required/>
                        </div>
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
                            <div>
                                <span>Номер счётчика: </span>
                                <input type="tel" name="meter_number" value="{{$meter->meter_number}}" />
                            </div>
                            <div>
                                <span>Прошлые показания: </span>
                                <input type="tel" name="initially_kw" value="{{$meter->initially_kw}}" />
                            </div>
                        </div>
                        @if($meter->active == 1)
                        <span class="status">Статус: <span class="true">активный</span></span>
                        @else
                        <span class="status">Статус: <span class="false">Не активный</span></span>
                        @endif
                        <span>Дата создания: {{$meter->creation_date}}</span>
                    </div>
                    <div class="body_meter">
                        <form method="post" action="{{route('coopMetersPost.store', ['idCoop' => $idCoop])}}" class="form_update">
                            @csrf
                            <input type="hidden" name="meter_number" value="">
                            <input type="hidden" name="initially_kw" value="">
                            <input type="hidden" name="idPost" value="1">
                            <input type="hidden" name="number_id" value="{{$meter->id_meter_number}}">
                            <button type="submit">Изменить номер</button>
                        </form>
                    </div>
                </div>
                @empty
                <span style="font-size: 22px">Добавьте номер счётчика</span>
                @endforelse
            </div>
        </div>
        <div class="text_memo">
            <span style="font-size: 23px;">Памятка (обязательно к ознакомлению!)</span>
            <br>
            <span style="font-size: 23px;">
                Все показания привязываются к вашему номеру счётчика. Если вы добавляете новый номер счётчика, последующие показания будут привязываться к новому номеру (статус станет "активный"). Добавляйте новый номер счётчика только в случае замены счётчика в вашем кооперативе, чтобы новые показания начинались с 0 кВт.
                <br><br>
                Если вы ошиблись в цифрах номера счётчика при добавлении нового, вы всегда можете изменить номер активного счётчика или прошлые показания. 
                <br></br>
                Нельзя добавить больше 3 счётчиков в год.
                <br></br>
                Важно!!!<br>
                Все показания участников привязываются к вашему счётчику. Если в нём уже есть прошлые показания, то новые значения добавляются к общему показателю кооператива. При изменении показаний общие значения для всех месяцев также обновляются, так как они основываются на прошлых показаниях, указанных на этой странице. Рекомендуем не изменять значения после того, как показания участников были приняты.
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
            let initially_kw = $(this).closest('.block_meter').find('.meter_number_span input[name="initially_kw"]').val();
            $(this).find('input[name="meter_number"]').val(meterNumber);
            $(this).find('input[name="initially_kw"]').val(initially_kw);
            this.submit();
        });
    });

</script>
@endsection
