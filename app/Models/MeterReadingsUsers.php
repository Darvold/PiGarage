<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReadingsUsers extends Model
{
    use HasFactory;
    protected $table = 'meter_readings_users';
    protected $primaryKey = 'id_reading';
    public $timestamps = false;
    protected $guarded = [];
    public function garages()
    {
        return $this->hasMany(Garages::class, 'id_garage', 'id_garage');
    }
    public function cooperative()
    {
        return $this->hasOne(Cooperatives::class, 'id_coop', 'id_coop');
    }
    public function user()
    {
        return $this->hasMany(Users::class, 'id', 'user_id');
    }
}
