<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentUpdatedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Incident $incident,
        public User $recipient,
        public User $editor,
        public array $changes = [],
    ) {}

    public function envelope(): Envelope
    {
        $isRisk = $this->incident->failure_type === 'risk';
        $failureName = $this->incident->failure?->name ?? ($isRisk ? 'Riesgo priorizado' : 'Incidente general');
        $prefix = $isRisk ? 'Actualización de Riesgo Priorizado' : 'Actualización de Incidente';

        return new Envelope(
            subject: "{$prefix}: {$failureName} ({$this->incident->ticket})",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.incident-updated-notification',
            with: [
                'incident' => $this->incident,
                'recipient' => $this->recipient,
                'editor' => $this->editor,
                'changes' => $this->changes,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

