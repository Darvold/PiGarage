<?php

namespace App\Http\Controllers;
use App\Models\ApplicationsForAccessions;
use App\Models\Cooperatives;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Mockery\Exception;

class MyCooperatives
{
    public function myCoops(Request $request)
    {
        $myCoops = Cooperatives::where('user_id', Auth::id())
            ->withCount('userAndCoop')
            ->get();
        return view('pagesForUser.coops.myCoops', compact('myCoops'));
    }


    //Контроллер для просмотр заявко других пользователей
    public function UserConnectCoop(Request $request)
    {
        if ($request->ajax()) {
            $idMessage = $request->input("idMessage");
            if ($idMessage == 1) {
                $nameCoop = $request->input("nameCoop");
                $selectedRegion = $request->input("selectedRegion");
                $selectedCity = $request->input("selectedCity");
                if ($selectedRegion == $selectedCity) {
                    $location = '%' . $selectedRegion . '%';
                } else {
                    $location = '%' . $selectedRegion . ', ' . $selectedCity . '%';
                }
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

        return view('PagesForChairman.profile.connectCoop');
    }

}
