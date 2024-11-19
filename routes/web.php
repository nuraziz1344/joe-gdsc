<?php

use App\Http\Controllers\FirebaseControl;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/index', [FirebaseControl::class, 'index'])->name('index');
Route::get('/tambah', [FirebaseControl::class, 'create'])->name('tambah.create');
Route::post('/tambah', [FirebaseControl::class, 'store'])->name('tambah.store');
