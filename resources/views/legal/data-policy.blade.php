@php($policy = \App\Models\DataPolicy::query()->where('is_active', true)->latest('published_at')->first())

<x-layouts.app title="Politica de tratamiento de datos" heading="Politica de tratamiento de datos" subheading="{{ $policy ? 'Version '.$policy->version : 'Documento publico inicial' }}">
    <article class="max-w-3xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Tratamiento de datos personales</h2>
        <div class="mt-3 space-y-4 text-sm leading-6 text-zinc-600">
            @if ($policy)
                <p class="font-medium text-zinc-800">Version {{ $policy->version }} publicada el {{ $policy->published_at->format('Y-m-d') }}.</p>
                <p>{!! nl2br(e($policy->content)) !!}</p>
            @else
                <p>Esta vista publica queda preparada para publicar la politica vigente, su version, fecha, responsable y canales de atencion.</p>
            @endif
        </div>
    </article>
</x-layouts.app>
