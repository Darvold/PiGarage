<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CooperativeBlocks extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'cooperative_blocks';
    protected $guarded = [];
    protected $primaryKey = 'id_block';
    public function meterReadingsUsers()
    {
        return $this->hasMany(MeterReadingsUsers::class, 'id_block', 'id_block');
    }
}
