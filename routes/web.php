<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// default
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Pintu masuk umum: setelah login, arahkan sesuai peran
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('super_admin')) {
        return redirect()->route('super-admin.dashboard');
    }

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('kasir')) {
        return redirect()->route('kasir.dashboard');
    }

    abort(403, 'Akun Anda belum memiliki peran.');
})->middleware(['auth', 'verified'])->name('dashboard');

// Area Super Admin
Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', fn () => view('dashboards.super-admin'))->name('dashboard');
    });

// Area Admin (super admin juga boleh masuk)
Route::middleware(['auth', 'verified', 'role:super_admin|admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn () => view('dashboards.admin'))->name('dashboard');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
    });

// Area Kasir
Route::middleware(['auth', 'verified', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::get('/dashboard', fn () => view('dashboards.kasir'))->name('dashboard');
    });

require __DIR__.'/auth.php';
