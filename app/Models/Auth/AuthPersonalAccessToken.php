<?php


// app/Models/Auth/AuthPersonalAccessToken.php dans User Microservice
namespace App\Models\Auth;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Modèle pour accéder aux tokens de la base AuthenticationService
 */
class AuthPersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'auth_db';  // Utilise la connexion auth_db
    protected $table = 'personal_access_tokens';
    
    /**
     * Relation avec l'utilisateur auth
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable', 'tokenable_type', 'tokenable_id', 'id')
            ->setConnection('auth_db');
    }

}