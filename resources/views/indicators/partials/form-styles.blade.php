<style>
    .sheet-block { background: rgba(18,63,110,0.025); border: 1px solid rgba(18,63,110,0.06); border-radius: 14px; padding: 20px; }
    .sheet-title { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #123f6e; text-align: center; margin: 0 0 18px; }

    .sheet-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 900px) { .sheet-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 620px) { .sheet-grid { grid-template-columns: 1fr; } }

    .field { display: flex; flex-direction: column; min-width: 0; }
    .field-label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .field-label .req { color: #dc2626; font-weight: 700; }
    .field-hint { font-size: 11px; color: #94a3b8; margin: 4px 0 0; }
    .field-error { font-size: 12px; color: #dc2626; margin: 4px 0 0; }
    .error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }
    .field textarea.input-field { resize: vertical; min-height: 42px; }

    .range-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    @media (max-width: 700px) { .range-grid { grid-template-columns: 1fr; } }
    .range-card { border-radius: 10px; padding: 14px 12px; text-align: center; border: 1px solid transparent; }
    .range-name { font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin: 0 0 6px; }
    .range-value { font-size: 15px; font-weight: 700; margin: 0; }
    .range-bad { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.18); }
    .range-bad .range-name, .range-bad .range-value { color: #dc2626; }
    .range-mid { background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.22); }
    .range-mid .range-name, .range-mid .range-value { color: #b45309; }
    .range-good { background: rgba(5,150,105,0.08); border-color: rgba(5,150,105,0.18); }
    .range-good .range-name, .range-good .range-value { color: #059669; }

    .range-inputs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 16px; }
    @media (max-width: 620px) { .range-inputs { grid-template-columns: 1fr; } }
    .range-hint {
        grid-column: 1 / -1; display: flex; align-items: flex-start; gap: 7px;
        font-size: 11px; color: #64748b; line-height: 1.55; margin: 0;
        background: rgba(18,63,110,0.03); border-radius: 8px; padding: 9px 11px;
    }
    .range-hint i { flex-shrink: 0; margin-top: 2px; color: #123f6e; }

    .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; flex-wrap: wrap; }
</style>
