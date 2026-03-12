<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController
{
    public function logoutUser(Request $request)
    {
        auth()->guard('web')->logout(); // Выход текущего пользователя

        return redirect()->route('welcome.index')->with('info', 'Вы успешно вышли из аккаунта');
    }
}
