<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskBellNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $action,
        public string $actionLabel,
        public string $actionDetail,
        public User $actedBy,
        public ?string $extra = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->payload()))->onConnection('sync');
    }

    public function databaseType(object $notifiable): string
    {
        return 'task';
    }

    public function broadcastType(): string
    {
        return 'task';
    }

    private function payload(): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->actionLabel,
            'body' => "{$this->actedBy->name} {$this->actionDetail}",
            'extra' => $this->extra,
            'action' => $this->action,
            'url' => route('tasks.show', $this->task),
            'created_at' => now()->toISOString(),
        ];
    }
}
