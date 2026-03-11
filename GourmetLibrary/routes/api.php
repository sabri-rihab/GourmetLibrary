<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GourmetLibrary — API Routes
|--------------------------------------------------------------------------
| All routes here are prefixed with /api automatically (set in bootstrap/app.php).
| Auth routes use no middleware; protected routes require Sanctum token.
|--------------------------------------------------------------------------
*/

// ── Public Auth Routes ───────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {

    // POST /api/auth/register
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // POST /api/auth/login
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// ── Protected Routes (require valid Sanctum token) ───────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // POST /api/auth/logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // GET /api/user  — returns the authenticated user's info
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    })->name('user.me');
});
