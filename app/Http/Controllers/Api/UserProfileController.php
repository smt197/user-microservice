<?php

namespace App\Http\Controllers\Api;

use App\Models\UserProfile;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    use DisableAuthorization;

    protected $model = UserProfile::class;

    /**
     * Récupérer le profil de l'utilisateur connecté.
     */
    public function currentUserProfile(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Chercher le profil. `findOrFail` renverra une erreur 404 si non trouvé, ce qui est approprié.
        $userProfile = UserProfile::findOrFail($userId);

        return response()->json($userProfile);
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté.
     */
    public function updateCurrentUserProfile(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'preferences' => 'nullable|array',
        ]);

        $userProfile = UserProfile::findOrFail($userId);
        $userProfile->update($validated);

        return response()->json($userProfile);
    }

    // Méthodes pour Orion
    public function filterableBy(): array
    {
        return ['auth_user_id', 'name', 'email'];
    }

    public function searchableBy(): array
    {
        return ['name', 'email', 'bio'];
    }

    public function sortableBy(): array
    {
        return ['auth_user_id', 'name', 'email', 'created_at', 'updated_at'];
    }

    /**
     * Assure que la route principale d'Orion (`/api/user-profiles`) 
     * ne retourne que le profil de l'utilisateur connecté.
     */
    protected function buildIndexQuery($request, array $requestedRelations): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::buildIndexQuery($request, $requestedRelations);
        
        if (Auth::check()) {
            $query->where('auth_user_id', Auth::id());
        }
        
        return $query;
    }
}
