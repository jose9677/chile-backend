<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class asignarSolicitudMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

     public $subject;
     public $id;
     public $names;
     public $lastname;
     public $visit_date;

    public function __construct($id, $names, $lastname, $visit_date)
    {
        $this->id = $id;
        $this->names = $names;
        $this->lastname = $lastname;
        $this->visit_date = $visit_date;
        $this->subject = 'Cita Asignada - Solicitud Nro. '.$id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.asignarSolicitud')->with('id', $this->id, 'names', $this->names, 'lastname', $this->lastname, 'visit_date', $this->visit_date);
    }
}
