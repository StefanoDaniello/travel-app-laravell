<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TravelController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('travel', [TravelController::class, 'index']);
Route::get('/travel/{slug}', [TravelController::class, 'show']);
Route::post('/travel', [TravelController::class, 'store']);
// Route::put('/travel/{slug}', [TravelController::class, 'update']);

