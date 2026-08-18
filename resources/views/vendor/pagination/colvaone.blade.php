@if ($paginator->hasPages())
    <style>
        .cv-pag { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
        .cv-pag-info { font-size:13px; color:#94a3b8; margin:0; }
        .cv-pag-info strong { color:#475569; font-weight:600; }
        .cv-pag-nav { display:flex; align-items:center; gap:4px; flex-wrap:wrap; }
        .cv-pag-item {
            min-width:34px; height:34px; padding:0 10px;
            display:inline-flex; align-items:center; justify-content:center;
            border-radius:8px; border:1px solid rgba(18,63,110,0.08);
            background:white; color:#475569;
            font-size:13px; font-weight:500; line-height:1; text-decoration:none;
            transition:all 0.15s;
        }
        .cv-pag-item:hover { background:rgba(18,63,110,0.04); color:#123f6e; border-color:rgba(18,63,110,0.16); }
        .cv-pag-item.is-active { background:#123f6e; border-color:#123f6e; color:white; font-weight:600; cursor:default; }
        .cv-pag-item.is-active:hover { background:#123f6e; color:white; }
        .cv-pag-item.is-disabled { color:#cbd5e1; background:#f8fafc; cursor:not-allowed; }
        .cv-pag-item.is-disabled:hover { background:#f8fafc; color:#cbd5e1; border-color:rgba(18,63,110,0.08); }
        .cv-pag-dots { padding:0 4px; color:#cbd5e1; font-size:13px; user-select:none; }
        @media (max-width: 640px) {
            .cv-pag { justify-content:center; }
            .cv-pag-info { width:100%; text-align:center; order:2; }
            .cv-pag-nav { order:1; justify-content:center; }
            .cv-pag-page { display:none; }
            .cv-pag-page.is-active { display:inline-flex; }
        }
    </style>

    <nav class="cv-pag" role="navigation" aria-label="Paginacion">
        <p class="cv-pag-info">
            Mostrando <strong>{{ $paginator->firstItem() }}</strong>
            a <strong>{{ $paginator->lastItem() }}</strong>
            de <strong>{{ $paginator->total() }}</strong>
            {{ $paginator->total() === 1 ? 'resultado' : 'resultados' }}
        </p>

        <div class="cv-pag-nav">
            @if ($paginator->onFirstPage())
                <span class="cv-pag-item is-disabled" aria-disabled="true" aria-label="Anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="cv-pag-item" aria-label="Anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="cv-pag-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="cv-pag-item cv-pag-page is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="cv-pag-item cv-pag-page" aria-label="Ir a la pagina {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="cv-pag-item" aria-label="Siguiente">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="cv-pag-item is-disabled" aria-disabled="true" aria-label="Siguiente">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
