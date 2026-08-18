<x-layouts.app title="Manual de usuario | {{ config('app.name') }}" heading="Manual de usuario" subheading="Guia de uso de ColvaOne">
    <div style="max-width:620px;margin:0 auto">
        <div class="card" style="padding:40px;text-align:center">
            <div style="width:56px;height:56px;border-radius:14px;display:grid;place-items:center;background:rgba(18,63,110,0.05);margin:0 auto 16px">
                <i data-lucide="file-question" style="width:26px;height:26px;color:#94a3b8"></i>
            </div>
            <h2 style="font-size:17px;font-weight:600;color:#1e293b;margin:0 0 8px">El manual aun no esta disponible</h2>
            <p style="font-size:13px;color:#64748b;line-height:1.6;margin:0 0 20px">
                Todavia no se ha cargado el documento del manual de usuario. Cuando el equipo de TI lo publique, este boton lo abrira directamente.
            </p>
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver al inicio
            </a>
        </div>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
