@extends('layouts.profileChairman', ['ApplicationsStyles' => ['applications.css', 'scroll_coop.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('Applications.index', ['id' => Auth::id()])}}" class="active">Заявки</a>
        <a href="">Отклонённые заявки</a>
    </div>
    <div class="main_body">
        <div class="left_block">
            <?php for ($i=0; $i < 10; $i++) { ?>
                <div class="block_coop">
                    <div class="img_center_left">
                        <img src="{{asset('image/user/defaultGarage.jpg')}}" alt="кооператив">
                    </div>
                    <div class="right_text">
                        <span>
                            Железная дорога и богатая линия дорога производства
                        </span>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="right_block">
           <?php for ($i=0; $i < 10; $i++) { ?>
            <div class="block_applications">
                <div class="img_center_left">
                    <img src="{{asset('image/user/DefaultUser.jpg')}}" alt="кооператив">
                </div>
                <div class="right_text">
                    <span>
                        Панкин Игорь Сергеевич
                    </span>
                    <div class="display_form">
                        <form>
                            <button type="submit" class="button_green">Принять</button>
                        </form>
                        <form>
                            <button type="submit" class="button_red">Отклонить</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</div>
@endsection
