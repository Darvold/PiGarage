<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $table = 'payments';
    protected $primaryKey = 'id_payment'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
