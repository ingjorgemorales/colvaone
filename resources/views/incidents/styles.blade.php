<style>
    .incident-page { width:100%; min-width:0; max-width:1440px; margin:0 auto; color:#1e293b; }
    .incident-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; }
    .incident-buttons { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
    body:has(.incident-list) .content { max-width:none; }
    .incident-list { max-width:none; }
    .incident-list-toolbar { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:center; margin-bottom:20px; }
    .incident-filter-primary, .incident-filter-secondary { display:grid; grid-template-columns:repeat(auto-fit,minmax(165px,1fr)); gap:8px; min-width:0; align-items:end; }
    .incident-filter-secondary { grid-column:1 / -1; }
    .incident-filter-field { width:100%; min-width:0; padding:9px 14px; border-radius:10px; border:1px solid rgba(18,63,110,0.12); background:white; font-size:13px; color:#475569; outline:none; }
    select.incident-filter-field { cursor:pointer; }
    input[name="search"].incident-filter-field { color:#000; }
    .incident-filter-field:focus-visible { outline:2px solid #123f6e; outline-offset:2px; }
    .incident-list .incident-search-button { padding:9px 16px; font-size:13px; justify-content:center; }
    .incident-list .incident-clear-button { color:#dc2626; }
    .incident-count { color:#94a3b8; font-size:13px; margin-bottom:12px; }
    .incident-sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px !important; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    .incident-page label, .incident-field-label { display:block; font-size:13px; font-weight:500; color:#475569; margin-bottom:6px; }
    .incident-page .input-field { width:100%; min-width:0; box-sizing:border-box; }
    .incident-page textarea { resize:vertical; }
    .incident-table-wrap { width:100%; overflow-x:auto; }
    .incident-table { width:100%; min-width:1060px; table-layout:auto; border-collapse:separate; border-spacing:0; font-size:13px; }
    .incident-table th { text-align:left; padding:12px 14px; font-weight:600; color:#475569; border-bottom:2px solid rgba(18,63,110,0.06); }
    .incident-table td { padding:14px; border-bottom:1px solid rgba(18,63,110,0.04); vertical-align:middle; }
    .incident-table tbody tr { transition:background 0.15s; }
    .incident-table tbody tr:hover { background:rgba(18,63,110,0.02); }
    .incident-ticket { font-weight:600; color:#1e293b; text-decoration:none; }
    .incident-table .incident-actions { min-width:146px; }
    .incident-summary { min-width:190px; max-width:290px; overflow-wrap:anywhere; }
    .incident-muted { color:#64748b; font-size:12px; margin-top:5px; }
    .incident-actions { display:flex; gap:6px; flex-wrap:wrap; }
    .incident-icon { display:grid; place-items:center; width:32px; height:32px; flex-shrink:0; border:0; border-radius:8px; background:rgba(18,63,110,0.04); color:#123f6e; cursor:pointer; text-decoration:none; transition:background 0.15s; }
    .incident-icon:hover { background:rgba(18,63,110,0.1); }
    .incident-icon :is(i,svg) { width:15px; height:15px; }
    .incident-icon.edit { color:#6366f1; }
    .incident-icon.disable { color:#ef4444; background:rgba(239,68,68,0.04); }
    .incident-icon.disable:hover { background:rgba(239,68,68,0.1); }
    .incident-icon.archive { color:#64748b; background:rgba(100,116,139,0.06); }
    .incident-icon.archive:hover { background:rgba(100,116,139,0.14); }
    .incident-pill { display:inline-flex; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:500; background:rgba(18,63,110,0.06); color:#123f6e; white-space:nowrap; }
    .incident-pill.reportado, .incident-pill.en_atencion, .incident-pill.resuelto { padding:4px 10px; font-weight:600; color:white; }
    .incident-pill.reportado { background:#f59e0b; }
    .incident-pill.en_atencion { background:#6366f1; }
    .incident-pill.resuelto { background:#059669; }
    .incident-section { padding:28px; border-bottom:1px solid rgba(18,63,110,0.06); }
    .incident-section:last-child { border-bottom:0; }
    .incident-section h2 { font-size:16px; font-weight:600; margin:0 0 20px; }
    .incident-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .incident-grid.three { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .incident-full { grid-column:1 / -1; }
    .incident-errors { padding:16px 20px; margin-bottom:18px; background:#fff0f0; color:#b42318; border:1px solid #fecaca; border-radius:8px; font-size:13px; }
    .incident-errors ul { margin:0; padding-left:18px; }
    .incident-required { color:#dc2626; }
    .incident-details { margin:0; }
    .incident-details dt { font-size:12px; color:#64748b; margin-bottom:6px; }
    .incident-details dd { margin:0; font-size:14px; overflow-wrap:anywhere; }
    .incident-description { white-space:pre-wrap; overflow-wrap:anywhere; line-height:1.65; margin:0; font-size:14px; }
    .incident-evidence { display:flex; flex-wrap:wrap; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #e2e8f0; overflow-wrap:anywhere; }
    .incident-evidence label { margin:0; display:flex; align-items:center; gap:8px; }
    .incident-gallery { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:18px; margin-top:10px; }
    .incident-attachment { margin:0; min-width:0; }
    .incident-photo-link, .incident-document-link { display:flex; align-items:center; justify-content:center; width:100%; height:144px; background:rgba(18,63,110,0.04); border-radius:8px; overflow:hidden; color:#123f6e; }
    .incident-photo-link { cursor:zoom-in; }
    .incident-photo-link img { display:block; width:100%; height:100%; object-fit:contain; }
    .incident-filename { display:block; color:#123f6e; font-size:13px; overflow-wrap:anywhere; margin-top:8px; }
    .incident-attachment-tools { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:6px; }
    .incident-attachment .incident-remove { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .incident-viewer { position:fixed; inset:0; margin:auto; width:min(1000px,calc(100vw - 32px)); max-height:calc(100dvh - 32px); padding:20px; border:1px solid rgba(18,63,110,0.12); border-radius:16px; background:white; color:#1e293b; overflow-y:auto; }
    .incident-viewer::backdrop { background:rgba(15,23,42,0.65); }
    .incident-viewer-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; }
    .incident-viewer-title { font-size:14px; font-weight:600; overflow-wrap:anywhere; min-width:0; }
    .incident-viewer-toolbar .incident-actions { flex-shrink:0; }
    .incident-viewer-image { display:block; width:100%; height:65dvh; object-fit:contain; background:#f8fafc; }
    .incident-icon:disabled { opacity:0.4; cursor:default; }
    .incident-tabs { display:flex; flex-wrap:wrap; gap:8px; margin:20px 0; }
    .incident-tabs .selected { background:#123f6e; color:white; }
    .incident-option-row { display:grid; grid-template-columns:minmax(160px,1fr) 90px auto; gap:12px; align-items:end; }
    @media(max-width:800px) {
        .incident-grid.three { grid-template-columns:1fr; }
        .incident-section { padding:18px; }
    }
    @media(max-width:640px) {
        .incident-list-toolbar { grid-template-columns:1fr; align-items:stretch; }
        .incident-filter-primary, .incident-filter-secondary { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .incident-filter-primary > :first-child, .incident-filter-primary > :last-child { grid-column:1 / -1; }
        .incident-list-toolbar > .incident-buttons { justify-content:flex-end; }
    }
    @media(max-width:580px) {
        body:has(.incident-page) .header { flex-wrap:wrap; height:auto; padding:12px 16px; gap:8px; }
        body:has(.incident-page) .header > .hamburger { margin-right:auto; }
        body:has(.incident-page) .header-title { order:5; flex:1 0 100%; }
        body:has(.incident-page) .header-title h1 { white-space:normal; overflow-wrap:anywhere; }
        .incident-grid { grid-template-columns:1fr; }
        .incident-option-row { grid-template-columns:minmax(0,1fr) 70px; }
        .incident-option-row button { grid-column:1 / -1; }
        .incident-toolbar > .incident-buttons { width:100%; }
    }
</style>
