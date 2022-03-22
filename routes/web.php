<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Core\Notifier\Services\Implementations\Sms;
use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Implementations\Push\Push;
use Core\Notifier\Services\Contracts\NotifierContract;
use Core\Notifier\Services\Implementations\Push\WebPush;
use Core\Notifier\Services\Implementations\Sms\NexmoSms;
use Core\Notifier\Services\Contracts\LogNotifierContract;
use Core\Notifier\Services\Implementations\Sms\TwilioSms;
use Core\Notifier\Services\Implementations\Skype\SkypePHP;

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

Route::get('/sms/{phone?}', function ($phone = '+84373850375', SmsAdapter $sms) {
    $sms = Sms::twilio();// new Sms(new TwilioSms()); // new Sms(new NexmoSms(); //Sms::nexmo(); //Sms::twilio();
    $message = $sms->send("Message from {$sms->provider}: ".date('Y-m-d H:i:s'), '', ['phone_number' => $phone]);
    echo "Sms sent to [{$phone}]: ".($message ? 'success' : 'error');
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

Route::get('/slack', function () {
    $settings = [
        'username' => 'System',
        'channel' => '#general',
        'link_names' => true
    ];
    $slack = new \Maknz\Slack\Client(env('SLACK_WEBHOOK'), $settings);
    foreach (['@dev2nguyenphat82', '@jhphich82'] as $to) {
        $client = $slack->to('#develop');
        $client = $client->withIcon(':ghost:');
        $client->to($to);
        $client->send('Hello Jhp Phich!');
    }
    dd('Slack Test.');
});

Route::get('/dispatch', function () {

    dispatch(function () {
        // sleep(2);
        Log::info('[Dispatch] => 1');
    });
    dispatch(function () {
        // sleep(2);
        Log::info('[Dispatch] => 2');
    });
    Log::info('[Dispatch] => Test');
    dd('Dispatch Test.');
});

Route::get('/push', function () {
    $push = (new Push)->fcm()->send([
        'title' => 'Test Title',
        'message' => 'Test Message',
        'payload' => [
            'data' => 'Test'
        ],
        'device_tokens' => [
            'fYA0rOPpQlap-LKkgjVJZ_:APA91bGFIL0i95s3aVmSTpKBPilaZ031llVze2fu2Lr6VxNWKsEjUMPOIoBwX7mgRLEm7rIMgJwHpts0bmT3DTUNIWth1O1XOFSx_ZkXw1WMrrHOD4676fl4dbyomaMaT-KUwO9AXiG4'
        ]
    ]);
    $failed = $push->getFailed();
    $error = array_shift($failed);
    dd($error->getMessage());
    dd('Push Notification Test.');
});

/** Register device tokens or endpoint */
Route::post('/register-push-notification', function () {
    $params = request()->all();
    if (!empty($params) && $params['subscription']['endpoint']) {
        $row = DB::table('device_tokens')->where(['endpoint' => $params['subscription']['endpoint'], 'type' => 3])->first();
        if (empty($row)) {
            $result = DB::table('device_tokens')->insert([
                'endpoint'     => $params['subscription']['endpoint'],
                'expiry'       => $params['subscription']['expirationTime'],
                'subscription' => json_encode($params['subscription']),
                'ip'           => $params['ip'],
                'browser'      => $params['browser'],
                'type'         => 3, // 1: android, 2: ios, 3: web
                'track_log'    => 'Insert:System:'.date('Y-m-d H:i:s'),
                'created_at'   => date('Y-m-d H:i:s'),
                ]);
            return response()->json([
                'success' => $result,
                'action'  => 'insert'
            ]);
        } else {
            $result = DB::table('device_tokens')->where(['endpoint' => $params['subscription']['endpoint'], 'type' => 3])->update([
                'expiry'       => $params['subscription']['expirationTime'],
                'subscription' => json_encode($params['subscription']),
                'ip'           => $params['ip'],
                'browser'      => $params['browser'],
                'track_log'    => $row->track_log."\nUpdate:System:".date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            return response()->json([
                'success' => !!$result,
                'action'  => 'update'
            ]);
        }
    }

    return response()->json([
        'success' => false,
        'error' => 'Missing the endpoint key or empty.'
    ]);
});
Route::get('/sendpush', function () {
    $rows = DB::table('device_tokens')->where(['type' => 3])->get()->toArray();
    $webpush = new WebPush;
    $errors = [];
    foreach ($rows as $row) {
        $result = $webpush->send([
            'subscription' => json_decode($row->subscription, true),
            'payload' => [
                'title' => 'Notification Test',
                'body'  => "Hello! 👋.\nThis is webpush message: ".date('Y-m-d H:i:s'),
                'icon'  => "https://picsum.photos/64",
                'image' => "https://picsum.photos/200",
                'badge' => "https://picsum.photos/32",
                'sound' => "http://commondatastorage.googleapis.com/codeskulptor-demos/DDR_assets/Kangaroo_MusiQue_-_The_Neverwritten_Role_Playing_Game.mp3",
                'lang'  => 'en-US',
                'tag'   => null,
                'data'  => null,
                'vibrate' => true,
                'requireInteraction' => false,
            ]
        ]);
        if ($result) {
            $errors[] = $result;
        }
    }
    echo 'Pushed: '.(count($rows) - count($errors));
});
