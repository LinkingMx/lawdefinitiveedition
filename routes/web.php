<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Document routes
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])
        ->name('documents.preview')
        ->can('view', 'document');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download')
        ->can('download', 'document');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
