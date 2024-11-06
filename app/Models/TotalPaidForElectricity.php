<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TotalPaidForElectricity extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'total_paid_for_electricity';
    protected $primaryKey = 'id_total_elec';
    public $timestamps = false;
    protected $guarded = [];
}
