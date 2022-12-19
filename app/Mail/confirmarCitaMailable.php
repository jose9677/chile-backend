<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class confirmarCitaMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject = 'Cita confirmada - Solicitud Nro. {{$id}} - Salud a Tu Alcance';
    public $names;
    public $lastname;
    public $id;

    public function __construct($names, $lastname, $id)
    {
        $this->names = $names;
        $this->lastname = $lastname;
        $this->id = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.confirmarCita')->with('names', $this->names, 'lastname', $this->lastname, 'id', $this->id);
    }
}
