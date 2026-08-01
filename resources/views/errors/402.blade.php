<x-layouts.app title="Acceso no autorizado" heading="Acceso restringido" subheading="No tienes permisos para esta accion">
    <section class="mx-auto max-w-2xl rounded-lg border border-zinc-200 bg-white p-6 text-center shadow-sm">
        <div class="mx-auto"><img src="{{ asset('images/logo-login.png') }}" alt="Logo" class="mx-auto max-h-14 w-auto"></div>
        <h2 class="mt-5 text-2xl font-semibold">Acceso no autorizado</h2>
        <p class="mt-3 text-sm leading-6 text-zinc-600">
            Tu usuario no tiene el permiso requerido para abrir este recurso. Si necesitas acceso,
            solicita la autorizacion al administrador del sistema.
        </p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center gap-2 rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">
            <i data-lucide="arrow-left" class="size-4"></i>
            Volver al dashboard
        </a>
    </section>
</x-layouts.app>
