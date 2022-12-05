<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class reprogramarCitaMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject = 'REPROGRAMACIÓN';
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
        return $this->view('emails.reprogramarCita')->with('names', $this->names, 'lastname', $this->lastname, 'id', $this->id);
    }
}
