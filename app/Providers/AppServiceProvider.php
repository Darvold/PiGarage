<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\PendingApplicationsComposer;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Date::setLocale('ru');
        View::composer('layouts.mainChairman', PendingApplicationsComposer::class);
    }
}
