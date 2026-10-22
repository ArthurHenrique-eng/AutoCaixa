/* AutoCaixa Local — comportamento do totem.
 * O navegador envia intenções. Preços, estoque, status e finalização são sempre decididos pelo servidor. */
(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var INACTIVITY_MS = 120000;
  var lastScan = { code: '', at: 0 };

  function money(cents) {
    return 'R$ ' + (cents / 100).toFixed(2).replace('.', ',');
  }

  function setFeedback(el, text, kind) {
    if (!el) return;
    el.textContent = text; // textContent evita injeção de HTML a partir de mensagens
    el.className = 'feedback' + (kind ? ' ' + kind : '');
  }

  function api(path, body) {
    var opts = {
      method: body === undefined ? 'GET' : 'POST',
      headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
      credentials: 'same-origin'
    };
    if (body !== undefined) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
    return fetch(path, opts).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        return { ok: res.ok, status: res.status, data: data };
      });
    });
  }

  function inactivityReturn() {
    // Após inatividade, o totem volta ao início, sem apagar a sessão de compra no servidor.
    var timer;
    function reset() {
      clearTimeout(timer);
      timer = setTimeout(function () { window.location.href = '/'; }, INACTIVITY_MS);
    }
    ['pointerdown', 'keydown', 'touchstart'].forEach(function (evt) {
      document.addEventListener(evt, reset, { passive: true });
    });
    reset();
  }

  function renderCart(summary) {
    var list = document.getElementById('cart-list');
    if (!list) return;
    list.innerHTML = '';
    summary.items.forEach(function (line) {
      var li = document.createElement('li');
      li.className = 'cart-item';
      li.setAttribute('data-product-id', line.product_id);

      var info = document.createElement('div');
      info.className = 'item-info';
      var name = document.createElement('strong');
      name.textContent = line.name;
      var code = document.createElement('span');
      code.className = 'muted';
      code.textContent = 'Código: ' + line.barcode;
      info.appendChild(name);
      info.appendChild(code);

      var qty = document.createElement('div');
      qty.className = 'qty';
      qty.innerHTML = '<button type="button" class="btn btn-qty" data-action="dec" aria-label="Diminuir quantidade">−</button>' +
        '<span class="qty-value"></span>' +
        '<button type="button" class="btn btn-qty" data-action="inc" aria-label="Aumentar quantidade">+</button>';
      qty.querySelector('.qty-value').textContent = line.quantity;

      var price = document.createElement('div');
      price.className = 'item-price';
      price.textContent = money(line.subtotal_cents);

      var remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-remove';
      remove.setAttribute('data-action', 'remove');
      remove.textContent = 'Remover';

      li.appendChild(info);
      li.appendChild(qty);
      li.appendChild(price);
      li.appendChild(remove);
      list.appendChild(li);
    });

    var total = document.getElementById('total');
    if (total) total.textContent = money(summary.total_cents);
    var count = document.getElementById('item-count');
    if (count) count.textContent = summary.items.length + ' itens';

    var empty = document.querySelector('.empty');
    if (empty) empty.style.display = summary.items.length ? 'none' : '';
    document.querySelectorAll('[data-pay]').forEach(function (btn) {
      btn.disabled = summary.items.length === 0;
    });
    if (summary.removed && summary.removed.length) {
      setFeedback(document.getElementById('feedback'), summary.removed[0], 'error');
    }
  }

  function refreshCart() {
    return api('/api/carrinho').then(function (r) { renderCart(r.data); });
  }

  function initCart() {
    var scanner = document.getElementById('scanner');
    var feedback = document.getElementById('feedback');

    scanner.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      var code = scanner.value.trim();
      scanner.value = '';
      if (!code) return;
      // Evita registrar duas vezes o mesmo código por leitura dupla do leitor.
      var now = Date.now();
      if (code === lastScan.code && now - lastScan.at < 800) return;
      lastScan = { code: code, at: now };

      api('/api/carrinho/adicionar', { barcode: code }).then(function (r) {
        if (r.ok) {
          setFeedback(feedback, 'Produto adicionado.', 'ok');
          renderCart(r.data);
        } else {
          setFeedback(feedback, r.data.error || 'Não foi possível adicionar o produto.', 'error');
        }
        scanner.focus();
      });
    });

    document.getElementById('cart-list') && document.getElementById('cart-list').addEventListener('click', function (e) {
      var btn = e.target.closest('button[data-action]');
      if (!btn) return;
      var item = btn.closest('.cart-item');
      var productId = parseInt(item.getAttribute('data-product-id'), 10);
      var current = parseInt(item.querySelector('.qty-value').textContent, 10);
      var action = btn.getAttribute('data-action');
      var request;
      if (action === 'remove') {
        request = api('/api/carrinho/remover', { product_id: productId });
      } else {
        var next = action === 'inc' ? current + 1 : current - 1;
        request = api('/api/carrinho/quantidade', { product_id: productId, quantity: next });
      }
      request.then(function (r) {
        if (r.ok) {
          setFeedback(feedback, '', '');
          renderCart(r.data);
        } else {
          setFeedback(feedback, r.data.error || 'Operação não permitida.', 'error');
          refreshCart();
        }
      });
    });

    document.querySelectorAll('[data-pay]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (btn.disabled) return;
        var buttons = document.querySelectorAll('[data-pay]');
        buttons.forEach(function (b) { b.disabled = true; }); // impede envios duplicados
        api('/api/checkout', { method: btn.getAttribute('data-pay') }).then(function (r) {
          if (r.ok && r.data.redirect) {
            window.location.href = r.data.redirect;
          } else {
            setFeedback(feedback, r.data.error || 'Não foi possível iniciar o pagamento.', 'error');
            refreshCart();
          }
        });
      });
    });

    refreshCart();
    inactivityReturn();
    scanner.focus();
  }

  function initPayment() {
    var page = document.querySelector('[data-page="payment"]');
    var saleId = page.getAttribute('data-sale-id');
    var statusText = document.getElementById('status-text');
    var feedback = document.getElementById('feedback');
    var polling = true;

    var labels = {
      awaiting_payment: 'Aguardando pagamento...',
      paid: 'Pagamento aprovado',
      failed: 'Pagamento não aprovado. Chame um funcionário ou tente novamente.'
    };

    function poll() {
      if (!polling) return;
      api('/api/vendas/' + saleId + '/status').then(function (r) {
        if (!r.ok) {
          setFeedback(feedback, r.data.error || 'Não foi possível verificar o pagamento.', 'error');
        } else if (r.data.status === 'paid') {
          polling = false;
          window.location.href = '/comprovante/' + saleId;
          return;
        } else if (r.data.status === 'failed') {
          polling = false;
          statusText.textContent = labels.failed;
          return;
        } else {
          statusText.textContent = labels.awaiting_payment;
        }
        setTimeout(poll, 2000);
      });
    }

    var simulate = document.getElementById('simulate-approval');
    if (simulate) {
      simulate.addEventListener('click', function () {
        simulate.disabled = true;
        api('/api/vendas/' + saleId + '/simular-aprovacao', {}).then(function (r) {
          if (!r.ok) {
            setFeedback(feedback, r.data.error || 'Simulação indisponível.', 'error');
            simulate.disabled = false;
          }
        });
      });
    }
    poll();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('[data-page]');
    if (!page) return;
    var kind = page.getAttribute('data-page');
    if (kind === 'cart') initCart();
    if (kind === 'payment') initPayment();
  });
})();
