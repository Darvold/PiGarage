<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class ValidatorService
{
    /**
     * Базовые правила валидации
     */
    protected static array $baseRules = [
        'fio' => 'required|string|max:255',
        'phone' => 'required|string|regex:/^7[0-9]{10}$/',
        'email' => 'email|max:255',
        'password' => 'required|string|min:6|max:255',
        'password_confirmation' => 'required|string',
        'region' => 'required|string|max:255',

        'name' => 'required|min:3|max:250|regex:/^[а-яА-Я0-9\s\-№]+$/u',
        'number_meter' => 'required|integer|digits_between:1,19',
        'city' => 'string|max:255',
        'address' => 'string|max:255',
        'number_garage_blocks' => 'required|integer|min:1|max:10',
        'latitude' => 'sometimes|numeric|between:-90,90',
        'longitude' => 'sometimes|numeric|between:-180,180',
        'no_map_marker' => 'required|in:true,false,0,1'
    ];

    /**
     * Кастомные сообщения об ошибках
     */
    protected static array $messages = [
        // Телефон
        'phone.regex' => 'Номер телефона должен начинаться с 7 и содержать 11 цифр',
        'phone.unique' => 'Этот телефон уже занят',
        // Email
        'email.unique' => 'Этот email уже зарегистрирован',
        'email.email' => 'Неверный формат email адреса',
        // Пароль
        'password.confirmed' => 'Пароли не совпадают',
        'password.min' => 'Пароль должен содержать минимум 6 символов',
        'password.max' => 'Пароль должен содержать максимум 255 символов',
        // ФИО
        'fio.custom' => 'ФИО не должно содержать цифры и специальные символы!',
        // Регион
        'region.custom' => 'Выбранный регион не существует',
        // Для кооператива - общие
        'required' => 'Поле :attribute обязательно для заполнения',
        'string' => 'Поле :attribute должно быть строкой',
        'integer' => 'Поле :attribute должно быть целым числом',
        'numeric' => 'Поле :attribute должно быть числом',
        'min' => [
            'string' => 'Поле :attribute должно содержать минимум :min символов',
            'integer' => 'Поле :attribute должно быть не менее :min',
        ],
        'max' => [
            'string' => 'Поле :attribute должно содержать максимум :max символов',
            'integer' => 'Поле :attribute должно быть не более :max',
        ],
        'between' => 'Поле :attribute должно быть между :min и :max',
        'digits_between' => 'Поле :attribute должно содержать от :min до :max цифр',

        'name.regex' => 'Название может содержать только русские буквы, цифры, пробелы, дефисы и знак №',
        'name.min' => 'Название должно содержать минимум 3 символа',
        'name.max' => 'Название должно содержать максимум 255 символов',

        'number_meter.digits_between' => 'Номер счетчика должен содержать от 1 до 19 цифр',
        'number_meter.integer' => 'Номер счетчика должен быть целым числом',

        'city.required' => 'Город обязателен для заполнения',
        'city.max' => 'Название города не должно превышать 255 символов',

        'address.required' => 'Адрес обязателен для заполнения',
        'address.max' => 'Адрес не должен превышать 500 символов',

        'number_garage_blocks.min' => 'Количество гаражных блоков должно быть не менее 1',
        'number_garage_blocks.max' => 'Количество гаражных блоков должно быть не более 10',
        'number_garage_blocks.integer' => 'Количество гаражных блоков должно быть целым числом',

        'latitude.between' => 'Широта должна быть между -90 и 90 градусами',
        'longitude.between' => 'Долгота должна быть между -180 и 180 градусами',
    ];

    /**
     * Валидация данных
     * @throws ValidationException
     */
    public static function validate(array $data, ?int $userId = null): array
    {
        $rules = [];
        // ПРЕОБРАЗОВАНИЕ no_map_marker ДО валидации
        if (isset($data['no_map_marker'])) {
            // Преобразуем различные форматы в boolean
            if ($data['no_map_marker'] === 'on' || $data['no_map_marker'] === '1') {
                $data['no_map_marker'] = true;
            } elseif ($data['no_map_marker'] === 'false' || $data['no_map_marker'] === '0') {
                $data['no_map_marker'] = false;
            } else {
                // Пытаемся преобразовать в boolean
                $data['no_map_marker'] = filter_var($data['no_map_marker'], FILTER_VALIDATE_BOOLEAN);
            }
        } else {
            // Если поле не пришло (чекбокс не отмечен), устанавливаем false
            $data['no_map_marker'] = false;
        }

        foreach (self::$baseRules as $field => $fieldRules) {
            // Проверяем только если поле есть в данных и НЕ пустое
            if (array_key_exists($field, $data) && !empty(trim($data[$field]))) {
                $rules[$field] = $fieldRules;
            }
        }
        // Добавляем уникальность для email с исключением текущего пользователя
        if (isset($data['email']) && $data['email'] != null) {
                $rules['email'] .= '|unique:users,email,' . isset($userId) ? $userId : null;
        }

        // 3. Добавляем уникальность для phone если есть в данных
        if (isset($rules['phone']) && isset($data['phone'])) {
                $rules['phone'] .= '|unique:users,phone,' . isset($userId) ? $userId : null;
        }

        // Форматируем телефон перед валидацией
        if (isset($data['phone'])) {
            $data['phone'] = self::formatPhone($data['phone']);
        }

        // Валидируем данные с правилами
        $validator = validator($data, $rules, self::$messages);

        // Проверка региона если есть
        if (isset($data['region'])) {
            // Читаем и парсим JS файл
            $content = file_get_contents(public_path('js/select/russian-cities.js'));

            // Убираем "const cities = " и точку с запятой
            $content = str_replace(['const cities = '], '', $content);
            $content = trim($content);

            $regions = json_decode($content, true);

            if (!$regions) {
                // Если не удалось распарсить, пропускаем проверку
                // или выбрасываем ошибку
                $validator->errors()->add('region', 'Ошибка проверки региона');
            } else {
                $validRegions = array_unique(array_column($regions, 'subject'));

                if (!in_array($data['region'], $validRegions)) {
                    $validator->errors()->add('region', self::$messages['region.custom']);
                }
            }
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Возвращаем уже отформатированные данные
        return $validator->validated();
    }

    /**
     * Обработка ошибок валидации (опционально, можно использовать в контроллере)
     */
    public static function handleValidationError(ValidationException $e, Request $request = null)
    {
        $errors = $e->validator->errors();

        foreach ($errors->messages() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                if (!$error) break;
                    return redirect()->back()
                        ->with('error', $error)
                        ->withInput($request ? $request->all() : []);
            }
        }

        // Дефолтное сообщение
        return redirect()->back()
            ->with('error', 'Пожалуйста, проверьте правильность введенных данных')
            ->withInput($request ? $request->all() : []);
    }

    /**
     * Быстрая валидация без исключения (для простых проверок)
     */
    public static function quickValidate(array $data, ?int $userId = null): array
    {
        try {
            return self::validate($data, $userId);
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->validator->errors()->toArray(),
                'message' => self::getFirstErrorMessage($e)
            ];
        }
    }

    /**
     * Получить первую ошибку
     */
    private static function getFirstErrorMessage(ValidationException $e): string
    {
        $errors = $e->validator->errors()->all();
        return $errors[0] ?? 'Ошибка валидации';
    }

    public static function formatPhone(string $phone): string
    {
        // Убираем всё, кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Если номер начинается с 8, меняем на 7
        if (strlen($phone) === 11 && str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }

        // Если номер начинается с +7, убираем +
        if (strlen($phone) === 11 && str_starts_with($phone, '7')) {
            // Уже в правильном формате
        } elseif (strlen($phone) === 10) {
            // Если 10 цифр, добавляем 7 в начало
            $phone = '7' . $phone;
        }

        return $phone;
    }

}
