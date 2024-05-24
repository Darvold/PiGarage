<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CooperativesBlocksLossesKw extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'cooperative_blocks_losses_kw';
    protected $guarded = [];
    protected $primaryKey = 'id_block_losses_kw';
}
