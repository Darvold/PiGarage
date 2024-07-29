<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\Cooperatives;
use App\Models\CooperativesBlocksLossesKw;
use App\Models\coopLosses;
use App\Models\MetersReadings;
use App\Models\PayMents;
use App\Models\Rates;
use App\Models\UserAndCoop;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jenssegers\Date\Date;
use Mockery\Exception;
use App\Rules\NoNegativeNumbers;
use GuzzleHttp\Client;

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
        $cooperatives = Cooperatives::select('cooperatives.*', 'users.*')
            ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
            ->where('users.id_al', 2)
            ->get();
        $applicationStatus = ApplicationsForAccessions::where('user_id', Auth::id())
            ->get();

        return view('PagesForChairman.profile.createMyCoop',
            compact('cooperatives', 'applicationStatus'));

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
                    'regex:/^[a-zA-Z0-9\s\-]+$/u' // Разрешает только буквы, цифры, пробелы и дефисы
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
                return redirect()->back()->with(['error' => 'Название кооператива должно состоять минимум из 3-х символов до 250'])->withInput();
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
            if ($test === true) {
                // Координаты принадлежат России
                $data['id_point'] = $data['latitude'] . ',' . $data['longitude'];
            }
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
                'id_point' => $data['id_point'],

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
                $location = '%' . $selectedRegion . ', ' . $selectedCity . '%';
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
        $cooperatives = Cooperatives::select('cooperatives.*', 'users.*')
            ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
            ->withCount('amountGarageBlock')
            ->where('users.id_al', 2)
            ->get();
        $applicationStatus = ApplicationsForAccessions::where('user_id', Auth::id())
            ->get();
        return view('PagesForChairman.profile.connectCoop',
            compact('cooperatives', 'applicationStatus'));

    }

    protected function ChairmanConnectCoopPost()
    {
        $id_message = request()->input('id_message');
        try {
            if ($id_message == 1) {
                $data = request()->validate([
                    'id_coop' => 'required|numeric|min:0',
                    'garageData' => ['required', new NoNegativeNumbers],
                ]);

            } else {
                $data = request()->validate([
                    'id_coop' => 'required|numeric|min:0',
                ]);
            }
        } catch (ValidationException  $e) {
            $garageData = request()->input('garageData', null);
            $garagesDataJson = json_decode($garageData, true);
            return redirect()->back()
                ->with(['error' => 'Что-то пошло не так, возможно установлено отрицательное значение'])
                ->with('garageData', $garagesDataJson);
        }
        if ($id_message == 1) {
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
            if ($restoreApplication) {
                $restoreApplication->update(['status' => 'pending', 'send_date' => Date::now()]);
                foreach ($garagesData as $gData) {
                    //dd($garagesData);
                    ApplicationsGarageToCoop::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'id_block' => null,
                            'id_coop' => $data['id_coop'],
                            'status' => 'delete',
                        ],
                        [
                            'number_garage' => $gData['number_garage'],
                            'number_block' => $gData['number_block'],
                            'number_meter' => $gData['number_meter'],
                            'status' => 'pending',
                            'date_received' => Date::now()
                        ]
                    );
                }
                return redirect()->back()->with('success', 'Заявка успешно отправлена!');
            }

            ApplicationsForAccessions::create([
                'user_id' => Auth::id(),
                'id_coop' => $data['id_coop'],
                'status' => 'pending',
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
            return redirect()->back()->with('success', 'Заявка успешно отправлена!');
        }
        if ($id_message == 2) {
            ApplicationsForAccessions::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->where('status', 'pending')->update(['status' => 'delete']);
            ApplicationsGarageToCoop::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->where('status', 'pending')->update(['status' => 'delete']);
            return redirect()->back()->with('success', 'Заявка успешно отменена!');
        }
        return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');

    }


    //Главная таблица, сводная таблица кооператива
    protected function ChairmanMyCoopPivotTable($idCoop)
    {
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        return view('PagesForChairman.profile.pivotTableCoop.myCoopPivotTable', compact('coopData', 'idCoop'));

    }

    //Гаражные блоки, изменение кВт
    protected function ChairmanMyCoopBlocks(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            $numberBlock = $request->input('numberBlock');
            $numberYear = $request->input('numberYear');
            $id_block = $request->input('id_block');
            $blockDefaultKw = CooperativeBlocks::where('id_block', $id_block)->first();
            $blocksKW = CooperativesBlocksLossesKw::where('id_block', $id_block)
                ->whereYear('date_indication', $numberYear)
                ->orderByRaw('MONTH(date_indication)')
                ->get();

            return response()->json(['blockMessages' => $blocksKW, 'id_block' => $id_block,
                'number_block' => $numberBlock, 'defaultKW' => $blockDefaultKw]);
        }
        $blocks = CooperativeBlocks::where('id_coop', $idCoop)->get();
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $year = Date::now()->format('Y');
        return view('PagesForChairman.profile.pivotTableCoop.myCoopBlocks', compact('coopData', 'year', 'idCoop', 'blocks'));

    }

    protected function ChairmanMyCoopBlocksPost($idCoop)
    {
        $id_message = request()->input('id_message');
        if ($id_message == 1) {
            try {
                $idCoop = request()->validate([
                    'id_coop' => 'required|numeric|min:0',
                ])['id_coop'];

                $blocks = CooperativeBlocks::where('id_coop', $idCoop)->get();

                $count_block = count($blocks);

                if ($count_block < 10) {
                    $new_number_block = $count_block + 1;
                    CooperativeBlocks::create([
                        'id_coop' => $idCoop,
                        'number_block' => $new_number_block,
                    ]);
                    return back()->with('success', "Гаражный блок успешно создан $new_number_block/10");
                } else {
                    return back()->with('info', "Создано максимальное количество гаражных блоков $count_block/10");
                }
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        if ($id_message == 2) {
            try {
                $garage_block = request()->validate([
                    'default_kw' => 'required|numeric|min:0',
                    'id_block' => 'required|numeric|min:0',
                ]);
                $coopBlock = CooperativeBlocks::where('id_block', $garage_block['id_block'])
                    ->update(['default_kw' => $garage_block['default_kw']]);
                if ($coopBlock) {
                    return back()->with('success', "Успешно изменилось % потерь по умолчанию = {$garage_block['default_kw']}");
                }
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }
        }
        if ($id_message == 3) {
            try {
                $mouthKW = request()->validate([
                    'lossesNumber' => 'required|numeric|min:0',
                    'id_block' => 'required|numeric|min:0',
                    'id_year' => 'required|numeric|min:0',
                    'id_month_number' => 'required|numeric|min:0',
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

                return back()->with('success', $message);
            } catch (\Exception $e) {
                return back()->with('error', "Что-то пошло не так, повторите попытку позже");
            }

        }
        return back()->with('error', 'Что-то пошло не так, повторите запрос позже');
    }


    protected function MessagesMeters(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            $id_block = $request->input('id_block');
            $meters_readings = MetersReadings::with('garages.user', 'cooperative')
                ->where('id_block', $id_block)
                ->where('id_coop', $idCoop)
                ->where('status', 'pending')
                ->get();
            $folderPaths = [];
            foreach ($meters_readings as $index => $file_url) {
                $fio = $file_url->garages[0]->user->fio;
                $nameCoop = $file_url->cooperative->name;
                $regionFolder = explode(',', $file_url->cooperative->address);
                $cityFolder = explode(',', $file_url->cooperative->city);
                $nameAddress = trim($regionFolder[0]);
                $nameCity = trim($cityFolder[0]);
                $nameFile = $file_url->img_meter;
                $folderPath = '../../StoragePiGarage/CoopMeters/' . $nameAddress . '/' . $nameCity . '/' . $nameCoop . '/' . $fio . '/' . $nameFile;

                // Проверяем существование файла перед чтением
                if (file_exists($folderPath) && is_readable($folderPath)) {
                    // Читаем содержимое файла
                    $fileContent = file_get_contents($folderPath);

                    // Проверяем, удалось ли прочитать файл
                    if ($fileContent !== false) {
                        $base64Image = base64_encode($fileContent);
                        $imgHtml = '<a id="lightbox-image-' . $index . '" href="data:image/jpg/jpeg/png;base64,' . $base64Image .
                            '"data-title="Фото счётчика" data-lightbox="image-' . $index . '">' .
                            '<img src="data:image/jpg/jpeg/png;base64,' . $base64Image . '" alt="Фото счётчика"></a>';
                        $folderPaths[] = $imgHtml;
                    } else {
                        // Обработка ошибки чтения файла
                        $folderPaths[] = '<span style="color: red;">Ошибка чтения файла</span>';
                    }
                } else {
                    // Обработка отсутствия файла
                    $folderPaths[] = '<span style="color: red;">Файл не существует или не доступен для чтения</span>';
                }
            }

            return response()->json([
                'id_block' => $id_block,
                'metersReadings' => $meters_readings,
                'folderPaths' => $folderPaths
            ]);
        }
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $blocks = CooperativeBlocks::where('id_coop', $idCoop)->get();
        return view('PagesForChairman.communication.messagesMeters',
            compact('blocks', 'coopData', 'idCoop'));

    }

    protected function MessagesMetersPost(Request $request)
    {
        $idMessage = $request->input('idMessage');
        $numberMeter = $request->input('numberMeter');
        $idReading = $request->input('idReading');
        $metersReadings = MetersReadings::where('id_reading', $idReading);
        if ($idMessage == 0) {
            $metersReadings->update([
                'status' => 'accepted',
                'kw_meter' => $numberMeter
            ]);
        } elseif ($idMessage == 1) {
            $metersReadings->update(['status' => 'canceled']);
        } else {
            return response()->json(['response' => 'error']);
        }

        if ($metersReadings->get()) {
            return response()->json(['response' => $idReading]);
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
                'id_year' => 'required|numeric|min:0',
                'id_month_number' => 'required|numeric|min:0',
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
                'id_year' => 'required|numeric|min:0',
                'id_month_number' => 'required|numeric|min:0',
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

        } catch (\Exception $e) {
            return back()->with('error', "Что-то пошло не так, повторите попытку позже");
        }

    }


    protected function ChairmanMyCoopPayment(Request $request, $idCoop)
    {
        if ($request->ajax()) {
            $data = request()->validate([
                'id_year' => 'required|numeric|min:0',
                'id_month_number' => 'required|numeric|min:0',
            ]);
            if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже']);
            }
            $valuePayMents = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
                ->leftJoin('payments', function ($join) use ($idCoop, $data) {
                    $join->on('payments.user_id', '=', 'users.id')
                        ->where('payments.id_coop', $idCoop)
                        ->whereYear('payments.date_indication', $data['id_year'])
                        ->whereMonth('payments.date_indication', $data['id_month_number']);
                })
                ->where('user_and_coop.id_coop', $idCoop)
                ->orderByRaw('users.fio')
                ->get();
            return response()->json(['blockMessages' => $valuePayMents, 'year' => $data['id_year'], 'monthNow' => $data['id_month_number']]);
        }
        $year = Date::now()->format('Y');
        $monthNow = Date::now()->format('m');
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        $usersCoop = UserAndCoop::leftJoin('users', 'user_and_coop.user_id', '=', 'users.id')
            ->leftJoin('payments', function ($join) use ($idCoop, $year, $monthNow) {
                $join->on('payments.user_id', '=', 'users.id')
                    ->where('payments.id_coop', $idCoop)
                    ->whereYear('payments.date_indication', $year)
                    ->whereMonth('payments.date_indication', $monthNow);
            })
            ->where('user_and_coop.id_coop', $idCoop)
            ->orderByRaw('users.fio')
            ->get();

        return view('PagesForChairman.profile.pivotTableCoop.pagePayment', compact('year', 'coopData', 'idCoop', 'usersCoop', 'monthNow'));
    }

    protected function ChairmanMyCoopPaymentPost(Request $request, $idCoop)
    {
        try {
            $data = request()->validate([
                'id_user' => 'required|numeric|min:0',
                'payment_value' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    Rule::notIn([-1]),
                ],
                'id_year' => 'required|numeric|min:0',
                'id_month_number' => 'required|numeric|min:0',
            ]);
            if (($data['id_year'] < 2023 || $data['id_year'] > Date::now()->format('Y')) || !preg_match('/^(0[1-9]|1[0-2])$/', $data['id_month_number'])) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже']);
            }
            $selectUser = UserAndCoop::where('id_coop', $idCoop)
                ->where('user_id', $data['id_user'])->first();
            if (!$selectUser) {
                return response()->json(['error' => 'Что-то пошло не так, повторите попытку позже']);
            }
            $payMents = PayMents::where('id_coop', $idCoop)
                ->where('user_id', $data['id_user'])
                ->whereYear('date_indication', $data['id_year'])
                ->whereMonth('date_indication', $data['id_month_number'])
                ->first();
            if ($payMents) {
                $paymentValue = ($data['payment_value'] === '0' || $data['payment_value'] === null) ? null : $data['payment_value'];
                $payMents->update(['payment_value' => $paymentValue]);
                $message = "Сохранено";
            } else {
                if ($data['payment_value'] !== '0' && $data['payment_value'] !== null) {
                    PayMents::create([
                        'id_coop' => $idCoop,
                        'user_id' => $data['id_user'],
                        'payment_value' => $data['payment_value'],
                        'date_indication' => $data['id_year'] . '-' . $data['id_month_number'] . '-' . Date::now()->format('d'),
                    ]);
                }
                $message = "Сохранено";
            }
            return response()->json(['success' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => "Что-то пошло не так, повторите попытку позже"]);
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

        $usersGroupedByBlocks = $dataUsers->groupBy('number_block')->map(function ($users) {
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

        $emptyBlocks = $garageBlocks->filter(function($block) use ($usersGroupedByBlocks) {
            return !isset($usersGroupedByBlocks[$block->number_block]);
        });

        return view('PagesForChairman.profile.pivotTableCoop.participantsCoop', compact('coopData', 'idCoop', 'usersGroupedByBlocks', 'garageBlocks', 'emptyBlocks'));
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
}
