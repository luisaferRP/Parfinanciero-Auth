<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_id',
        'session_token',
        'expires_at'
    ];

    //relacion de sesion a ususario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
