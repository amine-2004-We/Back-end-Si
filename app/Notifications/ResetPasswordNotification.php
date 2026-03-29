<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
            $frontend = match (app()->environment()) {
            'local', 'development' => rtrim(config('app.frontend_url_local', env('FRONTEND_URL_LOCAL', 'http://localhost:5173')), '/'),
            'production' => rtrim(config('app.frontend_url_prod', env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')), '/'),
            default => rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost')), '/'),
        };

        $email = urlencode($notifiable->getEmailForPasswordReset());
        $url = "{$frontend}/reset-password?token={$this->token}&email={$email}";

        $passwordsConfig = config('auth.defaults.passwords');
        $expireMinutes = config("auth.passwords.{$passwordsConfig}.expire", 60);

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour' . (isset($notifiable->name) ? ' ' . $notifiable->name : ''))
            ->line("Nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.")
            ->line("Le lien est valable pendant {$expireMinutes} minutes.")
            ->action('Réinitialiser mon mot de passe', $url)
            ->line("Si vous n'avez rien demandé, ignorez cet e-mail.")
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

}
