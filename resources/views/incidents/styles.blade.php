<style>
    .incident-page { width:100%; min-width:0; max-width:1440px; margin:0 auto; color:#1e293b; }
    .incident-toolbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; }
    .incident-buttons { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
    body:has(.incident-list) .content { max-width:none; }
    .incident-list { max-width:none; }
    .incident-list-toolbar { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:center; margin-bottom:20px; }
    .incident-filter-primary { display:grid; grid-template-columns:repeat(auto-fit,minmax(165px,1fr)); gap:8px; min-width:0; align-items:end; }
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
    .incident-page .input-field[readonly] { background:rgba(18,63,110,0.04); color:#475569; cursor:default; }
    .incident-profile-notice { padding:14px 18px; margin-bottom:18px; border-radius:8px; color:#713f12; background:#fef3c7; font-size:13px; }
    .incident-profile-notice a { color:#123f6e; text-decoration:underline; }
    .incident-upload-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
    .incident-upload-toolbar .incident-muted { margin-top:0; }
    .incident-upload-toolbar [hidden] { display:none; }
    .incident-upload-files { padding:0; list-style:none; margin:10px 0 0; font-size:13px; color:#475569; overflow-wrap:anywhere; }
    .incident-upload-files li { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:6px 0; border-bottom:1px solid rgba(18,63,110,0.06); }
    .incident-upload-files li > span { min-width:0; }
    .incident-upload-files .incident-icon:focus-visible { outline:2px solid #123f6e; outline-offset:2px; }
    .incident-page .btn-primary:disabled { opacity:0.5; cursor:not-allowed; }
    .incident-page textarea { resize:vertical; }
    .incident-table-wrap { width:100%; overflow-x:auto; }
    .incident-table { width:100%; min-width:1060px; table-layout:auto; border-collapse:separate; border-spacing:0; font-size:13px; }
    .incident-table th { text-align:left; padding:12px 14px; font-weight:600; color:#475569; border-bottom:2px solid rgba(18,63,110,0.06); }
    .incident-table td { padding:14px; border-bottom:1px solid rgba(18,63,110,0.04); vertical-align:middle; }
    .incident-table tbody tr { transition:background 0.15s; }
    .incident-table tbody tr:hover { background:rgba(18,63,110,0.02); }
    .incident-ticket { font-weight:600; color:#1e293b; text-decoration:none; }
    .incident-table .incident-actions { min-width:146px; }
    .incident-cards { display:grid; grid-template-columns:minmax(0,1fr); gap:12px; }
    .incident-list-card { display:grid; grid-template-columns:110px minmax(0,1.7fr) minmax(0,1.2fr) minmax(0,1.1fr) minmax(90px,0.8fr) minmax(65px,0.65fr) 32px; align-items:start; gap:18px; padding:20px; min-width:0; background:#fff; border:1px solid #d8e2ed; border-radius:8px; transition:border-color 0.15s; }
    .incident-list-card > * { min-width:0; }
    .incident-list-card:hover, .incident-list-card:focus-within { border-color:#8ca9c4; }
    .incident-card-identity .incident-ticket { font-size:13px; color:#123f6e; }
    .incident-list-card .incident-pill { max-width:100%; white-space:normal; overflow-wrap:anywhere; }
    .incident-card-date { display:flex; align-items:center; gap:6px; color:#64748b; font-size:11px; margin-top:10px; flex-wrap:wrap; }
    .incident-card-date :is(i,svg) { width:14px; height:14px; flex-shrink:0; }
    .incident-card-title { margin:0 0 8px; font-size:14px; font-weight:600; line-height:1.4; overflow-wrap:anywhere; }
    .incident-card-title a { color:#1e293b; text-decoration:none; }
    .incident-card-title a:hover, .incident-ticket:hover { text-decoration:underline; }
    .incident-card-description { font-size:13px; line-height:1.6; color:#475569; margin:0; overflow-wrap:anywhere; }
    .incident-card-fields { display:grid; gap:14px; }
    .incident-card-fields > div { min-width:0; }
    .incident-card-fields.incident-details dd { font-size:13px; line-height:1.5; }
    .incident-card-activity { display:flex; gap:6px; flex-wrap:wrap; }
    .incident-card-actions .incident-actions { flex-direction:column; flex-wrap:nowrap; align-items:center; gap:8px; }
    .incident-card-actions .incident-actions form { margin:0; }
    .incident-empty { grid-column:1 / -1; padding:48px 20px; text-align:center; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b; font-size:14px; }
    .incident-empty > :is(i,svg) { width:28px; height:28px; margin:0 auto 12px; }
    .incident-empty p { margin:0; }
    .incident-detail-summary { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; padding:20px 24px; margin-bottom:20px; border:1px solid #c9d9e7; border-left:4px solid #123f6e; border-radius:8px; background:#fff; }
    .incident-detail-summary h2 { margin:0 0 4px; font-size:18px; font-weight:600; overflow-wrap:anywhere; }
    .incident-detail-summary > div { min-width:0; }
    .incident-detail-sections { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
    .incident-detail-panel { min-width:0; border:1px solid #cbd8e5; border-radius:8px; background:#fff; }
    .incident-panel-heading { display:flex; align-items:center; gap:10px; margin:0; padding:16px 22px; border-bottom:1px solid #d8e2ed; border-radius:8px 8px 0 0; background:#f1f6fa; color:#123f6e; font-size:15px; font-weight:600; line-height:1.5; overflow-wrap:anywhere; }
    .incident-panel-heading :is(i,svg) { width:19px; height:19px; flex-shrink:0; color:#0f766e; }
    .incident-panel-body { padding:22px; }
    .incident-panel-body .incident-gallery { margin-top:0; }
    .incident-detail .incident-details > div { min-width:0; }
    .incident-muted { color:#64748b; font-size:12px; margin-top:5px; }
    .incident-actions { display:flex; gap:6px; flex-wrap:wrap; }
    .incident-icon { display:grid; place-items:center; width:32px; height:32px; flex-shrink:0; border:0; border-radius:8px; background:rgba(18,63,110,0.04); color:#123f6e; cursor:pointer; text-decoration:none; transition:background 0.15s; }
    .incident-icon:hover { background:rgba(18,63,110,0.1); }
    .incident-icon:focus-visible, .incident-ticket:focus-visible, .incident-card-title a:focus-visible { outline:2px solid #123f6e; outline-offset:3px; }
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
    .incident-pill.tone-danger { background:#dc2626; color:white; }
    .incident-pill.tone-warning { background:#facc15; color:#713f12; }
    .incident-pill.tone-success { background:#059669; color:white; }
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
    @media(max-width:1200px) {
        .incident-list-card { grid-template-columns:repeat(3,minmax(0,1fr)) 32px; }
        .incident-card-actions { grid-column:4; grid-row:1 / span 2; }
    }
    @media(max-width:800px) {
        .incident-list-card { grid-template-columns:repeat(2,minmax(0,1fr)) 32px; }
        .incident-card-identity, .incident-card-summary { grid-column:1 / span 2; }
        .incident-card-actions { grid-column:3; grid-row:1 / span 4; }
        .incident-detail-sections { grid-template-columns:1fr; }
        .incident-grid.three { grid-template-columns:1fr; }
        .incident-section { padding:18px; }
    }
    @media(max-width:640px) {
        .incident-list-toolbar { grid-template-columns:1fr; align-items:stretch; }
        .incident-filter-primary { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .incident-filter-primary > :first-child, .incident-filter-primary > :last-child { grid-column:1 / -1; }
        .incident-list-toolbar > .incident-buttons { justify-content:flex-end; }
    }
    @media(max-width:580px) {
        .incident-list-card { padding:16px; gap:18px 14px; }
        .incident-card-identity { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
        .incident-card-date { margin-top:0; }
        .incident-detail-summary { padding:18px; }
        .incident-panel-heading, .incident-panel-body { padding:16px; }
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
