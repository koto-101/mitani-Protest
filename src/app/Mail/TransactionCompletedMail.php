<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatRoom;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $chatRoom;

    public function __construct(ChatRoom $chatRoom)
    {
        $this->chatRoom = $chatRoom;
    }

    public function build()
    {
        return $this->subject('購入者が取引を完了しました')
                    ->view('emails.transaction_completed')
                    ->with(['chatRoom' => $this->chatRoom]);
    }
}
