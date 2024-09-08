<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Support\Facades\URL;

class ForgotPassword extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toMail($notifiable)
    {
        $url = URL::temporarySignedRoute('change-password', now()->addHours(12) ,['id' => $this->token]);
        return (new MailMessage)
                    ->line('Salut!')
                    ->subject('Changement de mot de passe')
                    ->line('Vous recevez ce mail car vous avez fait une demande de reinitialisation de mot de passe sur notre plateforme')
                    ->action('Reinitialiser le mot de passe', $url )
                    ->line("Si vous n'avez pas fait cette demande, veuillez ignorer ce mail.")
                    ->line('Merci!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
