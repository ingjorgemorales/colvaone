<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskOverdueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public User $user,
        public int $daysOverdue,
        public bool $isCreator,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tarea retrasada: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.task-overdue-reminder',
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'daysOverdue' => $this->daysOverdue,
                'isCreator' => $this->isCreator,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
