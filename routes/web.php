<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Core\Notifier\Services\Contracts\NotifierContract;
use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Implementations\Sms;
use Core\Notifier\Services\Implementations\Sms\NexmoSms;

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

Route::get('/sms', function (SmsAdapter $sms) {
    dd(new Sms(new NexmoSms()), Sms::nexmo(['xxx']));
    //$message = $sms->send('Message from Twilio', '', ['phone_number' => '+84903012375']);
    //dd('Send sms.', $message);
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
