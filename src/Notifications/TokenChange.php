<?php

namespace Ikechukwukalu\Tokenmiddleware\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TokenChange extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('tokenmiddleware::notify.token.subject'))
            ->line(trans('tokenmiddleware::notify.token.introduction'))
            ->line(trans('tokenmiddleware::notify.token.message'))
            ->action(trans('tokenmiddleware::notify.token.action'), route('changeToken'))
            ->line(trans('tokenmiddleware::notify.token.complimentary_close'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
