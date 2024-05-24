<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserAccess;

Route::middleware([CheckUserAccess::class])->group(function () {
    Route::get('/User/Profile/', 'UserController@indexUser')
        ->name('ProfileUser.index');

    Route::get('/Garage/Garage/', 'UserController@UserGarage')
        ->name('UserGarage.index');
    Route::get('/User/Garage/{idGarage}/PivotTable/', 'UserController@myGaragePivotTable')
        ->name('myUserGaragePivotTable.index');

    Route::get('/User/Garage/CreateGarage/', 'UserController@UserCreateGarage')
        ->name('UserCreateGarage.index');

    Route::post('/User/Garage/CreateGarage/new', 'UserController@CreatingGarage')
        ->name('UserCreatingGarage.store');

   /* Route::get('/Chairman/{id}/MyCoop/', 'PageProfileController@ChairmanMyCoop')
        ->name('ChairmanMyCoop.index');
    Route::get('/Chairman/{id}/MyCoop/PivotTable/{Name}/{idCoop}', 'PageProfileController@ChairmanMyCoopPivotTable')
        ->name('ChairmanMyCoopPivotTable.index');

    Route::get('/Chairman/{id}/MyCoop/CreateMyCoop/', 'PageProfileController@ChairmanCreateMyCoop')
        ->name('ChairmanCreateMyCoop.index');

    Route::post('/Chairman/{id}/MyCoop/CreateMyCoop/sendingDataCoop', 'PageProfileController@ChairmanCreatingCoop')
        ->name('ChairmanSendingDataCreateCoop.store');*/

    Route::get('/User/MyCoop/ConnectMyCoop/', 'UserController@UserConnectCoop')
        ->name('UserConnectCoop.index');
    Route::post('/User/MyCoop/ConnectMyCoop/JoinTheCoop', 'UserController@JoinTheCoop')
        ->name('JoinTheCoop.store');


/*    Route::get('/Chairman/{id}/Communication/Applications/', 'CommunicationController@Applications')
        ->name('Applications.index');
        Route::get('/Chairman/{id}/Messages/', 'CommunicationController@Messages')
        ->name('Messages.index');
        Route::get('/Chairman/{id}/Messages/Meters/', 'CommunicationController@MessagesMeters')
        ->name('MessagesMeters.index');*/

    Route::post('/User/Profile/logout', 'UserController@logout')
        ->name('logoutUser.store');
    /*general*/

});
