<?php

use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\TranscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'v1'], function () {
    # Transcribe audio via API
    Route::get('transcribe', [TranscriptionController::class, 'transcribeGCSAudio']);

    # Find any file on Google Drive
    Route::resource('inventory', InventoryController::class);
});
