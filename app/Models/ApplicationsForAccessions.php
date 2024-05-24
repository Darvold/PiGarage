<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ApplicationsForAccessions extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Notifiable;
    public $timestamps = false;
    protected $table = 'applications_for_accessions';
    protected $guarded = [];
    protected $primaryKey = 'id_application';
}
