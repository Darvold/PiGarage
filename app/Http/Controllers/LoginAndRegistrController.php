<?php

namespace App\Http\Controllers;

use App\Models\AdminUsers;
use Illuminate\Support\Str;
use App\Models\Users;
use Illuminate\Support\Facades\Session;
use App\Models\Sessions;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Date\Date;


class LoginAndRegistrController extends Controller
{
    public function indexUser()
    {
        if (Auth::id()) {
            $user = Users::where('id', Auth::id())->first();
            if ($user->id_al == 2) {
                return redirect()->route('ProfileChairman.index');
            } else {
                return redirect()->route('ProfileUser.index');
            }
        }
        return view('registr.registr');
    }
    public function indexLoginUser()
    {
        if (Auth::id()) {
            $user = Users::where('id', Auth::id())->first();
            if ($user->id_al == 2) {
                return redirect()->route('ProfileChairman.index');
            } else {
                return redirect()->route('ProfileUser.index');
            }
        }
        return view('login.login');
    }

    public function registrationUser(Request $request) {
        $data = request()->validate([
            'fio' => '',
            'phone' => '',
            'email' => '',
            'password' => '',
            'password_confirm' => '',
            'region' => '',
        ]);
        $data['data_reg'] = Date::now();
        $data['id_al'] = $request->input('id_al');

        if (preg_match('/[0-9!@#$%^&*()_+|~=`{}\[\]:";\'<>?,.\/]/', $data['fio'])) {
            // Если в поле FIO обнаружены цифры или знаки, добавляем сообщение об ошибке и перенаправляем обратно
            return redirect()->back()->with('error', 'ФИО не должно содержать цифры и специальные символы!')->withInput();
        }
        // Проверяем, существует ли адрес электронной почты в базе данных
        if (Users::where('email', $data['email'])->exists()) {
            return redirect()->back()->with('error', 'Данный Email адрес уже зарегистрирован!')->withInput();
        }

        if (strlen($data['password']) < 6) {
            return redirect()->back()->with('error', 'Пароль должен быть минимум из 6 символов!')->withInput();
        }

        if ($data['password'] !== $data['password_confirm']) {
            return redirect()->back()->with('error', 'Пароли не совпадают!')->withInput();
        }
        // Удаляем поле Password_confirm из массива данных
        unset($data['password_confirm']);
        // Хэшируем пароль
        $data['password'] = bcrypt($data['password']);
        Users::create($data);

        return redirect()->route('login.index')->with('success', 'Вы успешно зарегестрировались!');
    }

    public function loginUser(Request $request) {
        $data = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = Users::where('email', $data['email'])
            ->first();

        //dd($user);
        if (!$user) {
            return redirect()->back()->with('error', 'Неверный Email или пароль!')->withInput();
        }

        if ($user && password_verify($data['password'], $user->password)) {
            auth()->login($user);

            if ($user->id_al == 2) {
                return redirect()->route('ProfileChairman.index');
            } elseif ($user->id_al == 1) {
                return redirect()->route('ProfileUser.index');
            }
        }

        return redirect()->back()->with('error', 'Неверный Email или пароль!')->withInput();
    }


}
