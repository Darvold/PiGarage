<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReadingsBlocks extends Model
{
    use HasFactory;
    protected $table = 'meter_readings_blocks';
    protected $primaryKey = 'id_reading';
    public $timestamps = false;
    protected $guarded = [];
}
