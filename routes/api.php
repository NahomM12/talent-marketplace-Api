<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\PortfolioItemController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/professionals', [ProfessionalController::class, 'index'])->name('professionals.index');
Route::get('/professionals/{professional}', [ProfessionalController::class, 'show'])->name('professionals.show');

Route::get('/portfolio', [PortfolioItemController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{portfolioItem}', [PortfolioItemController::class, 'show'])->name('portfolio.show');

Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');

Route::prefix('admin')->group(base_path('routes/admin.php'));
Route::middleware('auth:sanctum')->get('/debug-auth-user', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    return response()->json([
        'user' => $user,
        'user_class' => $user ? get_class($user) : null,
    ]);
});