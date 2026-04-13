<?php
session_start();
require_once 'db.php';

$orderId = isset($_GET['order']) ? (int) $_GET['order'] : 0;
$order = null;
$items = [];

if ($orderId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        $orderUserId = $order['user_id'] !== null && $order['user_id'] !== '' ? (int) $order['user_id'] : null;
        $sessionUid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        if ($orderUserId !== null && ($sessionUid === null || $sessionUid !== $orderUserId)) {
            $order = null;
        }
        if ($order) {
            $stmtI = $pdo->prepare("SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?");
            $stmtI->execute([$orderId]);
            $items = $stmtI->fetchAll();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Grazie per il tuo ordine | Luxury Sofà</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --bg: #FDFDFB; --text: #000; --muted: #707070; --border: #E5E5E5; --font-h: 'Playfair Display', serif; --font-b: 'Inter', sans-serif; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-b); background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }
    .container { width: min(92%, 640px); margin: auto; padding: 48px 16px 80px; text-align: center; }
    .mark { width: 64px; height: 64px; border: 1px solid var(--text); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 28px; font-size: 1.4rem; }
    h1 { font-family: var(--font-h); font-weight: 400; font-size: clamp(1.75rem, 4vw, 2.25rem); margin-bottom: 16px; }
    p.lead { color: var(--muted); font-size: 0.95rem; line-height: 1.7; margin-bottom: 32px; }
    .order-box { border: 1px solid var(--border); padding: 24px; text-align: left; margin-bottom: 28px; background: #fff; }
    .order-box h2 { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; color: var(--muted); margin-bottom: 12px; font-weight: 600; }
    .order-num { font-family: var(--font-h); font-size: 1.5rem; margin-bottom: 8px; }
    .item-row { display: flex; justify-content: space-between; font-size: 0.88rem; padding: 8px 0; border-bottom: 1px solid var(--border); color: var(--muted); }
    .item-row:last-child { border-bottom: none; }
    .total-row { display: flex; justify-content: space-between; margin-top: 16px; font-family: var(--font-h); font-size: 1.25rem; }
    .actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
    .actions a {
      display: inline-flex; align-items: center; justify-content: center; padding: 16px 28px;
      font-size: 0.72rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;
      text-decoration: none; border: 1px solid var(--text); color: var(--text); transition: 0.3s;
    }
    .actions a.primary { background: var(--text); color: #fff; }
    .actions a:hover { opacity: 0.85; }
    .err { color: #b45309; }
  </style>
</head>
<body>
  <div class="container">
    <?php if ($order && $orderId > 0): ?>
      <div class="mark"><i class="fas fa-check"></i></div>
      <h1>Grazie per aver scelto Luxury Sofà.</h1>
      <p class="lead">Abbiamo registrato il tuo ordine. Riceverai aggiornamenti via email non appena il nostro team avrà preso in carico la pratica.</p>

      <div class="order-box">
        <h2>Numero ordine</h2>
        <p class="order-num">#<?= str_pad((string) $orderId, 5, '0', STR_PAD_LEFT) ?></p>
        <p style="font-size:0.85rem;color:var(--muted);">Totale: €<?= number_format((float) $order['total_amount'], 2, ',', '.') ?> · Stato: <?= htmlspecialchars($order['status'] === 'paid' ? 'Confermato' : $order['status']) ?></p>
        <?php if (!empty($items)): ?>
          <h2 style="margin-top:20px;">Articoli</h2>
          <?php foreach ($items as $it): ?>
            <div class="item-row">
              <span><?= htmlspecialchars($it['product_name'] ?? 'Prodotto') ?> × <?= (int) $it['quantity'] ?></span>
              <span>€<?= number_format((float) $it['unit_price'] * (int) $it['quantity'], 2, ',', '.') ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="actions">
        <a href="index.php" class="primary">Torna alla home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="profilo.php">Il mio profilo</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <p class="err" style="margin-bottom:20px;">Non è stato possibile mostrare i dettagli di questo ordine.</p>
      <div class="actions">
        <a href="index.php" class="primary">Torna alla home</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
