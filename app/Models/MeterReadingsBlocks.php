<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReadingsBlocks extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'meter_readings_blocks';
    protected $primaryKey = 'id_reading';
    public $timestamps = false;
    protected $guarded = [];
    public function Cooperative() {
        return $this->belongsTo(Cooperatives::class, 'id_coop', 'id_coop');
    }

}
