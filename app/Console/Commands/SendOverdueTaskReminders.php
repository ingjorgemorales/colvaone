<?php

namespace App\Console\Commands;

use App\Mail\TaskOverdueReminderMail;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendOverdueTaskReminders extends Command
{
    protected $signature = 'tasks:send-overdue-reminders {--dry-run : Show how many reminders would be sent without sending emails.}';

    protected $description = 'Send daily email reminders for overdue tasks to assignees and creators.';

    public function handle(): int
    {
        $now = now();
        $cutoff = $now->copy()->subDay();
        $sent = 0;

        Task::query()
            ->with(['assignees', 'creator', 'group'])
            ->whereDate('end_date', '<', today())
            ->whereNotIn('status', Task::LOCKED_STATUSES)
            ->chunkById(100, function ($tasks) use ($now, $cutoff, &$sent): void {
                foreach ($tasks as $task) {
                    $recipients = $task->assignees
                        ->when($task->creator, fn ($users) => $users->push($task->creator))
                        ->unique('id')
                        ->values();

                    foreach ($recipients as $recipient) {
                        $reminder = DB::table('task_overdue_reminders')
                            ->where('task_id', $task->id)
                            ->where('user_id', $recipient->id)
                            ->first();

                        if ($reminder?->last_sent_at && CarbonImmutable::parse($reminder->last_sent_at)->greaterThan($cutoff)) {
                            continue;
                        }

                        $daysOverdue = max(1, (int) $task->end_date->startOfDay()->diffInDays(today(), false));

                        if (! $this->option('dry-run')) {
                            Mail::to($recipient->email)->send(new TaskOverdueReminderMail(
                                $task,
                                $recipient,
                                $daysOverdue,
                                $task->created_by === $recipient->id,
                            ));

                            DB::table('task_overdue_reminders')->updateOrInsert(
                                ['task_id' => $task->id, 'user_id' => $recipient->id],
                                ['last_sent_at' => $now, 'updated_at' => $now, 'created_at' => $reminder->created_at ?? $now],
                            );
                        }

                        $sent++;
                    }
                }
            });

        $label = $this->option('dry-run') ? 'Overdue task reminders pending' : 'Overdue task reminders sent';
        $this->info("{$label}: {$sent}");

        return self::SUCCESS;
    }
}
