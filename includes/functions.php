<?php
// functions.php - Database-enabled functions

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $configPath = __DIR__ . '/../config/database.php';
        if (file_exists($configPath)) {
            $config = include $configPath;
        } else {
            // Fallback config
            $config = [
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'bicaranta_db',
                'charset' => 'utf8mb4'
            ];
        }
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getPDO() {
        return $this->pdo;
    }
}

// Get database instance
function getDB() {
    return Database::getInstance()->getPDO();
}

// User functions
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function getUserRole() {
    $user = getCurrentUser();
    return $user ? ($user['role'] ?? 'student') : null;
}

function isUserLoggedIn() {
    return getCurrentUser() !== null;
}

function validateUser($userId, $password = '') {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND is_active = 1 AND status = 'active'");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && !empty($password)) {
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return null;
}

function setUserLogin($user) {
    $_SESSION['user'] = $user;
    
    // Update last login
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Log activity
    logActivity('login', ['timestamp' => date('Y-m-d H:i:s')]);
}

function logoutUser() {
    logActivity('logout', ['timestamp' => date('Y-m-d H:i:s')]);
    unset($_SESSION['user']);
}

// Material functions
function getMaterialsFromDB($userId = null) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM materials WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $materials = $stmt->fetchAll();
    
    $result = [];
    foreach ($materials as $material) {
        $progress = getUserProgress($userId, $material['material_key']);
        $result[$material['material_key']] = [
            'id' => $material['id'],
            'material_key' => $material['material_key'],
            'name' => $material['name'],
            'description' => $material['description'],
            'icon' => $material['icon'],
            'difficulty' => $material['difficulty'],
            'duration' => $material['duration'],
            'clicks' => $progress['clicks'] ?? 0,
            'progress_percentage' => $progress['progress_percentage'] ?? 0,
            'status' => $progress['status'] ?? 'not_started'
        ];
    }
    
    return $result;
}

function getUserProgress($userId, $materialKey) {
    if (!$userId) return ['clicks' => 0, 'progress_percentage' => 0, 'status' => 'not_started'];
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM user_progress WHERE user_id = ? AND material_key = ?");
    $stmt->execute([$userId, $materialKey]);
    return $stmt->fetch() ?: ['clicks' => 0, 'progress_percentage' => 0, 'status' => 'not_started'];
}

// REVISED: Updated to handle submaterial parameter
function updateMaterialProgress($userId, $materialKey, $subMaterial = null) {
    if (!$userId) return;
    
    $db = getDB();
    
    // Check if progress exists
    $stmt = $db->prepare("SELECT * FROM user_progress WHERE user_id = ? AND material_key = ?");
    $stmt->execute([$userId, $materialKey]);
    $progress = $stmt->fetch();
    
    if ($progress) {
        // Update existing progress
        $newClicks = $progress['clicks'] + 1;
        $newProgress = min(100, $newClicks * 5);
        $status = $newProgress >= 100 ? 'completed' : ($newProgress > 0 ? 'in_progress' : 'not_started');
        
        $stmt = $db->prepare("UPDATE user_progress SET clicks = ?, progress_percentage = ?, status = ?, last_accessed = NOW() WHERE user_id = ? AND material_key = ?");
        $stmt->execute([$newClicks, $newProgress, $status, $userId, $materialKey]);
    } else {
        // Create new progress
        $stmt = $db->prepare("INSERT INTO user_progress (user_id, material_key, clicks, progress_percentage, status) VALUES (?, ?, 1, 5, 'in_progress')");
        $stmt->execute([$userId, $materialKey]);
    }
    
    // Log activity with submaterial info if provided
    $activityData = ['material' => $materialKey, 'page' => 'index'];
    if ($subMaterial) {
        $activityData['sub_material'] = $subMaterial;
    }
    logActivity('material_click', $activityData);
}

function getStudyStatistics($userId = null) {
    if (!$userId) {
        // Return basic stats from materials table
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM materials WHERE is_active = 1");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        return [
            'total_clicks' => 0,
            'studied_materials' => 0,
            'total_materials' => $total,
            'completed_materials' => 0,
            'avg_progress' => 0
        ];
    }
    
    $db = getDB();
    
    // Get total materials
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM materials WHERE is_active = 1");
    $stmt->execute();
    $totalMaterials = $stmt->fetch()['total'];
    
    // Get user progress stats
    $stmt = $db->prepare("
        SELECT 
            SUM(clicks) as total_clicks,
            COUNT(*) as studied_materials,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_materials,
            AVG(progress_percentage) as avg_progress
        FROM user_progress 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();
    
    return [
        'total_clicks' => $stats['total_clicks'] ?: 0,
        'studied_materials' => $stats['studied_materials'] ?: 0,
        'total_materials' => $totalMaterials,
        'completed_materials' => $stats['completed_materials'] ?: 0,
        'avg_progress' => $stats['avg_progress'] ?: 0
    ];
}

function searchMaterials($query) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT material_key, name, description, icon, difficulty, duration 
        FROM materials 
        WHERE is_active = 1 
        AND (name LIKE ? OR description LIKE ? OR material_key LIKE ?)
        ORDER BY name
    ");
    $searchTerm = "%{$query}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

function logActivity($type, $data = []) {
    $user = getCurrentUser();
    if (!$user) return;
    
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO activity_log (user_id, activity_type, activity_data, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['user_id'],
        $type,
        json_encode($data),
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
}

// REVISED: Guest progress functions with submaterial support
function updateGuestProgress($progressId, $materialKey, $subMaterial = null) {
    if (!$progressId) return;
    
    if (!isset($_SESSION['guest_progress'])) {
        $_SESSION['guest_progress'] = [];
    }
    
    if (!isset($_SESSION['guest_progress'][$progressId])) {
        $_SESSION['guest_progress'][$progressId] = [];
    }
    
    if (!isset($_SESSION['guest_progress'][$progressId][$materialKey])) {
        $_SESSION['guest_progress'][$progressId][$materialKey] = 0;
    }
    
    $_SESSION['guest_progress'][$progressId][$materialKey]++;
    
    // Store submaterial access if provided
    if ($subMaterial) {
        if (!isset($_SESSION['guest_progress'][$progressId]['submaterials'])) {
            $_SESSION['guest_progress'][$progressId]['submaterials'] = [];
        }
        if (!isset($_SESSION['guest_progress'][$progressId]['submaterials'][$materialKey])) {
            $_SESSION['guest_progress'][$progressId]['submaterials'][$materialKey] = [];
        }
        $_SESSION['guest_progress'][$progressId]['submaterials'][$materialKey][$subMaterial] = true;
    }
}

function getGuestProgress($progressId, $materialKey) {
    if (!$progressId || !isset($_SESSION['guest_progress'][$progressId][$materialKey])) {
        return ['clicks' => 0, 'progress_percentage' => 0, 'status' => 'not_started'];
    }
    
    $clicks = $_SESSION['guest_progress'][$progressId][$materialKey];
    $progress = min(100, $clicks * 5);
    $status = $progress >= 100 ? 'completed' : ($progress > 0 ? 'in_progress' : 'not_started');
    
    return [
        'clicks' => $clicks,
        'progress_percentage' => $progress,
        'status' => $status
    ];
}

function getMaterialsForGuest($progressId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM materials WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $materials = $stmt->fetchAll();
    
    $result = [];
    foreach ($materials as $material) {
        $progress = getGuestProgress($progressId, $material['material_key']);
        $result[$material['material_key']] = [
            'id' => $material['id'],
            'material_key' => $material['material_key'],
            'name' => $material['name'],
            'description' => $material['description'],
            'icon' => $material['icon'],
            'difficulty' => $material['difficulty'],
            'duration' => $material['duration'],
            'clicks' => $progress['clicks'],
            'progress_percentage' => $progress['progress_percentage'],
            'status' => $progress['status']
        ];
    }
    
    return $result;
}

// Tambahkan function ini di functions.php jika belum ada

function getSubmaterialContent($materialKey, $submaterialKey) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM submaterial_content 
            WHERE material_key = ? AND submaterial_key = ? AND is_active = 1 
            ORDER BY display_order
        ");
        $stmt->execute([$materialKey, $submaterialKey]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error in getSubmaterialContent: " . $e->getMessage());
        return [];
    }
}

function getGuestStatistics($progressId) {
    if (!$progressId || !isset($_SESSION['guest_progress'][$progressId])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM materials WHERE is_active = 1");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        return [
            'total_clicks' => 0,
            'studied_materials' => 0,
            'total_materials' => $total,
            'completed_materials' => 0,
            'avg_progress' => 0
        ];
    }
    
    $guestData = $_SESSION['guest_progress'][$progressId];
    $totalClicks = array_sum($guestData);
    $studiedMaterials = count(array_filter($guestData, function($clicks) { return $clicks > 0; }));
    $completedMaterials = count(array_filter($guestData, function($clicks) { return $clicks >= 20; }));
    
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM materials WHERE is_active = 1");
    $stmt->execute();
    $totalMaterials = $stmt->fetch()['total'];
    
    $avgProgress = $studiedMaterials > 0 ? 
        array_sum(array_map(function($clicks) { return min(100, $clicks * 5); }, $guestData)) / $studiedMaterials : 0;
    
    return [
        'total_clicks' => $totalClicks,
        'studied_materials' => $studiedMaterials,
        'total_materials' => $totalMaterials,
        'completed_materials' => $completedMaterials,
        'avg_progress' => $avgProgress
    ];
}

// Legacy functions for compatibility
function getProgressText($clicks) {
    if ($clicks == 0) {
        return 'Belum dipelajari';
    } elseif ($clicks < 5) {
        return 'Baru mulai (' . ($clicks * 5) . '%)';
    } elseif ($clicks < 10) {
        return 'Sedang belajar (' . ($clicks * 5) . '%)';
    } elseif ($clicks < 20) {
        return 'Hampir selesai (' . min(100, $clicks * 5) . '%)';
    } else {
        return 'Dikuasai (100%)';
    }
}

function getProgressPercentage($clicks) {
    return min(100, $clicks * 5);
}

// Helper function for HTML escaping
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// REVISED: Material and submaterial functions with better error handling
function getMaterialInfo($materialKey) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM materials WHERE material_key = ? AND is_active = 1");
        $stmt->execute([$materialKey]);
        $result = $stmt->fetch();
        
        // If no result from database, return default
        if (!$result) {
            return [
                'material_key' => $materialKey,
                'name' => ucwords(str_replace('-', ' ', $materialKey)),
                'description' => 'Pelajari materi pembelajaran yang menarik dan bermanfaat',
                'icon' => 'bi-book',
                'difficulty' => 'Pemula',
                'duration' => '2-3 jam'
            ];
        }
        
        return $result;
    } catch (Exception $e) {
        // Fallback if database error
        return [
            'material_key' => $materialKey,
            'name' => ucwords(str_replace('-', ' ', $materialKey)),
            'description' => 'Pelajari materi pembelajaran yang menarik dan bermanfaat',
            'icon' => 'bi-book',
            'difficulty' => 'Pemula',
            'duration' => '2-3 jam'
        ];
    }
}

// REVISED: Added material_id parameter support
function getSubmaterials($materialKey, $userId = null, $progressId = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM submaterials 
            WHERE material_key = ? AND is_active = 1 
            ORDER BY sort_order, level
        ");
        $stmt->execute([$materialKey]);
        $submaterials = $stmt->fetchAll();
        
        // If no submaterials in database, return empty array
        if (empty($submaterials)) {
            return [];
        }
        
        // Add completion status for each submaterial
        foreach ($submaterials as &$submaterial) {
            $submaterial['is_completed'] = false;
            
            try {
                if ($userId) {
                    $stmt = $db->prepare("
                        SELECT is_completed FROM submaterial_progress 
                        WHERE user_id = ? AND material_key = ? AND submaterial_key = ?
                    ");
                    $stmt->execute([$userId, $materialKey, $submaterial['submaterial_key']]);
                    $progress = $stmt->fetch();
                    $submaterial['is_completed'] = $progress ? (bool)$progress['is_completed'] : false;
                } elseif ($progressId) {
                    $stmt = $db->prepare("
                        SELECT is_completed FROM submaterial_progress 
                        WHERE progress_id = ? AND material_key = ? AND submaterial_key = ?
                    ");
                    $stmt->execute([$progressId, $materialKey, $submaterial['submaterial_key']]);
                    $progress = $stmt->fetch();
                    $submaterial['is_completed'] = $progress ? (bool)$progress['is_completed'] : false;
                }
            } catch (Exception $e) {
                // If submaterial_progress table doesn't exist, default to false
                $submaterial['is_completed'] = false;
            }
        }
        
        return $submaterials;
    } catch (Exception $e) {
        // If submaterials table doesn't exist, return empty array
        return [];
    }
}

function completeSubmaterial($materialKey, $submaterialKey, $userId = null, $progressId = null) {
    if (!$userId && !$progressId) return false;
    
    try {
        $db = getDB();
        
        if ($userId) {
            $stmt = $db->prepare("
                INSERT INTO submaterial_progress (user_id, material_key, submaterial_key, is_completed, completed_at) 
                VALUES (?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE is_completed = 1, completed_at = NOW()
            ");
            $stmt->execute([$userId, $materialKey, $submaterialKey]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO submaterial_progress (progress_id, material_key, submaterial_key, is_completed, completed_at) 
                VALUES (?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE is_completed = 1, completed_at = NOW()
            ");
            $stmt->execute([$progressId, $materialKey, $submaterialKey]);
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ADDED: New function to get material by ID (for compatibility)
function getMaterialByKey($materialKey) {
    return getMaterialInfo($materialKey);
}

function getMaterialById($materialId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM materials WHERE id = ? AND is_active = 1");
        $stmt->execute([$materialId]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return null;
        }
        
        return $result;
    } catch (Exception $e) {
        return null;
    }
}

// Get user permissions
function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM permissions WHERE role = ? AND permission = ?");
        $stmt->execute([$user['role'], $permission]);
        return $stmt->fetch()['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// ADDED: Helper function to validate material access
function validateMaterialAccess($materialKey, $subMaterial = null) {
    // Basic validation - can be extended based on your needs
    if (empty($materialKey)) {
        return false;
    }
    
    // Check if material exists
    $material = getMaterialInfo($materialKey);
    if (!$material) {
        return false;
    }
    
    // If submaterial is provided, validate it exists
    if ($subMaterial) {
        $submaterials = getSubmaterials($materialKey);
        $submaterialExists = false;
        foreach ($submaterials as $sm) {
            if ($sm['submaterial_key'] === $subMaterial) {
                $submaterialExists = true;
                break;
            }
        }
        if (!$submaterialExists) {
            return false;
        }
    }
    
    return true;
}

// Update function di functions.php

function convertYouTubeURL($url, $minimal_controls = false) {
    $videoId = '';
    
    // Extract video ID from various YouTube URL formats
    if (strpos($url, 'youtube.com/shorts/') !== false) {
        $videoId = basename(parse_url($url, PHP_URL_PATH));
    } elseif (strpos($url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $videoId = $params['v'] ?? '';
    } elseif (strpos($url, 'youtube.com/embed/') !== false) {
        $videoId = basename(parse_url($url, PHP_URL_PATH));
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $videoId = basename(parse_url($url, PHP_URL_PATH));
    }
    
    if (empty($videoId)) {
        return $url; // Return original if can't extract video ID
    }
    
    $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
    
    if ($minimal_controls) {
        $params = [
            'controls' => '0',        // Hide controls
            'showinfo' => '0',        // Hide video info
            'rel' => '0',             // Don't show related videos
            'autoplay' => '0',        // Don't autoplay
            'modestbranding' => '1',  // Minimal YouTube branding
            'fs' => '0',              // Disable fullscreen
            'disablekb' => '1',       // Disable keyboard controls
            'loop' => '0',            // Don't loop
            'iv_load_policy' => '3',  // Hide annotations
            'cc_load_policy' => '0',  // Hide captions by default
            'playsinline' => '1'      // Play inline on mobile
        ];
        
        $embedUrl .= '?' . http_build_query($params);
    }
    
    return $embedUrl;
}

?>