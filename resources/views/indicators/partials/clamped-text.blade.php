@php
    $clampClass = $clampClass ?? 'clamp-box';
    // Umbral conservador: si el texto pasa esta longitud, es casi seguro que
    // no cabe en la caja recortada, asi que el boton se calcula en el
    // servidor (no depende de que JS mida el desborde en el navegador).
    $threshold = $threshold ?? 140;
@endphp
@if($text)
    <div class="data-value {{ $clampClass }}">{{ $text }}</div>
    @if(mb_strlen($text) > $threshold)
        <button type="button" class="clamp-more-btn"
            @click="openText(@js($modalTitle ?? ''), @js($text))">
            <i data-lucide="maximize-2" style="width:12px;height:12px"></i> Ver texto completo
        </button>
    @endif
@else
    <p class="data-value">-</p>
@endif
