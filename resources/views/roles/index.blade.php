<x-layouts.app title="Roles | {{ config('app.name') }}" heading="Roles y permisos" subheading="Administra los roles del sistema">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <p style="font-size:13px;color:#94a3b8;margin:0">{{ $roles->total() }} roles configurados</p>
        <a href="{{ route('roles.create') }}" class="btn-primary">
            <i data-lucide="shield-plus" style="width:16px;height:16px"></i> Nuevo rol
        </a>
    </div>

    @if (session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#dc2626;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.15)">{{ session('error') }}</div>
    @endif

    <div class="card" style="overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%;font-size:14px;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid rgba(18,63,110,0.06)">
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Rol</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Descripcion</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Permisos</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Usuarios</th>
                        <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                                        <i data-lucide="shield-check" style="width:18px;height:18px;color:#123f6e"></i>
                                    </div>
                                    <span style="font-weight:600;color:#1e293b">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:#64748b;font-size:13px">{{ $role->description ?: '-' }}</td>
                            <td style="padding:12px 16px">
                                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(18,63,110,0.06);color:#123f6e">{{ count($role->permissions ?? []) }} permisos</span>
                            </td>
                            <td style="padding:12px 16px;color:#64748b;font-size:13px">{{ $role->users_count }}</td>
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                    <a href="{{ route('roles.edit', $role) }}" title="Editar" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#94a3b8;transition:all 0.2s;text-decoration:none" onmouseover="this.style.background='rgba(18,63,110,0.06)';this.style.color='#123f6e'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                                        <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                    </a>
                                    @if ($role->slug !== 'superadmin')
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Eliminar este rol?')" style="margin:0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:none;color:#94a3b8;cursor:pointer;transition:all 0.2s" onmouseover="this.style.background='rgba(220,38,38,0.06)';this.style.color='#dc2626'" onmouseout="this.style.background='transparent';this.style.color='#94a3b8'">
                                                <i data-lucide="trash-2" style="width:14px;height:14px"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="shield" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0">No hay roles configurados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:16px">{{ $roles->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
