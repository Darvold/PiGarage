<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class coopLosses extends Model
{
    use HasFactory;
    protected $table = 'coop_losses';
    protected $primaryKey = 'id_losses'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
}
