<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Установка локали
        Carbon::setLocale('ru');
        //Передаём информацию о текущем пользователе всем blade файлам
        View::composer('pagesForUser/*', function ($view) {
            $user = Auth::user();

            // Не делаем редирект здесь! View composer не для этого
            if (!$user) {
                // Просто не передаем данные о пользователе
                return;
            }

            // Прячем чувствительные данные
            $user->makeHidden(['password', 'remember_token']);

            // Передаем данные в представление
            $view->with('userCurrent', $user);

            // Если getSafeAttributes() возвращает что-то особенное
            if (method_exists($user, 'getSafeAttributes')) {
                $view->with('userSafe', $user->getSafeAttributes());
            }
        });
    }
}
