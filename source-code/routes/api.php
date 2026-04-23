<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\PollController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('authorization')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('/channels')->group(function () {
            Route::resource('/', ChannelController::class)->only(['index', 'show', 'store']);
            Route::post('/join/{token}', [ChannelController::class, 'joinByToken'])
                ->middleware('throttle:10,1');
        });

        Route::prefix('/channels/{channelId}/polls')->group(function () {
            Route::resource('/', PollController::class)->only(['index', 'show', 'store']);

            Route::post('/{poll}/vote', [PollController::class, 'vote'])
                ->middleware('throttle:3,1');
            Route::get('/{poll}/results', [PollController::class, 'results']);
        });
    });
});
