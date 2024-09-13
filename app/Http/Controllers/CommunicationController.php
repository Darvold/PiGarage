<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\Garages;
use App\Models\MeterNumbers;
use App\Models\MeterReadings;
use App\Models\UserAndCoop;
use App\Models\Users;
use App\Models\Cooperatives;
use App\Rules\NoNegativeNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Jenssegers\Date\Date;
use Mockery\Exception;
use function Laravel\Prompts\error;

class CommunicationController extends Controller
{
    protected function Applications(Request $request)
    {
        if ($request->ajax()) {
            $idMessage = $request->input('idMessage');
            if ($idMessage == 1) {
                try {
                    // Получите данные из запроса
                    $idCoop = $request->input('idCoop');
                    $coopMessages = ApplicationsForAccessions::join('users', 'applications_for_accessions.user_id', '=', 'users.id')
                        ->where('applications_for_accessions.id_coop', $idCoop)
                        ->where('applications_for_accessions.status', 'pending')
                        ->select('applications_for_accessions.*', 'users.fio as user_fio')
                        ->get();
                    $garagesUsers = ApplicationsGarageToCoop::join('users', 'applications_garage_to_coop.user_id', '=', 'users.id')
                        ->where('applications_garage_to_coop.id_coop', $idCoop)
                        ->where('applications_garage_to_coop.status', 'pending')
                        ->where('applications_garage_to_coop.active', 1)
                        ->select('applications_garage_to_coop.*', 'users.fio as user_fio')
                        ->get();
                    return response()->json(['coopMessages' => $coopMessages, 'garagesUsers' => $garagesUsers]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка"], 500);
                }
            }
            if ($idMessage == 2) {
                try {
                    $idCoop = $request->input('idCoop');
                    $coopMessages = ApplicationsGarageToCoop::join('users', 'applications_garage_to_coop.user_id', '=', 'users.id')
                        ->join('applications_for_accessions', 'applications_for_accessions.user_id', '=', 'users.id')
                        ->where('applications_garage_to_coop.id_coop', $idCoop)
                        ->where('applications_garage_to_coop.status', 'pending')
                        ->where('applications_for_accessions.status', 'accepted')
                        ->select(
                            'users.fio as user_fio',
                            'applications_garage_to_coop.id_block as user_id_block',
                            'applications_garage_to_coop.number_block as user_number_block',
                            'applications_garage_to_coop.number_garage as user_number_garage',
                            'applications_garage_to_coop.number_meter as user_number_meter',
                            'applications_garage_to_coop.user_id as user_id',
                            'applications_garage_to_coop.id_application as number_app',
                        )
                        ->get();

                    return response()->json(['coopMessages' => $coopMessages]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка."], 500);
                }
            }
        }
        $coops = Cooperatives::where('user_id', Auth::id())
            ->withCount('amountGarageBlock')
            ->get();

        return view('PagesForChairman.communication.applications', compact('coops'));
    }

    protected function ApplicationsPost(Request $request)
    {
        // Для пользователей которые в первый раз присоединяются
        // $request->input('ipMessage') == 1 Добавлем пользователя и его гаражи
        // $request->input('ipMessage') == 2 Отмена действия принятие или отказа
        // $request->input('ipMessage') == 3 Отклоняем заявку пользователя и его гаражи

        // Для участников кооп
        // $request->input('ipMessage') == 4 Добавлем пользователя и его гаражи
        // $request->input('ipMessage') == 5 Отклоняем заявку пользователя и его гараж

        if ($request->ajax()) {
            try {
                if ($request->input('garageData')) {
                    //Пользователь с гаражом или гаражами
                    $data = $request->validate([
                        'idUser' => 'required|integer|min:1',
                        'idCoop' => 'required|integer|min:1',
                        'garageData' => ['required', new NoNegativeNumbers]
                    ]);
                } elseif (in_array($request->input('ipMessage'), [4, 5, 6])) {
                    //Участник который хочет присоединить свой гараж
                    $data = $request->validate([
                        'idUser' => 'required|integer|min:1',
                        'idCoop' => 'required|integer|min:1',
                        'idApp' => 'required|integer|min:1',
                        'numberBlock' => 'required|integer|min:1',
                    ]);
                } else {
                    //Пользователь без гаража
                    $data = $request->validate([
                        'idUser' => 'required|integer|min:1',
                        'idCoop' => 'required|integer|min:1',
                    ]);
                }
                $coopUser = Cooperatives::where('user_id', auth::id())
                    ->where('id_coop', $data['idCoop'])
                    ->first();
                if (!$coopUser) {
                    return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                }
                $amount_garages = count($data['garageData']);
                $ApplicationsForAccessionsSelect_amount_garages = ApplicationsForAccessions::withTrashed()
                    ->where('user_id', $data['idUser'])
                    ->where('id_coop', $data['idCoop'])->first();
                if ($amount_garages != $ApplicationsForAccessionsSelect_amount_garages->amount_garages) {
                    return response()->json(['error' => 'Произошла ошибка, пользователь изменил количество гаражей в заявке, обновите страницу'], 500);
                }
            } catch (ValidationException|\Exception  $e) {
                return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
            }
            if ($request->input('ipMessage') == 1) {
                try {
                    $ApplicationsForAccessions_Accepted_Select = ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->whereIn('status', ['accepted', 'delete'])->first();
                    if ($ApplicationsForAccessions_Accepted_Select) {
                        return response()->json(['error' => "Ошибка, заявка уже принята, отменена или не найдена!"], 500);
                    }
                    if ($request->input('garageData')) {
                        foreach ($data['garageData'] as $gData) {
                            $coopBlockCount = CooperativeBlocks::where('id_coop', $data['idCoop'])
                                ->where('number_block', $gData['number_block'])->first();
                            if (!$coopBlockCount && $gData['checked'] === 'true') {
                                return response()->json(['error' => 'Не совпадает количество гаражных блоков, добавьте ещё гаражных блоков'], 500);
                            }
                            $ApplicationsGarageToCoop = ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                                ->where('user_id', $data['idUser'])
                                ->where('id_coop', $data['idCoop'])
                                ->where('status', 'pending')
                                ->first();
                            if (!$ApplicationsGarageToCoop) {
                                return response()->json(['error' => "Ошибка, заявка уже принята или не найдена!"], 500);
                            }

                            if ($gData['checked'] === 'true') {
                                $ApplicationsGarageToCoop->update(['id_block' => $coopBlockCount->id_block,
                                    'status' => 'accepted',
                                    'date_accepted' => Date::now()]);
                            } else {
                                $ApplicationsGarageToCoop->delete();
                            }

                            if ($gData['checked'] === 'true') {
                                $existingGarage = Garages::withTrashed()
                                    ->where('id_application', $gData['id_application'])
                                    ->where('user_id', $data['idUser'])
                                    ->where('id_coop', $data['idCoop'])
                                    ->first();
                                if ($existingGarage) {
                                    $existingGarage->restore();
                                    MeterNumbers::where('id_garage', $existingGarage->id_garage)->update(['meter_number' => $gData['number_meter']]);
                                    $existingGarage->update([
                                        'id_block' => $coopBlockCount->id_block,
                                        'number_garage' => $gData['number_garage'],
                                        'number_block' => $gData['number_block'],
                                    ]);
                                } else {
                                    // Если заявки нет, создаем новую
                                    $newGarage = Garages::create([
                                        'id_application' => $gData['id_application'],
                                        'user_id' => $data['idUser'],
                                        'id_block' => $coopBlockCount->id_block,
                                        'id_coop' => $data['idCoop'],
                                        'number_garage' => $gData['number_garage'],
                                        'number_block' => $gData['number_block'],
                                        'id_meter_number' => null
                                    ]);
                                    $meter_number = MeterNumbers::create([
                                        'id_garage' => $newGarage->id_garage,
                                        'meter_number' => $gData['number_meter'],
                                        'active' => 1,
                                        'creation_date' => Date::now(),
                                    ]);
                                    $newGarage->update(['id_meter_number' => $meter_number->id_meter_number]);
                                }
                            }
                        }
                    }
                    $userAndCoop = UserAndCoop::withTrashed()
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();
                    if ($userAndCoop) {
                        $userAndCoop->restore();
                    } else {
                        // Если записи нет, создаем новую
                        UserAndCoop::create([
                            'user_id' => $data['idUser'],
                            'id_coop' => $data['idCoop'],
                        ]);
                    }
                    ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->update(['status' => 'accepted', 'date_accepted' => Date::now()]);

                    return response()->json(['idUser' => $data['idUser']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                }
            }
            if ($request->input('ipMessage') == 2) {
                try {
                    if ($request->input('garageData')) {
                        $applicationIds = collect($data['garageData'])->pluck('id_application');

                        // Получаем гаражи и проверяем, есть ли показания для них
                        $garagesWithReadings = Garages::withTrashed()
                            ->whereIn('id_application', $applicationIds)
                            ->with('metersReadings') // используем hasMany связь
                            ->get();

                        // Проверяем, если есть любые показания для каждого гаража
                        foreach ($garagesWithReadings as $garage) {
                            if ($garage->metersReadings->isNotEmpty()) {
                                return response()->json(['error' => "Похоже, пользователь уже отправил показания, невозможно отменить"], 500);
                            }
                        }
                        foreach ($data['garageData'] as $gData) {
                            $ipApplication = ApplicationsGarageToCoop::withTrashed()
                                ->where('id_application', $gData['id_application'])
                                ->where('user_id', $data['idUser'])
                                ->where('id_coop', $data['idCoop'])->first();
                            if (!$ipApplication) {
                                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                            }

                            $ipApplication->restore();
                            $ipApplication->update(['id_block' => null, 'status' => 'pending', 'date_accepted' => null]);
                            $garages = Garages::where('id_application', $gData['id_application'])
                                ->where('user_id', $data['idUser'])
                                ->where('id_coop', $data['idCoop'])
                                ->first();
                            if ($garages) {
                                $garages->delete();
                            }
                        }
                    }
                    $ApplicationsForAccessions = ApplicationsForAccessions::withTrashed()
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();

                    if ($ApplicationsForAccessions) {
                        $ApplicationsForAccessions->restore();
                        $ApplicationsForAccessions->update(['status' => 'pending', 'date_accepted' => null]);;
                    }
                    $userAndCoop = UserAndCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();
                    if ($userAndCoop) {
                        $userAndCoop->delete();
                    }

                    return response()->json(['idUser' => $data['idUser']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }

            if ($request->input('ipMessage') == 3) {
                try {
                    $ApplicationsForAccessions_Rejected_Select = ApplicationsForAccessions::withTrashed()
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->whereIn('status', ['rejected', 'delete'])->first();
                    if ($ApplicationsForAccessions_Rejected_Select) {
                        return response()->json(['error' => "Ошибка, заявка уже отклонена, отменена или не найдена!"], 500);
                    }
                    if ($request->input('garageData')) {
                        foreach ($data['garageData'] as $gData) {
                            $ApplicationsGarageToCoop_Rejected = ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                                ->where('user_id', $data['idUser'])
                                ->where('id_coop', $data['idCoop']);
                            $ApplicationsGarageToCoop_Rejected->update(['status' => 'rejected']);
                            $ApplicationsGarageToCoop_Rejected->delete();

                            Garages::where('id_application', $gData['id_application'])
                                ->where('user_id', $data['idUser'])
                                ->where('id_coop', $data['idCoop'])->delete();
                        }
                    }
                    $userAndCoop = UserAndCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();
                    if ($userAndCoop) {
                        // Если запись найдена, обновляем статус
                        $userAndCoop->delete();
                    }
                    // Обновляем статус заявки
                    $ApplicationsForAccessions_Rejected = ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop']);
                    $ApplicationsForAccessions_Rejected->update(['status' => 'rejected']);
                    $ApplicationsForAccessions_Rejected->delete();
                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                }
            }
            if (in_array($request->input('ipMessage'), [4, 5, 6])) {
                $selectGarages = Garages::withTrashed()
                    ->where('id_application', $data['idApp'])
                    ->first();
                $selectMeters = MeterReadings::where('id_garage', $selectGarages->id_garage)
                    ->first();
                if ($selectMeters) {
                    return response()->json(['error' => "Похоже, пользователь уже отправил показания, невозможно отменить"], 500);
                }
            }

            if ($request->input('ipMessage') == 4) {
                try {
                    $coopBlockCount = CooperativeBlocks::where('id_coop', $data['idCoop'])
                        ->where('number_block', $data['numberBlock'])->first();
                    if (!$coopBlockCount) {
                        return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                    }
                    $applicationUserGarage = ApplicationsGarageToCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->where('id_application', $data['idApp'])->first();
                    if (!$applicationUserGarage) {
                        return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                    }
                    $applicationUserGarage->update(['status' => 'accepted', 'date_accepted' => Date::now()]);

                    $existingGarage = Garages::withTrashed()
                        ->where('id_application', $applicationUserGarage->id_application)
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();

                    if ($existingGarage) {
                        $existingGarage->restore();
                        $existingGarage->update([
                            'id_block' => $applicationUserGarage->id_block,
                            'number_garage' => $applicationUserGarage->number_garage,
                            'number_block' => $applicationUserGarage->number_block,
                            'number_meter' => $applicationUserGarage->number_meter
                        ]);
                    } else {
                        // Если заявки нет, создаем новую
                        Garages::create([
                            'id_application' => $applicationUserGarage->id_application,
                            'user_id' => $data['idUser'],
                            'id_block' => $applicationUserGarage->id_block,
                            'id_coop' => $data['idCoop'],
                            'number_garage' => $applicationUserGarage->number_garage,
                            'number_block' => $applicationUserGarage->number_block,
                            'number_meter' => $applicationUserGarage->number_meter
                        ]);
                    }
                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop'], 'success' => true]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                }
            }
            if ($request->input('ipMessage') == 5) {
                try {
                    // Обновляем статус заявки и удаляем соответствующие записи
                    $applicationUserGarage = ApplicationsGarageToCoop::where('id_application', $data['idApp'])
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();

                    if ($applicationUserGarage) {
                        $applicationUserGarage->update(['status' => 'rejected']);
                        $applicationUserGarage->delete();
                    }
                    Garages::where('id_application', $data['idApp'])
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])->delete();
                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                }
            }
            if ($request->input('ipMessage') == 6) {
                try {
                    // Обновляем статус заявки и удаляем соответствующие записи
                    $applicationUserGarage = ApplicationsGarageToCoop::withTrashed()
                        ->where('id_application', $data['idApp'])
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();

                    if ($applicationUserGarage) {
                        $applicationUserGarage->restore();
                        $applicationUserGarage->update(['status' => 'pending', 'date_accepted' => null]);
                    }
                    $garageUser = Garages::where('id_application', $data['idApp'])
                        ->where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])->first();
                    if ($garageUser) {
                        $garageUser->delete();
                    }
                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
        }
        return redirect()->back();
    }

    //Страница заявок пользователя на присоединения к кооперативу
    protected function MyApplicationToCoop()
    {
        $userApplication = ApplicationsForAccessions::withTrashed()
            ->select(
                'applications_for_accessions.*',
                'users.fio',
                'cooperatives.*',
                DB::raw('JSON_ARRAYAGG(
                CASE
                    WHEN applications_for_accessions.amount_garages = 0 THEN NULL
                    ELSE JSON_OBJECT(
                        "number_meter", applications_garage_to_coop.number_meter,
                        "number_garage", applications_garage_to_coop.number_garage,
                        "number_block", applications_garage_to_coop.number_block
                    )
                END
            ) as garages')
            )
            ->leftJoin('cooperatives', 'applications_for_accessions.id_coop', '=', 'cooperatives.id_coop')
            ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
            ->leftJoin('applications_garage_to_coop', function ($join) {
                $join->on('applications_garage_to_coop.id_coop', '=', 'cooperatives.id_coop')
                    ->on('applications_garage_to_coop.user_id', '=', 'applications_for_accessions.user_id')
                    ->where('applications_for_accessions.amount_garages', '!=', 0)
                    ->where('applications_garage_to_coop.active', '=', 1);
            })
            ->where('applications_for_accessions.user_id', Auth::id())
            ->where('users.id_al', 2)
            ->groupBy('applications_for_accessions.id_application')  // Группировка по заявке
            ->orderBy('applications_for_accessions.send_date', 'desc')  // Сортировка от свежих к старым
            ->get();

        // Преобразуем JSON в массивы
        $userApplication->each(function ($app) {
            $app->garages = array_filter(json_decode($app->garages, true)); // Убираем NULL значения
        });

        return view('PagesForChairman.profile.myApplicationToCoop', compact(['userApplication']));
    }

    protected function MyApplicationToCoopPost(Request $request)
    {
        try {
            $data = request()->validate([
                'number_app' => 'required|integer|min:1',
                'id_coop' => 'required|integer|min:1',
                'value' => 'required|in:delete,pending',
            ]);
        } catch (ValidationException|\Exception  $e) {
            return redirect()->back()
                ->with(['error' => "Что-то пошло не так, повторите попытку позже."]);
        }
        try {
            $application = ApplicationsForAccessions::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])->first();

            $garageUsers = ApplicationsGarageToCoop::where('user_id', Auth::id())
                ->where('id_coop', $data['id_coop'])->get();

            if (!$application || $data['number_app'] != $application->id_application) {
                return redirect()->back()
                    ->with(['error' => "Что-то пошло не так, повторите попытку позже."]);
            }
            $message = null;
            if ($data['value'] == 'delete') {
                if ($application->status != 'pending') {
                    return redirect()->back()
                        ->with(['error' => "Неверный статус"]);
                }
                $application->update(['status' => 'delete']);
                $garageUsers->each(function ($garageUser) {
                    $garageUser->update(['status' => 'delete']);
                });
                $message = 'Заявка успешно отменена';
            } elseif ($data['value'] == 'pending') {
                if ($application->status != 'delete') {
                    return redirect()->back()
                        ->with(['error' => "Неверный статус"]);
                }
                $application->update(['status' => 'pending']);
                $garageUsers->each(function ($garageUser) {
                    $garageUser->update(['status' => 'pending']);
                });
                $message = 'Заявка успешно возобновлена';
            }

            return redirect()->back()->with(['success' => $message]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with(['error' => "Что-то пошло не так, повторите попытку позже."]);
        }
    }

    //Контроллер для страницы мессенджера, на будущее
    /*    public function Messages(Request $request)
        {
            return view('PagesForChairman.communication.messages');

        }*/


    //на будущее, заранее создал страницу с отклонёнными заявками
    protected function ApplicationsReject()
    {
        // Получите текущего аутентифицированного пользователя
        $coops = Cooperatives::where('user_id', Auth::id())->get();
        return view('PagesForChairman.communication.ApplicationsReject', compact('coops'));

    }

    protected function ApplicationsRejectPost(Request $request)
    {
        //Можно всё скопировать с контроллера ApplicationsPost но изменив пару значений в запросах
    }
}
