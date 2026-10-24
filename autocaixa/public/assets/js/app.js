/* AutoCaixa Local — comportamento do totem e do painel.
 * O navegador envia intenções. Preços, estoque, status e finalização são sempre decididos pelo servidor. */
(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var INACTIVITY_MS = 120000;
  var lastScan = { code: '', at: 0 };
  var cartRendered = false;
  var prevQty = {};

  /* ---------- Utilidades ---------- */

  function money(cents) {
    return 'R$ ' + (cents / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function iconHtml(name) {
    return '<svg class="icon" aria-hidden="true" focusable="false"><use href="/assets/icons.svg#' + name + '"></use></svg>';
  }

  function itemsLabel(n) {
    return n + (n === 1 ? ' item' : ' itens');
  }

  /* Mensagens entram sempre com textContent, nunca como HTML. */
  function setFeedback(el, text, kind) {
    if (!el) return;
    el.innerHTML = '';
    el.className = 'feedback' + (kind ? ' ' + kind : '');
    if (!text) return;
    var ic = document.createElement('span');
    ic.innerHTML = iconHtml(kind === 'error' ? 'circle-alert' : 'circle-check');
    var msg = document.createElement('span');
    msg.textContent = text;
    el.appendChild(ic.firstChild);
    el.appendChild(msg);
  }

  function toast(text, kind) {
    var box = document.getElementById('toasts');
    if (!box) return;
    var t = document.createElement('div');
    t.className = 'toast' + (kind === 'error' ? ' is-error' : '');
    var ic = document.createElement('span');
    ic.innerHTML = iconHtml(kind === 'error' ? 'circle-alert' : 'circle-check');
    var msg = document.createElement('span');
    msg.textContent = text;
    t.appendChild(ic.firstChild);
    t.appendChild(msg);
    box.appendChild(t);
    while (box.children.length > 3) box.removeChild(box.firstChild);
    setTimeout(function () {
      t.classList.add('is-leaving');
      setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 220);
    }, 3200);
  }

  function startLoading(btn, text) {
    if (!btn || btn.classList.contains('is-loading')) return;
    btn.__orig = btn.innerHTML;
    var label = text || btn.textContent.trim();
    btn.innerHTML = '<span class="spinner" aria-hidden="true"></span><span></span>';
    btn.lastChild.textContent = label;
    btn.classList.add('is-loading');
    btn.setAttribute('aria-busy', 'true');
  }

  function stopLoading(btn) {
    if (!btn || !btn.classList.contains('is-loading')) return;
    btn.innerHTML = btn.__orig;
    btn.classList.remove('is-loading');
    btn.removeAttribute('aria-busy');
    btn.disabled = false;
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
    }).catch(function () {
      return { ok: false, status: 0, data: { error: 'Sem conexão com o servidor. Tente novamente ou chame um funcionário.' } };
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

  /* ---------- Totem: carrinho ---------- */

  function renderCart(summary) {
    var list = document.getElementById('cart-list');
    if (!list || !summary || !summary.items) return;
    list.innerHTML = '';
    var totalQty = 0;
    var nextQty = {};

    summary.items.forEach(function (line) {
      totalQty += line.quantity;
      nextQty[line.product_id] = line.quantity;

      var li = document.createElement('li');
      li.className = 'cart-item';
      li.setAttribute('data-product-id', line.product_id);
      li.innerHTML =
        '<div class="item-info"><strong></strong><span class="muted"></span></div>' +
        '<div class="qty">' +
          '<button type="button" class="btn btn-qty" data-action="dec" aria-label="Diminuir quantidade">' + iconHtml('minus') + '</button>' +
          '<span class="qty-value"></span>' +
          '<button type="button" class="btn btn-qty" data-action="inc" aria-label="Aumentar quantidade">' + iconHtml('plus') + '</button>' +
        '</div>' +
        '<div class="item-price"><strong></strong><span class="muted"></span></div>' +
        '<button type="button" class="btn btn-remove" data-action="remove">' + iconHtml('trash-2') + 'Remover</button>';
      li.querySelector('.item-info strong').textContent = line.name;
      li.querySelector('.item-info .muted').textContent = 'Código: ' + line.barcode;
      li.querySelector('.qty-value').textContent = line.quantity;
      li.querySelector('.item-price strong').textContent = money(line.subtotal_cents);
      li.querySelector('.item-price .muted').textContent = money(line.unit_price_cents) + ' cada';
      if (cartRendered && (prevQty[line.product_id] || 0) < line.quantity) li.classList.add('is-new');
      list.appendChild(li);
    });
    prevQty = nextQty;
    cartRendered = true;

    var total = document.getElementById('total');
    if (total) total.textContent = money(summary.total_cents);
    var count = document.getElementById('item-count');
    if (count) count.textContent = itemsLabel(totalQty);

    var empty = document.querySelector('.empty');
    if (empty) empty.hidden = summary.items.length > 0;
    document.querySelectorAll('[data-pay]').forEach(function (btn) {
      btn.disabled = summary.items.length === 0;
    });
    if (summary.removed && summary.removed.length) {
      setFeedback(document.getElementById('feedback'), summary.removed[0], 'error');
    }
  }

  function refreshCart() {
    return api('/api/carrinho').then(function (r) { if (r.ok) renderCart(r.data); });
  }

  function initCart() {
    var scanner = document.getElementById('scanner');
    var feedback = document.getElementById('feedback');
    var scanCard = document.querySelector('.scan-card');

    // Tocar no cartão de leitura devolve o foco ao campo do leitor.
    if (scanCard) scanCard.addEventListener('click', function (e) {
      if (e.target !== scanner) scanner.focus();
    });

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
          setFeedback(feedback, '', '');
          toast('Produto adicionado.');
          refreshCart();
        } else {
          setFeedback(feedback, r.data.error || 'Não foi possível adicionar o produto.', 'error');
        }
        scanner.focus();
      });
    });

    var cartList = document.getElementById('cart-list');
    if (cartList) cartList.addEventListener('click', function (e) {
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
          if (action === 'remove') toast('Produto removido.');
        } else {
          setFeedback(feedback, r.data.error || 'Operação não permitida.', 'error');
        }
        refreshCart();
      });
    });

    document.querySelectorAll('[data-pay]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (btn.disabled) return;
        document.querySelectorAll('[data-pay]').forEach(function (b) { b.disabled = true; }); // impede envios duplicados
        startLoading(btn, 'Iniciando pagamento...');
        api('/api/checkout', { method: btn.getAttribute('data-pay') }).then(function (r) {
          if (r.ok && r.data.redirect) {
            window.location.href = r.data.redirect;
          } else {
            stopLoading(btn);
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

  /* ---------- Totem: pagamento ---------- */

  function initPayment() {
    var page = document.querySelector('[data-page="payment"]');
    var saleId = page.getAttribute('data-sale-id');
    var card = document.getElementById('status-card');
    var statusText = document.getElementById('status-text');
    var hint = document.getElementById('status-hint');
    var feedback = document.getElementById('feedback');
    var simulate = document.getElementById('simulate-approval');
    var polling = true;

    var labels = {
      awaiting_payment: 'Aguardando pagamento...',
      paid: 'Pagamento aprovado',
      failed: 'Pagamento não aprovado'
    };

    function setState(state, text, hintText) {
      if (card) card.setAttribute('data-state', state);
      statusText.textContent = text;
      if (hint && hintText) hint.textContent = hintText;
    }

    function poll() {
      if (!polling) return;
      api('/api/vendas/' + saleId + '/status').then(function (r) {
        if (!r.ok) {
          setFeedback(feedback, r.data.error || 'Não foi possível verificar o pagamento.', 'error');
        } else if (r.data.status === 'paid') {
          polling = false;
          setFeedback(feedback, '', '');
          if (simulate) simulate.hidden = true;
          setState('paid', labels.paid, 'Gerando o comprovante...');
          setTimeout(function () { window.location.href = '/comprovante/' + saleId; }, 900);
          return;
        } else if (r.data.status === 'failed') {
          polling = false;
          if (simulate) simulate.hidden = true;
          setState('failed', labels.failed, 'Nenhuma cobrança foi concluída. Volte ao carrinho para tentar de novo ou chame um funcionário.');
          return;
        } else {
          setFeedback(feedback, '', '');
          setState('waiting', labels.awaiting_payment);
        }
        setTimeout(poll, 2000);
      });
    }

    if (simulate) {
      simulate.addEventListener('click', function () {
        startLoading(simulate, 'Aprovando...');
        api('/api/vendas/' + saleId + '/simular-aprovacao', {}).then(function (r) {
          if (!r.ok) {
            stopLoading(simulate);
            setFeedback(feedback, r.data.error || 'Simulação indisponível.', 'error');
          }
        });
      });
    }
    poll();
  }

  /* ---------- Painel e componentes compartilhados ---------- */

  function setNav(open) {
    var toggle = document.querySelector('[data-nav-toggle]');
    document.body.classList.toggle('nav-open', open);
    if (toggle) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    }
  }

  function confirmDialog(opts) {
    return new Promise(function (resolve) {
      var d = document.createElement('dialog');
      d.className = 'modal';
      d.setAttribute('aria-labelledby', 'confirm-title');
      d.innerHTML =
        '<div class="modal-form">' +
          '<div class="modal-head"><h2 id="confirm-title"></h2></div>' +
          '<p class="muted"></p>' +
          '<div class="modal-foot">' +
            '<button type="button" class="btn btn-ghost" data-act="cancel">Voltar</button>' +
            '<button type="button" class="btn btn-danger-solid" data-act="ok"></button>' +
          '</div>' +
        '</div>';
      d.querySelector('h2').textContent = opts.title;
      d.querySelector('p').textContent = opts.text || '';
      d.querySelector('[data-act="ok"]').textContent = opts.label || 'Confirmar';
      var result = false;
      d.addEventListener('click', function (e) {
        var act = e.target.closest('[data-act]');
        if (act) { result = act.getAttribute('data-act') === 'ok'; d.close(); }
        else if (e.target === d) d.close();
      });
      d.addEventListener('close', function () { d.remove(); resolve(result); });
      document.body.appendChild(d);
      d.showModal();
      d.querySelector('[data-act="cancel"]').focus(); // foco no lado seguro
    });
  }

  function lockSubmit(btn) {
    if (!btn) return;
    setTimeout(function () {
      startLoading(btn, btn.getAttribute('data-loading-text'));
      btn.disabled = true;
    }, 0);
  }

  function filterTable(input) {
    var id = input.getAttribute('data-table-search');
    var table = document.getElementById(id);
    if (!table) return;
    var norm = function (s) { return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); };
    var q = norm(input.value.trim());
    var visible = 0;
    Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
      var show = q === '' || norm(row.textContent).indexOf(q) !== -1;
      row.hidden = !show;
      if (show) visible++;
    });
    var count = document.querySelector('[data-search-count="' + id + '"]');
    if (count) count.textContent = visible + (visible === 1 ? ' produto' : ' produtos');
    var empty = document.querySelector('[data-search-empty="' + id + '"]');
    if (empty) empty.hidden = visible > 0;
    table.hidden = visible === 0;
  }

  function initShared() {
    document.addEventListener('click', function (e) {
      var t = e.target;

      var open = t.closest('[data-open-dialog]');
      if (open) {
        var dlg = document.getElementById(open.getAttribute('data-open-dialog'));
        if (dlg && dlg.showModal) {
          dlg.showModal();
          var first = dlg.querySelector('input:not([type="hidden"]):not([type="checkbox"]), select');
          if (first) first.focus();
        }
        return;
      }
      var close = t.closest('[data-close-dialog]');
      if (close) { close.closest('dialog').close(); return; }
      if (t.tagName === 'DIALOG' && t.classList.contains('modal')) { t.close(); return; }

      if (t.closest('[data-nav-toggle]')) { setNav(!document.body.classList.contains('nav-open')); return; }
      if (t.closest('[data-nav-close]') || t.closest('.side-link')) setNav(false);

      var dismiss = t.closest('[data-dismiss]');
      if (dismiss) { var alertBox = dismiss.closest('.alert'); if (alertBox) alertBox.remove(); return; }

      var pw = t.closest('[data-toggle-password]');
      if (pw) {
        var input = document.getElementById(pw.getAttribute('data-toggle-password'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        pw.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
        var use = pw.querySelector('use');
        if (use) use.setAttribute('href', '/assets/icons.svg#' + (show ? 'eye-off' : 'eye'));
        return;
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('nav-open')) setNav(false);
    });

    document.addEventListener('input', function (e) {
      if (e.target.matches && e.target.matches('[data-table-search]')) filterTable(e.target);
    });

    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      var btn = e.submitter || form.querySelector('[type="submit"]');
      if (form.hasAttribute('data-confirm') && !form.__confirmed) {
        e.preventDefault();
        confirmDialog({
          title: form.getAttribute('data-confirm'),
          text: form.getAttribute('data-confirm-text'),
          label: form.getAttribute('data-confirm-label')
        }).then(function (ok) {
          if (!ok) return;
          form.__confirmed = true;
          lockSubmit(btn);
          form.submit();
        });
        return;
      }
      lockSubmit(btn);
    });

    // Ao voltar pelo histórico, os botões não podem ficar travados.
    window.addEventListener('pageshow', function (e) {
      if (!e.persisted) return;
      document.querySelectorAll('.btn.is-loading').forEach(stopLoading);
    });
  }

  initShared();

  document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('[data-page]');
    if (!page) return;
    var kind = page.getAttribute('data-page');
    if (kind === 'cart') initCart();
    if (kind === 'payment') initPayment();
  });
})();
