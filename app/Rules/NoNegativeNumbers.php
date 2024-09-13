<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoNegativeNumbers implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail("The $attribute cannot contain negative numbers.");
        }
    }
    public function passes($attribute, $value)
    {
        $garagesDataArray = is_string($value) ? json_decode($value, true) : $value;

        if (is_array($garagesDataArray)) {
            if (count($garagesDataArray) > 3) {
                return false;
            }
            foreach ($garagesDataArray as $item) {
                if (isset($item['number_meter'])) {
                    if (!ctype_digit($item['number_meter']) || $item['number_meter'] <= 0 || strlen($item['number_meter']) > 19) {
                        return false;
                    }
                }
                if (isset($item['number_garage'])) {
                    if (!ctype_digit($item['number_garage']) || $item['number_garage'] <= 0 || strlen($item['number_garage']) > 19) {
                        return false;
                    }
                }
                if (isset($item['number_block'])) {
                    if (!ctype_digit($item['number_block']) || $item['number_block'] <= 0 || strlen($item['number_block']) > 19) {
                        return false;
                    }
                }
            }
        }

        return true;
    }


}
