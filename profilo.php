<?php
session_start();
require_once 'db.php';

// Se l'utente NON è loggato, lo rimandiamo alla home
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Recuperiamo i dati dell'utente
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Recuperiamo gli ordini dell'utente
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Il mio Profilo | Luxury Sofà</title>
  <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* --- VARIABILI E BASE --- */
    :root {
      --bg-primary: #FDFDFB; --text-primary: #000000; --text-secondary: #707070; 
      --white: #FFFFFF; --border-color: #E5E5E5;
      --font-heading: 'Playfair Display', serif; --font-body: 'Inter', sans-serif;
      --transition-fast: 0.3s ease;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; }
    body { font-family: var(--font-body); background-color: var(--bg-primary); color: var(--text-primary); line-height: 1.5; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
    .container { width: min(92%, 1200px); max-width: 1200px; margin: 0 auto; padding-left: env(safe-area-inset-left); padding-right: env(safe-area-inset-right); }
    a { text-decoration: none; color: inherit; }
    
    /* --- HEADER SPECIFICO --- */
    header { padding: 25px 0; padding-top: max(16px, env(safe-area-inset-top)); background-color: var(--bg-primary); border-bottom: 1px solid var(--border-color); }
    header .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px 16px; }
    .logo { font-family: var(--font-heading); font-size: clamp(1.35rem, 4vw, 2rem); font-weight: 500; color: var(--text-primary); letter-spacing: -1px; }
    .back-btn { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 500; display: inline-flex; align-items: center; gap: 10px; transition: var(--transition-fast); white-space: nowrap; }
    .back-btn:hover { color: var(--text-secondary); }

    /* --- LAYOUT PROFILO --- */
    .profile-layout { display: grid; grid-template-columns: 1fr 2.5fr; gap: 80px; padding: 80px 0 150px; padding-bottom: max(80px, env(safe-area-inset-bottom)); }
    
    /* Colonna Info */
    .profile-sidebar h1 { font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 10px; line-height: 1.1; }
    .profile-sidebar p { color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 40px; }
    .user-details { border-top: 1px solid var(--border-color); padding-top: 30px; margin-bottom: 40px; }
    .detail-group { margin-bottom: 25px; }
    .detail-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; color: var(--text-secondary); display: block; margin-bottom: 5px; }
    .detail-value { font-size: 1.1rem; font-weight: 500; }
    
    /* Bottone Logout */
    .btn-logout { display: inline-block; padding: 18px 40px; background-color: transparent; border: 1px solid var(--text-primary); color: var(--text-primary); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all var(--transition-fast); width: 100%; text-align: center; }
    .btn-logout:hover { background-color: var(--text-primary); color: var(--white); }

    /* Colonna Ordini */
    .section-title { font-family: var(--font-heading); font-size: 2rem; margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; }
    
    .order-card { border: 1px solid var(--border-color); padding: 30px; margin-bottom: 20px; background: #fff; display: flex; justify-content: space-between; align-items: center; transition: box-shadow 0.4s ease; }
    .order-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-color: #d0d0d0; }
    .order-info h3 { font-family: var(--font-body); font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; }
    .order-info p { color: var(--text-secondary); font-size: 0.9rem; }
    .order-status { padding: 6px 15px; border-radius: 50px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; display: inline-block; margin-top: 10px; }
    .status-paid { background: #e6f4ea; color: #1e4620; }
    .status-pending { background: #fef3c7; color: #684b00; }
    .order-price { font-family: var(--font-heading); font-size: 1.5rem; }

    .empty-orders { color: var(--text-secondary); font-style: italic; }

    /* --- ANIMATIONS (Nuove!) --- */
    .reveal { opacity: 0; transform: translateY(40px); transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
    .delay-1 { transition-delay: 0.1s; } 
    .delay-2 { transition-delay: 0.2s; } 
    .delay-3 { transition-delay: 0.3s; }

    @media (max-width: 900px) {
        .profile-layout { grid-template-columns: 1fr; gap: 40px; padding: 48px 0 100px; }
        .profile-sidebar h1 { font-size: clamp(1.75rem, 6vw, 2.5rem); }
        .section-title { font-size: clamp(1.5rem, 5vw, 2rem); margin-bottom: 28px; }
        .order-card { flex-direction: column; align-items: flex-start; gap: 15px; padding: 22px; }
        .order-price { align-self: flex-end; }
    }
    @media (max-width: 480px) {
        header .container { flex-direction: column; align-items: flex-start; }
        .back-btn { font-size: 0.72rem; letter-spacing: 1px; }
        .btn-logout { padding: 16px 24px; font-size: 0.75rem; }
        .order-card { padding: 18px; }
        .order-info h3 { font-size: 1rem; }
        .order-price { font-size: 1.35rem; align-self: flex-start; }
    }
  </style>
</head>
<body>

  <header>
    <div class="container reveal active">
      <a href="index.php" class="logo">Luxury Sofà.</a>
      <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Torna allo Store</a>
    </div>
  </header>

  <main class="container">
    <div class="profile-layout">
      
      <!-- Colonna Sinistra (Dati Utente) - Con animazione base -->
      <aside class="profile-sidebar reveal">
        <h1>Benvenuto,<br><?= htmlspecialchars($user['first_name']) ?>.</h1>
        <p>Gestisci il tuo account e i tuoi ordini.</p>
        
        <div class="user-details">
            <div class="detail-group">
                <span class="detail-label">Nome Completo</span>
                <span class="detail-value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
            </div>
            <div class="detail-group">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="detail-group">
                <span class="detail-label">Membro dal</span>
                <span class="detail-value"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <a href="logout.php" class="btn-logout">Disconnetti Account</a>
      </aside>

      <!-- Colonna Destra (Storico Ordini) -->
      <section class="profile-orders">
        <!-- Titolo con leggero delay -->
        <h2 class="section-title reveal delay-1">Storico Ordini</h2>

        <?php if (empty($orders)): ?>
            <p class="empty-orders reveal delay-2">Non hai ancora effettuato nessun ordine.</p>
        <?php else: ?>
            <?php 
            $delayCounter = 1;
            foreach ($orders as $order): 
                // Genera dinamicamente le classi delay-1, delay-2, delay-3
                $delayClass = 'delay-' . ($delayCounter % 3 == 0 ? 3 : ($delayCounter % 3));
            ?>
                <!-- Singolo ordine animato -->
                <div class="order-card reveal <?= $delayClass ?>">
                    <div class="order-info">
                        <h3>Ordine #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h3>
                        <p>Effettuato il: <?= date('d M Y - H:i', strtotime($order['created_at'])) ?></p>
                        <?php 
                            $statusClass = ($order['status'] == 'paid') ? 'status-paid' : 'status-pending';
                            $statusText = ($order['status'] == 'paid') ? 'Pagato & In Lavorazione' : 'In attesa di pagamento';
                        ?>
                        <span class="order-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                    <div class="order-price">
                        €<?= number_format($order['total_amount'], 2, ',', '.') ?>
                    </div>
                </div>
            <?php 
            $delayCounter++;
            endforeach; 
            ?>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <!-- SCRIPT PER LE ANIMAZIONI -->
  <script>
    function reveal() {
      document.querySelectorAll(".reveal").forEach(rev => {
        // Usa un margine più piccolo rispetto alla home per attivare prima l'animazione
        if (rev.getBoundingClientRect().top < window.innerHeight - 50) {
            rev.classList.add("active");
        }
      });
    }
    
    // Controlla lo scroll
    window.addEventListener("scroll", reveal);
    
    // Lancia subito l'animazione appena la pagina carica
    window.addEventListener("load", reveal);
    reveal();
  </script>
</body>
</html>