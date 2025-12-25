<?php

namespace App\Data;

class GaragesData
{
    public static function get()
    {
        return [
            [
                'number' => 'А-15',
                'owner' => 'Иванов А.П.',
                'electricity' => 245,
                'water' => 12.5,
                'last_payment' => '15.05.2023',
                'status' => 'paid',
                'status_text' => 'Оплачено'
            ],
            [
                'number' => 'Б-07',
                'owner' => 'Петров С.И.',
                'electricity' => 198,
                'water' => 10.2,
                'last_payment' => '10.05.2023',
                'status' => 'paid',
                'status_text' => 'Оплачено'
            ],
            [
                'number' => 'В-22',
                'owner' => 'Сидорова М.К.',
                'electricity' => 312,
                'water' => 15.8,
                'last_payment' => '25.04.2023',
                'status' => 'pending',
                'status_text' => 'Ожидает'
            ],
            [
                'number' => 'Г-11',
                'owner' => 'Кузнецов В.А.',
                'electricity' => 276,
                'water' => 13.4,
                'last_payment' => '28.04.2023',
                'status' => 'paid',
                'status_text' => 'Оплачено'
            ],
            [
                'number' => 'Д-05',
                'owner' => 'Смирнова О.Л.',
                'electricity' => 189,
                'water' => 9.7,
                'last_payment' => '05.04.2023',
                'status' => 'overdue',
                'status_text' => 'Просрочено'
            ],
            [
                'number' => 'Е-18',
                'owner' => 'Васильев П.М.',
                'electricity' => 224,
                'water' => 11.9,
                'last_payment' => '18.05.2023',
                'status' => 'paid',
                'status_text' => 'Оплачено'
            ]
        ];
    }
}
