<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class claveEmpleadoMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject = 'Contraseña Empleado';
    public $rut; 
    public $api_token;
    public $app_url;

    public function __construct($rut, $api_token, $app_url)
    {
        $this->rut = $rut;
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
        return $this->view('emails.claveEmpleado')->with('rut', $this->rut, 'api_token', $this->api_token, 'app_url', $this->app_url);
    }
}
