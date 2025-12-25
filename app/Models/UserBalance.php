<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserBalance extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    public $timestamps = false;
    protected $table = 'user_balances';
    protected $guarded = [];
    protected $primaryKey = 'id_balance';
}
