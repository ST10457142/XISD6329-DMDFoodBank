<?php
// includes/config.php – edit these values to match your server
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dmd_foodbank');
define('SITE_NAME', 'DMD Food Bank');
define('SITE_URL', 'http://localhost/dmd_php');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee;border:1px solid #c00;border-radius:8px;max-width:600px;margin:2rem auto">
                <h2 style="color:#c00">Database Connection Error</h2>
                <p>Could not connect to the database. Please check your settings in <code>includes/config.php</code>.</p>
                <p><strong>Error:</strong> '.htmlspecialchars($e->getMessage()).'</p>
                <p>Make sure you have run <code>database.sql</code> to set up the database.</p>
            </div>');
        }
    }
    return $pdo;
}

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool {
    session_start_safe();
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(): void {
    session_start_safe();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function flash(string $key, string $msg = null): ?string {
    session_start_safe();
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

function format_zar(float $amount): string {
    return 'R ' . number_format($amount, 2);
}
