@extends('layouts.app')
@section('title','Google Places — Saved')
@section('subtitle','All businesses saved from Google Places searches')
@section('content')

<style>
.gp-wrap{overflow-x:auto}
.gp-table{width:100%;border-collapse:collapse;font-size:.82rem}
.gp-table th{background:var(--surface,#f8f9fa);padding:.55rem .9rem;text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;border-bottom:2px solid #e5e7eb;white-space:nowrap}
.gp-table td{padding:.55rem .9rem;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.gp-table tr:hover td{background:#fafafa}
.gp-num{color:#94a3b8;font-size:.72rem}
.gp-badge{padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:600;color:#fff;background:#4285f4}
.stat-row{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
.stat-card{background:var(--surface,#f8f9fa);border:1px solid var(--border,#e5e7eb);border-radius:8px;padding:.75rem 1.25rem;min-width:130px}
.stat-card strong{display:block;font-size:1.4rem;font-weight:700}
.stat-card span{font-size:.75rem;color:#94a3b8}
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem}
.filter-bar .field{display:flex;flex-direction:column;gap:.25rem}
.filter-bar label{font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em}
.filter-bar select,.filter-bar input{padding:.45rem .7rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff}
.gp-status-op{color:#16a34a;font-weight:600;font-size:.72rem}
.gp-status-cl{color:#dc2626;font-size:.72rem}
.stars{color:#f59e0b;font-size:.8rem}
.email-chip{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.15rem .45rem;border-radius:4px;font-size:.72rem;margin:.1rem .1rem 0 0;display:inline-block}
.del-btn{background:none;border:1px solid #fecaca;color:#dc2626;border-radius:5px;padding:.25rem .6rem;cursor:pointer;font-size:.75rem}
.del-btn:hover{background:#fef2f2}
.spin{display:inline-block;width:13px;height:13px;border:2px solid #ddd;border-top-color:#4285f4;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
.bulk-progress{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem;display:none}
.bulk-bar-wrap{background:#e5e7eb;border-radius:999px;height:10px;margin:.5rem 0}
.bulk-bar{background:#4285f4;height:10px;border-radius:999px;transition:width .3s}
/* modal */
#confirm-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center}
#confirm-bg.open{display:flex}
#confirm-box{background:#fff;border-radius:10px;padding:1.5rem 2rem;max-width:380px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,.15);text-align:center}
#confirm-box h3{margin:0 0 .5rem;font-size:1rem}
#confirm-box p{color:#64748b;font-size:.875rem;margin:0 0 1.25rem}
#confirm-box .actions{display:flex;gap:.75rem;justify-content:center}
/* email modal */
#em-modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center}
#em-modal-bg.open{display:flex}
#em-modal{background:#fff;border-radius:10px;max-width:580px;width:95%;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2)}
#em-modal-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #f0f0f0}
#em-modal-head h3{margin:0;font-size:.95rem}
#em-modal-close{background:none;border:none;font-size:1.3rem;cursor:pointer;color:#888}
#em-modal-body{padding:1.25rem}
.em-email-row{display:flex;align-items:center;gap:.5rem;padding:.35rem 0;border-bottom:1px solid #f3f4f6}
.em-email-row:last-child{border-bottom:none}
.em-email-row input[type=checkbox]{width:15px;height:15px;cursor:pointer}
.em-email-row label{flex:1;font-size:.85rem;word-break:break-all;cursor:pointer}
.em-source{font-size:.68rem;color:#94a3b8}
</style>

{{-- Stats --}}
<div class="stat-row">
    <div class="stat-card"><strong>{{ number_format($stats['total']) }}</strong><span>Total Saved</span></div>
    <div class="stat-card"><strong style="color:#16a34a">{{ number_format($stats['operational']) }}</strong><span>Operational</span></div>
    <div class="stat-card"><strong style="color:#4285f4">{{ number_format($stats['with_phone']) }}</strong><span>With Phone</span></div>
    <div class="stat-card"><strong style="color:#7c3aed">{{ number_format($stats['with_website']) }}</strong><span>With Website</span></div>
    <div class="stat-card"><strong style="color:#15803d">{{ number_format($stats['with_email']) }}</strong><span>With Email</span></div>
</div>

{{-- Filters --}}
<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="{{ route('google.saved') }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <div class="filter-bar">
                <div class="field"><label>Name</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…"></div>
                <div class="field"><label>Location</label><input type="text" name="location" value="{{ request('location') }}" placeholder="City or area…"></div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All statuses</option>
                        <option value="OPERATIONAL" @selected(request('status')==='OPERATIONAL')>Operational</option>
                        <option value="CLOSED_TEMPORARILY" @selected(request('status')==='CLOSED_TEMPORARILY')>Closed Temporarily</option>
                        <option value="CLOSED_PERMANENTLY" @selected(request('status')==='CLOSED_PERMANENTLY')>Closed Permanently</option>
                    </select>
                </div>
                <div class="field"><label>Postcode</label><input type="text" name="postal_code" value="{{ request('postal_code') }}" placeholder="e.g. SW1" style="width:90px"></div>
                <div class="field" style="justify-content:flex-end">
                    <label style="visibility:hidden">x</label>
                    <div style="display:flex;gap:.5rem">
                        <a class="btn" href="{{ route('google.saved') }}">Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Saved Places <span class="gp-badge">{{ $places->total() }}</span></h2>
        <div style="display:flex;gap:.5rem;align-items:center">
            <button class="btn success" id="bulk-email-btn" type="button">📧 Bulk Get Emails</button>
            <a href="{{ route('google.index') }}" class="btn">← New Search</a>
        </div>
    </div>
    <div style="padding:1.25rem">

        {{-- Bulk progress --}}
        <div class="bulk-progress" id="bulk-progress">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem">
                <strong style="font-size:.875rem">📧 Bulk Email Extraction</strong>
                <span id="bulk-status-text" style="font-size:.82rem;color:#64748b"></span>
            </div>
            <div class="bulk-bar-wrap"><div class="bulk-bar" id="bulk-bar" style="width:0%"></div></div>
            <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#64748b;margin-top:.25rem">
                <span>Processed: <strong id="bulk-done">0</strong> / <strong id="bulk-total">0</strong></span>
                <span>Found: <strong id="bulk-found">0</strong> emails</span>
                <span>Saved: <strong id="bulk-saved">0</strong> new</span>
            </div>
        </div>

        {{-- Sort bar --}}
        @php $currentParams = request()->except('page'); @endphp
        <div style="display:flex;gap:.5rem;margin-bottom:.75rem;flex-wrap:wrap;align-items:center">
            <span style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Sort:</span>
            @foreach(['latest'=>'Latest','name'=>'Name A–Z','city'=>'City','rating'=>'Rating','emails'=>'Most Emails'] as $key=>$label)
                <a href="?{{ http_build_query(array_merge($currentParams, ['sort'=>$key])) }}"
                   class="btn {{ $sort===$key?'primary':'' }}"
                   style="font-size:.75rem;padding:.3rem .65rem">{{ $label }}</a>
            @endforeach
        </div>

        @if($places->isEmpty())
            <p style="text-align:center;padding:3rem;color:#94a3b8">No places saved yet. <a href="{{ route('google.index') }}">Search first.</a></p>
        @else
        <div class="gp-wrap">
            <table class="gp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Website</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Emails</th>
                        <th>Get Emails</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($places as $place)
                    <tr id="place-row-{{ $place->id }}">
                        <td class="gp-num">{{ $loop->iteration + ($places->currentPage()-1)*$places->perPage() }}</td>
                        <td>
                            <strong>{{ $place->name }}</strong>
                            @if($place->url)<br><a href="{{ $place->url }}" target="_blank" rel="noopener" style="font-size:.7rem;color:#4285f4">📍 Maps</a>@endif
                        </td>
                        <td style="max-width:160px;font-size:.78rem">
                            {{ $place->formatted_address ?? $place->vicinity ?? '—' }}
                            @if($place->postal_code)<br><span class="gp-num">{{ $place->postal_code }}</span>@endif
                        </td>
                        <td style="font-size:.78rem">{{ $place->formatted_phone_number ?? $place->international_phone_number ?? '—' }}</td>
                        <td>
                            @if($place->website)
                                <a href="{{ $place->website }}" target="_blank" rel="noopener" style="font-size:.75rem;color:#4285f4;word-break:break-all">
                                    {{ parse_url($place->website, PHP_URL_HOST) ?? Str::limit($place->website,30) }}
                                </a>
                            @else <span class="gp-num">—</span> @endif
                        </td>
                        <td>
                            @if($place->rating)
                                <span class="stars">{{ str_repeat('★',round($place->rating)) }}{{ str_repeat('☆',5-round($place->rating)) }}</span>
                                <br><span class="gp-num">{{ $place->rating }} ({{ number_format($place->user_ratings_total??0) }})</span>
                            @else <span class="gp-num">—</span> @endif
                        </td>
                        <td>
                            @php $s=$place->business_status??'—'; @endphp
                            <span class="{{ $s==='OPERATIONAL'?'gp-status-op':'gp-status-cl' }}">{{ str_replace('_',' ',$s) }}</span>
                        </td>
                        <td id="emails-cell-{{ $place->id }}">
                            @if($place->emails_count > 0)
                                <div>@foreach($place->emails as $em)<span class="email-chip">{{ $em->email }}</span>@endforeach</div>
                            @else <span class="gp-num">—</span> @endif
                        </td>
                        <td>
                            @if($place->website)
                                <button class="btn primary"
                                    onclick="getEmails({{ $place->id }}, '{{ addslashes($place->website) }}', '{{ addslashes($place->name) }}')"
                                    id="get-btn-{{ $place->id }}" style="font-size:.75rem;padding:.35rem .65rem">
                                    📧 Get Emails
                                </button>
                            @else <span class="gp-num">No website</span> @endif
                        </td>
                        <td>
                            <button class="del-btn"
                                onclick="confirmDeletePlace({{ $place->id }}, '{{ addslashes($place->name) }}')"
                                title="Delete this place and its emails">
                                🗑 Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($places->hasPages())<div style="margin-top:1rem">{{ $places->links() }}</div>@endif
        @endif
    </div>
</div>

{{-- Confirm delete modal --}}
<div id="confirm-bg">
    <div id="confirm-box">
        <h3>⚠ Delete Place?</h3>
        <p id="confirm-msg">This will delete the place and all its emails.</p>
        <div class="actions">
            <button class="btn danger" id="confirm-yes" type="button">Yes, Delete</button>
            <button class="btn" id="confirm-no" type="button">Cancel</button>
        </div>
    </div>
</div>

{{-- Email modal --}}
<div id="em-modal-bg">
    <div id="em-modal">
        <div id="em-modal-head">
            <h3 id="em-modal-title">Emails Found</h3>
            <button id="em-modal-close" type="button">✕</button>
        </div>
        <div id="em-modal-body">
            <p id="em-modal-status" style="color:#64748b;font-size:.875rem"></p>
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
    const filterForm = document.querySelector('.filter-bar').closest('form');
    const filterInputs = filterForm ? filterForm.querySelectorAll('input[name="search"], input[name="location"], input[name="postal_code"], select[name="status"]') : [];
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

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    function esc(str){ const d=document.createElement('div'); d.appendChild(document.createTextNode(str??'')); return d.innerHTML; }

    // ── Delete place ────────────────────────────────────────────
    const confirmBg  = document.getElementById('confirm-bg');
    const confirmMsg = document.getElementById('confirm-msg');
    const yesBtn     = document.getElementById('confirm-yes');
    const noBtn      = document.getElementById('confirm-no');
    let pendingPlaceId = null;

    window.confirmDeletePlace = function(id, name){
        pendingPlaceId = id;
        confirmMsg.textContent = `Delete "${name}" and all its emails? This cannot be undone.`;
        confirmBg.classList.add('open');
    };
    noBtn.addEventListener('click', ()=>{ confirmBg.classList.remove('open'); pendingPlaceId=null; });
    confirmBg.addEventListener('click', e=>{ if(e.target===confirmBg){ confirmBg.classList.remove('open'); pendingPlaceId=null; } });
    yesBtn.addEventListener('click', async function(){
        if(!pendingPlaceId) return;
        yesBtn.disabled=true; yesBtn.textContent='Deleting…';
        try{
            const res = await fetch(`{{ url('/google-places/saved') }}/${pendingPlaceId}`,{
                method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}
            });
            const json = await res.json();
            if(res.ok && json.status==='success'){
                const row = document.getElementById('place-row-'+pendingPlaceId);
                if(row){ row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                confirmBg.classList.remove('open');
            } else { alert(json.message??'Failed.'); }
        }catch(e){ alert('Error: '+e.message); }
        finally{ yesBtn.disabled=false; yesBtn.textContent='Yes, Delete'; pendingPlaceId=null; }
    });

    // ── Individual email modal ──────────────────────────────────
    const modalBg     = document.getElementById('em-modal-bg');
    const modalTitle  = document.getElementById('em-modal-title');
    const modalStatus = document.getElementById('em-modal-status');
    const modalList   = document.getElementById('em-modal-list');
    const modalActions= document.getElementById('em-modal-actions');
    const storeBtn    = document.getElementById('em-store-btn');
    const selAllBtn   = document.getElementById('em-select-all-btn');
    const storeResult = document.getElementById('em-store-result');
    document.getElementById('em-modal-close').addEventListener('click',()=>modalBg.classList.remove('open'));
    modalBg.addEventListener('click',e=>{ if(e.target===modalBg) modalBg.classList.remove('open'); });
    selAllBtn.addEventListener('click',()=>{
        const boxes=[...modalList.querySelectorAll('input[type=checkbox]')];
        const all=boxes.every(b=>b.checked); boxes.forEach(b=>b.checked=!all);
        selAllBtn.textContent=all?'Select All':'Deselect All';
    });
    let currentPlaceId=null, scrapedEmails=[];

    window.getEmails = async function(placeId, website, name){
        currentPlaceId=placeId; scrapedEmails=[];
        storeResult.textContent=''; modalTitle.textContent=name;
        modalStatus.textContent='Crawling website pages…';
        modalList.innerHTML='<div style="text-align:center;padding:1.5rem"><span class="spin"></span></div>';
        modalActions.style.display='none'; modalBg.classList.add('open');
        const btn=document.getElementById('get-btn-'+placeId);
        if(btn){ btn.disabled=true; btn.textContent='Crawling…'; }
        try{
            const res=await fetch('{{ route('google.emails.scrape') }}?website='+encodeURIComponent(website)+'&place_id='+placeId,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const json=await res.json();
            if(!res.ok||json.error){ modalStatus.textContent=json.error??'Failed.'; modalList.innerHTML=''; return; }
            scrapedEmails=json.data??[];
            modalStatus.textContent=scrapedEmails.length+' email(s) found across '+json.pages_crawled+' page(s).';
            if(!scrapedEmails.length){ modalList.innerHTML='<p style="text-align:center;color:#94a3b8;padding:1rem">No emails found.</p>'; modalActions.style.display='none'; return; }
            modalList.innerHTML='';
            scrapedEmails.forEach((item,i)=>{
                const row=document.createElement('div'); row.className='em-email-row';
                row.innerHTML=`<input type="checkbox" id="em-cb-${i}" checked><label for="em-cb-${i}">${esc(item.email)}<br><span class="em-source">${esc(item.source_url??'')}</span></label>`;
                modalList.appendChild(row);
            });
            modalActions.style.display='flex'; selAllBtn.textContent='Deselect All';
        }catch(err){ modalStatus.textContent='Error: '+err.message; modalList.innerHTML=''; }
        finally{ if(btn){ btn.disabled=false; btn.textContent='📧 Get Emails'; } }
    };
    storeBtn.addEventListener('click', async function(){
        const checked=[...modalList.querySelectorAll('input[type=checkbox]:checked')];
        const selected=checked.map(b=>scrapedEmails[parseInt(b.id.replace('em-cb-',''))]).filter(Boolean);
        if(!selected.length){ storeResult.textContent='Select at least one.'; return; }
        storeBtn.disabled=true; storeResult.textContent='Saving…';
        try{
            const res=await fetch('{{ route('google.emails.store') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({place_id:currentPlaceId,emails:selected})});
            const json=await res.json();
            storeResult.textContent=json.message??(res.ok?'Saved!':'Error.'); storeResult.style.color=res.ok?'#16a34a':'#b91c1c';
            if(res.ok&&json.stored){ const cell=document.getElementById('emails-cell-'+currentPlaceId); if(cell&&json.stored.length) cell.innerHTML='<div>'+json.stored.map(e=>`<span class="email-chip">${esc(e.email)}</span>`).join('')+'</div>'; }
        }catch(err){ storeResult.textContent='Error: '+err.message; storeResult.style.color='#b91c1c'; }
        finally{ storeBtn.disabled=false; }
    });

    // ── Bulk emails ─────────────────────────────────────────────
    const bulkBtn=document.getElementById('bulk-email-btn');
    const bulkProgress=document.getElementById('bulk-progress');
    const bulkBar=document.getElementById('bulk-bar');
    const bulkDone=document.getElementById('bulk-done');
    const bulkTotal=document.getElementById('bulk-total');
    const bulkFound=document.getElementById('bulk-found');
    const bulkSaved=document.getElementById('bulk-saved');
    const bulkStatus=document.getElementById('bulk-status-text');
    function getPagePlaces(){
        const rows=document.querySelectorAll('[id^="place-row-"]'); const places=[];
        rows.forEach(row=>{ const id=row.id.replace('place-row-',''); const btn=document.getElementById('get-btn-'+id); if(btn){ const wl=row.querySelector('td:nth-child(5) a'); if(wl) places.push({id,website:wl.href}); } });
        return places;
    }
    bulkBtn.addEventListener('click', async function(){
        const places=getPagePlaces();
        if(!places.length){ alert('No places with websites on this page.'); return; }
        bulkBtn.disabled=true; bulkProgress.style.display='block';
        bulkTotal.textContent=places.length; bulkDone.textContent='0'; bulkFound.textContent='0'; bulkSaved.textContent='0'; bulkBar.style.width='0%';
        let done=0,totalFound=0,totalSaved=0;
        for(const place of places){
            bulkStatus.textContent='Crawling '+place.id+'…';
            try{
                const sr=await fetch('{{ route('google.emails.scrape') }}?website='+encodeURIComponent(place.website)+'&place_id='+place.id,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                const sj=await sr.json(); const emails=sj.data??[];
                totalFound+=emails.length; bulkFound.textContent=totalFound;
                if(emails.length){
                    const tr=await fetch('{{ route('google.emails.store') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({place_id:parseInt(place.id),emails})});
                    const tj=await tr.json(); totalSaved+=tj.inserted??0; bulkSaved.textContent=totalSaved;
                    if(tj.stored){ const cell=document.getElementById('emails-cell-'+place.id); if(cell&&tj.stored.length) cell.innerHTML='<div>'+tj.stored.map(e=>`<span class="email-chip">${esc(e.email)}</span>`).join('')+'</div>'; }
                }
            }catch(e){}
            done++; bulkDone.textContent=done; bulkBar.style.width=Math.round((done/places.length)*100)+'%'; bulkStatus.textContent=done+' / '+places.length+' processed';
        }
        bulkStatus.textContent='✅ Done! '+totalFound+' found, '+totalSaved+' new saved.'; bulkBtn.disabled=false;
    });
})();
</script>
@endpush
