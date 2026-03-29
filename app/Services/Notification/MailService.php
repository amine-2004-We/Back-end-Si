<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Envoi un email générique
     *
     * @param string|array $to Adresse(s) du destinataire
     * @param string $subject Sujet du mail
     * @param string $view Nom de la vue Blade pour le contenu
     * @param array $data Variables passées à la vue
     * @return void
     */
    public static function sendMail($to, string $subject, string $view, array $data = []): void
    {
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject)
                ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    /**
     * @param $to
     * @param string $subject
     * @param string $content
     * @return void
     */
    public static function sendMailRaw($to, string $subject, string $content): void
    {
        Mail::raw($content, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject)
                ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }
}
