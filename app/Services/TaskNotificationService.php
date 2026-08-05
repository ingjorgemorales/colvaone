<?php

namespace App\Services;

use App\Mail\TaskNotificationMail;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskBellNotification;
use Illuminate\Support\Facades\Mail;

class TaskNotificationService
{
    public function send(
        User $recipient,
        Task $task,
        string $action,
        string $actionLabel,
        string $actionDetail,
        User $actedBy,
        ?string $extra = null,
    ): void {
        Mail::to($recipient->email)->send(new TaskNotificationMail(
            $task,
            $recipient,
            $action,
            $actionLabel,
            $actionDetail,
            $actedBy,
            $extra,
        ));

        $recipient->notify(new TaskBellNotification(
            $task,
            $action,
            $actionLabel,
            $actionDetail,
            $actedBy,
            $extra,
        ));
    }
}
