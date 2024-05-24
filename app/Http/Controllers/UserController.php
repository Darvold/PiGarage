<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsForAccessions;
use App\Models\Cooperatives;
use App\Models\Garages;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Date\Date;

class UserController extends Controller
{
    public function indexUser() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();

            return view('PagesForUser.profile.profile', compact('user'));

    }
    public function logout() {
        auth()->guard('web')->logout(); // Выход текущего пользователя

        return redirect()->route('login.index');
    }
    public function UserCreateGarage() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();

            return view('PagesForUser.profile.createGarage', compact('user'));

    }
    public function UserGarage() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();
            $user_id = Auth::id();
            $garages = Garages::where('user_id', $user_id)->get();

            return view('PagesForUser.profile.garage', compact('user', 'garages'));

    }
    public function CreatingGarage() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();
            $data = request()->validate([
                'id_garage' => 'nullable', // 'id' может быть null
                'id_coop' => 'nullable', // 'id_coop' может быть null
                'number_meter' => 'required|numeric',
                'number_garage' => 'required|numeric',
                'number_block' => 'required|numeric',
            ]);

            // Добавьте user_id в массив данных перед созданием записи
            $data['user_id'] = $user->getAuthIdentifier(); // Получить идентификатор пользователя

            Garages::create($data);
            return redirect()->route('UserGarage.index', ['id' => $user->getAuthIdentifier()]);

    }
    public function myGaragePivotTable($idGarage) {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();
            $user_id = Auth::id();
            $garage = Garages::where('id_garage', $idGarage)->first();

            if ($garage) {
                return view('PagesForUser.profile.myGaragePivotTable', ['garage' => $garage]);
            } else {
                return back()->with('error', 'Гараж не найден');
            }

    }
    public function UserConnectCoop() {

            // Получите текущего аутентифицированного пользователя
            $user = Auth::user();

            $cooperatives = Cooperatives::select('cooperatives.*', 'users.*')
                ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
                ->where('users.id_al', 2)
                ->get();


            return view('PagesForUser.profile.connectCoop',
                compact('user', 'cooperatives'));

    }
    public function JoinTheCoop() {

            $data = request()->validate([
                'user_id' => 'required',
                'id_coop' => 'required',
                ]);

            $data['send_date'] = Date::now();

            ApplicationsForAccessions::Create($data);

          return  redirect()->route('UserConnectCoop.index', ['id' => Auth::id()])->with('success', 'Заявка отправлена!');

    }
}
