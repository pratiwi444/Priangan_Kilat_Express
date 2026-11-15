<?php
namespace App\Rules;

use App\Support\Telepon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TeleponValid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Telepon::normalisasi((string) $value) === null) {
            $fail('Nomor telepon tidak valid: tidak boleh berisi huruf dan minimal 10 digit.');
        }
    }
}