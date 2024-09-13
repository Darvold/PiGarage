<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterNumbers extends Model
{
    use HasFactory;
    protected $table = 'meter_numbers';
    protected $primaryKey = 'id_meter_number'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
