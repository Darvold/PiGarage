<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MeterReadingsCoops extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    public $timestamps = false;
    protected $table = 'meter_readings_coops';
    protected $guarded = [];
    protected $primaryKey = 'id_reading';
    public function Cooperative() {
        return $this->belongsTo(Cooperatives::class, 'id_coop', 'id_coop');
    }
    public function MeterNumberCoopsActive() {
        return $this->hasOne(MeterNumbersCoops::class, 'id_coop', 'id_coop');
    }
}
