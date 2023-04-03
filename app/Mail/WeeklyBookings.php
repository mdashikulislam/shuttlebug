<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class WeeklyBookings extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $bookings;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param $bookings
     * @return void
     */
    public function __construct(User $user, $bookings)
    {
        $this->user = $user;
        $this->bookings = $bookings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('bookings@shuttlebug.co.za')
            ->subject('Confirm bookings for this week')
            ->markdown('emails.weekly-bookings');
    }
}
