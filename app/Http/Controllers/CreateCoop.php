<?php

namespace App\Http\Controllers;

use App\Models\ApplicationsForAccessions;
use App\Models\CooperativeBlocks;
use App\Models\Cooperatives;
use App\Services\ValidatorService;
use App\Traits\HandlesErrors;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Mockery\Exception;

class CreateCoop
{
    use HandlesErrors;

    public function myCoops_Create(Request $request)
    {
        return view('pagesForUser/coops/createCoops');
    }

    function myCoops_CreatePost(Request $request)
    {
        try {
            $data = ValidatorService::validate($request->all());
        } catch (ValidationException $e) {
            return ValidatorService::handleValidationError($e, $request);
        }
        return $this->safeExecute(function () use ($request, $data) {
            // Проверяем координаты и страну, город
            if (!empty($data['city']) && !empty($data['address']) &&
                isset($data['latitude']) && isset($data['longitude'])) {

                $test = $this->getCountryFromCoordinates(
                    $data['city'],
                    $data['address'],
                    $data['latitude'],
                    $data['longitude']
                );

                $errors = [
                    '[1]' => 'Что-то пошло не так, возможно метка указана вне города/посёлка/села',
                    '[2]' => 'Ваши координаты метки не относятся к России',
                    false => 'Что-то пошло не так, попробуйте в другой день выполнить запрос'
                ];

                if (isset($errors[$test])) {
                    return redirect()->back()
                        ->with(['error' => $errors[$test]])
                        ->withInput();
                }
            }
            // Генерируем уникальный personal_number
            do {
                $personalNumber = mt_rand(10000, 999999);
            } while (Cooperatives::where('personal_number', $personalNumber)->exists());

            $newCoop = Cooperatives::create([
                'user_id' => Auth::id(),
                'name' => $data['name'],
                'city' =>  $data['city'] ?? null,
                'address' =>  $data['address'] ?? null,
                'date_create' => Date::now(),
                'personal_number' => $personalNumber, // Используем сгенерированный
                'status' => 'pending',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ]);
            if ($data['number_garage_blocks'] != null) {
                for ($i = 0; $i < $data['number_garage_blocks']; $i++) {
                    CooperativeBlocks::create([
                        'id_coop' => $newCoop->id_coop,
                        'number_block' => $i + 1,
                        'default_kw' => null
                    ]);
                }
            } else {
                $newCoop->delete();
                return redirect()->back()->with('error', 'Что-то пошло не так');
            }
            return redirect()->back()->with('success', 'Заявка успешно отправлена!');
        }, 'myCoops_Create.index'); // Маршрут для редиректа при ошибке
    }

    public function getCountryFromCoordinates($city, $address, $latitude, $longitude)
    {
        $apiKeys = [
            'e268d199-9698-48cd-ac43-a489a6d43bbd',
            'aa1a4f1a-2153-49ec-bbc8-db91be38ff21',
        ];

        foreach ($apiKeys as $apiKey) {
            $client = new Client();

            try {
                $response = $client->get('https://geocode-maps.yandex.ru/1.x/', [
                    'query' => [
                        'apikey' => $apiKey,
                        'geocode' => $longitude . ',' . $latitude,
                        'format' => 'json',
                    ]
                ]);

                $data = json_decode($response->getBody(), true);

                // Проверяем, что запрос успешен и есть результат
                if (isset($data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'])) {
                    $geoData = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'];

                    // Проверяем, что код страны - Россия (RU)
                    if ($geoData['country_code'] === 'RU') {
                        // Проверяем соответствие города и адреса
                        $fetchedCity = $geoData['Components'][3]['name'] ?? '';
                        $fetchedAddress = $geoData['formatted'] ?? '';

                        if (str_contains($fetchedAddress, $city) && str_contains($fetchedAddress, $address)) {
                            return true;
                        } else {
                            return '[1]';
                        }
                    } else {
                        return '[2]';
                    }
                }
            } catch (GuzzleException $e) {
                // Обработка ошибки 403 Forbidden
                if ($e->getResponse() && $e->getResponse()->getStatusCode() === 403) {
                    continue; // Пробуем следующий ключ в случае ошибки 403
                }
                continue; // Пробуем следующий ключ в случае других ошибок
            } catch (Exception $e) {
                continue; // Пробуем следующий ключ в случае общих ошибок
            }
        }

        // Если все ключи исчерпаны и запрос не удался
        return false;
    }

    public function UserSelectCoopsAJAX(Request $request)
    {
        if ($request->ajax()) {
            $key = 'UserSelectCoopsAJAX|' . $request->ip(); // Уникальный ключ для IP-адреса

            if (RateLimiter::tooManyAttempts($key, 30)) { // Максимум 30 запросов
                return response()->json(['error' => 'Слишком много запросов. Пожалуйста, подождите.'], 429);
            }

            RateLimiter::hit($key, 60); // Запрос считается в течение 60 секунд
            $coordinates = $request->input('bounds');
            if (!$coordinates) {
                return response()->json(['error' => 'Ошибка получения данных'], 500);
            }
            try {
                $minLatitude = number_format($coordinates[0][0], 6, '.', '');
                $maxLatitude = number_format($coordinates[1][0], 6, '.', '');
                $minLongitude = number_format($coordinates[0][1], 6, '.', '');
                $maxLongitude = number_format($coordinates[1][1], 6, '.', '');

                $cooperatives = Cooperatives::select('cooperatives.*', 'users.fio')
                    ->leftJoin('users', 'cooperatives.user_id', '=', 'users.id')
                    ->withCount('amountGarageBlock')
                    ->where('status', 'accepted')
                    ->whereBetween('cooperatives.latitude', [$minLatitude, $maxLatitude])
                    ->whereBetween('cooperatives.longitude', [$minLongitude, $maxLongitude])
                    ->get();

                $applicationStatus = ApplicationsForAccessions::withTrashed()
                    ->where('user_id', Auth::id())
                    ->get();

                $response = [
                    'cooperatives' => $cooperatives,
                    'applicationStatus' => $applicationStatus,
                ];

                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Ошибка получения данных'], 500);
            }
        }
        return redirect()->back();
    }
}
