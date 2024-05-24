<?php

namespace App\Http\Controllers;

use App\Models\AdminUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function LoginAdmin() {
        return view('administrator.login.loginAdmin');
    }
    public function RegistrNewAdmin() {
        return view('administrator.registr.registrAdmin');
    }
    public function RegistrNewAdminInsert() {
        $data = request()->validate([
            'login' => 'required',
            'password' => 'required|min:6',
            'password_confirm' => 'required|same:password',
        ]);
        $data['is_admin'] = 1;
        // Проверяем, существует ли адрес электронной почты в базе данных
        if (AdminUsers::where('login', $data['login'])->exists()) {
            return redirect()->back()->with('error', 'Данный логин адрес уже зарегистрирован!')->withInput();
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
        AdminUsers::create($data);

        return redirect()->route('AdminLogin.index')->with('success', 'Вы успешно зарегестрировались!');
    }
    public function LoginAdminNewSession(Request $request) {
        $data = request()->validate([
            'login' => 'required',
            'password' => 'required',
        ]);


        $user = AdminUsers::where('login', $data['login'])->first();

        //dd($user);
        if (!$user) {
            return redirect()->back()->with('error', 'Неверный логин или пароль!')->withInput();
        }
        $id = $user-> id;

        if (password_verify($data['password'], $user->password)) {
            auth()->login($user);
            return redirect()->route('AdminMain.index', ['idAdmin' => $id]);
        } else {
            return redirect()->back()->with('error', 'Неверный логин или пароль!')->withInput();
        }
    }
}
