<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class asignarVisitadorMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject;
    public $names ;
    public $lastname;
    public $id;

    public function __construct($names, $lastname, $id)
    {
        $this->names = $names;
        $this->lastname = $lastname;
        $this->id = $id;
        $this->subject = 'Se te ha asignado la solicitud Nro.'.$id.'- Salud a Tu Alcance';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.asignarVisitador')->with('names', $this->names, 'lastname', $this->lastname, 'id', $this->id);
    }
}
