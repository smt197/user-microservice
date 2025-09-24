<?php

// app/Models/Auth/AuthUser.php dans User Microservice
namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle pour accéder aux utilisateurs de la base AuthenticationService
 */
class AuthUser extends Model
{
    use HasApiTokens;

    protected $connection = 'auth_db';  // Utilise la connexion auth_db
    protected $table = 'users';
    
    // Définir les attributs lisibles (read-only depuis User Microservice)
    protected $fillable = [];  // Vide car on ne modifie pas depuis ici
    
    // Relations avec les tokens
    public function tokens()
    {
        return $this->hasMany(AuthPersonalAccessToken::class, 'tokenable_id');
    }
}
