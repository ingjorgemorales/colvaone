<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Committee;
use App\Models\Indicator;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AiChatContextService
{
    public function build(User $user, string $question): string
    {
        $terms = $this->keywords($question);
        $sections = [
            'Usuario actual: ' . trim($user->name . ' ' . ($user->last_name ?? '')) . ' <' . $user->email . '>',
        ];

        $taskContext = $this->tasks($user, $question, $terms);
        if ($taskContext !== '') {
            $sections[] = $taskContext;
        }

        $indicatorContext = $this->indicators($user, $terms);
        if ($indicatorContext !== '') {
            $sections[] = $indicatorContext;
        }

        $applicationContext = $this->applications($user, $terms);
        if ($applicationContext !== '') {
            $sections[] = $applicationContext;
        }

        $committeeContext = $this->committees($user, $terms);
        if ($committeeContext !== '') {
            $sections[] = $committeeContext;
        }

        $userContext = $this->users($user, $question, $terms);
        if ($userContext !== '') {
            $sections[] = $userContext;
        }

        return implode("\n\n", $sections);
    }

    private function tasks(User $user, string $question, array $terms): string
    {
        if (!$user->hasAnyPermission(['group_tasks.view', 'group_tasks.view_group', 'group_tasks.view_all'])) {
            return '';
        }

        $query = Task::with(['group', 'assignees'])->visibleFor($user);

        $normalizedQuestion = Str::lower(Str::ascii($question));

        if (!Str::contains($normalizedQuestion, ['archiv'])) {
            $query->where('tasks.status', '!=', 'archivada');
        }

        if (Str::contains($normalizedQuestion, ['retras', 'vencid'])) {
            $query->whereDate('tasks.end_date', '<', today())
                ->whereNotIn('tasks.status', Task::LOCKED_STATUSES);
        } elseif (Str::contains($normalizedQuestion, ['finaliz', 'complet'])) {
            $query->whereIn('tasks.status', ['finalizada', 'completada']);
        } elseif (Str::contains($normalizedQuestion, ['cancel'])) {
            $query->where('tasks.status', 'cancelada');
        } elseif (Str::contains($normalizedQuestion, ['archiv'])) {
            $query->where('tasks.status', 'archivada');
        } else {
            $this->applyTerms($query, ['tasks.title', 'tasks.description', 'tasks.area', 'tasks.status'], $terms);
        }

        $tasks = $query->orderBy('end_date')->limit(8)->get();

        $baseTaskQuery = Task::query()->visibleFor($user);
        $activeCount = (clone $baseTaskQuery)->whereIn('tasks.status', ['pendiente', 'asignada', 'en_progreso'])->count();
        $delayedCount = (clone $baseTaskQuery)
            ->whereDate('tasks.end_date', '<', today())
            ->whereNotIn('tasks.status', Task::LOCKED_STATUSES)
            ->count();

        $lines = [
            "Tareas visibles: {$activeCount} activas, {$delayedCount} retrasadas.",
        ];

        foreach ($tasks as $task) {
            $assignees = $task->assignees->pluck('name')->join(', ') ?: 'Sin asignados';
            $delay = $task->isDelayed() ? ', retraso: ' . $task->days_delayed . ' dias' : '';
            $lines[] = "- {$task->title}; estado: {$task->status_label}; progreso: {$task->progress}%; grupo: " . ($task->group->name ?? 'Sin grupo') . "; asignados: {$assignees}; vence: {$task->end_date->format('d/m/Y')}{$delay}.";
        }

        return implode("\n", $lines);
    }

    private function indicators(User $user, array $terms): string
    {
        if (!$user->hasAnyPermission(['indicators.view', 'indicators.view_all'])) {
            return '';
        }

        $query = Indicator::with(['responsible', 'latestResult', 'process', 'subprocess'])->visibleFor($user);
        $this->applyTerms($query, ['indicators.name', 'indicators.objective', 'indicators.type', 'indicators.category'], $terms);

        $indicators = $query->orderBy('name')->limit(8)->get();

        if ($indicators->isEmpty()) {
            return '';
        }

        $lines = ['Indicadores visibles relacionados:'];

        foreach ($indicators as $indicator) {
            $latest = $indicator->latestResult
                ? $indicator->latestResult->compliance . '% (' . $indicator->latestResult->evaluation_label . ')'
                : 'sin resultado vigente';

            $lines[] = "- {$indicator->name}; categoria: {$indicator->category_label}; proceso: {$indicator->process_label}; responsable: " . ($indicator->responsible->name ?? 'Sin responsable') . "; meta: {$indicator->goal}%; ultimo resultado: {$latest}.";
        }

        return implode("\n", $lines);
    }

    private function applications(User $user, array $terms): string
    {
        if (!$user->hasPermission('applications.view')) {
            return '';
        }

        $query = Application::query()->where('status', 'active');
        $this->applyTerms($query, ['name', 'description', 'url'], $terms);

        $applications = $query->orderBy('name')->limit(8)->get();

        if ($applications->isEmpty()) {
            return '';
        }

        $lines = ['Aplicativos activos relacionados:'];

        foreach ($applications as $application) {
            $description = $application->description ? '; descripcion: ' . Str::limit($application->description, 120) : '';
            $lines[] = "- {$application->name}; URL: {$application->url}{$description}.";
        }

        return implode("\n", $lines);
    }

    private function committees(User $user, array $terms): string
    {
        if (!$user->hasAnyPermission(['committees.view', 'committees.view_all'])) {
            return '';
        }

        $query = Committee::with(['members', 'latestReport'])->visibleFor($user);
        $this->applyTerms($query, ['committees.title', 'committees.summary', 'committees.status'], $terms);

        $committees = $query->orderByDesc('committee_date')->limit(6)->get();

        if ($committees->isEmpty()) {
            return '';
        }

        $lines = ['Comites visibles relacionados:'];

        foreach ($committees as $committee) {
            $members = $committee->members->pluck('name')->join(', ') ?: 'Sin integrantes';
            $report = $committee->latestReport?->content ? '; ultimo relato: ' . Str::limit($committee->latestReport->content, 160) : '';
            $lines[] = "- {$committee->title}; fecha: {$committee->committee_date->format('d/m/Y')}; estado: {$committee->status_label}; integrantes: {$members}{$report}.";
        }

        return implode("\n", $lines);
    }

    private function users(User $user, string $question, array $terms): string
    {
        if (!$user->hasPermission('users.view') || !$this->mentionsUsers($question)) {
            return '';
        }

        $query = User::with('roleObject')->where('is_active', true);
        $this->applyTerms($query, ['name', 'last_name', 'email', 'document_number', 'position', 'area', 'department'], $terms);

        $users = $query->orderBy('name')->limit(8)->get();

        if ($users->isEmpty()) {
            return '';
        }

        $lines = ['Usuarios activos relacionados:'];

        foreach ($users as $visibleUser) {
            $lines[] = "- " . trim($visibleUser->name . ' ' . ($visibleUser->last_name ?? '')) . "; documento: {$visibleUser->document_type} {$visibleUser->document_number}; cargo: " . ($visibleUser->position ?: 'Sin cargo') . "; rol: {$visibleUser->role_label}; correo: {$visibleUser->email}.";
        }

        return implode("\n", $lines);
    }

    private function applyTerms(Builder $query, array $columns, array $terms): void
    {
        if ($terms === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($columns, $terms): void {
            foreach ($terms as $term) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', '%' . $term . '%');
                }
            }
        });
    }

    private function keywords(string $question): array
    {
        $normalized = Str::of($question)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9@._-]+/', ' ')
            ->explode(' ')
            ->map(fn ($term) => trim($term))
            ->filter(fn ($term) => strlen($term) >= 3)
            ->reject(fn ($term) => in_array($term, $this->stopWords(), true))
            ->take(10)
            ->values()
            ->all();

        return $normalized;
    }

    private function mentionsUsers(string $question): bool
    {
        return Str::contains(Str::lower(Str::ascii($question)), [
            'usuario',
            'usuarios',
            'persona',
            'cedula',
            'documento',
            'correo',
            'email',
            'cargo',
        ]);
    }

    private function stopWords(): array
    {
        return [
            'que',
            'con',
            'los',
            'las',
            'del',
            'por',
            'para',
            'una',
            'uno',
            'unos',
            'unas',
            'como',
            'cual',
            'cuales',
            'dame',
            'muestra',
            'muestrame',
            'revisa',
            'revisar',
            'revisame',
            'mira',
            'mirar',
            'mirame',
            'tengo',
            'tenga',
            'tengas',
            'ver',
            'resumen',
            'resumeme',
            'sobre',
            'esta',
            'este',
            'estos',
            'estas',
            'tiene',
            'tienen',
            'activo',
            'activos',
            'activa',
            'activas',
            'tarea',
            'tareas',
            'indicador',
            'indicadores',
            'aplicativo',
            'aplicativos',
            'aplicacion',
            'aplicaciones',
            'comite',
            'comites',
            'url',
            'urls',
            'link',
            'links',
        ];
    }
}
