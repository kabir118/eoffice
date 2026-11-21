<?php

/**
 * USTB e-Office Master Central Licensing & Signal Control Panel
 * 
 * Upload this single standalone file to your master web server: https://ustb.edu.pk/ac.php
 */

session_start();

// Configuration
define('ADMIN_PASSWORD', 'Haider118@2026'); // Change your master admin password here
define('LICENSE_DB_FILE', __DIR__ . '/licenses.json');
define('MASTER_SECRET_SALT', 'KABIR_MASTER_REMOTE_SALT_2026_99A721_EOFFICE');

// Ensure database file exists
if (!file_exists(LICENSE_DB_FILE)) {
    $initialData = [
        'domains' => [
            'localhost' => [
                'client_name' => 'Local Development',
                'key' => generateKey('localhost'),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            'eoffice.ustb.edu.pk' => [
                'client_name' => 'UST Bannu Production',
                'key' => generateKey('eoffice.ustb.edu.pk'),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]
    ];
    file_put_contents(LICENSE_DB_FILE, json_encode($initialData, JSON_PRETTY_PRINT));
}

function getLicenses() {
    $content = file_get_contents(LICENSE_DB_FILE);
    return json_decode($content, true) ?? ['domains' => []];
}

function saveLicenses($data) {
    file_put_contents(LICENSE_DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function generateKey($domain) {
    $cd = strtolower(trim($domain));
    if (str_contains($cd, ':')) { $cd = explode(':', $cd)[0]; }
    $rh = hash_hmac('sha256', 'EOFFICE_REMOTE_LIC:' . $cd, MASTER_SECRET_SALT);
    return "LIC-" . strtoupper(substr($rh, 0, 5) . "-" . substr($rh, 5, 5) . "-" . substr($rh, 10, 5) . "-" . substr($rh, 15, 5) . "-" . substr($rh, 20, 5));
}

// ==========================================
// PART 1: AUTOMATED SIGNAL VERIFICATION API
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'verify') {
    header('Content-Type: application/json');
    $domain = strtolower(trim($_REQUEST['domain'] ?? ''));
    $key = trim($_REQUEST['key'] ?? '');

    if (str_contains($domain, ':')) { $domain = explode(':', $domain)[0]; }

    $db = getLicenses();

    if (isset($db['domains'][$domain])) {
        $record = $db['domains'][$domain];
        if ($record['status'] === 'active' && hash_equals($record['key'], $key)) {
            $token = hash_hmac('sha256', $domain . date('Y-m-d') . $key, MASTER_SECRET_SALT);
            echo json_encode([
                'status' => 'APPROVED',
                'message' => 'License valid and active',
                'token' => $token,
                'domain' => $domain
            ]);
            exit;
        }
    }

    echo json_encode([
        'status' => 'REJECTED',
        'message' => 'Unauthorized or blocked domain installation',
        'domain' => $domain
    ]);
    exit;
}

// ==========================================
// PART 2: MASTER ADMIN CONTROL PANEL
// ==========================================

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_auth']);
    header('Location: ac.php');
    exit;
}

// Handle Login
$loginError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ADMIN_PASSWORD) {
        $_SESSION['admin_auth'] = true;
    } else {
        $loginError = 'Invalid Master Password';
    }
}

// Protect Admin Panel
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Master Licensing Admin Login</title>
        <style>
            body { background: #0f172a; color: #f8fafc; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 35px; width: 100%; max-width: 380px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
            h2 { font-size: 1.25rem; margin-bottom: 20px; color: #ffffff; text-align: center; }
            input[type="password"] { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #fff; margin-bottom: 15px; box-sizing: border-box; outline: none; }
            button { width: 100%; padding: 12px; background: #256f88; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
            button:hover { background: #1f5d72; }
            .err { color: #fca5a5; font-size: 0.82rem; margin-bottom: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>🔐 Licensing Admin Panel</h2>
            <?php if ($loginError): ?><div class="err"><?php echo $loginError; ?></div><?php endif; ?>
            <form method="POST">
                <input type="password" name="login_pass" placeholder="Enter Master Password" required autofocus>
                <button type="submit">Unlock Control Panel</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle Admin Actions (Add, Block, Unblock, Delete)
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $db = getLicenses();
    $action = $_POST['action_type'];
    $domain = strtolower(trim($_POST['target_domain'] ?? ''));

    if ($action === 'add') {
        $clientName = trim($_POST['client_name'] ?? 'Client');
        if (!empty($domain)) {
            $key = generateKey($domain);
            $db['domains'][$domain] = [
                'client_name' => $clientName,
                'key' => $key,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            saveLicenses($db);
            $msg = "Approved license generated for {$domain}!";
        }
    } elseif ($action === 'toggle_status') {
        if (isset($db['domains'][$domain])) {
            $currentStatus = $db['domains'][$domain]['status'];
            $db['domains'][$domain]['status'] = ($currentStatus === 'active') ? 'blocked' : 'active';
            saveLicenses($db);
            $msg = "Status for {$domain} updated to " . strtoupper($db['domains'][$domain]['status']);
        }
    } elseif ($action === 'delete') {
        if (isset($db['domains'][$domain])) {
            unset($db['domains'][$domain]);
            saveLicenses($db);
            $msg = "Removed domain {$domain} from database.";
        }
    }
}

$db = getLicenses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Central Licensing Control Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --primary: #256f88; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:system-ui, sans-serif; }
        body { background: var(--bg); color: #f8fafc; padding: 30px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .header h1 { font-size: 1.4rem; color: #fff; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .card h3 { font-size: 1.05rem; margin-bottom: 15px; color: #38bdf8; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; }
        input[type="text"] { padding: 10px 14px; background: #0f172a; border: 1px solid var(--border); border-radius: 8px; color: #fff; outline: none; font-size: 0.9rem; }
        .btn { padding: 10px 18px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.88rem; }
        .btn:hover { background: #1f5d72; }
        .btn-danger { background: #e74c3c; } .btn-danger:hover { background: #c0392b; }
        .btn-success { background: #2ecc71; color: #111; } .btn-success:hover { background: #27ae60; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--border); font-size: 0.88rem; }
        th { background: #0f172a; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
        .badge { padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-active { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.4); }
        .badge-blocked { background: rgba(231, 76, 60, 0.2); color: #fca5a5; border: 1px solid rgba(231, 76, 60, 0.4); }
        .alert { background: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.88rem; color: #2ecc71; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="bi bi-shield-lock-fill" style="color: #38bdf8;"></i> e-Office Central Licensing Panel</h1>
            <a href="ac.php?action=logout" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>

        <?php if ($msg): ?>
            <div class="alert"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <!-- Add New Buyer Form -->
        <div class="card">
            <h3><i class="bi bi-plus-circle-fill"></i> Approve New Client / Domain</h3>
            <form method="POST">
                <input type="hidden" name="action_type" value="add">
                <div class="form-grid">
                    <div>
                        <label style="display:block; font-size:0.78rem; color:#94a3b8; margin-bottom:5px;">CLIENT NAME</label>
                        <input type="text" name="client_name" placeholder="e.g. Govt College Bannu" required style="width:100%;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.78rem; color:#94a3b8; margin-bottom:5px;">DOMAIN / HOSTNAME</label>
                        <input type="text" name="target_domain" placeholder="e.g. clientdomain.com" required style="width:100%;">
                    </div>
                    <button type="submit" class="btn"><i class="bi bi-key-fill"></i> Generate & Approve Key</button>
                </div>
            </form>
        </div>

        <!-- Authorized Domains List -->
        <div class="card">
            <h3><i class="bi bi-list-stars"></i> Authorized Client Domains</h3>
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Domain</th>
                        <th>License Key</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($db['domains'] as $d => $info): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($info['client_name'] ?? 'N/A'); ?></strong></td>
                            <td><code style="color:#38bdf8;"><?php echo htmlspecialchars($d); ?></code></td>
                            <td><code style="font-size:0.8rem; background:#0f172a; padding:4px 8px; border-radius:4px;"><?php echo htmlspecialchars($info['key']); ?></code></td>
                            <td>
                                <span class="badge <?php echo $info['status'] === 'active' ? 'badge-active' : 'badge-blocked'; ?>">
                                    <?php echo strtoupper($info['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action_type" value="toggle_status">
                                    <input type="hidden" name="target_domain" value="<?php echo htmlspecialchars($d); ?>">
                                    <button type="submit" class="btn <?php echo $info['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>" style="padding:4px 10px; font-size:0.78rem;">
                                        <?php echo $info['status'] === 'active' ? '<i class="bi bi-slash-circle"></i> Block' : '<i class="bi bi-check-lg"></i> Activate'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete <?php echo $d; ?>?');">
                                    <input type="hidden" name="action_type" value="delete">
                                    <input type="hidden" name="target_domain" value="<?php echo htmlspecialchars($d); ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:4px 8px; font-size:0.78rem;" title="Delete Record"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
