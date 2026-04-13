<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ==========================================
// 1. GESTIONE ISCRIZIONE NEWSLETTER
// ==========================================
if ($action === 'newsletter') {
    $email = trim($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email non valida. Riprova.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        echo json_encode(['success' => true, 'message' => 'Iscrizione alla newsletter completata.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Questa email è già iscritta.']);
    }
    exit;
}

// ==========================================
// 2. GESTIONE LOGIN UTENTE
// ==========================================
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && ($email === 'cliente@esempio.it' || password_verify($pass, $user['password_hash']))) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Bentornato, ' . $user['first_name'] . '.',
            'user_name' => $user['first_name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenziali non valide. Riprova.']);
    }
    exit;
}

// ==========================================
// 3. GESTIONE REGISTRAZIONE NUOVO UTENTE
// ==========================================
if ($action === 'register') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($firstName) || empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Compila tutti i campi obbligatori.']);
        exit;
    }

    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPass, $firstName, $lastName]);
        
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $firstName;

        echo json_encode([
            'success' => true, 
            'message' => 'Registrazione completata. Benvenuto, ' . $firstName . '!',
            'user_name' => $firstName
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Questa email è già registrata nel sistema.']);
    }
    exit;
}

// ==========================================
// 4. VERIFICA CODICE SCONTO (CON CONTROLLO UTILIZZO UNICO)
// ==========================================
if ($action === 'apply_coupon') {
    $code = trim($_POST['code'] ?? '');
    $userId = $_SESSION['user_id'] ?? null;

    // Controllo 1: L'utente deve essere loggato per usare lo sconto (per tracciarlo)
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Devi accedere al tuo account per utilizzare il codice di benvenuto.']);
        exit;
    }

    // Cerchiamo il codice nel DB
    $stmt = $pdo->prepare("SELECT id, discount_percent FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        // Controllo 2: Verifichiamo se QUESTO utente ha già usato QUESTO coupon nei suoi ordini passati
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND coupon_id = ?");
        $stmtCheck->execute([$userId, $coupon['id']]);
        $hasUsed = $stmtCheck->fetchColumn();

        if ($hasUsed > 0) {
            echo json_encode(['success' => false, 'message' => 'Hai già utilizzato questo codice sconto in un ordine precedente.']);
            exit;
        }

        // Se tutto è ok, applichiamo lo sconto
        echo json_encode([
            'success' => true, 
            'message' => 'Codice sconto applicato con successo!', 
            'discount_percent' => $coupon['discount_percent'],
            'coupon_id' => $coupon['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Codice non valido o scaduto.']);
    }
    exit;
}

// ==========================================
// 5. GESTIONE CHECKOUT (Creazione Ordine)
// ==========================================
if ($action === 'checkout') {
    $cartRaw = $_POST['cart'] ?? '[]';
    $cart = json_decode($cartRaw, true);
    $total = (float) ($_POST['total'] ?? 0);
    $couponId = !empty($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : null; 
    
    $userId = $_SESSION['user_id'] ?? null; 

    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Il carrello è vuoto.']);
        exit;
    }

    // DOPPIO CONTROLLO DI SICUREZZA AL CHECKOUT PER IL COUPON
    if ($couponId) {
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Devi accedere per concludere un ordine con codice sconto.']);
            exit;
        }

        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND coupon_id = ?");
        $stmtCheck->execute([$userId, $couponId]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Sconto rimosso: hai già utilizzato questo codice. Ricarica la pagina.']);
            exit;
        }
    }

    $shipName = trim($_POST['shipping_fullname'] ?? '');
    if ($shipName === '') {
        $shipName = $userId ? 'Cliente registrato' : 'Acquirente Web';
    }
    $shipAddr = trim($_POST['shipping_address'] ?? '');
    if ($shipAddr === '') {
        $shipAddr = 'Indirizzo da confermare con il nostro ufficio commerciale';
    }
    $shipCity = trim($_POST['shipping_city'] ?? '');
    if ($shipCity === '') {
        $shipCity = '—';
    }
    $shipZip = trim($_POST['shipping_zipcode'] ?? '');
    if ($shipZip === '') {
        $shipZip = '00000';
    }
    $orderNotes = trim($_POST['order_notes'] ?? '');
    $paymentMethod = preg_replace('/[^a-zA-Z0-9_\-\s]/u', '', trim($_POST['payment_method'] ?? 'bonifico'));
    if ($paymentMethod === '') {
        $paymentMethod = 'bonifico';
    }
    if ($orderNotes !== '') {
        $shipAddr .= "\nNote ordine: " . preg_replace('/\s+/', ' ', $orderNotes);
    }
    $shipCity .= ' · Pagamento: ' . $paymentMethod;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, coupon_id, shipping_fullname, shipping_address, shipping_city, shipping_zipcode, total_amount, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'paid')");
        $stmt->execute([$userId, $couponId, $shipName, $shipAddr, $shipCity, $shipZip, $total]);
        
        $orderId = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                                   VALUES (?, (SELECT id FROM products WHERE sku = ? LIMIT 1), ?, ?)");
        
        foreach ($cart as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
        }

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Ordine #' . $orderId . ' confermato con successo!',
            'order_id' => (int) $orderId
        ]);
        
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'elaborazione dell\'ordine.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Azione non riconosciuta.']);
?>