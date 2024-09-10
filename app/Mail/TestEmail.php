<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        // Si tu veux passer des données au template, tu peux les ajouter ici
    }

    public function build()
    {
        return $this->view('vendor.mail.html.layout')  // Ton fichier de layout email
                    ->with([
                        'url' => 'https://example.com',  // Passer des variables au template
                        'slot' => 'Wanita Care'
                    ]);
    }
}
