@extends('layouts.mainChairman', ['ParticipantsCoopStyles' => ['participantsCoop.css', 'scroll_coop.css']])

@section('profile')
    <script src="{{ asset('js/NumberMask/imask.js') }}"></script>

    <div class="main_block_coop">
        <div class="main_head">
            <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
            <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
            <a href="{{route('ChairmanMyCoopParticipants.index', ['idCoop' => $coopData -> id_coop])}}" class="active">Участники</a>
        </div>
        <div class="main_body">
            <div class="block_search_user">
                <div class="block_input">
                    <label>ФИО</label>
                    <div>
                        <input type="text" name="fio" value="" required>
                    </div>
                </div>
                <div class="block_input">
                    <label>Номер телефона</label>
                    <div>
                        <input type="text" data-mask="phone" id="phone" class="phone" required placeholder="+7">
                    </div>
                </div>
                <div class="block_input">
                    <label>Номер ряда</label>
                    <div>
                        <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage_blocks"
                               maxlength="2" max="10" oninput="this.value=this.value.replace(/\D/g,'')" value=""
                               required="">
                    </div>
                </div>
                <div class="block_input">
                    <label>Номер гаража</label>
                    <div>
                        <input type="tel" pattern="[0-9]{1,10}" title="Только цифры" name="number_garage_blocks"
                               maxlength="4" max="100" oninput="this.value=this.value.replace(/\D/g,'')" value=""
                               required="">
                    </div>
                </div>
                <button id="search_user">Найти</button>
                <button id="clear_result">Очистить всё</button>
            </div>
            <div class="error_not_found" style="display: none;">
                <span>Участник не найден</span>
            </div>
            <div class="result_search_user" style="display: none;">
                <div class="nubmer_block_center">
                    <span>Найденные участники</span>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>ФИО</th>
                        <th>Номер телефона</th>
                        <th>Второй номер телефона</th>
                        <th>Домашний телефон</th>
                        <th>Номер ряда</th>
                        <th>Почта</th>
                        <th>Гаражи</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            @if($notGarageArrayUsers)
                <div class="table_block_users">
                    <div class="nubmer_block_center">
                        <span>Участники без гаража</span>
                    </div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>№</th>
                            <th>ФИО</th>
                            <th>Номер телефона</th>
                            <th>Второй номер телефона</th>
                            <th>Домашний телефон</th>
                            <th>Почта</th>
                            <th>Гаражи</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $i = 0; @endphp
                        @foreach($notGarageArrayUsers as $user)
                            @php $i++; @endphp
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{ $user['fio'] }}</td>
                                <td>{{ formatPhoneNumber($user['phone']) }}</td>
                                <td>{{ formatPhoneNumber($user['second_phone']) }}</td>
                                <td>{{ formatHomePhoneNumber($user['home_phone']) }}</td>
                                <td>{{ $user['email'] }}</td>
                                <td>Без гаража</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="flex_block_center">
                @foreach($garageBlocks as $block)
                    <div class="table_block_users">
                        @if(isset($usersGroupedByBlocks[$block->number_block]) && count($usersGroupedByBlocks[$block->number_block]) > 0)
                            <div class="nubmer_block_center">
                                <span>Гаражный ряд №{{ $block->number_block }}</span>
                            </div>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>№</th>
                                    <th>ФИО</th>
                                    <th>Номер телефона</th>
                                    <th>Второй номер телефона</th>
                                    <th>Домашний телефон</th>
                                    <th>Почта</th>
                                    <th>Гаражи</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($usersGroupedByBlocks[$block->number_block] as $key => $user)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $user['fio'] }}</td>
                                        <td>{{ formatPhoneNumber($user['phone']) }}</td>
                                        <td>{{ formatPhoneNumber($user['second_phone']) }}</td>
                                        <td>{{ formatHomePhoneNumber($user['home_phone']) }}</td>
                                        <td>{{ $user['email'] }}</td>
                                        <td>
                                            @foreach($user['garages'] as $garage)
                                                № гаража: {{ $garage }}<br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            {{--<p>Нет участников</p>--}}
                        @endif
                    </div>
                @endforeach

                @if($emptyBlocks->isNotEmpty())
                    <span class="text_block">Гаражные блоки: {{ $emptyBlocks->pluck('number_block')->implode(', ') }} – нет участников.</span>
                @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputElement = document.querySelector('[data-mask="phone"]')
            const maskOptions = { // создаем объект параметров
                mask: '+{7}(000)000-00-00' // задаем единственный параметр mask
            }
            IMask(inputElement, maskOptions) // запускаем плагин с переданными параметрами
        });
        $(document).ready(function () {
            $('#search_user').on('click', function () {
                let fio = $('input[name="fio"]').val() ? $('input[name="fio"]').val().trim().toLowerCase() : '';
                let phone = $('#phone').val() ? $('#phone').val().trim().toLowerCase() : '';
                let numberBlock = $('input[name="number_garage_blocks"]').val() ? $('input[name="number_garage_blocks"]').val().trim() : '';
                let numberGarage = $('input[name="number_garage"]').val() ? $('input[name="number_garage"]').val().trim() : '';

                let foundUsers = [];

                $('.table_block_users').each(function () {
                    let blockSection = $(this);
                    let userBlockText = blockSection.find('.nubmer_block_center span').text().toLowerCase();
                    let userBlockMatch = userBlockText.match(/\d+/);
                    let userBlock = userBlockMatch ? userBlockMatch[0] : '';

                    blockSection.find('table tbody tr').each(function () {
                        let row = $(this);
                        let userFio = row.find('td:eq(1)').text().toLowerCase();
                        let userPhone = row.find('td:eq(2)').text().toLowerCase();
                        let userSecondPhone = row.find('td:eq(3)').text().toLowerCase();
                        let userHomePhone = row.find('td:eq(4)').text().toLowerCase();
                        let userEmail = row.find('td:eq(5)').text().toLowerCase();
                        let userGarages = row.find('td:eq(6)').html().toLowerCase(); // Используем .html() для сбора содержимого с тегами <br>

                        let fioMatch = fio === "" || userFio.includes(fio);
                        let phoneMatch = phone === "" || userPhone.includes(phone) || userSecondPhone.includes(phone) || userHomePhone.includes(phone);
                        let blockMatch = numberBlock === "" || userBlock.includes(numberBlock);
                        let garageMatch = numberGarage === "" || userGarages.includes(numberGarage);

                        if (fioMatch && phoneMatch && blockMatch && garageMatch) {
                            foundUsers.push({
                                userFio: row.find('td:eq(1)').text(),
                                userPhone: row.find('td:eq(2)').text(),
                                userSecondPhone: row.find('td:eq(3)').text(),
                                userHomePhone: row.find('td:eq(4)').text(),
                                userEmail: row.find('td:eq(5)').text(),
                                userGarages: row.find('td:eq(6)').html(), // Используем .html() для сбора содержимого с тегами <br>
                                userBlock: userBlock
                            });
                        }
                    });
                });

                let resultTable = $('.result_search_user table tbody');
                resultTable.empty();
                if (foundUsers.length > 0) {
                    $.each(foundUsers, function (index, userData) {
                        var userRow = `
                    <tr>
                    <td>${index + 1}</td>
                    <td>${userData.userFio}</td>
                    <td>${userData.userPhone}</td>
                    <td>${userData.userSecondPhone}</td>
                    <td>${userData.userHomePhone}</td>
                    <td>${userData.userBlock}</td>
                    <td>${userData.userEmail}</td>
                    <td>${userData.userGarages}</td>
                    </tr>
                    `;
                        resultTable.append(userRow);
                    });
                    $('.result_search_user').show();
                    $('.error_not_found').hide();
                } else {
                    $('.result_search_user').hide();
                    $('.error_not_found').show();
                }
            });

            $('#clear_result').on('click', function () {
                $('.error_not_found').hide();
                $('.result_search_user').hide();
                $('.result_search_user table tbody').empty();
                $('input[name="fio"]').val('');
                $('#phone').val('');
                $('input[name="number_garage_blocks"]').val('');
                $('input[name="number_garage"]').val('');
            });
        });

    </script>
@endsection
