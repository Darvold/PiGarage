<?php

namespace App\Http\Controllers;

use App\Models\AdminUsers;
use App\Services\ValidatorService;
use Illuminate\Support\Str;
use App\Models\Users;
use Illuminate\Support\Facades\Session;
use App\Models\Sessions;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Date\Date;
use Illuminate\Validation\ValidationException;


class LoginAndRegistrController extends Controller
{
    public function loginUser()
    {
        /*if (Auth::id()) {
            $user = Users::where('id', Auth::id())->first();
            if ($user->id_al == 2) {
                return redirect()->route('ProfileChairman.index');
            } else {
                return redirect()->route('ProfileUser.index');
            }
        }*/
        return view('userAuth.login');
    }
    public function registrUser()
    {
        /*        if (Auth::id()) {
                    $user = Users::where('id', Auth::id())->first();
                    if ($user->id_al == 2) {
                        return redirect()->route('ProfileChairman.index');
                    } else {
                        return redirect()->route('ProfileUser.index');
                    }
                }*/
        return view('userAuth.registr');
    }

    public function registrUserPost(Request $request) {
        try {
            $data = ValidatorService::validate($request->all());
        } catch (ValidationException $e) {
            return ValidatorService::handleValidationError($e, $request);
        }

        if (preg_match('/[0-9!@#$%^&*()_+|~=`{}\[\]:";\'<>?,.\/]/', $data['fio'])) {
            // Если в поле FIO обнаружены цифры или знаки, добавляем сообщение об ошибке и перенаправляем обратно
            return redirect()->back()->with('error', 'ФИО не должно содержать цифры и специальные символы!')->withInput();
        }
        // Проверяем, существует ли адрес электронной почты в базе данных
        if (Users::where('phone', $data['phone'])->exists()) {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку')->withInput();
        }

        if (strlen($data['password']) < 6) {
            return redirect()->back()->with('error', 'Пароль должен быть минимум из 6 символов!')->withInput();
        }
        if ($data['password'] !== $data['password_confirmation']) {
            return redirect()->back()->with('error', 'Пароли не совпадают!')->withInput();
        }
        $newUser = Users::create([
            'fio' => $data['fio'],
            'phone' => $data['phone'],
            'password' => bcrypt($data['password']),
            'region' => $data['region']
        ]);
        if ($newUser) {
            return redirect()->route('login.index')->with('success', 'Вы успешно зарегестрировались!');
        } else {
            return redirect()->back()->with('error', 'Что-то пошло не так, повторите попытку')->withInput();
        }
    }

    public function loginUserPost(Request $request) {
        try {
            $data = ValidatorService::validate($request->all());
        } catch (ValidationException $e) {
            return ValidatorService::handleValidationError($e, $request);
        }
        $user = Users::where('phone', $data['phone'])
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Неверный номер или пароль!')->withInput();
        }

        if (password_verify($data['password'], $user->password)) {
            auth()->login($user);
            return redirect()->route('profileUser.index');
        }

        return redirect()->back()->with('error', 'Неверный номер или пароль!')->withInput();
    }


}
