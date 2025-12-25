<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ApplicationsGarageToCoop extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public $timestamps = false;
    protected $table = 'applications_garage_to_coop';
    protected $guarded = [];
    protected $primaryKey = 'id_application';

}
