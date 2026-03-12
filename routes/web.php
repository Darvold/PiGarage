<?php
use App\Http\Middleware as Middleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Controller;
// Очистка кэша
Route::get('/cc', function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize');
    return 'Cache cleared successfully.';
});

//Главная
Route::get('/', function () {
    return view('welcome');
})->name('welcome.index');
// Авторизация
Route::get('/authorization', [Controller\LoginAndRegistrController::class, 'loginUser'])->name('login.index');
Route::post('/authorization', [Controller\LoginAndRegistrController::class, 'loginUserPost'])->name('login.store');
// Регистрация
Route::get('/registration', [Controller\LoginAndRegistrController::class, 'registrUser'])->name('registr.index');
Route::post('/registration', [Controller\LoginAndRegistrController::class, 'registrUserPost'])->name('registr.store');

Route::middleware([Middleware\CheckUserAccess::class])->group(function () {
    //Главная страница профиля
    Route::get('/profile', [Controller\PagesProfileController::class, 'profileUser'])->name('profileUser.index');

    // Обновить информацию о пользователе
    Route::post('/profile', [Controller\PagesProfileController::class, 'updateProfileUser'])->name('updateProfileUser.store');

    // Страница мои кооперативы
    Route::get('/myCoops', [Controller\MyCooperatives::class, 'myCoops'])->name('myCoops.index');

    // Страница создать кооператив
    Route::get('/myCoops/create', [Controller\CreateCoop::class, 'myCoops_Create'])->name('myCoops_Create.index');
    Route::post('/myCoops/create', [Controller\CreateCoop::class, 'myCoops_CreatePost'])->name('myCoops_Create.store');

    // Возвращает кооперативы по координатам через AJAX
    Route::get('/Chairman/MyCoop/SelectCoopsAJAX/', [Controller\CreateCoop::class, 'UserSelectCoopsAJAX'])
        ->name('ChairmanSelectCoopsAJAX.index')->middleware('throttle:30,1');

    // Присоединение к кооперативу
    Route::get('/Chairman/MyCoop/ConnectMyCoop/', [Controller\MyCooperatives::class, 'UserConnectCoop'])
        ->name('ChairmanConnectCoop.index');

    // Выход из аккаунта
    Route::post('/logout', [Controller\UserController::class, 'logoutUser'])->name('logout.store');
});

Route::prefix('error-logs')->name('error-logs.')->group(function () {
    Route::get('/', [Controller\ErrorLogController::class, 'index'])->name('index');
    Route::get('/{id}', [Controller\ErrorLogController::class, 'show'])->name('show');
    Route::delete('/{id}', [Controller\ErrorLogController::class, 'destroy'])->name('destroy');
    Route::post('/clear-all', [Controller\ErrorLogController::class, 'clearAll'])->name('clear-all');
});
