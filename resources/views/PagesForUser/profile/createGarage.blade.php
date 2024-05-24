@extends('layouts.profileUser', ['ProfileCreateGarageStyles' => ['createGarage.css']])
@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileUser.index', ['id' => Auth::id()])}}">Мой профиль</a>
        <a href="{{route('UserCreateGarage.index', ['id' => Auth::id()])}}" class="active">Создание гаража</a>
        <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Мои кооперативы</a>
    </div>
    <div class="main_body_create_garage">
        <form class="POST" action="{{route('CreatingGarage.store', ['id' => Auth::id()])}}" class="form_create">
            @csrf
            <div class="form_block">
                <div class="image_block_garage">
                  <div class="Button_update_img">
                    <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="аватар">
                </div>
                <div class="Button_update_img">
                    <a class="Update_img" href="">Изменить фото</a>
                </div>
            </div>
            <div class="left_block_text">
                <label>Номер счётчика: </label>
                <input  type="number" name="number_meter" required inputmode="none">
            </div>
            <div class="right_block_input">
                <label>Номер гаража:</label>
                <input  type="number" name="number_garage" required inputmode="none">
            </div>
                <div class="right_block_input">
                <label>Номер блока:</label>
                <input  type="number" name="number_block" required inputmode="none">
            </div>
                <div style="width: 100%; display: flex; justify-content: center;">
                    <a href="{{route('UserGarage.index', ['id' => Auth::id()])}}" class="back_a">Вернуться назад</a>
                    <button type="submit" class="button_form">Создать гараж</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
