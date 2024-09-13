<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\CooperativeBlocks;
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
    public function LogoutAdmin()
    {
        auth()->logout();
        return redirect()->route('AdminLogin.index');
    }

    public function AdminMain()
    {

        // Получите текущего аутентифицированного пользователя
        $user = Auth::user();

        $applications = ApplicationsCreateNewCoop::select('applications_create_new_coops.*')
            ->where('status', 'pending')->get();

        return view('administrator.pages.main', compact('applications'));

    }

    public function AddNewCoop($idAdmin)
    {

        $data = request()->validate([
            'id_application' => 'required|numeric',
        ]);
        // Получите заявку по ID
        $application = ApplicationsCreateNewCoop::find($data['id_application']);
        // Проверяем, найдена ли заявка
        if ($application) {
            // Создайте новый кооператив на основе данных заявки
            try {
                $newCoop = Cooperatives::create([
                    'user_id' => $application->user_id,
                    'name' => $application->name,
                    'city' => $application->city,
                    'address' => $application->address,
                    'data_create' => Date::now(),
                    'latitude' => $application->latitude,
                    'longitude' => $application->longitude,
                ]);
                if ($application->number_garage_blocks) {
                    for ($i = 0; $i < $application->number_garage_blocks; $i++) {
                        CooperativeBlocks::create([
                            'id_coop' => $newCoop->id_coop,
                            'number_block' => $i + 1,
                            'default_kw' => null
                        ]);
                    }
                } else {
                    $newCoop->delete();
                    return redirect()->back()->with('error', 'Что-то пошло не так');
                }

                ApplicationsCreateNewCoop::where('id_application', $application->id_application)
                    ->update(['status' => 'accepted']);

                $basePath = '../../CoopMeters/';
                $regionFolder = explode(',', $application->address);
                $cityFolder = explode(',', $application->city);

                $region = trim($regionFolder[0]);
                $city = trim($cityFolder[0]);

                // Полный путь к новой папке
                $folderPath = $basePath . $region;
                $folderPathCity = $folderPath . '/' . $city;
                $folderPathCoopName = $folderPathCity . '/' . $application->name . '_' . $newCoop->id_coop;
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

                return redirect()->back()->with('success', 'Кооператив успешно создался');
            } catch (\Exception $e) {
                // dump($application);
                return redirect()->back()->with('error', "$e");
            }
            // Перенаправьте пользователя обратно на страницу с заявками
            // return redirect()->route('AdminMain.index', ['idAdmin' => Auth::id()])->with('success', 'Кооператив успешно создан');
        } else {
            // Обработка случая, если заявка не найдена
            return redirect()->back()->with('error', 'Заявка не найдена.');
        }
    }

}
