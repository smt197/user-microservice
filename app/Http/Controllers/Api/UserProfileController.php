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
     * Récupérer le profil de l'utilisateur connecté
     */
    public function currentUserProfile(Request $request)
    {
        // Récupérer les données utilisateur validées par le middleware
        $authUser = $request->attributes->get('auth_user');

        if (!$authUser) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Chercher ou créer le profil utilisateur
        $userProfile = UserProfile::firstOrCreate(
            ['auth_user_id' => $authUser['id']],
            [
                'name' => $authUser['name'],
                'email' => $authUser['email'],
                'bio' => '',
                'avatar' => null,
                'phone' => null,
                'address' => null,
                'preferences' => []
            ]
        );

        return response()->json($userProfile);
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateCurrentUserProfile(Request $request)
    {
        // Récupérer les données utilisateur validées par le middleware
        $authUser = $request->attributes->get('auth_user');

        if (!$authUser) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'preferences' => 'nullable|array',
        ]);

        $userProfile = UserProfile::updateOrCreate(
            ['auth_user_id' => $authUser['id']],
            array_merge($validated, [
                'name' => $authUser['name'],
                'email' => $authUser['email'],
            ])
        );

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

    public function includes(): array
    {
        return [];
    }

    public function alwaysIncludes(): array
    {
        return [];
    }

    public function aggregates(): array
    {
        return [];
    }

    /**
     * Override pour filtrer automatiquement par l'utilisateur connecté
     */
    public function resolveResourcePaginate($query, $request, $paginationLimit)
    {
        $authUser = $request->attributes->get('auth_user');
        if ($authUser) {
            $query->where('auth_user_id', $authUser['id']);
        }
        return parent::resolveResourcePaginate($query, $request, $paginationLimit);
    }
}