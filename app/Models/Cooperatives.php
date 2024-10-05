<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cooperatives extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'cooperatives';
    protected $guarded = [];
    protected $primaryKey = 'id_coop';
    public function metersReadings()
    {
        return $this->hasMany(MeterReadingsUsers::class, 'id_coop', 'id_coop');
    }
    public function amountGarageBlock()
    {
        return $this->hasMany(CooperativeBlocks::class, 'id_coop', 'id_coop');
    }
    public function userAndCoop()
    {
        return $this->hasMany(UserAndCoop::class, 'id_coop', 'id_coop');
    }
    public function applicationsForAccessions()
    {
        return $this->hasMany(applicationsForAccessions::class, 'id_coop', 'id_coop');
    }
    public function meterReadingsBlocks()
    {
        return $this->hasMany(MeterReadingsBlocks::class, 'id_coop', 'id_coop');
    }

}
