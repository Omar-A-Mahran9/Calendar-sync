<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

Auth::routes();
// routes/web.php





Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function(){
Route::resource('events',EventController::class);
Route::get('refetch-events',[EventController::class,'refetchEvents'])->name('refetch-events');
Route::get('/login/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback']);

});
