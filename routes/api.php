<?php

use App\Http\Controllers\Api\PageApiController;
use App\Http\Controllers\Api\ElementApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'nullable|string|max:255',
    ]);

    $user = \App\Models\User::where('email', $validated['email'])->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($validated['password'], $user->password)) {
        return response()->json(['message' => 'Credenciais inválidas.'], 401);
    }

    $token = $user->createToken(
        $validated['device_name'] ?? 'API Token',
        ['*']
    );

    return response()->json([
        'token' => $token->plainTextToken,
        'user' => $user,
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::delete('/tokens', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token revogado']);
    });

    Route::apiResource('pages', PageApiController::class);
    Route::get('pages/{page}/elements', [ElementApiController::class, 'index']);
    Route::post('pages/{page}/elements', [ElementApiController::class, 'store']);
    Route::apiResource('elements', ElementApiController::class)->only(['show', 'update', 'destroy']);
});
