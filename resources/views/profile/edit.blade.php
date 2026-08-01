<x-layouts.app title="Mi perfil | {{ config('app.name') }}" heading="Mi perfil" subheading="Administra tu informacion personal">
    <div style="max-width:700px;margin:0 auto">
        <div class="card" style="padding:24px;margin-bottom:20px">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
                <div class="avatar" style="width:64px;height:64px;font-size:20px;border-radius:16px">{{ $user->initials }}</div>
                <div>
                    <h2 style="font-size:18px;font-weight:600;color:#1e293b;margin:0">{{ $user->name }} {{ $user->last_name }}</h2>
                    <p style="font-size:14px;color:#94a3b8;margin:4px 0 0">{{ $user->role_label }}</p>
                    <p style="font-size:13px;color:#64748b;margin:2px 0 0">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="user" style="width:16px;height:16px;color:#123f6e"></i>
                Informacion personal
            </h3>

            @if (session('status'))
                <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf
                @method('PUT')

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre *</label>
                        <input name="name" type="text" value="{{ old('name', $user->name) }}" required class="input-field">
                        @error('name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Apellido</label>
                        <input name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" class="input-field">
                        @error('last_name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Correo electronico *</label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="input-field">
                    @error('email') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Telefono</label>
                    <input name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input-field">
                    @error('phone') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Cargo</label>
                        <input name="position" type="text" value="{{ old('position', $user->position) }}" class="input-field">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Area</label>
                        <input name="area" type="text" value="{{ old('area', $user->area) }}" class="input-field">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Departamento</label>
                    <input name="department" type="text" value="{{ old('department', $user->department) }}" class="input-field">
                </div>

                <div style="display:flex;align-items:center;gap:12px;padding-top:8px">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" style="width:16px;height:16px"></i> Guardar cambios
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <div class="card" style="padding:24px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="key-round" style="width:16px;height:16px;color:#059669"></i>
                Cambiar contrasena
            </h3>
            <a href="{{ route('password.change.edit') }}" class="btn-primary">
                <i data-lucide="key-round" style="width:16px;height:16px"></i> Cambiar contrasena
            </a>
        </div>
    </div>

    @if(session('password_changed'))
    <div id="successModal" style="position:fixed;inset:0;z-index:9999;display:grid;place-items:center;background:rgba(18,63,110,0.3);backdrop-filter:blur(4px)" onclick="this.style.display='none'">
        <div class="card" style="padding:32px;max-width:400px;width:90%;text-align:center" onclick="event.stopPropagation()">
            <div style="width:64px;height:64px;border-radius:50%;display:grid;place-items:center;background:rgba(5,150,105,0.1);margin:0 auto 20px">
                <i data-lucide="check-circle" style="width:32px;height:32px;color:#059669"></i>
            </div>
            <h3 style="font-size:18px;font-weight:600;color:#1e293b;margin:0 0 8px">Contrasena actualizada</h3>
            <p style="font-size:14px;color:#64748b;margin:0 0 24px">Tu contrasena ha sido cambiada exitosamente.</p>
            <button onclick="document.getElementById('successModal').style.display='none'" class="btn-primary" style="width:100%;justify-content:center">
                <i data-lucide="check" style="width:16px;height:16px"></i> Entendido
            </button>
        </div>
    </div>
    @endif

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
