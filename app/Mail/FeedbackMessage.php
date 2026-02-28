<?php

namespace App\Mail;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $type; // coach|admin

    public User $user;

    public ?Team $team;

    public string $bodyMessage;

    public ?string $attachmentDisk;

    public ?string $attachmentPath;

    public function __construct(
        string $type,
        User $user,
        string $subject,
        string $message,
        ?Team $team = null,
        ?string $attachmentDisk = null,
        ?string $attachmentPath = null,
        ?string $locale = null,
    ) {
        $this->type = $type;
        $this->user = $user;
        $this->subject($subject);
        $this->bodyMessage = $message;
        $this->team = $team;
        $this->attachmentDisk = $attachmentDisk;
        $this->attachmentPath = $attachmentPath;

        if ($locale) {
            $this->locale($locale);
        }
    }

    public function build(): self
    {
        $mail = $this->view('emails.feedback.message')
            ->with([
                'type' => $this->type,
                'user' => $this->user,
                'team' => $this->team,
                'bodyMessage' => $this->bodyMessage,
            ]);

        if ($this->attachmentDisk && $this->attachmentPath) {
            $mail->attachFromStorageDisk($this->attachmentDisk, $this->attachmentPath);
        }

        return $mail;
    }
}
