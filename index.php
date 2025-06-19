<?php
session_start();


$appConfig = include 'config/app.php';
include 'includes/functions.php';

// Handle POST requests (login/logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $userId = trim($_POST['user_id'] ?? '');
                $password = trim($_POST['password'] ?? '');
                
                if ($userId) {
                    $user = validateUser($userId, $password);
                    if ($user) {
                        setUserLogin($user);
                        $response = ['success' => true, 'message' => 'Login berhasil! Selamat datang, ' . $user['name']];
                    } else {
                        $response = ['success' => false, 'message' => 'ID atau password tidak valid!'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'ID pengguna harus diisi!'];
                }
                break;
                
            case 'logout':
                logoutUser();
                $response = ['success' => true, 'message' => 'Logout berhasil!'];
                break;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'complete_submaterial') {
    $materialKey = trim($_POST['material_key'] ?? '');
    $submaterialKey = trim($_POST['submaterial_key'] ?? '');
    $progressId = trim($_POST['progress_id'] ?? '');
    
    $response = ['success' => false, 'message' => ''];
    
    if ($materialKey && $submaterialKey) {
        if ($currentUser) {
            $success = completeSubmaterial($materialKey, $submaterialKey, $currentUser['user_id'], null);
        } elseif ($progressId) {
            $success = completeSubmaterial($materialKey, $submaterialKey, null, $progressId);
        } else {
            $success = false;
        }
        
        if ($success) {
            $response = ['success' => true, 'message' => 'Sub materi berhasil diselesaikan!'];
        } else {
            $response = ['success' => false, 'message' => 'Gagal menandai sub materi sebagai selesai'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Parameter tidak lengkap'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
    
    // Return JSON for AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect for non-AJAX
    $messageType = $response['success'] ? 'success' : 'error';
    header('Location: index.php?msg=' . urlencode($response['message']) . '&type=' . $messageType);
    exit;
}

// Handle material click - REVISED SECTION
if (isset($_GET['material']) || isset($_GET['material_id']) || isset($_GET['material_key'])) {
    $currentUser = getCurrentUser();
    $progressId = $_GET['progress_id'] ?? $_SESSION['progress_id'] ?? null;
    
    // Get parameters - support multiple parameter names for backward compatibility
    $materialKey = $_GET['material_key'] ?? $_GET['material_id'] ?? $_GET['material'] ?? null;
    $subMaterial = $_GET['sub_material'] ?? null;
    
    if ($materialKey) {
        // Validate material access
        if (!validateMaterialAccess($materialKey, $subMaterial)) {
            header('Location: index.php?msg=' . urlencode('Materi tidak ditemukan!') . '&type=error');
            exit;
        }
        
        // Track progress for logged users or users with progress ID
        if ($currentUser) {
            updateMaterialProgress($currentUser['user_id'], $materialKey, $subMaterial);
        } elseif ($progressId) {
            updateGuestProgress($progressId, $materialKey, $subMaterial);
        }
        
        // Build URL parameters for material.php
        $params = [
            'material_key' => $materialKey
        ];
        
        if ($subMaterial) {
            $params['sub_material'] = $subMaterial;
        }
        
        if ($progressId && !$currentUser) {
            $params['progress_id'] = $progressId;
        }
        
        // Redirect to material.php with proper parameters
        $queryString = http_build_query($params);
        header('Location: pages/material.php?' . $queryString);
        exit;
    } else {
        header('Location: index.php?msg=' . urlencode('Parameter materi tidak valid!') . '&type=error');
        exit;
    }
}

// Handle progress ID actions
if (isset($_POST['action']) && $_POST['action'] === 'set_progress_id') {
    $progressId = trim($_POST['progress_id'] ?? '');
    if (!empty($progressId) && preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $progressId)) {
        $_SESSION['progress_id'] = $progressId;
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Progress ID berhasil disimpan']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Progress ID tidak valid (3-20 karakter, huruf/angka/_/-)']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'clear_progress_id') {
    unset($_SESSION['progress_id']);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Progress ID telah dihapus']);
    exit;
}

// Get current user and materials
$currentUser = getCurrentUser();
$userId = $currentUser ? $currentUser['user_id'] : null;
$progressId = $_SESSION['progress_id'] ?? null;

// Get materials with progress (for logged users or guest with progress ID)
if ($userId) {
    $materials = getMaterialsFromDB($userId);
    $stats = getStudyStatistics($userId);
} elseif ($progressId) {
    $materials = getMaterialsForGuest($progressId);
    $stats = getGuestStatistics($progressId);
} else {
    $materials = getMaterialsFromDB(null);
    $stats = getStudyStatistics(null);
}

// Handle search
$searchQuery = $_GET['search'] ?? '';
$searchResults = [];
if (!empty($searchQuery)) {
    $searchResults = searchMaterials($searchQuery);
    if ($currentUser) {
        logActivity('search', ['query' => $searchQuery, 'results_count' => count($searchResults)]);
    }
}

// Handle messages
$message = $_GET['msg'] ?? '';
$messageType = $_GET['type'] ?? 'info';

// Log page view
if ($currentUser) {
    logActivity('page_view', ['page' => 'index']);
}

// Include header
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container py-5 mt-5">
    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert" data-aos="fade-down">
            <i class="bi bi-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
            <?php echo e($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome Section for logged in users -->
    <?php if (isUserLoggedIn()): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="welcome-card card border-0 bg-primary text-white" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2">
                                    <i class="bi bi-sun me-2"></i>
                                    Selamat datang kembali, <?php echo e($currentUser['name']); ?>!
                                </h4>
                                <p class="mb-0 opacity-75">
                                    Lanjutkan perjalanan pembelajaran Anda. Anda telah menyelesaikan 
                                    <?php echo $stats['completed_materials']; ?> dari <?php echo $stats['total_materials']; ?> materi.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="badge bg-light text-primary fs-6 px-3 py-2">
                                    <i class="bi bi-award me-1"></i>
                                    <?php echo ucfirst($currentUser['role']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg" data-aos="fade-up">
                <div class="card-body p-4 p-md-5">
                    <!-- Header -->
                    <h1 class="header-text text-center mb-4 text-primary floating-animation">
                        <?php echo e($appConfig['headerText']); ?>
                    </h1>
                    
                    <!-- Search Section -->
                    <?php include 'includes/search_section.php'; ?>

                    <!-- Material Grid -->
                    <?php include 'includes/material_grid.php'; ?>

                    <!-- Empty State for Search -->
                    <?php if (!empty($searchQuery) && empty($searchResults)): ?>
                        <div class="text-center py-5" data-aos="fade-up">
                            <i class="bi bi-search display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Tidak ada materi yang ditemukan</h4>
                            <p class="text-muted mb-4">Coba kata kunci lain atau lihat semua materi yang tersedia</p>
                            <a href="index.php" class="btn btn-primary">Lihat Semua Materi</a>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Section -->
                    <?php include 'includes/statistics.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
<?php include 'modals/login.php'; ?>
<?php include 'modals/register.php'; ?>
<?php include 'modals/progress_id.php'; ?>

<script>
// Function to navigate to material page
function goToMaterial(materialKey, subMaterial = null) {
    let url = 'index.php?material_key=' + encodeURIComponent(materialKey);
    
    if (subMaterial) {
        url += '&sub_material=' + encodeURIComponent(subMaterial);
    }
    
    // Add progress_id if user is not logged in but has progress_id
    <?php if (!$currentUser && $progressId): ?>
    url += '&progress_id=' + encodeURIComponent('<?php echo $progressId; ?>');
    <?php endif; ?>
    
    window.location.href = url;
}

// Logout function
function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'logout';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Show progress function (placeholder)
function showProgress() {
    alert('Fitur progress detail akan segera tersedia!');
}
</script>

<?php include 'includes/footer.php'; ?>