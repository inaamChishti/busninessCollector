<?php $__env->startSection('title','Wikidata API'); ?>
<?php $__env->startSection('subtitle','Search businesses by city and category via Wikidata'); ?>
<?php $__env->startSection('content'); ?>

<style>
.wd-form{display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.5rem}
.wd-form .field{display:flex;flex-direction:column;gap:.3rem;min-width:200px}
.wd-form label{font-size:.8rem;font-weight:600;color:var(--text-muted,#666);text-transform:uppercase;letter-spacing:.04em}
.wd-form select{padding:.5rem .75rem;border:1px solid var(--border,#ddd);border-radius:6px;font-size:.9rem;background:#fff;min-width:200px}
.wd-meta{display:flex;align-items:center;gap:1rem;margin-bottom:1rem;flex-wrap:wrap}
.wd-badge{background:var(--primary,#3b5bdb);color:#fff;padding:.25rem .65rem;border-radius:999px;font-size:.8rem;font-weight:600}
.wd-spinner{display:none;width:18px;height:18px;border:2px solid #ddd;border-top-color:var(--primary,#3b5bdb);border-radius:50%;animation:wd-spin .7s linear infinite}
@keyframes wd-spin{to{transform:rotate(360deg)}}
.wd-table-wrap{overflow-x:auto}
.wd-table{width:100%;border-collapse:collapse;font-size:.875rem}
.wd-table th{background:var(--surface,#f8f9fa);padding:.6rem 1rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted,#888);border-bottom:2px solid var(--border,#e5e7eb);white-space:nowrap}
.wd-table td{padding:.65rem 1rem;border-bottom:1px solid var(--border,#f0f0f0);vertical-align:middle}
.wd-table tr:hover td{background:var(--surface,#fafafa)}
.wd-table td a{color:var(--primary,#3b5bdb);text-decoration:none;font-size:.8rem;word-break:break-all}
.wd-table td a:hover{text-decoration:underline}
.wd-empty{text-align:center;padding:3rem;color:var(--text-muted,#888);font-size:.9rem}
.wd-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;display:none}
.wd-num{color:var(--text-muted,#aaa);font-size:.75rem}
</style>

<div class="panel">
    <div class="panel-head"><h2>Search Wikidata Businesses</h2></div>

    <div style="padding:1.25rem">

        
        <div class="wd-form">
            <div class="field">
                <label for="wd-city">City</label>
                <select id="wd-city">
                    <option value="">— Select city —</option>
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city); ?>"><?php echo e($city); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="field">
                <label for="wd-category">Category</label>
                <select id="wd-category">
                    <option value="">— Select category —</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button class="btn primary" id="wd-fetch-btn" type="button">Fetch Results</button>
            <div class="wd-spinner" id="wd-spinner"></div>
        </div>

        
        <div class="wd-error" id="wd-error"></div>

        
        <div class="wd-meta" id="wd-meta" style="display:none!important">
            <span id="wd-count-badge" class="wd-badge"></span>
            <span id="wd-context-label" style="font-size:.85rem;color:var(--text-muted,#666)"></span>
            <div style="margin-left:auto;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                <button class="btn success" id="wd-store-btn" type="button">💾 Store to Database</button>
                <button class="btn" id="wd-export-btn" type="button">⇩ Export CSV</button>
                <div class="wd-spinner" id="wd-store-spinner" style="display:none"></div>
            </div>
        </div>
        <div id="wd-store-msg" style="display:none;margin-bottom:.75rem;padding:.6rem 1rem;border-radius:6px;font-size:.875rem"></div>

        
        <div class="wd-table-wrap">
            <table class="wd-table" id="wd-table" style="display:none">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Business Name</th>
                        <th>Website</th>
                        <th>Phone</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody id="wd-tbody"></tbody>
            </table>
            <p class="wd-empty" id="wd-empty" style="display:none">No results found for this combination. Try a different city or category.</p>
            <p class="wd-empty" id="wd-placeholder">Select a city and category above, then click <strong>Fetch Results</strong>.</p>
        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const fetchBtn  = document.getElementById('wd-fetch-btn');
    const spinner   = document.getElementById('wd-spinner');
    const errorBox  = document.getElementById('wd-error');
    const metaRow   = document.getElementById('wd-meta');
    const badge     = document.getElementById('wd-count-badge');
    const ctxLabel  = document.getElementById('wd-context-label');
    const table     = document.getElementById('wd-table');
    const tbody     = document.getElementById('wd-tbody');
    const empty     = document.getElementById('wd-empty');
    const placeholder = document.getElementById('wd-placeholder');
    const exportBtn = document.getElementById('wd-export-btn');

    let lastResults = [];

    function setLoading(on) {
        fetchBtn.disabled = on;
        spinner.style.display = on ? 'block' : 'none';
        fetchBtn.textContent = on ? 'Fetching…' : 'Fetch Results';
    }

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }

    function hideError() {
        errorBox.style.display = 'none';
    }

    function buildRow(item, index) {
        const tr = document.createElement('tr');
        const website = item.website
            ? `<a href="${escHtml(item.website)}" target="_blank" rel="noopener">${escHtml(item.website)}</a>`
            : '<span class="wd-num">—</span>';
        const phone   = item.phone   ? escHtml(item.phone)   : '<span class="wd-num">—</span>';
        const address = item.address ? escHtml(item.address) : '<span class="wd-num">—</span>';

        tr.innerHTML = `
            <td class="wd-num">${index + 1}</td>
            <td><strong>${escHtml(item.name)}</strong></td>
            <td>${website}</td>
            <td>${phone}</td>
            <td>${address}</td>
        `;
        return tr;
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str ?? ''));
        return d.innerHTML;
    }

    function renderTable(data) {
        tbody.innerHTML = '';
        data.forEach((item, i) => tbody.appendChild(buildRow(item, i)));
        table.style.display   = data.length ? 'table' : 'none';
        empty.style.display   = data.length ? 'none'  : 'block';
        placeholder.style.display = 'none';
        metaRow.style.removeProperty('display');
    }

    fetchBtn.addEventListener('click', async function () {
        const city     = document.getElementById('wd-city').value;
        const category = document.getElementById('wd-category').value;

        if (!city || !category) {
            showError('Please select both a city and a category before fetching.');
            return;
        }

        hideError();
        setLoading(true);
        metaRow.style.setProperty('display', 'none', 'important');
        table.style.display = 'none';
        empty.style.display = 'none';
        placeholder.style.display = 'none';

        try {
            const res = await fetch(
                `<?php echo e(route('wikidata.fetch')); ?>?city=${encodeURIComponent(city)}&category=${encodeURIComponent(category)}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }
            );

            const json = await res.json();

            if (!res.ok || json.error) {
                showError(json.error ?? 'Something went wrong. Please try again.');
                placeholder.style.display = 'block';
                return;
            }

            lastResults = json.data ?? [];
            badge.textContent    = `${json.total} results`;
            ctxLabel.textContent = `${json.city} · ${json.category}`;
            renderTable(lastResults);

        } catch (err) {
            showError('Network error: ' + err.message);
            placeholder.style.display = 'block';
        } finally {
            setLoading(false);
        }
    });

    // CSV export
    exportBtn.addEventListener('click', function () {
        if (!lastResults.length) return;

        const city     = document.getElementById('wd-city').value;
        const category = document.getElementById('wd-category').value;
        const rows     = [['#', 'Name', 'Website', 'Phone', 'Address']];

        lastResults.forEach((item, i) => {
            rows.push([i + 1, item.name ?? '', item.website ?? '', item.phone ?? '', item.address ?? '']);
        });

        const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `wikidata-${city}-${category}.csv`.replace(/\s+/g, '-').toLowerCase();
        a.click();
        URL.revokeObjectURL(url);
    });

    // Store to DB
    const storeBtn     = document.getElementById('wd-store-btn');
    const storeSpinner = document.getElementById('wd-store-spinner');
    const storeMsg     = document.getElementById('wd-store-msg');

    storeBtn.addEventListener('click', async function () {
        if (!lastResults.length) return;
        const city     = document.getElementById('wd-city').value;
        const category = document.getElementById('wd-category').value;

        storeBtn.disabled = true;
        storeSpinner.style.display = 'block';
        storeMsg.style.display = 'none';

        try {
            const res = await fetch('<?php echo e(route('wikidata.store')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ city, category, data: lastResults }),
            });
            const json = await res.json();
            const ok = res.ok && json.status === 'success';
            storeMsg.textContent = json.message ?? (ok ? 'Saved!' : 'Error saving.');
            storeMsg.style.display = 'block';
            storeMsg.style.background = ok ? '#f0fdf4' : '#fef2f2';
            storeMsg.style.border     = ok ? '1px solid #bbf7d0' : '1px solid #fecaca';
            storeMsg.style.color      = ok ? '#15803d' : '#b91c1c';
        } catch (err) {
            storeMsg.textContent = 'Network error: ' + err.message;
            storeMsg.style.display = 'block';
            storeMsg.style.background = '#fef2f2';
            storeMsg.style.color = '#b91c1c';
        } finally {
            storeBtn.disabled = false;
            storeSpinner.style.display = 'none';
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\business collector - Copy\resources\views/wikidata/index.blade.php ENDPATH**/ ?>