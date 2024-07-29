<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserAndCoop extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public $timestamps = false;
    protected $table = 'user_and_coop';
    protected $guarded = [];
    protected $primaryKey = 'id_connection';

    public function users()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
    public function payments()
    {
        return $this->hasMany(PayMents::class, 'user_id', 'user_id');
    }
}
