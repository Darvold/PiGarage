<?php

use App\Http\Middleware\CheckIdCoop;
use App\Http\Middleware\CheckIdGarage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckChairmanAccess;

Route::get('/', 'MasterController@index')
    ->name('welcome.index');

//Очистка Laravel. Очень важное!!!
//Без него невозможно добавить новый route адрес
Route::get('/cc', function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize');
    return 'Cache cleared successfully.';
});

Route::get('/registrationUser', 'LoginAndRegistrController@indexUser')
    ->name('registr.index');
Route::get('/authorizationUser', 'LoginAndRegistrController@indexLoginUser')
    ->name('login.index');


Route::post('/registrationUser', 'LoginAndRegistrController@registrationUser')
    ->name('registrationAddUser.store');
Route::post('/authorizationUser', 'LoginAndRegistrController@loginUser')
    ->name('loginUser.store');


Route::middleware([CheckChairmanAccess::class])->group(function () {
    // Главные страницы
    Route::get('/Chairman/Profile/', 'PageProfileController@indexChairman')
        ->name('ProfileChairman.index');

    //Настройки
    Route::get('/Chairman/Settings/', 'PageProfileController@settingsChairman')
        ->name('SettingsChairman.index');
    Route::post('/Chairman/Settings/', 'PageProfileController@settingsChairmanPost')
        ->name('settingsChairmanPost.store');

    //Список гаражей
    Route::get('/Chairman/Garages/', 'PageProfileController@ChairmanGarage')
        ->name('ChairmanGarage.index');

    // Главные страницы гаража
    Route::middleware([CheckIdGarage::class])->group(function () {
        //Главаная таблица (сводная) гаража
        Route::get('/Chairman/Garage/{idGarage}/PivotTable/', 'PageProfileController@myGaragePivotTable')
            ->name('myGaragePivotTable.index');

        //Отправка показаний счётчка председателю
        Route::get('/Chairman/Garage/{idGarage}/SubmitIndications/{numberGarage}', 'PageProfileController@ChairmanSubmitIndicationsGarage')
            ->name('ChairmanSubmitIndicationsGarage.index');
        Route::post('/Chairman/Garage/{idGarage}/SubmitIndications/{numberGarage}/Request', 'PageProfileController@ChairmanSubmitIndicationsPostGarage')
            ->name('ChairmanSubmitIndicationsPostGarage.store');

        //Список номеров счётчиков гаража/изменение счётчика гаража/добавление нового счётчика
        Route::get('/Chairman/Garage/{idGarage}/PivotTable/meters', 'PageProfileController@garageMeters')->name('garageMeters.index');
        Route::post('/Chairman/Garage/{idGarage}/PivotTable/meters', 'PageProfileController@garageMetersPost')->name('garageMeters.store');
    });

    // Создание гаража
    Route::get('/Chairman/Garage/CreateGarage/', 'PageProfileController@ChairmanCreateGarage')
        ->name('ChairmanCreateGarage.index');
    Route::post('/Chairman/Garage/CreateGarage/', 'PageProfileController@ChairmanCreateGaragePost')
            ->name('ChairmanCreateGaragePost.store');

    // Список кооператив председателя
    Route::get('/Chairman/MyCoop/', 'MyCooperatives@ChairmanMyCoop')
        ->name('ChairmanMyCoop.index');

    // Гаражные блоки, таблица кооператива, участники и другие возможности
    Route::middleware([CheckIdCoop::class])->group(function () {
        // Главная таблица кооператива
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}', 'MyCooperatives@ChairmanMyCoopPivotTable')
            ->name('ChairmanMyCoopPivotTable.index');

        //Просмотр участников гаражного кооператива
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}/Participants','MyCooperatives@ParticipantsCoop')
            ->name('ChairmanMyCoopParticipants.index');

        // Установка тарифа
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}/Rate', 'MyCooperatives@ChairmanMyCoopRate')
            ->name('ChairmanMyCoopRate.index');
        Route::post('/Chairman/MyCoop/PivotTable/{idCoop}/Rate', 'MyCooperatives@ChairmanMyCoopRatePost')
            ->name('ChairmanMyCoopRatePost.store');

        // Установка потерь кооператива
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}/Losses', 'MyCooperatives@ChairmanMyCoopLosses')
            ->name('ChairmanMyCoopLosses.index');
        Route::post('/Chairman/MyCoop/PivotTable/{idCoop}/Losses', 'MyCooperatives@ChairmanMyCoopLossesPost')
            ->name('ChairmanMyCoopLossesPost.store');

        // Установить оплату
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}/Payment', 'MyCooperatives@ChairmanMyCoopPayment')
            ->name('ChairmanMyCoopPayment.index');
        Route::post('/Chairman/MyCoop/PivotTable/{idCoop}/Payment', 'MyCooperatives@ChairmanMyCoopPaymentPost')
            ->name('ChairmanMyCoopPaymentPost.store');

        // Установить оплату, таблица сборов
        Route::get('/Chairman/MyCoop/PivotTable/{idCoop}/PaymentOther', 'MyCooperatives@ChairmanMyCoopPaymentOther')
            ->name('ChairmanMyCoopPaymentOther.index');
        Route::post('/Chairman/MyCoop/PivotTable/{idCoop}/PaymentOther', 'MyCooperatives@ChairmanMyCoopPaymentOtherPost')
            ->name('ChairmanMyCoopPaymentOtherPost.store');

        // гаражные блоки кооператива
        Route::get('/Chairman/MyCoop/{idCoop}/Blocks', 'MyCooperatives@ChairmanMyCoopBlocks')
            ->name('ChairmanMyCoopBlocks.index');
        Route::post('/Chairman/MyCoop/{idCoop}/Blocks', 'MyCooperatives@ChairmanMyCoopBlocksPost')
            ->name('ChairmanMyCoopNewBlocks.store');

        // Сообщение от пользователей (Чаты и показания)
        /*Route::get('/Chairman/Messages/', 'CommunicationController@Messages')
            ->name('Messages.index');*/
        Route::get('/Chairman/MyCoop/{idCoop}/MetersUser/', 'MyCooperatives@MessagesMeters')
            ->name('MessagesMeters.index');
        Route::post('/Chairman/MyCoop/{idCoop}/MetersUser/', 'MyCooperatives@MessagesMetersPost')
            ->name('MessagesMetersPost.store');
    });

    // Создание кооператива
    Route::get('/Chairman/MyCoop/CreateMyCoop/', 'MyCooperatives@ChairmanCreateMyCoop')
        ->name('ChairmanCreateMyCoop.index');
    Route::post('/Chairman/MyCoop/CreateMyCoop/', 'MyCooperatives@ChairmanCreateMyCoopPost')
        ->name('ChairmanSendingDataCreateCoop.store');

    // Возвращает кооперативы по координатам через AJAX
    Route::get('/Chairman/MyCoop/SelectCoopsAJAX/', 'MyCooperatives@ChairmanSelectCoopsAJAX')
        ->name('ChairmanSelectCoopsAJAX.index')->middleware('throttle:30,1');

    // Присоединение к кооперативу
    Route::get('/Chairman/MyCoop/ConnectMyCoop/', 'MyCooperatives@ChairmanConnectCoop')
        ->name('ChairmanConnectCoop.index');
    Route::post('/Chairman/MyCoop/ConnectMyCoop/', 'MyCooperatives@ChairmanConnectCoopPost')
        ->name('JoinTheCoopOrCansel.store');

    // Заявки самого пользователя на присоединения к кооперативу
    Route::get('/Chairman/MyCoop/MyApplicationToCoop/', 'CommunicationController@MyApplicationToCoop')
        ->name('MyApplicationToCoop.index');
    Route::post('/Chairman/MyCoop/MyApplicationToCoop/', 'CommunicationController@MyApplicationToCoopPost')
        ->name('MyApplicationToCoopPost.store');

    // Заявки других пользователей на присоединение к кооперативу
    Route::get('/Chairman/Communication/Applications/', 'CommunicationController@Applications')
        ->name('Applications.index');
    Route::post('/Chairman/Communication/Applications/', 'CommunicationController@ApplicationsPost')
        ->name('ApplicationsPost.store');

    // Отменённые заявки на присоединение к кооперативу
    Route::get('/Chairman/Communication/Applications/Reject', 'CommunicationController@ApplicationsReject')
        ->name('ApplicationsReject.index');
    Route::post('/Chairman/Communication/Applications/Reject', 'CommunicationController@ApplicationsRejectPost')
        ->name('ApplicationsRejectPost.store');

    // Выход и учётной записи
    Route::get('/Chairman/Profile/logout', 'PageProfileController@logout')
        ->name('logoutChairman.store');
    /*general*/

});



//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
