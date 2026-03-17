<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $invitee,
        public User $inviter,
        public string $token,
    ) {
    }

    public function envelope(): Envelope
    {
        $companyName = (string) ($this->inviter->company?->name ?? 'your team');

        return new Envelope(
            subject: "You've been invited to join {$companyName} on Pulsify",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invite',
            with: [
                'invitee' => $this->invitee,
                'inviter' => $this->inviter,
                'company' => $this->inviter->company,
                'token' => $this->token,
            ],
        );
    }
}

