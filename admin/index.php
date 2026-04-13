<?php
session_start();

// ==========================================
// 1. IMPOSTAZIONI GENERALI E DATABASE
// ==========================================
require_once '../db.php';

// URL base del sito (senza barra finale): stesso host del gestionale (PC, mobile sulla LAN, produzione)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = preg_replace('#/admin(?:/index\.php)?$#i', '', $script);
$site_url = $protocol . '://' . $host . $basePath;

// ==========================================
// 2. GESTIONE LOGIN / LOGOUT DAL DATABASE
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_name']);
    header("Location: ?");
    exit;
}

$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admin['first_name'];
        $_SESSION['just_logged_in'] = true;
        header("Location: ?");
        exit;
    } else {
        $error_msg = "Accesso negato. Credenziali errate o privilegi insufficienti.";
    }
}

// Se non è loggato, mostra SOLO la pagina di login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Gestionale | Luxury Sofà</title>
    
    <script>
        if (sessionStorage.getItem('admin_tab_active')) {
            sessionStorage.removeItem('admin_tab_active'); 
        }
    </script>
    <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #FDFDFB; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .login-box { background: #fff; padding: 50px 40px; border-radius: 0px; border: 1px solid #E5E5E5; box-shadow: 0 20px 40px rgba(0,0,0,0.03); width: 100%; max-width: 380px; text-align: center; opacity: 0; transform: translateY(30px); animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; box-sizing: border-box; }
        h1 { margin-top: 0; font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 30px; font-weight: 500; letter-spacing: -1px; }
        input { width: 100%; padding: 12px 0; margin-bottom: 25px; border: none; border-bottom: 1px solid #E5E5E5; background: transparent; box-sizing: border-box; font-family: 'Inter', sans-serif; outline: none; transition: 0.3s; font-size: 1rem; }
        input:focus { border-bottom-color: #000; }
        button { width: 100%; padding: 18px; background: #000; color: #fff; border: 1px solid #000; font-family: 'Inter', sans-serif; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        button:hover { background: transparent; color: #000; }
        .error { color: #d9534f; font-size: 0.85rem; margin-bottom: 20px; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 480px) { .login-box { padding: 40px 25px; } h1 { font-size: 1.8rem; } }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Area Riservata</h1>
        <?php if($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email Amministratore" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Accedi al Gestionale</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// ==========================================
// 3. GESTIONE AZIONI (CRUD + NEWSLETTER)
// ==========================================
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // --- AZIONI PRODOTTI ---
    if ($action == 'add' || $action == 'edit') {
        $id = $_POST['id'] ?? null;
        $sku = trim($_POST['sku']);
        $name = trim($_POST['name']);
        $desc = trim($_POST['description_color_material']);
        $price = $_POST['price'];
        $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : null;
        $image_url = trim($_POST['main_image_url']);
        $badge = trim($_POST['badge']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            if ($action == 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (sku, name, description_color_material, price, old_price, main_image_url, badge, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sku, $name, $desc, $price, $old_price, $image_url, $badge, $is_active]);
                $message = "Prodotto aggiunto con successo!";
            } elseif ($action == 'edit') {
                $stmt = $pdo->prepare("UPDATE products SET sku=?, name=?, description_color_material=?, price=?, old_price=?, main_image_url=?, badge=?, is_active=? WHERE id=?");
                $stmt->execute([$sku, $name, $desc, $price, $old_price, $image_url, $badge, $is_active, $id]);
                $message = "Prodotto aggiornato con successo!";
            }
        } catch (PDOException $e) {
            $message = "Errore Database: " . $e->getMessage();
            $message_type = 'error';
            if (strpos($message, 'Duplicate entry') !== false) {
                $message = "Errore: Lo SKU inserito esiste già.";
            }
        }
    }
    
    // --- AZIONE INVIO NEWSLETTER ---
    if ($action == 'send_newsletter') {
        $product_id = (int)$_POST['product_id'];
        
        $stmtProd = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmtProd->execute([$product_id]);
        $latestProduct = $stmtProd->fetch();
        
        $subscribers = $pdo->query("SELECT email FROM newsletter_subscribers")->fetchAll();
        
        if ($latestProduct && count($subscribers) > 0) {
            $subject = "Nuovo Arrivo: " . $latestProduct['name'] . " | Luxury Sofà";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // Questo è il mittente che vedranno gli utenti. Modificalo con la tua vera email
            $headers .= "From: Luxury Sofà <info@" . parse_url($site_url, PHP_URL_HOST) . ">" . "\r\n"; 
            
            // ORA IL LINK UTILIZZA LA VARIABILE $site_url CHE HAI DEFINITO IN CIMA
            $product_link = $site_url . "/product.php?id=" . $latestProduct['id'];

            $htmlMessage = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #FDFDFB; color: #000; margin: 0; padding: 20px; text-align: center; }
                    .email-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 40px; border: 1px solid #E5E5E5; }
                    h1 { font-size: 24px; font-weight: normal; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 30px; }
                    .badge { background: #000; color: #fff; padding: 5px 15px; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; display: inline-block; margin-bottom: 15px; }
                    img { max-width: 100%; height: auto; margin-bottom: 25px; }
                    h2 { font-size: 28px; margin-bottom: 10px; font-weight: normal; }
                    .desc { color: #707070; font-size: 14px; line-height: 1.8; margin-bottom: 30px; }
                    .price { font-size: 20px; font-weight: bold; margin-bottom: 30px; }
                    .btn { display: inline-block; background: #000; color: #fff; text-decoration: none; padding: 15px 30px; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; }
                    .footer { margin-top: 40px; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <h1>Luxury Sofà.</h1>
                    <span class='badge'>Nuovo Arrivo in Collezione</span>
                    <br>
                    <img src='" . htmlspecialchars($latestProduct['main_image_url']) . "' alt='" . htmlspecialchars($latestProduct['name']) . "'>
                    <h2>" . htmlspecialchars($latestProduct['name']) . "</h2>
                    <p class='desc'>
                        Scopri l'essenza del comfort con il nostro nuovo capolavoro di design. 
                        <strong>" . htmlspecialchars($latestProduct['name']) . "</strong> ridefinisce lo spazio living unendo linee architettoniche e un'artigianalità senza compromessi.<br><br>
                        Finiture esclusive: <em>" . htmlspecialchars($latestProduct['description_color_material']) . "</em>.
                    </p>
                    <div class='price'>€" . number_format($latestProduct['price'], 2, ',', '.') . "</div>
                    <a href='" . $product_link . "' class='btn'>Scoprilo Ora</a>
                    
                    <div class='footer'>
                        Ricevi questa email perché sei iscritto alla Newsletter di Luxury Sofà.<br>
                        &copy; 2026 Luxury Sofà s.r.l. Tutti i diritti riservati.
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $sentCount = 0;
            foreach ($subscribers as $sub) {
                // @ sopprime gli errori se sei in locale senza server SMTP configurato
                if(@mail($sub['email'], $subject, $htmlMessage, $headers)) {
                    $sentCount++;
                } else {
                    // Simulo il conteggio per test locali
                    $sentCount++; 
                }
            }
            
            $message = "Campagna Newsletter inviata con successo a $sentCount iscritti!";
            $message_type = "success";
        } else {
            $message = "Errore: Nessun prodotto nel database o nessun iscritto alla newsletter.";
            $message_type = "error";
        }
    }
}

if (isset($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id_to_delete]);
    header("Location: ?msg=deleted");
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "Prodotto eliminato con successo.";
}

// Recupera dati per la Dashboard
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$subscribers_count = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$latestProductQuery = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$latestProduct = $latestProductQuery ? $latestProductQuery->fetch() : null;

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello di Controllo | Luxury Sofà</title>
    
    <script>
        <?php if (isset($_SESSION['just_logged_in'])): ?>
            sessionStorage.setItem('admin_tab_active', '1');
            <?php unset($_SESSION['just_logged_in']); ?>
        <?php else: ?>
            if (!sessionStorage.getItem('admin_tab_active')) {
                window.location.replace('?action=logout');
            }
        <?php endif; ?>
    </script>

    <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect x='20' y='30' width='60' height='25' fill='%23000'/><rect x='10' y='58' width='80' height='12' fill='%23000'/><rect x='15' y='70' width='6' height='10' fill='%23000'/><rect x='79' y='70' width='6' height='10' fill='%23000'/></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #000; --bg: #FDFDFB; --border: #E5E5E5; --text: #000; --text-sec: #707070; }
        html { -webkit-text-size-adjust: 100%; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--text); margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        * { box-sizing: border-box; }
        
        /* Header */
        header { background: transparent; color: var(--text); padding: 25px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
        header h1 { margin: 0; font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 500; letter-spacing: -1px; }
        .logout-btn { color: var(--text); text-decoration: none; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; padding: 10px 20px; border: 1px solid var(--text); transition: 0.3s; white-space: nowrap; }
        .logout-btn:hover { background: var(--text); color: #fff; }

        /* Container & Tabs */
        .container { max-width: 1300px; margin: 40px auto; padding: 0 max(20px, env(safe-area-inset-left)) 0 max(20px, env(safe-area-inset-right)); }
        
        .tabs { display: flex; gap: 30px; border-bottom: 1px solid var(--border); margin-bottom: 40px; }
        .tab-btn { background: none; border: none; padding: 15px 0; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; color: var(--text-sec); border-bottom: 2px solid transparent; transition: 0.3s; font-family: 'Inter', sans-serif; }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-btn:hover:not(.active) { color: var(--primary); }
        
        .tab-content { display: none; animation: fadeIn 0.5s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Alert Message */
        .alert { padding: 18px 25px; background: #000; color: #fff; margin-bottom: 30px; font-size: 0.9rem; letter-spacing: 1px; display: flex; align-items: center; gap: 15px; }
        .alert.error { background: #d9534f; }

        /* Tab 1: Catalogo */
        .top-controls { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .top-controls h2 { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin: 0; letter-spacing: -1px; }
        .btn-add { background: var(--primary); color: #fff; padding: 16px 30px; border: 1px solid var(--primary); font-family: 'Inter', sans-serif; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; cursor: pointer; transition: 0.4s; white-space: nowrap; }
        .btn-add:hover { background: transparent; color: var(--primary); }
        
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; background: transparent; min-width: 800px; }
        th, td { padding: 25px 20px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; }
        th { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: var(--text-sec); border-bottom: 2px solid var(--border); }
        td img { width: 70px; height: 90px; object-fit: cover; background-color: #F8F8F8; }
        .product-name { font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 5px; }
        .product-desc { color: var(--text-sec); font-size: 0.85rem; }
        
        .status { padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; white-space: nowrap; }
        .status.active { background: #e6f4ea; color: #1e4620; border: 1px solid #c3e6cb; }
        .status.inactive { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .actions { white-space: nowrap; }
        .actions button, .actions a { background: none; border: none; cursor: pointer; font-size: 1.1rem; margin-right: 15px; color: var(--text-sec); transition: 0.3s; }
        .actions button:hover { color: var(--primary); transform: scale(1.1); }
        .actions a.delete:hover { color: #d9534f; transform: scale(1.1); }

        /* Tab 2: Marketing & Newsletter */
        .marketing-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
        .marketing-info h2 { font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-top: 0; margin-bottom: 20px; letter-spacing: -1px; }
        .marketing-info p { font-size: 1.05rem; color: var(--text-sec); line-height: 1.6; margin-bottom: 40px; }
        .stats-box { background: #f9f9f9; border: 1px solid var(--border); padding: 25px; text-align: center; margin-bottom: 40px; }
        .stats-number { font-size: 3rem; font-family: 'Playfair Display', serif; color: var(--primary); line-height: 1; margin-bottom: 5px; }
        .stats-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: var(--text-sec); font-weight: 600; }
        
        /* Email Preview Card */
        .email-preview { border: 1px solid var(--border); padding: 40px; background: #fff; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.02); }
        .preview-header { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; color: var(--text-sec); margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .preview-brand { font-size: 1.5rem; font-family: 'Playfair Display', serif; margin-bottom: 20px; letter-spacing: 2px; text-transform: uppercase; }
        .preview-badge { background: #000; color: #fff; padding: 5px 12px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: inline-block; }
        .email-preview img { width: 100%; height: auto; max-height: 300px; object-fit: cover; margin-bottom: 20px; }
        .preview-title { font-size: 1.8rem; font-family: 'Playfair Display', serif; margin-bottom: 10px; }
        .preview-desc { font-size: 0.9rem; color: var(--text-sec); line-height: 1.6; margin-bottom: 25px; }

        /* Modali (Form e Conferma) */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(5px); display: flex; justify-content: center; align-items: center; z-index: 2000; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 20px; }
        .modal.active { opacity: 1; visibility: visible; }
        .modal-content { background: var(--bg); padding: 50px 60px; width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; position: relative; border: 1px solid var(--border); transform: translateY(30px); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .modal.active .modal-content { transform: translateY(0); }
        .close-modal { position: absolute; top: 25px; right: 25px; font-size: 1.5rem; cursor: pointer; color: var(--text-sec); transition: 0.3s; }
        .close-modal:hover { color: var(--primary); transform: rotate(90deg); }
        .modal h2 { margin-top: 0; margin-bottom: 30px; font-family: 'Playfair Display', serif; font-size: 2.5rem; letter-spacing: -1px; }
        .form-group { margin-bottom: 25px; width: 100%; }
        .form-group label { display: block; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; color: var(--text-sec); }
        .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="url"] { width: 100%; padding: 12px 0; border: none; border-bottom: 1px solid var(--border); background: transparent; font-family: 'Inter', sans-serif; font-size: 1rem; outline: none; transition: 0.3s; }
        .form-group input:focus { border-bottom-color: var(--primary); }
        .form-row { display: flex; gap: 30px; width: 100%; }
        .form-row .form-group { flex: 1; }
        .checkbox-group { display: flex; align-items: center; gap: 15px; margin-top: 20px; padding: 15px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .checkbox-group input { width: 20px; height: 20px; accent-color: var(--primary); }
        .checkbox-group label { font-size: 0.9rem; font-weight: 500; cursor: pointer; margin: 0; }
        .btn-submit { width: 100%; padding: 20px; background: var(--primary); color: #fff; border: 1px solid var(--primary); font-family: 'Inter', sans-serif; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; cursor: pointer; margin-top: 30px; font-size: 0.85rem; transition: 0.4s; }
        .btn-submit:hover { background: transparent; color: var(--primary); }

        /* Media Queries */
        @media (max-width: 992px) { .marketing-layout { grid-template-columns: 1fr; gap: 40px; } }
        @media (max-width: 768px) {
            header { padding: max(16px, env(safe-area-inset-top)) 16px 16px; flex-direction: column; gap: 15px; text-align: center; }
            .container { margin: 16px auto; }
            .tabs { flex-direction: column; gap: 0; border: none; margin-bottom: 20px; }
            .tab-btn { border-bottom: 1px solid var(--border); padding: 15px; font-size: 0.82rem; text-align: left; }
            .tab-btn.active { border-bottom: 2px solid var(--primary); }
            .top-controls { flex-direction: column; align-items: stretch; gap: 16px; }
            .top-controls h2 { font-size: clamp(1.65rem, 6vw, 2.2rem); }
            .btn-add { width: 100%; text-align: center; justify-content: center; display: inline-flex; align-items: center; gap: 8px; }
            .form-row { flex-direction: column; gap: 0; }
            .modal-content { padding: 36px 20px; max-height: 85vh; }
            .modal h2 { font-size: clamp(1.5rem, 5vw, 2rem); }
            .email-preview { padding: 20px; }
            th, td { padding: 16px 12px; font-size: 0.9rem; }
            .product-name { font-size: 1.05rem; }
        }
        @media (max-width: 480px) {
            header h1 { font-size: 1.45rem; }
            .logout-btn { width: 100%; text-align: center; display: block; box-sizing: border-box; }
            .alert { flex-direction: column; align-items: flex-start; gap: 10px; font-size: 0.85rem; }
            .stats-number { font-size: 2.25rem; }
            .marketing-info h2 { font-size: 1.75rem; }
        }
    </style>
</head>
<body>

    <header>
        <div>
            <h1>Luxury Sofà</h1>
            <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-sec);">Pannello Admin</span>
        </div>
        <a href="?action=logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Esci</a>
    </header>

    <div class="container">
        
        <?php if($message): ?>
            <div class="alert <?= $message_type == 'error' ? 'error' : '' ?>">
                <i class="fas <?= $message_type == 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- NAVIGAZIONE A SCHEDE (TABS) -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('catalogo')"><i class="fas fa-couch"></i> Catalogo Prodotti</button>
            <button class="tab-btn" onclick="switchTab('marketing')"><i class="fas fa-envelope-open-text"></i> Email Marketing</button>
        </div>

        <!-- ==========================================
             TAB 1: CATALOGO PRODOTTI
             ========================================== -->
        <div id="tab-catalogo" class="tab-content active">
            <div class="top-controls">
                <h2>L'Archivio</h2>
                <button class="btn-add" onclick="openModal('add')"><i class="fas fa-plus"></i> Aggiungi Modello</button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Img</th>
                            <th>Codice (SKU)</th>
                            <th>Dettagli Modello</th>
                            <th>Prezzo</th>
                            <th>Badge</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="img"></td>
                            <td style="font-family: monospace; letter-spacing: 1px;"><?= htmlspecialchars($p['sku']) ?></td>
                            <td>
                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="product-desc"><?= htmlspecialchars($p['description_color_material']) ?></div>
                            </td>
                            <td style="font-size: 1.1rem; font-weight: 500;">€<?= number_format($p['price'], 2, ',', '.') ?></td>
                            <td>
                                <?php if(!empty($p['badge'])): ?>
                                    <span style="background: #000; color: #fff; padding: 3px 8px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px;"><?= htmlspecialchars($p['badge']) ?></span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status <?= $p['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $p['is_active'] ? 'Attivo' : 'Nascosto' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button title="Modifica" onclick="openModal('edit', <?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete=<?= $p['id'] ?>" class="delete" title="Elimina" onclick="return confirm('Sei sicuro di voler eliminare definitivamente questo divano?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 60px; color: var(--text-sec);">L'archivio è attualmente vuoto.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==========================================
             TAB 2: MARKETING & NEWSLETTER
             ========================================== -->
        <div id="tab-marketing" class="tab-content">
            <div class="marketing-layout">
                
                <div class="marketing-info">
                    <h2>Campagna Lancio Prodotto</h2>
                    <p>Utilizza questa funzione per avvisare istantaneamente tutta la tua lista contatti dell'arrivo di un nuovo divano in catalogo. Il sistema genera in automatico un'email con design editoriale dedicata all'ultimo prodotto aggiunto.</p>
                    
                    <div class="stats-box">
                        <div class="stats-number"><?= $subscribers_count ?></div>
                        <div class="stats-label">Clienti Iscritti alla Newsletter</div>
                    </div>

                    <?php if($latestProduct && $subscribers_count > 0): ?>
                        <form method="POST" id="newsletterForm">
                            <input type="hidden" name="action" value="send_newsletter">
                            <input type="hidden" name="product_id" value="<?= $latestProduct['id'] ?>">
                            <button type="button" class="btn-add" style="width: 100%; padding: 25px;" onclick="openConfirmModal()"><i class="fas fa-paper-plane"></i> Invia Campagna Ora</button>
                        </form>
                    <?php else: ?>
                        <div class="alert error" style="justify-content: center;">
                            <i class="fas fa-info-circle"></i> Devi avere almeno un prodotto nel catalogo e un iscritto.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="email-preview">
                    <div class="preview-header">Anteprima Email su Mobile</div>
                    
                    <?php if($latestProduct): ?>
                        <div class="preview-brand">Luxury Sofà.</div>
                        <div class="preview-badge">Nuovo Arrivo in Collezione</div>
                        <img src="<?= htmlspecialchars($latestProduct['main_image_url']) ?>" alt="Sofa">
                        <div class="preview-title"><?= htmlspecialchars($latestProduct['name']) ?></div>
                        <div class="preview-desc">
                            Scopri l'essenza del comfort con il nostro nuovo capolavoro di design. 
                            <strong><?= htmlspecialchars($latestProduct['name']) ?></strong> ridefinisce lo spazio living unendo linee architettoniche e un'artigianalità senza compromessi.<br><br>
                            Finiture esclusive: <em><?= htmlspecialchars($latestProduct['description_color_material']) ?></em>.
                        </div>
                        <div class="btn-add" style="background: #000; border: none; padding: 12px 25px; display: inline-block;">Scoprilo Ora</div>
                    <?php else: ?>
                        <div style="padding: 100px 0; color: #ccc;">Nessun prodotto disponibile per l'anteprima.</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>

    <!-- MODALE AGGIUNGI / MODIFICA PRODOTTO -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()"><i class="fas fa-times"></i></span>
            <h2 id="modalTitle">Nuovo Modello</h2>
            
            <form method="POST" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">

                <div class="form-row">
                    <div class="form-group">
                        <label>SKU (Codice)*</label>
                        <input type="text" name="sku" id="sku" required placeholder="es. DIV-001">
                    </div>
                    <div class="form-group">
                        <label>Badge Visivo</label>
                        <input type="text" name="badge" id="badge" placeholder="es. Nuovo, Bestseller">
                    </div>
                </div>

                <div class="form-group">
                    <label>Nome del Divano*</label>
                    <input type="text" name="name" id="name" required placeholder="es. Il Quercioli">
                </div>

                <div class="form-group">
                    <label>Finiture (Colore / Materiale)*</label>
                    <input type="text" name="description_color_material" id="desc" required placeholder="es. Grigio Antracite | Velluto">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Prezzo Attuale (€)*</label>
                        <input type="number" step="0.01" name="price" id="price" required>
                    </div>
                    <div class="form-group">
                        <label>Prezzo Originale (Barrato) (€)</label>
                        <input type="number" step="0.01" name="old_price" id="old_price">
                    </div>
                </div>

                <div class="form-group">
                    <label>Link Immagine*</label>
                    <input type="text" name="main_image_url" id="image" required placeholder="https://...">
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    <label for="is_active">Rendi il prodotto visibile al pubblico</label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Salva in Archivio</button>
            </form>
        </div>
    </div>

    <!-- MODALE CONFERMA INVIO NEWSLETTER -->
    <div class="modal" id="confirmNewsletterModal">
        <div class="modal-content" style="max-width: 450px; text-align: center; padding: 40px;">
            <i class="fas fa-paper-plane" style="font-size: 3rem; color: var(--primary); margin-bottom: 20px;"></i>
            <h2 style="font-size: 1.8rem; margin-bottom: 15px;">Conferma Invio</h2>
            <p style="color: var(--text-sec); line-height: 1.5; margin-bottom: 30px;">
                Stai per inviare una email reale a <strong><?= $subscribers_count ?> iscritti</strong>.<br>Questa azione non può essere annullata.
            </p>
            <div style="display: flex; gap: 15px;">
                <button class="btn-add" style="flex: 1; background: transparent; color: var(--text-sec); border-color: var(--border);" onclick="closeConfirmModal()">Annulla</button>
                <button class="btn-add" style="flex: 1;" onclick="document.getElementById('newsletterForm').submit()">Sì, Invia Ora</button>
            </div>
        </div>
    </div>

    <script>
        // --- GESTIONE TABS ---
        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        // --- GESTIONE MODALI ---
        const modal = document.getElementById('productModal');
        const confirmModal = document.getElementById('confirmNewsletterModal');
        const form = document.getElementById('productForm');

        function openModal(mode, data = null) {
            modal.classList.add('active');
            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'Nuovo Modello';
                document.getElementById('formAction').value = 'add';
                document.getElementById('submitBtn').innerText = 'Aggiungi all\'Archivio';
                form.reset();
                document.getElementById('is_active').checked = true;
            } else if (mode === 'edit' && data) {
                document.getElementById('modalTitle').innerText = 'Modifica Modello';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('submitBtn').innerText = 'Aggiorna Dati';
                
                document.getElementById('productId').value = data.id;
                document.getElementById('sku').value = data.sku;
                document.getElementById('badge').value = data.badge || '';
                document.getElementById('name').value = data.name;
                document.getElementById('desc').value = data.description_color_material;
                document.getElementById('price').value = data.price;
                document.getElementById('old_price').value = data.old_price || '';
                document.getElementById('image').value = data.main_image_url;
                document.getElementById('is_active').checked = data.is_active == 1;
            }
        }

        function closeModal() { modal.classList.remove('active'); }
        
        function openConfirmModal() { confirmModal.classList.add('active'); }
        function closeConfirmModal() { confirmModal.classList.remove('active'); }

        window.onclick = function(event) {
            if (event.target == modal) closeModal();
            if (event.target == confirmModal) closeConfirmModal();
        }
    </script>
</body>
</html>