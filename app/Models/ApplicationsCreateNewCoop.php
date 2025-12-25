<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ApplicationsCreateNewCoop extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'applications_create_new_coops';
    protected $guarded = [];
    protected $primaryKey = 'id_application';
}
