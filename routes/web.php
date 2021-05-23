<?php

use Core\Notifier\Services\Contracts\LogNotifierContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Core\Notifier\Services\Contracts\NotifierContract;
use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Implementations\Skype\SkypePHP;
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

Route::get('/sendmail', function () {
    $notifier = app()->make(LogNotifierContract::class);
    $notifier->send('test', 'test', ['test']);
    dd('SendMail Test.');
});

Route::get('/sendmail/full', function (NotifierContract $notifier) {
    $notifier->send('test', 'test', ['test']);
    dd('SendMail Test.');
});

Route::get('/skype', function () {
    $password = '123@haPHAT';
    $username1 = 'nguyenphat82@gmail.com';
    $skypeId1 = 'live:nguyenphat82_2';
    $username2 = 'dev2nguyenphat82@outlook.com';
    $skypeId2 = 'live:.cid.948ea366e238f59d';

    $skype = new SkypePHP;

    $username = $username1;
    $receiver = $skypeId2;

    $skype->login($username, $password) or die ('Username or password is invalid');
    $skype->sendMessage($receiver, "Hello From {$username} - ".date('Y-m-d H:i:s'));

    // $group = $skype->createGroup([$skypeId1, $skypeId2], 'sporting-dev');
    // $skype->addUserToGroup( 'live:nguyenphat82_1', $group);
    // $group = '19:56e1000620f248fdb2268adf0b0a7286@thread.skype';

    // $skype->kickUser('live:nguyenphat82_1', $group);
    // $skype->leaveGroup($group);

    // $skype->addContact('live:nguyenphat82_1', 'Hello, Jhp Phich.');

    dd(
        'Send to skype',
        'Conversation List',
        $skype->getConversationsList(),
        'Group Name',
        // $group,
        // 'Group Info',
        // $skype->getGroupInfo($group),
        'Contact List',
        $skype->getContactsList(),
        'Profile List',
        // $skype->readProfile(['live:.cid.948ea366e238f59d', 'live:nguyenphat82_2']),
        'Search Result',
        $skype->search('nguyenphat82'),
        // $skype->readMyProfile(),
        // $skype->getMessagesList($skypeId1),
        // $skype->getMessagesList($skypeId2),
        // $skype->logout(),
    );
});
