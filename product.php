<?php
session_start();
require_once 'db.php';

// Controllo se l'ID è presente e valido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php#collezione");
    exit;
}

$product_id = (int)$_GET['id'];

// Recupero i dati del singolo prodotto
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Se il prodotto non esiste, torno alla home
if (!$product) {
    header("Location: index.php#collezione");
    exit;
}

// Controllo per il badge (se presente)
$badgeText = isset($product['badge']) ? $product['badge'] : '';

$site_base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$og_url = $site_base . ($basePath ? $basePath : '') . '/product.php?id=' . (int) $product_id;
$_plain = trim(strip_tags($product['description_color_material'] ?? ''));
$page_meta_desc = $_plain !== '' ? mb_substr($_plain, 0, 158) : ($product['name'] . ' — Divani di design Luxury Sofà, artigianato italiano.');
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($page_meta_desc) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($product['name']) ?> | Luxury Sofà">
  <meta property="og:description" content="<?= htmlspecialchars($page_meta_desc) ?>">
  <meta property="og:type" content="product">
  <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($product['main_image_url']) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <title><?= htmlspecialchars($product['name']) ?> | Luxury Sofà</title>
  
  <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* --- VARIABILI E BASE --- */
    :root {
      --bg-primary: #FDFDFB; --bg-secondary: #F4F4F4; --text-primary: #000000; 
      --text-secondary: #707070; --white: #FFFFFF; --border-color: #E5E5E5;
      --font-heading: 'Playfair Display', serif; --font-body: 'Inter', sans-serif;
      --transition-fast: 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; }
    body { font-family: var(--font-body); background-color: var(--bg-primary); color: var(--text-primary); line-height: 1.5; overflow-x: hidden; -webkit-font-smoothing: antialiased; }
    img { max-width: 100%; height: auto; display: block; }
    .container { width: min(92%, 1400px); max-width: 1400px; margin: 0 auto; padding-left: env(safe-area-inset-left); padding-right: env(safe-area-inset-right); }
    a { text-decoration: none; color: inherit; }

    /* --- HEADER MINIMALE --- */
    header { padding: 25px 0; background-color: var(--bg-primary); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1000; padding-top: max(16px, env(safe-area-inset-top)); }
    header .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px 16px; }
    .logo { font-family: var(--font-heading); font-size: clamp(1.35rem, 4vw, 2rem); font-weight: 500; color: var(--text-primary); letter-spacing: -1px; }
    
    .header-actions { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; justify-content: flex-end; flex: 1; min-width: 0; }
    .back-btn { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; transition: var(--transition-fast); color: var(--text-primary); white-space: nowrap; }
    .back-btn-short { display: none; }
    .back-btn:hover { color: var(--text-secondary); transform: translateX(-5px); }
    
    .wishlist-nav { position: relative; font-size: 1.15rem; color: var(--text-primary); text-decoration: none; display: flex; align-items: center; }
    .wishlist-nav:hover { opacity: 0.6; }
    .wishlist-badge { position: absolute; top: -6px; right: -10px; background-color: var(--text-primary); color: var(--white); font-size: 0.6rem; font-weight: 600; min-width: 16px; height: 16px; padding: 0 4px; display: none; align-items: center; justify-content: center; border-radius: 50%; }
    .cart-btn { position: relative; font-size: 1.2rem; transition: var(--transition-fast); cursor: pointer; color: var(--text-primary); display: flex; align-items: center; }
    .cart-btn:hover { opacity: 0.6; }
    .cart-badge { position: absolute; top: -6px; right: -10px; background-color: var(--text-primary); color: var(--white); font-size: 0.65rem; font-weight: 600; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: transform 0.3s ease; }

    /* --- LAYOUT PRODOTTO SINGOLO --- */
    .product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 100px; padding: 80px 0 150px; align-items: start; }
    .product-gallery { position: sticky; top: 120px; overflow: hidden; background: #F8F8F8; }
    .product-gallery img { width: 100%; height: auto; object-fit: cover; transition: transform 2s ease; cursor: zoom-in; }
    .product-gallery:hover img { transform: scale(1.05); }

    /* Stile Badge Interno */
    .product-badge-large { display: inline-block; background-color: var(--text-primary); color: var(--white); padding: 5px 12px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-bottom: 15px; }

    .product-details { display: flex; flex-direction: column; }
    .product-subtitle { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 3px; color: var(--text-secondary); margin-bottom: 15px; display: block; }
    .product-title { font-family: var(--font-heading); font-size: 4.5rem; line-height: 1; letter-spacing: -2px; margin-bottom: 25px; }
    
    .product-price-box { display: flex; align-items: baseline; gap: 15px; margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 30px; }
    .price { font-size: 1.8rem; font-weight: 500; }
    .old-price { font-size: 1.2rem; color: #A0A0A0; text-decoration: line-through; }

    .product-description { font-size: 1.05rem; color: var(--text-secondary); line-height: 1.8; margin-bottom: 40px; }

    .btn-add-massive { width: 100%; padding: 25px; background-color: var(--text-primary); color: var(--white); font-family: var(--font-body); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; border: 1px solid var(--text-primary); cursor: pointer; transition: all 0.4s ease; margin-bottom: 50px; display: flex; justify-content: center; align-items: center; gap: 10px; }
    .btn-add-massive:hover { background-color: transparent; color: var(--text-primary); }
    .btn-add-massive:active { transform: scale(0.98); }
    .btn-wish-line { width: 100%; padding: 16px; margin-bottom: 24px; background: transparent; border: 1px solid var(--border-color); font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; color: var(--text-primary); transition: var(--transition-fast); display: flex; align-items: center; justify-content: center; gap: 10px; }
    .btn-wish-line:hover { border-color: var(--text-primary); background: #fafafa; }
    .btn-wish-line.is-active { border-color: var(--text-primary); }
    .btn-wish-line.is-active i { color: #b91c1c; }

    /* Accordion */
    .accordion-item { border-top: 1px solid var(--border-color); }
    .accordion-item:last-child { border-bottom: 1px solid var(--border-color); }
    .accordion-header { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 25px 0; background: none; border: none; font-family: var(--font-body); font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; color: var(--text-primary); }
    .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.4s ease; color: var(--text-secondary); font-size: 0.95rem; line-height: 1.7; }
    .accordion-content p { padding-bottom: 25px; }
    .accordion-item.active .accordion-content { max-height: 1200px; }
    .accordion-item.active .accordion-header i { transform: rotate(180deg); }

    /* --- SIDE CART & TOAST (Omissis CSS per non allungare) --- */
    .cart-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(8px); z-index: 2000; opacity: 0; visibility: hidden; transition: all 0.5s ease; }
    .cart-overlay.active { opacity: 1; visibility: visible; }
    .cart-drawer { position: fixed; top: 0; right: -100%; width: 100%; max-width: 480px; height: 100vh; background-color: var(--bg-primary); z-index: 2001; transition: right 0.6s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; box-shadow: -10px 0 30px rgba(0,0,0,0.05); }
    .cart-drawer.open { right: 0; }
    .cart-header { padding: 35px 40px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .cart-header h2 { font-size: 1.6rem; margin: 0; }
    .close-cart { font-size: 1.2rem; color: var(--text-primary); cursor: pointer; transition: transform 0.3s; }
    .close-cart:hover { transform: rotate(90deg); }
    .cart-items { flex: 1; overflow-y: auto; padding: 40px; display: flex; flex-direction: column; gap: 30px; }
    .cart-empty { text-align: center; color: var(--text-secondary); margin-top: 50px; font-size: 0.9rem; }
    .cart-item { display: flex; gap: 20px; padding-bottom: 30px; border-bottom: 1px solid var(--border-color); }
    .cart-item-img { width: 90px; height: 110px; object-fit: cover; background-color: #F0F0F0; }
    .cart-item-info { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .cart-item-title { font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 5px; }
    .cart-item-price { font-family: var(--font-body); font-weight: 500; font-size: 0.95rem; }
    .cart-item-actions { display: flex; justify-content: space-between; align-items: center; }
    .qty-control { display: flex; align-items: center; gap: 15px; border: 1px solid var(--border-color); padding: 5px 12px; }
    .qty-btn { background: none; border: none; color: var(--text-primary); cursor: pointer; }
    .remove-item { color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; }
    .coupon-section { padding: 0 40px 20px; display: flex; gap: 10px; border-bottom: 1px solid var(--border-color); }
    .coupon-section input { flex: 1; padding: 12px 15px; border: 1px solid var(--border-color); background: transparent; font-family: var(--font-body); outline: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
    .coupon-section button { padding: 0 25px; background: var(--text-primary); color: var(--white); border: none; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.75rem; cursor: pointer; transition: opacity 0.3s; }
    .cart-footer { padding: 25px 40px 30px; background-color: var(--bg-primary); }
    .discount-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 0.9rem; color: #b91c1c; font-weight: 500; display: none; }
    .cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
    .cart-total span:first-child { text-transform: uppercase; font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); }
    .cart-total span:last-child { font-size: 1.8rem; font-family: var(--font-heading); color: var(--text-primary); line-height: 1; }
    .btn-checkout { width: 100%; padding: 24px; background-color: var(--text-primary); color: var(--white); border: 1px solid var(--text-primary); font-family: var(--font-body); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; cursor: pointer; display: flex; justify-content: center; }

    #toast-container { position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 15px; max-width: calc(100vw - 24px); }
    .toast { background-color: var(--text-primary); color: var(--white); padding: 20px 30px; font-size: 0.85rem; letter-spacing: 1px; display: flex; align-items: center; gap: 15px; transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .toast.show { transform: translateX(0); }

    /* --- ANIMAZIONI --- */
    .reveal { opacity: 0; transform: translateY(40px); transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
    .delay-1 { transition-delay: 0.1s; } .delay-2 { transition-delay: 0.2s; } .delay-3 { transition-delay: 0.3s; }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
      .product-layout { grid-template-columns: 1fr; gap: 40px; padding: 32px 0 80px; }
      .product-gallery { position: relative; top: 0; }
      .product-title { font-size: clamp(2.2rem, 8vw, 3.5rem); }
    }
    @media (max-width: 768px) {
      .product-title { font-size: clamp(1.85rem, 7.5vw, 2.8rem); letter-spacing: -0.03em; }
      .product-price-box { flex-wrap: wrap; padding-bottom: 24px; margin-bottom: 28px; }
      .price { font-size: 1.5rem; }
      .product-description { font-size: 1rem; }
      .btn-add-massive { padding: 22px 18px; font-size: 0.78rem; letter-spacing: 2px; }
      .accordion-header { padding: 20px 0; font-size: 0.82rem; }
      .cart-drawer { max-width: 100%; }
      .cart-header, .cart-items, .coupon-section, .cart-footer { padding-left: max(20px, env(safe-area-inset-left)); padding-right: max(20px, env(safe-area-inset-right)); }
      .cart-header { padding-top: max(24px, env(safe-area-inset-top)); }
      #toast-container { left: 12px; right: 12px; bottom: max(16px, env(safe-area-inset-bottom)); }
      .toast { transform: translateY(120%); width: 100%; justify-content: center; padding: 16px 18px; }
      .toast.show { transform: translateY(0); }
    }
    @media (max-width: 480px) {
      .back-btn-full { display: none; }
      .back-btn-short { display: inline; }
      .back-btn { letter-spacing: 1.2px; font-size: 0.7rem; }
      header .container { flex-direction: column; align-items: stretch; }
      .header-actions { justify-content: space-between; width: 100%; }
      .coupon-section { flex-direction: column; align-items: stretch; }
      .coupon-section button { width: 100%; padding: 14px; }
    }
  </style>
</head>
<body>

  <!-- HEADER CON ICONA CARRELLO -->
  <header>
    <div class="container reveal active">
      <a href="index.php" class="logo">Luxury Sofà.</a>
      <div class="header-actions">
          <a href="index.php#collezione" class="back-btn"><i class="fas fa-arrow-left"></i> <span class="back-btn-full">Torna alla Collezione</span><span class="back-btn-short">Collezione</span></a>
          <a href="wishlist.php" class="wishlist-nav" id="wishlist-nav" aria-label="Preferiti"><i class="far fa-heart"></i><span class="wishlist-badge" id="wishlist-badge">0</span></a>
          <div class="cart-btn" id="cart-icon">
            <i class="fas fa-shopping-bag"></i>
            <span class="cart-badge" id="cart-badge">0</span>
          </div>
      </div>
    </div>
  </header>

  <!-- SIDE CART DRAWER (GLOBALE) -->
  <div class="cart-overlay" id="cart-overlay"></div>
  <div class="cart-drawer" id="cart-drawer">
    <div class="cart-header">
      <h2>Il tuo Carrello</h2>
      <i class="fas fa-times close-cart" id="close-cart"></i>
    </div>
    
    <div class="cart-items" id="cart-items">
      <div class="cart-empty">Il carrello è attualmente vuoto.</div>
    </div>
    
    <div class="coupon-section">
      <input type="text" id="coupon-input" placeholder="Codice Sconto (es: BENVENUTO10)">
      <button id="apply-coupon-btn">Applica</button>
    </div>

    <div class="cart-footer">
      <div class="discount-row" id="discount-row">
        <span>Sconto applicato (<span id="discount-percent-display">0</span>%)</span>
        <span id="discount-amount">-€0,00</span>
      </div>
      <div class="cart-total">
        <span>Totale</span>
        <span id="cart-total-price" data-raw-total="0">€0,00</span>
      </div>
      <button class="btn-checkout" id="checkout-btn">Procedi al Checkout</button>
    </div>
  </div>

  <!-- TOAST CONTAINER -->
  <div id="toast-container"></div>

  <main>
    <div class="container">
      <div class="product-layout">
        
        <!-- IMMAGINE PRODOTTO -->
        <div class="product-gallery reveal">
          <img src="<?= htmlspecialchars($product['main_image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- DETTAGLI PRODOTTO -->
        <div class="product-details">
          <div class="reveal delay-1">
            
            <?php if (!empty($badgeText)): ?>
               <span class="product-badge-large"><?= htmlspecialchars($badgeText) ?></span>
            <?php else: ?>
               <span class="product-subtitle">Nuova Collezione</span>
            <?php endif; ?>

            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-price-box">
              <?php if (!empty($product['old_price'])): ?>
                <span class="old-price">€<?= number_format($product['old_price'], 0, ',', '.') ?></span>
              <?php endif; ?>
              <span class="price">€<?= number_format($product['price'], 0, ',', '.') ?></span>
            </div>
            
            <p class="product-description">
              <?= htmlspecialchars($product['description_color_material'] ?? 'Un design esclusivo che combina estetica pura e comfort assoluto. Realizzato artigianalmente in Italia con materiali di altissima qualità, pensato per resistere nel tempo e arricchire il tuo spazio living.') ?>
            </p>
          </div>

          <div class="reveal delay-2">
            <!-- PULSANTE AGGIUNGI AL CARRELLO CHE APRE IL DRAWER -->
            <button class="btn-add-massive" onclick="addToCart('<?= $product['sku'] ?>', '<?= addslashes($product['name']) ?>', <?= $product['price'] ?>, '<?= $product['main_image_url'] ?>')">
              Aggiungi al Carrello <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" class="btn-wish-line" id="product-wish-btn" data-pid="<?= (int)$product_id ?>" data-sku="<?= htmlspecialchars($product['sku'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars((string)(float)$product['price']) ?>" data-img="<?= htmlspecialchars($product['main_image_url'], ENT_QUOTES) ?>">
              <i class="far fa-heart"></i> <span class="wish-btn-label">Aggiungi ai preferiti</span>
            </button>
          </div>

          <!-- FISARMONICA INFORMAZIONI -->
          <div class="accordion reveal delay-3">
            <div class="accordion-item active">
              <button class="accordion-header">
                Materiali & Cura <i class="fas fa-chevron-down"></i>
              </button>
              <div class="accordion-content">
                <p>Rivestimento in vera pelle o tessuti pregiati idrorepellenti. Struttura in legno massello stagionato. Cuscini in piuma d'oca e memory foam ad alta densità. Pulire con un panno morbido e umido; evitare prodotti chimici aggressivi.</p>
              </div>
            </div>
            <div class="accordion-item">
              <button class="accordion-header">
                Dimensioni & tempi <i class="fas fa-chevron-down"></i>
              </button>
              <div class="accordion-content">
                <p>Le misure definitive dipendono dalla configurazione modulare e dal rivestimento scelto: dopo l’ordine riceverai il disegno tecnico in scala per approvazione. I tempi di consegna indicativi sono di 4–6 settimane dalla conferma dell’ordine e del saldo secondo le modalità concordate.</p>
              </div>
            </div>
            <div class="accordion-item">
              <button class="accordion-header">
                Spedizione <i class="fas fa-chevron-down"></i>
              </button>
              <div class="accordion-content">
                <p>Offriamo il servizio "White Glove": consegna al piano, disimballaggio e montaggio inclusi nel prezzo. I tempi di produzione e spedizione stimati sono di 4-6 settimane, in quanto ogni pezzo è realizzato su ordinazione.</p>
              </div>
            </div>
            <div class="accordion-item">
              <button class="accordion-header">
                Garanzia <i class="fas fa-chevron-down"></i>
              </button>
              <div class="accordion-content">
                <p>Tutti i nostri divani sono coperti da una garanzia strutturale di 10 anni. La manifattura italiana garantisce un'attenzione maniacale ai dettagli, ma in caso di difetti di fabbrica provvederemo alla sostituzione o riparazione gratuita.</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  <script src="js/store-common.js"></script>
  <script>
    // --- ANIMAZIONI & UI ---
    function reveal() {
      document.querySelectorAll(".reveal").forEach(rev => {
        if (rev.getBoundingClientRect().top < window.innerHeight - 50) rev.classList.add("active");
      });
    }
    window.addEventListener("load", reveal);
    window.addEventListener("scroll", reveal);

    document.querySelectorAll('.accordion-header').forEach(button => {
      button.addEventListener('click', () => {
        const item = button.parentElement;
        const isActive = item.classList.contains('active');
        document.querySelectorAll('.accordion-item').forEach(el => el.classList.remove('active'));
        if (!isActive) item.classList.add('active');
      });
    });

    function showToast(message) {
      const toastContainer = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = 'toast';
      toast.innerHTML = `<i class="fas fa-check-circle" style="color:#FFF;"></i> <span>${message}</span>`;
      toastContainer.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
    }

    // --- LOGICA CARRELLO GLOBALE (LOCALSTORAGE) ---
    let cart = LUXURY_STORE.getCart();
    let activeCouponId = null;
    let discountPercent = 0;

    const cartIcon = document.getElementById('cart-icon');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartDrawer = document.getElementById('cart-drawer');
    const closeCartBtn = document.getElementById('close-cart');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartBadge = document.getElementById('cart-badge');
    const cartTotalPrice = document.getElementById('cart-total-price');
    const discountRow = document.getElementById('discount-row');
    const discountAmount = document.getElementById('discount-amount');
    const discountPercentDisplay = document.getElementById('discount-percent-display');

    function saveCart() { LUXURY_STORE.saveCart(cart); }

    function toggleCart() {
      cartDrawer.classList.toggle('open');
      cartOverlay.classList.toggle('active');
    }
    cartIcon.addEventListener('click', toggleCart);
    closeCartBtn.addEventListener('click', toggleCart);
    cartOverlay.addEventListener('click', toggleCart);

    window.addToCart = function(id, title, price, imgUrl) {
      const existingItem = cart.find(item => item.id === id);
      if (existingItem) existingItem.qty++;
      else cart.push({ id, title, price, imgUrl, qty: 1 });
      
      cartBadge.style.transform = 'scale(1.5)';
      setTimeout(() => cartBadge.style.transform = 'scale(1)', 200);
      
      saveCart();
      updateCartUI();
      if(!cartDrawer.classList.contains('open')) toggleCart();
    };

    window.updateQty = function(id, change) {
      const item = cart.find(item => item.id === id);
      if(item) {
        item.qty += change;
        if(item.qty <= 0) cart = cart.filter(i => i.id !== id);
        saveCart();
        updateCartUI();
      }
    };

    function updateCartUI() {
      const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
      cartBadge.innerText = totalItems;

      if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<div class="cart-empty">Il carrello è attualmente vuoto.</div>';
        cartTotalPrice.innerText = '€0,00';
        cartTotalPrice.dataset.rawTotal = 0;
        discountRow.style.display = 'none';
        return;
      }

      cartItemsContainer.innerHTML = cart.map(item => `
        <div class="cart-item">
          <img src="${item.imgUrl}" alt="${item.title}" class="cart-item-img">
          <div class="cart-item-info">
            <div>
              <div class="cart-item-title">${item.title}</div>
              <div class="cart-item-price">€${item.price.toFixed(2).replace('.', ',')}</div>
            </div>
            <div class="cart-item-actions">
              <div class="qty-control">
                <button class="qty-btn" onclick="updateQty('${item.id}', -1)"><i class="fas fa-minus"></i></button>
                <span>${item.qty}</span>
                <button class="qty-btn" onclick="updateQty('${item.id}', 1)"><i class="fas fa-plus"></i></button>
              </div>
              <a class="remove-item" onclick="updateQty('${item.id}', -999)">Rimuovi</a>
            </div>
          </div>
        </div>
      `).join('');

      let subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
      let finalTotal = subtotal;

      if(discountPercent > 0) {
        let discountValue = (subtotal * discountPercent) / 100;
        finalTotal = subtotal - discountValue;
        discountPercentDisplay.innerText = discountPercent;
        discountAmount.innerText = `-€${discountValue.toFixed(2).replace('.', ',')}`;
        discountRow.style.display = 'flex';
      } else {
        discountRow.style.display = 'none';
      }

      cartTotalPrice.innerText = `€${finalTotal.toFixed(2).replace('.', ',')}`;
      cartTotalPrice.dataset.rawTotal = finalTotal;
    }

    // Aggiorna l'UI all'avvio della pagina leggendo i dati dal localStorage
    updateCartUI();

    document.getElementById('apply-coupon-btn').addEventListener('click', async () => {
        const codeInput = document.getElementById('coupon-input').value;
        if(cart.length === 0) { showToast("Aggiungi prodotti al carrello prima."); return; }
        if(!codeInput) return;

        const formData = new FormData();
        formData.append('action', 'apply_coupon');
        formData.append('code', codeInput);

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const data = await res.json();

        if(data.success) {
            discountPercent = data.discount_percent;
            activeCouponId = data.coupon_id;
            updateCartUI();
            showToast(data.message);
        } else {
            discountPercent = 0;
            activeCouponId = null;
            updateCartUI();
            showToast(data.message);
        }
    });

    const checkoutBtn = document.getElementById('checkout-btn');
    checkoutBtn.addEventListener('click', () => {
      if (cart.length === 0) return;
      window.location.href = 'checkout.php';
    });

    (function wishProductPage() {
      const wbtn = document.getElementById('product-wish-btn');
      if (!wbtn) return;
      function sync() {
        const on = LUXURY_STORE.isInWishlist(wbtn.dataset.sku);
        wbtn.classList.toggle('is-active', on);
        const ic = wbtn.querySelector('i');
        if (ic) ic.className = on ? 'fas fa-heart' : 'far fa-heart';
        const lbl = wbtn.querySelector('.wish-btn-label');
        if (lbl) lbl.textContent = on ? 'Nei tuoi preferiti' : 'Aggiungi ai preferiti';
        LUXURY_STORE.updateWishlistBadges();
      }
      sync();
      wbtn.addEventListener('click', () => {
        LUXURY_STORE.toggleWishlist({
          sku: wbtn.dataset.sku,
          pid: parseInt(wbtn.dataset.pid, 10),
          name: wbtn.dataset.name,
          price: parseFloat(wbtn.dataset.price),
          imgUrl: wbtn.dataset.img
        });
        sync();
        showToast(LUXURY_STORE.isInWishlist(wbtn.dataset.sku) ? 'Salvato nei preferiti' : 'Rimosso dai preferiti');
      });
    })();
  </script>
</body>
</html>