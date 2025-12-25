<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Garages extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public $timestamps = false;
    protected $table = 'garages';
    protected $guarded = [];
    protected $primaryKey = 'id_garage';
    public function cooperative()
    {
        return $this->hasOne(Cooperatives::class, 'id_coop', 'id_coop');
    }
    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
    public function garageUser() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    // В модели Garages
    public function metersReadings()
    {
        return $this->hasMany(MeterReadingsUsers::class, 'id_garage');
    }



}
