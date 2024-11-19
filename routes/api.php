<?php

use App\Http\Controllers\Api\BookApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get("/books", [BookApiController::class, "index"]);
Route::post("/books", [BookApiController::class, "store"]);
Route::put("/books/{id}", [BookApiController::class, "update"]);
Route::get("/books/{id}", [BookApiController::class, "show"]);
Route::delete("/books/{id}", [BookApiController::class, "destroy"]);