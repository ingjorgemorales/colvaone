<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Task $task,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva tarea asignada: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.task-assigned',
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'creator' => $this->task->creator,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
