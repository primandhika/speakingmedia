<?php

session_start();
include '../includes/functions.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: ../index.php?msg=' . urlencode('Token verifikasi tidak valid') . '&type=error');
    exit;
}

try {
    $db = getDB();
    
    // Find user with verification token
    $stmt = $db->prepare("SELECT * FROM users WHERE verification_token = ? AND is_active = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: ../index.php?msg=' . urlencode('Token verifikasi tidak valid atau sudah digunakan') . '&type=error');
        exit;
    }
    
    // Check if token is not too old (24 hours)
    $createdAt = new DateTime($user['created_at']);
    $now = new DateTime();
    $diff = $now->diff($createdAt);
    
    if ($diff->days > 1) {
        header('Location: ../index.php?msg=' . urlencode('Token verifikasi sudah kadaluarsa') . '&type=error');
        exit;
    }
    
    // Activate user account
    $stmt = $db->prepare("
        UPDATE users 
        SET is_active = 1, status = 'active', verification_token = NULL, updated_at = NOW() 
        WHERE user_id = ?
    ");
    $stmt->execute([$user['user_id']]);
    
    // Auto login the user
    $userData = [
        'user_id' => $user['user_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    
    setUserLogin($userData);
    
    header('Location: ../index.php?msg=' . urlencode('Email berhasil diverifikasi! Selamat datang, ' . $user['name']) . '&type=success');
    exit;
    
} catch (Exception $e) {
    error_log("Verification error: " . $e->getMessage());
    header('Location: ../index.php?msg=' . urlencode('Terjadi kesalahan sistem') . '&type=error');
    exit;
}
?>