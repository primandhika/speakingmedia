<?php
/**
 * auth/register.php - Handle user registration
 */

session_start();

// Include functions
include '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $nim = trim($_POST['nim'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    if (empty($nim) || empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }
    
    // Validate NIM format (8-12 digits)
    if (!preg_match('/^[0-9]{8,12}$/', $nim)) {
        echo json_encode(['success' => false, 'message' => 'NIM harus berupa angka 8-12 digit']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit;
    }
    
    // Validate name length
    if (strlen($name) < 3) {
        echo json_encode(['success' => false, 'message' => 'Nama harus minimal 3 karakter']);
        exit;
    }
    
    $db = getDB();
    
    // Check if NIM already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->execute([$nim]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'NIM sudah terdaftar']);
        exit;
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }
    
    // Generate verification token
    $verificationToken = bin2hex(random_bytes(32));
    
    // Insert pending user (inactive until email verified)
    $stmt = $db->prepare("
        INSERT INTO users (user_id, name, email, password, role, status, is_active, verification_token, created_at) 
        VALUES (?, ?, ?, ?, 'student', 'inactive', 0, ?, NOW())
    ");
    
    // For now, set a default password (will be set during email verification)
    $defaultPassword = password_hash($nim . '_temp', PASSWORD_DEFAULT);
    
    $stmt->execute([$nim, $name, $email, $defaultPassword, $verificationToken]);
    
    // Send verification email (simulate for now)
    $verificationLink = "http://{$_SERVER['HTTP_HOST']}/speakingmedia/auth/verify.php?token=" . $verificationToken;
    
    // In production, you would send actual email here
    // For demo, we'll just log the verification link
    error_log("Verification link for {$email}: {$verificationLink}");
    
    // Store verification info in session for demo purposes
    $_SESSION['pending_verification'] = [
        'nim' => $nim,
        'name' => $name,
        'email' => $email,
        'token' => $verificationToken,
        'link' => $verificationLink
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pendaftaran berhasil! Email konfirmasi telah dikirim ke ' . $email . '. Silakan cek inbox Anda.',
        'verification_link' => $verificationLink // For demo purposes only
    ]);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.']);
}
?>