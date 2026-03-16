<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
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

// ── Public Routes (no token required) ────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login',    [AuthController::class, 'login'])->name('login');
});

// List all categories (public — gourmands can browse without logging in)
Route::get('/categories', [CategoryController::class, 'show'])->name('categories.index');

// ── Gourmand Routes (token required) ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Authenticated user info
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    })->name('user.me');

    // ── [US-G1] Browse books by category ─────────────────────────────────────
    // GET /api/books/category/{identifier}  — identifier can be numeric ID or slug
    Route::get('/books/category/{identifier}', [BookController::class, 'byCategory'])
         ->name('books.by-category');

    // ── [US-G2] Search books by title, author, or category ───────────────────
    // GET /api/books/search?q=...
    Route::get('/books/search', [BookController::class, 'search'])
         ->name('books.search');

    // ── [US-G3a] Most popular books (most borrowed) ───────────────────────────
    // GET /api/books/popular?category_id=...&limit=...
    Route::get('/books/popular', [BookController::class, 'popular'])
         ->name('books.popular');

    // ── [US-G3b] New arrivals (last 30 days) ─────────────────────────────────
    // GET /api/books/new-arrivals?category_id=...&limit=...
    Route::get('/books/new-arrivals', [BookController::class, 'newArrivals'])
         ->name('books.new-arrivals');

    // ── [US-SLUG] Human-readable book detail URL ──────────────────────────────
    // GET /api/{categorySlug}/livres/{bookSlug}
    // Example: GET /api/cuisine-italienne/livres/les-meilleures-recettes-de-pates
    Route::get('/{categorySlug}/livres/{bookSlug}', [BookController::class, 'showBySlug'])
         ->name('books.show-by-slug');

    // ── Admin Routes (token required; controller checks role === 'admin') ─────
    Route::prefix('admin')->name('admin.')->group(function () {

        // [US-A1] Category CRUD
        Route::post('/categories',       [CategoryController::class, 'create'])->name('categories.store');
        Route::put('/categories/{id}',   [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}',[CategoryController::class, 'destroy'])->name('categories.destroy');

        // [US-A2] Book CRUD
        Route::post('/books',            [BookController::class, 'store'])->name('books.store');
        Route::put('/books/{id}',        [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{id}',     [BookController::class, 'destroy'])->name('books.destroy');

        // [US-A3] Collection statistics
        Route::get('/stats',             [AdminController::class, 'stats'])->name('stats');

        // [US-A4] Degraded copies report
        Route::get('/degraded-copies',   [AdminController::class, 'degradedCopies'])->name('degraded-copies');
    });
});

// ── Quick smoke-test ──────────────────────────────────────────────────────────
Route::get('/test', fn () => response()->json(['message' => 'GourmetLibrary API is up!']));