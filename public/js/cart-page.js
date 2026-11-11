(function () {
  var root = document.getElementById('cart-app'); if (!root) return;
  var money = function (n) { return '৳ ' + n.toLocaleString('en-US'); };
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); };

  function render() {
    var items = fdCart.read(), t = fdCart.totals();
    $('#cart-empty').hidden = items.length > 0; $('#cart-full').hidden = items.length === 0;
    $('#cart-rows').innerHTML = items.map(function (i) {
      var pic = i.image ? '<img class="thumb-sm" style="width:52px;height:52px" src="' + esc(i.image) + '" alt="">' : '<span class="thumb-sm" style="width:52px;height:52px">' + esc(i.emoji || '📦') + '</span>';
      return '<tr><td><div class="table-user">' + pic + '<div><div class="bold">' + esc(i.name) + '</div><div class="muted"><i class="bi bi-geo-alt"></i> ' + esc(i.district || '') + ' · ' + esc(i.unit) + '</div></div></div></td>' +
        '<td>' + money(i.price) + '</td>' +
        '<td><div class="qty"><button type="button" data-cart-qty="-1" data-id="' + esc(i.id) + '" aria-label="Decrease ' + esc(i.name) + '">−</button><input type="text" value="' + i.qty + '" readonly aria-label="Quantity of ' + esc(i.name) + '"><button type="button" data-cart-qty="1" data-id="' + esc(i.id) + '" aria-label="Increase ' + esc(i.name) + '">+</button></div></td>' +
        '<td class="green bold">' + money(i.price * i.qty) + '</td>' +
        '<td><button type="button" data-cart-remove data-id="' + esc(i.id) + '" style="background:none;border:0;color:var(--r);cursor:pointer" aria-label="Remove ' + esc(i.name) + '"><i class="bi bi-trash"></i></button></td></tr>';
    }).join('');
    $('[data-count]').textContent = fdCart.count();
    $('[data-sub]').textContent = money(t.subtotal);
    $('[data-delivery]').textContent = t.delivery ? money(t.delivery) : 'Free';
    $('[data-total]').textContent = money(t.total);
    $('[data-pay-amount]').textContent = money(t.total);
    var FREE = (window.FD_SHIPPING || { free: 1000 }).free;
    $('#free-note').textContent = t.subtotal && t.subtotal < FREE ? 'Add ' + money(FREE - t.subtotal) + ' more for free delivery' : (t.subtotal ? 'You get free delivery' : '');
  }
  function updatePaymentMethod() {
    var selected = $('input[name="payment"]:checked');
    var method = selected ? selected.value : '';
    document.querySelectorAll('.pay-option').forEach(function (option) {
      option.classList.toggle('selected', !!option.querySelector('input[name="payment"]:checked'));
    });
    document.querySelectorAll('[data-payment-details]').forEach(function (details) {
      var active = details.dataset.paymentDetails === method;
      details.hidden = !active;
      details.querySelectorAll('[data-payment-required]').forEach(function (input) {
        input.disabled = !active;
        input.required = active;
      });
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target.matches('input[name="payment"]')) updatePaymentMethod();
  });
  document.addEventListener('click', function (e) {
    var q = e.target.closest('[data-cart-qty]');
    if (q) { var it = fdCart.read().find(function (x) { return String(x.id) === q.dataset.id; }); if (it) { fdCart.setQty(it.id, it.qty + parseInt(q.dataset.cartQty)); render(); } }
    var r = e.target.closest('[data-cart-remove]'); if (r) { fdCart.remove(r.dataset.id); render(); window.fdToast('Removed from cart'); }
  });

  function finish() {
    var form = $('#checkout');
    $('#f-items').value = JSON.stringify(fdCart.read().map(function (item) { return { id: item.id, qty: item.qty }; }));
    form.submit();
  }
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#place-order')) return;
    e.preventDefault();
    if (!fdCart.read().length) { window.fdToast('Your cart is empty'); return; }
    if (root.dataset.auth !== '1') { window.fdToast('Please log in to place your order'); setTimeout(function () { location.href = root.dataset.loginUrl; }, 900); return; }
    if (root.dataset.role !== 'client') { window.fdToast('Only client accounts can place orders'); return; }
    var form = $('#checkout');
    if (!form.reportValidity()) return;
    if (!$('input[name="payment"]:checked')) { window.fdToast('No payment method is currently available'); return; }
    finish();
  });
  updatePaymentMethod();
  render();
})();
