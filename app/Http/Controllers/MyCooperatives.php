<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsCreateNewCoop;
use App\Models\ApplicationsForAccessions;
use App\Models\CooperativeBlocks;
use App\Models\Cooperatives;
use App\Models\CooperativesBlocksLossesKw;
use App\Models\coopLosses;
use App\Models\MetersReadings;
use App\Models\PayMents;
use App\Models\Rates;
use App\Models\UserAndCoop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Jenssegers\Date\Date;
use Mockery\Exception;

class MyCooperatives extends Controller
{
    public function ChairmanMyCoop()
    {

        $myCoops = Cooperatives::where('user_id', Auth::id())->get();

        return view('PagesForChairman.profile.myCoop', compact('myCoops'));
    }

    public function ChairmanCreateMyCoop()
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

    public function ChairmanCreatingCoop()
    {
        $data = request()->validate([
            'name' => 'required',
            'number_meter' => 'required|numeric|min:0',
            'city' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';
        // Объединяем координаты в одну строку
        $data['id_point'] = $data['latitude'] . ',' . $data['longitude'];
        // Удаляем ненужные отдельные поля latitude и longitude
        unset($data['latitude']);
        unset($data['longitude']);
        $data['date_received'] = Date::now();

        ApplicationsCreateNewCoop::create($data);

        return redirect()
            ->route('ChairmanCreateMyCoop.index')
            ->with('success', 'Заявка успешно отправлена!');
    }


    public function ChairmanConnectCoop()
    {
        $cooperatives = Cooperatives::select('cooperatives.*', 'users.*')
            ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
            ->where('users.id_al', 2)
            ->get();
        $applicationStatus = ApplicationsForAccessions::where('user_id', Auth::id())
            ->get();
        return view('PagesForChairman.profile.connectCoop',
            compact('cooperatives', 'applicationStatus'));

    }

    public function JoinTheCoopOrCansel()
    {
        try {
            $data = request()->validate([
                'id_coop' => 'required|numeric|min:0',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');
        }
        $id_message = request()->input('id_message');
        if ($id_message == 1) {
            $data['user_id'] = Auth::id();
            $data['status'] = 'pending';
            $data['send_date'] = Date::now();

            $restoreApplication = ApplicationsForAccessions::withTrashed()
                ->where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->first();
            if ($restoreApplication) {
                // восстанавливаем запись
                $restoreApplication->restore();
                return redirect()->back()->with('success', 'Заявка успешно отправлена!');
            }
            ApplicationsForAccessions::updateOrCreate(
                ['user_id' => $data['user_id'], 'id_coop' => $data['id_coop']],
                $data
            );

            return redirect()->back()->with('success', 'Заявка успешно отправлена!');
        }
        if ($id_message == 2) {
            ApplicationsForAccessions::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])
                ->where('status', 'pending')->delete();

            return redirect()->back()->with('success', 'Заявка успешно отменена!');
        }
        return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку позже');

    }

    public function ChairmanMyCoopPivotTable($idCoop)
    {
        $coopData = Cooperatives::where('id_coop', $idCoop)->first();
        return view('PagesForChairman.profile.myCoopPivotTable', compact('coopData', 'idCoop'));

    }


    public function ChairmanMyCoopBlocks(Request $request, $idCoop)
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

    public function ChairmanMyCoopNewBlocks($idCoop)
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

    public function MessagesMeters(Request $request, $idCoop)
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

    public function MessagesMetersPost(Request $request)
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

    public function ChairmanMyCoopRate(Request $request, $idCoop)
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
        return view('PagesForChairman.profile.pageRate', compact('year', 'coopData', 'idCoop', 'valueRates'));
    }

    public function ChairmanMyCoopRatePost(Request $request, $idCoop)
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

    public function ChairmanMyCoopLosses(Request $request, $idCoop)
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
        return view('PagesForChairman.profile.pageLossesCoop', compact('year', 'coopData', 'idCoop', 'valueRates'));
    }

    public function ChairmanMyCoopLossesPost(Request $request, $idCoop)
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

    public function ChairmanMyCoopPayment(Request $request, $idCoop)
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

        return view('PagesForChairman.profile.pagePayment', compact('year', 'coopData', 'idCoop', 'usersCoop', 'monthNow'));
    }

    public function ChairmanMyCoopPaymentPost(Request $request, $idCoop)
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

}
