<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Core\Notifier\Services\Contracts\NotifierContract;

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
    Log::info('Web Test Log');
    return view('welcome');
});

Route::get('/api/v1/test/error', function () {
    Log::error('Error Test - Api V1');
    dd('Error Test - Api V1');
});

Route::get('/webhook/test', function () {
    Log::info('Webhook Test');
    dd('Webhook Test');
});

Route::get('/webhook/v1/test', function () {
    Log::info('Webhook V1 Test');
    dd('Webhook V1 Test');
});

Route::get('/webhook/v2/test', function () {
    Log::info('Webhook V2 Test');
    dd('Webhook V2 Test');
});

Route::get('/sendmail', function (NotifierContract $notifier) {
    $notifier->send('test', 'test', ['test']);
    dd('SendMail Test.');
});
