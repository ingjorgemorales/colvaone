<x-layouts.app title="Editar rol | {{ config('app.name') }}" heading="Editar rol" subheading="Modifica el rol y sus permisos">
    <div style="max-width:800px;margin:0 auto">
        <div class="card" style="padding:24px">
            <form method="POST" action="{{ route('roles.update', $role) }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf
                @method('PUT')

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre del rol *</label>
                        <input name="name" type="text" value="{{ old('name', $role->name) }}" required class="input-field">
                        @error('name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Descripcion</label>
                        <input name="description" type="text" value="{{ old('description', $role->description) }}" class="input-field">
                    </div>
                </div>

                <div style="border-top:1px solid rgba(18,63,110,0.06);padding-top:16px">
                    <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 12px;display:flex;align-items:center;gap:8px">
                        <i data-lucide="key-round" style="width:16px;height:16px;color:#123f6e"></i> Permisos
                    </h3>
                    <p style="font-size:13px;color:#94a3b8;margin:0 0 16px">Selecciona los permisos que tendra este rol:</p>

                    @foreach($permissions as $group)
                        <div style="margin-bottom:16px;padding:16px;border-radius:12px;background:rgba(18,63,110,0.02);border:1px solid rgba(18,63,110,0.04)">
                            <h4 style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 10px;display:flex;align-items:center;gap:6px">
                                {{ $group['name'] }}
                                <label style="font-size:11px;font-weight:400;color:#94a3b8;cursor:pointer">
                                    <input type="checkbox" class="group-toggle" data-group="{{ $group['name'] }}" style="accent-color:#123f6e;margin-right:4px"> Todos
                                </label>
                            </h4>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px">
                                @foreach($group['items'] as $perm => $label)
                                    <label style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;font-size:13px;color:#475569;cursor:pointer;transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.04)'" onmouseout="this.style.background='transparent'">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }} style="accent-color:#123f6e;width:16px;height:16px">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex;align-items:center;gap:12px;padding-top:8px">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" style="width:16px;height:16px"></i> Guardar cambios
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        setTimeout(() => lucide.createIcons(), 300);
        document.querySelectorAll('.group-toggle').forEach(btn => {
            btn.addEventListener('change', function() {
                const group = this.closest('.rounded-xl, [style*="border-radius"]');
                group.querySelectorAll('input[type="checkbox"]:not(.group-toggle)').forEach(cb => {
                    cb.checked = btn.checked;
                });
            });
        });
    </script>
</x-layouts.app>
