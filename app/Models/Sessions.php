<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sessions extends Model
{
    use HasFactory;
    protected $table = 'sessions';
    protected $primaryKey = 'id'; // Указываем первичный ключ
    public $timestamps = true; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
