<x-layouts.app title="Nuevo comite | {{ config('app.name') }}" heading="Nuevo comite" subheading="Registra los integrantes y relatos del comite">
    <div style="max-width:820px">
        <div class="card" style="padding:28px">
            <form method="POST" action="{{ route('committees.store') }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf

                <div style="display:grid;grid-template-columns:1.5fr 220px;gap:16px">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre del comite *</label>
                        <input name="title" type="text" value="{{ old('title') }}" required class="input-field @error('title') {{ 'error-field' }} @enderror" placeholder="Ej: Comite operativo semanal">
                        @error('title') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Fecha *</label>
                        <input name="committee_date" type="date" value="{{ old('committee_date', today()->format('Y-m-d')) }}" min="{{ today()->format('Y-m-d') }}" required class="input-field @error('committee_date') {{ 'error-field' }} @enderror">
                        @error('committee_date') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Integrantes *</label>
                    <input type="text" data-member-search="#committeeMembersContainer" placeholder="Buscar por nombre o cedula..." class="input-field" style="margin-bottom:8px">
                    <div id="committeeMembersContainer" style="border:1px solid rgba(18,63,110,0.12);border-radius:10px;padding:10px;max-height:240px;overflow-y:auto;background:white">
                        @foreach($users as $u)
                            <label data-member-item data-search="{{ $u->name }} {{ $u->last_name }} {{ $u->email }} {{ $u->document_number }}" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background 0.15s;font-size:13px;color:#1e293b" onmouseover="this.style.background='rgba(18,63,110,0.04)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="members[]" value="{{ $u->id }}" {{ in_array($u->id, old('members', [])) ? 'checked' : '' }} style="accent-color:#123f6e">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ $u->initials }}</div>
                                <div>
                                    <span style="font-weight:500">{{ $u->name }} {{ $u->last_name }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->role_label }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->document_type }} {{ $u->document_number }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('members') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Relatos *</label>
                    <div id="reportsContainer" style="display:flex;flex-direction:column;gap:10px">
                        @foreach(old('reports', ['']) as $report)
                            <textarea name="reports[]" rows="5" required class="input-field @error('reports') {{ 'error-field' }} @enderror @error('reports.*') {{ 'error-field' }} @enderror" style="resize:vertical" placeholder="Escribe el resumen de lo hablado, acuerdos o notas relevantes...">{{ $report }}</textarea>
                        @endforeach
                    </div>
                    @error('reports') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    @error('reports.*') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    <button type="button" class="btn-secondary" style="margin-top:10px;padding:8px 12px;font-size:12px" onclick="addReportField()">
                        <i data-lucide="plus" style="width:14px;height:14px"></i> Agregar relato
                    </button>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end">
                    <a href="{{ route('committees.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px"></i> Crear comite
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }
        @media (max-width: 720px) { form > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; } }
    </style>
    <script>
        function addReportField() {
            const container = document.getElementById('reportsContainer');
            const textarea = document.createElement('textarea');
            textarea.name = 'reports[]';
            textarea.rows = 5;
            textarea.required = true;
            textarea.className = 'input-field';
            textarea.style.resize = 'vertical';
            textarea.placeholder = 'Escribe otro relato del comite...';
            container.appendChild(textarea);
        }

        setTimeout(() => lucide.createIcons(), 300);
    </script>
</x-layouts.app>
