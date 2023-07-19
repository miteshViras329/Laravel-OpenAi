<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAiController;

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

Route::group(['controller' => OpenAiController::class], function () {
    Route::get('/', 'chat');
    Route::get('/get-chats', 'getChats');
});
