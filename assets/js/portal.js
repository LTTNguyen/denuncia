async function loadCategories(companyId, selectId){
  try{
    const res = await fetch(`${window.PORTAL_BASE}/api/categories.php?company_id=${encodeURIComponent(companyId)}`);
    const data = await res.json();
    const sel = document.getElementById(selectId);
    if(!sel) return;

    sel.innerHTML = '';

    // Add default option
    const opt0 = document.createElement('option');
    opt0.value = "0";
    opt0.textContent = "— Selecciona —";
    sel.appendChild(opt0);

    (data.categories || []).forEach(c=>{
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      sel.appendChild(opt);
    });

    // enable/disable select depending on results
    sel.disabled = !(data.categories && data.categories.length);
  }catch(e){
    console.error(e);
  }
}

/**
 * Auto calendar (flatpickr) for inputs with class ".js-date"
 */
document.addEventListener('DOMContentLoaded', () => {
  // If flatpickr is not loaded, do nothing
  if (!window.flatpickr) return;

  // Spanish locale (depends on how you included flatpickr locale file)
  const localeEs = (window.flatpickr.l10ns && window.flatpickr.l10ns.es)
    ? window.flatpickr.l10ns.es
    : null;

  document.querySelectorAll('.js-date').forEach(el => {
    window.flatpickr(el, {
      locale: localeEs || undefined,
      dateFormat: "Y-m-d",     // value sent to PHP
      altInput: true,
      altFormat: "d/m/Y",      // what user sees
      showMonths: 2,
      allowInput: true
    });
  });
});
