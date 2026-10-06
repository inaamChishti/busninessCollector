@extends('layouts.app')
@section('title','Wikidata — Get Emails')
@section('subtitle','Crawl stored business websites and extract email addresses')
@section('content')

<style>
/* ── shared table styles ── */
.wd-table-wrap{overflow-x:auto}
.wd-table{width:100%;border-collapse:collapse;font-size:.875rem}
.wd-table th{background:var(--surface,#f8f9fa);padding:.6rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted,#888);border-bottom:2px solid var(--border,#e5e7eb);white-space:nowrap}
.wd-table td{padding:.6rem 1rem;border-bottom:1px solid var(--border,#f0f0f0);vertical-align:middle}
.wd-table tr:hover td{background:var(--surface,#fafafa)}
.wd-table td a{color:var(--primary,#3b5bdb);text-decoration:none;font-size:.8rem;word-break:break-all}
.wd-table td a:hover{text-decoration:underline}
.wd-num{color:var(--text-muted,#aaa);font-size:.75rem}
.wd-badge{background:var(--primary,#3b5bdb);color:#fff;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:600}
.wd-badge.green{background:#16a34a}
/* ── inline spinner ── */
.spin{display:inline-block;width:14px;height:14px;border:2px solid #ddd;border-top-color:var(--primary,#3b5bdb);border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
/* ── email chips ── */
.email-chips{display:flex;flex-wrap:wrap;gap:.3rem}
.email-chip{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:.15rem .5rem;border-radius:4px;font-size:.75rem}
/* ── filter bar ── */
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem}
.filter-bar .field{display:flex;flex-direction:column;gap:.25rem}
.filter-bar label{font-size:.75rem;font-weight:600;color:var(--text-muted,#666);text-transform:uppercase;letter-spacing:.04em}
.filter-bar select,.filter-bar input{padding:.45rem .7rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff}
/* ── result modal ── */
#em-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center}
#em-modal-bg.open{display:flex}
#em-modal{background:#fff;border-radius:10px;max-width:600px;width:95%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25)}
#em-modal-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border,#eee)}
#em-modal-head h3{margin:0;font-size:1rem}
#em-modal-close{background:none;border:none;font-size:1.3rem;cursor:pointer;color:#888;line-height:1}
#em-modal-body{padding:1.25rem}
.em-email-row{display:flex;align-items:center;gap:.5rem;padding:.4rem 0;border-bottom:1px solid #f3f4f6}
.em-email-row:last-child{border-bottom:none}
.em-email-row input[type=checkbox]{width:15px;height:15px;cursor:pointer}
.em-email-row label{flex:1;font-size:.875rem;word-break:break-all;cursor:pointer}
.em-source{font-size:.7rem;color:var(--text-muted,#999)}
</style>

{{-- Filter bar --}}
<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="{{ route('wikidata.emails') }}">
            <div class="filter-bar">
                <div class="field">
                    <label>City</label>
                    <select name="city">
                        <option value="">All cities</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" @selected(request('city')===$c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Search name</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Business name…">
                </div>
                <div class="field" style="justify-content:flex-end">
                    <button class="btn primary" type="submit">Filter</button>
                    <a class="btn" href="{{ route('wikidata.emails') }}" style="margin-top:.3rem">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Businesses table --}}
<div class="panel">
    <div class="panel-head">
        <h2>Stored Businesses <span class="wd-badge">{{ $businesses->total() }}</span></h2>
        <a href="{{ route('wikidata.index') }}" class="btn">← Back to Search</a>
    </div>
    <div style="padding:1.25rem">

        @if($businesses->isEmpty())
            <p style="text-align:center;padding:3rem;color:var(--text-muted,#888)">
                No businesses stored yet. <a href="{{ route('wikidata.index') }}">Fetch and store some first.</a>
            </p>
        @else
        <div class="wd-table-wrap">
            <table class="wd-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Business Name</th>
                        <th>City / Category</th>
                        <th>Website</th>
                        <th>Stored Emails</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($businesses as $biz)
                    <tr id="biz-row-{{ $biz->id }}">
                        <td class="wd-num">{{ $loop->iteration + ($businesses->currentPage()-1)*$businesses->perPage() }}</td>
                        <td><strong>{{ $biz->name }}</strong></td>
                        <td>
                            <span style="font-size:.8rem">{{ $biz->city }}</span>
                            @if($biz->category)<br><span class="wd-num">{{ $biz->category }}</span>@endif
                        </td>
                        <td>
                            @if($biz->website)
                                <a href="{{ $biz->website }}" target="_blank" rel="noopener">{{ Str::limit($biz->website, 40) }}</a>
                            @else
                                <span class="wd-num">—</span>
                            @endif
                        </td>
                        <td id="emails-cell-{{ $biz->id }}">
                            @if($biz->emails_count > 0)
                                <div class="email-chips">
                                    @foreach($biz->emails as $em)
                                        <span class="email-chip">{{ $em->email }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="wd-num">—</span>
                            @endif
                        </td>
                        <td>
                            @if($biz->website)
                                <button class="btn primary"
                                    onclick="getEmails({{ $biz->id }}, '{{ addslashes($biz->website) }}', '{{ addslashes($biz->name) }}')"
                                    id="get-btn-{{ $biz->id }}">
                                    Get Emails
                                </button>
                            @else
                                <span class="wd-num">No website</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($businesses->hasPages())
        <div style="margin-top:1rem">{{ $businesses->links() }}</div>
        @endif
        @endif

    </div>
</div>

{{-- Email result modal --}}
<div id="em-modal-bg">
    <div id="em-modal">
        <div id="em-modal-head">
            <h3 id="em-modal-title">Emails Found</h3>
            <button id="em-modal-close" type="button">✕</button>
        </div>
        <div id="em-modal-body">
            <p id="em-modal-status" style="color:var(--text-muted,#888);font-size:.875rem"></p>
            <div id="em-modal-list"></div>
            <div id="em-modal-actions" style="display:none;margin-top:1rem;display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                <button class="btn success" id="em-store-btn" type="button">💾 Store Selected</button>
                <button class="btn" id="em-select-all-btn" type="button">Select All</button>
                <span id="em-store-result" style="font-size:.8rem"></span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
    const modalBg     = document.getElementById('em-modal-bg');
    const modalTitle  = document.getElementById('em-modal-title');
    const modalStatus = document.getElementById('em-modal-status');
    const modalList   = document.getElementById('em-modal-list');
    const modalActions= document.getElementById('em-modal-actions');
    const storeBtn    = document.getElementById('em-store-btn');
    const selectAllBtn= document.getElementById('em-select-all-btn');
    const storeResult = document.getElementById('em-store-result');
    const closeBtn    = document.getElementById('em-modal-close');
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;

    let currentBusinessId = null;
    let scrapedEmails     = [];

    // Close modal
    closeBtn.addEventListener('click', () => { modalBg.classList.remove('open'); });
    modalBg.addEventListener('click', (e) => { if(e.target === modalBg) modalBg.classList.remove('open'); });

    // Select all toggle
    selectAllBtn.addEventListener('click', () => {
        const boxes = modalList.querySelectorAll('input[type=checkbox]');
        const allChecked = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        selectAllBtn.textContent = allChecked ? 'Select All' : 'Deselect All';
    });

    window.getEmails = async function(businessId, website, name) {
        currentBusinessId = businessId;
        scrapedEmails     = [];
        storeResult.textContent = '';
        modalTitle.textContent  = name;
        modalStatus.textContent = 'Crawling website pages…';
        modalList.innerHTML     = '<div style="text-align:center;padding:1.5rem"><span class="spin"></span></div>';
        modalActions.style.display = 'none';
        modalBg.classList.add('open');

        // Disable the trigger button
        const btn = document.getElementById('get-btn-' + businessId);
        if (btn) { btn.disabled = true; btn.textContent = 'Crawling…'; }

        try {
            const res  = await fetch('{{ route('wikidata.emails.scrape') }}?website=' + encodeURIComponent(website) + '&business_id=' + businessId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();

            if (!res.ok || json.error) {
                modalStatus.textContent = json.error ?? 'Failed to crawl website.';
                modalList.innerHTML = '';
                return;
            }

            scrapedEmails = json.data ?? [];
            const pages   = json.pages_crawled ?? 0;
            modalStatus.textContent = `${scrapedEmails.length} email(s) found across ${pages} page(s).`;

            if (!scrapedEmails.length) {
                modalList.innerHTML = '<p style="text-align:center;color:var(--text-muted,#999);padding:1rem">No emails found on this website.</p>';
                modalActions.style.display = 'none';
                return;
            }

            // Render checkboxes
            modalList.innerHTML = '';
            scrapedEmails.forEach((item, i) => {
                const row = document.createElement('div');
                row.className = 'em-email-row';
                row.innerHTML = `
                    <input type="checkbox" id="em-cb-${i}" checked>
                    <label for="em-cb-${i}">
                        ${escHtml(item.email)}
                        <br><span class="em-source">${escHtml(item.source_url ?? '')}</span>
                    </label>
                `;
                modalList.appendChild(row);
            });

            modalActions.style.display = 'flex';
            selectAllBtn.textContent   = 'Deselect All';

        } catch(err) {
            modalStatus.textContent = 'Network error: ' + err.message;
            modalList.innerHTML = '';
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = 'Get Emails'; }
        }
    };

    // Store selected emails
    storeBtn.addEventListener('click', async function() {
        const boxes    = modalList.querySelectorAll('input[type=checkbox]:checked');
        const selected = [...boxes].map(b => {
            const idx = parseInt(b.id.replace('em-cb-', ''));
            return scrapedEmails[idx];
        }).filter(Boolean);

        if (!selected.length) {
            storeResult.textContent = 'Select at least one email.';
            return;
        }

        storeBtn.disabled = true;
        storeResult.textContent = 'Saving…';

        try {
            const res  = await fetch('{{ route('wikidata.emails.store') }}', {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : csrfToken,
                },
                body: JSON.stringify({ business_id: currentBusinessId, emails: selected }),
            });
            const json = await res.json();

            storeResult.textContent = json.message ?? (res.ok ? 'Saved!' : 'Error.');
            storeResult.style.color = res.ok ? '#16a34a' : '#b91c1c';

            // Refresh the emails cell in the table without reload
            if (res.ok && json.stored) {
                const cell = document.getElementById('emails-cell-' + currentBusinessId);
                if (cell) {
                    if (json.stored.length) {
                        cell.innerHTML = '<div class="email-chips">' +
                            json.stored.map(e => `<span class="email-chip">${escHtml(e.email)}</span>`).join('') +
                            '</div>';
                    }
                }
            }
        } catch(err) {
            storeResult.textContent = 'Network error: ' + err.message;
            storeResult.style.color = '#b91c1c';
        } finally {
            storeBtn.disabled = false;
        }
    });

    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str ?? ''));
        return d.innerHTML;
    }
})();
</script>
@endpush
