<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MainEmail extends Mailable
{
    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $femail = env('MAIL_FROM_ADDRESS');
        $fname = env('MAIL_FROM_NAME');
        //return $this->view('view.name');
        return $this->view('email.main')
                    ->from($femail, $fname )
                    ->subject($this->data['subject'])
                    ->with('data', $this->data);
                ;
    }
}
