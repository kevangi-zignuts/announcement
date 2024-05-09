<?php

use Illuminate\Http\Request;
use App\Http\Middleware\UserCheck;
use App\Http\Middleware\AdminCheck;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\UserAnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('isAdmin')->prefix('admin/announcements')->group( function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::post('/store', [AnnouncementController::class, 'store']);
        Route::get('/view/{id}', [AnnouncementController::class, 'view']);
        Route::post('/update/{id}', [AnnouncementController::class, 'update']);
        Route::get('/delete/{id}', [AnnouncementController::class, 'delete']);
    });
    Route::middleware('isUser')->prefix('user/announcements')->group(function () {
        Route::get('/', [UserAnnouncementController::class, 'index']);
        Route::get('/view/{id}', [UserAnnouncementController::class, 'view']);
    });
});
