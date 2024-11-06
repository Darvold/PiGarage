<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\Cooperatives;
use App\Models\CooperativesBlocksLossesKw;
use App\Models\coopLosses;
use App\Models\Fees;
use App\Models\Garages;
use App\Models\MeterNumbersBlocks;
use App\Models\MeterNumbersCoops;
use App\Models\MeterNumbersGarages;
use App\Models\MeterReadingsBlocks;
use App\Models\MeterReadingsCoops;
use App\Models\MeterReadingsUsers;
use App\Models\Payments;
use App\Models\Rates;
use App\Models\TotalPaidForElectricity;
use App\Models\UserAndCoop;
use App\Models\UserBalance;
use App\Rules\MyYear;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Jenssegers\Date\Date;
use Mockery\Exception;
use App\Rules\NoNegativeNumbers;
use GuzzleHttp\Client;
use function Laravel\Prompts\select;

class MyCooperatives extends Controller
{
    //Контроллер для страницы всех кооперативов председателя
    protected function ChairmanMyCoop()
    {

        $myCoops = Cooperatives::where('user_id', Auth::id())
            ->withCount('userAndCoop')
            ->get();


        return view('PagesForChairman.profile.myCoop', compact('myCoops'));
    }

    //Контроллер для страницы создание кооператива
    protected function ChairmanCreateMyCoop()
    {
        return view('PagesForChairman.profile.createMyCoop');

    }

    protected function ChairmanCreateMyCoopPost()
    {
        try {
            $data = request()->validate([
                'name' => [
                    'required',
                    'min:3',
                    'max:250',
                    // Регулярное выражение для запрета тэгов < и > и других символов, которые могут использоваться в инъекциях
                    //'regex:/^[^<>]+$/' - проверка на < >
                    'regex:/^[а-яА-Я0-9\s\-№]+$/u'// Разрешает только буквы, цифры, пробелы и дефисы и №
                ],
                'number_meter' => 'required|integer|digits_between:1,19',
                'city' => 'required',
                'address' => 'required',
                'number_garage_blocks' => 'required|integer|min:1|max:10',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            if ($errors->has('name')) {
                return redirect()->back()->with(['error' => "Название кооператива должно состоять минимум из 3-х символов до 250, русскими буквами"])->withInput();
            }
            if ($errors->has('number_meter')) {
                return redirect()->back()->with(['error' => 'Недопустимое количество цифр в номере счётчика – макс. 19'])->withInput();
            }
            if ($errors->has('number_garage_blocks')) {
                return redirect()->back()->with(['error' => 'Количество гаражных блоков не совпадает с правилами'])->withInput();
            }
            // Другие ошибки:
            return redirect()->back()->with(['error' => 'Что-то пошло не так, повторите попытку'])->withInput();
        }
        $data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
        try {
            // Проверяем координаты и страну, город
            $test = $this->getCountryFromCoordinates($data['city'], $data['address'], $data['latitude'], $data['longitude']);
            //Старая версия кода
            /* if ($test === true) {
                // Координаты принадлежат России
                $data['id_point'] = $data['latitude'] . ',' . $data['longitude'];
            }*/
            if ($test === '[1]') {
                return redirect()->back()->with(['error' => 'Что-то пошло не так, возможно метка указано вне города/посёлка/село'])->withInput();
            }
            if ($test === '[2]') {
                return redirect()->back()->with(['error' => 'Ваши координаты метки не отсносятся к России'])->withInput();
            }
            if (!$test) {
                return redirect()->back()->with(['error' => 'Что-то пошло не так, попробуйте в другой день выполнить запрос'])->withInput();
            }

            ApplicationsCreateNewCoop::create([
                'user_id' => Auth::id(),
                'name' => $data['name'],
                'city' => $data['city'],
                'address' => $data['address'],
                'date_received' => Date::now(),
                'number_meter' => $data['number_meter'],
                'status' => 'pending',
                'number_garage_blocks' => $data['number_garage_blocks'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],

            ]);
            return redirect()->back()->with('success', 'Заявка успешно отправлена!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку')->withInput();
        }
    }


    //Контроллер для просмотр заявков других пользователей
    protected function ChairmanConnectCoop(Request $request)
    {
        if ($request->ajax()) {
            $idMessage = $request->input("idMessage");
            if ($idMessage == 1) {
                $nameCoop = $request->input("nameCoop");
                $selectedRegion = $request->input("selectedRegion");
                $selectedCity = $request->input("selectedCity");
                if ($selectedRegion == $selectedCity) {
                    $location = '%' . $selectedRegion . '%';
                } else {
                    $location = '%' . $selectedRegion . ', ' . $selectedCity . '%';
                }
                $nameCoopLike = '%' . $nameCoop . '%';
                $offset = $request->input('offset', 0);

                $allCoops = Cooperatives::where('name', 'LIKE', $nameCoopLike)
                    ->where('address', 'LIKE', $location)
                    ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
                    ->select('cooperatives.*', 'users.fio')
                    ->skip($offset)
                    ->take(5)
                    ->get();

                $totalCount = Cooperatives::where('name', 'LIKE', $nameCoopLike)
                    ->where('address', 'LIKE', $location)
                    ->count();
                $hasMore = ($offset + 5) < $totalCount;

                if ($allCoops) {
                    return response()->json(['blockMessages' => $allCoops,
                        'hasMore' => $hasMore]);
                } else {
                    return response()->json(['error' => "Ошибка, похоже нет кооперативов с заданными параметрами"], 500);
                }
            }
            return response()->json(['error' => "Что-то пошло не так"], 500);
        }

        return view('PagesForChairman.profile.connectCoop');
    }

    protected function ChairmanConnectCoopPost()
    {
        $id_message = request()->input('id_message');
        if (!$id_message) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
        }
        try {
            if ($id_message == 1) {
                if (request()->input('notGarage') === 'false') {
                    $data = request()->validate([
                        'id_coop' => 'required|integer|min:1',
                        'garageData' => ['required', new NoNegativeNumbers],
                    ]);
                } elseif (request()->input('notGarage') === 'true') {
                    $data = request()->validate([
                        'id_coop' => 'required|integer|min:1',
                    ]);
                } else {
                    throw ValidationException::withMessages([
                        'notGarage' => 'Некорректное значение notGarage.'
                    ]);
                }
            } else {
                $data = request()->validate([
                    'id_coop' => 'required|integer|min:1',
                ]);
            }
        } catch (ValidationException|\Exception  $e) {
            $garageData = request()->input('garageData', null);
            $garagesDataJson = json_decode($garageData, true);
            return redirect()->back()
                ->with(['error' => "Что-то пошло не так, ошибка валидации"])
                ->with('garageData', $garagesDataJson);
        }
        $coopUser = Cooperatives::where('id_coop', $data['id_coop'])->first();
        if ($coopUser['user_id'] == Auth::id()) {
            return redirect()->back()->with('error', 'Нельзя отправить заявку в свой же кооператив');
        }
        $Application = ApplicationsForAccessions::withTrashed()
            ->where('user_id', Auth::id())
            ->where('id_coop', $data['id_coop'])
            ->where(function ($query) {
                $query->where('status', 'rejected')
                    ->orWhere('status', 'pending')
                    ->orWhere('status', 'delete');
            })
            ->first();
        if ($Application) {
            switch ($Application->status) {
                case 'rejected':
                    if ($id_message == 1) {
                        return redirect()->back()->with('error', 'Заявка отклонена председателем!');
                    }
                    break;
                case 'pending':
                    if ($id_message == 1) {
                        return redirect()->back()->with('error', 'Заявка уже отправлена!');
                    }
                    break;
                case 'delete':
                    if ($id_message == 2) {
                        return redirect()->back()->with('error', 'Заявка уже отменена!');
                    }
                    break;
            }
        } elseif ($id_message == 2) {
            return redirect()->back()->with('error', 'Невозможно удалить то чего нет!');
        }
        if ($id_message == 1) {
            if (request()->input('notGarage') === 'true') {
                try {
                    $restoreApplication = ApplicationsForAccessions::where('user_id', Auth::id())
                        ->where('id_coop', $data['id_coop'])
                        ->where('status', 'delete')
                        ->first();
                    ApplicationsGarageToCoop::where('user_id', Auth::id())
                        ->where('id_coop', $data['id_coop'])
                        ->update([
                            'active' => 0,
                        ]);
                    if ($restoreApplication) {
                        $restoreApplication->update(['status' => 'pending', 'send_date' => Date::now(), 'amount_garages' => 0]);
                        return redirect()->back()->with('success', 'Заявка успешно отправлена!');
                    }
                    ApplicationsForAccessions::create([
                        'user_id' => Auth::id(),
                        'id_coop' => $data['id_coop'],
                        'status' => 'pending',
                        'amount_garages' => 0,
                        'send_date' => Date::now(),
                    ]);
                    return redirect()->back()->with('success', 'Заявка успешно отправлена!');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
                }
            }
            try {
                $garagesData = json_decode($data['garageData'], true);
                $countGarageBlock = count(CooperativeBlocks::where('id_coop', $data['id_coop'])->get());
                foreach ($garagesData as $gData) {
                    if ($gData['number_block'] > $countGarageBlock) {
                        return redirect()->back()
                            ->with(['error' => "Не правильно указан номер блока. В кооперативе доступно всего $countGarageBlock гаражных блоков"])
                            ->with('garageData', $garagesData);
                    }
                }
                $restoreApplication = ApplicationsForAccessions::where('user_id', Auth::id())
                    ->where('id_coop', $data['id_coop'])
                    ->where('status', 'delete')
                    ->first();
                $amount_garages = count($garagesData);
                if ($restoreApplication) {
                    $restoreApplication->update(['status' => 'pending', 'send_date' => Date::now(), 'amount_garages' => $amount_garages]);
                    ApplicationsGarageToCoop::where('user_id', Auth::id())
                        ->where('id_coop', $data['id_coop'])
                        ->update(['active' => 0]);
                    foreach ($garagesData as $gData) {
                        // Проверяем наличие гаража с нужными параметрами
                        $garage = ApplicationsGarageToCoop::where('user_id', Auth::id())
                            ->where('id_coop', $data['id_coop'])
                            ->where('status', 'delete')
                            ->first();

                        if ($garage) {
                            // Обновляем только найденную запись
                            $garage->update([
                                'number_garage' => $gData['number_garage'],
                                'number_block' => $gData['number_block'],
                                'number_meter' => $gData['number_meter'],
                                'status' => 'pending',
                                'active' => 1,
                                'date_received' => Date::now()
                            ]);
                        } else {
                            // Создаём новую запись, если не нашли подходящую
                            ApplicationsGarageToCoop::create([
                                'user_id' => Auth::id(),
                                'id_coop' => $data['id_coop'],
                                'number_garage' => $gData['number_garage'],
                                'number_block' => $gData['number_block'],
                                'number_meter' => $gData['number_meter'],
                                'status' => 'pending',
                                'active' => 1,
                                'date_received' => Date::now(),
                            ]);
                        }
                    }

                    return redirect()->back()->with('success', 'Заявка успешно отправлена!');
                }

                ApplicationsForAccessions::create([
                    'user_id' => Auth::id(),
                    'id_coop' => $data['id_coop'],
                    'status' => 'pending',
                    'amount_garages' => $amount_garages,
                    'send_date' => Date::now(),
                ]);
                foreach ($garagesData as $gData) {
                    ApplicationsGarageToCoop::create(
                        [
                            'user_id' => Auth::id(),
                            'id_block' => null,
                            'id_coop' => $data['id_coop'],
                            'number_garage' => $gData['number_garage'],
                            'number_block' => $gData['number_block'],
                            'number_meter' => $gData['number_meter'],
                            'status' => 'pending',
                            'date_received' => Date::now()
                        ]);
                }
                return redirect()->back()->with('success', 'Заявка успешно отправлена!')->with('garageData', $garagesData);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        if ($id_message == 2) {
            try {
                ApplicationsForAccessions::where('user_id', Auth::id())
                    ->where('id_coop', $data['id_coop'])
                    ->where('status', 'pending')->update(['status' => 'delete']);
                ApplicationsGarageToCoop::where('user_id', Auth::id())
                    ->where('id_coop', $data['id_coop'])
                    ->where('status', 'pending')->update(['status' => 'delete']);
                return redirect()->back()->with('success', 'Заявка успешно отменена!');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
            }
        }
        return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
    }


    //Главная таблица, сводная таблица кооператива
    protected function ChairmanMyCoopPivotTable(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $dataInput = /*$request->validate([
                    'numberYear' => ['required', 'numeric', new MyYear()],
                ]);*/ ['numberYear' => 2024];
                // Создаем массив для хранения данных по каждому месяцу
                $data = [];

                // Получаем данные из всех необходимых таблиц
                $meterReadings = MeterReadingsCoops::where('id_coop', $idCoop)
                    ->whereYear('save_day', $dataInput['numberYear'])->get();
                $totalPaid = TotalPaidForElectricity::where('id_coop', $idCoop)
                    ->whereYear('date_indication', $dataInput['numberYear'])
                    ->orderBy('date_indication')->get();
                $tariffs = Rates::where('id_coop', $idCoop)->whereYear('date_indication', $dataInput['numberYear'])
                    ->orderBy('date_indication')->get();
                $losses = CoopLosses::where('id_coop', $idCoop)->whereYear('date_indication', $dataInput['numberYear'])
                    ->orderBy('date_indication')->get();

                $previousReading = null;

                foreach ($meterReadings as $reading) {
                    // Определяем месяц по дате показаний
                    $month = $reading->save_day->format('F');

                    // Проверка наличия достаточных данных
                    $hasData = $reading && $reading->kw_meter && $reading->save_day;
                    if (!$hasData) {
                        $data[$month] = "Недостаточно данных";
                        continue;
                    }


                    // Заполняем массив данных для текущего месяца
                    $data[$month] = [
                        'meter_readings' => $reading->kw_meter ?? "Недостаточно данных",
                        /*'kw_garages' => $kwGarages,
                        'kw_rows' => $kwRows,
                        'losses' => $lossValue,
                        'kw_with_losses' => $kwWithLosses,
                        'tariff' => $tariffValue,
                        'total_cost' => $totalCost,
                        'paid' => $paidAmount,
                        'debt' => $debt*/
                    ];

                    $previousReading = $reading; // Сохраняем текущее значение для расчета разницы в следующем цикле
                }
                // Возвращаем данные в формате JSON для обработки на клиенте
                return response()->json(['data' => $meterReadings]);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        return view('PagesForChairman.profile.pivotTableCoop.myCoopPivotTable', compact('coopData', 'idCoop'));

    }

    //Гаражные блоки, изменение кВт
    protected function ChairmanMyCoopBlocks(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = $request->validate([
                    'numberYear' => ['required', 'numeric', new MyYear()],
                    'id_block' => 'required|integer|exists:cooperative_blocks,id_block', // Проверка на существование id_block
                    'month' => 'numeric|between:1,12', // Проверяем, что месяц - это число от 1 до 12
                ]);
                $numberYear = $data['numberYear'];
                $id_block = $data['id_block'];
                $month = $data['month'] ?? null;
                // Показания + фотография счётчика
                if ($request->input('id_message') == 2 && $month !== null) {
                    $readings_meter_img = MeterReadingsBlocks::with(['MeterNumberBlockActive' => function ($query) use ($id_block) {
                        $query->where('id_block', $id_block)
                            ->where('active', 1);
                    }])
                        ->with(['Cooperative' => function ($query) {
                            $query->select('id_coop', 'name', 'address', 'city');
                        }])
                        ->where('id_block', $id_block)
                        ->where('id_coop', $idCoop)
                        ->where('id_meter_number_block', function ($query) use ($id_block) {
                            $query->select('id_meter_number')
                                ->from('meter_numbers_blocks')
                                ->where('id_block', $id_block)
                                ->where('active', 1)
                                ->limit(1);
                        })
                        ->whereMonth('save_day', $month)
                        ->whereYear('save_day', $numberYear)
                        ->first();


                    if (!$readings_meter_img) {
                        return response()->json(['blockMessages' => false]);
                    }
                    $imgHtml = '<span style="color: forestgreen; font-size: 22px;">Без файла</span>';
                    $kw_meter = $readings_meter_img->kw_meter;
                    if ($readings_meter_img->img_meter != null) {
                        $nameCoop = $readings_meter_img->Cooperative->name . '_' . $readings_meter_img->Cooperative->id_coop;
                        $regionFolder = explode(',', $readings_meter_img->Cooperative->address);
                        $cityFolder = explode(',', $readings_meter_img->Cooperative->city);
                        $nameAddress = trim($regionFolder[0]);
                        $nameCity = trim($cityFolder[0]);
                        $nameFile = $readings_meter_img->img_meter;
                        $date = $readings_meter_img->save_day;
                        $folderPath = '../../StoragePiGarage/CoopMeters/' . $numberYear . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания рядов' . '/' . $id_block . '/' . $nameFile;
                        if (file_exists($folderPath) && is_readable($folderPath)) {
                            // Читаем содержимое файла
                            $fileContent = file_get_contents($folderPath);
                            // Проверяем, удалось ли прочитать файл
                            if ($fileContent !== false) {
                                $base64Image = base64_encode($fileContent);
                                $imgHtml = '<a data-date="' . $date . '" id="lightbox-image" href="data:image/jpg/jpeg/png;base64,' . $base64Image .
                                    '"data-title="Фото счётчика" data-lightbox="image">' .
                                    '<img src="data:image/jpg/jpeg/png;base64,' . $base64Image . '" alt="Фото счётчика"></a>';
                            } else {
                                // Обработка ошибки чтения файла
                                $imgHtml = '<span style="color: red;" data-date="' . $date . '">Ошибка чтения файла</span>';
                            }
                        } else {
                            // Обработка отсутствия файла
                            $imgHtml = '<span style="color: red; font-size: 21px;" data-date="' . $date . '">Файл не существует или удалён</span>';
                        }
                    }
                    return response()->json(['kw_meter' => $kw_meter, 'imgHtml' => $imgHtml]);

                }
                // Таблица потерь + счётчики гаражного ряда
                if ($request->input('id_message') == 1) {
                    $blockDefaultKw = CooperativeBlocks::where('id_block', $id_block)
                        ->where('id_coop', $idCoop)->first();
                    if (!$blockDefaultKw) {
                        return response()->json(['error' => 'Ошибка, повторите попытку позже'], 500);
                    }
                    $blocksKW = CooperativesBlocksLossesKw::where('id_block', $id_block)
                        ->whereYear('date_indication', $numberYear)
                        ->orderByRaw('MONTH(date_indication)')
                        ->get();
                    $messageMeters = MeterNumbersBlocks::where('id_block', $id_block)
                        ->orderBy('creation_date', 'desc')
                        ->orderBy('active', 'desc')
                        ->get();
                    return response()->json(['blockMessages' => $blocksKW, 'id_block' => $id_block,
                        'defaultKW' => $blockDefaultKw, 'messageMeters' => $messageMeters]);
                }
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('numberYear')) {
                    $yearErrors = $errors->get('numberYear');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError], 500);
                }

                return response()->json(['error' => "Ошибка валидации"], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => "Что-то пошло не так, повторите попытку позже"], 500);
            }
        }
        $blocks = CooperativeBlocks::leftJoin('garages', 'garages.id_block', '=', 'cooperative_blocks.id_block')
            ->where('cooperative_blocks.id_coop', $idCoop)
            ->select('cooperative_blocks.*', DB::raw('COUNT(garages.id_garage) as garage_count'))
            ->groupBy('cooperative_blocks.id_block')
            ->get();

        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $year = Date::now()->format('Y');
        return view('PagesForChairman.profile.pivotTableCoop.myCoopBlocks', compact('coopData', 'year', 'idCoop', 'blocks'));
    }

    protected function ChairmanMyCoopBlocksPost(Request $request, $idCoop)
    {
        $id_message = request()->input('id_message');
        //Создание нового ряда
        if ($id_message == 1) {
            try {
                $idCoop = request()->validate([
                    'id_coop' => 'required|numeric|min:0',
                ])['id_coop'];

                $blocks = CooperativeBlocks::where('id_coop', $idCoop)->get();

                $count_block = count($blocks);

                if ($count_block < 50) {
                    $new_number_block = $count_block + 1;
                    CooperativeBlocks::create([
                        'id_coop' => $idCoop,
                        'number_block' => $new_number_block,
                        'default_kw' => null
                    ]);
                    return back()->with('success', "Гаражный ряд успешно создан $new_number_block/50");
                } else {
                    return back()->with('info', "Создано максимальное количество гаражных рядов $count_block/50");
                }
            } catch (ValidationException $e) {
                return back()->with(['error' => 'Ошибка валидации']);
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        //Изменение % потерь по умолчанию
        if ($id_message == 2) {
            try {
                $garage_block = request()->validate([
                    'default_kw' => 'required|numeric|min:0',
                    'id_block' => 'required|numeric|min:0',
                ]);
                $coopBlock = CooperativeBlocks::where('id_block', $garage_block['id_block'])
                    ->update(['default_kw' => $garage_block['default_kw']]);
                if (!$coopBlock) {
                    return back()->with('error', "Не найден гаражный ряд, попробуйте позже");
                }
                return back()->with('success', "Успешно изменилось % потерь по умолчанию = {$garage_block['default_kw']}");
            } catch (ValidationException $e) {
                return back()->with(['error' => 'Ошибка валидации']);
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        //Изменение % потерь
        if ($id_message == 3) {
            try {
                $mouthKW = request()->validate([
                    'lossesNumber' => 'required|numeric|min:0',
                    'id_block' => 'required|numeric|min:0',
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'id_month_number' => 'required|numeric|between:1,12',
                ]);
                $month = $mouthKW['id_month_number'];
                $year = $mouthKW['id_year'];
                if (($year < 2023 || $year > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
                    return back()->with('error', "Что-то пошло не так, повторите попытку позже");
                }

                $datetime = now()->setMonth($month)->setYear($year)->format('Y-m-d H:i:s');
                $existingRecord = CooperativesBlocksLossesKw::where('id_block', $mouthKW['id_block'])
                    ->whereYear('date_indication', $year)
                    ->whereMonth('date_indication', $month)
                    ->first();

                if ($existingRecord) {
                    $existingRecord->update([
                        'date_indication' => $datetime,
                        'percent_kw' => $mouthKW['lossesNumber'],
                    ]);
                    $message = "Успешно изменилось % потерь на {$mouthKW['lossesNumber']}";
                } else {
                    CooperativesBlocksLossesKw::create([
                        'id_block' => $mouthKW['id_block'],
                        'percent_kw' => $mouthKW['lossesNumber'],
                        'date_indication' => $datetime,
                    ]);
                    $message = "Успешно добавлены % потерь на {$mouthKW['lossesNumber']}";
                }

                return back()->with(['success' => $message, 'id_block' => $mouthKW['id_block']]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return back()->with(['error' => $yearError]);
                }
                return back()->with(['error' => 'Ошибка валидации']);
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        //Сохранения изображения и кВт в месяце
        if ($id_message == 4) {
            try {
                $meter = request()->validate([
                    'kw_meter' => 'required|integer|digits_between:0,19',
                    'img_meter' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
                    'id_block' => 'required|numeric|min:0',
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'id_month_number' => 'required|numeric|between:1,12',
                ]);
                $returnData = [
                    'id_block' => $meter['id_block'],
                    'id_year' => $meter['id_year'],
                    'id_month_number' => $meter['id_month_number']
                ];
                $readings_meter_img = Cooperatives::with(['meterReadingsBlocks' => function ($query) use ($meter, $idCoop) {
                    $query->where('id_block', $meter['id_block'])
                        ->where('id_coop', $idCoop)
                        ->where('id_meter_number_block', function ($query) use ($meter) {
                            $query->select('id_meter_number')
                                ->from('meter_numbers_blocks')
                                ->where('id_block', $meter['id_block'])
                                ->where('active', 1)
                                ->limit(1);
                        })
                        ->whereYear('save_day', $meter['id_year'])
                        ->whereMonth('save_day', $meter['id_month_number'])
                        ->withTrashed();
                }])->where('id_coop', $idCoop)
                    ->where('user_id', Auth::id())->first();
                if (!$readings_meter_img) {
                    return back()->with(['error' => "Не найден кооператив"] + $returnData);
                }
                $activeMeter = MeterNumbersBlocks::where('id_block', $meter['id_block'])->where('active', 1)->first();
                if (!$activeMeter) {
                    return back()->with(['error' => "Добавьте счётчик!"] + $returnData);
                }
                $nameCoop = $readings_meter_img->name . '_' . $readings_meter_img->id_coop;
                $regionFolder = explode(',', $readings_meter_img->address);
                $cityFolder = explode(',', $readings_meter_img->city);
                $nameAddress = trim($regionFolder[0]);
                $nameCity = trim($cityFolder[0]);

                if ($meter['kw_meter'] == 0) {
                    MeterReadingsBlocks::where('id_block', $meter['id_block'])
                        ->where('id_coop', $idCoop)
                        ->whereYear('save_day', $meter['id_year'])
                        ->whereMonth('save_day', $meter['id_month_number'])->delete();

                    $this->deleteOldImage($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop);
                    return back()->with(['success' => 'Успешно удалено'] + $returnData);
                }
                $saveDay = Carbon::create($meter['id_year'], $meter['id_month_number'], now()->day)
                    ->setTime(now()->hour, now()->minute, now()->second);
                $saveDay = $saveDay->format('Y-m-d_H-i-s');

                $imageNameWithExtension = null;
                if (isset($meter['img_meter'])) {
                    // Удаляем старую фотографию, если она существует
                    $this->deleteOldImage($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop);
                    // Загрузка нового изображения
                    $imageExtension = $meter['img_meter']->getClientOriginalExtension();
                    $imageName = 'фото_счётчика' . '_' . $saveDay;
                    $imageNameWithExtension = $imageName . '.' . $imageExtension;
                    $folderPath = '../../StoragePiGarage/CoopMeters/' . $meter['id_year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания рядов' . '/' . $meter['id_block'];

                    if (!File::exists($folderPath)) {
                        File::makeDirectory($folderPath, 0755, true, true);
                    }

                    $compressedImage = Image::read($meter['img_meter']->getRealPath())
                        ->resize(600, 800);
                    $compressedImage->save($folderPath . '/' . $imageNameWithExtension);
                }

                // Обновляем или создаем запись
                if ($readings_meter_img->meterReadingsBlocks->isNotEmpty()) {
                    // Обновляем существующую запись
                    $meterReading = $readings_meter_img->meterReadingsBlocks->first();

                    if ($meterReading->trashed()) {
                        $meterReading->restore();
                    }

                    $meterReading->update([
                        'kw_meter' => $meter['kw_meter'],
                        'id_meter_number_block' => $activeMeter->id_meter_number,
                        'img_meter' => $imageNameWithExtension ?? $meterReading->img_meter, // Сохраняем старое имя, если новое не задано
                        'save_day' => $saveDay,
                    ]);
                } else {
                    // Создаем новую запись
                    MeterReadingsBlocks::create([
                        'id_block' => $meter['id_block'],
                        'id_coop' => $idCoop,
                        'id_meter_number_block' => $activeMeter->id_meter_number,
                        'kw_meter' => $meter['kw_meter'],
                        'img_meter' => $imageNameWithExtension ?? null,
                        'save_day' => $saveDay,
                    ]);
                }

                return back()->with(['success' => "Успешно сохранено"] + $returnData);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return back()->with(['error' => $yearError]);
                }
                return back()->with(['error' => 'Ошибка валидации']);
            } catch (\Exception $e) {
                return back()->with(['error' => 'Что-то пошло не так, повторите попытку']);
            }
        }
        // Создание счётчика и изменения текущего
        if ($id_message == 5) {
            try {
                $data = $request->validate([
                    'meter_number' => 'required|integer|min:0',
                    'number_id' => 'integer|min:1',
                    'idPost' => 'required|integer|min:0',
                    'id_block' => 'required|integer|min:0',
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'initially_kw' => 'required|integer|min:0',
                    'id_month_number' => 'required|numeric|min:0|max:12',
                ]);
                $returnData = [
                    'id_block' => $data['id_block'],
                    'id_year' => $data['id_year'],
                    'id_month_number' => $data['id_month_number']
                ];
                $selectIdBlock = CooperativeBlocks::where('id_block', $data['id_block'])
                    ->where('id_coop', $idCoop)->first();
                if (!$selectIdBlock) {
                    return back()->with(['error' => 'Что-то пошло не так, не найден гаражный ряд текущего кооператива'] + $returnData);
                }
                // Создание нового счётчика
                if ($data['idPost'] == 0) {
                    $number_meter = MeterNumbersBlocks::where('id_block', $data['id_block'])
                        ->whereYear('creation_date', Date::now('Y'))->get();
                    if (count($number_meter) > 3) {
                        return back()->with(['error' => 'Нельзя добавить больше 3 счётчиков в год'] + $returnData);
                    }
                    MeterNumbersBlocks::where('id_block', $data['id_block'])->update(['active' => 0]);
                    MeterNumbersBlocks::create([
                        'id_block' => $data['id_block'],
                        'meter_number' => $data['meter_number'],
                        'initially_kw' => $data['initially_kw'],
                        'active' => 1,
                        'creation_date' => Date::now(),
                    ]);
                    return redirect()->back()->with(['success' => 'Счётчик успешно добавлен!'] + $returnData);
                }

                // Обновление счётчика
                if ($data['idPost'] == 1) {
                    $number_meter = MeterNumbersBlocks::where('id_block', $data['id_block'])
                        ->where('id_meter_number', $data['number_id'])
                        ->where('active', 1)->first();

                    if ($number_meter) {
                        // Обновление данных
                        $number_meter->update(['meter_number' => $data['meter_number'], 'initially_kw' => $data['initially_kw']]);
                        return redirect()->back()->with(['success' => 'Успешно изменено!'] + $returnData);
                    }

                    // Случай, когда номер не найден
                    return redirect()->back()->with(['error' => 'Не удалось найти счётчик или он является не активным.'] + $returnData);
                }

                // Если idPost не равен 1
                return redirect()->back()->with(['error' => 'Что-то пошло не так, повторите попытку позже'] + $returnData);

            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                // Обработка исключений валидации
                return redirect()->back()->with(['error' => 'Ошибка валидации данных']);
            } catch (\Exception $e) {
                // Общая обработка исключений
                return redirect()->back()->with(['error' => "Что-то пошло не так, повторите попытку позже"]);
            }
        }
        return back()->with(['error' => 'Что-то пошло не так, повторите запрос позже']);
    }

    protected function ChairmanMyCoopGeneralCounter(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = $request->validate([
                    'numberYear' => ['required', 'numeric', new MyYear()],
                    'month' => 'required|numeric|between:1,12', // Проверяем, что месяц - это число от 1 до 12
                ]);
                $numberYear = $data['numberYear'];
                $month = $data['month'];
                // Показания + фотография счётчика
                if ($request->input('id_message') == 1) {
                    $readings_meter_img = MeterReadingsCoops::with(['MeterNumberCoopsActive' => function ($query) use ($idCoop) {
                        $query->where('id_coop', $idCoop)
                            ->where('active', 1);
                    }])
                        ->with(['Cooperative' => function ($query) {
                            $query->select('id_coop', 'name', 'address', 'city');
                        }])
                        ->where('id_coop', $idCoop)
                        ->whereMonth('save_day', $month)
                        ->whereYear('save_day', $numberYear)
                        ->first();

                    if (!$readings_meter_img) {
                        return response()->json(['blockMessages' => false]);
                    }
                    $imgHtml = '<span style="color: forestgreen; font-size: 22px;">Без файла</span>';
                    $kw_meter = $readings_meter_img->kw_meter;
                    if ($readings_meter_img->img_meter != null) {
                        $nameCoop = $readings_meter_img->Cooperative->name . '_' . $readings_meter_img->Cooperative->id_coop;
                        $regionFolder = explode(',', $readings_meter_img->Cooperative->address);
                        $cityFolder = explode(',', $readings_meter_img->Cooperative->city);
                        $nameAddress = trim($regionFolder[0]);
                        $nameCity = trim($cityFolder[0]);
                        $nameFile = $readings_meter_img->img_meter;
                        $date = $readings_meter_img->save_day;
                        $folderPath = '../../StoragePiGarage/CoopMeters/' . $numberYear . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания общего счётчика' . '/' . $nameFile;
                        if (file_exists($folderPath) && is_readable($folderPath)) {
                            // Читаем содержимое файла
                            $fileContent = file_get_contents($folderPath);
                            // Проверяем, удалось ли прочитать файл
                            if ($fileContent !== false) {
                                $base64Image = base64_encode($fileContent);
                                $imgHtml = '<a data-date="' . $date . '" id="lightbox-image" href="data:image/jpg/jpeg/png;base64,' . $base64Image .
                                    '"data-title="Фото счётчика" data-lightbox="image">' .
                                    '<img src="data:image/jpg/jpeg/png;base64,' . $base64Image . '" alt="Фото счётчика"></a>';
                            } else {
                                // Обработка ошибки чтения файла
                                $imgHtml = '<span style="color: red;" data-date="' . $date . '">Ошибка чтения файла</span>';
                            }
                        } else {
                            // Обработка отсутствия файла
                            $imgHtml = '<span style="color: red; font-size: 21px;" data-date="' . $date . '">Файл не существует или удалён</span>';
                        }
                    }
                    return response()->json(['kw_meter' => $kw_meter, 'imgHtml' => $imgHtml]);
                }
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('numberYear')) {
                    $yearErrors = $errors->get('numberYear');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                return response()->json(['error' => "Ошибка валидации"], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку'], 500);
            }
        }
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $year = Date::now()->format('Y');
        $monthNow = Date::now()->format('m');
        return view('PagesForChairman.profile.pivotTableCoop.generalCounter', compact('coopData', 'idCoop', 'year', 'monthNow'));
    }

    protected function ChairmanMyCoopGeneralCounterPost(Request $request, $idCoop)
    {
        try {
            $meter = request()->validate([
                'kw_meter' => 'required|integer|digits_between:0,19',
                'img_meter' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
                'id_year' => ['required', 'numeric', new MyYear()],
                'id_month_number' => 'required|numeric|between:1,12',
            ]);
            $returnData = [
                'id_year' => $meter['id_year'],
                'id_month_number' => $meter['id_month_number']
            ];
            $readings_meter_img = Cooperatives::with(['meterReadingsCoops' => function ($query) use ($meter, $idCoop) {
                $query->where('id_coop', $idCoop)
                    ->where('id_meter_number_coop', function ($query) use ($meter, $idCoop) {
                        $query->select('id_meter_number')
                            ->from('meter_numbers_coops')
                            ->where('id_coop', $idCoop)
                            ->where('active', 1)
                            ->limit(1);
                    })
                    ->whereYear('save_day', $meter['id_year'])
                    ->whereMonth('save_day', $meter['id_month_number'])
                    ->withTrashed();
            }])->where('id_coop', $idCoop)
                ->where('user_id', Auth::id())->first();
            if (!$readings_meter_img) {
                return back()->with(['error' => "Не найден кооператив"] + $returnData);
            }
            $activeMeter = MeterNumbersCoops::where('id_coop', $idCoop)->where('active', 1)->first();
            if (!$activeMeter) {
                return back()->with(['error' => "Добавьте счётчик!"] + $returnData);
            }
            $nameCoop = $readings_meter_img->name . '_' . $readings_meter_img->id_coop;
            $regionFolder = explode(',', $readings_meter_img->address);
            $cityFolder = explode(',', $readings_meter_img->city);
            $nameAddress = trim($regionFolder[0]);
            $nameCity = trim($cityFolder[0]);

            if ($meter['kw_meter'] == 0) {
                MeterReadingsCoops::where('id_coop', $idCoop)
                    ->whereYear('save_day', $meter['id_year'])
                    ->whereMonth('save_day', $meter['id_month_number'])->update(['img_meter' => null]);
                MeterReadingsCoops::where('id_coop', $idCoop)
                    ->whereYear('save_day', $meter['id_year'])
                    ->whereMonth('save_day', $meter['id_month_number'])->delete();

                $this->deleteOldImageCoops($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop);
                return back()->with(['success' => 'Успешно удалено'] + $returnData);
            }
            $saveDay = Carbon::create($meter['id_year'], $meter['id_month_number'], now()->day)
                ->setTime(now()->hour, now()->minute, now()->second);
            $saveDay = $saveDay->format('Y-m-d_H-i-s');

            $imageNameWithExtension = null;
            if (isset($meter['img_meter'])) {
                // Удаляем старую фотографию, если она существует
                $this->deleteOldImageCoops($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop);
                // Загрузка нового изображения
                $imageExtension = $meter['img_meter']->getClientOriginalExtension();
                $imageName = 'фото_счётчика' . '_' . $saveDay;
                $imageNameWithExtension = $imageName . '.' . $imageExtension;
                $folderPath = '../../StoragePiGarage/CoopMeters/' . $meter['id_year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания общего счётчика' . '/';

                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, 0755, true, true);
                }

                $compressedImage = Image::read($meter['img_meter']->getRealPath())
                    ->resize(600, 800);
                $compressedImage->save($folderPath . '/' . $imageNameWithExtension);
            }

            // Обновляем или создаем запись
            if ($readings_meter_img->meterReadingsCoops->isNotEmpty()) {
                // Обновляем существующую запись
                $meterReading = $readings_meter_img->meterReadingsCoops->first();

                if ($meterReading->trashed()) {
                    $meterReading->restore();
                }

                $meterReading->update([
                    'kw_meter' => $meter['kw_meter'],
                    'id_meter_number_coop' => $activeMeter->id_meter_number,
                    'img_meter' => $imageNameWithExtension ?? $meterReading->img_meter, // Сохраняем старое имя, если новое не задано
                    'save_day' => $saveDay,
                ]);
            } else {
                // Создаем новую запись
                MeterReadingsCoops::create([
                    'id_coop' => $idCoop,
                    'id_meter_number_coop' => $activeMeter->id_meter_number,
                    'kw_meter' => $meter['kw_meter'],
                    'img_meter' => $imageNameWithExtension ?? null,
                    'save_day' => $saveDay,
                ]);
            }

            return back()->with(['success' => "Успешно сохранено"] + $returnData);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            if ($errors->has('id_year')) {
                $yearErrors = $errors->get('id_year');
                // Объединяем массив ошибок в строку
                $yearError = implode(', ', $yearErrors);
                return back()->with(['error' => $yearError]);
            }

            return back()->with(['error' => 'Ошибка валидации']);
        } catch (\Exception $e) {
            return back()->with(['error' => 'Что-то пошло не так, повторите попытку']);
        }
    }

    protected function MessagesMeters(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = $request->validate([
                    'id_block' => 'required|integer|min:1',
                    'year' => ['required', 'numeric', new MyYear()],
                    'month' => 'required|numeric|between:1,12',
                ]);
            } catch (ValidationException|\Exception $e) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }
            try {
                if (($data['year'] < 2023 || $data['year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['month'])) {
                    return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже']);
                }
                $meters_readings = MeterReadingsUsers::with([
                    'garages.user' => function ($query) {
                        $query->select('id', 'fio'); // Выбираем только id и fio из таблицы users
                    },
                    'cooperative' => function ($query) {
                        $query->select('id_coop', 'name', 'address', 'city'); // Выбираем нужные поля из cooperative, если необходимо
                    }
                ])
                    ->where('id_block', $data['id_block'])
                    ->where('id_coop', $idCoop)
                    ->whereYear('send_date', $data['year'])
                    ->whereMonth('send_date', $data['month'])
                    ->where('status', 'pending')
                    ->orderBy('id_reading', 'desc')
                    ->get();

                $meters_readings_old = MeterReadingsUsers::with([
                    'garages.user' => function ($query) {
                        // Выбираем только id и fio из users
                        $query->select('id', 'fio');
                    },
                    'cooperative' => function ($query) {
                        // Выбираем необходимые поля из cooperative
                        $query->select('id_coop', 'name', 'address', 'city');
                    }
                ])
                    ->where('meter_readings_users.id_block', $data['id_block'])
                    ->where('meter_readings_users.id_coop', $idCoop)
                    ->whereIn('meter_readings_users.status', ['accepted'])
                    ->where('meter_readings_users.send_date', '<', Date::now())
                    ->orderBy('meter_readings_users.id_reading', 'desc')
                    ->get();

                $folderPaths = [];
                $folderPathsOld = [];
                foreach ($meters_readings as $index => $file_url) {
                    $fio = $file_url->garages[0]->user->fio;
                    $nameCoop = $file_url->cooperative->name . '_' . $file_url->cooperative->id_coop;
                    $regionFolder = explode(',', $file_url->cooperative->address);
                    $cityFolder = explode(',', $file_url->cooperative->city);
                    $nameAddress = trim($regionFolder[0]);
                    $nameCity = trim($cityFolder[0]);
                    $nameFile = $file_url->img_meter;
                    $date = $file_url->send_date;
                    $folderPath = '../../StoragePiGarage/CoopMeters/' . $data['year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Участники' . '/' . $fio . '/' . $nameFile;

                    // Проверяем существование файла перед чтением
                    if (file_exists($folderPath) && is_readable($folderPath)) {
                        // Читаем содержимое файла
                        $fileContent = file_get_contents($folderPath);

                        // Проверяем, удалось ли прочитать файл
                        if ($fileContent !== false) {
                            $base64Image = base64_encode($fileContent);
                            $imgHtml = '<a data-date="' . $date . '" id="lightbox-image-' . $index . '" href="data:image/jpg/jpeg/png;base64,' . $base64Image .
                                '"data-title="Фото счётчика" data-lightbox="image-' . $index . '">' .
                                '<img src="data:image/jpg/jpeg/png;base64,' . $base64Image . '" alt="Фото счётчика"></a>';
                            $folderPaths[] = $imgHtml;
                        } else {
                            // Обработка ошибки чтения файла
                            $folderPaths[] = '<span style="color: red;" data-date="' . $date . '">Ошибка чтения файла</span>';
                        }
                    } else {
                        // Обработка отсутствия файла
                        $folderPaths[] = '<span style="color: red; font-size: 22px;" data-date="' . $date . '">Файл не существует или не доступен для чтения</span>';
                    }
                }
                foreach ($meters_readings_old as $index => $file_url) {
                    $fio = $file_url->garages[0]->user->fio;
                    $nameCoop = $file_url->cooperative->name . '_' . $file_url->cooperative->id_coop;
                    $regionFolder = explode(',', $file_url->cooperative->address);
                    $cityFolder = explode(',', $file_url->cooperative->city);
                    $nameAddress = trim($regionFolder[0]);
                    $nameCity = trim($cityFolder[0]);
                    $nameFile = $file_url->img_meter;
                    $date = $file_url->send_date;
                    $oldKw_meter = $file_url->kw_meter;
                    $folderPath = '../../StoragePiGarage/CoopMeters/' . $data['year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Участники' . '/' . $fio . '/' . $nameFile;

                    // Проверяем существование файла перед чтением
                    if (file_exists($folderPath) && is_readable($folderPath)) {
                        // Читаем содержимое файла
                        $fileContent = file_get_contents($folderPath);

                        // Проверяем, удалось ли прочитать файл
                        if ($fileContent !== false) {
                            $base64Image = base64_encode($fileContent);
                            $imgHtml = '<a data-date="' . $date . '" data-kw-meter="' . $oldKw_meter . '" id="lightbox-image-' . $index . '" href="data:image/jpg/jpeg/png;base64,' . $base64Image .
                                '"data-title="Фото счётчика" data-lightbox="image-' . $index . '">' .
                                '<img src="data:image/jpg/jpeg/png;base64,' . $base64Image . '" alt="Фото счётчика"></a>';
                            $folderPathsOld[] = $imgHtml;
                        } else {
                            // Обработка ошибки чтения файла
                            $folderPathsOld[] = '<span style="color: red;" data-date="' . $date . '">Ошибка чтения файла</span>';
                        }
                    } else {
                        // Обработка отсутствия файла
                        $folderPathsOld[] = '<span style="color: red;" data-date="' . $date . '">Файл не существует или не доступен</span>';
                    }
                }
                return response()->json([
                    'id_block' => $data['id_block'],
                    'metersReadings' => $meters_readings,
                    'folderPaths' => [
                        'current' => $folderPaths, // Пути к фотографиям текущих показаний
                        'old' => $folderPathsOld, // Пути к фотографиям прошлых показаний
                    ],
                    'monthNow' => $data['month']
                ]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                return response()->json(['error' => 'Ошибка валидации'], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку'], 500);
            }
        }
        $year = Date::now()->format('Y');
        $currentMonth = Date::now()->format('m');
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $blocks = CooperativeBlocks::where('id_coop', $idCoop)
            ->withCount(['meterReadingsUsers' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->get();

        return view('PagesForChairman.communication.messagesMeters',
            compact('blocks', 'coopData', 'idCoop', 'year', 'currentMonth'));

    }

    protected function MessagesMetersPost(Request $request)
    {
        try {
            $data = $request->validate([
                'idMessage' => 'required|integer|min:1',
                'numberMeter' => 'required|integer|min:0',
                'idReading' => 'required|integer|min:1',
            ]);
        } catch (ValidationException|\Exception $e) {
            return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
        }

        $metersReadings = MeterReadingsUsers::where('id_reading', $data['idReading']);
        if ($data['idMessage'] == 0) {
            $metersReadings->update([
                'status' => 'accepted',
                'kw_meter' => $data['numberMeter']
            ]);
        } elseif ($data['idMessage'] == 1) {
            $metersReadings->update(['status' => 'canceled']);
        } else {
            return response()->json(['response' => 'error']);
        }

        if ($metersReadings->get()) {
            return response()->json(['response' => $data['idReading']]);
        }

        return response()->json(['response' => 'error']);
    }


    protected function ChairmanMyCoopRate(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            $year = $request->input('numberYear');
            $valueRates = Rates::where('id_coop', $idCoop)
                ->whereYear('date_indication', $year)
                ->orderByRaw('MONTH(date_indication)')
                ->get();
            return response()->json(['blockMessages' => $valueRates, 'year' => $year]);
        }
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $year = Date::now()->format('Y');
        $valueRates = Rates::where('id_coop', $idCoop)
            ->whereYear('date_indication', $year)
            ->orderByRaw('MONTH(date_indication)')
            ->get();
        return view('PagesForChairman.profile.pivotTableCoop.pageRate', compact('year', 'coopData', 'idCoop', 'valueRates'));
    }

    protected function ChairmanMyCoopRatePost(Request $request, $idCoop)
    {
        try {
            $data = request()->validate([
                'id_year' => ['required', 'numeric', new MyYear()],
                'id_month_number' => 'required|numeric|between:1,12',
                'tariff_value' => 'required|numeric|min:0',

            ]);
            if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
            $datetime = now()->setMonth($data['id_month_number'])->setYear($data['id_year'])->format('Y-m-d H:i:s');
            $existingRecord = Rates::where('id_coop', $idCoop)
                ->whereYear('date_indication', $data['id_year'])
                ->whereMonth('date_indication', $data['id_month_number'])
                ->first();

            if ($existingRecord) {
                $existingRecord->update([
                    'tariff_value' => $data['tariff_value'],
                ]);
                $message = "Успешно изменился тариф на {$data['tariff_value']}";
            } else {
                Rates::create([
                    'id_coop' => $idCoop,
                    'tariff_value' => $data['tariff_value'],
                    'date_indication' => $datetime,
                ]);
                $message = "Успешно добавлен новый тариф на {$data['tariff_value']}";
            }
            return back()->with('success', $message);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();

            if ($errors->has('id_year')) {
                $yearErrors = $errors->get('id_year');
                // Объединяем массив ошибок в строку
                $yearError = implode(', ', $yearErrors);
                return back()->with(['error' => $yearError]);
            }
            return back()->with('error', "Ошибка валидации");
        } catch (\Exception $e) {
            return back()->with('error', "Что-то пошло не так, повторите попытку позже");
        }

    }


    protected function ChairmanMyCoopLosses(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            $year = $request->input('numberYear');
            $valueRates = coopLosses::where('id_coop', $idCoop)
                ->whereYear('date_indication', $year)
                ->orderByRaw('MONTH(date_indication)')
                ->get();
            return response()->json(['blockMessages' => $valueRates, 'year' => $year]);
        }
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $year = Date::now()->format('Y');
        $valueRates = coopLosses::where('id_coop', $idCoop)
            ->whereYear('date_indication', $year)
            ->orderByRaw('MONTH(date_indication)')
            ->get();
        return view('PagesForChairman.profile.pivotTableCoop.pageLossesCoop', compact('year', 'coopData', 'idCoop', 'valueRates'));
    }

    protected function ChairmanMyCoopLossesPost(Request $request, $idCoop)
    {
        try {
            $data = request()->validate([
                'id_year' => ['required', 'numeric', new MyYear()],
                'id_month_number' => 'required|numeric|between:1,12',
                'losses_value' => 'required|numeric|min:0',

            ]);
            if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
            $datetime = now()->setMonth($data['id_month_number'])->setYear($data['id_year'])->format('Y-m-d H:i:s');
            $existingRecord = coopLosses::where('id_coop', $idCoop)
                ->whereYear('date_indication', $data['id_year'])
                ->whereMonth('date_indication', $data['id_month_number'])
                ->first();

            if ($existingRecord) {
                $existingRecord->update([
                    'losses_value' => $data['losses_value'],
                ]);
                $message = "Успешно изменились потери на {$data['losses_value']}";
            } else {
                coopLosses::create([
                    'id_coop' => $idCoop,
                    'losses_value' => $data['losses_value'],
                    'date_indication' => $datetime,
                ]);
                $message = "Успешно добавлены новые потери на {$data['losses_value']}";
            }
            return back()->with('success', $message);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            if ($errors->has('id_year')) {
                $yearErrors = $errors->get('id_year');
                // Объединяем массив ошибок в строку
                $yearError = implode(', ', $yearErrors);
                return back()->with(['error' => $yearError]);
            }
            return back()->with('error', "Ошибка валидации");
        } catch (\Exception $e) {
            return back()->with('error', "Что-то пошло не так, повторите попытку позже");
        }

    }


    protected function ChairmanMyCoopPayment(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = request()->validate([
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'id_month_number' => 'required|numeric|between:1,12',
                ]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                return response()->json(['error' => 'Ошибка валидации'], 500);
            }
            try {
                $valuePayMents = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
                    ->leftJoin('garages', function ($join) use ($idCoop) {
                        $join->on('garages.user_id', '=', 'users.id')
                            ->where('garages.id_coop', $idCoop);
                    })
                    ->leftJoin('payments', function ($join) use ($idCoop, $data) {
                        $join->on('payments.id_garage', '=', 'garages.id_garage')
                            ->where('payments.id_coop', $idCoop)
                            ->where('payments.type_payment', 0)
                            ->whereColumn('payments.id_garage', 'garages.id_garage')
                            ->whereYear('payments.date_indication', $data['id_year'])
                            ->whereMonth('payments.date_indication', $data['id_month_number']);
                    })
                    /*->leftJoin('user_balances', function ($join) use ($idCoop, $data) {
                        $join->on('garages.id_garage', '=', 'user_balances.id_garage');
                    })*/
                    ->where('user_and_coop.id_coop', $idCoop)
                    ->orderBy('users.fio')
                    ->select(
                        'users.id',
                        'users.fio',
                        'garages.id_garage as garage_id',  // Альтернативное имя для избежания коллизии
                        'garages.number_garage',
                        'garages.number_block',
                        /*'user_balances.balance',*/
                        'payments.*'
                    )
                    ->get();
                return response()->json(['blockMessages' => $valuePayMents, 'year' => $data['id_year'], 'monthNow' => $data['id_month_number']]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }
        }
        $year = Date::now()->format('Y');
        $monthNow = Date::now()->format('m');
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $usersCoop = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
            ->leftJoin('garages', function ($join) use ($idCoop) {
                $join->on('garages.user_id', '=', 'users.id')
                    ->where('garages.id_coop', $idCoop);
            })
            ->leftJoin('payments', function ($join) use ($idCoop, $year, $monthNow) {
                $join->on('payments.id_garage', '=', 'garages.id_garage')
                    ->where('payments.id_coop', $idCoop)
                    ->where('payments.type_payment', 0)
                    ->whereColumn('payments.id_garage', 'garages.id_garage')
                    ->whereYear('payments.date_indication', $year)
                    ->whereMonth('payments.date_indication', $monthNow);
            })
            /*->leftJoin('user_balances', function ($join) use ($idCoop) {
                $join->on('garages.id_garage', '=', 'user_balances.id_garage');
            })*/
            ->where('user_and_coop.id_coop', $idCoop)
            ->orderBy('users.fio')
            ->select(
                'users.id',
                'users.fio',
                'garages.id_garage as garage_id',  // Альтернативное имя для избежания коллизии
                'garages.number_garage',
                'garages.number_block',
                /*'user_balances.balance',*/
                'payments.*'
            )
            ->get();

        $blocksWithGarages = Garages::where('id_coop', $idCoop)
            ->leftJoin('users', 'garages.user_id', '=', 'users.id')
            ->orderBy('garages.number_block')
            ->orderBy('garages.number_garage')
            ->select('garages.*', 'garages.id_garage as garage_id')
            ->get()
            ->groupBy('number_block');

        return view('PagesForChairman.profile.pivotTableCoop.pagePayment', compact('year', 'coopData', 'idCoop', 'usersCoop', 'blocksWithGarages', 'monthNow'));
    }

    protected function ChairmanMyCoopPaymentPost(Request $request, $idCoop)
    {
        try {
            $data = request()->validate([
                'id_user' => 'required|numeric|min:1',
                'id_garage' => 'required|numeric|min:1',
                'payment_value' => [
                    'nullable',
                    'integer',
                    'min:0',
                    Rule::notIn([-1]),
                ],
                'id_year' => ['required', 'numeric', new MyYear()],
                'id_month_number' => 'required|numeric|between:1,12',
            ]);

            $selectUser = UserAndCoop::where('id_coop', $idCoop)
                ->where('user_id', $data['id_user'])
                ->whereHas('garages', function ($query) use ($data, $idCoop) {
                    $query->where('id_coop', $idCoop)
                        ->where('id_garage', $data['id_garage']);
                })->first();

            if (!$selectUser) {
                return response()->json(['error' => 'Не найден участник']);
            }

            $currentDate = Date::now();
            $targetDate = Date::create($data['id_year'], $data['id_month_number'], 1);

            // Проверка разницы в месяцах
            if ($currentDate->diffInMonths($targetDate) > 12) {
                return response()->json(['error' => 'Запрещено изменять оплату: можно менять только записи не старше двух месяцев']);
            }

            $payMents = Payments::where('id_coop', $idCoop)
                ->where('id_garage', $data['id_garage'])
                ->where('type_payment', 0) // 0 - это тип оплаты за электричество
                ->whereYear('date_indication', $data['id_year'])
                ->whereMonth('date_indication', $data['id_month_number'])
                ->first();

            if ((!$payMents || $payMents->payment_value == 0) && $data['payment_value'] == 0) {
                return response()->json(['error' => "Без изменений"]);
            }


            if ($payMents) {
                $oldPaymentValue = $payMents->payment_value ?? 0;
                $newPaymentValue = $data['payment_value'] ?? 0;

                // Обновляем значение оплаты
                $payMents->update(['payment_value' => $newPaymentValue]);

                // Обновляем или вставляем запись в таблицу total_paid_for_electricity
                TotalPaidForElectricity::updateOrCreate(
                    [
                        'date_indication' => $targetDate,
                        'id_coop' => $idCoop,
                    ],
                    [
                        'value' => DB::raw("value + " . ($newPaymentValue - $oldPaymentValue)),
                    ]
                );

            } else {
                if ($data['payment_value'] !== '0' && $data['payment_value'] !== null) {
                    $newPaymentValue = $data['payment_value'];

                    Payments::create([
                        'id_coop' => $idCoop,
                        'id_garage' => $data['id_garage'],
                        'payment_value' => $newPaymentValue,
                        'type_payment' => 0,
                        'date_indication' => $targetDate,
                    ]);

                    // Обновляем или вставляем запись в таблицу total_paid_for_electricity
                    TotalPaidForElectricity::updateOrCreate(
                        [
                            'date_indication' => $targetDate,
                            'id_coop' => $idCoop,
                        ],
                        [
                            'value' => DB::raw("value + " . $newPaymentValue),
                        ]
                    );
                }
            }

            return response()->json(['success' => "Сохранено"]);
        } catch (\Exception $e) {
            return response()->json(['error' => "Что-то пошло не так, повторите попытку позже"]);
        }
    }

    //Контроллеры (ChairmanMyCoopPaymentOther, ChairmanMyCoopPaymentOtherPost) временно не работают,
    // так как нужно многое продумать (дата комментария: 04.11.2024)
    // дата когда закончу: где-то в следующем году :)
    protected function ChairmanMyCoopPaymentOther(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = request()->validate([
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'id_month_number' => 'required|numeric|between:1,12',
                    'type_payment' => 'required|integer|in:3,4,5,6,7,8,9,10,11',
                ]);
            } catch (ValidationException|\Exception $e) {
                return response()->json(['error' => 'Ошибка валидации'], 500);
            }
            try {
                if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                    return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже']);
                }
                $fees = Fees::where('id_coop', $idCoop)
                    ->whereYear('date_indication', $data['id_year'])
                    ->where('type_payment', $data['type_payment'])
                    ->whereMonth('date_indication', $data['id_month_number'])->pluck('value')->first();
                $valuePayMents = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
                    ->leftJoin('garages', function ($join) use ($idCoop) {
                        $join->on('garages.user_id', '=', 'users.id')
                            ->where('garages.id_coop', $idCoop);
                    })
                    ->leftJoin('payments', function ($join) use ($idCoop, $data) {
                        $join->on('payments.id_garage', '=', 'garages.id_garage')
                            ->where('payments.id_coop', $idCoop)
                            ->where('payments.type_payment', $data['type_payment'])
                            ->whereColumn('payments.id_garage', 'garages.id_garage')
                            ->whereYear('payments.date_indication', $data['id_year'])
                            ->whereMonth('payments.date_indication', $data['id_month_number']);
                    })
                    ->where('user_and_coop.id_coop', $idCoop)
                    ->orderBy('users.fio')
                    ->select(
                        'users.id',
                        'users.fio',
                        'garages.id_garage as garage_id',  // Альтернативное имя для избежания коллизии
                        'garages.number_garage',
                        'garages.number_block',
                        'payments.*'
                    )
                    ->get();
                return response()->json(['blockMessages' => $valuePayMents, 'fee' => $fees, 'year' => $data['id_year'], 'monthNow' => $data['id_month_number']]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                return response()->json(['error' => 'Ошибка валидации'], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }
        }
        $year = Date::now()->format('Y');
        $monthNow = Date::now()->format('m');
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $fees = Fees::where('id_coop', $idCoop)
            ->whereYear('date_indication', $year)
            ->where('type_payment', 3)
            ->whereMonth('date_indication', $monthNow)->pluck('value')->first();
        $usersCoop = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
            ->leftJoin('garages', function ($join) use ($idCoop) {
                $join->on('garages.user_id', '=', 'users.id')
                    ->where('garages.id_coop', $idCoop);
            })
            ->leftJoin('payments', function ($join) use ($idCoop, $year, $monthNow) {
                $join->on('payments.id_garage', '=', 'garages.id_garage')
                    ->where('payments.id_coop', $idCoop)
                    ->where('payments.type_payment', 3)
                    ->whereColumn('payments.id_garage', 'garages.id_garage')
                    ->whereYear('payments.date_indication', $year)
                    ->whereMonth('payments.date_indication', $monthNow);
            })
            ->where('user_and_coop.id_coop', $idCoop)
            ->orderBy('users.fio')
            ->select(
                'users.id',
                'users.fio',
                'garages.id_garage as garage_id',  // Альтернативное имя для избежания коллизии
                'garages.number_garage',
                'garages.number_block',
                'payments.*'
            )
            ->get();

        $blocksWithGarages = Garages::where('id_coop', $idCoop)
            ->leftJoin('users', 'garages.user_id', '=', 'users.id')
            ->orderBy('garages.number_block')
            ->orderBy('garages.number_garage')
            ->select('garages.*', 'garages.id_garage as garage_id')
            ->get()
            ->groupBy('number_block');

        return view('PagesForChairman.profile.pivotTableCoop.pagePaymentOther', compact('fees', 'year', 'coopData', 'idCoop', 'usersCoop', 'blocksWithGarages', 'monthNow'));
    }

    protected function ChairmanMyCoopPaymentOtherPost(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            try {
                $data = request()->validate([
                    'id_user' => 'required|numeric|min:1',
                    'id_garage' => 'required|numeric|min:1',
                    'payment_value' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        Rule::notIn([-1]),
                    ],
                    'id_year' => ['required', 'numeric', new MyYear()],
                    'id_month_number' => 'required|numeric|between:1,12',
                    'type_payment' => 'required|integer|in:3,4,5,6,7,8,9,10,11'
                ]);
                if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                    return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже'], 500);
                }
                $selectUser = UserAndCoop::where('id_coop', $idCoop)
                    ->where('user_id', $data['id_user'])
                    ->whereHas('garages', function ($query) use ($data, $idCoop) {
                        $query->where('id_coop', $idCoop)
                            ->where('id_garage', $data['id_garage']);
                    })->first();

                if (!$selectUser) {
                    return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже'], 500);
                }
                $balanceGarage = UserBalance::where('id_garage', $data['id_garage'])->first();
                if (!$balanceGarage) {
                    $balanceGarage = UserBalance::create([
                        'id_garage' => $data['id_garage'],
                        'balance' => 0,
                    ]);
                }
                $payMents = Payments::where('id_coop', $idCoop)
                    ->where('id_garage', $data['id_garage'])
                    ->where('type_payment', $data['type_payment']) // от 3 до 11 (2 - это вода)
                    ->whereYear('date_indication', $data['id_year'])
                    ->whereMonth('date_indication', $data['id_month_number'])
                    ->first();
                if ((!$payMents || $payMents->payment_value == 0) && $data['payment_value'] == 0) {
                    return response()->json(['error' => "Без изменений"]);
                }
                $feeAmount = Fees::where('id_coop', $idCoop)
                    ->whereYear('date_indication', $data['id_year'])
                    ->whereMonth('date_indication', $data['id_month_number'])
                    ->where('type_payment', $data['type_payment'])
                    ->pluck('value')
                    ->first();
                if(!$feeAmount) {
                    return response()->json(['error' => "Установите сумму сбора!"]);
                }
                // Основная логика
                if ($payMents && $data['payment_value'] !== 0) {
                    $oldPaymentValue = $payMents->payment_value;
                    $newPaymentValue = $data['payment_value'];
                    $difference = $newPaymentValue - $oldPaymentValue;

                    // Проверка внесенной суммы относительно суммы сбора
                    if ($newPaymentValue == $feeAmount) {
                        // Если внесенная сумма равна сбору, баланс не изменяется
                        // Но здесь вы можете убрать или оставить эту проверку для читабельности
                    } elseif ($newPaymentValue > $feeAmount) {
                        // Если внесенная сумма больше сбора, добавляем разницу в баланс
                        $balanceGarage->update(['balance' => $balanceGarage->balance + ($difference)]);
                    } else {
                        // Если внесенная сумма меньше сбора, уменьшаем баланс на разницу
                        $balanceGarage->update(['balance' => $balanceGarage->balance - $difference]);
                    }

                    // Обновляем запись об оплате с новой суммой
                    $payMents->update(['payment_value' => $newPaymentValue]);
                } else {
                    if ($data['payment_value'] !== '0' && $data['payment_value'] !== null) {
                        $newPaymentValue = $data['payment_value'];

                        // Создаем новую запись для оплаты
                        Payments::create([
                            'id_coop' => $idCoop,
                            'id_garage' => $data['id_garage'],
                            'payment_value' => $newPaymentValue,
                            'type_payment' => $data['type_payment'],
                            'date_indication' => $data['id_year'] . '-' . $data['id_month_number'] . '-' . Date::now()->format('d'),
                        ]);

                        // Обновление баланса в зависимости от суммы оплаты и сбора
                        if ($newPaymentValue == $feeAmount) {
                            $balanceGarage->update(['balance' => $balanceGarage->balance - $feeAmount]);
                        } elseif ($newPaymentValue > $feeAmount) {
                            $balanceGarage->update(['balance' => $balanceGarage->balance + ($newPaymentValue - $feeAmount)]);
                        } else {
                            $balanceGarage->update(['balance' => $balanceGarage->balance - ($feeAmount - $newPaymentValue)]);
                        }
                    }
                }
                $message = "Сохранено";
                return response()->json(['success' => $message]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();

                if ($errors->has('id_year')) {
                    $yearErrors = $errors->get('id_year');
                    // Объединяем массив ошибок в строку
                    $yearError = implode(', ', $yearErrors);
                    return response()->json(['error' => $yearError]);
                }
                return response()->json(['error' => 'Ошибка валидации'], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку'], 500);
            }
        }
        try {
            $data = request()->validate([
                'id_year' => ['required', 'integer', new MyYear()],
                'id_month_number' => 'required|numeric|between:1,12',
                'value' => 'required|integer|min:1',
                'type_payment' => 'required|integer|in:3,4,5,6,7,8,9,10,11'
            ]);
            $selectFee = Fees::where('id_coop', $idCoop)
                ->where('type_payment', $data['type_payment'])
                ->whereYear('date_indication', $data['id_year'])
                ->whereMonth('date_indication', $data['id_month_number'])->first();
            if ($selectFee) {
                $selectFee->update(['value' => $data['value']]);
                return back()->with(['success' => 'Успешно установлен сбор']);
            }
            Fees::create([
                'id_coop' => $idCoop,
                'type_payment' => $data['type_payment'],
                'value' => $data['value'],
                'date_indication' => $data['id_year'] . '-' . $data['id_month_number'] . '-' . Date::now()->format('d H:i:s'),
            ]);
            return back()->with(['success' => 'Успешно установлен сбор']);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            if ($errors->has('id_year')) {
                $yearErrors = $errors->get('id_year');
                // Объединяем массив ошибок в строку
                $yearError = implode(', ', $yearErrors);
                return  back()->with(['error' => $yearError]);
            }
            return back()->with(['error' => 'Ошибка валидации'], 500);
        } catch (\Exception $e) {
            return back()->with(['error' => 'Что-то пошло не так, повторите попытку'], 500);
        }
    }

//Участники кооператива
    protected function ParticipantsCoop(Request $request, $idCoop)
    {
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $garageBlocks = CooperativeBlocks::where('id_coop', $idCoop)->get();

        $dataUsers = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
            ->leftJoin('garages', function ($join) use ($idCoop) {
                $join->on('garages.user_id', '=', 'users.id')
                    ->where('garages.id_coop', $idCoop);
            })
            ->where('user_and_coop.id_coop', $idCoop)
            ->select(
                'users.id',
                'users.fio',
                'users.phone',
                'users.second_phone',
                'users.home_phone',
                'users.email',
                'garages.number_garage',
                'garages.number_block'
            )
            ->orderBy('users.fio')
            ->get();

        // Группируем пользователей по номеру блока, для тех у кого есть гараж
        $usersGroupedByBlocks = $dataUsers->whereNotNull('number_garage')->groupBy('number_block')->map(function ($users) {
            return $users->groupBy('id')->map(function ($userGarages) {
                $userData = $userGarages->first();
                $garages = $userGarages->map(function ($garage) {
                    return $garage->number_garage;
                })->toArray();
                return [
                    'id' => $userData->id,
                    'fio' => $userData->fio,
                    'phone' => $userData->phone,
                    'second_phone' => $userData->second_phone,
                    'home_phone' => $userData->home_phone,
                    'email' => $userData->email,
                    'garages' => $garages
                ];
            })->values();
        });

        // Получаем пользователей без гаража
        $notGarageArrayUsers = $dataUsers->whereNull('number_garage')->map(function ($user) {
            return [
                'id' => $user->id,
                'fio' => $user->fio,
                'phone' => $user->phone,
                'second_phone' => $user->second_phone,
                'home_phone' => $user->home_phone,
                'email' => $user->email,
                'garages' => [] // Пустой массив, так как гаражей нет
            ];
        });

        $emptyBlocks = $garageBlocks->filter(function ($block) use ($usersGroupedByBlocks) {
            return !isset($usersGroupedByBlocks[$block->number_block]);
        });

        return view('PagesForChairman.profile.pivotTableCoop.participantsCoop', compact('coopData', 'idCoop', 'usersGroupedByBlocks', 'garageBlocks', 'emptyBlocks', 'notGarageArrayUsers'));
    }


    protected function coopMeters(Request $request, $idCoop)
    {
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $meters = MeterNumbersCoops::where('id_coop', $idCoop)
            ->orderBy('id_meter_number', 'desc')
            ->get();
        return view('PagesForChairman.profile.pivotTableCoop.coopMeters', compact(['meters', 'coopData', 'idCoop']));
    }

    protected function coopMetersPost(Request $request, $idCoop)
    {
        try {
            $data = $request->validate([
                'meter_number' => 'required|integer|min:0',
                'initially_kw' => 'required|integer|min:0',
                'number_id' => 'integer|min:1',
                'idPost' => 'required|integer|min:0',
            ]);

            if ($data['idPost'] == 0) {
                $meter_readings_users = MeterReadingsUsers::where('id_coop', $idCoop)
                    ->where('status', 'pending')
                    ->first();
                if ($meter_readings_users) {
                    return redirect()->back()->with('error', 'Нельзя добавить счётчик, пока показания участников в ожидании');
                }
                $number_meter = MeterNumbersCoops::where('id_coop', $idCoop)
                    ->whereYear('creation_date', Date::now('Y'))->get();
                if (count($number_meter) > 3) {
                    return redirect()->back()->with('error', 'Нельзя добавить больше 3 счётчиков в год');
                }
                MeterNumbersCoops::where('id_coop', $idCoop)->update(['active' => 0]);
                MeterNumbersCoops::create([
                    'id_coop' => $idCoop,
                    'meter_number' => $data['meter_number'],
                    'initially_kw' => $data['initially_kw'],
                    'active' => 1,
                    'creation_date' => Date::now(),
                ]);
                return redirect()->back()->with('success', 'Счётчик успешно добавлен!');
            }

            // Обработка для idPost == 1
            if ($data['idPost'] == 1) {
                $number_meter = MeterNumbersCoops::where('id_coop', $idCoop)
                    ->where('id_meter_number', $data['number_id'])
                    ->where('active', 1)->first();

                if ($number_meter) {
                    // Обновление данных
                    $number_meter->update(['meter_number' => $data['meter_number'], 'initially_kw' => $data['initially_kw']]);
                    return redirect()->back()->with('success', 'Успешно изменено!');
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


//Функция для проверки соответствия координат от пользователя
    protected function getCountryFromCoordinates($city, $address, $latitude, $longitude)
    {
        $apiKeys = [
            'e268d199-9698-48cd-ac43-a489a6d43bbd',
            'aa1a4f1a-2153-49ec-bbc8-db91be38ff21',
        ];

        foreach ($apiKeys as $apiKey) {
            $client = new Client();

            try {
                $response = $client->get('https://geocode-maps.yandex.ru/1.x/', [
                    'query' => [
                        'apikey' => $apiKey,
                        'geocode' => $longitude . ',' . $latitude,
                        'format' => 'json',
                    ]
                ]);

                $data = json_decode($response->getBody(), true);

                // Проверяем, что запрос успешен и есть результат
                if (isset($data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'])) {
                    $geoData = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'];

                    // Проверяем, что код страны - Россия (RU)
                    if ($geoData['country_code'] === 'RU') {
                        // Проверяем соответствие города и адреса
                        $fetchedCity = $geoData['Components'][3]['name'] ?? '';
                        $fetchedAddress = $geoData['formatted'] ?? '';

                        if (str_contains($fetchedAddress, $city) && str_contains($fetchedAddress, $address)) {
                            return true;
                        } else {
                            return '[1]';
                        }
                    } else {
                        return '[2]';
                    }
                }
            } catch (GuzzleException $e) {
                // Обработка ошибки 403 Forbidden
                if ($e->getResponse() && $e->getResponse()->getStatusCode() === 403) {
                    continue; // Пробуем следующий ключ в случае ошибки 403
                }
                continue; // Пробуем следующий ключ в случае других ошибок
            } catch (Exception $e) {
                continue; // Пробуем следующий ключ в случае общих ошибок
            }
        }

        // Если все ключи исчерпаны и запрос не удался
        return false;
    }

    protected function ChairmanSelectCoopsAJAX(Request $request)
    {
        if ($request->ajax()) {
            $key = 'ChairmanSelectCoopsAJAX|' . $request->ip(); // Уникальный ключ для IP-адреса

            if (RateLimiter::tooManyAttempts($key, 30)) { // Максимум 30 запросов
                return response()->json(['error' => 'Слишком много запросов. Пожалуйста, подождите.'], 429);
            }

            RateLimiter::hit($key, 60); // Запрос считается в течение 60 секунд
            $coordinates = $request->input('bounds');
            if (!$coordinates) {
                return response()->json(['error' => 'Ошибка получения данных'], 500);
            }
            try {
                $minLatitude = number_format($coordinates[0][0], 6, '.', '');
                $maxLatitude = number_format($coordinates[1][0], 6, '.', '');
                $minLongitude = number_format($coordinates[0][1], 6, '.', '');
                $maxLongitude = number_format($coordinates[1][1], 6, '.', '');

                $cooperatives = Cooperatives::select('cooperatives.*', 'users.fio')
                    ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
                    ->withCount('amountGarageBlock')
                    ->where('users.id_al', 2)
                    ->whereBetween('cooperatives.latitude', [$minLatitude, $maxLatitude])
                    ->whereBetween('cooperatives.longitude', [$minLongitude, $maxLongitude])
                    ->get();

                $applicationStatus = ApplicationsForAccessions::withTrashed()
                    ->where('user_id', Auth::id())
                    ->get();

                $response = [
                    'cooperatives' => $cooperatives,
                    'applicationStatus' => $applicationStatus,
                ];

                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Ошибка получения данных'], 500);
            }
        }
        return redirect()->back();
    }

    protected function deleteOldImage($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop)
    {
        // Проверяем, есть ли связанные записи с показаниями
        if ($readings_meter_img->meterReadingsBlocks->isNotEmpty()) {
            $meterReading = $readings_meter_img->meterReadingsBlocks->first();

            // Проверяем, существует ли старое изображение
            if ($meterReading->img_meter) {
                $oldImagePath = '../../StoragePiGarage/CoopMeters/' . $meter['id_year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания рядов' . '/' . $meter['id_block'] . '/' . $meterReading->img_meter;

                // Удаляем старое изображение, если оно существует
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }
        }
    }

    protected function deleteOldImageCoops($readings_meter_img, $meter, $nameAddress, $nameCity, $nameCoop)
    {
        // Проверяем, есть ли связанные записи с показаниями
        if ($readings_meter_img->meterReadingsBlocks->isNotEmpty()) {
            $meterReading = $readings_meter_img->meterReadingsCoops->first();

            // Проверяем, существует ли старое изображение
            if ($meterReading->img_meter) {
                $oldImagePath = '../../StoragePiGarage/CoopMeters/' . $meter['id_year'] . '/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . 'Показания общего счётчика' . '/' . $meterReading->img_meter;

                // Удаляем старое изображение, если оно существует
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }
        }
    }

}
