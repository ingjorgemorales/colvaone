<x-layouts.app title="Cambiar contrasena" heading="Cambiar contrasena" subheading="Actualizacion obligatoria de seguridad">
    <section class="mx-auto max-w-xl">
        <div class="card-futurist p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="grid size-10 place-items-center rounded-xl" style="background:rgba(18,63,110,0.06)">
                    <i data-lucide="shield-check" class="size-5" style="color:#123f6e"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-zinc-800">Define una nueva contrasena</h2>
                    <p class="text-sm text-zinc-500">Debe tener minimo 12 caracteres, mayusculas, minusculas, numeros y simbolos.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="text-sm font-medium text-zinc-600">Contrasena actual</label>
                    <input id="current_password" name="current_password" type="password" required class="input-futurist mt-1">
                    @error('current_password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="text-sm font-medium text-zinc-600">Nueva contrasena</label>
                    <input id="password" name="password" type="password" required class="input-futurist mt-1">
                    @error('password') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-sm font-medium text-zinc-600">Confirmar nueva contrasena</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input-futurist mt-1">
                </div>

                <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2">
                    <i data-lucide="shield-check" class="size-4"></i>
                    Actualizar contrasena
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
