<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id ASC");
$products = $stmt->fetchAll();

$site_base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$canonical = $site_base . ($basePath ? $basePath : '') . '/index.php';
$og_image = 'https://cdn.shopify.com/s/files/1/0854/6936/4498/files/PS_Folignano_IT_FR_Base.jpg?v=1715678672';
$meta_desc = 'Luxury Sofà — divani e sistemi living artigianali italiani. Design editoriale, comfort assoluto, spedizione dedicata.';
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:title" content="Luxury Sofà | Editorial Design 2026">
  <meta property="og:description" content="<?= htmlspecialchars($meta_desc) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <title>Luxury Sofà | Editorial Design 2026</title>
  
  <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* --- VARIABILI GLOBALI E BASE --- */
    :root {
      --bg-primary: #FDFDFB; --bg-secondary: #F4F4F4; --text-primary: #000000; 
      --text-secondary: #707070; --accent: #000000; --white: #FFFFFF; --border-color: #E5E5E5;
      --font-heading: 'Playfair Display', serif; --font-body: 'Inter', sans-serif;
      --transition-fast: 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94); --transition-slow: 1s cubic-bezier(0.16, 1, 0.3, 1);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--bg-primary); }
    ::-webkit-scrollbar-thumb { background: #D0D0D0; }
    html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
    body { font-family: var(--font-body); background-color: var(--bg-primary); color: var(--text-primary); line-height: 1.5; overflow-x: hidden; -webkit-font-smoothing: antialiased; }
    img { max-width: 100%; height: auto; display: block; }
    .container { width: min(92%, 1400px); max-width: 1400px; margin: 0 auto; padding-left: env(safe-area-inset-left); padding-right: env(safe-area-inset-right); }
    h1, h2, h3, h4 { font-family: var(--font-heading); font-weight: 400; color: var(--text-primary); }
    .subtitle { text-transform: uppercase; letter-spacing: 3px; font-size: 0.7rem; font-weight: 600; color: var(--text-primary); margin-bottom: 20px; display: block; }

    /* --- HEADER --- */
    header { position: fixed; width: 100%; top: 0; z-index: 1000; padding: 30px 0; background-color: transparent; transition: var(--transition-fast); border-bottom: 1px solid transparent; }
    header.scrolled { padding: 15px 0; background-color: rgba(253, 253, 251, 0.98); border-bottom: 1px solid var(--border-color); }
    header .container { display: flex; justify-content: space-between; align-items: center; }
    .logo { font-family: var(--font-heading); font-size: 2.2rem; font-weight: 500; color: var(--white); text-decoration: none; letter-spacing: -1px; transition: var(--transition-fast); position: relative; z-index: 1001; }
    header.scrolled .logo { color: var(--text-primary); }
    .nav-links { display: flex; list-style: none; gap: 50px; }
    .nav-links a { text-decoration: none; color: var(--white); font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px; transition: var(--transition-fast); position: relative; }
    header.scrolled .nav-links a { color: var(--text-primary); }
    .nav-links a::after { content: ''; position: absolute; width: 0; height: 1px; bottom: -6px; left: 0; background-color: var(--text-primary); transition: var(--transition-fast); }
    .nav-links a:hover::after { width: 100%; }
    .header-actions { display: flex; align-items: center; gap: 25px; color: var(--white); }
    header.scrolled .header-actions { color: var(--text-primary); }
    .cart-btn, .user-btn, .wishlist-nav { position: relative; font-size: 1.1rem; transition: var(--transition-fast); cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .cart-btn:hover, .user-btn:hover, .wishlist-nav:hover { opacity: 0.6; }
    .wishlist-nav { text-decoration: none; color: inherit; }
    .wishlist-badge { position: absolute; top: -6px; right: -10px; background-color: var(--white); color: var(--text-primary); font-size: 0.6rem; font-weight: 600; min-width: 16px; height: 16px; padding: 0 4px; display: none; align-items: center; justify-content: center; border-radius: 50%; }
    header.scrolled .wishlist-badge { background-color: var(--text-primary); color: var(--white); }
    .cart-badge { position: absolute; top: -6px; right: -10px; background-color: var(--white); color: var(--text-primary); font-size: 0.65rem; font-weight: 600; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: transform 0.3s ease; }
    header.scrolled .cart-badge { background-color: var(--text-primary); color: var(--white); }
    #user-name-display { font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; display: <?= isset($_SESSION['user_id']) ? 'inline' : 'none' ?>; }
    .hamburger { display: none; font-size: 1.5rem; z-index: 1001; cursor: pointer; }

    /* --- HERO --- */
    .hero { height: 100vh; min-height: 700px; background-image: url('https://cdn.shopify.com/s/files/1/0854/6936/4498/files/PS_Folignano_IT_FR_Base.jpg?v=1715678672'); background-size: cover; background-position: center; background-attachment: fixed; display: flex; align-items: center; position: relative; }
    .hero::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.4); }
    .hero-content { position: relative; z-index: 2; max-width: 800px; color: var(--white); }
    .hero-content .subtitle { color: var(--white); }
    .hero h1 { font-size: 6rem; line-height: 1.05; margin-bottom: 30px; color: var(--white); letter-spacing: -2px; }
    .hero p { font-size: 1.1rem; margin-bottom: 50px; font-weight: 300; max-width: 500px; line-height: 1.8; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 20px 75px; font-family: var(--font-body); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; text-decoration: none; border: 1px solid var(--white); transition: var(--transition-fast); position: relative; overflow: hidden; cursor: pointer; background: transparent; color: var(--white); }
    .btn:hover { background: var(--white); color: var(--text-primary); }

    /* --- MARQUEE --- */
    .marquee-container { background-color: var(--white); color: var(--text-primary); padding: 25px 0; overflow: hidden; white-space: nowrap; display: flex; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
    .marquee-content { display: flex; animation: marquee 25s linear infinite; }
    .marquee-content span { font-family: var(--font-body); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 500; padding: 0 50px; display: flex; align-items: center; gap: 50px; }
    .marquee-content span::after { content: ''; display: block; width: 4px; height: 4px; background-color: var(--text-primary); }
    @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    /* --- STORY & QUOTE --- */
    .story { padding: 150px 0; background-color: var(--bg-primary); }
    .story-grid { display: grid; grid-template-columns: 5fr 4fr; gap: 120px; align-items: center; }
    .story-img { position: relative; overflow: hidden; }
    .story-img img { width: 100%; filter: grayscale(20%); transition: transform 2s ease; }
    .story-img:hover img { transform: scale(1.03); }
    .story-content h2 { font-size: 4rem; margin-bottom: 35px; line-height: 1.1; letter-spacing: -1px; }
    .story-content p { color: var(--text-secondary); margin-bottom: 25px; font-size: 1.05rem; line-height: 1.8; }
    .quote-section { padding: 150px 0; background-color: var(--bg-secondary); text-align: center; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); }
    .quote-section h2 { font-size: 4.5rem; line-height: 1.2; max-width: 1000px; margin: 0 auto 40px; color: var(--text-primary); letter-spacing: -1px; }
    .quote-section p { font-weight: 500; letter-spacing: 3px; text-transform: uppercase; font-size: 0.8rem; color: var(--text-secondary); }

    /* --- PRODUCTS & CONTROLS --- */
    .products { padding: 150px 0; background-color: var(--bg-primary); }
    .section-header { text-align: center; margin-bottom: 60px; }
    .section-header h2 { font-size: 4rem; letter-spacing: -1px; margin-bottom: 40px; }
    
    /* Search & Filter Bar */
    .collection-controls { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 60px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; }
    .category-filters { display: flex; gap: 15px; flex-wrap: wrap; }
    .filter-btn { background: transparent; border: 1px solid var(--border-color); padding: 10px 25px; font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all var(--transition-fast); color: var(--text-primary); font-weight: 500; border-radius: 50px; }
    .filter-btn.active, .filter-btn:hover { background: var(--text-primary); color: var(--white); border-color: var(--text-primary); }
    
    .collection-search { display: flex; align-items: center; width: 100%; max-width: 300px; border: 1px solid var(--border-color); padding: 10px 20px; border-radius: 50px; transition: border-color var(--transition-fast); }
    .collection-search:focus-within { border-color: var(--text-primary); }
    .collection-search i { color: var(--text-secondary); font-size: 1rem; margin-right: 12px; }
    .collection-search input { width: 100%; border: none; outline: none; background: transparent; font-family: var(--font-body); font-size: 0.9rem; color: var(--text-primary); }
    .collection-search input::placeholder { color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }

    .sort-wrap { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .sort-wrap label {
      font-family: var(--font-body); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px;
      color: var(--text-primary); font-weight: 500; white-space: nowrap;
    }
    .sort-wrap select {
      font-family: var(--font-body); font-size: 0.75rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px;
      color: var(--text-primary); cursor: pointer; min-width: 200px;
      padding: 10px 42px 10px 25px; border: 1px solid var(--border-color); border-radius: 50px; background-color: transparent;
      transition: all var(--transition-fast);
      appearance: none; -webkit-appearance: none; -moz-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23000000' d='M5 6L0 0h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 20px center;
    }
    .sort-wrap select:hover { border-color: var(--text-primary); background-color: rgba(0,0,0,0.02); }
    .sort-wrap select:focus { outline: none; border-color: var(--text-primary); }

    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 80px 40px; }
    .product-card { position: relative; display: flex; flex-direction: column; cursor: pointer; transition: transform var(--transition-fast), opacity var(--transition-fast); }
    
    /* Badge Stile Editoriale */
    .product-badge { position: absolute; top: 15px; left: 15px; background-color: var(--text-primary); color: var(--white); padding: 6px 14px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; z-index: 5; }
    .badge-bestseller { background-color: #000; } /* Puoi cambiare colore se vuoi */
    .badge-nuovo { background-color: #000; }

    .wishlist-heart { position: absolute; top: 14px; right: 14px; z-index: 8; width: 40px; height: 40px; border: none; background: rgba(255,255,255,0.92); color: var(--text-primary); border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition-fast); box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
    .wishlist-heart:hover { transform: scale(1.06); }
    .wishlist-heart.is-active { color: #b91c1c; }

    .product-img-wrap { position: relative; overflow: hidden; aspect-ratio: 4/5; background-color: #F8F8F8; margin-bottom: 25px; transition: transform var(--transition-slow); }
    .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition-slow), filter var(--transition-slow); }
    .product-card:hover .product-img-wrap img { transform: scale(1.04); }
    .quick-add-wrap { position: absolute; bottom: 20px; left: 20px; right: 20px; transform: translateY(15px); opacity: 0; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .product-card:hover .quick-add-wrap { transform: translateY(0); opacity: 1; }
    .btn-quick-add { width: 100%; padding: 18px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: var(--text-primary); border: 1px solid rgba(0, 0, 0, 0.1); font-family: var(--font-body); font-weight: 500; text-transform: uppercase; letter-spacing: 2px; font-size: 0.75rem; transition: var(--transition-fast); cursor: pointer; position: relative; z-index: 10; }
    .btn-quick-add:hover { background: var(--text-primary); color: var(--white); border-color: var(--text-primary); }
    .product-info { display: flex; flex-direction: column; }
    .product-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px; }
    .product-header h3 { font-size: 1.5rem; margin: 0; line-height: 1.2; }
    .product-price { display: flex; flex-direction: row; align-items: baseline; gap: 12px; }
    .product-price .price { font-family: var(--font-body); font-weight: 500; font-size: 1.1rem; }
    .product-price .old-price { color: #A0A0A0; text-decoration: line-through; font-size: 0.9rem; font-family: var(--font-body); }
    .product-color { font-size: 0.85rem; color: var(--text-secondary); margin: 0; }
    
    #no-results-msg { grid-column: 1 / -1; text-align: center; padding: 60px 0; color: var(--text-secondary); font-size: 1.1rem; display: none; }

    /* --- SIDE CART & TOAST (Omissis CSS per non allungare, è uguale) --- */
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
    .remove-item { color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; text-decoration: none; cursor: pointer; transition: color var(--transition-fast); }
    .remove-item:hover { color: var(--text-primary); }

    .coupon-section { padding: 0 40px 20px; display: flex; gap: 10px; border-bottom: 1px solid var(--border-color); }
    .coupon-section input { flex: 1; padding: 12px 15px; border: 1px solid var(--border-color); background: transparent; font-family: var(--font-body); outline: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
    .coupon-section button { padding: 0 25px; background: var(--text-primary); color: var(--white); border: none; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.75rem; cursor: pointer; transition: opacity 0.3s; }
    .coupon-section button:hover { opacity: 0.8; }
    
    .cart-footer { padding: 25px 40px 30px; background-color: var(--bg-primary); }
    .discount-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 0.9rem; color: #b91c1c; font-weight: 500; display: none; }
    .cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
    .cart-total span:first-child { text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); }
    .cart-total span:last-child { font-size: 1.8rem; font-family: var(--font-heading); color: var(--text-primary); line-height: 1; }
    .btn-checkout { width: 100%; padding: 24px; background-color: var(--text-primary); color: var(--white); border: 1px solid var(--text-primary); font-family: var(--font-body); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; transition: all 0.4s ease; cursor: pointer; display: flex; justify-content: center; align-items: center; }
    .btn-checkout:hover { background-color: transparent; color: var(--text-primary); }

    /* --- FOOTER --- */
    footer { background-color: var(--text-primary); color: var(--white); padding: 120px 0 40px; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 100px; margin-bottom: 100px; }
    .footer-brand h2 { font-family: var(--font-heading); font-size: 3.5rem; margin-bottom: 25px; color: var(--white); }
    .footer-brand p { color: #A0A0A0; margin-bottom: 40px; max-width: 400px; font-size: 0.95rem; line-height: 1.8; }
    .newsletter-form { display: flex; border-bottom: 1px solid #444; padding-bottom: 15px; }
    .newsletter-form input { background: transparent; border: none; color: var(--white); flex: 1; font-family: var(--font-body); outline: none; font-size: 0.9rem; letter-spacing: 1px; }
    .newsletter-form button { background: transparent; color: var(--white); border: none; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; font-size: 0.75rem; cursor: pointer; }
    .footer-links h4 { color: var(--white); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 30px; }
    .footer-links ul { list-style: none; }
    .footer-links ul li { margin-bottom: 15px; }
    .footer-links ul li a { color: #A0A0A0; text-decoration: none; transition: color var(--transition-fast); font-size: 0.9rem; }
    .footer-links ul li a:hover { color: var(--white); }
    .footer-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 40px; border-top: 1px solid #222; color: #666; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
    .social-links a { color: var(--white); margin-left: 25px; font-size: 1.2rem; transition: opacity var(--transition-fast); }
    .social-links a:hover { opacity: 0.6; }

    /* --- TOAST & MODALE --- */
    .btn:active, .btn-quick-add:active, .btn-checkout:active, .auth-form button:active, .coupon-section button:active { transform: scale(0.98) !important; }
    #toast-container { position: fixed; bottom: 30px; right: 30px; z-index: 9999; display: flex; flex-direction: column; gap: 15px; }
    .toast { background-color: var(--text-primary); color: var(--white); padding: 20px 30px; font-size: 0.85rem; letter-spacing: 1px; display: flex; align-items: center; gap: 15px; transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .toast.show { transform: translateX(0); }
    .toast i { color: var(--white); }

    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(5px); z-index: 3000; display: flex; justify-content: center; align-items: center; opacity: 0; visibility: hidden; transition: all 0.4s ease; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-content { background: var(--bg-primary); color: var(--text-primary); padding: 50px 60px; width: 100%; max-width: 480px; position: relative; transform: translateY(20px); transition: transform 0.4s ease; border: 1px solid var(--border-color); box-shadow: 0 30px 60px rgba(0,0,0,0.08); }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    .close-modal { position: absolute; top: 25px; right: 25px; font-size: 1.2rem; cursor: pointer; color: var(--text-secondary); transition: color var(--transition-fast); }
    .close-modal:hover { color: var(--text-primary); }
    
    .form-toggle { display: flex; gap: 30px; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); }
    .form-toggle span { cursor: pointer; color: var(--text-secondary); font-weight: 500; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 15px; position: relative; transition: color var(--transition-fast); }
    .form-toggle span.active { color: var(--text-primary); font-weight: 600; }
    .form-toggle span.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background-color: var(--text-primary); }
    
    .auth-form { display: flex; flex-direction: column; gap: 25px; }
    .auth-form input { padding: 10px 0; border: none; border-bottom: 1px solid var(--border-color); background: transparent; color: var(--text-primary); outline: none; font-size: 0.95rem; transition: border-color var(--transition-fast); }
    .auth-form input:focus { border-color: var(--text-primary); }
    .auth-form button { margin-top: 15px; padding: 22px; background-color: var(--text-primary); color: var(--white); border: 1px solid var(--text-primary); font-weight: 500; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; font-size: 0.8rem; transition: all var(--transition-fast); }
    .auth-form button:hover { background-color: transparent; color: var(--text-primary); }
    
    #register-form { display: none; }
    .register-names-row { display: flex; gap: 15px; flex-wrap: wrap; }
    .register-names-row input { flex: 1; min-width: 140px; }

    /* --- ANIMATIONS --- */
    .reveal { opacity: 0; transform: translateY(40px); transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
    .delay-1 { transition-delay: 0.1s; } .delay-2 { transition-delay: 0.2s; } .delay-3 { transition-delay: 0.3s; }

    /* --- MEDIA QUERIES --- */
    @media (max-width: 1200px) { .hero h1 { font-size: 5rem; } .product-grid { gap: 60px 30px; } }
    @media (max-width: 992px) {
      .story-grid { grid-template-columns: 1fr; gap: 48px; }
      .story { padding: 100px 0; }
      .story-content h2 { font-size: clamp(2.25rem, 5vw, 3rem); }
      .quote-section { padding: 100px 0; }
      .quote-section h2 { font-size: clamp(2rem, 5.5vw, 3.25rem); }
      .products { padding: 100px 0; }
      .footer-grid { grid-template-columns: 1fr 1fr; gap: 48px; }
      .hero h1 { font-size: clamp(3rem, 6vw, 4rem); }
    }
    @media (max-width: 768px) {
      .hero { height: auto; min-height: 100vh; min-height: 100svh; min-height: 100dvh; padding-top: 88px; background-attachment: scroll; }
      .hero h1 { font-size: clamp(2rem, 9vw, 3.2rem); margin-bottom: 20px; letter-spacing: -0.04em; }
      .hero p { font-size: 1rem; margin-bottom: 28px; max-width: 100%; }
      .hero .btn { padding: 18px 36px; width: 100%; max-width: 320px; justify-content: center; }
      header { padding: 16px 0; }
      .logo { font-size: clamp(1.35rem, 5vw, 1.85rem); }
      .hamburger { display: block; color: var(--white); padding: 8px; margin: -8px -8px -8px 0; }
      header.scrolled .hamburger { color: var(--text-primary); }
      .header-actions { gap: 14px; flex-shrink: 0; }
      #user-name-display { max-width: 72px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
      .nav-links { position: fixed; top: 0; left: 0; width: 100%; height: 100vh; height: 100dvh; padding: env(safe-area-inset-top) 0 env(safe-area-inset-bottom); background-color: var(--bg-primary); flex-direction: column; justify-content: center; align-items: center; gap: 28px; transition: 0.4s ease-in-out; opacity: 0; visibility: hidden; z-index: -1; }
      .nav-links.active { opacity: 1; visibility: visible; z-index: 1000; }
      .nav-links a { color: var(--text-primary); font-size: clamp(1.25rem, 4.5vw, 1.5rem); }
      .story { padding: 72px 0; }
      .story-content h2 { font-size: clamp(1.85rem, 7vw, 2.75rem); }
      .quote-section { padding: 72px 0; }
      .quote-section h2 { font-size: clamp(1.65rem, 6.5vw, 2.5rem); line-height: 1.25; padding: 0 4px; }
      .products { padding: 72px 0; }
      .section-header { margin-bottom: 36px; }
      .section-header h2 { font-size: clamp(2rem, 8vw, 3rem); }
      .product-grid { grid-template-columns: 1fr; gap: 48px 0; }
      .product-header { flex-direction: column; align-items: flex-start; gap: 10px; }
      .product-header h3 { font-size: clamp(1.2rem, 4.2vw, 1.45rem); }
      .product-price { flex-wrap: wrap; }
      @media (hover: none) and (pointer: coarse) {
        .quick-add-wrap { transform: translateY(0); opacity: 1; }
      }
      footer { padding: 72px 0 32px; }
      .footer-brand h2 { font-size: clamp(2rem, 8vw, 2.75rem); }
      .footer-grid { grid-template-columns: 1fr; gap: 40px; margin-bottom: 56px; }
      .footer-bottom { flex-direction: column; gap: 20px; align-items: flex-start; }
      .social-links a:first-child { margin-left: 0; }
      .cart-drawer { max-width: 100%; }
      .cart-header, .cart-items, .coupon-section, .cart-footer { padding-left: max(20px, env(safe-area-inset-left)); padding-right: max(20px, env(safe-area-inset-right)); }
      .cart-header { padding-top: max(24px, env(safe-area-inset-top)); }
      .modal-content { padding: 36px 22px; width: 100%; max-width: 420px; margin: 12px; }
      .collection-controls { flex-direction: column; align-items: stretch; gap: 16px; }
      .collection-search { max-width: 100%; }
      .category-filters { gap: 10px; }
      .filter-btn { padding: 10px 18px; font-size: 0.7rem; }
      .sort-wrap label { font-size: 0.7rem; }
      .sort-wrap select { font-size: 0.7rem; padding: 10px 38px 10px 18px; min-width: 0; width: 100%; max-width: 100%; background-position: right 16px center; }
      #toast-container { left: 12px; right: 12px; bottom: max(16px, env(safe-area-inset-bottom)); display: flex; flex-direction: column; align-items: stretch; }
      .toast { transform: translateY(120%); width: 100%; justify-content: center; padding: 16px 20px; font-size: 0.8rem; }
      .toast.show { transform: translateY(0); }
      .register-names-row { flex-direction: column; gap: 0; }
      .register-names-row input { width: 100%; min-width: 0; }
    }
    @media (max-width: 480px) {
      .marquee-content span { padding: 0 28px; font-size: 0.72rem; }
      .coupon-section { flex-direction: column; align-items: stretch; }
      .coupon-section button { padding: 14px; width: 100%; }
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header id="main-header">
    <div class="container">
      <a href="#" class="logo">Luxury Sofà.</a>
      <nav>
        <ul class="nav-links" id="nav-links">
          <li><a href="#home">Home</a></li>
          <li><a href="#storia">La Storia</a></li>
          <li><a href="#collezione">Collezione</a></li>
          <li><a href="#contatti">Contatti</a></li>
        </ul>
      </nav>
      <div class="header-actions">
        <a href="wishlist.php" class="wishlist-nav" id="wishlist-nav" title="Preferiti" aria-label="Preferiti">
          <i class="far fa-heart"></i>
          <span class="wishlist-badge" id="wishlist-badge">0</span>
        </a>
        <div class="user-btn" id="login-icon" title="Area Riservata">
          <i class="<?= isset($_SESSION['user_id']) ? 'fas' : 'far' ?> fa-user"></i>
          <span id="user-name-display" style="display: <?= isset($_SESSION['user_id']) ? 'inline' : 'none' ?>;">
            <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Accedi' ?>
          </span>
        </div>
        <div class="cart-btn" id="cart-icon">
          <i class="fas fa-shopping-bag"></i>
          <span class="cart-badge" id="cart-badge">0</span>
        </div>
        <div class="hamburger" id="hamburger">
          <i class="fas fa-bars"></i>
        </div>
      </div>
    </div>
  </header>

  <!-- SIDE CART DRAWER -->
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

  <!-- LOGIN/REGISTER MODAL -->
  <div class="modal-overlay" id="login-modal">
    <div class="modal-content">
      <i class="fas fa-times close-modal" id="close-login"></i>
      
      <div class="form-toggle">
        <span class="active" id="toggle-login">Accedi</span>
        <span id="toggle-register">Crea Account</span>
      </div>

      <form class="auth-form" id="login-form">
        <input type="email" name="email" placeholder="Indirizzo Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Entra nel Profilo</button>
      </form>

      <form class="auth-form" id="register-form">
        <div class="register-names-row">
            <input type="text" name="first_name" placeholder="Nome" required>
            <input type="text" name="last_name" placeholder="Cognome" required>
        </div>
        <input type="email" name="email" placeholder="Indirizzo Email" required>
        <input type="password" name="password" placeholder="Crea Password" required>
        <button type="submit">Registrati</button>
      </form>
    </div>
  </div>

  <main>
    <section id="home" class="hero">
      <div class="container">
        <div class="hero-content reveal active">
          <span class="subtitle">Nuova Collezione 2026</span>
          <h1>L'Essenza del Comfort.</h1>
          <p>Ridefiniamo il concetto di relax con arredi dal rigore sartoriale. Materiali assoluti, linee architettoniche e un comfort senza compromessi.</p>
          <div style="display: flex; gap: 15px;">
            <a href="#collezione" class="btn">Esplora</a>
          </div>
        </div>
      </div>
    </section>

    <div class="marquee-container">
      <div class="marquee-content">
        <span>Design Italiano</span><span>Spedizione White Glove</span><span>Pellami Selezionati</span><span>Garanzia 10 Anni</span>
        <span>Design Italiano</span><span>Spedizione White Glove</span><span>Pellami Selezionati</span><span>Garanzia 10 Anni</span>
        <span>Design Italiano</span><span>Spedizione White Glove</span>
      </div>
    </div>

    <section id="storia" class="story">
      <div class="container">
        <div class="story-grid">
          <div class="story-img reveal">
            <img src="https://cdn.shopify.com/s/files/1/0854/6936/4498/files/PS_Biancolina_IT_CY_MT_schienali_indietro.jpg?v=1715677744" alt="Artigianato Italiano">
          </div>
          <div class="story-content reveal delay-1">
            <span class="subtitle">Manifesto</span>
            <h2>Maestri dell'Artigianato dal 1985.</h2>
            <p>Ogni divano nasce da una sottrazione, dalla ricerca ostinata dell'essenziale. I nostri maestri artigiani plasmano la materia prima seguendo antiche regole di proporzione.</p>
            <p>Non costruiamo semplici sedute, ma architetture domestiche concepite per resistere alle mode e al tempo.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="quote-section reveal">
      <div class="container">
        <h2>"Il vero lusso è l'assoluta libertà di vivere lo spazio."</h2>
        <p>— Direzione Creativa 2026</p>
      </div>
    </section>

    <section id="collezione" class="products">
      <div class="container">
        
        <div class="section-header reveal">
          <span class="subtitle">Archivio</span>
          <h2>La Collezione</h2>
        </div>

        <!-- BARRA CONTROLLI (Filtri + Ricerca) -->
        <div class="collection-controls reveal">
          <div class="category-filters">
            <button class="filter-btn active" data-filter="all">Tutti</button>
            <button class="filter-btn" data-filter="nuovo">Nuovi Arrivi</button>
            <button class="filter-btn" data-filter="bestseller">Più Venduti</button>
          </div>
          <div class="sort-wrap">
            <label for="sort-products">Ordina</label>
            <select id="sort-products" aria-label="Ordina prodotti">
              <option value="default">In evidenza</option>
              <option value="price-asc">Prezzo ↑</option>
              <option value="price-desc">Prezzo ↓</option>
              <option value="newest">Più recenti</option>
            </select>
          </div>
          <div class="collection-search">
            <i class="fas fa-search"></i>
            <input type="text" id="product-search" placeholder="Cerca un modello o colore...">
          </div>
        </div>

        <div class="product-grid" id="product-grid">
          <?php 
          $delayCounter = 1;
          foreach ($products as $p): 
              $delayClass = 'delay-' . ($delayCounter % 3 == 0 ? 3 : ($delayCounter % 3));
              
              // Recupera il badge dal database, se esiste, forzandolo a minuscolo per facilitare il filtro
              $badgeText = isset($p['badge']) ? $p['badge'] : '';
              $badgeFilterData = strtolower($badgeText);
          ?>
          
          <!-- L'attributo data-badge serve al JavaScript per i filtri -->
          <div class="product-card reveal <?= $delayClass ?>" data-badge="<?= htmlspecialchars($badgeFilterData) ?>" data-product-id="<?= (int)$p['id'] ?>" data-price="<?= htmlspecialchars((string)(float)$p['price']) ?>" data-sku="<?= htmlspecialchars($p['sku']) ?>" onclick="window.location.href='product.php?id=<?= $p['id'] ?>'">
            <div class="product-img-wrap">
              
              <!-- Mostra il badge visivo se presente nel DB -->
              <?php if (!empty($badgeText)): ?>
                 <span class="product-badge badge-<?= $badgeFilterData ?>"><?= htmlspecialchars($badgeText) ?></span>
              <?php endif; ?>

              <button type="button" class="wishlist-heart" title="Salva nei preferiti" aria-label="Preferiti" data-pid="<?= (int)$p['id'] ?>" data-sku="<?= htmlspecialchars($p['sku'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>" data-price="<?= htmlspecialchars((string)(float)$p['price']) ?>" data-img="<?= htmlspecialchars($p['main_image_url'], ENT_QUOTES) ?>" onclick="event.stopPropagation(); window.toggleWishlistBtn(this);"><i class="far fa-heart"></i></button>

              <img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" decoding="async" width="640" height="800">
              
              <div class="quick-add-wrap">
                <button class="btn-quick-add" onclick="event.stopPropagation(); addToCart('<?= $p['sku'] ?>', '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>, '<?= $p['main_image_url'] ?>')">Aggiungi</button>
              </div>
            </div>
            <div class="product-info">
              <div class="product-header">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <div class="product-price">
                  <?php if (!empty($p['old_price'])): ?>
                    <span class="old-price">€<?= number_format($p['old_price'], 0, ',', '.') ?></span>
                  <?php endif; ?>
                  <span class="price">€<?= number_format($p['price'], 0, ',', '.') ?></span>
                </div>
              </div>
              <p class="product-color"><?= htmlspecialchars($p['description_color_material']) ?></p>
            </div>
          </div>
          <?php 
          $delayCounter++;
          endforeach; 
          
          if (empty($products)):
          ?>
            <p class="text-center w-100 py-5">Nessun prodotto disponibile al momento.</p>
          <?php endif; ?>
          
          <div id="no-results-msg">Nessun divano trovato con i criteri selezionati.</div>
        </div>
      </div>
    </section>
  </main>

  <footer id="contatti">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand reveal">
          <h2>Luxury Sofà.</h2>
          <p>Ispiriamo il tuo abitare con arredi che combinano estetica moderna, comfort assoluto e sapienza artigianale italiana.</p>
          <form class="newsletter-form" id="newsletter-form">
            <input type="email" name="email" placeholder="La tua email" required>
            <button type="submit">Iscriviti</button>
          </form>
        </div>
        
        <div class="footer-links reveal delay-1">
          <h4>Esplora</h4>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#storia">Manifesto</a></li>
            <li><a href="#collezione">Archivio</a></li>
            <li><a href="wishlist.php">Preferiti</a></li>
          </ul>
        </div>

        <div class="footer-links reveal delay-2">
          <h4>Assistenza</h4>
          <ul>
            <li><a href="legali.php#privacy">Privacy</a></li>
            <li><a href="legali.php#spedizioni">Spedizioni e resi</a></li>
            <li><a href="legali.php#termini">Condizioni d'uso</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 Luxury Sofà s.r.l. - Tutti i diritti riservati.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-pinterest"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <script src="js/store-common.js"></script>
  <script>
    // --- UTILS & HEADER SCROLL ---
    const header = document.getElementById('main-header');
    window.addEventListener('scroll', () => { header.classList.toggle('scrolled', window.scrollY > 50); });

    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('active');
      const icon = hamburger.querySelector('i');
      icon.className = navLinks.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
      if(navLinks.classList.contains('active')) header.classList.add('scrolled');
    });

    document.querySelectorAll('.nav-links a').forEach(item => {
      item.addEventListener('click', () => {
        navLinks.classList.remove('active');
        hamburger.querySelector('i').className = 'fas fa-bars';
      });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        window.scrollTo({ top: document.querySelector(targetId).getBoundingClientRect().top + window.pageYOffset - 80, behavior: "smooth" });
      });
    });

    function reveal() {
      document.querySelectorAll(".reveal").forEach(rev => {
        if (rev.getBoundingClientRect().top < window.innerHeight - 100) rev.classList.add("active");
      });
    }
    window.addEventListener("scroll", reveal);
    reveal();

    function showToast(message) {
      const toastContainer = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = 'toast';
      toast.innerHTML = `<i class="fas fa-check-circle" style="color:#FFF;"></i> <span>${message}</span>`;
      toastContainer.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
    }

    // --- LOGICA DI RICERCA E FILTRI (LIVE) ---
    const searchInput = document.getElementById('product-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    const noResultsMsg = document.getElementById('no-results-msg');
    
    let currentCategory = 'all';
    const sortSelect = document.getElementById('sort-products');

    function sortProductCards() {
      const grid = document.getElementById('product-grid');
      const msg = document.getElementById('no-results-msg');
      const cards = Array.from(grid.querySelectorAll('.product-card'));
      const mode = sortSelect ? sortSelect.value : 'default';
      cards.sort((a, b) => {
        if (mode === 'price-asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (mode === 'price-desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        if (mode === 'newest') return parseInt(b.dataset.productId, 10) - parseInt(a.dataset.productId, 10);
        return parseInt(a.dataset.productId, 10) - parseInt(b.dataset.productId, 10);
      });
      cards.forEach(c => grid.insertBefore(c, msg));
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCards = 0;

        productCards.forEach(card => {
            const title = card.querySelector('.product-header h3').textContent.toLowerCase();
            const color = card.querySelector('.product-color').textContent.toLowerCase();
            const badge = card.dataset.badge || '';

            // Controlla se rispetta la ricerca di testo
            const matchesSearch = title.includes(searchTerm) || color.includes(searchTerm);
            // Controlla se rispetta la categoria selezionata
            const matchesCategory = (currentCategory === 'all') || (badge.includes(currentCategory));

            if(matchesSearch && matchesCategory) {
                card.style.display = 'flex';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                visibleCards++;
            } else {
                card.style.display = 'none';
            }
        });

        if(visibleCards === 0 && productCards.length > 0) {
            noResultsMsg.style.display = 'block';
        } else {
            noResultsMsg.style.display = 'none';
        }
        sortProductCards();
    }

    // Event listener per la barra di ricerca
    searchInput.addEventListener('input', applyFilters);
    if (sortSelect) sortSelect.addEventListener('change', applyFilters);

    // Event listener per i pulsanti Categoria (Tutti, Nuovo, Bestseller)
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCategory = btn.dataset.filter;
            applyFilters();
        });
    });

    sortProductCards();

    // --- CARRELLO (SALVATO IN LOCALSTORAGE) ---
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

    window.toggleWishlistBtn = function(btn) {
      const item = {
        sku: btn.dataset.sku,
        pid: parseInt(btn.dataset.pid, 10),
        name: btn.dataset.name,
        price: parseFloat(btn.dataset.price),
        imgUrl: btn.dataset.img
      };
      const on = LUXURY_STORE.toggleWishlist(item);
      btn.classList.toggle('is-active', on);
      const ic = btn.querySelector('i');
      if (ic) ic.className = on ? 'fas fa-heart' : 'far fa-heart';
      LUXURY_STORE.updateWishlistBadges();
      showToast(on ? 'Salvato nei preferiti' : 'Rimosso dai preferiti');
    };

    (function syncWishlistHearts() {
      document.querySelectorAll('.wishlist-heart').forEach(btn => {
        const on = LUXURY_STORE.isInWishlist(btn.dataset.sku);
        btn.classList.toggle('is-active', on);
        const ic = btn.querySelector('i');
        if (ic) ic.className = on ? 'fas fa-heart' : 'far fa-heart';
      });
      LUXURY_STORE.updateWishlistBadges();
    })();

    // --- MODALE LOGIN / REGISTRAZIONE ---
    const loginIcon = document.getElementById('login-icon');
    const loginModal = document.getElementById('login-modal');
    const closeLogin = document.getElementById('close-login');
    const userNameDisplay = document.getElementById('user-name-display');
    const toggleLogin = document.getElementById('toggle-login');
    const toggleRegister = document.getElementById('toggle-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    let isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

    loginIcon.addEventListener('click', () => {
      if(isLoggedIn) {
         window.location.href = 'profilo.php';
      } else {
         loginModal.classList.add('active');
      }
    });

    closeLogin.addEventListener('click', () => loginModal.classList.remove('active'));

    toggleLogin.addEventListener('click', () => {
        toggleLogin.classList.add('active'); toggleRegister.classList.remove('active');
        loginForm.style.display = 'flex'; registerForm.style.display = 'none';
    });
    toggleRegister.addEventListener('click', () => {
        toggleRegister.classList.add('active'); toggleLogin.classList.remove('active');
        registerForm.style.display = 'flex'; loginForm.style.display = 'none';
    });

    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm); formData.append('action', 'login');
      const btn = loginForm.querySelector('button'); btn.innerText = "Accesso in corso...";

      try {
          const res = await fetch('api.php', { method: 'POST', body: formData });
          const data = await res.json();
          if(data.success) {
              isLoggedIn = true; loginModal.classList.remove('active');
              loginIcon.querySelector('i').classList.replace('far', 'fas'); 
              userNameDisplay.innerText = data.user_name; userNameDisplay.style.display = 'inline';
              showToast(data.message);
          } else { showToast(data.message); }
      } catch(e) { showToast("Errore di connessione."); } 
      finally { btn.innerText = "Entra nel Profilo"; }
    });

    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(registerForm); formData.append('action', 'register');
      const btn = registerForm.querySelector('button'); btn.innerText = "Creazione in corso...";

      try {
          const res = await fetch('api.php', { method: 'POST', body: formData });
          const data = await res.json();
          if(data.success) {
              isLoggedIn = true; loginModal.classList.remove('active');
              loginIcon.querySelector('i').classList.replace('far', 'fas'); 
              userNameDisplay.innerText = data.user_name; userNameDisplay.style.display = 'inline';
              registerForm.reset();
              showToast(data.message);
          } else { showToast(data.message); }
      } catch(e) { showToast("Errore di connessione."); } 
      finally { btn.innerText = "Registrati"; }
    });

    document.getElementById('newsletter-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form); formData.append('action', 'newsletter');
      const btn = form.querySelector('button'); btn.innerText = "Attendere...";

      try {
          const res = await fetch('api.php', { method: 'POST', body: formData });
          const data = await res.json();
          showToast(data.message); if (data.success) form.reset();
      } catch(err) { showToast("Errore di rete."); } 
      finally { btn.innerText = "Iscriviti"; }
    });
  </script>
</body>
</html>