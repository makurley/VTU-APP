<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Sendmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->data['name'] != null)
        {
            $fname =  $this->data['name'];
        }else{
            $fname =  env('MAIL_FROM_NAME');
        }
        return $this->view($this->data['view'])
                    ->from($this->data['email'], $fname)
                    ->subject($this->data['subject'])
                    ->with('data', $this->data);
    }
}
