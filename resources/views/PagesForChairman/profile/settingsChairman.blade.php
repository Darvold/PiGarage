@extends('layouts.profileChairman', ['ProfileSettingsChairman' => ['settingsChairman.css']])
@section('profile')
<link rel="stylesheet" href="{{ asset('css/PagesForChairman/connectCoop/select2.min.css') }}">
<script src="{{ asset('js/NumberMask/imask.js') }}"></script>
<script src="{{ asset('js/select/select2.min.js') }}"></script>
<script src="{{ asset('js/select/ru.js') }}"></script>
<script src="{{ asset('js/select/russian-cities.js') }}"></script>
<div class="main_block">
    <div class="main_head">
        <a href="{{route('ProfileChairman.index')}}">Мой профиль</a>
        <a href="{{route('SettingsChairman.index')}}" class="active">Настроить профиль</a>
    </div>
    <div class="main_body">
        <div class="flex_block_content">
            <div class="information_user">
                <div class="Position_user_information">
                    <form method="post" action="{{route('settingsChairmanPost.store')}}">
                        @csrf
                        <div class="User_information">
                            <div class="form-group">
                                <label for="fio">ФИО:</label>
                                <input type="text" id="fio" name="fio" value="{{ $user->fio }}">
                            </div>
                            <div class="form-group">
                                <label for="email">Почта:</label>
                                <input type="email" id="email" name="email" value="{{ $user->email }}">
                            </div>
                            <div class="form-group">
                                <label for="region">Регион:</label>
                                <div class="select_wrp">
                                    <select class="js-select-region" placeholder="Выберите регион"
                                    style="min-width: 230px;">
                                    <option value="">Выберите регион</option>
                                    <option value=""></option>
                                </select>
                                <input type="hidden" name="region" value="{{ $user->region }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Телефон:</label>
                            <input type="text" data-mask="phone" id="phone" name="phone" class="phone"
                            placeholder="+7" value="{{ formatPhoneNumber($user->phone) }}">
                        </div>
                        <div class="form-group">
                            <label for="second_phone">Дополнительный телефон:</label>
                            <input type="text" data-mask="second_phone" id="second_phone" class="second_phone"
                            name="second_phone" placeholder="+7"
                            value="{{ formatPhoneNumber($user->second_phone) }}">
                        </div>
                        <div class="form-group">
                            <label for="home_phone">Домашний телефон:</label>
                            <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="home_phone" maxlength="10" max="10" oninput="this.value=this.value.replace(/\D/g,'')" value="{{ formatHomePhoneNumber($user->home_phone) }}">
                        </div>
                        <div class="button_form">
                            <div class="form-group-date">
                                <label>Дата регистрации:</label>
                                <span>{{ Date::parse($user->data_reg)->format('d.m.Y') }}</span>
                            </div>
                            <button type="submit">Изменить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
                <!-- <div class="Float_right_coop">
            <div class="Position_user_information">
                <span class="FIO">Мои кооперативы:</span>
                <div class="User_information_coop">
                    <ul>
                        @forelse($myCoops as $myCoop)
                    <li><a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $myCoop->id_coop])}}">{{$myCoop->name}}</a></li>

                @empty
                    <a href="{{route('ChairmanMyCoop.index')}}">Создать первый гаражный коорператив</a>

                @endforelse
                </ul>
            </div>
        </div>
    </div> -->
</div>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputElements = document.querySelectorAll('[data-mask="phone"], [data-mask="second_phone"]');
        const maskOptions = {
            mask: '+{7}(000)000-00-00'
        };

        inputElements.forEach(inputElement => {
            IMask(inputElement, maskOptions);
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
       $('.js-select-region').change(function() {
        var selectedRegion = $(this).val();
        $('input[name="region"]').val(selectedRegion);
    });
       $('#load-more').on('click', function () {
        noEmty = false;
        ajaxSelectCoop();
    });

       $('.js-select-region').select2({
        placeholder: "{{ $user->region }}",
        language: "ru"
    });

       if (typeof cities !== 'undefined' && Array.isArray(cities)) {
        populateRegions(cities);
    } else {
        console.error('Данные о городах не загружены или данные не являются массивом.');
    }

            // Функция для заполнения <select> регионами
    function populateRegions(data) {
        const selectRegion = $('.js-select-region');
        const uniqueSubjects = new Set();

        data.forEach(item => {
            uniqueSubjects.add(item.subject);
        });

        uniqueSubjects.forEach(subject => {
            const option = $('<option></option>').val(subject).text(subject);
            selectRegion.append(option);
        });
    }

            // Обработчик события 'change' для выбора региона
    $('.js-select-region').on('change', function () {
        selectedRegion = $(this).val();
    });
});
</script>
<script>
    $(document).ready(function () {
        $('.Position_user_information input').on('input', function () {
            if ($(this).val().trim() === '') {
                $(this).removeClass('valid').addClass('invalid');
            } else {
                $(this).removeClass('invalid').addClass('valid');
            }
        });
    });

</script>
@endsection
