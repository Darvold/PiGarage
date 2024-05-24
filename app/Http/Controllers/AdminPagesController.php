<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\Cooperatives;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Date\Date;

class AdminPagesController extends Controller
{
    public function LogoutAdmin() {
        auth()->logout();
        return redirect()->route('AdminLogin.index');
    }
    public function AdminMain() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();

            $applications = ApplicationsCreateNewCoop::select('applications_create_new_coops.*')
            ->where('status', 'pending')->get();

            return view('administrator.pages.main', compact('applications'));

    }

    public function AddNewCoop($idAdmin) {

            $data = request()->validate([
                'id_application' => 'required|numeric',
            ]);
            // Получите заявку по ID
            $application = ApplicationsCreateNewCoop::find($data['id_application']);

            // Проверяем, найдена ли заявка
            if ($application) {
                // Создайте новый кооператив на основе данных заявки
                try {
                    Cooperatives::create([
                        'user_id' => $application->user_id,
                        'name' => $application->name,
                        'city' => $application->city,
                        'address' => $application->address,
                        'data_create' =>  Date::now(),
                        'id_point' => $application->id_point,
                    ]);

                    ApplicationsCreateNewCoop::where('id_application', $application->id_application)
                        ->update(['status' => 'accepted']);

                    $coop = Cooperatives::where('id_point', $application->id_point)->first();

                    $basePath = '../../CoopMeters/';
                    $regionFolder = explode(',', $application->address);
                    $cityFolder = explode(',', $application->city);

                    $region = trim($regionFolder[0]);
                    $city = trim($cityFolder[0]);

                    // Полный путь к новой папке
                    $folderPath = $basePath . $region;
                    $folderPathCity = $folderPath . '/' . $city;
                    $folderPathCoopName = $folderPathCity . '/' . $application->name . '_' . $coop->id_coop;
                    // Проверяем, существует ли папка
                    if (!file_exists($folderPath)) {
                        // Создаем новую папку
                        mkdir($folderPath, 0755, true);
                        //  return 'Папка успешно создана: ' . $folderPath;
                    }
                    if (!file_exists($folderPathCity)) {
                        // Создаем новую папку
                        mkdir($folderPathCity, 0755, true);
                        //  return 'Папка успешно создана: ' . $folderPath;
                    }
                    if (!file_exists($folderPathCoopName)) {
                        // Создаем новую папку
                        mkdir($folderPathCoopName, 0755, true);
                        //  return 'Папка успешно создана: ' . $folderPath;
                    }

                    return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()])->with('error', 'Кооператив успешно создался');
                } catch (\Exception $e) {
                   // dump($application);
                    return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()])->with('error', 'Ошибка, что-то пошло не так :(');
                }
                // Перенаправьте пользователя обратно на страницу с заявками
               // return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()])->with('success', 'Кооператив успешно создан');
            } else {
                // Обработка случая, если заявка не найдена
                return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()])->with('error', 'Заявка не найдена.');
            }
    }

}
