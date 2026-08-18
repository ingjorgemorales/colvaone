@if ($paginator->hasPages())
    <style>
        .cv-pag-simple { display:flex; align-items:center; justify-content:center; gap:8px; }
        .cv-pag-simple .cv-pag-item {
            height:34px; padding:0 14px;
            display:inline-flex; align-items:center; justify-content:center; gap:6px;
            border-radius:8px; border:1px solid rgba(18,63,110,0.08);
            background:white; color:#475569;
            font-size:13px; font-weight:500; line-height:1; text-decoration:none;
            transition:all 0.15s;
        }
        .cv-pag-simple .cv-pag-item:hover { background:rgba(18,63,110,0.04); color:#123f6e; border-color:rgba(18,63,110,0.16); }
        .cv-pag-simple .is-disabled { color:#cbd5e1; background:#f8fafc; cursor:not-allowed; }
        .cv-pag-simple .is-disabled:hover { background:#f8fafc; color:#cbd5e1; border-color:rgba(18,63,110,0.08); }
    </style>

    <nav class="cv-pag-simple" role="navigation" aria-label="Paginacion">
        @if ($paginator->onFirstPage())
            <span class="cv-pag-item is-disabled" aria-disabled="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Anterior
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="cv-pag-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Anterior
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="cv-pag-item">
                Siguiente
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        @else
            <span class="cv-pag-item is-disabled" aria-disabled="true">
                Siguiente
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </span>
        @endif
    </nav>
@endif
