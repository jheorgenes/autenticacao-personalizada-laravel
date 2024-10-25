<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticable;

// Transformando esse User Model em um User que estende de Illuminate\Foundation\Auth\User [apelidado de Authenticable] (autenticação)
class User extends Authenticable
{
    // Indicando ao Eloquent que esse user será um soft delete
    use SoftDeletes;

    // atributes that are hidden for serialization
    protected $hidden = [
        'password',
        'token'
    ];
}
