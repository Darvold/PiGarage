@extends('layouts.profileUser', ['ProfileUserStyles' => ['main.css']])
@section('profile')
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ProfileUser.index', ['id' => Auth::id()])}}" class="active">Мой профиль</a>
            <a href="{{route('UserGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
            <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Мои кооперативы</a>
        </div>
        <div class="main_body">
            <div class="flex_block_content">
                <div class="image_block_user">
                    <img src="{{asset('image/user/defaultUser.jpg')}}" alt="аватар">
                </div>
                <div class="information_user">
                    <div class="Float_right">
                        <div class="Position_user_information">
                            <span class="FIO">{{ $user->fio }}</span>
                            <div class="User_information">
                                <span>Почта: {{ $user->email }}</span>
                                <span>Регион: {{ $user->region }}</span>
                                <span>Телефон: {{ $user->phone }}</span>
                                <span>Дополнительный телефон: +7(923)622-71-12</span>
                                <span>Домашний телефон: 6-32-51</span><br><br>
                                <span>Дата регистрации: {{ \Carbon\Carbon::parse($user->data_reg)->format('d.m.Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Float_right_coop">
                    <div class="Position_user_information">
                        <span class="FIO">Мои кооперативы:</span>
                        <div class="User_information_coop">
                            <ul>
                                <li><a href="">Железная дорога</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
