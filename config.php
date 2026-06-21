<?php
// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'perpus');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL — adjust if running on different port
define('BASE_URL', 'http://localhost:8080');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid #f00;border-radius:8px;margin:20px">'
                . '<strong>Database Connection Error:</strong><br>' . htmlspecialchars($e->getMessage())
                . '</div>');
        }
    }
    return $pdo;
}
