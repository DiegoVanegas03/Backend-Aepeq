<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $hash = sha1($notifiable->getEmailForVerification());
            return 'http://localhost:3000/verifyMail?id='.$notifiable->getKey().'&hash='.$hash . '&name='. $notifiable->getNameUser();
        });
    }
}
