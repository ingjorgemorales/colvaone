<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentRiskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Incident $incident,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $isRisk = $this->incident->failure_type === 'risk';
        $failureName = $this->incident->failure?->name ?? ($isRisk ? 'Riesgo priorizado' : 'Incidente general');
        $prefix = $isRisk ? 'Alerta de Riesgo Priorizado' : 'Alerta de Incidente';

        return new Envelope(
            subject: "{$prefix}: {$failureName} ({$this->incident->ticket})",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.incident-risk-notification',
            with: [
                'incident' => $this->incident,
                'recipient' => $this->recipient,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
