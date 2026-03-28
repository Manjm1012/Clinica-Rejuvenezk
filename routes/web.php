<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TayraiWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/procedimientos/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/contacto', [LeadController::class, 'store'])->name('leads.store');

Route::post('/webhooks/tayrai', TayraiWebhookController::class)->name('webhooks.tayrai');
