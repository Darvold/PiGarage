<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\CooperativesBlocksLossesKw;
use App\Models\Garages;
use App\Models\MetersReadings;
use App\Models\UserAndCoop;
use App\Models\Cooperatives;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Date\Date;

class PageProfileController extends Controller
{
    public function indexChairman()
    {
        // Получите текущего аутентифицированного пользователя
        $user = Auth::user();
        $myCoops = Cooperatives::where('user_id', Auth::id())->get();
        return view('PagesForChairman.profile.profile', compact('user', 'myCoops'));
    }

    public function logout()
    {
        auth()->guard('web')->logout(); // Выход текущего пользователя

        return redirect()->route('login.index');
    }

    public function ChairmanGarage()
    {
        $garages = Garages::select('garages.*', 'cooperatives.name as coop_name', 'cooperatives.city as coop_city')
            ->leftJoin('cooperatives', 'cooperatives.id_coop', '=', 'garages.id_coop')
            ->where('garages.user_id', Auth::id())
            ->get();


        return view('PagesForChairman.profile.garage', compact('garages'));
    }

    public function ChairmanCreateGarage(Request $request)
    {
        if ($request->ajax()) {
            $idCoop = $request->input('idCoop');
            $blocks = CooperativeBlocks::where('id_coop', $idCoop)->get();
            return response()->json(['blocksForGarage' => $blocks]);
        }
        $coops = UserAndCoop::select('cooperatives.*')
            ->leftJoin('cooperatives', 'cooperatives.id_coop', '=', 'user_and_coop.id_coop')
            ->where('user_and_coop.user_id', Auth::id())
            ->get();

        return view('PagesForChairman.profile.createGarage', compact('coops'));

    }

    public function ChairmanCreatingGarage(Request $request)
    {
        try {
            $data = request()->validate([
                'number_meter' => 'required|numeric|min:0',
                'number_garage' => 'required|numeric|min:0',
                'number_block' => 'required|numeric|min:0',
                'id_coop' => 'required|numeric|min:0',
                'id_block' => 'required|numeric|min:0',
            ]);
            $coop = UserAndCoop::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->first();
            if ($data['id_coop'] == $coop->id_coop) {
                $conditions = [
                    'user_id' => Auth::id(),
                    'id_block' => $data['id_block'],
                    'number_garage' => $data['number_garage'],
                    'number_block' => $data['number_block'],
                    'number_meter' => $data['number_meter'],
                    'id_coop' => $data['id_coop'],
                ];
                // Попробуйте найти запись с соответствующими условиями
                $existingApplication = ApplicationsGarageToCoop::where('user_id', $conditions['user_id'])
                    ->where('id_coop', $conditions['id_coop'])
                    ->where('status', 'pending')
                    ->get();

                if ($existingApplication->count() > 2) {
                    return redirect()->back()->with('info', 'Уже отправлено максимальное количество запросов');
                }
                $conditions['status'] = 'pending';
                $application = ApplicationsGarageToCoop::firstOrCreate($conditions);
                $application->update(['date_received' => Date::now()]);
                return redirect()->route('ChairmanGarage.index')->with('success', 'Запрос успешно отправлен!');
            } else {
                return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
        }
    }


    public function myGaragePivotTable($idGarage)
    {
        $garage = Garages::select('garages.*')
            ->where('garages.id_garage', $idGarage)->first();
        $coop = Cooperatives::select('cooperatives.name')
            ->where('id_coop', $garage->id_coop)->first();
        if ($garage) {
            return view('PagesForChairman.profile.myGaragePivotTable', ['garage' => $garage, 'nameCoop' => $coop]);
        } else {
            return back()->with('error', 'Гараж не найден');
        }

    }

    public function ChairmanSubmitIndicationsGarage($idGarage)
    {

        $garageCoop = Garages::where('id_garage', $idGarage)->with('cooperative')->first();
        return view('PagesForChairman.profile.pageGarage.submitIndications', ['idGarage' => $idGarage, 'garageCoop' => $garageCoop]);

    }

    public function ChairmanSubmitIndicationsPostGarage(Request $request, $idGarage)
    {
        $currentMonth = Date::now()->month;
        $currentYear = Date::now()->year;

        $reading = MetersReadings::where('id_garage', $idGarage)
            ->whereIn('status', ['pending', 'accepted'])
            ->whereMonth('send_date', $currentMonth)
            ->whereYear('send_date', $currentYear)
            ->first();
        if ($reading) {
            return redirect()->back()->with('info', 'В этом месяце вы уже отправили показания');
        }

        try {
            $data = request()->validate([
                'kw_meter' => 'required|numeric|min:0',
                'img_meter' => 'required|image|mimes:jpeg,png,jpg|max:4096',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Размер файла слишком большой');
        }
        try {
            $coopURL = Garages::with(['cooperative', 'user'])
                ->where('id_garage', $idGarage)
                ->first();
            // Используйте функцию optional() для безопасного доступа к свойствам
            $regionFolder = explode(',', $coopURL->cooperative->address);
            $cityFolder = explode(',', $coopURL->cooperative->city);
            $region = trim($regionFolder[0]);
            $city = trim($cityFolder[0]);
            $name = $coopURL->cooperative->name;

            $userFIO = $coopURL->user->fio;
            // Получите файл из запроса
            $image = $data['img_meter'];
            // Генерируйте уникальное имя файла
            $imageExtension = $data['img_meter']->getClientOriginalExtension();
            $imageName = $userFIO . '_' . now()->format('Y-m-d_H-i-s');
            $imageNameWithExtension = $imageName . '.' . $imageExtension;
            // Определите путь куда сохранить файл в storage/app/public
            $path = '../../StoragePiGarage/CoopMeters/' . $region . '/' . $city . '/' . $name . '/' . $userFIO;
            $folderPath = '../../StoragePiGarage/CoopMeters/' . $region . '/' . $city . '/' . $name . '/' . $userFIO;

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true, true);
            }
            // Сохраните файл
            $image->move($path, $imageNameWithExtension);

            MetersReadings::create([
                'id_garage' => $idGarage,
                'id_block' => $coopURL->number_block,
                'id_coop' => $coopURL->id_coop,
                'kw_meter' => $data['kw_meter'],
                'status' => 'pending',
                'img_meter' => $imageNameWithExtension,
                'send_date' => Date::now(),
            ]);
            return redirect()->route('myGaragePivotTable.index', ['idGarage' => $idGarage])
                ->with('success', 'Показания успешно переданы!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка, повторите попытку позже');
        }
    }

}
