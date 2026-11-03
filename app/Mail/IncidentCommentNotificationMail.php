<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentCommentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Incident $incident,
        public IncidentComment $comment,
        public User $recipient,
        public User $author,
    ) {}

    public function envelope(): Envelope
    {
        $authorName = trim($this->author->name.' '.$this->author->last_name);
        $failureName = $this->incident->failure?->name ?? 'Incidente';

        return new Envelope(
            subject: "Nuevo comentario en {$this->incident->ticket}: {$failureName} ({$authorName})",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.incident-comment-notification',
            with: [
                'incident' => $this->incident,
                'comment' => $this->comment,
                'recipient' => $this->recipient,
                'author' => $this->author,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

