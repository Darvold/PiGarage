<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Accessor: Форматированный телефон
     */
    public function getFormattedPhoneAttribute()
    {
        if (empty($this->phone)) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $this->phone);

        if (substr($phone, 0, 1) === '8' && strlen($phone) === 11) {
            $phone = '7' . substr($phone, 1);
        }

        if (strlen($phone) === 11 && substr($phone, 0, 1) === '7') {
            return sprintf(
                '+7(%s)%s-%s-%s',
                substr($phone, 1, 3),
                substr($phone, 4, 3),
                substr($phone, 7, 2),
                substr($phone, 9, 2)
            );
        }

        return $this->phone;
    }

    /**
     * Accessor: Инициалы в формате "Ф.О." (Имя Отчество)
     * Пример: "Михаил Дмитриевич" -> "М.Д."
     */
    public function getInitialsAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        if (count($parts) < 2) {
            // Если только фамилия или одно слово
            return mb_substr($parts[0], 0, 1, 'UTF-8') . '.';
        }

        // Берем второе (имя) и третье (отчество) слова
        $initials = '';

        // Имя (второе слово, индекс 1)
        if (isset($parts[1])) {
            $initials .= mb_substr($parts[1], 0, 1, 'UTF-8');
        }

        // Отчество (третье слово, индекс 2)
        if (isset($parts[2])) {
            $initials .= mb_substr($parts[2], 0, 1, 'UTF-8');
        }

        return mb_strtoupper($initials, 'UTF-8');
    }
    /**
     * Accessor: Инициалы в формате "И.Ф." (Имя Фамилия)
     * Пример: "Михаил Дмитриевич" -> "И.Ф."
     */
    public function getShortFiAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        if (count($parts) < 2) {
            // Если только фамилия или одно слово
            return mb_substr($parts[0], 0, 1, 'UTF-8') . '.';
        }

        // Берем второе (имя) и второе (фамилия) слова
        $initials = '';

        // Имя (второе слово, индекс 1)
        if (isset($parts[1])) {
            $initials .= mb_substr($parts[1], 0, 1, 'UTF-8');
        }

        // Отчество (третье слово, индекс 2)
        if (isset($parts[0])) {
            $initials .= mb_substr($parts[0], 0, 1, 'UTF-8');
        }

        return mb_strtoupper($initials, 'UTF-8');
    }

    /**
     * Accessor: Инициалы в формате "Фамилия Ф.О."
     * Пример: "Шевяков Михаил Дмитриевич" -> "Шевяков М.Д."
     */
    public function getShortFioAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        if (empty($parts)) {
            return null;
        }

        // Фамилия (первое слово)
        $result = $parts[0];

        // Добавляем инициалы имени и отчества
        if (isset($parts[1])) {
            $result .= ' ' . mb_substr($parts[1], 0, 1, 'UTF-8') . '.';
        }

        if (isset($parts[2])) {
            $result .= mb_substr($parts[2], 0, 1, 'UTF-8') . '.';
        }

        return $result;
    }

    /**
     * Accessor: Только фамилия
     */
    public function getLastNameAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        return $parts[0] ?? null;
    }

    /**
     * Accessor: Только имя
     */
    public function getFirstNameAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        return $parts[1] ?? null;
    }

    /**
     * Accessor: Только отчество
     */
    public function getMiddleNameAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));

        return $parts[2] ?? null;
    }

    /**
     * Accessor: Полные инициалы "Ф.И.О."
     * Пример: "Шевяков Михаил Дмитриевич" -> "Ш.М.Д."
     */
    public function getFullInitialsAttribute()
    {
        if (empty($this->fio)) {
            return null;
        }

        $parts = array_filter(explode(' ', trim($this->fio)));
        $initials = '';

        foreach ($parts as $part) {
            $initials .= mb_substr($part, 0, 1, 'UTF-8') . '.';
        }

        return mb_strtoupper($initials, 'UTF-8');
    }

    /**
     * Мутатор: Очистка телефона при сохранении
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Получить безопасные данные пользователя
     */
    public function getSafeAttributes()
    {
        return [
            'id' => $this->id,
            'fio' => $this->fio,
            'short_fio' => $this->short_fio,
            'short_fi' => $this->short_fi, // "ИФ"
            'initials' => $this->initials, // "ФО"
            'full_initials' => $this->full_initials, // "Ф.И.О."
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'formatted_phone' => $this->formatted_phone,
            'email' => $this->email,
            'region' => $this->region,
            'data_reg' => $this->data_reg,
        ];
    }
    /**
     * Добавляем вычисляемые поля при сериализации
     */
    protected $appends = [
        'formatted_phone',
        'initials',
        'short_fio',
        'full_initials',
        'last_name',
        'first_name',
        'middle_name'
    ];
}
