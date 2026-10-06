<?php $__env->startSection('title','Wikidata — Get Emails'); ?>
<?php $__env->startSection('subtitle','Crawl stored business websites and extract email addresses'); ?>
<?php $__env->startSection('content'); ?>

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


<div class="panel" style="margin-bottom:1rem">
    <div style="padding:1.25rem">
        <form method="GET" action="<?php echo e(route('wikidata.emails')); ?>">
            <div class="filter-bar">
                <div class="field">
                    <label>City</label>
                    <select name="city">
                        <option value="">All cities</option>
                        <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c); ?>" <?php if(request('city')===$c): echo 'selected'; endif; ?>><?php echo e($c); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All categories</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php if(request('category')===$cat): echo 'selected'; endif; ?>><?php echo e($cat); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="field">
                    <label>Search name</label>
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Business name…">
                </div>
                <div class="field" style="justify-content:flex-end">
                    <button class="btn primary" type="submit">Filter</button>
                    <a class="btn" href="<?php echo e(route('wikidata.emails')); ?>" style="margin-top:.3rem">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="panel">
    <div class="panel-head">
        <h2>Stored Businesses <span class="wd-badge"><?php echo e($businesses->total()); ?></span></h2>
        <a href="<?php echo e(route('wikidata.index')); ?>" class="btn">← Back to Search</a>
    </div>
    <div style="padding:1.25rem">

        <?php if($businesses->isEmpty()): ?>
            <p style="text-align:center;padding:3rem;color:var(--text-muted,#888)">
                No businesses stored yet. <a href="<?php echo e(route('wikidata.index')); ?>">Fetch and store some first.</a>
            </p>
        <?php else: ?>
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
                    <?php $__currentLoopData = $businesses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $biz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr id="biz-row-<?php echo e($biz->id); ?>">
                        <td class="wd-num"><?php echo e($loop->iteration + ($businesses->currentPage()-1)*$businesses->perPage()); ?></td>
                        <td><strong><?php echo e($biz->name); ?></strong></td>
                        <td>
                            <span style="font-size:.8rem"><?php echo e($biz->city); ?></span>
                            <?php if($biz->category): ?><br><span class="wd-num"><?php echo e($biz->category); ?></span><?php endif; ?>
                        </td>
                        <td>
                            <?php if($biz->website): ?>
                                <a href="<?php echo e($biz->website); ?>" target="_blank" rel="noopener"><?php echo e(Str::limit($biz->website, 40)); ?></a>
                            <?php else: ?>
                                <span class="wd-num">—</span>
                            <?php endif; ?>
                        </td>
                        <td id="emails-cell-<?php echo e($biz->id); ?>">
                            <?php if($biz->emails_count > 0): ?>
                                <div class="email-chips">
                                    <?php $__currentLoopData = $biz->emails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $em): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="email-chip"><?php echo e($em->email); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <span class="wd-num">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($biz->website): ?>
                                <button class="btn primary"
                                    onclick="getEmails(<?php echo e($biz->id); ?>, '<?php echo e(addslashes($biz->website)); ?>', '<?php echo e(addslashes($biz->name)); ?>')"
                                    id="get-btn-<?php echo e($biz->id); ?>">
                                    Get Emails
                                </button>
                            <?php else: ?>
                                <span class="wd-num">No website</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        
        <?php if($businesses->hasPages()): ?>
        <div style="margin-top:1rem"><?php echo e($businesses->links()); ?></div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>


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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
            const res  = await fetch('<?php echo e(route('wikidata.emails.scrape')); ?>?website=' + encodeURIComponent(website) + '&business_id=' + businessId, {
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
            const res  = await fetch('<?php echo e(route('wikidata.emails.store')); ?>', {
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\business collector - Copy\resources\views/wikidata/emails.blade.php ENDPATH**/ ?>