@extends('layouts.mainChairman', ['ProfileCoopPaymentOther' => ['paymentOther.css', 'scroll.css']])

@section('profile')
@php($months = ['01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
'05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
'10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'])
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
        <a href="{{route('ChairmanMyCoopPaymentOther.index', ['idCoop' => $idCoop])}}" class="active">Оплата сборов</a>
    </div>
    <div class="main_body">
        <div class="flex_container">
            <div class="container_mouth">
                @foreach ($months as $month => $key)
                <button class="id_month" data-month="{{$month}}">{{$key}}</button>
                @endforeach
            </div>
            <div class="main_table">
                <div class="head_table_block">
                    <div class="button_form_ajax">
                        <div class="block_1">
                            <span>Таблица сборов</span>
                            <button class="last_year"><</button>
                            <span class="year">{{$year}}</span>
                            <button class="next_year">></button>
                            <div class="name_table_payment">
                                <select id="paymentType" name="payment_type">
                                    <option value="membership_fee" data-id="2">Членские взносы (Общий сбор)</option>
                                    <option value="target_fee" data-id="3">Целевые взносы</option>
                                    <option value="water_fee" data-id="4">Оплата за воду</option>
                                    <option value="security_fee" data-id="5">Оплата за охрану</option>
                                    <option value="cleaning_fee" data-id="6">Оплата за уборку и содержание территории</option>
                                    <option value="maintenance_fee" data-id="7">Оплата за ремонт и техническое обслуживание</option>
                                    <option value="utilities_fee" data-id="8">Оплата на хозяйственные нужды</option>
                                    <option value="land_fee" data-id="9">Платежи за землю</option>
                                    <option value="construction_fee" data-id="10">Строительные взносы</option>
                                    <option value="reserve_fund" data-id="11">Фонд резервного капитала</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="body_table_block">
                    @foreach($usersCoop as $user)
                    <?php
                        $randomNumber = rand(0, 999);
                        $randomLetter = chr(rand(97, 122));
                        $randomNumberFormatted = sprintf("%03d", $randomNumber);
                        $randomString = $randomLetter . $randomNumberFormatted;
                    ?>
                    <form method="post" action="" class="form_payment"
                    id="stringNumber{{$randomString}}Code{{$user->id}}{{$randomLetter}}PiPgarageNumber">
                    @csrf
                    <div class="block_payment">
                        <div class="blocks_flex_content">
                            <div class="image_avatar_block">
                                <img src="{{asset('image/user/defaultUser.jpg')}}">
                            </div>
                            <div class="flex_column_container">
                                <div class="head_container">
                                    <span class="head_container_fio">{{$user->fio}}</span>
                                    <span class="payment_inf_user_garage">Номер блока | гаража: {{$user->number_block}}/{{$user->number_garage}}</span>
                                    <input type="hidden" name="table_user_number_garage" value="{{$user->garage_id}}">
                                </div>
                                <div class="body_container">
                                    <span>Оплатил:</span>
                                    <input type="tel" pattern="[0-9]{1,10}" title="Только цифры"
                                    name="tariff_value"
                                    id="tariff_value"
                                    maxlength="10"
                                    oninput="this.value=this.value.replace(/\D/g,'')"
                                    value="{{ $user->payment_value }}">
                                    <span>Руб.</span>
                                </div>
                                <div class="footer_container">
                                    <button type="submit" class="button_save">Сохранить</button>
                                </div>
                            </div>
                        </div>
                        <div class="block_message">
                            <div class="error-message" style="display: none;"></div>
                            <div class="success-message" style="display: none;"></div>
                        </div>
                    </div>
                </form>
                @endforeach
            </div>
        </div>
        <div class="flex_container_right_block">
            <div class="right_head_block">
                <div class="input_search_block">
                    <input type="text" name="search_fio" id="search_fio" placeholder="Фамилия / Имя / Отчество">
                    <!--  <button id="find_in_list">Найти</button> -->
                </div>
                <div class="block_2">
                    <span>Список участников: </span>
                    <button id="minus"><</button>
                    <span id="number_list"></span>
                    <button id="plus">></button>
                    <button id="reset" title="Сбросить выделенное"><img src="{{asset('icons/user/reset.svg')}}">
                    </button>
                </div>
                <div class="block_3">
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-1" checked="checked" name="payment" type="radio"
                        style="display: none;"/>
                        <label class="rbx" for="rbx-1">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Смешанно (По умолчанию)</span>
                        </label>
                    </div>
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-2" name="payment" type="radio" style="display: none;"/>
                        <label class="rbx" for="rbx-2">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Оплатили</span>
                        </label>
                    </div>
                    <div class="radio-wrapper-15">
                        <input class="inp-rbx" id="rbx-3" name="payment" type="radio" style="display: none;"/>
                        <label class="rbx" for="rbx-3">
                            <span>
                                <svg width="12px" height="9px" viewbox="0 0 12 9">
                                    <polyline points="1 5 4 8 11 1"></polyline>
                                </svg>
                            </span>
                            <span>Не оплатили</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="container_users">
                @forelse($usersCoop as $user)
                <div class="flex_fio_check">
                    <div class="fio_user">
                        <button data-id="{{$user->id}}">{{$user->fio}}</button>
                        <span class="inf_user_garage">№ блока | гаража: {{$user->number_block}}/{{$user->number_garage}}</span>
                        <input type="hidden" name="table_user_number_garage" value="{{$user->garage_id}}">
                    </div>
                    <div class="checkbox-wrapper-4">
                        <input class="inp-cbx" id="morning{{$user->garage_id}}" type="checkbox"/>
                        <label class="cbx" for="morning{{$user->garage_id}}"><span>
                            <svg width="12px" height="10px">
                                <use xlink:href="#check-4"></use>
                            </svg></span><span><!-- Text --></span></label>
                            <svg class="inline-svg">
                                <symbol id="check-4" viewbox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </symbol>
                            </svg>
                        </div>
                    </div>
                    @empty
                    <span class="empty_message">В кооперативе нет участников</span>
                    @endforelse
                </div>
            </div>
            <div class="flex_container_right_block_garages">
                <div class="right_head_block_garages">
                    <div class="span_text_garages">
                        <span>Список гаражей:</span>
                    </div>
                    <div class="overflow_block_garages">
                        @forelse($blocksWithGarages as $numberBlock => $garages)
                        <div class="number_garage_block">
                            <span>Гаражный блок №{{ $numberBlock }}</span>
                            <div class="flex_container_garages">
                                @foreach($garages as $garage)
                                <div class="number_garage">
                                    <button>{{ $garage->number_garage }}</button>
                                    <input type="hidden" name="table_user_number_garage" value="{{ $garage->garage_id }}">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <span class="empty_message">В кооперативе нет гаражных блоков</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text_description">
        <div class="coop-payments">
            <h2>Виды оплат для участников гаражного кооператива</h2>
            <p>
                Участники гаражного кооператива могут вносить различные платежи, которые необходимы для поддержания работы и развития кооператива. 
                Важно отметить, что эти виды оплат не являются обязательными для всех кооперативов. Структура и необходимость взносов зависят от конкретных решений вашего кооператива и его устава.
            </p>
            <div class="payment-type">
                <h3>Членские взносы (Общий сбор)</h3>
                <p>Это регулярные платежи, которые направлены на покрытие общих расходов кооператива: административные расходы, оплата труда сотрудников и содержание инфраструктуры.</p>
            </div>
            <div class="payment-type">
                <h3>Целевые взносы</h3>
                <p>Средства, собираемые на определенные проекты или улучшения, такие как строительство новых объектов, ремонт дорог или установка освещения.</p>
            </div>
            <div class="payment-type">
                <h3>Оплата за воду</h3>
                <p>В ближайшем будущем вода станет важной статьей расходов, подобно оплате за электричество. Это особенно актуально для кооперативов, где предусмотрено водоснабжение для хозяйственных нужд, таких как мойка автомобилей. Подробности и условия будут установлены кооперативом в ближайшее время.</p>
            </div>
            <div class="payment-type">
                <h3>Оплата за охрану</h3>
                <p>Взносы, направленные на обеспечение безопасности: оплата работы охраны, установка систем видеонаблюдения и других мер по защите территории кооператива.</p>
            </div>
            <div class="payment-type">
                <h3>Оплата за уборку и содержание территории</h3>
                <p>Платежи, покрывающие регулярные работы по поддержанию чистоты и порядка: уборка территории, вывоз мусора, расчистка снега и благоустройство.</p>
            </div>
            <div class="payment-type">
                <h3>Оплата за ремонт и техническое обслуживание</h3>
                <p>Средства на капитальный и текущий ремонт инфраструктуры, включая дороги, освещение и ограждения.</p>
            </div>
            <div class="payment-type">
                <h3>Оплата на хозяйственные нужды</h3>
                <p>Платежи, которые идут на поддержание общего имущества кооператива и удовлетворение хозяйственных нужд. Это могут быть расходы на закупку инвентаря, материалы для уборки, поддержание работы инженерных систем и прочие потребности, связанные с повседневной эксплуатацией территории и объектов кооператива.</p>
            </div>
            <div class="payment-type">
                <h3>Платежи за землю</h3>
                <p>В некоторых кооперативах может взиматься оплата за аренду или выкуп земли, на которой располагаются гаражи.</p>
            </div>
            <div class="payment-type">
                <h3>Строительные взносы</h3>
                <p>Взносы на строительство новых объектов, например, дополнительных гаражей или складов, утвержденные кооперативом.</p>
            </div>
            <div class="payment-type">
                <h3>Фонд резервного капитала</h3>
                <p>Резервный фонд создается для покрытия непредвиденных расходов, таких как аварийные ремонты или чрезвычайные ситуации, что обеспечивает финансовую стабильность кооператива.</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        let dataIdTypePayment = 2;
        let currentPage = 1;
        let usersPerPage = 8;
        let selectedId = "rbx-1";
        let $usersContainer = $('.container_users');
        let $usersButtons = $usersContainer.find('.flex_fio_check');
        let totalPages = Math.ceil($usersButtons.length / usersPerPage);
        let usersSelected = [];
        let garagesSelected = [];
        let $filteredButtons = $usersButtons;

            // Функция для показа пользователей на текущей странице
        function showPage(page) {
            let startIndex = (page - 1) * usersPerPage;
            let endIndex = startIndex + usersPerPage;
                $usersButtons.hide(); // Сначала скрываем все кнопки
                $filteredButtons.slice(startIndex, endIndex).show(); // Показываем отфильтрованные кнопки для текущей страницы
                $('#number_list').text(page + '/' + totalPages);
            }

            // Инициализация показа первой страницы
            showPage(currentPage);

            // Обработчик нажатия кнопки вперед
            $('#plus').click(function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    showPage(currentPage);
                }
            });

            // Обработчик нажатия кнопки назад
            $('#minus').click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                }
            });
            $('.block_3 .inp-rbx').change(function () {
                selectedId = $(this).attr('id');
                //var selectedText = $('label[for="' + selectedId + '"] span:last-child').text();
                findUsers(selectedId, usersSelected);

            });
            // Обработчик ввода в поле поиска
            $('#search_fio').on('input', function () {
                var searchValue = $(this).val().toLowerCase();
                $filteredButtons = $usersButtons.filter(function () {
                    return $(this).find('button').text().toLowerCase().includes(searchValue);
                });
                totalPages = Math.ceil($filteredButtons.length / usersPerPage);
                currentPage = 1;
                showPage(currentPage);
            });
            // Обработчик нажатия кнопки сброса
            $('#reset').click(function () {
                $('.flex_fio_check button').css({
                    backgroundColor: '',
                    color: 'black',
                });
                $('.number_garage_block button').css({
                    backgroundColor: 'rgb(28, 130, 231)',
                    color: 'white',
                    border: 'none'
                });
                $('.body_table_block .form_payment').show();
                garagesSelected.length = 0;
                $('#search_fio').val('');
                $filteredButtons = $usersButtons;
                totalPages = Math.ceil($filteredButtons.length / usersPerPage);
                currentPage = 1;
                $('.inp-cbx').prop('checked', false);
                $('#rbx-1').prop('checked', true);
                selectedId = 'rbx-1';
                showPage(currentPage);
            });
            $(document).on('click', '.container_users button', function (event) {
                $('.flex_fio_check button').css({
                    backgroundColor: '',
                    color: 'black',
                });
                if ($('.inp-cbx').is(':checked')) {
                    $('.inp-cbx:checked').closest('.flex_fio_check').find('button').css({
                        backgroundColor: '#33B168',
                        color: 'white'
                    });
                }
                let numberGarageUser = $(this).closest('.fio_user').find('input[name="table_user_number_garage"]').val();
                let numberGarageBlockUserCSS = $('.number_garage').filter(function() {
                    return $(this).find('input[name="table_user_number_garage"]').val() === numberGarageUser;
                }).find('button');
                numberGarageBlockUserCSS.css({
                    backgroundColor: 'rgb(28, 130, 231)',
                    color: 'white',
                    border: 'none'
                });
                garagesSelected.push(numberGarageUser);
                findUsers(selectedId, garagesSelected);
                let index = garagesSelected.indexOf(numberGarageUser);
                if (index !== -1) {
                    garagesSelected.splice(index, 1);
                }
                $(this).closest('.flex_fio_check').find('button').css({
                    backgroundColor: '#33B168',
                    color: 'white',
                });

            });

            $(document).on('click', '.number_garage_block button', function (event) {
                $('.flex_fio_check button').css({
                    backgroundColor: '',
                    color: 'black',
                });
                $('.number_garage_block button').css({
                    backgroundColor: 'rgb(28, 130, 231)',
                    color: 'white',
                    border: 'none'
                });
                $(this).css({
                    backgroundColor: '#33B168',
                    color: 'white',
                    border: '2px solid black'
                });
                let numberGarageUser = $(this).closest('.number_garage').find('input[name="table_user_number_garage"]').val();
                garagesSelected.push(numberGarageUser);
                findUsers(selectedId, garagesSelected);
                let index = garagesSelected.indexOf(numberGarageUser);
                if (index !== -1) {
                    garagesSelected.splice(index, 1);
                }
            });
            function findUsers(selectedId, garagesSelected) {
                // Сначала скрываем все формы
                $('.body_table_block .form_payment').hide();
                $('.flex_fio_check button').css({
                    backgroundColor: '',
                    color: 'black',
                });

                if ($('.inp-cbx').is(':checked')) {
                    $('.inp-cbx:checked').closest('.flex_fio_check').find('button').css({
                        backgroundColor: '#33B168',
                        color: 'white'
                    });
                }
                // Перебираем каждую форму
                $('.body_table_block .form_payment').each(function () {
                    let numberGarageUser = $(this).find('.head_container input[name="table_user_number_garage"]').val();

                    let $paymentInput = $(this).find('[name="tariff_value"]');
                    let paymentValue = $paymentInput.val();
                    if (paymentValue === "0") {
                        $paymentInput.val(null);
                        paymentValue = '';
                    }
                    // Показываем формы, если нет выбранных пользователей
                    if (garagesSelected.length === 0) {
                        $('.flex_fio_check button').css({
                            backgroundColor: '',
                            color: 'black',
                        });
                        if (selectedId === "rbx-1") {
                            $(this).show();
                        } else if (selectedId === "rbx-2" && paymentValue) {
                            $(this).show();
                        } else if (selectedId === "rbx-3" && !paymentValue) {
                            $(this).show();
                        }
                    } else {
                        // Показываем формы, соответствующие выбранным пользователям
                        if (garagesSelected.includes(numberGarageUser)) {
                            if (selectedId === "rbx-1") {
                                $(this).show();
                            } else if (selectedId === "rbx-2" && paymentValue) {
                                $(this).show();
                            } else if (selectedId === "rbx-3" && !paymentValue) {
                                $(this).show();
                            }
                        }
                    }
                });

            }

            $('.inp-cbx').on('change', function () {
                let css = $(this).closest('.flex_fio_check').find('button');
                let numberGarageUser = $(this).closest('.flex_fio_check').find('input[name="table_user_number_garage"]').val();
                let numberGarageBlockUserCSS = $('.number_garage').filter(function() {
                    return $(this).find('input[name="table_user_number_garage"]').val() === numberGarageUser;
                }).find('button');
                if ($(this).is(':checked')) {
                    css.css({
                        backgroundColor: '#33B168',
                        color: 'white',
                    });
                    numberGarageBlockUserCSS.css({
                        backgroundColor: '#33B168',
                        color: 'white',
                    });
                    garagesSelected.push(numberGarageUser);
                } else {
                    css.css({
                        backgroundColor: '',
                        color: 'black',
                    });
                    numberGarageBlockUserCSS.css({
                        backgroundColor: '',
                        color: 'white',
                    });
                    let indexGarage = garagesSelected.indexOf(numberGarageUser);

                    if (indexGarage !== -1) {
                        garagesSelected.splice(indexGarage, 1);
                    }
                }
                findUsers(selectedId, garagesSelected);
            });


            function searchPaymentValue() {
                $('.body_table_block .form_payment').each(function () {
                    let fioUser = $(this).find('.head_container .head_container_fio').text();
                    let paymentValue = $(this).find('input[name="tariff_value"]').val();
                    let numberGarageUser = $(this).find('.head_container input[name="table_user_number_garage"]').val();

                    $('.container_users .flex_fio_check').each(function () {
                        let userFio = $(this).find('.fio_user button').text();
                        let userNumberGarage = $(this).find('.fio_user input[name="table_user_number_garage"]').val();

                        if (userNumberGarage === numberGarageUser && paymentValue) {
                            $(this).find('.fio_user #inf_value_payment').remove();
                            $(this).find('.fio_user input[name="table_user_number_garage"]').after(`<span id="inf_value_payment">Оплатил: ${paymentValue}</span>`);
                        }
                    });
                });
            }


            searchPaymentValue();
            $('.next_year').prop("disabled", true);
            $('.id_month[data-month="{{$monthNow}}"]').prop("disabled", true);
            let isSubmitMyBlock = false;
            let previousButton; // Переменная для хранения предыдущей кнопки
            let lastClickTime = 0; // Переменная для хранения времени последнего нажатия
            let clickCount = 0; // Счетчик нажатий
            let currentTime = new Date().getTime();
            let timeDifference = currentTime - lastClickTime;
            let currentYear;
            let idMonth = '0' + {{$monthNow}};
            let currentYearInput = {{$year}};

            $('.id_month[data-month={{$monthNow}}]').css({
                backgroundColor: '#1C82E7',
                color: 'white',
            });

            var currentMonth = new Date().getMonth(); // Получаем текущий месяц (от 0 до 11)
            // Создаем массивы месяцев и их номеров
            let months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            let numberMonths = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

            function sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment) {
                $.ajax({
                    url: '{{route('ChairmanMyCoopPaymentOther.index', ['idCoop' => $idCoop])}}',
                    type: "GET",
                    data: {
                        id_year: currentYearInput,
                        id_month_number: idMonth,
                        type_payment: dataIdTypePayment,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('.body_table_block').empty();
                        // Блокируем новую кнопку
                        $('.id_month').prop("disabled", false);
                        let currentButton = $(`.id_month[data-month='${response.monthNow}']`).prop("disabled", true);
                        currentButton.css({
                            backgroundColor: '#1C82E7',
                            color: 'white',
                        });
                        // Сохраняем текущую кнопку как предыдущую
                        previousButton = currentButton;
                        // Обновляем время последнего нажатия и увеличиваем счетчик
                        lastClickTime = new Date().getTime();
                        clickCount++;
                        let blockMessages = response.blockMessages;
                        // Перебираем все месяцы
                        for (var i = 0; i < blockMessages.length; i++) {
                            var randomString = getRandomString();
                            var formId = 'stringNumber' + randomString + 'Code' + blockMessages[i].id + randomString.charAt(0) + 'PiPgarageNumber';
                            // Создаем общую структуру формы с CSRF-токеном
                            var html = `<form method="post" action="" class="form_payment" id="${formId}">
                            @csrf
                            <div class="block_payment">
                            <div class="blocks_flex_content">
                            <div class="image_avatar_block">
                            <img src="{{asset('image/user/defaultUser.jpg')}}">
                            </div>
                            <div class="flex_column_container">
                            <div class="head_container">
                            <span class="head_container_fio">${blockMessages[i].fio}</span>
                            <span class="payment_inf_user_garage">Номер блока | гаража: ${blockMessages[i].number_block}/${blockMessages[i].number_garage}</span>
                            <input type="hidden" name="table_user_number_garage" value="${blockMessages[i].garage_id}">
                            </div>
                            <div class="body_container">
                            <span>Оплатил:</span>
                            <input type="text" pattern="[0-9]{1,10}"  title="Только цифры" name="tariff_value" id="tariff_value"
                            maxlength="10" oninput="this.value=this.value.replace(/\\D/g,'')"
                            value="${blockMessages[i].payment_value ? blockMessages[i].payment_value : ''}">
                            <span>Руб.</span>
                            </div>
                            <div class="footer_container">
                            <button type="submit" class="button_save">Сохранить</button>
                            </div>
                            </div>
                            </div>
                            <div class="block_message">
                            <div class="error-message" style="display: none;"></div>
                            <div class="success-message" style="display: none;"></div>
                            </div>
                            </div>
                            </form>`;
                            $('.body_table_block').append(html);

                        }
                        findUsers(selectedId, usersSelected);
                        $('.container_users .flex_fio_check').each(function () {
                            $(this).find('.fio_user #inf_value_payment').remove();
                        });
                        searchPaymentValue();
                    },
                    error: function (error) {
                        $('.id_month').prop("disabled", false);
                        let textError = error.responseJSON.error;
                        $('.body_table_block').html(`<span style="font-size: 23px">${textError}</span>`);
                    }
                });
            }

$('.last_year, .next_year').click(function (e) {
    let currentTime = new Date().getTime();
    let timeDifference = currentTime - lastClickTime;
    currentYearInput = parseInt($('.year').text());
    $('.body_table_block').empty();
    $('.body_table_block').html('<span style="font-size: 23px">Подождите, запрос выполняется...</span>');
    if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
        $(".body_table_block").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

                    // Блокируем кнопки на 3 секунды
        $('.last_year, .next_year, .id_month').prop("disabled", true);

        setTimeout(function () {
            $('.next_year').prop("disabled", false);
            if (currentYearInput === 2023) {
                $('.last_year').prop("disabled", true);
            } else {
                $('.last_year').prop("disabled", false);
            }
            if (currentYearInput === {{$year}} || currentYearInput >= {{$year}}) {
                $('.next_year').prop("disabled", true);
            } else {
                $('.next_year').prop("disabled", false);
            }

            sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
        }, 3000);
        clickCount = 0;
    } else {
        // Сбрасываем счетчик, если прошло более 1 секунды с предыдущего нажатия
        if (timeDifference >= 500) {
            clickCount = 0;
        }
        clickCount++;
        if ($(this).hasClass('last_year')) {
                        // Если нажата кнопка "last_year"
                        currentYear = Math.max(currentYearInput - 1, 2022); // Ограничение до 2020
                        currentYearInput = Math.max(currentYearInput - 1, 2022); // Ограничение до 2020
                        sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
                        if (currentYearInput === 2023) {
                            $('.last_year').prop("disabled", true);
                        } else {
                            $('.last_year').prop("disabled", false);
                        }
                        $('.next_year').prop("disabled", false);
                    } else {
                        $('.last_year').prop("disabled", false);
                        // Если нажата кнопка "next_year"
                        currentYear = currentYearInput + 1;
                        currentYearInput = currentYearInput + 1;
                        sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
                        if (currentYearInput === {{$year}} || currentYearInput >= {{$year}}) {
                            $('.next_year').prop("disabled", true);
                        } else {
                            $('.next_year').prop("disabled", false);
                        }
                        $('.last_year').prop("disabled", false);

                    }
                    return $('.year').text(currentYear) + currentYearInput;

                }
            });

$('#paymentType').on('change', function() {
    let selectedOption = $(this).find('option:selected');
    dataIdTypePayment = selectedOption.data('id');
    $('.body_table_block').empty();
    $('.body_table_block').html('<span style="font-size: 23px">Подождите, запрос выполняется...</span>');
    sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
});
$('.id_month').click(function (e) {
    e.preventDefault();
    $('.id_month').css({
        backgroundColor: 'white',
        color: 'black'
    });
    $(this).css({
        backgroundColor: '#1C82E7',
        color: 'white'
    });
    $('.id_month').prop("disabled", true);
    idMonth = $(this).data('month');
    isSubmitMyBlock = true;
    $('.body_table_block').empty();
    $('.body_table_block').html('<span style="font-size: 23px">Подождите, запрос выполняется...</span>');
    let currentTime = new Date().getTime();
    let timeDifference = currentTime - lastClickTime;
                // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
    if (timeDifference < 2000 && clickCount > 5) {
                    // Отображаем сообщение об ошибке
        $(".body_table_block").html(`<span style="font-size: 23px">Ошибка: Слишком много запросов. Пожалуйста, подождите.</span>`);
                    // Блокируем кнопки на 3 секунды
        $('.last_year, .next_year, .id_month').prop("disabled", true);

        setTimeout(function () {
            $('.last_year, .next_year, .id_month').prop("disabled", false);
            if (currentYearInput === 2023) {
                $('.last_year').prop("disabled", true);
            } else {
                $('.last_year').prop("disabled", false);
            }
            sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
            clickCount = 0;

        }, 3000);

    } else {
                    // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
        if (timeDifference >= 1000) {
            clickCount = 0;
        }
                    // Снимаем блокировку с предыдущей кнопки, если она существует
        if (previousButton) {
            previousButton.prop("disabled", false);
        }

        if (isSubmitMyBlock === true) {
            sendAjaxRequestPayment(currentYearInput, idMonth, dataIdTypePayment);
        }
    }
});

function messageBlock(text, form, bool) {
    if (bool == false) {
        form.closest('.form_payment').find('.error-message').html(text).slideDown(500);

        setTimeout(function () {
            form.closest('.form_payment').find('.error-message').slideUp(500);
            form.closest('.form_payment').find('.error-message').html();
            form.closest('.form_payment').find('.button_save').prop("disabled", false);
        }, 3000);
    } else {
        form.closest('.form_payment').find('.success-message').html(text).slideDown(500);

        setTimeout(function () {
            form.closest('.form_payment').find('.success-message').slideUp(500);
            form.closest('.form_payment').find('.success-message').html();
            form.closest('.form_payment').find('.button_save').prop("disabled", false);
        }, 3000);
    }
}

function sendAjaxRequestPaymentPost(paymentValue, numbers, currentYearInput, idMonth, form, id_number_garage, dataIdTypePayment) {
    $.ajax({
        url: '{{route('ChairmanMyCoopPaymentOtherPost.store', ['idCoop' => $idCoop])}}',
        type: "POST",
        data: {
            id_user: numbers,
            id_garage: id_number_garage,
            payment_value: paymentValue,
            id_year: currentYearInput,
            id_month_number: idMonth,
            type_payment: dataIdTypePayment,
            _token: '{{ csrf_token() }}',
        },
        success: function (response) {
            if (response.error) {
                messageBlock("Не удалось отправить запрос", form, false);
                return;
            }
            messageBlock(response.success, form, true);
            searchPaymentValue();
        },
        error: function (error) {
            let errorText = error.responseJSON.error
            $('.body_table_block').html(`<span style="font-size: 23px">${errorText}</span>`);
            
        }
    });
}

function getRandomString() {
    var randomNumber = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    var randomLetter = String.fromCharCode(97 + Math.floor(Math.random() * 26));
    return randomLetter + randomNumber;
}

$(document).on('submit', '.form_payment', function (e) {
    e.preventDefault();
    let form = $(this);
    form.closest('.form_payment').find('.button_save').prop("disabled", true);
    let paymentValue = $(this).closest('.form_payment').find('[name="tariff_value"]').val();
    let idUser = $(this).closest('.form_payment').find('[name="user_id"]').val();
    let id_number_garage = $(this).closest('.form_payment').find('input[name="table_user_number_garage"]').val();
        // Получение числовой части id
    let formId = form.attr('id');
    let match = formId.match(/stringNumber(\w+)Code(\d+)/);
    let numbers = null;

                // Если числовая часть найдена, извлекаем её
    if (match && match.length > 2) {
        numbers = match[2];
    }


    sendAjaxRequestPaymentPost(paymentValue, numbers, currentYearInput, idMonth, form, id_number_garage, dataIdTypePayment);
                /*
                        isSubmitMyBlock = true;
                        $('.body_table_block').empty();
                        $('.body_table_block').html('Подождите, запрос выполняется...');
                        let currentTime = new Date().getTime();
                        let timeDifference = currentTime - lastClickTime;
                            // Если прошло менее 1 секунд с предыдущего нажатия и количество нажатий больше 3
                        if (timeDifference < 2000 && clickCount > 5) {
                                // Отображаем сообщение об ошибке
                            $(".body_table_block").append("Ошибка: Слишком много запросов. Пожалуйста, подождите.");
                                // Блокируем кнопки на 3 секунды
                            $('.last_year, .next_year, .id_month').prop("disabled", true);

                            setTimeout(function () {
                                $('.last_year, .next_year, .id_month').prop("disabled", false);
                                if (currentYearInput === 2023) {
                                    $('.last_year').prop("disabled", true);
                                } else {
                                    $('.last_year').prop("disabled", false);
                                }
                                sendAjaxRequestPayment(currentYearInput, idMonth);
                                clickCount = 0;

                            }, 3000);

                        } else {
                                // Сбрасываем счетчик, если прошло более 1 секунд с предыдущего нажатия
                            if (timeDifference >= 1000) {
                                clickCount = 0;
                            }
                                // Снимаем блокировку с предыдущей кнопки, если она существует
                            if (previousButton) {
                                previousButton.prop("disabled", false);
                            }
                            e.preventDefault();

                            if (isSubmitMyBlock === true) {
                                sendAjaxRequestPayment(currentYearInput, idMonth);
                            }
                        }*/
});
});
</script>
@endsection
