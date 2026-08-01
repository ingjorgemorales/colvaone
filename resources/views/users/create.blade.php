<x-layouts.app title="Crear usuario | {{ config('app.name') }}" heading="Crear usuario" subheading="Registra un nuevo usuario en el sistema">
    <div style="max-width:700px;margin:0 auto">
        <div class="card" style="padding:24px">
            <form method="POST" action="{{ route('users.store') }}" style="display:flex;flex-direction:column;gap:16px">
                @csrf

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombres *</label>
                        <input name="name" type="text" value="{{ old('name') }}" required class="input-field" placeholder="Nombres">
                        @error('name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Apellidos *</label>
                        <input name="last_name" type="text" value="{{ old('last_name') }}" required class="input-field" placeholder="Apellidos">
                        @error('last_name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Tipo de documento *</label>
                        <select name="document_type" class="input-field" required>
                            <option value="">Seleccionar</option>
                            <option value="CC" {{ old('document_type') === 'CC' ? 'selected' : '' }}>Cedula de ciudadania</option>
                            <option value="TI" {{ old('document_type') === 'TI' ? 'selected' : '' }}>Tarjeta de identidad</option>
                            <option value="CE" {{ old('document_type') === 'CE' ? 'selected' : '' }}>Cedula de extranjeria</option>
                            <option value="NIT" {{ old('document_type') === 'NIT' ? 'selected' : '' }}>NIT</option>
                        </select>
                        @error('document_type') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Numero de documento *</label>
                        <input name="document_number" type="text" value="{{ old('document_number') }}" required class="input-field" placeholder="Cedula">
                        @error('document_number') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Correo electronico *</label>
                    <input name="email" type="email" value="{{ old('email') }}" required class="input-field" placeholder="correo@ejemplo.com">
                    @error('email') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Telefono *</label>
                        <input name="phone" type="text" value="{{ old('phone') }}" required class="input-field" placeholder="Telefono">
                        @error('phone') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Cargo *</label>
                        <input name="position" type="text" value="{{ old('position') }}" required class="input-field" placeholder="Cargo">
                        @error('position') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Area</label>
                        <input name="area" type="text" value="{{ old('area') }}" class="input-field" placeholder="Area">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Departamento</label>
                        <input name="department" type="text" value="{{ old('department') }}" class="input-field" placeholder="Departamento">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Rol *</label>
                    <select name="role" class="input-field" required>
                        <option value="">Seleccionar rol</option>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div style="padding:12px 16px;border-radius:10px;font-size:13px;color:#123f6e;background:rgba(18,63,110,0.04);border:1px solid rgba(18,63,110,0.08)">
                    Se enviara un correo al usuario con una contrasena temporal. Debera cambiarla al iniciar sesion.
                </div>

                <div style="display:flex;align-items:center;gap:12px;padding-top:8px">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="user-plus" style="width:16px;height:16px"></i> Crear usuario
                    </button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
