<x-mail::message>
# Nueva tarea asignada

Hola **{{ $user->name }}**,

**{{ $creator->name }}** te ha asignado una nueva tarea.

## Detalles de la tarea

| Campo | Valor |
|-------|-------|
| **Tarea** | {{ $task->title }} |
| **Area** | {{ $task->area ?? 'Sin area' }} |
| **Fecha inicio** | {{ $task->start_date->format('d/m/Y') }} |
| **Fecha fin** | {{ $task->end_date->format('d/m/Y') }} |
| **Prioridad** | {{ ucfirst($task->priority) }} |

@if($task->description)
### Descripcion
{!! nl2br(e($task->description)) !!}
@endif

@if($task->observations)
### Observaciones
{!! nl2br(e($task->observations)) !!}
@endif

<x-mail::button :url="route('tasks.show', $task)">
Ver tarea
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
