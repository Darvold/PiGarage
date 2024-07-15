@extends('layouts.profileChairman', ['ProfileApplicationToCoopStyles' => ['applicationToCoop.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ChairmanConnectCoop.index', ['id' => Auth::id()])}}" class="">Поиск кооперативов</a>
       <a href="{{route('MyApplicationToCoop.index', ['id' => Auth::id()])}}" class="active">Мои заявки</a>
   </div>
   <div class="main_body">

   </div>
</div>
@endsection
