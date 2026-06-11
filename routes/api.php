<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\PublicContentController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [PublicContentController::class, 'home']);
Route::get('/services/{service:slug}', [PublicContentController::class, 'service']);
Route::post('/leads', [LeadApiController::class, 'store']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	Route::get('/auth/me', [AuthController::class, 'me']);
	Route::post('/auth/logout', [AuthController::class, 'logout']);
});
