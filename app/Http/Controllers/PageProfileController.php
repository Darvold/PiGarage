<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\CooperativesBlocksLossesKw;
use App\Models\Garages;
use App\Models\MeterNumbersGarages;
use App\Models\MeterReadingsUsers;
use App\Models\User;
use App\Models\UserAndCoop;
use App\Models\Cooperatives;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PageProfileController extends Controller
{
    protected function indexChairman()
    {
        // Получите текущего аутентифицированного пользователя
        $user = Auth::user();
        $myCoops = Cooperatives::where('user_id', Auth::id())->get();
        return view('PagesForChairman.profile.profile', compact('user', 'myCoops'));
    }

    protected function settingsChairman()
    {
        $user = Auth::user();
        $myCoops = Cooperatives::where('user_id', Auth::id())->get();
        return view('PagesForChairman.profile.settingsChairman', compact('user', 'myCoops'));
    }

    protected function settingsChairmanPost(Request $request)
    {
        try {
            $request->validate([
                'fio' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'region' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20|unique:users,phone,' . auth()->id(),
                'second_phone' => 'nullable|string|max:20',
                'home_phone' => 'nullable|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', 'Ошибка валидации');
        }


        User::where('id', Auth::id())->update([
            'fio' => $request->fio,
            'phone' => $this->formatPhoneNumber($request->phone),
            'second_phone' => $this->formatPhoneNumber($request->second_phone),
            'home_phone' => $this->formatPhoneNumber($request->home_phone) ? $this->formatPhoneNumber($request->home_phone) : null,
            'email' => $request->email,
            'region' => $request->region,
        ]);

        // Редирект с сообщением об успехе
        return redirect()->back()->with('success', 'Данные успешно изменены!');
    }

    protected function logout()
    {
        auth()->guard('web')->logout(); // Выход текущего пользователя

        return redirect()->route('login.index');
    }

    protected function ChairmanGarage()
    {
        $garages = Garages::select('garages.*', 'cooperatives.name as coop_name', 'cooperatives.city as coop_city')
            ->leftJoin('cooperatives', 'cooperatives.id_coop', '=', 'garages.id_coop')
            ->where('garages.user_id', Auth::id())
            ->get();


        return view('PagesForChairman.profile.pageGarage.garage', compact('garages'));
    }

    protected function ChairmanCreateGarage(Request $request)
    {
        if ($request->ajax()) {
            try {
                $data = request()->validate([
                    'id_coop' => 'required|integer|min:1',
                ]);
            } catch (ValidationException $e) {
                return response()->json(['error' => "Ошибка, повторите попытку"], 500);
            }
            $blocks = CooperativeBlocks::leftJoin('user_and_coop', 'cooperative_blocks.id_coop', '=', 'user_and_coop.id_coop')
                ->where('user_and_coop.user_id', Auth::id())
                ->where('cooperative_blocks.id_coop', $data['id_coop'])
                ->get();

            return response()->json(['blocksForGarage' => $blocks]);
        }
        $coops = UserAndCoop::select('cooperatives.*')
            ->leftJoin('cooperatives', 'cooperatives.id_coop', '=', 'user_and_coop.id_coop')
            ->where('user_and_coop.user_id', Auth::id())
            ->get();

        return view('PagesForChairman.profile.pageGarage.createGarage', compact('coops'));

    }

    protected function ChairmanCreateGaragePost(Request $request)
    {
        try {
            $data = request()->validate([
                'number_meter' => 'required|integer|min:1',
                'number_garage' => 'required|integer|min:1',
                'number_block' => 'required|integer|min:1',
                'id_coop' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже 0');
        }
        try {
            $coop = UserAndCoop::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->first();
            $idBlockSelect = CooperativeBlocks::where('id_coop', $data['id_coop'])
                ->where('number_block', $data['number_block'])->first();
            if (!$coop || !$idBlockSelect) {
                return redirect()->back()->with('error', 'Неправильный номер блока');
            }
            if ($data['id_coop'] == $coop->id_coop) {
                $conditions = [
                    'user_id' => Auth::id(),
                    'id_block' => $idBlockSelect->id_block,
                    'number_garage' => $data['number_garage'],
                    'number_block' => $data['number_block'],
                    'number_meter' => $data['number_meter'],
                    'id_coop' => $data['id_coop'],
                    'status' => 'pending',
                    'active' => 1,
                    'date_received' => Date::now()
                ];
                $existingApplication = ApplicationsGarageToCoop::where('user_id', $conditions['user_id'])
                    ->where('id_coop', $conditions['id_coop'])
                    ->where('status', 'pending')
                    ->get();

                if ($existingApplication->count() > 4) {
                    return redirect()->back()->with('info', 'Уже отправлено максимальное количество запросов');
                }
                $application = ApplicationsGarageToCoop::firstOrCreate($conditions);
                return redirect()->route('ChairmanGarage.index')->with('success', 'Запрос успешно отправлен!');
            } else {
                return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже 1');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже 2');
        }
    }


    protected function myGaragePivotTable($idGarage)
    {
        $garage = Garages::select('garages.*')
            ->where('garages.id_garage', $idGarage)->first();
        $coop = Cooperatives::select('cooperatives.name')
            ->where('id_coop', $garage->id_coop)->first();
        if ($garage) {
            return view('PagesForChairman.profile.pageGarage.myGaragePivotTable', ['garage' => $garage, 'nameCoop' => $coop]);
        } else {
            return back()->with('error', 'Гараж не найден');
        }

    }

    protected function ChairmanSubmitIndicationsGarage($idGarage)
    {

        $garageCoop = Garages::where('id_garage', $idGarage)->with('cooperative')->first();
        return view('PagesForChairman.profile.pageGarage.submitIndications', ['idGarage' => $idGarage, 'garageCoop' => $garageCoop]);

    }

    protected function ChairmanSubmitIndicationsPostGarage(Request $request, $idGarage)
    {
        try {
            $date = Date::now();
            $currentMonth = Date::now()->month;
            $currentYear = Date::now()->year;
            $previousMonth = $date->subMonth(); // Прошлый месяц от текущей даты и времени

            $reading = MeterReadingsUsers::leftJoin('garages', 'garages.id_garage', '=', 'meter_readings_users.id_garage')
                ->where('meter_readings_users.id_garage', $idGarage)
                ->whereColumn('garages.id_coop', '=', 'meter_readings_users.id_coop')
                ->whereIn('meter_readings_users.status', ['pending', 'accepted'])
                ->whereMonth('meter_readings_users.send_date', $currentMonth)
                ->whereYear('meter_readings_users.send_date', $currentYear)
                ->first();
            if ($reading) {
                return redirect()->back()->with('info', 'В этом месяце вы уже отправили показания');
            }
            $data = request()->validate([
                'kw_meter' => 'required|integer|digits_between:1,19',
                'img_meter' => 'required|image|mimes:jpeg,png,jpg|max:50000',
            ]);
            $data['kw_meter'] = ltrim($data['kw_meter'], '0');
            $coopURL = Garages::select('garages.*', 'meter_numbers_garages.id_meter_number')
                ->join('meter_numbers_garages', 'meter_numbers_garages.id_garage', '=', 'garages.id_garage')
                ->where('garages.id_garage', $idGarage)
                ->where('meter_numbers_garages.active', 1)
                ->with(['cooperative', 'user'])
                ->first();
            if (!$coopURL) {
                return redirect()->back()->with('error', 'Гараж или счетчик не найден.');
            }
            $image = $data['img_meter'];
            // Генерируем хэш изображения для текущей загруженной фотографии
            $imageHash = hash_file('sha256', $image->getRealPath());
            // Проверяем предыдущие показания
            $previousReading = MeterReadingsUsers::leftJoin('garages', 'garages.id_garage', '=', 'meter_readings_users.id_garage')
                ->where('meter_readings_users.id_garage', $idGarage)
                ->whereColumn('garages.id_coop', '=', 'meter_readings_users.id_coop')
                ->whereIn('meter_readings_users.status', ['canceled', 'accepted'])
                ->whereMonth('meter_readings_users.send_date', $previousMonth)
                ->whereYear('meter_readings_users.send_date', $currentYear)
                ->first();

            if ($previousReading) {
                // Получаем хэш предыдущей фотографии из базы данных
                $previousImageHash = $previousReading->image_hash;

                // Проверяем, совпадают ли хэши изображений
                if ($imageHash === $previousImageHash) {
                    return redirect()->back()->with('info', 'Одна и та же фотография');
                }
            }

            // Используйте функцию optional() для безопасного доступа к свойствам
            $regionFolder = explode(',', $coopURL->cooperative->address);
            $cityFolder = explode(',', $coopURL->cooperative->city);
            $region = trim($regionFolder[0]);
            $city = trim($cityFolder[0]);
            $name = $coopURL->cooperative->name;

            $userFIO = $coopURL->user->fio;
            // Генерируйте уникальное имя файла
            $imageExtension = $data['img_meter']->getClientOriginalExtension();
            $imageName = $userFIO . '_' . now()->format('Y-m-d_H-i-s');
            $imageNameWithExtension = $imageName . '.' . $imageExtension;
            // Определите путь куда сохранить файл в storage/app/public
            $folderPath = '../../StoragePiGarage/CoopMeters/' . $currentYear . '/' . $region . '/' . $city . '/' . $name . '/' . 'Участники' .  '/' . $userFIO;

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true, true);
            }
            $compressedImage = Image::read($image->getRealPath()) // Используйте метод load для загрузки изображения
            ->resize(600, 800); // Устанавливаем качество сжатия
            // Сохраняем сжатое изображение
            $compressedImage->save($folderPath . '/' . $imageNameWithExtension);

            MeterReadingsUsers::create([
                'id_garage' => $idGarage,
                'id_block' => $coopURL->id_block,
                'id_coop' => $coopURL->id_coop,
                'id_meter_number' => $coopURL->id_meter_number,
                'kw_meter' => $data['kw_meter'],
                'status' => 'pending',
                'image_hash' => $imageHash,
                'img_meter' => $imageNameWithExtension,
                'send_date' => Date::now(),
            ]);
            return redirect()->route('myGaragePivotTable.index', ['idGarage' => $idGarage])
                ->with('success', 'Показания успешно переданы!');
        } catch (ValidationException $e) {
            // Это исключение связано с валидацией
            $errors = $e->validator->errors();
            if ($errors->has('img_meter')) {
                return redirect()->back()->with(['error' => "Максимальный размер файла 50 мегабайт|jpeg,png,jpg"])->withInput();
            }
            return redirect()->back()->with('error', 'Ошибка валидации, повторите попытку.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Произошла ошибка, повторите попытку позже');
        }
    }

    protected function garageMeters(Request $request, $idGarage)
    {
        $garage = Garages::select('garages.*')
            ->where('garages.id_garage', $idGarage)->first();
        $meters = MeterNumbersGarages::where('id_garage', $idGarage)
            ->orderBy('id_meter_number', 'desc')
            ->get();
        return view('PagesForChairman.profile.pageGarage.garageMeters', compact(['garage', 'meters']));
    }

    protected function garageMetersPost(Request $request, $idGarage)
    {
        try {
            $data = $request->validate([
                'meter_number' => 'required|integer|min:0',
                'number_id' => 'integer|min:1',
                'idPost' => 'required|integer|min:0',
            ]);

            if ($data['idPost'] == 0) {
                $meter_readings_users = MeterReadingsUsers::leftJoin('garages', 'garages.id_garage', '=', 'meter_readings_users.id_garage')
                    ->where('meter_readings_users.id_garage', $idGarage)
                    ->where('meter_readings_users.status', 'pending')
                    ->whereColumn('garages.id_coop', 'meter_readings_users.id_coop') // сравнение двух столбцов
                    ->first();
                if ($meter_readings_users) {
                    return redirect()->back()->with('error', 'Нельзя добавить счётчик, пока ваши показания находятся в ожидании');
                }
                $number_meter = MeterNumbersGarages::where('id_garage', $idGarage)
                    ->whereYear('creation_date', Date::now('Y'))->get();
                if (count($number_meter) > 3) {
                    return redirect()->back()->with('error', 'Нельзя добавить больше 3 счётчиков в год');
                }
                MeterNumbersGarages::where('id_garage', $idGarage)->update(['active' => 0]);
                MeterNumbersGarages::create([
                    'id_garage' => $idGarage,
                    'meter_number' => $data['meter_number'],
                    'active' => 1,
                    'creation_date' => Date::now(),
                ]);
                return redirect()->back()->with('success', 'Счётчик успешно добавлен!');
            }

            // Обработка для idPost == 1
            if ($data['idPost'] == 1) {
                $number_meter = MeterNumbersGarages::where('id_garage', $idGarage)
                    ->where('id_meter_number', $data['number_id'])
                    ->where('active', 1)->first();

                if ($number_meter) {
                    // Обновление данных
                    $number_meter->update(['meter_number' => $data['meter_number']]);
                    return redirect()->back()->with('success', 'Номер успешно изменён!');
                }

                // Случай, когда номер не найден
                return redirect()->back()->with('error', 'Не удалось найти счётчик или он является не активным.');
            }

            // Если idPost не равен 1
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');

        } catch (ValidationException $e) {
            // Обработка исключений валидации
            return redirect()->back()->with('error', 'Ошибка валидации данных');
        } catch (\Exception $e) {
            // Общая обработка исключений
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    // Функция для форматирования номера телефона
    private function formatPhoneNumber($phone)
    {
        // Удаляем все ненужные символы, оставляя только цифры
        return preg_replace('/\D/', '', $phone);
    }

}
