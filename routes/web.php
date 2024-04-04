<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
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

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/news/{slug}', [LandingController::class, 'newsDetail'])->name('news.detail');
Route::get('/news-all', [LandingController::class, 'newsAll'])->name('news.all');

Route::get('/download-profile', [LandingController::class, 'formDownloadProfile'])->name('download-profile');
Route::post('/send-mail/{form}', [LandingController::class, 'sendMail'])->name('send-mail');
Route::get('/term-and-conditions', [LandingController::class, 'tnc'])->name('tnc');
Route::get('/frequently-asked-questions', [LandingController::class, 'faq'])->name('faq');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginProcess'])->name('login.process');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');