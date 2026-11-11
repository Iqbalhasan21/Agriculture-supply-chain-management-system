/* FarmDirect demo interactions: table actions, modals, exports, notifications.
   Changes are saved in this browser only (localStorage). The backend stage replaces this with the database. */
(function () {
  'use strict';
  var VER = 'v5', P = 'fd:' + VER + ':';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); };
  var slug = function (s) { return String(s).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); };
  var toast = function (m) { (window.fdToast || function () {})(m); };
  function lsGet(k, d) { try { var v = localStorage.getItem(P + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } }
  function lsSet(k, v) { try { localStorage.setItem(P + k, JSON.stringify(v)); } catch (e) {} }
  function today() { return new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); }
  var ICON = { View: 'eye', Edit: 'pencil-square', Delete: 'trash', Approve: 'check-lg', Reject: 'x-lg', Assign: 'person-plus', Track: 'geo-alt', Download: 'download', Restore: 'arrow-counterclockwise', Move: 'arrow-left-right', 'Update Stock': 'arrow-repeat', Activate: 'toggle-on', Deactivate: 'toggle-off', Cancel: 'x-circle' };
  var FAMILIES = [['Active', 'Inactive'], ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'], ['In Stock', 'Low Stock', 'Out of Stock'], ['Pending', 'Approved', 'Rejected'], ['Completed', 'Pending', 'Failed', 'Refunded']];
  var ROLES = ['Admin', 'Manager', 'Farmer', 'Client'], MEN = ['Karim Mia', 'Salam Khan', 'Jamal Uddin'], WH = ['Warehouse A', 'Warehouse B', 'Warehouse C'];

  /* ---------- modal ---------- */
  var dlg;
  function ensureDialog() {
    if (dlg) return dlg;
    dlg = document.createElement('dialog'); dlg.id = 'fd-modal'; dlg.setAttribute('aria-labelledby', 'fd-modal-title');
    dlg.innerHTML = '<form id="fd-modal-form" novalidate><h2 id="fd-modal-title"></h2><div id="fd-modal-body"></div><div class="row gap-12" style="justify-content:flex-end;margin-top:16px"><button type="button" class="btn btn-outline" data-modal-cancel>Cancel</button><button type="submit" class="btn" id="fd-modal-ok">OK</button></div></form>';
    document.body.appendChild(dlg);
    dlg.addEventListener('click', function (e) { if (e.target.closest('[data-modal-cancel]')) dlg.close(); });
    $('#fd-modal-form', dlg).addEventListener('submit', function (e) {
      e.preventDefault();
      var f = e.target; if (f.reportValidity && !f.reportValidity()) return;
      var vals = {}; $$('[name]', f).forEach(function (i) { vals[i.name] = i.value; });
      if (dlg._ok && dlg._ok(vals) === false) return;
      dlg.close();
    });
    return dlg;
  }
  function modal(title, body, ok, label, danger) {
    ensureDialog(); $('#fd-modal-title', dlg).textContent = title; $('#fd-modal-body', dlg).innerHTML = body;
    var b = $('#fd-modal-ok', dlg); b.textContent = label || 'Save'; b.className = 'btn' + (danger ? ' btn-danger' : '');
    dlg._ok = ok || null; dlg.showModal(); var f = $('input,select,textarea', dlg); if (f) f.focus();
  }
  function field(label, name, value, type) { return '<div class="form-group"><label>' + esc(label) + '</label><input name="' + name + '" type="' + (type || 'text') + '" value="' + esc(value || '') + '" required></div>'; }
  function selectField(label, name, options, value) { return '<div class="form-group"><label>' + esc(label) + '</label><select name="' + name + '">' + options.map(function (o) { return '<option' + (o === value ? ' selected' : '') + '>' + esc(o) + '</option>'; }).join('') + '</select></div>'; }

  /* ---------- table helpers ---------- */
  function tablesIn() { return $$('.dash-content table'); }
  function headers(t) { return $$('thead th', t).map(function (h) { return h.textContent.trim(); }); }
  function col(t, name) { return headers(t).indexOf(name); }
  function cellText(td) { var u = $('.table-user span:last-child', td); return (u || td).textContent.replace(/\s+/g, ' ').trim(); }
  function setCell(td, text) { var u = $('.table-user span:last-child', td); (u || td).textContent = text; }
  function setBadge(td, text) { var b = $('.badge', td); if (!b) return; b.textContent = text; b.className = 'badge badge-' + slug(text); }
  function actionLabels(tr) { return $$('[data-row-action]', tr).map(function (a) { return a.dataset.rowAction; }); }
  function setActions(tr, labels) {
    var box = $('.actions', tr); if (!box) return;
    box.innerHTML = labels.map(function (l) { return '<a href="#" data-row-action="' + esc(l) + '" title="' + esc(l) + '"' + (/Reject|Delete|Cancel|Remove/.test(l) ? ' style="color:var(--r)"' : '') + '><i class="bi bi-' + (ICON[l] || 'three-dots') + '"></i> ' + esc(l) + '</a>'; }).join('');
  }
  function persist(t) {
    var i = tablesIn().indexOf(t); if (i < 0 || t.hasAttribute('data-real')) return;
    var tb = $('tbody', t).cloneNode(true); $$('[hidden]', tb).forEach(function (n) { n.removeAttribute('hidden'); });
    lsSet('tbl:' + location.pathname + ':' + i, tb.innerHTML);
  }
  function restoreAll() { tablesIn().forEach(function (t, i) { if (t.hasAttribute('data-real')) return; var h = lsGet('tbl:' + location.pathname + ':' + i, null); if (h !== null) $('tbody', t).innerHTML = h; }); }
  function familyFor(vals) { for (var i = 0; i < FAMILIES.length; i++) if (vals.every(function (v) { return FAMILIES[i].indexOf(v) !== -1; })) return FAMILIES[i]; return null; }
  function distinct(t, c) { var s = []; $$('tbody tr', t).forEach(function (r) { var v = cellText(r.children[c]); if (v && s.indexOf(v) === -1) s.push(v); }); return s; }
  function tableById(id) { return $('[data-table="' + id + '"]'); }
  function updateRequestsBadge() { var t = tableById('requests'); var b = $('[data-pending-count]'); if (t && b) b.textContent = $$('tbody tr', t).length + ' pending'; }
  function fillRow(tr, cells, t) {
    cells.forEach(function (v, i) {
      var td = tr.children[i]; if (!td || !v) return;
      if ($('.badge', td)) setBadge(td, v);
      else { var im = $('img', td); if (im) { var sp = document.createElement('span'); sp.className = $('.thumb-sm', td) ? 'thumb-sm' : 'avatar'; sp.textContent = $('.thumb-sm', td) ? '📦' : ''; im.replaceWith(sp); } setCell(td, v); }
    });
    tr.hidden = false;
  }

  /* ---------- row actions ---------- */
  function detailHtml(t, tr) {
    return '<dl class="detail-list">' + headers(t).map(function (h, i) { if (/^actions?$/i.test(h)) return ''; return '<dt>' + esc(h) + '</dt><dd>' + esc(cellText(tr.children[i])) + '</dd>'; }).join('') + '</dl>';
  }
  function syncActions(t, tr) {
    var sc = col(t, 'Status'); if (sc < 0) return;
    var st = cellText(tr.children[sc]), L = actionLabels(tr);
    if (L.indexOf('Activate') !== -1 || L.indexOf('Deactivate') !== -1) { L = L.filter(function (x) { return x !== 'Activate' && x !== 'Deactivate'; }); if (st === 'Active') L.push('Deactivate'); else if (st === 'Inactive') L.push('Activate'); setActions(tr, L); }
  }
  function editRow(t, tr) {
    var hs = headers(t), html = '', names = [];
    hs.forEach(function (h, i) {
      if (/^actions?$/i.test(h)) return;
      var td = tr.children[i], v = cellText(td), n = 'c' + i;
      if ($('.badge', td)) html += selectField(h, n, familyFor(distinct(t, i)) || [v], v);
      else if (h === 'Role') html += selectField(h, n, ROLES, v);
      else if (h === 'Warehouse') html += selectField(h, n, WH, v);
      else html += field(h, n, v);
      names.push([n, i]);
    });
    modal('Edit', html, function (vals) {
      names.forEach(function (p) { var td = tr.children[p[1]]; if ($('.badge', td)) setBadge(td, vals[p[0]]); else setCell(td, vals[p[0]]); });
      syncActions(t, tr); persist(t); toast('Changes saved');
    }, 'Save changes');
  }
  function newUserRow(t, name) {
    var tr = $('tbody tr', t).cloneNode(true), cells = headers(t).map(function (h) {
      if (h === 'Name') return name; if (h === 'Email') return slug(name).replace(/-/g, '.') + '@mail.com'; if (h === 'Role') return 'Farmer'; if (h === 'Joined') return today(); if (h === 'Status') return 'Active'; return '';
    });
    fillRow(tr, cells, t); setActions(tr, ['Edit', 'Deactivate']); return tr;
  }
  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-row-action]'); if (!a) return;
    e.preventDefault();
    var tr = a.closest('tr'), t = a.closest('table'), label = a.dataset.rowAction, sc = col(t, 'Status'), name = cellText(tr.children[0]);
    if (label === 'View' || label === 'Track') return modal(name, detailHtml(t, tr), null, 'Close');
    if (label === 'Edit') return editRow(t, tr);
    if (label === 'Delete' || label === 'Remove') return modal('Delete', '<p>Delete <b>' + esc(name) + '</b>? This cannot be undone.</p>', function () { tr.remove(); persist(t); toast(name + ' deleted'); }, 'Delete', true);
    if (label === 'Activate' || label === 'Deactivate') { var to = label === 'Activate' ? 'Active' : 'Inactive'; setBadge(tr.children[sc], to); syncActions(t, tr); persist(t); return toast(name + ' is now ' + to.toLowerCase()); }
    if (label === 'Approve' || label === 'Reject') {
      var into = t.dataset.approveInto;
      if (into) {
        if (label === 'Approve') { var ut = tableById(into); if (ut) { $('tbody', ut).appendChild(newUserRow(ut, name)); persist(ut); } }
        tr.remove(); persist(t); updateRequestsBadge(); return toast(name + (label === 'Approve' ? ' approved. Account created.' : ' rejected'));
      }
      if (sc >= 0) { setBadge(tr.children[sc], label === 'Approve' ? 'Approved' : 'Rejected'); setActions(tr, ['View']); persist(t); return toast(name + (label === 'Approve' ? ' approved' : ' rejected')); }
    }
    if (label === 'Cancel') { if (sc >= 0) { setBadge(tr.children[sc], 'Cancelled'); setActions(tr, ['View']); persist(t); toast(name + ' cancelled'); } return; }
    if (label === 'Assign') {
      return modal('Assign delivery', '<p>Order <b>' + esc(name) + '</b></p>' + selectField('Delivery man', 'man', MEN, MEN[1]), function (v) {
        var dc = col(t, 'Delivery Man');
        if (dc >= 0) { tr.children[dc].textContent = v.man; if (sc >= 0 && cellText(tr.children[sc]) === 'Pending') setBadge(tr.children[sc], 'Processing'); setActions(tr, ['View']); } else tr.remove();
        persist(t); toast('Assigned to ' + v.man);
      }, 'Assign');
    }
    if (label === 'Update Stock') {
      var stc = col(t, 'Stock'), cur = stc >= 0 ? cellText(tr.children[stc]) : '0', m = /^(\d+)\s*(.*)$/.exec(cur) || [0, '0', ''];
      return modal('Update stock', '<p><b>' + esc(name) + '</b></p>' + field('New stock quantity', 'qty', m[1], 'number'), function (v) {
        var q = Math.max(0, parseInt(v.qty) || 0); tr.children[stc].textContent = q + (m[2] ? ' ' + m[2] : '');
        if (sc >= 0) setBadge(tr.children[sc], q === 0 ? 'Out of Stock' : q < 10 ? 'Low Stock' : 'In Stock'); persist(t); toast('Stock updated to ' + q);
      }, 'Update');
    }
    if (label === 'Move') return modal('Move stock', '<p><b>' + esc(name) + '</b></p>' + selectField('Move to', 'wh', WH, 'Warehouse B'), function (v) { var wc = col(t, 'Warehouse'); if (wc >= 0) tr.children[wc].textContent = v.wh; persist(t); toast('Moved to ' + v.wh); }, 'Move');
    if (label === 'Download') return download(name, '-- FarmDirect demo backup\n-- File: ' + name + '\n-- Created: ' + new Date().toISOString() + '\n' + JSON.stringify(window.FD_DATA || {}, null, 2), 'text/plain');
    if (label === 'Restore') return modal('Restore database', '<p>Restore from <b>' + esc(name) + '</b>? Current data will be replaced.</p>', function () { toast('Database restored from ' + name + ' (demo)'); }, 'Restore', true);
  });

  /* ---------- toolbar buttons: data-do="add:users" etc ---------- */
  function csvOf(hs, rows) { var q = function (v) { v = String(v == null ? '' : v); return /[",\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; }; return [hs].concat(rows).map(function (r) { return r.map(q).join(','); }).join('\r\n'); }
  function download(name, text, type) {
    var blob = new Blob(['\ufeff' + text], { type: (type || 'text/csv') + ';charset=utf-8' }), url = URL.createObjectURL(blob), a = document.createElement('a');
    a.href = url; a.download = name; document.body.appendChild(a); a.click(); a.remove(); setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
  }
  function tableData(t) { var hs = headers(t), keep = hs.map(function (h) { return !/^actions?$/i.test(h); }); return { headers: hs.filter(function (h, i) { return keep[i]; }), rows: $$('tbody tr', t).map(function (r) { return $$('td', r).map(function (td) { return cellText(td); }).filter(function (v, i) { return keep[i]; }); }) }; }
  function printTable(title, d) {
    var w = window.open('', '_blank'); if (!w) { toast('Allow pop-ups to print or save as PDF'); return; }
    w.document.write('<title>' + esc(title) + '</title><style>body{font-family:Arial,sans-serif;padding:24px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:6px 8px;font-size:12px;text-align:left}th{background:#eee}</style><h2>' + esc(title) + '</h2><p>Generated ' + new Date().toLocaleString() + '</p><table><tr>' + d.headers.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr>' + d.rows.map(function (r) { return '<tr>' + r.map(function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>'; }).join('') + '</table>');
    w.document.close(); w.focus(); w.print();
  }
  function addRowModal(id) {
    var t = tableById(id); if (!t) return toast('Nothing to add to here');
    var hs = headers(t), html = '', first = $('tbody tr', t);
    hs.forEach(function (h, i) {
      if (/^actions?$/i.test(h)) return;
      var td = first && first.children[i], v = /^(Joined|Created|Date)$/.test(h) ? today() : '';
      if (td && $('.badge', td)) html += selectField(h, 'c' + i, familyFor(distinct(t, i)) || ['Active'], distinct(t, i)[0]);
      else if (h === 'Role') html += selectField(h, 'c' + i, ROLES, 'Client');
      else if (h === 'Warehouse') html += selectField(h, 'c' + i, WH, WH[0]);
      else html += field(h, 'c' + i, v);
    });
    modal('Add new', html, function (vals) {
      var tr = first.cloneNode(true); fillRow(tr, hs.map(function (h, i) { return /^actions?$/i.test(h) ? '' : vals['c' + i]; }), t);
      $('tbody', t).insertBefore(tr, $('tbody', t).firstChild); syncActions(t, tr); persist(t); toast('Added successfully');
    }, 'Add');
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-do]'); if (!b) return;
    var parts = b.dataset.do.split(':'), kind = parts[0], arg = parts.slice(1).join(':');
    if (kind === 'add') addRowModal(arg);
    else if (kind === 'export') { var t = tableById(arg) || $('.dash-content table'), d = tableData(t); download(arg + '-' + new Date().toISOString().slice(0, 10) + '.csv', csvOf(d.headers, d.rows)); toast('CSV downloaded'); }
    else if (kind === 'info') toast(arg);
    else if (kind === 'approveall') { var tt = tableById(arg), sc = col(tt, 'Status'), n = 0; $$('tbody tr', tt).forEach(function (r) { if (cellText(r.children[sc]) === 'Pending') { setBadge(r.children[sc], 'Approved'); setActions(r, ['View']); n++; } }); persist(tt); toast(n ? n + ' products approved' : 'No pending products'); }
    else if (kind === 'backup') {
      var bt = tableById('backups'), tr = $('tbody tr', bt).cloneNode(true), d2 = new Date();
      var nm = 'backup-' + d2.toISOString().slice(0, 10) + '-' + String(d2.getHours()).padStart(2, '0') + String(d2.getMinutes()).padStart(2, '0') + '.sql';
      fillRow(tr, [nm, today() + ', ' + d2.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), '24 MB', 'Manual'], bt); $('tbody', bt).insertBefore(tr, $('tbody', bt).firstChild); persist(bt); toast('Backup created: ' + nm);
    }
    else if (kind === 'addwh') modal('Add warehouse', field('Warehouse name', 'n', 'Warehouse D') + field('District', 'd', 'Dhaka'), function (v) { var g = $('.wh-grid'), c = document.createElement('div'); c.className = 'card stack gap-8'; c.innerHTML = '<div class="row"><h3>' + esc(v.n) + '</h3><b style="color:#1E7B34">0% full</b></div><span class="muted"><i class="bi bi-geo-alt"></i> ' + esc(v.d) + '</span><div class="bar wh-bar"><i style="width:0%;background:#1E7B34"></i></div><b>0 items</b>'; g.appendChild(c); toast('Warehouse added'); }, 'Add');
    else if (kind === 'report') generate(b.dataset.type, b.dataset.format || 'Excel');
  });

  /* ---------- reports ---------- */
  var NAMES = { sales: 'Sales Report', orders: 'Orders Report', users: 'Users Report', inventory: 'Inventory Report', earnings: 'Farmer Earnings', delivery: 'Delivery Report', transactions: 'Transactions Report' };
  function generate(type, format, period) {
    var D = window.FD_DATA && window.FD_DATA[type]; if (!D) return toast('No data for this report');
    var stamp = new Date().toISOString().slice(0, 10), title = NAMES[type] || type;
    if (/pdf/i.test(format)) printTable(title, D); else download(type + '-report-' + stamp + '.csv', csvOf(D.headers, D.rows));
    var rt = tableById('recent');
    if (rt) { var tr = $('tbody tr', rt).cloneNode(true); fillRow(tr, [title, period || 'This month', /pdf/i.test(format) ? 'PDF' : 'Excel', today()], rt); $('tbody', rt).insertBefore(tr, $('tbody', rt).firstChild); persist(rt); }
    toast(title + ' generated');
  }
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('[data-report-form]');
    if (f) { e.preventDefault(); var d = new FormData(f), key = Object.keys(NAMES).filter(function (k) { return NAMES[k] === d.get('type'); })[0] || 'sales'; generate(key, d.get('format'), (d.get('from') || d.get('to')) ? (d.get('from') || '…') + ' to ' + (d.get('to') || '…') : 'This month'); return; }
    var r = e.target.closest('[data-restore-form]');
    if (r) { e.preventDefault(); var file = $('input[type=file]', r).files[0]; if (!file) return toast('Choose a backup file first'); modal('Restore database', '<p>Restore from <b>' + esc(file.name) + '</b>? Current data will be replaced.</p>', function () { toast('Database restored from ' + file.name + ' (demo)'); }, 'Restore', true); }
  });

  /* ---------- forms: persist, add-to-table, password check ---------- */
  function serialize(f) { var o = {}; $$('input,select,textarea', f).forEach(function (i) { if (!i.name || /password|_token/.test(i.name) || i.type === 'file') return; o[i.name + (i.type === 'radio' ? ':' + i.value : '')] = (i.type === 'checkbox' || i.type === 'radio') ? i.checked : i.value; }); return o; }
  function initForms() { $$('form[data-persist]').forEach(function (f, n) { var o = lsGet('form:' + location.pathname + ':' + n, null); if (!o) return; $$('input,select,textarea', f).forEach(function (i) { var k = i.name + (i.type === 'radio' ? ':' + i.value : ''); if (!(k in o)) return; if (i.type === 'checkbox' || i.type === 'radio') i.checked = o[k]; else i.value = o[k]; }); }); }
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('form'); if (!f) return;
    var pw = f.querySelector('[name=password]'), pc = f.querySelector('[name=password_confirmation]');
    if (pw && pc && f.classList.contains('demo-form')) {
      if (pw.value.length < 8) { e.preventDefault(); e.stopImmediatePropagation(); return toast('Password must be at least 8 characters'); }
      if (pw.value !== pc.value) { e.preventDefault(); e.stopImmediatePropagation(); return toast('Passwords do not match'); }
      setTimeout(function () { f.reset(); }, 0);
    }
    if (f.hasAttribute('data-persist')) lsSet('form:' + location.pathname + ':' + $$('form[data-persist]').indexOf(f), serialize(f));
    var target = f.dataset.addTo, queue = f.dataset.queue;
    if (target || queue) {
      e.preventDefault(); e.stopImmediatePropagation();
      var d = {}; $$('[name]', f).forEach(function (i) { d[i.name] = i.value; });
      var build = function (map) { return map.split('|').map(function (x) { return x.replace(/@(\w+)/g, function (_, k) { return d[k] || ''; }); }); };
      if (queue) JSON.parse(queue).forEach(function (q) { var list = lsGet('queue:' + q.page, []); list.push(build(q.map)); lsSet('queue:' + q.page, list); });
      var t = target && tableById(target);
      if (t) { var tr = $('tbody tr', t).cloneNode(true); fillRow(tr, build(f.dataset.map), t); $('tbody', t).insertBefore(tr, $('tbody', t).firstChild); syncActions(t, tr); persist(t); }
      toast(f.dataset.toast || 'Added'); f.reset();
    }
  }, true);

  /* ---------- notifications: click to read, persist, badges ---------- */
  function role() { return document.body.dataset.role || 'client'; }
  function readSet() { return lsGet('read:' + role(), []); }
  function applyNotifs() {
    var read = readSet(), items = $$('.notif[data-idx]'), unread;
    items.forEach(function (n) { if (read.indexOf(n.dataset.idx) !== -1) n.classList.remove('unread'); });
    if (items.length) unread = items.filter(function (n) { return n.classList.contains('unread'); }).length;
    else { var link = $('[data-notif-badge]'); if (!link) return; var ids = (link.dataset.unreadIdx || '').split(',').filter(Boolean); unread = ids.filter(function (i) { return read.indexOf(i) === -1; }).length; }
    $$('[data-notif-badge]').forEach(function (b) { b.textContent = unread; b.hidden = !unread; });
    $$('.tabs[data-filter] .tab').forEach(function (t) { if (/^unread/i.test(t.textContent)) t.textContent = 'Unread (' + unread + ')'; });
  }
  function markRead(idxs) { var r = readSet(); idxs.forEach(function (i) { if (r.indexOf(i) === -1) r.push(i); }); lsSet('read:' + role(), r); applyNotifs(); }
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-mark-read]')) { e.preventDefault(); e.stopImmediatePropagation(); markRead($$('.notif[data-idx]').map(function (n) { return n.dataset.idx; })); return toast('All notifications marked as read'); }
    var n = e.target.closest('.notif[data-idx]'); if (n && n.classList.contains('unread')) markRead([n.dataset.idx]);
  }, true);
  document.addEventListener('keydown', function (e) { var n = e.target.closest && e.target.closest('.notif[data-idx]'); if (n && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); n.click(); } });

  /* ---------- reset ---------- */
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-reset-demo]')) return; e.preventDefault();
    Object.keys(localStorage).filter(function (k) { return k.indexOf('fd:') === 0; }).forEach(function (k) { localStorage.removeItem(k); });
    toast('Demo data reset'); setTimeout(function () { location.reload(); }, 600);
  });

  /* ---------- init ---------- */
  function init() {
    restoreAll(); initForms(); applyNotifs(); updateRequestsBadge();
    var q = lsGet('queue:' + location.pathname, []), t = $('.dash-content table[data-table]');
    if (q.length && t) { q.forEach(function (row) { var tr = $('tbody tr', t).cloneNode(true); fillRow(tr, row, t); $('tbody', t).insertBefore(tr, $('tbody', t).firstChild); }); lsSet('queue:' + location.pathname, []); persist(t); }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
