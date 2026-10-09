@extends('layouts.app')
@section('title','Companies Businesses')
@section('subtitle','Search UK registered companies by postcode area and business category')
@section('content')

{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
/* ── Card & layout ── */
.ch-search-card{background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);border:1px solid var(--border,#e2e8f0);border-radius:14px;box-shadow:0 1px 3px rgba(15,23,42,.04),0 4px 16px rgba(15,23,42,.04);overflow:hidden}
.ch-search-card .ch-card-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border,#e2e8f0);background:#fff}
.ch-search-card .ch-card-head h2{margin:0;font-size:1.1rem;font-weight:700;color:var(--text,#0f172a);display:flex;align-items:center;gap:.55rem}
.ch-search-card .ch-card-head h2::before{content:'🏢';font-size:1.15rem}
.ch-search-card .ch-card-body{padding:1.5rem}

/* ── Form grid ── */
.ch-form{display:grid;grid-template-columns:repeat(12,1fr);gap:.9rem 1rem;margin-bottom:1rem;align-items:end}
.ch-form .field{display:flex;flex-direction:column;gap:.35rem;min-width:0}
.ch-form .field.col-pc{grid-column:span 3}
.ch-form .field.col-cat{grid-column:span 3}
.ch-form .field.col-st{grid-column:span 2}
.ch-form .field.col-mr{grid-column:span 2}
.ch-form .field.col-tg{grid-column:span 2}
.ch-form label.field-label{font-size:.7rem;font-weight:700;color:var(--text-muted,#64748b);text-transform:uppercase;letter-spacing:.06em;display:flex;align-items:center;gap:.3rem;min-height:1rem;line-height:1;padding-bottom:.1rem}
.ch-form select{padding:.55rem .8rem;height:40px;border:1.5px solid var(--border,#e2e8f0);border-radius:10px;font-size:.875rem;background:#fff;width:100%;color:#0f172a;transition:all .18s ease;appearance:none;background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");background-repeat:no-repeat;background-position:right .7rem center;background-size:16px;padding-right:2.4rem;box-sizing:border-box;line-height:1.2}
.ch-form select:hover{border-color:#cbd5e1}
.ch-form select:focus{outline:none;border-color:#3b5bdb;box-shadow:0 0 0 3px rgba(59,91,219,.12)}
.ch-form .field-hint{font-size:.66rem;color:var(--text-muted,#94a3b8);margin-top:.05rem;line-height:1.35}
.ch-form .field-hint.warn{color:#f59e0b}

@media(max-width:960px){
    .ch-form .field.col-pc,.ch-form .field.col-cat{grid-column:span 6}
    .ch-form .field.col-st,.ch-form .field.col-mr,.ch-form .field.col-tg{grid-column:span 4}
}
@media(max-width:600px){
    .ch-form .field.col-pc,.ch-form .field.col-cat,
    .ch-form .field.col-st,.ch-form .field.col-mr,.ch-form .field.col-tg{grid-column:span 12}
}

/* ── Checkbox (toggle style) ── */
.ch-check-field{padding-bottom:0}
.ch-check-field .field-label{visibility:hidden!important}
.ch-toggle{position:relative;display:inline-flex;align-items:center;gap:.6rem;cursor:pointer;user-select:none;height:40px;box-sizing:border-box;padding:0 .15rem}
.ch-toggle input[type="checkbox"]{position:absolute;opacity:0;pointer-events:none}
.ch-toggle-track{width:38px;height:21px;border-radius:999px;background:#e2e8f0;transition:all .2s ease;position:relative;flex-shrink:0;box-shadow:inset 0 1px 2px rgba(0,0,0,.06)}
.ch-toggle-track::after{content:'';position:absolute;top:2px;left:2px;width:17px;height:17px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.18);transition:all .22s cubic-bezier(.4,0,.2,1)}
.ch-toggle input:checked + .ch-toggle-track{background:linear-gradient(135deg,#3b5bdb,#5b7cfa);box-shadow:inset 0 1px 2px rgba(0,0,0,.08)}
.ch-toggle input:checked + .ch-toggle-track::after{transform:translateX(17px);box-shadow:0 1px 4px rgba(59,91,219,.3)}
.ch-toggle-label{font-size:.82rem;font-weight:500;color:#334155;transition:color .15s;white-space:nowrap}
.ch-toggle-label strong{color:#0f172a;font-weight:600}
.ch-toggle input:checked ~ .ch-toggle-label strong{color:#3b5bdb}

/* ── Select2 overrides (polished) ── */
.select2-container{width:100%!important}
.select2-container .select2-selection--single{
    height:40px!important;min-height:40px!important;
    border:1.5px solid var(--border,#e2e8f0)!important;
    border-radius:10px!important;font-size:.875rem!important;
    transition:all .18s ease!important;
    background:#fff!important;
    box-sizing:border-box!important;
}
.select2-container .select2-selection--single:hover{border-color:#cbd5e1!important}
.select2-container--default.select2-container--open .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--single{
    border-color:#3b5bdb!important;
    box-shadow:0 0 0 3px rgba(59,91,219,.12)!important;
    outline:none!important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:38px!important;height:38px!important;
    padding-left:.85rem!important;color:#0f172a!important;font-weight:400!important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow{
    height:38px!important;right:7px!important;top:0!important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b{
    border-color:#64748b transparent transparent transparent!important;
    border-width:6px 5px 0 5px!important;
    margin-left:-5px!important;margin-top:-2px!important;
}
.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b{
    border-color:transparent transparent #3b5bdb transparent!important;
    border-width:0 5px 6px 5px!important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder{
    color:#94a3b8!important;
}
.select2-container--default .select2-selection--single .select2-selection__clear{
    line-height:40px!important;margin-right:.5rem!important;color:#94a3b8!important;font-size:1.1rem!important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected]{
    background:linear-gradient(135deg,#3b5bdb,#5b7cfa)!important;
    color:#fff!important;
}
.select2-container--default .select2-results__option[aria-selected="true"]{
    background:#eff6ff!important;color:#1e3a8a!important;
}
.select2-container--default .select2-results__option{
    padding:.55rem .85rem!important;font-size:.85rem!important;color:#0f172a!important;
}
.select2-container--default .select2-results__option:hover{
    background:#f8fafc!important;
}
.select2-container--default .select2-results__group{
    font-size:.68rem!important;font-weight:800!important;text-transform:uppercase!important;
    letter-spacing:.08em!important;color:#64748b!important;
    padding:.6rem .85rem .25rem!important;
    background:linear-gradient(180deg,#f8fafc,#fff)!important;
    border-top:1px solid #f1f5f9!important;
}
.select2-container--default .select2-results__group:first-child{border-top:none!important}
.select2-search--dropdown{padding:.6rem!important;background:#fff!important;border-bottom:1px solid #f1f5f9!important}
.select2-search--dropdown .select2-search__field{
    border:1.5px solid #e2e8f0!important;border-radius:8px!important;padding:.45rem .7rem!important;
    font-size:.85rem!important;outline:none!important;transition:all .15s ease!important;
}
.select2-search--dropdown .select2-search__field:focus{
    border-color:#3b5bdb!important;box-shadow:0 0 0 3px rgba(59,91,219,.1)!important;
}
.select2-dropdown{
    border:1px solid var(--border,#e2e8f0)!important;border-radius:12px!important;
    box-shadow:0 10px 40px rgba(15,23,42,.12),0 2px 6px rgba(15,23,42,.05)!important;
    overflow:hidden!important;
}
.select2-results__options{max-height:340px!important;padding:.3rem 0!important}

/* ── Action bar & buttons (online-ready polished) ── */
.ch-form-actions{
    display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;
    padding:1rem 1.25rem;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);
    border-top:1px solid var(--border,#e2e8f0);
    margin:0 -1.5rem -1.5rem;
}
.btn,.ch-btn{
    display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
    padding:.62rem 1.15rem;border-radius:10px;font-size:.85rem;font-weight:600;
    border:1.5px solid transparent;cursor:pointer;transition:all .18s cubic-bezier(.4,0,.2,1);
    line-height:1.25;text-decoration:none;white-space:nowrap;user-select:none;
    letter-spacing:.01em;
}
.btn:focus,.ch-btn:focus{outline:none;box-shadow:0 0 0 3px rgba(59,91,219,.18)}
.btn:disabled,.ch-btn:disabled{opacity:.55;cursor:not-allowed;transform:none!important;box-shadow:none!important}

.btn.primary,.ch-btn.primary{
    background:linear-gradient(135deg,#3b5bdb 0%,#5b7cfa 100%);
    color:#fff;border-color:transparent;
    box-shadow:0 1px 2px rgba(59,91,219,.15),0 2px 6px rgba(59,91,219,.18);
}
.btn.primary:hover:not(:disabled),.ch-btn.primary:hover:not(:disabled){
    background:linear-gradient(135deg,#334db8 0%,#4a69d9 100%);
    transform:translateY(-1px);
    box-shadow:0 2px 6px rgba(59,91,219,.2),0 6px 18px rgba(59,91,219,.22);
}
.btn.primary:active:not(:disabled),.ch-btn.primary:active:not(:disabled){
    transform:translateY(0);
    box-shadow:0 1px 2px rgba(59,91,219,.18);
}

.btn.success,.ch-btn.success{
    background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);
    color:#fff;border-color:transparent;
    box-shadow:0 1px 2px rgba(22,163,74,.12),0 2px 6px rgba(22,163,74,.15);
}
.btn.success:hover:not(:disabled),.ch-btn.success:hover:not(:disabled){
    background:linear-gradient(135deg,#15803d 0%,#16a34a 100%);
    transform:translateY(-1px);
    box-shadow:0 2px 6px rgba(22,163,74,.18),0 6px 18px rgba(22,163,74,.2);
}
.btn.success:active:not(:disabled),.ch-btn.success:active:not(:disabled){transform:translateY(0)}

.btn.ghost,.ch-btn.ghost,.btn:not(.primary):not(.success){
    background:#fff;color:#334155;border-color:#e2e8f0;
    box-shadow:0 1px 2px rgba(15,23,42,.04);
}
.btn.ghost:hover:not(:disabled),.ch-btn.ghost:hover:not(:disabled),.btn:not(.primary):not(.success):hover:not(:disabled){
    background:#f8fafc;border-color:#cbd5e1;color:#0f172a;transform:translateY(-1px);
    box-shadow:0 2px 6px rgba(15,23,42,.06);
}
.btn.ghost:active:not(:disabled),.ch-btn.ghost:active:not(:disabled),.btn:not(.primary):not(.success):active:not(:disabled){
    transform:translateY(0);background:#f1f5f9;
}

.btn-sm{padding:.45rem .85rem;font-size:.78rem;border-radius:8px}
.ch-search-hint{font-size:.8rem;color:var(--text-muted,#94a3b8);font-weight:400}

/* ── spinner ── */
.ch-spinner{display:none;width:18px;height:18px;border:2.5px solid #dbeafe;border-top-color:#3b5bdb;border-radius:50%;animation:ch-spin .7s linear infinite;flex-shrink:0}
@keyframes ch-spin{to{transform:rotate(360deg)}}

/* ── alerts ── */
.ch-error{background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c;padding:.85rem 1.1rem;border-radius:10px;margin-bottom:1.25rem;display:none;font-size:.875rem;display:flex;align-items:flex-start;gap:.6rem;box-shadow:0 1px 2px rgba(239,68,68,.05)}
.ch-error::before{content:'⚠';font-size:1.05rem;flex-shrink:0;margin-top:1px}
.ch-info{background:#eff6ff;border:1.5px solid #bfdbfe;color:#1d4ed8;padding:.85rem 1.1rem;border-radius:10px;margin-bottom:1.25rem;font-size:.875rem;display:flex;align-items:flex-start;gap:.6rem}
.ch-info::before{content:'ℹ';font-size:1.05rem;flex-shrink:0;font-weight:700;margin-top:1px}
.ch-warn{background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%);border:1.5px solid #fde68a;color:#92400e;padding:.9rem 1.15rem;border-radius:12px;margin-bottom:1.5rem;font-size:.875rem;display:flex;align-items:flex-start;gap:.7rem;box-shadow:0 1px 3px rgba(245,158,11,.06)}
.ch-warn::before{content:'⚠️';font-size:1.1rem;flex-shrink:0;margin-top:0}
.ch-warn strong{color:#78350f;font-weight:700}
.ch-warn code{background:#fff7ed;padding:.12rem .4rem;border-radius:4px;border:1px solid #fed7aa;color:#92400e;font-size:.8rem}
.ch-store-msg{padding:.8rem 1.1rem;border-radius:10px;font-size:.875rem;display:none;margin-bottom:1rem;font-weight:500;display:flex;align-items:center;gap:.5rem;box-shadow:0 1px 2px rgba(0,0,0,.03)}
.ch-store-msg.ok::before{content:'✓'}
.ch-store-msg.bad::before{content:'✕'}

/* ── Results meta bar ── */
.ch-meta{
    display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 1rem;flex-wrap:wrap;display:none;
    padding:.85rem 1.1rem;background:#fff;border:1.5px solid var(--border,#e2e8f0);border-radius:12px;
    box-shadow:0 1px 3px rgba(15,23,42,.03);
}
.ch-badge{
    display:inline-flex;align-items:center;gap:.35rem;
    padding:.28rem .75rem;border-radius:999px;font-size:.74rem;font-weight:700;
    color:#fff;background:linear-gradient(135deg,#3b5bdb,#5b7cfa);
    box-shadow:0 1px 3px rgba(59,91,219,.15);
}
.ch-badge::before{content:'📊'}
.ch-badge.green{background:linear-gradient(135deg,#16a34a,#22c55e);box-shadow:0 1px 3px rgba(22,163,74,.15)}
.ch-meta-ctx{font-size:.82rem;color:var(--text-muted,#64748b);font-weight:500}
.ch-meta-right{margin-left:auto;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap}
.ch-select-count{font-size:.8rem;color:#475569;font-weight:600;padding:.25rem .65rem;background:#f1f5f9;border-radius:8px}

/* ── Results table ── */
.ch-wrap{overflow-x:auto;border:1.5px solid var(--border,#e2e8f0);border-radius:12px;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.03)}
.ch-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ch-table thead{background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%)}
.ch-table th{
    padding:.7rem .95rem;text-align:left;font-size:.7rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.06em;color:#64748b;
    border-bottom:2px solid #e2e8f0;white-space:nowrap;
}
.ch-table td{padding:.65rem .95rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#334155}
.ch-table tbody tr:last-child td{border-bottom:none}
.ch-table tbody tr{transition:background .15s ease}
.ch-table tbody tr:hover td{background:#f8fafc}
.ch-table th:first-child,.ch-table td:first-child{width:42px;text-align:center}
.ch-table th:nth-child(2),.ch-table td:nth-child(2){width:48px;color:#94a3b8;font-weight:600}

/* Checkbox in table */
.ch-table input[type="checkbox"],.ch-th-check{
    width:16px;height:16px;cursor:pointer;accent-color:#3b5bdb;
    border:1.5px solid #cbd5e1;border-radius:4px;
}

.status-active{color:#16a34a;font-weight:700;font-size:.75rem;padding:.18rem .55rem;background:#dcfce7;border-radius:6px;display:inline-block}
.status-dissolved{color:#dc2626;font-size:.75rem;font-weight:600;padding:.18rem .55rem;background:#fee2e2;border-radius:6px;display:inline-block}
.status-other{color:#92400e;font-size:.75rem;font-weight:600;padding:.18rem .55rem;background:#fef3c7;border-radius:6px;display:inline-block}
.ch-num{color:var(--text-muted,#94a3b8);font-size:.72rem;font-weight:500;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.ch-placeholder{
    text-align:center;padding:4rem 2rem;color:var(--text-muted,#94a3b8);font-size:.9rem;
    background:linear-gradient(180deg,#fff,#f8fafc);border:1.5px dashed #e2e8f0;border-radius:12px;
    margin-top:.5rem;
}
.ch-placeholder strong{color:#64748b}
</style>

@if(!$apiConfigured)
<div class="ch-warn">
    <div>
        <strong>API key not configured.</strong><br>
        Add <code>COMPANIES_HOUSE_API_KEY=your_key</code> to <code>.env</code> —
        get a free key at <a href="https://developer.company-information.service.gov.uk/get-started" target="_blank" rel="noopener" style="color:#78350f;font-weight:600;text-decoration:underline">developer.company-information.service.gov.uk</a>
    </div>
</div>
@endif

<div class="ch-search-card">
    <div class="ch-card-head">
        <h2>Search Companies Businesses</h2>
        <a href="{{ route('companies.saved') }}" class="btn ghost btn-sm">
            💾 Saved Leads →
        </a>
    </div>
    <div class="ch-card-body">

        {{-- Search form --}}
        <div class="ch-form">

            {{-- Postcode area (Select2) --}}
            <div class="field col-pc">
                <label class="field-label" for="ch-location">Postcode Area</label>
                <select id="ch-location" name="location">
                    <option value=""></option>
                    @foreach($postcodeAreas as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="field-hint">e.g. B = Birmingham, M = Manchester</span>
            </div>

            {{-- Business category (Select2 with optgroups) --}}
            <div class="field col-cat">
                <label class="field-label" for="ch-category">Business Category</label>
                <select id="ch-category" name="category">
                    <option value=""></option>
                    @foreach($grouped as $groupName => $cats)
                        <optgroup label="{{ $groupName }}">
                            @foreach($cats as $cat)
                                <option value="{{ $cat['key'] }}">{{ $cat['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <span class="field-hint">Grouped by industry sector</span>
            </div>

            {{-- Company status --}}
            <div class="field col-st">
                <label class="field-label" for="ch-status">Status</label>
                <select id="ch-status">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ $s==='active'?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Max results --}}
            <div class="field col-mr">
                <label class="field-label" for="ch-size">Max Results</label>
                <select id="ch-size">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                    <option value="1000">1,000</option>
                    <option value="5000">5,000</option>
                </select>
            </div>



        </div>

        {{-- Actions bar --}}
        <div class="ch-form-actions">
            <button class="btn primary" id="ch-search-btn" type="button" {{ !$apiConfigured?'disabled':'' }}>
                🔍 Search Companies
            </button>
            <div class="ch-spinner" id="ch-spinner"></div>
            <span class="ch-search-hint" id="ch-search-hint">
                Select a postcode area (category optional), then click Search
            </span>
        </div>
    </div>
</div>

<div class="ch-error" id="ch-error"></div>

{{-- Results meta bar --}}
<div class="ch-meta" id="ch-meta">
    <span class="ch-badge" id="ch-total-badge"></span>
    <span class="ch-meta-ctx" id="ch-ctx-label"></span>
    <div class="ch-meta-right">
        <span class="ch-select-count" id="ch-select-label">0 selected</span>
        <button class="btn ghost btn-sm" id="ch-select-all-btn" type="button">☑ Select All</button>
        <button class="btn success btn-sm" id="ch-store-btn" type="button">💾 Store Selected</button>
        <button class="btn ghost btn-sm" id="ch-export-btn" type="button">⇩ Export CSV</button>
    </div>
</div>

<div class="ch-store-msg" id="ch-store-msg"></div>

{{-- Results table --}}
<div class="ch-wrap">
    <table class="ch-table" id="ch-table" style="display:none">
        <thead>
            <tr>
                <th><input type="checkbox" class="ch-th-check" id="ch-check-all" title="Toggle all"></th>
                <th>#</th>
                <th>Company Name</th>
                <th>Company No.</th>
                <th>Status</th>
                <th>Type</th>
                <th>Address</th>
                <th>Postcode</th>
                <th>Business Activity</th>
                <th>Incorporated</th>
            </tr>
        </thead>
        <tbody id="ch-tbody"></tbody>
    </table>
    <p class="ch-placeholder" id="ch-empty" style="display:none">
        ❌ No companies matched your search.<br><small>Try a broader area or different category.</small>
    </p>
    <p class="ch-placeholder" id="ch-placeholder">
        👆 Select a <strong>postcode area</strong> (category optional), then click <strong>Search Companies</strong> above.
    </p>
</div>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@push('scripts')
<script>
$(function(){
    // ── Init Select2 ─────────────────────────────────────────────
    $('#ch-location').select2({
        placeholder: 'Type postcode area or city…',
        allowClear: true,
        width: '100%',
    });

    $('#ch-category').select2({
        placeholder: 'Search or browse categories…',
        allowClear: true,
        width: '100%',
    });

    // ── DOM refs ─────────────────────────────────────────────────
    const searchBtn   = document.getElementById('ch-search-btn');
    const spinner     = document.getElementById('ch-spinner');
    const hint        = document.getElementById('ch-search-hint');
    const errorBox    = document.getElementById('ch-error');
    const metaRow     = document.getElementById('ch-meta');
    const totalBadge  = document.getElementById('ch-total-badge');
    const ctxLabel    = document.getElementById('ch-ctx-label');
    const table       = document.getElementById('ch-table');
    const tbody       = document.getElementById('ch-tbody');
    const emptyMsg    = document.getElementById('ch-empty');
    const placeholder = document.getElementById('ch-placeholder');
    const storeBtn    = document.getElementById('ch-store-btn');
    const storeMsg    = document.getElementById('ch-store-msg');
    const exportBtn   = document.getElementById('ch-export-btn');
    const selectAllBtn= document.getElementById('ch-select-all-btn');
    const checkAll    = document.getElementById('ch-check-all');
    const selectLabel = document.getElementById('ch-select-label');
    const csrf        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let lastResults = [];

    function esc(str){
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str ?? ''));
        return d.innerHTML;
    }

    function setLoading(on){
        searchBtn.disabled = on;
        spinner.style.display = on ? 'block' : 'none';
        hint.style.display    = on ? 'none'  : 'inline';
        searchBtn.textContent = on ? 'Searching…' : '🔍 Search Companies';
    }

    function showError(msg){
        errorBox.innerHTML = '<span>' + msg + '</span>';
        errorBox.style.display = 'flex';
    }
    function hideError(){ errorBox.style.display = 'none'; }

    function statusClass(s){
        if (s === 'active')    return 'status-active';
        if (s === 'dissolved') return 'status-dissolved';
        return 'status-other';
    }

    function updateSelectLabel(){
        const n = tbody.querySelectorAll('.ch-row-check:checked').length;
        selectLabel.textContent = n + ' selected';
    }

    function buildRow(item, index){
        const tr  = document.createElement('tr');
        const addr = [item.address_line_1, item.address_line_2, item.locality].filter(Boolean).join(', ');
        tr.innerHTML = `
            <td><input type="checkbox" class="ch-row-check" data-idx="${index}" checked></td>
            <td class="ch-num">${index + 1}</td>
            <td><strong>${esc(item.company_name)}</strong></td>
            <td class="ch-num">${esc(item.company_number)}</td>
            <td><span class="${statusClass(item.company_status)}">${esc(item.company_status ?? '—')}</span></td>
            <td class="ch-num">${esc(item.company_type ?? '—')}</td>
            <td style="max-width:200px">${esc(addr || '—')}</td>
            <td class="ch-num">${esc(item.postal_code ?? '—')}</td>
            <td style="max-width:180px;font-size:.75rem">${esc(item.sic_names ?? item.sic_codes ?? '—')}</td>
            <td class="ch-num">${esc(item.date_of_creation ?? '—')}</td>
        `;
        return tr;
    }

    function renderTable(data){
        tbody.innerHTML = '';
        data.forEach((item, i) => tbody.appendChild(buildRow(item, i)));
        table.style.display    = data.length ? 'table' : 'none';
        emptyMsg.style.display = data.length ? 'none'  : 'block';
        placeholder.style.display = 'none';
        metaRow.style.display  = 'flex';
        selectAllBtn.innerHTML = '☑ Select All';
        checkAll.checked = true;
        updateSelectLabel();
        tbody.addEventListener('change', updateSelectLabel);
    }

    // ── Search ────────────────────────────────────────────────────
    searchBtn.addEventListener('click', async function(){
        const location = $('#ch-location').val();
        const category = $('#ch-category').val();
        const status   = document.getElementById('ch-status').value;
        const size     = document.getElementById('ch-size').value;
        const exact    = 0;

        if (!location){ showError('Please select a postcode area.'); return; }

        hideError();
        storeMsg.style.display = 'none';
        setLoading(true);
        metaRow.style.display = 'none';
        table.style.display   = 'none';
        emptyMsg.style.display = 'none';
        placeholder.style.display = 'none';
        lastResults = [];

        try {
            const url = `{{ route('companies.search') }}?location=${encodeURIComponent(location)}&category=${encodeURIComponent(category)}&status=${status}&size=${size}&exact_postcode=${exact}`;
            const res  = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();

            if (!res.ok || json.error){
                showError(json.error ?? 'Request failed. Please try again.');
                placeholder.style.display = 'block';
                return;
            }

            lastResults = json.data ?? [];
            totalBadge.textContent = json.total + ' results';
            ctxLabel.textContent   = json.location + ' · ' + json.category + (json.exact_filter ? ' (exact postcode)' : '');

            if (json.total === 0){
                if (json.exact_filter) {
                    showError('API found ' + json.total_api + ' companies but none matched the exact postcode "' + json.location + '". Uncheck "Exact postcode match only" to see all results.');
                } else {
                    emptyMsg.style.display = 'block';
                }
                metaRow.style.display     = 'none';
                table.style.display       = 'none';
                placeholder.style.display = 'none';
            } else {
                renderTable(lastResults);
            }

        } catch(err){
            showError('Network error: ' + err.message);
            placeholder.style.display = 'block';
        } finally {
            setLoading(false);
        }
    });

    // ── Header checkbox ───────────────────────────────────────────
    checkAll.addEventListener('change', function(){
        tbody.querySelectorAll('.ch-row-check').forEach(b => b.checked = this.checked);
        updateSelectLabel();
    });

    // ── Select All button ─────────────────────────────────────────
    selectAllBtn.addEventListener('click', function(){
        const boxes     = tbody.querySelectorAll('.ch-row-check');
        const allChecked = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        checkAll.checked = !allChecked;
        selectAllBtn.innerHTML = allChecked ? '☑ Select All' : '☐ Deselect All';
        updateSelectLabel();
    });

    // ── Store selected ────────────────────────────────────────────
    storeBtn.addEventListener('click', async function(){
        const checked  = [...tbody.querySelectorAll('.ch-row-check:checked')];
        const selected = checked.map(b => lastResults[parseInt(b.dataset.idx)]).filter(Boolean);

        if (!selected.length){
            storeMsg.textContent = '⚠ Select at least one company first.';
            storeMsg.className = 'ch-store-msg bad';
            storeMsg.style.cssText = 'display:flex;background:#fffbeb;border:1.5px solid #fde68a;color:#92400e';
            return;
        }

        storeBtn.disabled = true;
        storeMsg.style.display = 'none';
        storeMsg.className = 'ch-store-msg';

        try {
            const res  = await fetch('{{ route('companies.store') }}', {
                method : 'POST',
                headers: {
                    'Content-Type'  : 'application/json',
                    'Accept'        : 'application/json',
                    'X-CSRF-TOKEN'  : csrf,
                },
                body: JSON.stringify({ data: selected }),
            });
            const json = await res.json();
            const ok   = res.ok && json.status === 'success';
            storeMsg.textContent = json.message ?? (ok ? 'Saved successfully!' : 'Error saving.');
            storeMsg.className = ok ? 'ch-store-msg ok' : 'ch-store-msg bad';
            storeMsg.style.cssText = ok
                ? 'display:flex;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#15803d'
                : 'display:flex;background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c';
        } catch(err){
            storeMsg.textContent = '✕ Network error: ' + err.message;
            storeMsg.className = 'ch-store-msg bad';
            storeMsg.style.cssText = 'display:flex;background:#fef2f2;border:1.5px solid #fecaca;color:#b91c1c';
        } finally {
            storeBtn.disabled = false;
        }
    });

    // ── CSV Export ────────────────────────────────────────────────
    exportBtn.addEventListener('click', function(){
        if (!lastResults.length) return;
        const location = $('#ch-location option:selected').text();
        const rows = [['#','Company Name','Company No.','Status','Type','Address','Postcode','Business Activity','Incorporated']];
        lastResults.forEach((item, i) => {
            const addr = [item.address_line_1, item.address_line_2, item.locality].filter(Boolean).join(', ');
            rows.push([i+1, item.company_name, item.company_number, item.company_status,
                       item.company_type, addr, item.postal_code,
                       item.sic_names ?? item.sic_codes, item.date_of_creation]);
        });
        const csv  = rows.map(r => r.map(v => `"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
        const a    = document.createElement('a');
        a.href     = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
        a.download = `companies-${location}.csv`.replace(/[\s–]+/g, '-').toLowerCase();
        a.click();
    });
});
</script>
@endpush
@endsection
