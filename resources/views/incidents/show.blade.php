<x-layouts.app title="{{ $incident->ticket }} | {{ config('app.name') }}" heading="{{ $incident->ticket }}" subheading="Detalle del incidente">
    @include('incidents.styles')
    <div class="incident-page incident-detail" style="max-width:1100px">
        <div class="incident-toolbar">
            <a class="btn-secondary" href="{{ route('incidents.index') }}"><i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver al listado</a>
            <div class="incident-buttons">
                <a class="btn-secondary" download href="{{ route('incidents.pdf', $incident) }}"><i data-lucide="file-down" style="width:16px;height:16px"></i> Generar PDF</a>
                @include('incidents.actions', ['detail' => true])
            </div>
        </div>
        <div class="incident-detail-summary">
            <div><h2>{{ $incident->failure?->name }}</h2><span class="incident-muted">{{ $incident->ticket }}</span></div>
            <div class="incident-buttons"><span class="incident-pill {{ $incident->is_active ? 'tone-success' : 'tone-warning' }}">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at)<span class="incident-pill">Archivado</span>@endif</div>
        </div>
        <div class="incident-detail-sections">
            <section class="incident-detail-panel" aria-labelledby="incident-reporter-heading">
                <h2 id="incident-reporter-heading" class="incident-panel-heading"><i data-lucide="user-round" aria-hidden="true"></i>Información de quien reporta</h2>
                <dl class="incident-panel-body incident-details incident-grid">
                    <div><dt>Nombre completo</dt><dd>{{ $incident->reporter_name }}</dd></div>
                    <div><dt>Cargo</dt><dd>{{ $incident->reporter_position }}</dd></div>
                    <div class="incident-full"><dt>Área / dependencia</dt><dd>{{ $incident->area?->name }}</dd></div>
                </dl>
            </section>
            <section class="incident-detail-panel" aria-labelledby="incident-location-heading">
                <h2 id="incident-location-heading" class="incident-panel-heading"><i data-lucide="map-pin" aria-hidden="true"></i>Ubicación del incidente</h2>
                <dl class="incident-panel-body incident-details incident-grid"><div class="incident-full"><dt>Sede</dt><dd>{{ $incident->zone?->name ?? 'Sin especificar' }}</dd></div><div class="incident-full"><dt>Ubicación exacta</dt><dd>{{ $incident->location }}</dd></div></dl>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-description-heading">
                <h2 id="incident-description-heading" class="incident-panel-heading"><i data-lucide="clipboard-list" aria-hidden="true"></i>Detalles de Incidente o Riesgo</h2>
                <dl class="incident-panel-body incident-details incident-grid">
                    <div><dt>Clasificación</dt><dd><span class="incident-pill {{ $incident->failure_type === 'risk' ? 'tone-warning' : '' }}">{{ $incident->failure_type === 'risk' ? 'Riesgo priorizado' : 'Incidente general' }}</span></dd></div>
                    <div><dt>{{ $incident->failure_type === 'risk' ? 'Riesgo priorizado' : 'Tipo de falla' }}</dt><dd><strong>{{ $incident->failure?->name }}</strong></dd></div>
                    <div><dt>Persona responsable</dt><dd><strong>{{ $incident->responsible_name ?: 'Sin asignar' }}</strong>@if($incident->responsibleUser?->email) <span class="incident-muted">({{ $incident->responsibleUser->email }})</span>@endif</dd></div>
                    <div><dt>Cargo del responsable</dt><dd><strong>{{ $incident->responsible_position ?: 'Sin especificar' }}</strong></dd></div>
                    <div><dt>¿El incidente afecta la operación?</dt><dd>{{ $incident->impact?->name }}</dd></div>
                    <div class="incident-full"><dt>Descripción del problema</dt><dd class="incident-description">{{ $incident->description }}</dd></div>
                    <div class="incident-full"><dt>Equipos o activos afectados</dt><dd>{{ $incident->affected_assets ?: 'Sin especificar' }}</dd></div>
                </dl>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-evidence-heading">
                <h2 id="incident-evidence-heading" class="incident-panel-heading"><i data-lucide="paperclip" aria-hidden="true"></i>Evidencias adjuntas</h2>
                <div class="incident-panel-body">@include('incidents.evidence')</div>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-tracking-heading">
                <h2 id="incident-tracking-heading" class="incident-panel-heading"><i data-lucide="history" aria-hidden="true"></i>Prioridad y seguimiento</h2>
                <dl class="incident-panel-body incident-details incident-grid three">
                    <div><dt>Prioridad sugerida</dt><dd><span class="incident-pill {{ $incident->priority_tone }}">{{ $incident->priority?->name }}</span></dd></div>
                    <div><dt>Generado por</dt><dd>{{ $incident->creator?->name }} {{ $incident->creator?->last_name }}</dd></div>
                    <div><dt>Fecha de registro</dt><dd>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Última actualización</dt><dd>{{ $incident->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    @if($incident->archived_at)<div><dt>Fecha de archivo</dt><dd>{{ $incident->archived_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>@endif
                </dl>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-comments-heading">
                <h2 id="incident-comments-heading" class="incident-panel-heading"><i data-lucide="message-square-text" aria-hidden="true"></i>Comentarios de las personas involucradas ({{ $incident->comments->count() }})</h2>
                <div class="incident-panel-body">
                    @if(!$incident->archived_at)
                        <form method="POST" action="{{ route('incidents.comments.add', $incident) }}" style="margin-bottom:20px">
                            @csrf
                            <label for="incident_comment" style="display:block;font-size:13px;font-weight:600;color:#1e293b;margin-bottom:6px">Agregar un comentario o actualización</label>
                            <textarea id="incident_comment" name="comment" required rows="3" class="input-field" placeholder="Escribe aquí un comentario, observación o avance sobre el incidente..." style="width:100%;margin-bottom:8px"></textarea>
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:13px">
                                    <i data-lucide="send" style="width:14px;height:14px"></i> Enviar comentario
                                </button>
                            </div>
                        </form>
                    @endif

                    <div style="display:flex;flex-direction:column;gap:12px">
                        @forelse($incident->comments as $comment)
                            <div style="display:flex;gap:12px;padding:12px 16px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0">
                                <div style="width:34px;height:34px;border-radius:50%;background:#0b3967;color:#ffffff;display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0">
                                    {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;margin-bottom:4px">
                                        <div>
                                            <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $comment->user->name }} {{ $comment->user->last_name }}</span>
                                            @if($comment->user->position)
                                                <span style="font-size:12px;color:#64748b;margin-left:4px">&middot; {{ $comment->user->position }}</span>
                                            @endif
                                            @if($comment->user_id === $incident->created_by)
                                                <span class="incident-pill" style="font-size:10px;padding:1px 6px;margin-left:6px;background:#e0f2fe;color:#0369a1">Reportante</span>
                                            @elseif($comment->user_id === $incident->responsible_user_id)
                                                <span class="incident-pill tone-warning" style="font-size:10px;padding:1px 6px;margin-left:6px">Responsable</span>
                                            @endif
                                        </div>
                                        <time style="font-size:11px;color:#94a3b8" title="{{ $comment->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}">
                                            {{ $comment->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                        </time>
                                    </div>
                                    <p style="font-size:13px;color:#334155;margin:0;line-height:1.6;white-space:pre-wrap">{{ $comment->comment }}</p>
                                </div>
                            </div>
                        @empty
                            <p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px 0;margin:0">No hay comentarios registrados por las personas involucradas aún.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
