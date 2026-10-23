<div class="incident-actions">
    @unless($detail ?? false)
        <a class="incident-icon" href="{{ route('incidents.show', $incident) }}" title="Ver incidente" aria-label="Ver {{ $incident->ticket }}"><i data-lucide="eye"></i></a>
    @endunless
    @if(!$incident->archived_at)
        @if(auth()->user()->hasPermission('incidents.edit'))
            <a class="incident-icon edit" href="{{ route('incidents.edit', $incident) }}" title="Editar incidente" aria-label="Editar {{ $incident->ticket }}"><i data-lucide="pencil"></i></a>
        @endif
        @if(auth()->user()->hasPermission('incidents.toggle'))
            <form method="POST" action="{{ route('incidents.toggle', $incident) }}" onsubmit="return confirm('¿Confirmas el cambio de actividad del incidente?')">
                @csrf
                <button class="incident-icon {{ $incident->is_active ? 'disable' : '' }}" title="{{ $incident->is_active ? 'Inactivar' : 'Reactivar' }} incidente" aria-label="{{ $incident->is_active ? 'Inactivar' : 'Reactivar' }} {{ $incident->ticket }}"><i data-lucide="{{ $incident->is_active ? 'power-off' : 'power' }}"></i></button>
            </form>
        @endif
    @endif
    @if(auth()->user()->hasPermission('incidents.archive') && ($incident->archived_at || $incident->status === 'resuelto'))
        <form method="POST" action="{{ route('incidents.archive', $incident) }}" onsubmit="return confirm('¿Confirmas {{ $incident->archived_at ? 'restaurar' : 'archivar' }} el incidente?')">
            @csrf
            <button class="incident-icon archive" title="{{ $incident->archived_at ? 'Restaurar' : 'Archivar' }} incidente" aria-label="{{ $incident->archived_at ? 'Restaurar' : 'Archivar' }} {{ $incident->ticket }}"><i data-lucide="{{ $incident->archived_at ? 'archive-restore' : 'archive' }}"></i></button>
        </form>
    @endif
</div>
