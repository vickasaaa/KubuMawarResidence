<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\BookingController;

// ─── Public Routes (no auth required) ───
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ─── Google OAuth ───
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// ─── Public Rooms (Landing Page & User Dashboard browse) ───
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);

// ─── Public / Guest Booking Creation ───
Route::post('/bookings', [BookingController::class, 'store']);

// ─── Admin / Authenticated Room & Booking Management ───
// Accessible for Admin operations (also usable with Bearer token)
Route::group([], function () {
    // Rooms CRUD & Status Quick-Toggle
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{id}', [RoomController::class, 'update']);
    Route::patch('/rooms/{id}/status', [RoomController::class, 'updateStatus']);
    Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

    // Bookings Management
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
});

// ─── Protected Routes (auth required) ───
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
