<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'users';
    protected $guarded = [];
    protected $primaryKey = 'id';
    protected $fillable = [
        'fio', 'phone', 'email', 'password', 'region', 'data_reg', 'id_al',
    ];
    public function garages()
    {
        return $this->hasMany(Garages::class, 'user_id', 'id');
    }
    public function Cooperatives()
    {
        return $this->hasMany(Cooperatives::class, 'user_id', 'id');
    }
}
