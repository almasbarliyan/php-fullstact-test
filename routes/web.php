<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\MyClientController;

Route::get('clients', [MyClientController::class, 'index']);
Route::get('clients/{id}', [MyClientController::class, 'show']);
Route::post('clients', [MyClientController::class, 'store']);
Route::put('clients/{id}', [MyClientController::class, 'update']);
Route::delete('clients/{id}', [MyClientController::class, 'destroy']);