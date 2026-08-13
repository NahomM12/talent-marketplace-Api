<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\PortfolioItemController;
use App\Http\Controllers\Api\Admin\ProfessionalController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Talent CRUD (index/store/update/destroy — no single-item GET, per the architecture doc).
    Route::apiResource('professionals', ProfessionalController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['professionals' => 'id']);

    // Portfolio CRUD.
    Route::apiResource('portfolio', PortfolioItemController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['portfolio' => 'id']);
});
Route::get('/professionals/{id}', [ProfessionalController::class, 'show'])->name('professionals.show');
Route::get('/portfolio/{id}', [PortfolioItemController::class, 'show'])->name('portfolio.show');
Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    // Superadmin-only: manage admin accounts.
    Route::apiResource('admins', AdminUserController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['admins' => 'id']);
});
