@extends('layouts.mainChairman', ['ProfileUserStyles' => ['main.css']])
@section('profile')
    <div class="main_block">
        <div class="main_head">
            <a href="{{route('ProfileChairman.index')}}" class="active">Мой профиль</a>
            <a href="{{route('SettingsChairman.index')}}">Настроить профиль</a>
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
                                <span>Телефон: {{ formatPhoneNumber($user->phone) }}</span>
                                <span>Дополнительный телефон: {{formatPhoneNumber($user['second_phone'])}}</span>
                                <span>Домашний телефон: {{ formatHomePhoneNumber($user['home_phone']) }}</span><br><br>
                                <span>Дата регистрации: {{ Date::parse($user->data_reg)->format('d.m.Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Float_right_coop">
                    <div class="Position_user_information">
                        <span class="FIO">Мои кооперативы:</span>
                        <div class="User_information_coop">
                            <ul>
                                @forelse($myCoops as $myCoop)
                                    <li>
                                        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $myCoop->id_coop])}}">{{$myCoop->name}}</a>
                                    </li>
                                @empty
                                    <a href="{{route('ChairmanMyCoop.index')}}">Создать первый гаражный коорператив</a>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
