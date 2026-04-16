<?php
date_default_timezone_set('Europe/Paris');

define('DB_HOST', 'localhost');
define('DB_NAME', 'proxmox_monitoring');
define('DB_USER', 'admin');
define('DB_PASS', 'Admin@2026!');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $pdo->exec("SET time_zone = '+02:00'");
    }
    return $pdo;
}
