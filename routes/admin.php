<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\ContactMessageController;
use App\Http\Controllers\Api\Admin\PortfolioItemController;
use App\Http\Controllers\Api\Admin\ProfessionalController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('admin.login');

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('admin.me');

    // Admin Talent CRUD
    Route::apiResource('professionals', ProfessionalController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['professionals' => 'id'])
        ->names('admin.professionals');

    // Admin Portfolio CRUD
    Route::apiResource('portfolio', PortfolioItemController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['portfolio' => 'id'])
        ->names('admin.portfolio');

    // Admin-only show routes — RENAMED to avoid conflict
    Route::get('/professionals/{id}', [ProfessionalController::class, 'show'])
        ->name('admin.professionals.show');
    Route::get('/portfolio/{id}', [PortfolioItemController::class, 'show'])
        ->name('admin.portfolio.show');

    // Admin Contact Message management
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])
        ->name('admin.contact-messages.index');
    Route::get('/contact-messages/{id}', [ContactMessageController::class, 'show'])
        ->name('admin.contact-messages.show');
    Route::delete('/contact-messages/{id}', [ContactMessageController::class, 'destroy'])
        ->name('admin.contact-messages.destroy');
});

Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
    Route::apiResource('admins', AdminUserController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['admins' => 'id'])
        ->names('superadmin.admins');
});