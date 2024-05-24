<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetersReadings extends Model
{
    use HasFactory;
    protected $table = 'meters_readings';
    protected $primaryKey = 'id_reading'; // Указываем первичный ключ
    public $timestamps = false; // Включаем автоматическое обновление времени создания и обновления
    protected $guarded = [];
    public function garages()
    {
        return $this->hasMany(Garages::class, 'id_garage', 'id_garage');
    }
    public function cooperative()
    {
        return $this->hasOne(Cooperatives::class, 'id_coop', 'id_coop');
    }
}
