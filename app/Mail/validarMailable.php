<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class validarMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject = "Recuperar Contraseña - Salud a Tu Alcance";
    public $api_token; 
    public $app_url;

    public function __construct($api_token, $app_url)
    {
        $this->api_token = $api_token;
        $this->app_url = $app_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.validar')->with('api_token', $this->api_token, 'app_url', $this->app_url);
    }
}
