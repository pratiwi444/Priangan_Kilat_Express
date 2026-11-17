<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    public $timestamps = false;

    protected $fillable = ['nama', 'username', 'password', 'role', 'wajib_ganti_password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    public function kurir() { return $this->hasOne(Kurir::class); }
}