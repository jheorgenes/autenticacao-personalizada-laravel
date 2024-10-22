<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticable;

// Transformando esse User Model em um User que estende de Illuminate\Foundation\Auth\User [apelidado de Authenticable] (autenticação)
class User extends Authenticable
{

    // atributes that are hidden for serialization
    protected $hidden = [
        'password',
        'token'
    ];
}
