<?php

namespace Core\Notifier\Providers;

use Devtools\Providers\AbstractModuleProvider;
use Core\Notifier\Services\Implementations\Sms;
use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Implementations\Skype;
use Core\Notifier\Services\Implementations\Slack;
use Core\Notifier\Services\Contracts\SkypeAdapter;
use Core\Notifier\Services\Contracts\SlackAdapter;
use Core\Notifier\Services\Implementations\Mailer;
use Core\Notifier\Services\Implementations\Pusher;
use Core\Notifier\Services\Contracts\MailerAdapter;
use Core\Notifier\Services\Contracts\PusherAdapter;
use Core\Notifier\Services\Implementations\EmailOnly;
use Core\Notifier\Services\Contracts\NotifierContract;
use Core\Notifier\Services\Implementations\SmsNotifier;
use Core\Notifier\Services\Contracts\LogNotifierContract;
use Core\Notifier\Services\Implementations\SkypeNotifier;
use Core\Notifier\Services\Implementations\SlackNotifier;
use Core\Notifier\Services\Implementations\PushNotification;
use Core\Notifier\Services\Contracts\PushNotificationAdapter;
use Core\Notifier\Services\Implementations\Sms\NexmoSms;
use Core\Notifier\Services\Implementations\Sms\TwilioSms;

class ModuleServiceProvider extends AbstractModuleProvider
{
    /**
     * @return string
     */
    public function getDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return 'notifier';
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(MailerAdapter::class, function ($app) {
            return new Mailer();
        });
        $this->app->singleton(SmsAdapter::class, function ($app) {
            return new Sms();
        });
        $this->app->singleton(SlackAdapter::class, function ($app) {
            return new Slack();
        });
        $this->app->singleton(SkypeAdapter::class, function ($app) {
            return new Skype();
        });
        $this->app->singleton(PusherAdapter::class, function ($app) {
            return new Pusher();
        });
        $this->app->singleton(PushNotificationAdapter::class, function ($app) {
            return new PushNotification();
        });
        $this->app->singleton(NotifierContract::class, function ($app) {
            // Only email
            // return new EmailOnly(new Mailer);

            // Email & Slack
            // return new SlackNotifier(
            //     new EmailOnly(new Mailer),
            //     new Slack
            // );

            // Email, Slack & Skype
            // return new SkypeNotifier(
            //     new SlackNotifier(
            //         new EmailOnly(new Mailer),
            //         new Slack
            //     ),
            //     new Skype
            // );

            // Email, Slack, Skype & Sms
            // return new SmsNotifier(
            //     new SkypeNotifier(
            //         new SlackNotifier(
            //             new EmailOnly(new Mailer),
            //             new Slack
            //         ),
            //         new Skype
            //     ),
            //     new Sms
            // );

            return new SmsNotifier(
                new SkypeNotifier(
                    new SlackNotifier(
                        new EmailOnly(new Mailer),
                        new Slack
                    ),
                    new Skype
                ),
                // new TwilioSms([
                //     'phone_number' => ['+84903012375', '+841673850375'],
                //     'from' => env('TWILIO_FROM')
                // ])
                new NexmoSms([
                    'phone_number' => ['84373850375'],
                    'from' => 'Vonage APIs', //env('TWILIO_FROM')
                ])
            );
        });

        $this->app->singleton(LogNotifierContract::class, function ($app) {
            // Only email
            // return new EmailOnly(new Mailer);

            // Email & Slack
            return new SlackNotifier(
                new EmailOnly(new Mailer),
                new Slack
            );
        });
    }
}
