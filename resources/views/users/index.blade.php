<x-layouts.app title="Usuarios | {{ config('app.name') }}" heading="Gestion de usuarios" subheading="Administra los usuarios del sistema">
    <div class="card" style="padding:20px;margin-bottom:20px">
        <form method="GET" action="{{ route('users.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:200px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, correo, cedula..." class="input-field">
            </div>
            <div style="min-width:160px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Rol</label>
                <select name="role" class="input-field">
                    <option value="">Todos</option>
                    @foreach(\App\Http\Controllers\UserController::roles() as $key => $label)
                        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Estado</label>
                <select name="status" class="input-field">
                    <option value="">Todos</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn-primary" style="padding:10px 16px">
                    <i data-lucide="search" style="width:16px;height:16px"></i> Filtrar
                </button>
                <a href="{{ route('users.index') }}" class="btn-secondary" style="padding:10px 16px">
                    <i data-lucide="x" style="width:16px;height:16px"></i>
                </a>
            </div>
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <p style="font-size:13px;color:#94a3b8;margin:0">{{ $users->total() }} usuarios registrados</p>
        <a href="{{ route('users.create') }}" class="btn-primary">
            <i data-lucide="user-plus" style="width:16px;height:16px"></i> Nuevo usuario
        </a>
    </div>

    @if (session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif

    <div class="card" style="overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%;font-size:14px;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid rgba(18,63,110,0.06)">
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Usuario</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Documento</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Cargo</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Rol</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="avatar" style="width:36px;height:36px;font-size:11px;border-radius:10px">{{ $user->initials }}</div>
                                    <div>
                                        <p style="font-weight:500;color:#1e293b;margin:0;font-size:14px">{{ $user->name }} {{ $user->last_name }}</p>
                                        <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:#64748b;font-size:13px">{{ $user->document_type }} {{ $user->document_number }}</td>
                            <td style="padding:12px 16px;color:#64748b;font-size:13px">{{ $user->position ?: '-' }}</td>
                            <td style="padding:12px 16px">
                                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(18,63,110,0.06);color:#123f6e">{{ $user->role_label }}</span>
                            </td>
                            <td style="padding:12px 16px">
                                @if ($user->is_active)
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(5,150,105,0.08);color:#059669">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#059669"></span> Activo
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(220,38,38,0.08);color:#dc2626">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#dc2626"></span> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                    <a href="{{ route('users.edit', $user) }}" title="Editar" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#94a3b8;transition:all 0.2s;text-decoration:none" onmouseover="this.style.background='rgba(18,63,110,0.06)';this.style.color='#123f6e'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                                        <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                    </a>
                                    <form method="POST" action="{{ route('users.toggle', $user) }}" style="margin:0">
                                        @csrf
                                        <button type="submit" title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:none;color:{{ $user->is_active ? '#f59e0b' : '#059669' }};cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='rgba(245,158,11,0.06)'" onmouseout="this.style.background='transparent'">
                                            <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" style="width:14px;height:14px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="users" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0">No hay usuarios registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:16px">{{ $users->withQueryString()->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
