<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class UserAndCoop extends Model
{
    use HasFactory;
    use Notifiable;
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
