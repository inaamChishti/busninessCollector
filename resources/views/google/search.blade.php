@extends('layouts.app')
@section('title','Google Places')
@section('subtitle','Search businesses worldwide using Google Places API')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
.gp-form{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1rem;align-items:end}
.gp-form .field{display:flex;flex-direction:column;gap:.3rem}
.gp-form label{font-size:.72rem;font-weight:700;color:var(--text-muted,#666);text-transform:uppercase;letter-spacing:.05em}
.gp-form input,.gp-form select{padding:.5rem .75rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.875rem;background:#fff;width:100%}
.gp-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem}
.gp-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;display:none;font-size:.875rem}
.gp-spinner{display:none;width:18px;height:18px;border:2px solid #ddd;border-top-color:#4285f4;border-radius:50%;animation:gp-spin .7s linear infinite;flex-shrink:0}
@keyframes gp-spin{to{transform:rotate(360deg)}}
.gp-meta{display:none;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.gp-badge{padding:.22rem .65rem;border-radius:999px;font-size:.75rem;font-weight:600;color:#fff;background:#4285f4}
/* table */
.gp-wrap{overflow-x:auto}
.gp-table{width:100%;border-collapse:collapse;font-size:.82rem}
.gp-table th{background:var(--surface,#f8f9fa);padding:.55rem .9rem;text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;border-bottom:2px solid #e5e7eb;white-space:nowrap}
.gp-table td{padding:.55rem .9rem;border-bottom:1px solid #f3f4f6;vertical-align:top}
.gp-table tr:hover td{background:#fafafa}
.gp-table th:first-child,.gp-table td:first-child{width:36px;text-align:center}
.gp-num{color:#94a3b8;font-size:.72rem}
.gp-placeholder{text-align:center;padding:4rem;color:#94a3b8;font-size:.9rem;border:1.5px dashed #e2e8f0;border-radius:12px;margin-top:.5rem}
.stars{color:#f59e0b;font-size:.8rem}
.gp-status-op{color:#16a34a;font-weight:600;font-size:.72rem}
.gp-status-cl{color:#dc2626;font-size:.72rem}
.tag{background:#eff6ff;color:#1d4ed8;padding:.1rem .4rem;border-radius:4px;font-size:.7rem;margin:.1rem .1rem 0 0;display:inline-block}
.gp-store-msg{padding:.6rem 1rem;border-radius:6px;font-size:.875rem;display:none;margin-bottom:.75rem}
/* select2 */
.select2-container .select2-selection--single{height:38px;border:1px solid var(--border,#ddd);border-radius:6px}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:36px;padding-left:.75rem}
.select2-container--default .select2-selection--single .select2-selection__arrow{height:36px}
.select2-container--default .select2-results__option--highlighted{background:#4285f4!important}
.select2-container{width:100%!important}
</style>

@if(!$apiConfigured)
<div class="gp-warn">
    ⚠ <strong>Google Places API key not configured.</strong>
    Add <code>GOOGLE_PLACES_API_KEY=your_key</code> to <code>.env</code> —
    get a key at <a href="https://console.cloud.google.com/apis/library/places-backend.googleapis.com" target="_blank" rel="noopener">Google Cloud Console</a>
    (enable <strong>Places API</strong>).
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <h2>Search Google Places</h2>
        <a href="{{ route('google.saved') }}" class="btn">💾 Saved Places →</a>
    </div>
    <div style="padding:1.25rem 1.25rem .75rem">

        <div class="gp-form">
            <div class="field" style="grid-column:span 2;min-width:280px">
                <label>Search Query <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#94a3b8">(optional)</span></label>
                <input type="text" id="gp-query" placeholder="e.g. restaurants, dentists, IT companies… (leave blank to search all)" maxlength="200">
            </div>
            <div class="field">
                <label>City / Location</label>
                <select id="gp-location-select">
                    <option value="">— Select city —</option>
                    <optgroup label="England">
                        <option value="London">London</option>
                        <option value="Birmingham">Birmingham</option>
                        <option value="Manchester">Manchester</option>
                        <option value="Leeds">Leeds</option>
                        <option value="Sheffield">Sheffield</option>
                        <option value="Liverpool">Liverpool</option>
                        <option value="Bristol">Bristol</option>
                        <option value="Nottingham">Nottingham</option>
                        <option value="Leicester">Leicester</option>
                        <option value="Coventry">Coventry</option>
                        <option value="Bradford">Bradford</option>
                        <option value="Kingston upon Hull">Hull</option>
                        <option value="Stoke-on-Trent">Stoke-on-Trent</option>
                        <option value="Wolverhampton">Wolverhampton</option>
                        <option value="Derby">Derby</option>
                        <option value="Swansea">Swansea</option>
                        <option value="Southampton">Southampton</option>
                        <option value="Salford">Salford</option>
                        <option value="Portsmouth">Portsmouth</option>
                        <option value="Newcastle upon Tyne">Newcastle</option>
                        <option value="Sunderland">Sunderland</option>
                        <option value="Middlesbrough">Middlesbrough</option>
                        <option value="Peterborough">Peterborough</option>
                        <option value="Luton">Luton</option>
                        <option value="Northampton">Northampton</option>
                        <option value="Milton Keynes">Milton Keynes</option>
                        <option value="Reading">Reading</option>
                        <option value="Exeter">Exeter</option>
                        <option value="Plymouth">Plymouth</option>
                        <option value="Brighton">Brighton</option>
                        <option value="Oxford">Oxford</option>
                        <option value="Cambridge">Cambridge</option>
                        <option value="Norwich">Norwich</option>
                        <option value="Gloucester">Gloucester</option>
                        <option value="Ipswich">Ipswich</option>
                        <option value="Wigan">Wigan</option>
                        <option value="Bolton">Bolton</option>
                        <option value="Blackpool">Blackpool</option>
                        <option value="Blackburn">Blackburn</option>
                        <option value="Huddersfield">Huddersfield</option>
                        <option value="York">York</option>
                        <option value="Doncaster">Doncaster</option>
                        <option value="Warrington">Warrington</option>
                        <option value="Rochdale">Rochdale</option>
                        <option value="Barnsley">Barnsley</option>
                        <option value="Rotherham">Rotherham</option>
                        <option value="Stockport">Stockport</option>
                        <option value="Oldham">Oldham</option>
                        <option value="Swindon">Swindon</option>
                        <option value="Basildon">Basildon</option>
                        <option value="Chelmsford">Chelmsford</option>
                        <option value="Colchester">Colchester</option>
                        <option value="Southend-on-Sea">Southend-on-Sea</option>
                        <option value="Worthing">Worthing</option>
                        <option value="Bournemouth">Bournemouth</option>
                        <option value="Swindon">Swindon</option>
                        <option value="Telford">Telford</option>
                        <option value="Worcester">Worcester</option>
                        <option value="Hereford">Hereford</option>
                        <option value="Shrewsbury">Shrewsbury</option>
                        <option value="Guildford">Guildford</option>
                        <option value="Slough">Slough</option>
                        <option value="Watford">Watford</option>
                        <option value="Stevenage">Stevenage</option>
                        <option value="Hemel Hempstead">Hemel Hempstead</option>
                        <option value="St Albans">St Albans</option>
                        <option value="Bath">Bath</option>
                        <option value="Taunton">Taunton</option>
                        <option value="Truro">Truro</option>
                        <option value="Weston-super-Mare">Weston-super-Mare</option>
                    </optgroup>
                    <optgroup label="Scotland">
                        <option value="Glasgow">Glasgow</option>
                        <option value="Edinburgh">Edinburgh</option>
                        <option value="Aberdeen">Aberdeen</option>
                        <option value="Dundee">Dundee</option>
                        <option value="Inverness">Inverness</option>
                        <option value="Stirling">Stirling</option>
                        <option value="Perth">Perth</option>
                        <option value="Falkirk">Falkirk</option>
                        <option value="Livingston">Livingston</option>
                        <option value="Paisley">Paisley</option>
                    </optgroup>
                    <optgroup label="Wales">
                        <option value="Cardiff">Cardiff</option>
                        <option value="Newport">Newport</option>
                        <option value="Swansea">Swansea</option>
                        <option value="Wrexham">Wrexham</option>
                        <option value="Barry">Barry</option>
                        <option value="Neath">Neath</option>
                        <option value="Llanelli">Llanelli</option>
                    </optgroup>
                    <optgroup label="Northern Ireland">
                        <option value="Belfast">Belfast</option>
                        <option value="Londonderry">Londonderry</option>
                        <option value="Lisburn">Lisburn</option>
                        <option value="Newry">Newry</option>
                        <option value="Armagh">Armagh</option>
                    </optgroup>
                    <optgroup label="Other / Custom">
                        <option value="__custom__">✏ Enter custom location…</option>
                    </optgroup>
                </select>
                <input type="text" id="gp-location-custom" placeholder="Type custom location…" style="display:none;margin-top:.4rem">
            </div>
            <div class="field">
                <label>Business Type (optional)</label>
                <select id="gp-type">
                    <option value="">— All Types —</option>
                    @foreach($placeTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Search Radius (metres)</label>
                <select id="gp-radius">
                    <option value="1000">1 km</option>
                    <option value="2000">2 km</option>
                    <option value="5000" selected>5 km</option>
                    <option value="10000">10 km</option>
                    <option value="25000">25 km</option>
                    <option value="50000">50 km</option>
                </select>
            </div>
            <div class="field" style="justify-content:flex-end">
                <label style="visibility:hidden">x</label>
                <div style="display:flex;align-items:center;gap:.6rem">
                    <button class="btn primary" id="gp-search-btn" type="button" {{ !$apiConfigured?'disabled':'' }}>
                        🔍 Search
                    </button>
                    <div class="gp-spinner" id="gp-spinner"></div>
                </div>
            </div>
        </div>

        <div class="gp-error" id="gp-error"></div>

        {{-- Meta bar --}}
        <div class="gp-meta" id="gp-meta">
            <span class="gp-badge" id="gp-total-badge"></span>
            <span id="gp-ctx-label" style="font-size:.82rem;color:#64748b"></span>
            <div style="margin-left:auto;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                <span id="gp-select-label" style="font-size:.8rem;color:#94a3b8"></span>
                <button class="btn" id="gp-select-all-btn" type="button">Select All</button>
                <button class="btn success" id="gp-store-btn" type="button">💾 Store Selected</button>
                <button class="btn" id="gp-details-btn" type="button" title="Fetch full details for selected">🔎 Get Full Details</button>
                <button class="btn" id="gp-export-btn" type="button">⇩ CSV</button>
            </div>
        </div>

        <div class="gp-store-msg" id="gp-store-msg"></div>

        <div class="gp-wrap">
            <table class="gp-table" id="gp-table" style="display:none">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="gp-check-all"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Website</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Types</th>
                        <th>Open Now</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody id="gp-tbody"></tbody>
            </table>
            <p class="gp-placeholder" id="gp-empty" style="display:none">No places found. Try a different query or location.</p>
            <p class="gp-placeholder" id="gp-placeholder">Enter a search query (e.g. "restaurants") and optionally a location, then click <strong>Search</strong>.</p>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@push('scripts')
<script>
$(function(){
    $('#gp-type').select2({ placeholder:'— All Types —', allowClear:true, width:'100%' });
    $('#gp-location-select').select2({ placeholder:'— Select city —', allowClear:true, width:'100%' });

    // Show custom input when "Enter custom" selected
    document.getElementById('gp-location-select').addEventListener('change', function(){
        const custom = document.getElementById('gp-location-custom');
        if(this.value === '__custom__'){ custom.style.display='block'; custom.focus(); }
        else { custom.style.display='none'; custom.value=''; }
    });

    function getLocation(){
        const sel = document.getElementById('gp-location-select').value;
        if(sel === '__custom__') return document.getElementById('gp-location-custom').value.trim();
        return sel;
    }

    const searchBtn  = document.getElementById('gp-search-btn');
    const spinner    = document.getElementById('gp-spinner');
    const errorBox   = document.getElementById('gp-error');
    const metaRow    = document.getElementById('gp-meta');
    const totalBadge = document.getElementById('gp-total-badge');
    const ctxLabel   = document.getElementById('gp-ctx-label');
    const table      = document.getElementById('gp-table');
    const tbody      = document.getElementById('gp-tbody');
    const emptyMsg   = document.getElementById('gp-empty');
    const placeholder= document.getElementById('gp-placeholder');
    const storeBtn   = document.getElementById('gp-store-btn');
    const storeMsg   = document.getElementById('gp-store-msg');
    const exportBtn  = document.getElementById('gp-export-btn');
    const selectAllBtn = document.getElementById('gp-select-all-btn');
    const checkAll   = document.getElementById('gp-check-all');
    const selectLabel= document.getElementById('gp-select-label');
    const detailsBtn = document.getElementById('gp-details-btn');
    const csrf       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let lastResults = [];

    const esc = str => { const d=document.createElement('div'); d.appendChild(document.createTextNode(str??'')); return d.innerHTML; };
    const stars = r => r ? '★'.repeat(Math.round(r))+'☆'.repeat(5-Math.round(r))+` <small>${r}</small>` : '—';
    const price = p => p != null ? '$'.repeat(parseInt(p)+1) : '—';

    function setLoading(on){
        searchBtn.disabled=on;
        spinner.style.display=on?'block':'none';
        searchBtn.textContent=on?'Searching…':'🔍 Search';
    }
    function showError(m){ errorBox.textContent=m; errorBox.style.display='block'; }
    function hideError(){ errorBox.style.display='none'; }
    function updateSelectLabel(){
        selectLabel.textContent=tbody.querySelectorAll('.gp-row-check:checked').length+' selected';
    }

    function buildRow(item, i){
        const tr = document.createElement('tr');
        const types = (item.types||[]).slice(0,3).map(t=>`<span class="tag">${esc(t.replace(/_/g,' '))}</span>`).join('');
        const statusCls = item.business_status==='OPERATIONAL'?'gp-status-op':'gp-status-cl';
        const statusLabel = (item.business_status||'—').replace(/_/g,' ');
        const openNow = item.open_now===true?'<span style="color:#16a34a;font-size:.75rem">Open</span>'
                       :item.open_now===false?'<span style="color:#dc2626;font-size:.75rem">Closed</span>':'—';
        const phone = item.formatted_phone_number||item.international_phone_number||'<span class="gp-num">—</span>';
        const web   = item.website?`<a href="${esc(item.website)}" target="_blank" rel="noopener" style="font-size:.75rem;color:#4285f4;word-break:break-all">${esc(new URL(item.website).hostname)}</a>`:'<span class="gp-num">—</span>';

        tr.innerHTML = `
            <td><input type="checkbox" class="gp-row-check" data-idx="${i}" checked></td>
            <td class="gp-num">${i+1}</td>
            <td><strong>${esc(item.name)}</strong>${item.vicinity?`<br><span class="gp-num">${esc(item.vicinity)}</span>`:''}</td>
            <td style="max-width:180px;font-size:.78rem">${esc(item.formatted_address||'—')}</td>
            <td style="font-size:.78rem">${esc(phone)}</td>
            <td>${web}</td>
            <td><span class="stars">${stars(item.rating)}</span>${item.user_ratings_total?`<br><span class="gp-num">${item.user_ratings_total} reviews</span>`:''}  </td>
            <td><span class="${statusCls}">${esc(statusLabel)}</span></td>
            <td style="max-width:140px">${types||'<span class="gp-num">—</span>'}</td>
            <td>${openNow}</td>
            <td>${esc(price(item.price_level))}</td>
        `;
        return tr;
    }

    function renderTable(data){
        tbody.innerHTML='';
        data.forEach((item,i)=>tbody.appendChild(buildRow(item,i)));
        table.style.display=data.length?'table':'none';
        emptyMsg.style.display=data.length?'none':'block';
        placeholder.style.display='none';
        metaRow.style.display='flex';
        updateSelectLabel();
        tbody.addEventListener('change', updateSelectLabel);
    }

    // Search
    searchBtn.addEventListener('click', async function(){
        const query    = document.getElementById('gp-query').value.trim();
        const location = getLocation();
        const type     = $('#gp-type').val()||'';
        const radius   = document.getElementById('gp-radius').value;

        if(!query && !location && !type){
            showError('Please enter a search query, select a city, or choose a business type.');
            return;
        }

        // Build a sensible query if blank
        const effectiveQuery = query || (type ? (type.replace(/_/g,' ')) : 'businesses');
        hideError();
        storeMsg.style.display='none';
        setLoading(true);
        metaRow.style.display='none';
        table.style.display='none';
        emptyMsg.style.display='none';
        placeholder.style.display='none';
        lastResults=[];

        try{
            const url = `{{ route('google.search') }}?query=${encodeURIComponent(effectiveQuery)}&location=${encodeURIComponent(location)}&type=${encodeURIComponent(type)}&radius=${radius}`;
            const res = await fetch(url,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const json = await res.json();
            if(!res.ok||json.error){ showError(json.error??'Request failed.'); placeholder.style.display='block'; return; }
            lastResults=json.data??[];
            totalBadge.textContent=json.total+' results';
            ctxLabel.textContent=effectiveQuery+(location?' in '+location:'')+(json.type&&json.type!=='All Types'?' · '+json.type:'');
            if(!lastResults.length){ emptyMsg.style.display='block'; metaRow.style.display='none'; }
            else renderTable(lastResults);
        }catch(err){
            showError('Network error: '+err.message);
            placeholder.style.display='block';
        }finally{ setLoading(false); }
    });

    document.getElementById('gp-query').addEventListener('keydown',e=>{ if(e.key==='Enter') searchBtn.click(); });

    // Check all
    checkAll.addEventListener('change',function(){
        tbody.querySelectorAll('.gp-row-check').forEach(b=>b.checked=this.checked);
        updateSelectLabel();
    });
    selectAllBtn.addEventListener('click',function(){
        const boxes=[...tbody.querySelectorAll('.gp-row-check')];
        const all=boxes.every(b=>b.checked);
        boxes.forEach(b=>b.checked=!all);
        checkAll.checked=!all;
        selectAllBtn.textContent=all?'Select All':'Deselect All';
        updateSelectLabel();
    });

    // Get Full Details for selected
    detailsBtn.addEventListener('click', async function(){
        const checked=[...tbody.querySelectorAll('.gp-row-check:checked')];
        const selected=checked.map(b=>lastResults[parseInt(b.dataset.idx)]).filter(Boolean);
        if(!selected.length){ alert('Select at least one place.'); return; }

        detailsBtn.disabled=true;
        detailsBtn.textContent='Fetching details…';

        for(let i=0;i<selected.length;i++){
            try{
                const res=await fetch(`{{ route('google.details') }}?place_id=${encodeURIComponent(selected[i].place_id)}`,{
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                });
                const json=await res.json();
                if(res.ok&&json.data){
                    // Merge full details into lastResults
                    const idx=lastResults.findIndex(r=>r.place_id===selected[i].place_id);
                    if(idx>-1) lastResults[idx]={...lastResults[idx],...json.data,...{search_query:lastResults[idx].search_query,search_location:lastResults[idx].search_location,search_type:lastResults[idx].search_type}};
                }
            }catch(e){}
        }
        renderTable(lastResults);
        detailsBtn.disabled=false;
        detailsBtn.textContent='🔎 Get Full Details';
        storeMsg.textContent='Full details fetched — now click 💾 Store Selected to save.';
        storeMsg.style.cssText='display:block;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:.6rem 1rem;border-radius:6px;font-size:.875rem;margin-bottom:.75rem';
    });

    // Store
    storeBtn.addEventListener('click', async function(){
        const checked=[...tbody.querySelectorAll('.gp-row-check:checked')];
        const selected=checked.map(b=>lastResults[parseInt(b.dataset.idx)]).filter(Boolean);
        if(!selected.length){ storeMsg.textContent='Select at least one place.'; storeMsg.style.cssText='display:block;background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:.6rem 1rem;border-radius:6px;font-size:.875rem;margin-bottom:.75rem'; return; }

        storeBtn.disabled=true;
        try{
            const res=await fetch('{{ route('google.store') }}',{
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({places:selected}),
            });
            const json=await res.json();
            storeMsg.textContent=json.message??(res.ok?'Saved!':'Error.');
            storeMsg.style.cssText=res.ok
                ?'display:block;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:.6rem 1rem;border-radius:6px;font-size:.875rem;margin-bottom:.75rem'
                :'display:block;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.6rem 1rem;border-radius:6px;font-size:.875rem;margin-bottom:.75rem';
        }catch(err){
            storeMsg.textContent='Network error: '+err.message;
            storeMsg.style.cssText='display:block;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.6rem 1rem;border-radius:6px;font-size:.875rem;margin-bottom:.75rem';
        }finally{ storeBtn.disabled=false; }
    });

    // CSV
    exportBtn.addEventListener('click',function(){
        if(!lastResults.length)return;
        const rows=[['#','Name','Address','Phone','Website','Rating','Reviews','Status','Types','Open Now','Price Level','Lat','Lng','Place ID']];
        lastResults.forEach((item,i)=>{
            rows.push([i+1,item.name,item.formatted_address,item.formatted_phone_number||item.international_phone_number,item.website,item.rating,item.user_ratings_total,item.business_status,(item.types||[]).join('|'),item.open_now,item.price_level,item.lat,item.lng,item.place_id]);
        });
        const csv=rows.map(r=>r.map(v=>`"${String(v??'').replace(/"/g,'""')}"`).join(',')).join('\n');
        const a=document.createElement('a');
        a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
        a.download='google-places.csv';
        a.click();
    });
});
</script>
@endpush
@endsection
