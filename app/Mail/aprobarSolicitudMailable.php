<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class aprobarSolicitudMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

     public $subject;
     public $id;

    public function __construct($id)
    {
        $this->id = $id;
        $this->subject = 'Su solicitud Nro. '.$id.' ha sido Aprobada';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.aprobarSolicitud')->with('id', $this->id);
    }
}
