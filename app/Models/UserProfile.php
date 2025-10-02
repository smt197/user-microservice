<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Changed from Model
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserProfile extends Authenticatable implements JWTSubject // Implemented JWTSubject
{
    use HasFactory;

    protected $table = 'user_profiles';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'auth_user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'auth_user_id',
        'name',
        'email',
        'bio',               // Champs étendus uniquement
        'avatar',
        'phone',
        'address',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}