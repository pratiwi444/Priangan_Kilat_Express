<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{

    protected $fillable = ['nama', 'username', 'password', 'role', 'wajib_ganti_password'];

    protected $hidden = ['password', 'remember_token'];
     
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }
}
