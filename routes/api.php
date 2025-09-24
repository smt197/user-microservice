<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;
use App\Http\Controllers\Api\UserProfileController;

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Routes spécifiques pour le profil de l'utilisateur connecté
    Route::get('/me/profile', [UserProfileController::class, 'currentUserProfile']);
    Route::put('/me/profile', [UserProfileController::class, 'updateCurrentUserProfile']);

    // Route de test pour vérifier l'authentification automatique
    Route::get('/me/test', function (Request $request) {
        $authUser = $request->attributes->get('auth_user');
        return response()->json([
            'message' => 'Authentication successful via cookie!',
            'user' => $authUser,
            'timestamp' => now()
        ]);
    });

    // Routes CRUD standard pour UserProfile (admin ou autres besoins)
    Orion::resource('user-profiles', UserProfileController::class);
});