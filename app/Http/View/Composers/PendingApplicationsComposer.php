<?php
namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ApplicationsForAccessions;
use App\Models\Cooperatives;

class PendingApplicationsComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            // Получаем ID кооперативов текущего пользователя
            $coopIds = Cooperatives::where('user_id', Auth::id())->pluck('id_coop');

            // Считаем количество заявок со статусом 'pending' для всех кооперативов
            $totalPendingApplications = ApplicationsForAccessions::whereIn('id_coop', $coopIds)
                ->where('status', 'pending')
                ->count();

            // Передаем данные в представление
            $view->with('totalPendingApplications', $totalPendingApplications);
        }
    }
}
