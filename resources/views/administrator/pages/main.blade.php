@extends('administrator.layouts.AdminMain', ['ProfileUserStyles' => ['main.css']])
@section('profileAdmin')
    <div class="main_block">
        <div class="main_head">

        </div>
        <div class="main_body">
            @foreach($applications as $application)
                <form method="POST" action="{{route('AddNewCoop.store', ['idAdmin' => Auth::id()])}}">
                    @csrf
                    <input type="hidden" name="id_application" value="{{$application->id_application}}">
                    <p>Айди заявки = {{$application->id_application}}</p><br>
                    <p>Пользователь = {{$application->user_id}}</p><br>
                    <p>{{$application->name}}</p><br>
                    <p>{{$application->id_point}}</p><br>
                    <button type="submit">Принять</button>
                </form>
            @endforeach
        </div>
    </div>
@endsection
