<?php
/**
 * UIU Career Portal - Database Configuration & PDO Connection
 * Target Environment: MySQL 5.7+ / MariaDB (XAMPP / Apache / Localhost)
 */

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'uiu_career_portal');
if (!defined('DB_PORT')) define('DB_PORT', '3306');

function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        ensure_database_schema($pdo);
        return $pdo;
    } catch (PDOException $e) {
        // Attempt to create database if connecting to MySQL server directly
        try {
            $fallbackDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $tempPdo = new PDO($fallbackDsn, DB_USER, DB_PASS, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            ensure_database_schema($pdo);
            return $pdo;
        } catch (PDOException $fallbackError) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Automatically verifies core tables exist; executes database schema if missing.
 */
function ensure_database_schema($pdo) {
    if (!$pdo) return;
    try {
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if (!$stmt->fetch()) {
            $sqlFile = __DIR__ . '/../database/database.sql';
            if (!file_exists($sqlFile)) {
                $sqlFile = __DIR__ . '/../database.sql';
            }
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $pdo->exec($sql);
            }
        }
    } catch (Exception $e) {
        error_log("Schema auto-verification note: " . $e->getMessage());
    }
}
