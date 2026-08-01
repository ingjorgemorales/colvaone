<x-layouts.app title="Verificar correo" heading="Verificar correo" subheading="Confirma tu direccion registrada">
    <section class="mx-auto max-w-xl">
        <div class="card-futurist p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl" style="background:rgba(5,150,105,0.06)">
                    <i data-lucide="mail-check" class="size-5" style="color:#059669"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Verifica tu correo</h2>
                    <p class="text-sm text-zinc-500">Confirma tu direccion de correo electronico.</p>
                </div>
            </div>

            <p class="text-sm leading-6 text-zinc-600">Antes de continuar, revisa tu correo y abre el enlace de verificacion. Puedes solicitar uno nuevo si no lo encuentras.</p>

            @if (session('status') === 'verification-link-sent')
                <div style="margin-top:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">Se envio un nuevo enlace de verificacion.</div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2">
                    <i data-lucide="mail-check" class="size-4"></i>
                    Reenviar verificacion
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
