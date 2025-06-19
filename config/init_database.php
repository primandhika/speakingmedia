<?php
// config/init_database.php - Database initialization script

function testDatabaseConnection() {
    try {
        $config = include 'database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function createDatabaseIfNotExists() {
    try {
        $config = include 'database.php';
        $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Test connection and provide feedback
if (!testDatabaseConnection()) {
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h4>⚠️ Database Connection Error</h4>";
    echo "<p><strong>Database 'bicaranta_db' tidak ditemukan atau tidak dapat diakses.</strong></p>";
    echo "<p>Silakan:</p>";
    echo "<ol>";
    echo "<li>Import file SQL yang tersedia: <code>bicaranta_db.sql</code></li>";
    echo "<li>Atau buat database secara manual di phpMyAdmin</li>";
    echo "<li>Pastikan konfigurasi database di <code>config/database.php</code> sudah benar</li>";
    echo "</ol>";
    echo "<p><strong>Langkah-langkah:</strong></p>";
    echo "<ul>";
    echo "<li>Buka phpMyAdmin (http://localhost/phpmyadmin)</li>";
    echo "<li>Klik 'Import' di menu atas</li>";
    echo "<li>Pilih file <code>bicaranta_db.sql</code></li>";
    echo "<li>Klik 'Go' untuk mengimport</li>";
    echo "</ul>";
    echo "</div>";
    exit;
}
?>