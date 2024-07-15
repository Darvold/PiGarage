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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Date\Date;
use Mockery\Exception;
use function Laravel\Prompts\error;

class CommunicationController extends Controller
{
    protected function Applications(Request $request)
    {
        if ($request->ajax()) {
            $idMessage = $request->input('idMessage');
            if ($idMessage ==  1) {
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
                    return response()->json(['error' => "Произошла ошибка"], 500);
                }
            }
            if ($idMessage == 2) {
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
            }
        }
        $user = Auth::user();

        $coops = Cooperatives::where('user_id', Auth::id())->get();

        return view('PagesForChairman.communication.applications', compact('user', 'coops'));
    }

    public function applicationsMessage(Request $request)
    {


    }

    protected function ApplicationsMessageGarage(Request $request)
    {
        if ($request->ajax()) {
            // Получите данные из запроса
            $idCoop = $request->input('idCoop');
            $coopMessages = ApplicationsGarageToCoop::join('users', 'applications_garage_to_coop.user_id', '=', 'users.id')
                ->where('applications_garage_to_coop.id_coop', $idCoop)
                ->where('applications_garage_to_coop.status', 'pending')
                ->select('users.fio as user_fio',
                    'applications_garage_to_coop.id_block as user_id_block',
                    'applications_garage_to_coop.number_block as user_number_block',
                    'applications_garage_to_coop.number_garage as user_number_garage',
                    'applications_garage_to_coop.number_meter as user_number_meter',
                    'applications_garage_to_coop.user_id as user_id')
                ->get();
            // Здесь вы можете выполнить любую логику на основе полученных данных

            // Верните JSON-ответ
            // return response()->json(['message' => 'Данные успешно получены', 'idCoop' => $idCoop, 'coopMessages' => $coopMessages]);
            return response()->json(['coopMessages' => $coopMessages]);
        }
    }

    public function ApplicationsMessageGarageReject(Request $request)
    {
        if ($request->ajax()) {
            // Получите данные из запроса
            $idCoop = $request->input('idCoop');
            $coopMessages = ApplicationsGarageToCoop::join('users', 'applications_garage_to_coop.user_id', '=', 'users.id')
                ->where('applications_garage_to_coop.id_coop', $idCoop)
                ->where('applications_garage_to_coop.status', 'canceled')
                ->select('users.fio as user_fio',
                    'applications_garage_to_coop.number_block as user_number_block',
                    'applications_garage_to_coop.number_garage as user_number_garage',
                    'applications_garage_to_coop.number_meter as user_number_meter',
                    'applications_garage_to_coop.user_id as user_id',)
                ->get();
            // Здесь вы можете выполнить любую логику на основе полученных данных

            // Верните JSON-ответ
            // return response()->json(['message' => 'Данные успешно получены', 'idCoop' => $idCoop, 'coopMessages' => $coopMessages]);
            return response()->json(['coopMessages' => $coopMessages]);
        }

    }

    public function ApplicationsMessageReject(Request $request)
    {

        // Получите данные из запроса
        $idCoop = $request->input('idCoop');
        $coopMessages = ApplicationsForAccessions::join('users', 'applications_for_accessions.user_id', '=', 'users.id')
            ->where('applications_for_accessions.id_coop', $idCoop)
            ->where('applications_for_accessions.status', 'canceled')
            ->select('applications_for_accessions.*', 'users.fio as user_fio')
            ->get();
        // Здесь вы можете выполнить любую логику на основе полученных данных

        // Верните JSON-ответ
        // return response()->json(['message' => 'Данные успешно получены', 'idCoop' => $idCoop, 'coopMessages' => $coopMessages]);
        // Верните JSON-ответ с массивом данных
        return response()->json(['coopMessages' => $coopMessages]);

    }

    protected function ApplicationsPost(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->validate([
                'idUser' => 'required|integer',
                'idCoop' => 'required|integer',
                'garageData' => 'required'
            ]);
            if (!$data) {
                return response()->json(['error' => 'Произошла ошибка'], 500);
            }
            $coopUser = Cooperatives::where('user_id', auth::id())
                ->where('id_coop', $data['idCoop'])
                ->first();

            if (!$coopUser) {
                return response()->json(['error' => 'Произошла ошибка'], 500);
            }
            $applicationUser = applicationsForAccessions::where('user_id', $data['idUser'])
                ->where('id_coop', $data['idCoop'])
                ->first();
            if (!$applicationUser) {
                return response()->json(['error' => 'Произошла ошибка'], 500);
            }

            if ($request->input('ipMessage') == 1) {
                try {

                foreach ($data['garageData'] as $gData) {
                    $coopBlockCount = CooperativeBlocks::where('id_coop', $data['idCoop'])
                        ->where('number_block', $gData['number_block'])
                        ->first();
                    ApplicationsGarageToCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->where('number_garage', $gData['number_garage'])
                        ->where('number_block', $gData['number_block'])
                        ->where('number_meter', $gData['number_meter'])
                        ->update(['id_block' => $coopBlockCount['id_block'], 'status' => $gData['checked'] === 'true' ? 'accepted' : 'canceled',
                        'date_accepted' => Date::now()]);
                    }
                    $userAndCoop = UserAndCoop::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->first();
                    if ($userAndCoop) {
                        // Если запись найдена, обновляем статус
                        $userAndCoop->update(['status' => 'accepted', 'date_accession' => Date::now()]);
                    } else {
                        // Если записи нет, создаем новую
                        UserAndCoop::create([
                            'user_id' => $data['idUser'],
                            'id_coop' => $data['idCoop'],
                            'date_accession' => Date::now(),
                            'status' => 'accepted'
                        ]);
                    }
                    ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->update(['status' => 'accepted', 'date_accepted' => Date::now()]);

                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
            }
            if ($request->input('ipMessage') == 2) {
                try {
                    ApplicationsForAccessions::where('user_id', $data['idUser'])
                        ->where('id_coop', $data['idCoop'])
                        ->update(['status' => 'pending', 'date_accepted' => null]);
                    foreach ($data['garageData'] as $gData) {
                        ApplicationsGarageToCoop::where('user_id', $data['idUser'])
                            ->where('id_coop', $data['idCoop'])
                            ->where('number_garage', $gData['number_garage'])
                            ->where('number_block', $gData['number_block'])
                            ->where('number_meter', $gData['number_meter'])
                            ->update(['id_block' => null, 'status' => 'pending', 'date_accepted' => null]);
                    }

                    UserAndCoop::where('user_id', $data['idUser'])
                    ->where('id_coop', $data['idCoop'])
                    ->update(['status' => 'pending']);

                    return response()->json(['idUser' => $data['idUser'], 'idCoop' => $data['idCoop']]);
                } catch (\Exception $e) {
                    return response()->json(['error' => "Произошла ошибка"], 500);
                }
            }
            if ($request->input('ipMessage') == 3) {
                // Получите данные из запроса
                $idUser = $request->input('idUser');
                $idCoop = $request->input('idCoop');

                // Обновляем статус заявки
                ApplicationsForAccessions::where('user_id', $idUser)
                    ->where('id_coop', $idCoop)
                    ->update(['status' => 'canceled']);

                return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop]);
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
    }

    public function Messages(Request $request)
    {
        return view('PagesForChairman.communication.messages');

    }

    public function ApplicationsReject()
    {
        // Получите текущего аутентифицированного пользователя
        $coops = Cooperatives::where('user_id', Auth::id())->get();
        return view('PagesForChairman.communication.ApplicationsReject', compact('coops'));

    }

    public function ApplicationsRejectPost(Request $request)
    {
        if ($request->input('ipMessage') == 1) {
            // Получите данные из запроса
            $idUser = $request->input('idUser');
            $idCoop = $request->input('idCoop');

            UserAndCoop::firstOrCreate(
                ['user_id' => $idUser, 'id_coop' => $idCoop],
                ['date_accession' => Date::now()]
            );
            // Обновляем статус заявки
            ApplicationsForAccessions::where('user_id', $idUser)
                ->where('id_coop', $idCoop)
                ->update(['status' => 'accepted']);

            return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop]);
        }
        if ($request->input('ipMessage') == 2) {
            // Получите данные из запроса
            $idUser = $request->input('idUser');
            $idCoop = $request->input('idCoop');

            ApplicationsForAccessions::where('user_id', $idUser)
                ->where('id_coop', $idCoop)
                ->delete();

            return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop]);
        }
        if ($request->input('ipMessage') == 3) {
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
                    'img_garage' => 'null',
                    'date_accession' => Date::now()]
            );
            // Обновляем статус заявки
            ApplicationsGarageToCoop::where('user_id', $idUser)
                ->where('id_block', $idBlock)
                ->where('id_coop', $idCoop)
                ->update(['status' => 'accepted']);
            // Ваш существующий код

            return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop, 'success' => true]);
        }
        if ($request->input('ipMessage') == 4) {
            // Получите данные из запроса
            $idUser = $request->input('idUser');
            $idCoop = $request->input('idCoop');

            ApplicationsGarageToCoop::where('user_id', $idUser)
                ->where('id_coop', $idCoop)
                ->delete();

            return response()->json(['idUser' => $idUser, 'idCoop' => $idCoop]);
        }
    }
}
