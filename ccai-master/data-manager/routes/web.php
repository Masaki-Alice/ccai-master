<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # Inventory
    Route::get('inventory/{id}/gemini-assist', [InventoryController::class, 'suggestWithGemini'])->name('inventory.suggest_gemini');
    Route::resource('/inventory', InventoryController::class);
    Route::post('/transcribe/{audioID}/{audioType}', [InventoryController::class, 'transcribeAudio'])->name('transcribe');
    Route::post('slicer/{id}/queue', [InventoryController::class, 'sliceAudio'])->name('slice');

    # Save ground truth data
    Route::post('ground-truth/{id}/save', [TranscriptionController::class, 'saveGroundTruth'])->name('transcriptions.ground-truth');
    Route::resource('transcriptions', TranscriptionController::class);
    Route::get('audio/play/{audioID}/{audioType}', [InventoryController::class, 'streamAudio'])->name('audio.play');

    Route::get('redact/{id}', [InventoryController::class, 'redactTranscript'])->name('redact');

    # Sync source files from bucket
    Route::get('dataset/sync', [DatasetController::class, 'syncFromBucket']);
});

# Export ZIP file dataset
Route::get('dataset/export', [DatasetController::class, 'export']);

Route::get('/dlp-test', [TestController::class, 'dlpTest']);

Route::get('test', [TestController::class, 'bigquery']);
