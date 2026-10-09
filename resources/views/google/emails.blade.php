@extends('layouts.app')
@section('title','Google Place Emails')
@section('subtitle','All extracted emails grouped by business')
@section('content')

<style>
.ge-wrap{overflow-x:auto}
.ge-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ge-table th{background:var(--surface,#f8f9fa);padding:.55rem .9rem;text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;border-bottom:2px solid #e5e7eb;white-space:nowrap}
.ge-table th a{color:#94a3b8;text-decoration:none;display:flex;align-items:center;gap:.3rem}
.ge-table th a:hover{color:#374151}
.ge-table th a.active{color:#4285f4}
.ge-table td{padding:.6rem .9rem;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.ge-table tr:hover td{background:#fafafa}
.ge-num{color:#94a3b8;font-size:.72rem}
.ge-badge{padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:600;color:#fff;background:#15803d}
.stat-row{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
.stat-card{background:var(--surface,#f8f9fa);border:1px solid var(--border,#e5e7eb);border-radius:8px;padding:.75rem 1.25rem;min-width:150px}
.stat-card strong{display:block;font-size:1.4rem;font-weight:700}
.stat-card span{font-size:.75rem;color:#94a3b8}
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end}
.filter-bar .field{display:flex;flex-direction:column;gap:.25rem}
.filter-bar label{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em}
.filter-bar select,.filter-bar input{padding:.45rem .7rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff}
/* email chips */
.email-chips{display:flex;flex-wrap:wrap;gap:.35rem}
.email-chip{display:inline-flex;align-items:center;gap:.3rem;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.18rem .5rem .18rem .55rem;border-radius:20px;font-size:.75rem;font-weight:500}
.email-chip .chip-del{background:none;border:none;cursor:pointer;color:#86efac;font-size:.8rem;line-height:1;padding:0 0 0 .1rem;transition:color .15s}
.email-chip .chip-del:hover{color:#dc2626}
.del-btn{background:none;border:1px solid #fecaca;color:#dc2626;border-radius:5px;padding:.25rem .6rem;cursor:pointer;font-size:.75rem}
.del-btn:hover{background:#fef2f2}
/* confirm modal */
#confirm-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center}
#confirm-bg.open{display:flex}
#confirm-box{background:#fff;border-radius:10px;padding:1.5rem 2rem;max-width:380px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,.15);text-align:center}
#confirm-box h3{margin:0 0 .5rem;font-size:1rem}
#confirm-box p{color:#64748b;font-size:.875rem;margin:0 0 1.25rem}
#confirm-box .actions{display:flex;gap:.75rem;justify-content:center}
</style>

{{-- Stats --}}
<div class="stat-row">
    <div class="stat-card"><strong style="color:#15803d">{{ number_format($stats['total_emails']) }}</strong><span>Total Emails</span></div>
    <div class="stat-card"><strong style="color:#4285f4">{{ number_format($stats['total_businesses']) }}</strong><span>Businesses</span></div>
</div>

{{-- Filters --}}
<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="{{ route('google.emails.index') }}">
            <input type="hidden" name="sort" value="{{ $sort }}">            <div class="filter-bar">
                <div class="field">
                    <label>Email</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email…" style="min-width:180px">
                </div>
                <div class="field">
                    <label>City</label>
                    <select name="city" style="min-width:150px">
                        <option value="">All cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" @selected(request('city')===$city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Business Name</label>
                    <input type="text" name="business" value="{{ request('business') }}" placeholder="Business name…" style="min-width:150px">
                </div>
                <div class="field" style="justify-content:flex-end">
                    <label style="visibility:hidden">x</label>
                    <div style="display:flex;gap:.5rem">
                        <a class="btn" href="{{ route('google.emails.index') }}">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Emails by Business <span class="ge-badge">{{ $places->total() }} businesses</span></h2>
        <div style="display:flex;gap:.5rem">
            <a href="{{ route('google.emails.export', request()->query()) }}" class="btn success">⬇ Export Excel</a>
            <a href="{{ route('google.saved') }}" class="btn">← Saved Places</a>
        </div>
    </div>
    <div style="padding:1.25rem">

        @if($places->isEmpty())
            <p style="text-align:center;padding:3rem;color:#94a3b8">
                No emails found yet. <a href="{{ route('google.saved') }}">Go to Saved Places and extract emails first.</a>
            </p>
        @else

        {{-- Sort bar --}}
        @php
            $currentParams = request()->except('page');
        @endphp
        <div style="display:flex;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap;align-items:center">
            <span style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Sort:</span>
            @foreach(['latest'=>'Latest','name'=>'Name A–Z','city'=>'City','emails_desc'=>'Most Emails','emails_asc'=>'Fewest Emails'] as $key=>$label)
                <a href="?{{ http_build_query(array_merge($currentParams, ['sort'=>$key])) }}"
                   class="btn {{ $sort===$key?'primary':'' }}"
                   style="font-size:.75rem;padding:.3rem .65rem">{{ $label }}</a>
            @endforeach
        </div>

        <div class="ge-wrap">
            <table class="ge-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Business Name</th>
                        <th>City</th>
                        <th>Website</th>
                        <th>Phone</th>
                        <th>Rating</th>
                        <th>Type</th>
                        <th>Emails (click ✕ to delete)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($places as $place)
                    <tr id="biz-row-{{ $place->id }}">
                        <td class="ge-num">{{ $loop->iteration + ($places->currentPage()-1)*$places->perPage() }}</td>
                        <td>
                            <strong>{{ $place->name }}</strong>
                            @if($place->url)
                                <br><a href="{{ $place->url }}" target="_blank" rel="noopener" style="font-size:.7rem;color:#4285f4">📍 Maps</a>
                            @endif
                        </td>
                        <td>{{ $place->locality ?? $place->search_location ?? '—' }}
                            @if($place->postal_code)<br><span class="ge-num">{{ $place->postal_code }}</span>@endif
                        </td>
                        <td>
                            @if($place->website)
                                <a href="{{ $place->website }}" target="_blank" rel="noopener" style="font-size:.75rem;color:#4285f4;word-break:break-all">
                                    {{ parse_url($place->website, PHP_URL_HOST) ?? Str::limit($place->website,30) }}
                                </a>
                            @else <span class="ge-num">—</span> @endif
                        </td>
                        <td style="font-size:.78rem">{{ $place->formatted_phone_number ?? $place->international_phone_number ?? '—' }}</td>
                        <td>
                            @if($place->rating)
                                <span style="color:#f59e0b">★</span> {{ $place->rating }}
                            @else <span class="ge-num">—</span> @endif
                        </td>
                        <td class="ge-num">{{ $place->search_type ?? '—' }}</td>
                        <td>
                            <div class="email-chips" id="chips-{{ $place->id }}">
                                @foreach($place->emails as $em)
                                    <span class="email-chip" id="chip-{{ $em->id }}">
                                        {{ $em->email }}
                                        <button class="chip-del"
                                            onclick="confirmDelete({{ $em->id }}, '{{ addslashes($em->email) }}', {{ $place->id }})"
                                            title="Delete this email">✕</button>
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <button class="del-btn"
                                onclick="confirmDeletePlace({{ $place->id }}, '{{ addslashes($place->name) }}')"
                                title="Delete this place and all its emails">
                                🗑 Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($places->hasPages())
            <div style="margin-top:1rem">{{ $places->links() }}</div>
        @endif
        @endif
    </div>
</div>

{{-- Confirm modal --}}
<div id="confirm-bg">
    <div id="confirm-box">
        <h3>⚠ Delete Email?</h3>
        <p id="confirm-msg">Are you sure?</p>
        <div class="actions">
            <button class="btn danger" id="confirm-yes" type="button">Yes, Delete</button>
            <button class="btn" id="confirm-no" type="button">Cancel</button>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
(function(){
    const filterForm = document.querySelector('.filter-bar').closest('form');
    const filterInputs = filterForm ? filterForm.querySelectorAll('input[name="search"], input[name="business"], select[name="city"]') : [];
    let filterTimer = null;
    filterInputs.forEach(el => {
        const eventName = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(eventName, () => {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(() => {
                filterForm.submit();
            }, el.tagName === 'SELECT' ? 50 : 450);
        });
    });

    const csrf   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const bg     = document.getElementById('confirm-bg');
    const msgEl  = document.getElementById('confirm-msg');
    const yesBtn = document.getElementById('confirm-yes');
    const noBtn  = document.getElementById('confirm-no');

    let pendingId      = null;
    let pendingPlaceId = null;
    let deleteMode     = 'email'; // 'email' or 'place'

    window.confirmDelete = function(emailId, email, placeId){
        pendingId      = emailId;
        pendingPlaceId = placeId;
        deleteMode     = 'email';
        document.querySelector('#confirm-box h3').textContent = '⚠ Delete Email?';
        msgEl.textContent = `Are you sure you want to delete "${email}"? This cannot be undone.`;
        bg.classList.add('open');
    };

    window.confirmDeletePlace = function(placeId, name){
        pendingPlaceId = placeId;
        deleteMode     = 'place';
        document.querySelector('#confirm-box h3').textContent = '⚠ Delete Business?';
        msgEl.textContent = `Delete "${name}" and ALL its emails? This cannot be undone.`;
        bg.classList.add('open');
    };

    noBtn.addEventListener('click',  () => { bg.classList.remove('open'); pendingId = null; });
    bg.addEventListener('click', e => { if(e.target===bg){ bg.classList.remove('open'); pendingId=null; } });

    yesBtn.addEventListener('click', async function(){
        if (!pendingId && !pendingPlaceId) return;
        yesBtn.disabled    = true;
        yesBtn.textContent = 'Deleting…';

        try {
            let url, res, json;

            if (deleteMode === 'place') {
                url  = `{{ url('/google-places/saved') }}/${pendingPlaceId}`;
                res  = await fetch(url, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf} });
                json = await res.json();
                if (res.ok && json.status === 'success') {
                    const row = document.getElementById('biz-row-' + pendingPlaceId);
                    if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                    bg.classList.remove('open');
                } else { alert(json.message ?? 'Failed.'); }
            } else {
                url  = `{{ url('/google-places/emails') }}/${pendingId}`;
                res  = await fetch(url, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf} });
                json = await res.json();
                if (res.ok && json.status === 'success') {
                    const chip = document.getElementById('chip-' + pendingId);
                    if (chip) chip.remove();
                    const chipsDiv = document.getElementById('chips-' + pendingPlaceId);
                    if (chipsDiv && chipsDiv.querySelectorAll('.email-chip').length === 0) {
                        const row = document.getElementById('biz-row-' + pendingPlaceId);
                        if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                    }
                    bg.classList.remove('open');
                } else { alert(json.message ?? 'Failed.'); }
            }
        } catch(err) {
            alert('Network error: ' + err.message);
        } finally {
            yesBtn.disabled    = false;
            yesBtn.textContent = 'Yes, Delete';
            pendingId          = null;
            pendingPlaceId     = null;
        }
    });
})();
</script>
@endpush
