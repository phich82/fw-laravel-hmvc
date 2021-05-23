<?php

return [
    'email' => [
        'from' => env('MAIL_FROM_ADDRESS'),
        'from_name' => env('MAIL_FROM_NAME'),
        'to' => [
            ['phich82@gmail.com', 'Phich'],
            ['jhphich82@gmail.com', 'Jhp Phich'],
            ['nguyenphat82@gmail.com', 'Phat Huynh'],
            ['dev2nguyenphat82@gmail.com', 'Jhp Dev'],
        ],
        'cc' => [],
        'bcc' => [],
        'attachments' => []
    ],
    'slack' => [

    ],
    'skype' => [
        'username' => env('SKYPE_USERNAME'),
        'password' => env('SKYPE_PASSWORD'),
        'to' => [
            'live:.cid.948ea366e238f59d',
            'live:nguyenphat82_2',
            'live:nguyenphat82_1'
        ],
        'message' => 'Hi. This message is from admin system at '.date('Y-m-d H:i:s')
    ],
    'sms' => [

    ],
    'pusher' => [

    ]
];
