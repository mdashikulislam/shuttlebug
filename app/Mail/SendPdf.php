<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPdf extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $data;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param $data
     * @return void
     */
    public function __construct(User $user, $data)
    {
        $this->user = $user;
        $this->data  = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from('admin.hb@shuttlebug.co.za')
            ->markdown('emails.pdf-documents');

        if ( $this->data['doc'] == 'inv' ) {
            $email->subject('Statement & Invoice');
            $email->attach(storage_path('pdf/shuttlebug-invoice.pdf'), [
                'mime' => 'application/pdf']);
        } else {
            $email->subject('Statement');
            $email->attach(storage_path('pdf/shuttlebug-statement.pdf'), [
                'mime' => 'application/pdf']);
        }

        return $email;
    }
}
