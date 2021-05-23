<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SubscriptionController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Auth::routes(['verify' => true]);

Route::get('subscription-success', function () {
    SubscriptionController::makeUserPremium();
    return redirect(env('FRONTEND_URL', 'https://app.feedbeggar.com/'));
})->name('subscription-success');

Route::get('subscription-error', function () {
    return redirect(env('FRONTEND_URL', 'https://app.feedbeggar.com/settings?subscription=false'));
})->name('subscription-error');
