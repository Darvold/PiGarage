@extends('layouts.profileChairman', ['MessagesMetersStyles' => ['messagesMeters.css', 'scroll.css']])

@section('profile')
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileChairman.index', ['id' => Auth::id()])}}">Мой профиль</a>
        <a href="{{route('ChairmanGarage.index', ['id' => Auth::id()])}}">Мои гаражи</a>
        <a href="{{route('ChairmanMyCoop.index', ['id' => Auth::id()])}}">Мои кооперативы</a>
        <span style="font-size: 34px;">></span>
        <a href="{{route('MessagesMeters.index', ['id' => Auth::id()])}}" class="active">Электросчетчики</a>
    </div>
    <div class="main_body">
        <div class="right_two_block">
            <div class="top_block">
                <div class="left_block_svg">
                    <img  class="svg" onclick="leftScroll()" src="{{asset('icons/user/buttonLeft.svg')}}" alt="Влево">
                </div>
                <div class="scroll_garage">
                  <?php /*for ($i=0; $i < 3; $i++) { */?>
                    <form>
                        <button>Гаражный блок №1</button>
                    </form>
                    <form>
                        <button>Гаражный блок №2</button>
                    </form>
                    <form>
                        <button>Гаражный блок №3</button>
                    </form>
                    <form>
                        <button>Гаражный блок №4</button>
                    </form>
                    <?php /*} */?>
                </div>
                <div class="right_block_svg">
                    <img class="svg" onclick="rightScroll()" src="{{asset('icons/user/buttonRight.svg')}}" alt="Влево">
                </div>
            </div>

            <div class="flex_block_2">

                <div class="display_mouth">

                    <div class="top_block_time">
                        <div class="left_block_svg">
                            <img  class="svg" onclick="leftScroll()" src="{{asset('icons/user/buttonLeft.svg')}}" alt="Влево">
                        </div>
                        <span style="font-size: 24px;">29.10.2023</span>
                        <div class="right_block_svg">
                            <img class="svg" onclick="rightScroll()" src="{{asset('icons/user/buttonRight.svg')}}" alt="Влево">
                        </div>
                    </div>

                </div>

                <div class="right_block">
                 <?php for ($i=0; $i < 10; $i++) { ?>
                    <div class="block_applications">
                        <div class="img_center_right">
                            <img src="{{asset('image/user/DefaultUser.jpg')}}" alt="Пользователь">
                        </div>
                        <div class="right_text_right_block">
                            <div style="display: flex;">
                                <div class="text_right_span">
                                    <span>
                                        Панкин Игорь Сергеевич
                                    </span>
                                    <span>
                                        Показатели: 12343кВт
                                    </span>
                                </div>
                                <div class="img_meter">
                                    <img src="{{asset('icons/user/meter.svg')}}" alt="Счётчик">
                                </div>
                            </div>
                            <div class="display_form">
                                <form>
                                    <button type="submit" class="button_green">Принять</button>
                                </form>
                                <form>
                                    <button type="submit" class="button_change">Изменить КВт</button>
                                </form>
                                <button type="submit" class="button_img">Смотреть фото</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>
</div>
</div>
<script>
  function leftScroll() {
    const left = $(".scroll_garage");
    left.animate({ scrollLeft: '-=200' }, 500);
}

function rightScroll() {
    const right = $(".scroll_garage");
    right.animate({ scrollLeft: '+=200' }, 500);
}
</script>

@endsection
