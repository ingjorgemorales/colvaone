<x-layouts.app title="Nuevo grupo | {{ config('app.name') }}" heading="Nuevo grupo" subheading="Crear un grupo de trabajo">
    <div style="max-width:700px">
        <div class="card" style="padding:28px">
            <form method="POST" action="{{ route('groups.store') }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre del grupo *</label>
                    <input name="name" type="text" value="{{ old('name') }}" required class="input-field @error('name') {{ 'error-field' }} @enderror" placeholder="Ej: Equipo de Desarrollo">
                    @error('name')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                            <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Descripcion</label>
                    <textarea name="description" rows="3" class="input-field" style="resize:vertical" placeholder="Describe el proposito del grupo...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Gerente(s) responsable(s) *</label>
                    <input type="text" data-member-search="#managersContainer" placeholder="Buscar por nombre o cedula..." class="input-field" style="margin-bottom:8px">
                    <div id="managersContainer" style="border:1px solid rgba(18,63,110,0.12);border-radius:10px;padding:10px;max-height:180px;overflow-y:auto;background:white">
                        @foreach($users as $u)
                            <label data-member-item data-search="{{ $u->name }} {{ $u->last_name }} {{ $u->email }} {{ $u->document_number }}" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background 0.15s;font-size:13px;color:#1e293b" onmouseover="this.style.background='rgba(18,63,110,0.04)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="managers[]" value="{{ $u->id }}" {{ in_array($u->id, old('managers', [])) ? 'checked' : '' }} style="accent-color:#123f6e">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ strtoupper(substr($u->name,0,1).substr($u->last_name??'',0,1)) }}</div>
                                <div>
                                    <span style="font-weight:500">{{ $u->name }} {{ $u->last_name }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->role_label }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->document_type }} {{ $u->document_number }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('managers')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                            <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Integrantes</label>
                    <input type="text" data-member-search="#groupMembersContainer" placeholder="Buscar por nombre o cedula..." class="input-field" style="margin-bottom:8px">
                    <div id="groupMembersContainer" style="border:1px solid rgba(18,63,110,0.12);border-radius:10px;padding:10px;max-height:220px;overflow-y:auto;background:white">
                        @foreach($users as $u)
                            <label data-member-item data-search="{{ $u->name }} {{ $u->last_name }} {{ $u->email }} {{ $u->document_number }}" style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background 0.15s;font-size:13px;color:#1e293b" onmouseover="this.style.background='rgba(18,63,110,0.04)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="members[]" value="{{ $u->id }}" {{ in_array($u->id, old('members', [])) ? 'checked' : '' }} style="accent-color:#123f6e">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ strtoupper(substr($u->name,0,1).substr($u->last_name??'',0,1)) }}</div>
                                <div>
                                    <span style="font-weight:500">{{ $u->name }} {{ $u->last_name }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->role_label }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->document_type }} {{ $u->document_number }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px">Selecciona los usuarios que formaran parte del grupo.</p>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end">
                    <a href="{{ route('groups.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px"></i> Crear grupo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>.error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }</style>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
