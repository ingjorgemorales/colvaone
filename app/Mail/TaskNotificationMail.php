<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public User $user,
        public string $action,
        public string $actionLabel,
        public string $actionDetail,
        public User $actedBy,
        public ?string $extra = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->actionLabel}: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.task-notification',
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'action' => $this->action,
                'actionLabel' => $this->actionLabel,
                'actionDetail' => $this->actionDetail,
                'actedBy' => $this->actedBy,
                'extra' => $this->extra,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
