<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Jenssegers\Date\Date;

class MyYear implements ValidationRule
{
    private $yearNow;
    private $maxYear;

    public function __construct()
    {
        $this->yearNow = Date::now()->year;
        $this->maxYear = $this->yearNow + 1;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }

    public function passes($attribute, $value): bool
    {
        // Проверяем, что значение является целым числом в диапазоне допустимых годов
        return is_numeric($value) && $value >= 2023 && $value <= $this->yearNow;
    }

    public function message(): string
    {
        return "Минимальный год - 2023, максимальный {$this->yearNow}.";
    }
}
