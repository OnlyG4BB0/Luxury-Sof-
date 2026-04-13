<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="I tuoi preferiti Luxury Sofà — salvati sul dispositivo.">
  <title>Preferiti | Luxury Sofà</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --bg: #FDFDFB; --text: #000; --muted: #707070; --border: #E5E5E5; --font-h: 'Playfair Display', serif; --font-b: 'Inter', sans-serif; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-b); background: var(--bg); color: var(--text); min-height: 100vh; }
    .container { width: min(92%, 1100px); margin: 0 auto; padding: 48px 16px 100px; }
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px; flex-wrap: wrap; gap: 16px; }
    .logo { font-family: var(--font-h); font-size: 1.6rem; text-decoration: none; color: var(--text); font-weight: 500; }
    .back { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 2px; color: var(--muted); text-decoration: none; }
    h1 { font-family: var(--font-h); font-weight: 400; font-size: clamp(1.75rem, 4vw, 2.5rem); margin-bottom: 12px; }
    .sub { color: var(--muted); font-size: 0.9rem; margin-bottom: 40px; max-width: 520px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 40px; }
    .card { border: 1px solid var(--border); background: #fff; transition: box-shadow 0.3s; }
    .card:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.06); }
    .card a { text-decoration: none; color: inherit; display: block; }
    .card-img { aspect-ratio: 4/5; overflow: hidden; background: #f4f4f4; }
    .card-img img { width: 100%; height: 100%; object-fit: cover; }
    .card-body { padding: 22px; }
    .card-body h2 { font-family: var(--font-h); font-size: 1.25rem; font-weight: 400; margin-bottom: 8px; }
    .price { font-weight: 500; font-size: 1rem; }
    .actions-row { display: flex; gap: 10px; margin-top: 16px; }
    .btn { flex: 1; text-align: center; padding: 14px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 2px; border: 1px solid var(--text); background: var(--text); color: #fff; cursor: pointer; font-family: var(--font-b); font-weight: 600; }
    .btn.outline { background: transparent; color: var(--text); }
    .empty { text-align: center; padding: 80px 20px; color: var(--muted); }
    .empty a { color: var(--text); font-weight: 600; }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <a href="index.php" class="logo">Luxury Sofà.</a>
      <a href="index.php#collezione" class="back"><i class="fas fa-arrow-left"></i> Collezione</a>
    </header>
    <h1>Preferiti</h1>
    <p class="sub">I modelli che hai salvato restano sul tuo browser: puoi tornare qui in qualsiasi momento.</p>
    <div id="wishlist-root"></div>
  </div>
  <script src="js/store-common.js"></script>
  <script>
    function render() {
      const root = document.getElementById('wishlist-root');
      const list = LUXURY_STORE.getWishlist();
      if (!list.length) {
        root.innerHTML = '<div class="empty">Nessun preferito ancora.<br><br><a href="index.php#collezione">Esplora la collezione</a></div>';
        return;
      }
      function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
      }
      root.innerHTML = '<div class="grid">' + list.map(item => {
        const pid = item.pid || item.product_id || '';
        return `
        <div class="card" data-sku="${esc(String(item.sku))}">
          <a href="product.php?id=${pid}">
            <div class="card-img"><img src="${esc(item.imgUrl)}" alt="" loading="lazy"></div>
          </a>
          <div class="card-body">
            <a href="product.php?id=${pid}"><h2>${esc(item.name)}</h2></a>
            <p class="price">€${Number(item.price).toFixed(2).replace('.', ',')}</p>
            <div class="actions-row">
              <button type="button" class="btn outline remove-w" data-sku="${esc(String(item.sku))}">Rimuovi</button>
              <button type="button" class="btn add-c" data-sku="${esc(String(item.sku))}">Carrello</button>
            </div>
          </div>
        </div>
      `;
      }).join('') + '</div>';

      root.querySelectorAll('.remove-w').forEach(btn => {
        btn.addEventListener('click', () => {
          const sku = btn.dataset.sku;
          const next = LUXURY_STORE.getWishlist().filter(x => String(x.sku) !== String(sku));
          LUXURY_STORE.saveWishlist(next);
          render();
        });
      });
      root.querySelectorAll('.add-c').forEach(btn => {
        btn.addEventListener('click', () => {
          const full = LUXURY_STORE.getWishlist().find(x => String(x.sku) === String(btn.dataset.sku));
          if (!full) return;
          let cart = LUXURY_STORE.getCart();
          const id = full.sku;
          const ex = cart.find(i => i.id === id);
          if (ex) ex.qty++; else cart.push({ id, title: full.name, price: Number(full.price), imgUrl: full.imgUrl, qty: 1 });
          LUXURY_STORE.saveCart(cart);
          btn.textContent = 'Aggiunto';
          setTimeout(() => { btn.textContent = 'Carrello'; }, 1500);
        });
      });
    }
    render();
  </script>
</body>
</html>
