<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsForAccessions;
use App\Models\ApplicationsGarageToCoop;
use App\Models\CooperativeBlocks;
use App\Models\Garages;
use App\Models\MetersReadings;
use App\Models\UserAndCoop;
use App\Models\Users;
use App\Models\Cooperatives;
use App\Rules\NoNegativeNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
                        ->get();
                    return response()->json(['coopMessages' => $coopMessages, 'garagesUsers' => $garagesUsers]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "$e"], 500);
                }
            }
            if ($idMessage == 2) {
                try {
                    $idCoop = $request->input('idCoop');
                    $coopMessages = ApplicationsGarageToCoop::join('users', 'applications_garage_to_coop.user_id', '=', 'users.id')
                        ->join('applications_for_accessions', 'applications_for_accessions.user_id', '=', 'users.id')
                        ->where('applications_for_accessions.id_coop', 'accepted')
                        ->where('applications_garage_to_coop.id_coop', $idCoop)
                        ->where('applications_garage_to_coop.status', 'pending')
                        ->select('users.fio as user_fio',
                            'applications_garage_to_coop.id_block as user_id_block',
                            'applications_garage_to_coop.number_block as user_number_block',
                            'applications_garage_to_coop.number_garage as user_number_garage',
                            'applications_garage_to_coop.number_meter as user_number_meter',
                            'applications_garage_to_coop.user_id as user_id')
                        ->get();
                    return response()->json(['coopMessages' => $coopMessages]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка"], 500);
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
        if ($request->ajax()) {
            try {
                $data = $request->validate([
                    'idUser' => 'required|integer',
                    'idCoop' => 'required|integer',
                    'garageData' => ['required', new NoNegativeNumbers]
                ]);
            } catch (ValidationException  $e) {
                return response()->json(['error' => "$e"], 500);
            }
            if (!$data) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }
            $coopUser = Cooperatives::where('user_id', auth::id())
                ->where('id_coop', $data['idCoop'])
                ->first();

            if (!$coopUser) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }
            $applicationUser = applicationsForAccessions::withTrashed()
                ->where('user_id', $data['idUser'])
                ->where('id_coop', $data['idCoop'])
                ->first();
            if (!$applicationUser) {
                return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
            }

            if ($request->input('ipMessage') == 1) {
                try {
                    foreach ($data['garageData'] as $gData) {
                        $coopBlockCount = CooperativeBlocks::where('id_coop', $data['idCoop'])
                            ->where('number_block', $gData['number_block'])->first();
                        if (!$coopBlockCount && $gData['checked'] === 'true') {
                            return response()->json(['error' => 'Не совпадает количество гаражных блоков, добавьте ещё гаражных блоков'], 500);
                        }
                        $ipApplication = ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])->first();
                        if (!$ipApplication) {
                            return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                        }
                        $ApplicationsGarageToCoop = ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])
                            ->where('status', 'pending')->first();
                        if ($ApplicationsGarageToCoop && $gData['checked'] === 'true') {
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
                                $existingGarage->update([
                                    'id_block' => $coopBlockCount->id_block,
                                    'number_garage' => $gData['number_garage'],
                                    'number_block' => $gData['number_block'],
                                    'number_meter' => $gData['number_meter']
                                ]);
                            } else {
                                // Если заявки нет, создаем новую
                                Garages::create([
                                    'id_application' => $gData['id_application'],
                                    'user_id' => $data['idUser'],
                                    'id_block' => $coopBlockCount->id_block,
                                    'id_coop' => $data['idCoop'],
                                    'number_garage' => $gData['number_garage'],
                                    'number_block' => $gData['number_block'],
                                    'number_meter' => $gData['number_meter']
                                ]);
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
                    return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                }
            }
            if ($request->input('ipMessage') == 2) {
                try {
                    foreach ($data['garageData'] as $gData) {
                        $ipApplication = ApplicationsGarageToCoop::withTrashed()
                            ->where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])->first();
                        if (!$ipApplication) {
                            return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                        }

                        if ($ipApplication) {
                            $ipApplication->restore();
                            $ipApplication->update(['id_block' => null, 'status' => 'pending', 'date_accepted' => null]);
                        }
                        $garages = Garages::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])
                            ->first();
                        if ($garages) {
                            $garages->delete();
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
                    return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                }
            }

            if ($request->input('ipMessage') == 3) {
                try {
                    foreach ($data['garageData'] as $gData) {
                        $ipApplication = ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])->first();
                        if (!$ipApplication) {
                            return response()->json(['error' => 'Произошла ошибка, повторите попытку позже'], 500);
                        }
                        ApplicationsGarageToCoop::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])
                            ->delete();
                        Garages::where('id_application', $gData['id_application'])
                            ->where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])->delete();
                    }

                    $userAndCoop = UserAndCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();
                    if ($userAndCoop) {
                        // Если запись найдена, обновляем статус
                        $userAndCoop->delete();
                    }
                    // Обновляем статус заявки
                    ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->delete();

                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
                }
            }
            if ($request->input('ipMessage') == 4) {
                // Получите данные из запроса
                $idUser = $request->input('idUser');
                $idCoop = $request->input('idCoop');
                $numberGarage = $request->input('numberGarage');
                $numberBlock = $request->input('numberBlock');
                $numberMeter = $request->input('numberMeter');
                $idBlock = $request->input('idBlock');

                Garages::firstOrCreate(
                    ['user_id' => $idUser,
                        'id_block' => $idBlock,
                        'number_garage' => $numberGarage,
                        'number_block' => $numberBlock,
                        'number_meter' => $numberMeter,
                        'id_coop' => $idCoop,
                        'img_garage' => null,
                        'date_accession' => Date::now()]
                );
                // Обновляем статус заявки
                ApplicationsGarageToCoop::where('user_id', $idUser)
                    ->where('id_coop', $idCoop)
                    ->update(['status' => 'accepted']);
                // Ваш существующий код

                return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop, 'success' => true]);
            }
            if ($request->input('ipMessage') == 5) {
                // Получите данные из запроса
                $idUser = $request->input('idUser');
                $idCoop = $request->input('idCoop');

                // Обновляем статус заявки
                ApplicationsGarageToCoop::where('user_id', $idUser)
                    ->where('id_coop', $idCoop)
                    ->update(['status' => 'canceled']);

                return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop]);
            }
        }
        return response()->json(['error' => "Произошла ошибка, повторите попытку позже"], 500);
    }

    //Страница заявок пользователя на присоединения к кооперативу
    protected function MyApplicationToCoop()
    {
        return view('PagesForChairman.profile.myApplicationToCoop');
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
        //Можно всё скопировать с контроллера ApplicationsPost
    }
}
