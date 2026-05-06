<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $broker = config('auth.defaults.passwords');
            $expiresIn = (int) config("auth.passwords.{$broker}.expire", 60);

            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], absolute: false));

            $userName = $notifiable->name ?? null;

            return (new MailMessage)
                ->subject('Redefinição de senha — '.config('app.name'))
                ->view('mail.password-reset', [
                    'appName' => config('app.name'),
                    'userName' => $userName,
                    'resetUrl' => $resetUrl,
                    'expiresIn' => $expiresIn,
                ])
                ->text('mail.password-reset-text', [
                    'appName' => config('app.name'),
                    'userName' => $userName,
                    'resetUrl' => $resetUrl,
                    'expiresIn' => $expiresIn,
                ]);
        });
    }
}
