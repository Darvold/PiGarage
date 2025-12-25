<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterNumbersGarages extends Model
{
    use HasFactory;
    protected $table = 'meter_numbers_garages';
    protected $primaryKey = 'id_meter_number'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
