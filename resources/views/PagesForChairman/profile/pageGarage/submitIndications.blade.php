@extends('layouts.profileChairman', ['SubmitIndications' => ['submitIndications.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanGarage.index')}}">Мои гаражи</a>
        <a href="{{route('myGaragePivotTable.index', ['idGarage' => $idGarage])}}">Номер гаража: {{$garageCoop->number_garage}}</a>
        <a href="{{route('ChairmanSubmitIndicationsGarage.index', ['idGarage' => $idGarage, 'numberGarage' => $garageCoop->number_garage])}}" class="active">Передать показания</a>
    </div>
    <script>
        $(document).ready(function () {
            $("#fileInput").change(function () {
                if (this.files && this.files[0]) {
                    $('#previewImage').attr('src', URL.createObjectURL(this.files[0]));
                }
            });
        });
    </script>
    <div class="main_body_create_garage">
        <form method="post" action="{{route('ChairmanSubmitIndicationsPostGarage.store', ['idGarage' => $idGarage, 'numberGarage' => $garageCoop->number_garage])}}"
          class="form_create" enctype="multipart/form-data">
          @csrf
          <div class="form_block">
            <div class="image_block_garage">
                <img id="previewImage" src="{{asset('image/user/defaultMeter.png')}}" alt="счётчик">
            </div>
            <div class="left_block_text">
                <label>Фотография показаний счётчика: </label>
                <input accept="image/png, image/jpg, image/jpeg" type="file" id="fileInput" class="file_img"
                name="img_meter" required inputmode="none">
            </div>
            <div class="right_block_input">
                <label>Показания (только цифры): </label>
                <input type="number" name="kw_meter" required inputmode="none"  value="">
            </div>
            <div class="right_block_input">
                <label for="cooperative">Кооператив: {{ $garageCoop->cooperative->name }}</label>
            </div>
            <div class="right_block_input">
                <label for="cooperative">Номер гаража: {{ $garageCoop->number_garage }}</label>
            </div>
            <div class="right_block_input">
                <label for="cooperative">Номер блока: {{ $garageCoop->number_block }}</label>
            </div>
            <div style="width: 100%; display: flex; justify-content: center;">
                <a href="{{ route('myGaragePivotTable.index', ['idGarage' => $idGarage]) }}"
                 class="back_a">Вернуться назад</a>
                 <button type="submit" name="submit" class="button_form">Отправить показания</button>
             </div>
         </div>
     </form>
 </div>
</div>
@endsection
