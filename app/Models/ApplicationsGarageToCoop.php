<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ApplicationsGarageToCoop extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'applications_garage_to_coop';
    protected $guarded = [];
    protected $primaryKey = 'id_application';
}
