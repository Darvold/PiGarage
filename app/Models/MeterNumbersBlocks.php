<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterNumbersBlocks extends Model
{
    use HasFactory;
    protected $table = 'meter_numbers_blocks';
    protected $primaryKey = 'id_meter_number'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
