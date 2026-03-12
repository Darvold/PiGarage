<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cooperatives extends Model
{
    use HasFactory;
    use Notifiable;
    public $timestamps = true;
    protected $table = 'cooperatives';
    protected $guarded = [];
    protected $primaryKey = 'id_coop';
    protected $hidden = [
        'id_coop'
    ];
    protected $fillable = [
        'name', 'city', 'address', 'status', 'personal_number', 'created_at',
    ];
    public function metersReadings()
    {
        return $this->hasMany(MeterReadingsUsers::class, 'id_coop', 'id_coop');
    }
    public function amountGarageBlock()
    {
        return $this->hasMany(CooperativeBlocks::class, 'id_coop', 'id_coop');
    }
    public function userAndCoop()
    {
        return $this->hasMany(UserAndCoop::class, 'id_coop', 'id_coop');
    }
    public function applicationsForAccessions()
    {
        return $this->hasMany(applicationsForAccessions::class, 'id_coop', 'id_coop');
    }
    public function meterReadingsBlocks()
    {
        return $this->hasMany(MeterReadingsBlocks::class, 'id_coop', 'id_coop');
    }
    public function meterReadingsCoops()
    {
        return $this->hasMany(MeterReadingsCoops::class, 'id_coop', 'id_coop');
    }

    public function getStatusRuAttribute(): string
    {
        return match($this->status) {
            'pending' => 'На проверке',
            'approved' => 'Одобрено',
            'rejected' => 'Отклонено',
            'active' => 'Активно',
            'accepted' => 'Принято',
            'inactive' => 'Неактивно',
            default => $this->status,
        };
    }
    public function getStatusClassAttribute()
    {
        return match($this->status_ru) {
            'На проверке' => 'statusYellow',
            'Одобрено', 'Активно' => 'statusGreen',
            'Отклонено' => 'statusRed',
            default => '',
        };
    }

}
