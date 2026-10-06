document.querySelector('[data-menu]')?.addEventListener('click',()=>document.querySelector('#sidebar')?.classList.toggle('open'));
document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm||'Are you sure?'))event.preventDefault()}));
document.querySelectorAll('[data-select-all]').forEach(master=>master.addEventListener('change',()=>{document.querySelectorAll('input[name="ids[]"]').forEach(box=>box.checked=master.checked)}));
