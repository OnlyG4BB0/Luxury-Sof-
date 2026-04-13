<?php
session_start();
require_once 'db.php';
$logged = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Completa il tuo ordine Luxury Sofà — dati di spedizione e metodo di pagamento.">
  <title>Checkout | Luxury Sofà</title>
  <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --bg: #FDFDFB; --text: #000; --muted: #707070; --border: #E5E5E5;
      --font-h: 'Playfair Display', serif; --font-b: 'Inter', sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { -webkit-text-size-adjust: 100%; }
    body { font-family: var(--font-b); background: var(--bg); color: var(--text); line-height: 1.5; }
    .container { width: min(92%, 1100px); margin: 0 auto; padding: 0 env(safe-area-inset-right) 0 env(safe-area-inset-left); }
    header { padding: 22px 0; border-bottom: 1px solid var(--border); padding-top: max(14px, env(safe-area-inset-top)); }
    header .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .logo { font-family: var(--font-h); font-size: 1.5rem; font-weight: 500; color: var(--text); text-decoration: none; letter-spacing: -0.5px; }
    .back-link { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 2px; color: var(--muted); text-decoration: none; }
    .back-link:hover { color: var(--text); }
    h1 { font-family: var(--font-h); font-weight: 400; font-size: clamp(1.75rem, 4vw, 2.5rem); margin-bottom: 8px; }
    .lead { color: var(--muted); font-size: 0.95rem; margin-bottom: 40px; }
    .checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 60px; align-items: start; padding: 48px 0 100px; }
    @media (max-width: 900px) { .checkout-grid { grid-template-columns: 1fr; gap: 40px; } }
    .form-section label { display: block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 2px; color: var(--muted); margin-bottom: 8px; font-weight: 600; }
    .form-section input, .form-section textarea, .form-section select {
      width: 100%; padding: 14px 0; border: none; border-bottom: 1px solid var(--border); background: transparent;
      font-family: var(--font-b); font-size: 0.95rem; margin-bottom: 24px; outline: none; transition: border-color 0.3s;
    }
    .form-section input:focus, .form-section textarea:focus { border-bottom-color: var(--text); }
    .form-section textarea { min-height: 90px; resize: vertical; border: 1px solid var(--border); padding: 14px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    .payment-options { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
    .payment-options label.opt {
      display: flex; align-items: center; gap: 14px; padding: 16px 18px; border: 1px solid var(--border); cursor: pointer;
      font-size: 0.88rem; text-transform: none; letter-spacing: 0; font-weight: 500; color: var(--text); margin: 0;
    }
    .payment-options input { width: auto; margin: 0; accent-color: #000; }
    .payment-options label.opt.is-selected { border-color: var(--text); background: #fafafa; }
    .btn-submit {
      width: 100%; padding: 22px; background: var(--text); color: #fff; border: 1px solid var(--text);
      font-family: var(--font-b); font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 3px;
      cursor: pointer; transition: 0.35s;
    }
    .btn-submit:hover { background: transparent; color: var(--text); }
    .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
    .summary {
      border: 1px solid var(--border); padding: 28px; background: #fff;
    }
    .summary h2 { font-family: var(--font-h); font-size: 1.25rem; font-weight: 400; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
    .summary-line { display: flex; justify-content: space-between; gap: 16px; font-size: 0.88rem; margin-bottom: 16px; color: var(--muted); }
    .summary-line strong { color: var(--text); font-weight: 500; }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); font-family: var(--font-h); font-size: 1.5rem; }
    .coupon-box { margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
    .coupon-box input { width: 100%; padding: 12px; border: 1px solid var(--border); margin-bottom: 10px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
    .coupon-box button { width: 100%; padding: 12px; background: var(--text); color: #fff; border: none; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; }
    .hint { font-size: 0.8rem; color: var(--muted); margin-top: 16px; }
    .empty-cart { text-align: center; padding: 60px 20px; color: var(--muted); }
    .empty-cart a { color: var(--text); font-weight: 600; }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <a href="index.php" class="logo">Luxury Sofà.</a>
      <a href="index.php#collezione" class="back-link"><i class="fas fa-arrow-left"></i> Continua lo shopping</a>
    </div>
  </header>

  <main class="container">
    <div id="checkout-empty" class="empty-cart" style="display:none;">
      <p>Il carrello è vuoto.</p>
      <p style="margin-top:12px;"><a href="index.php#collezione">Torna alla collezione</a></p>
    </div>

    <div id="checkout-main" style="display:none;">
      <h1>Checkout</h1>
      <p class="lead">Inserisci i dati per la consegna e scegli come desideri saldare l’ordine.</p>

      <div class="checkout-grid">
        <form id="checkout-form" class="form-section">
          <label for="shipping_fullname">Nome e cognome (destinatario)</label>
          <input type="text" id="shipping_fullname" name="shipping_fullname" required autocomplete="name" placeholder="Es. Mario Rossi">

          <label for="shipping_address">Indirizzo completo</label>
          <input type="text" id="shipping_address" name="shipping_address" required autocomplete="street-address" placeholder="Via, numero civico">

          <div class="form-row">
            <div>
              <label for="shipping_city">Città</label>
              <input type="text" id="shipping_city" name="shipping_city" required autocomplete="address-level2">
            </div>
            <div>
              <label for="shipping_zipcode">CAP</label>
              <input type="text" id="shipping_zipcode" name="shipping_zipcode" required autocomplete="postal-code" inputmode="numeric">
            </div>
          </div>

          <label for="order_notes">Note per la consegna (opzionale)</label>
          <textarea id="order_notes" name="order_notes" placeholder="Piano, citofono, orari preferiti…"></textarea>

          <p style="font-size:0.72rem;text-transform:uppercase;letter-spacing:2px;color:var(--muted);margin-bottom:12px;font-weight:600;">Metodo di pagamento</p>
          <div class="payment-options">
            <label class="opt"><input type="radio" name="payment_method" value="bonifico" checked> Bonifico bancario (istruzioni dopo la conferma)</label>
            <label class="opt"><input type="radio" name="payment_method" value="preventivo"> Richiesta preventivo / consulenza telefonica</label>
          </div>

          <?php if (!$logged): ?>
          <p class="hint"><i class="fas fa-info-circle"></i> Per usare un codice sconto <a href="index.php">accedi o registrati</a> dalla home, poi torna al carrello.</p>
          <?php endif; ?>

          <button type="submit" class="btn-submit" id="btn-place-order">Conferma ordine</button>
        </form>

        <aside class="summary" id="order-summary">
          <h2>Riepilogo</h2>
          <div id="summary-lines"></div>
          <div class="coupon-box" id="coupon-wrap" style="<?= $logged ? '' : 'display:none;' ?>">
            <input type="text" id="coupon-input" placeholder="Codice sconto">
            <button type="button" id="apply-coupon-btn">Applica</button>
          </div>
          <div class="summary-line discount-row" id="discount-row" style="display:none;color:#b91c1c;">
            <span>Sconto (<span id="discount-pct">0</span>%)</span>
            <span id="discount-amt">-€0,00</span>
          </div>
          <div class="summary-total">
            <span>Totale</span>
            <span id="order-total">€0,00</span>
          </div>
        </aside>
      </div>
    </div>
  </main>

  <script src="js/store-common.js"></script>
  <script>
    const loggedIn = <?= $logged ? 'true' : 'false' ?>;
    let cart = LUXURY_STORE.getCart();
    let discountPercent = 0;
    let activeCouponId = null;

    const emptyEl = document.getElementById('checkout-empty');
    const mainEl = document.getElementById('checkout-main');
    const summaryLines = document.getElementById('summary-lines');
    const orderTotalEl = document.getElementById('order-total');
    const form = document.getElementById('checkout-form');

    function fmtEuro(n) {
      return '€' + Number(n).toFixed(2).replace('.', ',');
    }

    function computeSubtotal() {
      return cart.reduce((s, i) => s + i.price * i.qty, 0);
    }

    function renderSummary() {
      if (!cart.length) {
        emptyEl.style.display = 'block';
        mainEl.style.display = 'none';
        return;
      }
      emptyEl.style.display = 'none';
      mainEl.style.display = 'block';
      summaryLines.innerHTML = cart.map(i => `
        <div class="summary-line">
          <span>${i.title} × ${i.qty}</span>
          <strong>${fmtEuro(i.price * i.qty)}</strong>
        </div>
      `).join('');
      updateTotal();
    }

    function updateTotal() {
      let sub = computeSubtotal();
      let final = sub;
      const dr = document.getElementById('discount-row');
      if (discountPercent > 0) {
        const d = sub * discountPercent / 100;
        final = sub - d;
        document.getElementById('discount-pct').textContent = discountPercent;
        document.getElementById('discount-amt').textContent = '-' + fmtEuro(d);
        dr.style.display = 'flex';
      } else {
        dr.style.display = 'none';
      }
      orderTotalEl.textContent = fmtEuro(final);
      orderTotalEl.dataset.raw = String(final);
    }

    document.getElementById('apply-coupon-btn')?.addEventListener('click', async () => {
      const code = document.getElementById('coupon-input').value.trim();
      if (!code) return;
      const fd = new FormData();
      fd.append('action', 'apply_coupon');
      fd.append('code', code);
      const res = await fetch('api.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        discountPercent = data.discount_percent;
        activeCouponId = data.coupon_id;
      } else {
        discountPercent = 0;
        activeCouponId = null;
        alert(data.message);
      }
      updateTotal();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('btn-place-order');
      btn.disabled = true;
      btn.textContent = 'Elaborazione…';
      const fd = new FormData(form);
      fd.append('action', 'checkout');
      fd.append('cart', JSON.stringify(cart));
      fd.append('total', orderTotalEl.dataset.raw || '0');
      if (activeCouponId) fd.append('coupon_id', activeCouponId);
      try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          LUXURY_STORE.saveCart([]);
          window.location.href = 'thank-you.php?order=' + encodeURIComponent(data.order_id);
        } else {
          alert(data.message);
        }
      } catch (err) {
        alert('Errore di rete. Riprova.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Conferma ordine';
      }
    });

    renderSummary();

    document.querySelectorAll('.payment-options input[name="payment_method"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        document.querySelectorAll('.payment-options label.opt').forEach(function (l) { l.classList.remove('is-selected'); });
        var p = radio.closest('label.opt');
        if (p) p.classList.add('is-selected');
      });
    });
    var _pr = document.querySelector('.payment-options input[name="payment_method"]:checked');
    if (_pr && _pr.closest('label.opt')) _pr.closest('label.opt').classList.add('is-selected');
  </script>
</body>
</html>
