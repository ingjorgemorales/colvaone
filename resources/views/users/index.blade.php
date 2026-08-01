<x-layouts.app title="Usuarios | {{ config('app.name') }}" heading="Gestion de usuarios" subheading="Administra los usuarios del sistema">
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
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Nombre</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Correo</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em" class="doc-col">Documento</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="avatar" style="width:32px;height:32px;font-size:10px;border-radius:8px">{{ $user->initials }}</div>
                                    <span style="font-weight:500;color:#1e293b">{{ $user->name }} {{ $user->last_name }}</span>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:#64748b">{{ $user->email }}</td>
                            <td style="padding:12px 16px;color:#64748b" class="doc-col">{{ $user->document_type }} {{ $user->document_number }}</td>
                            <td style="padding:12px 16px">
                                @if ($user->is_active)
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(5,150,105,0.08);color:#059669">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#059669"></span> Activo
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(100,116,139,0.08);color:#64748b">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8"></span> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:4px">
                                    <a href="{{ route('users.edit', $user) }}" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#94a3b8;transition:all 0.2s;text-decoration:none" onmouseover="this.style.background='rgba(18,63,110,0.06)';this.style.color='#123f6e'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                                        <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                    </a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Eliminar este usuario?')" style="margin:0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:none;color:#94a3b8;cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='rgba(220,38,38,0.06)';this.style.color='#dc2626'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                                            <i data-lucide="trash-2" style="width:14px;height:14px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:48px 16px;text-align:center">
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

    <div style="margin-top:16px">{{ $users->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
