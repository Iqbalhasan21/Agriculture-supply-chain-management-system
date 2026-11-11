(function(){
  function toast(msg){var t=document.createElement('div');t.className='toast';t.textContent=msg;document.body.appendChild(t);setTimeout(function(){t.remove()},2200);}
  window.fdToast=toast;
  document.addEventListener('click',function(e){
    var accountToggle=e.target.closest('[data-account-toggle]');
    if(accountToggle){
      var accountMenu=accountToggle.closest('[data-account-menu]');
      var accountDropdown=accountMenu.querySelector('[data-account-dropdown],.account-dropdown');
      var willOpen=accountDropdown.hidden;
      document.querySelectorAll('[data-account-menu]').forEach(function(menu){
        var dropdown=menu.querySelector('[data-account-dropdown],.account-dropdown');
        var trigger=menu.querySelector('[data-account-toggle]');
        dropdown.hidden=true; trigger.setAttribute('aria-expanded','false');
      });
      accountDropdown.hidden=!willOpen;
      accountToggle.setAttribute('aria-expanded',willOpen?'true':'false');
      return;
    }
    document.querySelectorAll('[data-account-menu]').forEach(function(menu){
      if(!menu.contains(e.target)){
        menu.querySelector('.account-dropdown').hidden=true;
        menu.querySelector('[data-account-toggle]').setAttribute('aria-expanded','false');
      }
    });
    var t=e.target.closest('[data-toggle]');
    if(t){var el=document.querySelector(t.dataset.toggle);if(el)el.classList.toggle('open');}
    var a=e.target.closest('.demo-action');
    if(a){e.preventDefault();toast(a.dataset.toast||'Done (demo)');}
    var q=e.target.closest('[data-qty]');
    if(q){var i=q.parentElement.querySelector('input');i.value=Math.max(1,(parseInt(i.value)||1)+parseInt(q.dataset.qty));}
    var tab=e.target.closest('[data-tab]');
    if(tab){tab.closest('.tabs').querySelectorAll('.tab').forEach(function(x){x.classList.remove('active')});tab.classList.add('active');
      document.querySelectorAll('[data-pane]').forEach(function(p){p.hidden=p.dataset.pane!==tab.dataset.tab});}
  });
  document.addEventListener('keydown',function(e){
    if(e.key!=='Escape')return;
    document.querySelectorAll('[data-account-menu]').forEach(function(menu){
      var dropdown=menu.querySelector('.account-dropdown');
      if(!dropdown.hidden){dropdown.hidden=true;menu.querySelector('[data-account-toggle]').setAttribute('aria-expanded','false');menu.querySelector('[data-account-toggle]').focus();}
    });
  });
  document.addEventListener('submit',function(e){var f=e.target.closest('.demo-form');if(f){e.preventDefault();toast(f.dataset.toast||'Saved (demo – backend comes in the next stage)');}});
})();

/* ---- working tabs, live search, mark-all-read (client side demo) ---- */
(function(){
  function rowsOf(scope){return scope.querySelectorAll('tbody tr, .notif');}
  function norm(label){
    var k=label.replace(/\s*\(.*?\)\s*/g,'').trim().toLowerCase();
    if(/^(admin|manager|farmer|client)s$/.test(k)) k=k.slice(0,-1);
    return k;
  }
  function applyFilter(scope,key){
    var shown=0;
    rowsOf(scope).forEach(function(r){
      var ok=true;
      if(key.indexOf('all')!==0){
        if(key==='unread') ok=r.classList.contains('unread');
        else if(r.dataset.cat) ok=r.dataset.cat===key;
        else ok=r.textContent.toLowerCase().indexOf(key)!==-1;
      }
      r.hidden=!ok; if(ok) shown++;
    });
    var e=scope.querySelector('[data-empty]'); if(e) e.hidden=shown!==0;
    return shown;
  }
  document.addEventListener('click',function(e){
    var tab=e.target.closest('.tabs[data-filter] .tab');
    if(tab){
      var tabs=tab.closest('.tabs'); tabs.querySelectorAll('.tab').forEach(function(x){x.classList.remove('active')}); tab.classList.add('active');
      var scope=tabs.parentElement.nextElementSibling; if(tabs.nextElementSibling&&tabs.nextElementSibling.matches('.card')) scope=tabs.nextElementSibling;
      if(scope) applyFilter(scope,norm(tab.textContent));
    }
  });
  document.addEventListener('keydown',function(e){ if((e.key==='Enter'||e.key===' ')&&e.target.matches('.tabs[data-filter] .tab')){e.preventDefault();e.target.click();} });
  document.addEventListener('input',function(e){
    var i=e.target.closest('[data-live-search]'); if(!i) return;
    var q=i.value.toLowerCase();
    document.querySelectorAll('.dash-content table tbody tr').forEach(function(r){ r.hidden = q && r.textContent.toLowerCase().indexOf(q)===-1; });
  });
})();

/* ---- cart (browser storage for the demo; the backend stage moves this to the database) ---- */
window.fdCart = (function () {
  var KEY = 'fd_cart';
  function read() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
  function write(a) { try { localStorage.setItem(KEY, JSON.stringify(a)); } catch (e) {} badge(); }
  function count() { return read().reduce(function (s, i) { return s + i.qty; }, 0); }
  function badge() { document.querySelectorAll('[data-cart-count]').forEach(function (e) { e.textContent = count(); }); }
  function add(item, qty) {
    var a = read(), f = a.find(function (x) { return String(x.id) === String(item.id); }), max = item.stock || 99;
    if (f) f.qty = Math.min(max, f.qty + qty); else a.push({ id: item.id, name: item.name, price: +item.price, unit: item.unit, image: item.image, emoji: item.emoji, district: item.district, stock: max, qty: Math.min(max, qty) });
    write(a);
  }
  function setQty(id, qty) { var a = read(); a.forEach(function (x) { if (String(x.id) === String(id)) x.qty = Math.max(1, Math.min(x.stock || 99, qty)); }); write(a); }
  function remove(id) { write(read().filter(function (x) { return String(x.id) !== String(id); })); }
  function clear() { write([]); }
  function totals() {
    var sub = read().reduce(function (s, i) { return s + i.price * i.qty; }, 0);
    var S = window.FD_SHIPPING || { charge: 40, free: 1000 };
    var delivery = (sub === 0 || sub >= S.free) ? 0 : S.charge;
    return { subtotal: sub, delivery: delivery, total: sub + delivery };
  }
  document.addEventListener('DOMContentLoaded', badge);
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-add-cart]'); if (!b) return;
    e.preventDefault();
    var qi = b.dataset.qtyInput && document.querySelector(b.dataset.qtyInput);
    var qty = Math.max(1, parseInt(qi ? qi.value : 1) || 1);
    add(b.dataset, qty);
    if (b.hasAttribute('data-buy-now')) { location.href = b.dataset.cartUrl; return; }
    window.fdToast(b.dataset.name + ' added to cart');
  });
  return { read: read, add: add, setQty: setQty, remove: remove, clear: clear, totals: totals, badge: badge, count: count };
})();

/* ---- back buttons and list filters ---- */
document.addEventListener('click', function (e) {
  var b = e.target.closest('[data-back]'); if (!b) return;
  e.preventDefault();
  if (history.length > 1 && document.referrer) history.back(); else location.href = b.dataset.fallback || '/';
});
(function () {
  function markRequiredFields() {
    document.querySelectorAll('label[for]').forEach(function (label) {
      var field = document.getElementById(label.htmlFor);
      if (!field || !field.required || field.disabled || label.querySelector('.required-indicator')) return;
      var marker = document.createElement('span');
      marker.className = 'required-indicator';
      marker.setAttribute('aria-label', 'required');
      marker.title = 'Required';
      var icon = document.createElement('i');
      icon.className = 'bi bi-star-fill';
      icon.setAttribute('aria-hidden', 'true');
      marker.append(icon);
      label.append(' ', marker);
    });
  }
  function syncSignupType() {
    var select = document.querySelector('[data-account-type-select]');
    if (!select) return;
    var farmer = select.value === 'farmer';
    var staff = select.value === 'manager' || select.value === 'admin';
    document.querySelectorAll('[data-farmer-only]').forEach(function (section) {
      section.hidden = !farmer;
      section.querySelectorAll('input,select,textarea').forEach(function (field) { field.disabled = !farmer; });
    });
    document.querySelectorAll('[data-staff-only]').forEach(function (note) { note.hidden = !staff; });
    markRequiredFields();
  }
  document.addEventListener('DOMContentLoaded', function () {
    syncSignupType();
    document.querySelectorAll('[data-account-type-select]').forEach(function (select) { select.addEventListener('change', syncSignupType); });
    markRequiredFields();
  });
})();
document.addEventListener('input', function (e) {
  var i = e.target.closest('[data-filter-list]'); if (!i) return;
  var q = i.value.toLowerCase();
  var rows = Array.from(document.querySelectorAll(i.dataset.filterList + ' > *'));
  var visible = 0;
  rows.forEach(function (r) { r.hidden = q && r.textContent.toLowerCase().indexOf(q) === -1; if (!r.hidden) visible++; });
  var empty = i.dataset.emptyState ? document.querySelector(i.dataset.emptyState) : null;
  if (empty) empty.hidden = visible > 0;
});
document.addEventListener('focusin', function (e) {
  var input = e.target.closest('[data-district-input]'); if (!input) return;
  var picker = input.closest('[data-district-picker]');
  var options = picker.querySelector('.district-picker-options');
  options.hidden = false; input.setAttribute('aria-expanded', 'true');
  filterDistrictOptions(picker, input.value);
});
document.addEventListener('input', function (e) {
  var input = e.target.closest('[data-district-input]'); if (!input) return;
  filterDistrictOptions(input.closest('[data-district-picker]'), input.value);
});
document.addEventListener('click', function (e) {
  var option = e.target.closest('[data-district-option]');
  if (option) {
    var picker = option.closest('[data-district-picker]');
    var input = picker.querySelector('[data-district-input]');
    input.value = option.dataset.districtOption;
    input.dispatchEvent(new Event('change', { bubbles: true }));
    input.focus();
    closeDistrictOptions(picker);
    return;
  }
  document.querySelectorAll('[data-district-picker]').forEach(function (picker) {
    if (!picker.contains(e.target)) closeDistrictOptions(picker);
  });
});
document.addEventListener('keydown', function (e) {
  var input = e.target.closest('[data-district-input]'); if (!input) return;
  var picker = input.closest('[data-district-picker]');
  if (e.key === 'Escape') closeDistrictOptions(picker);
  if (e.key === 'ArrowDown') { e.preventDefault(); picker.querySelector('.district-picker-option:not([hidden])')?.focus(); }
});
document.addEventListener('keydown', function (e) {
  var option = e.target.closest('[data-district-option]'); if (!option || !['ArrowDown', 'ArrowUp', 'Escape'].includes(e.key)) return;
  e.preventDefault();
  var picker = option.closest('[data-district-picker]');
  var options = Array.from(picker.querySelectorAll('.district-picker-option:not([hidden])'));
  if (e.key === 'Escape') { picker.querySelector('[data-district-input]').focus(); closeDistrictOptions(picker); return; }
  var index = options.indexOf(option) + (e.key === 'ArrowDown' ? 1 : -1);
  if (index >= 0 && index < options.length) options[index].focus();
  else if (e.key === 'ArrowUp' && index < 0) picker.querySelector('[data-district-input]').focus();
});
function filterDistrictOptions(picker, query) {
  var q = query.trim().toLowerCase(), visible = 0;
  picker.querySelectorAll('[data-district-option]').forEach(function (option) {
    option.hidden = q && option.dataset.districtOption.toLowerCase().indexOf(q) === -1;
    if (!option.hidden) visible++;
  });
  picker.querySelector('[data-district-empty]').hidden = visible > 0;
}
function closeDistrictOptions(picker) {
  if (!picker) return;
  picker.querySelector('.district-picker-options').hidden = true;
  picker.querySelector('[data-district-input]').setAttribute('aria-expanded', 'false');
}
document.addEventListener('input', function (e) {
  var range = e.target.closest('[data-max-price-range]'); if (!range) return;
  var output = document.querySelector('[data-max-price-output]');
  if (output) output.value = Number(range.value).toLocaleString('en-US');
});
document.addEventListener('change', function (e) {
  var range = e.target.closest('[data-max-price-range]'); if (!range) return;
  range.form?.requestSubmit();
});
document.addEventListener('change', function (e) {
  var input = e.target.closest('[data-profile-photo-input]');
  if (!input || !input.files || !input.files[0]) return;
  var control = document.querySelector('[data-profile-picture-control]');
  var preview = control?.querySelector('[data-profile-photo-preview]');
  if (!preview) return;
  var objectUrl = URL.createObjectURL(input.files[0]);
  preview.replaceChildren();
  var image = document.createElement('img');
  image.alt = 'Selected profile picture preview';
  image.src = objectUrl;
  preview.append(image);
  var full = document.querySelector('[data-profile-picture-full]');
  if (full) full.src = objectUrl;
  var empty = document.querySelector('[data-profile-picture-empty]');
  if (empty) empty.remove();
  var dialog = document.querySelector('[data-profile-picture-dialog]');
  if (dialog && !dialog.querySelector('[data-profile-picture-full]')) {
    var fullPreview = document.createElement('img');
    fullPreview.alt = 'Profile picture preview';
    fullPreview.dataset.profilePictureFull = '';
    fullPreview.src = objectUrl;
    dialog.append(fullPreview);
  }
});
document.addEventListener('click', function (e) {
  var trigger = e.target.closest('[data-profile-picture-trigger]');
  if (trigger) {
    var menu = trigger.parentElement.querySelector('[data-profile-picture-menu]');
    var opening = menu.hidden;
    document.querySelectorAll('[data-profile-picture-menu]').forEach(function (item) { item.hidden = true; });
    document.querySelectorAll('[data-profile-picture-trigger]').forEach(function (item) { item.setAttribute('aria-expanded', 'false'); });
    menu.hidden = !opening;
    trigger.setAttribute('aria-expanded', String(opening));
    return;
  }
  var see = e.target.closest('[data-see-profile-picture]');
  if (see) {
    var control = see.closest('[data-profile-picture-control]');
    var dialog = control.parentElement.querySelector('[data-profile-picture-dialog]');
    control.querySelector('[data-profile-picture-menu]').hidden = true;
    if (dialog?.showModal) dialog.showModal();
    return;
  }
  var choose = e.target.closest('[data-choose-profile-picture]');
  if (choose) {
    var box = choose.closest('[data-profile-picture-control]');
    box.querySelector('[data-profile-picture-menu]').hidden = true;
    box.querySelector('[data-profile-picture-trigger]').setAttribute('aria-expanded', 'false');
  }
  var close = e.target.closest('[data-close-profile-picture]');
  if (close) { close.closest('dialog').close(); return; }
  if (!e.target.closest('[data-profile-picture-control]')) {
    document.querySelectorAll('[data-profile-picture-menu]').forEach(function (item) { item.hidden = true; });
    document.querySelectorAll('[data-profile-picture-trigger]').forEach(function (item) { item.setAttribute('aria-expanded', 'false'); });
  }
});
