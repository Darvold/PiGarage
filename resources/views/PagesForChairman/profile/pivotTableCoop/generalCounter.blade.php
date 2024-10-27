@extends('layouts.mainChairman', ['ProfileCoopGeneralCounter' => ['generalCounter.css']])

@section('profile')
<script>
 $(document).ready(function () {
    $(document).on('change', '#fileInput', function () {
        let fileUrl = URL.createObjectURL(this.files[0]);
        let imgTag = `<a href="${fileUrl}" class="input_img_user" data-lightbox="image" data-title="Загруженное изображение">
        <img src="${fileUrl}" alt="Загруженное изображение" id="lightbox-image">
        <img src="{{asset('icons/user/check_mark.svg')}}" class="check" alt="">
        </a>`;
        $('.img').html(imgTag);
    });
});
</script>
<div class="main_block_coop">
    <div class="main_head">
        <a href="{{route('ChairmanMyCoop.index')}}">Мои кооперативы</a>
        <a href="{{route('ChairmanMyCoopPivotTable.index', ['idCoop' => $idCoop])}}">{{$coopData->name}}</a>
        <a href="{{route('ChairmanMyCoopGeneralCounter.index', ['idCoop' => $idCoop])}}" class="active">Показания общего счётчика</a>
    </div>
    <div class="main_body">
        <div class="flex_column">
            <div class="container_mouth">
                <button class="id_month" data-month="01">Январь</button>
                <button class="id_month" data-month="02">Февраль</button>
                <button class="id_month" data-month="03">Март</button>
                <button class="id_month" data-month="04">Апрель</button>
                <button class="id_month" data-month="05">Май</button>
                <button class="id_month" data-month="06">Июнь</button>
                <button class="id_month" data-month="07">Июль</button>
                <button class="id_month" data-month="08">Август</button>
                <button class="id_month" data-month="09">Сентябрь</button>
                <button class="id_month" data-month="10">Октябрь</button>
                <button class="id_month" data-month="11">Ноябрь</button>
                <button class="id_month" data-month="12">Декабрь</button>
            </div>
            <div class="meter_indication">
                <div class="head_indication">
                    <div class="block_1">
                        <span>Показания</span>
                        <button class="last_year"><</button>
                        <span class="year">{{session('id_year') ?? date('Y')}}</span>
                        <button class="next_year">></button>
                    </div>
                    <div class="block_2">
                    </div>
                </div>
                <div class="block_4">
                    
                </div>
                <div class="body_indication">
                    <span class="request_fail_img"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
      // Блокируем кнопку на текущий месяц и след. год
      $('.next_year').prop("disabled", true);
      $('.id_month[data-month="{{$monthNow}}"]').prop("disabled", true).css({
          backgroundColor: '#1C82E7',
          color: 'white',
      });

      let selectedMonth = {{ session('id_month_number') ?? 'new Date().getMonth() + 1' }};
      let isSubmitMyBlock = false;
      let lastClickedButton = null; // Последняя нажатая кнопка
      let lastClickTime = 0; // Время последнего нажатия
      let clickCount = 0; // Счетчик нажатий
      let idMonth = '0' + {{$monthNow}};
      if ({{$monthNow}} > 9) {
        idMonth = {{$monthNow}};
      } else {
        idMonth = '0' + {{$monthNow}};
      }
      let currentYearInput = {{ session('id_year') ?? 'new Date().getFullYear()' }};

      $('.id_month').click(function (e) {
          e.preventDefault();

          // Очищаем стили всех кнопок, затем выделяем текущую
          $('.id_month').css({
              backgroundColor: 'white',
              color: 'black'
          });
          $(this).css({
              backgroundColor: '#1C82E7',
              color: 'white'
          });

          // Блокируем все кнопки
          $('.id_month').prop("disabled", true);
          idMonth = $(this).data('month');
          isSubmitMyBlock = true;

          // Проверка времени между нажатиями
          let currentTime = new Date().getTime();
          let timeDifference = currentTime - lastClickTime;

          if (timeDifference < 2000 && clickCount > 5) {
              // Если слишком много запросов, выводим ошибку и блокируем кнопки
              $(".body_table_block").html(`<span style="font-size: 23px">Ошибка: Слишком много запросов. Пожалуйста, подождите.</span>`);
              $('.last_year, .next_year, .id_month').prop("disabled", true);

              setTimeout(function () {
                  $('.last_year, .next_year, .id_month').prop("disabled", false);
                  // Блокируем прошлый год, если текущий год 2023
                  if (currentYearInput === 2023) {
                      $('.last_year').prop("disabled", true);
                  }
                  clickCount = 0; // Сбрасываем счетчик
              }, 3000);
          } else {
              // Если прошло достаточно времени, сбрасываем счетчик
              if (timeDifference >= 1000) {
                  clickCount = 0;
              }

              // Разблокируем предыдущую кнопку
              if (lastClickedButton) {
                  lastClickedButton.prop("disabled", false);
              }

              // Блокируем текущую кнопку
              $(this).prop("disabled", true);
              lastClickedButton = $(this); // Запоминаем последнюю кнопку

              // Выполняем запрос, если флаг установлен
              if (isSubmitMyBlock === true) {
                  sendAjaxRequestCoopImg(currentYearInput, idMonth);
              }
          }

          lastClickTime = new Date().getTime(); // Обновляем время последнего нажатия
          clickCount++; // Увеличиваем счетчик нажатий
      });

      $('.last_year, .next_year').click(function (e) {
        let currentTime = new Date().getTime();
        let timeDifference = currentTime - lastClickTime;
        currentYear = parseInt($('.year').text());
        $('.table_kw_mouth, .request_fail').empty();
        $('.table_kw_mouth, .list_number_meter, .list_number_meter').html('Подождите, запрос выполняется...');
        if (timeDifference < 2000 && clickCount > 5) {
                        // Отображаем сообщение об ошибке
            $(".request_fail").text("Ошибка: Слишком много запросов. Пожалуйста, подождите.");

                        // Блокируем кнопки на 3 секунды
            $('.last_year, .next_year, .button_block').prop("disabled", true);

            setTimeout(function () {
                $('.next_year, .button_block').prop("disabled", false);
                if (currentYearInput === 2023) {
                    $('.last_year').prop("disabled", true);
                } else {
                    $('.last_year').prop("disabled", false);
                }
                sendAjaxRequestCoopImg(currentYearInput, idMonth);
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
                sendAjaxRequestCoopImg(currentYearInput, idMonth);
                if (currentYearInput === 2023) {
                    $('.last_year').prop("disabled", true);
                } else {
                    $('.last_year').prop("disabled", false);
                }
            } else {
                            // Если нажата кнопка "next_year"
                currentYear = currentYearInput + 1;
                currentYearInput = currentYearInput + 1;
                sendAjaxRequestCoopImg(currentYearInput, idMonth);
                $('.last_year').prop("disabled", false);
            }

            return $('.year').text(currentYear) + currentYearInput;

        }
});

      function sendAjaxRequestCoopImg(currentYearInput, selectedMonth) {
          $('.body_indication').empty().append('<span style="font-size: 23px">Загрузка...</span>');

          $.ajax({
              url: '{{ route('ChairmanMyCoopGeneralCounter.index', ['idCoop' => $idCoop]) }}',
              type: "GET",
              data: {
                  numberYear: currentYearInput,
                  month: selectedMonth,
                  id_message: 1,
                  _token: '{{ csrf_token() }}',
              },
              success: function (response) {
                  $('.body_indication').empty();
                  let kw_meter = response.kw_meter;
                  let imgHtml = response.imgHtml;
                  let html_img = `<div class="block_indication">
                      <form method="post" enctype="multipart/form-data" action="{{ route('ChairmanMyCoopGeneralCounterPost.store', ['idCoop' => $idCoop]) }}" id="meter_indication_img">
                          @csrf
                          <div class="head_block_indication">
                              <span>Показания кВт: </span>
                              <input type="tel" pattern="[0-9]{1,10}" maxlength="20" oninput="this.value=this.value.replace(/\D/g,'')"  name="kw_meter" value="${kw_meter ?? ''}" required/>
                              <input type="hidden" class="id_year" name="id_year" value="${currentYearInput}">
                              <input type="hidden" class="id_month_number" name="id_month_number" value="${selectedMonth}">
                          </div>
                          <div class="img_block">
                              <input accept="image/png, image/jpg, image/jpeg" type="file" id="fileInput" class="file_img" name="img_meter" inputmode="none">
                              <div class="flex_img">
                                  <button type="submit">Сохранить</button>
                                  <div class="img">
                                      ${imgHtml ?? ''}
                                  </div>
                              </div>
                          </div>
                      </form>
                      <span style="font-size: 19px">При указании значения 0, <br> показания удаляются</span>
                  </div>`;
                  $('.body_indication').append(html_img);
                              // Разблокировка всех кнопок кроме текущей
                $('.id_month').prop("disabled", false);  // Разблокируем все кнопки
                $(lastClickedButton).prop("disabled", true);  // Оставляем заблокированной только нажатую кнопку

                // Разблокировка кнопок года, если они должны быть разблокированы
                if (currentYearInput !== 2023) {
                    $('.last_year').prop("disabled", false);
                }
                $('.next_year').prop("disabled", false);
            },
              error: function (error) {
                  let errorText = error.responseJSON ? error.responseJSON.error : 'Неизвестная ошибка';
                  $('.body_indication').html(`<span style="font-size: 23px">${errorText}</span>`);
              }
          });
      }
      sendAjaxRequestCoopImg(currentYearInput, {{$monthNow}});
  });
</script>
@endsection
