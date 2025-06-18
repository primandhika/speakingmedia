<?php
// index.php - File utama dengan database integration

// Include file konfigurasi dan fungsi
$appConfig = include 'config/app.php';
include 'includes/functions.php';

// Handle POST requests untuk login/logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $userId = $_POST['user_id'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if ($userId && $password) {
                    $user = validateUser($userId, $password);
                } else if ($userId) {
                    // Simple ID validation untuk demo
                    $user = validateUser($userId);
                }
                
                if ($user) {
                    setUserLogin($user);
                    $response = ['success' => true, 'message' => 'Login berhasil! Selamat datang, ' . $user['name']];
                } else {
                    $response = ['success' => false, 'message' => 'ID atau password tidak valid!'];
                }
                break;
                
            case 'logout':
                logoutUser();
                $response = ['success' => true, 'message' => 'Logout berhasil!'];
                break;
        }
    }
    
    // Return JSON untuk AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect untuk non-AJAX
    $messageType = $response['success'] ? 'success' : 'error';
    header('Location: index.php?msg=' . urlencode($response['message']) . '&type=' . $messageType);
    exit;
}

// Handle material click
if (isset($_GET['material'])) {
    $materialKey = $_GET['material'];
    $currentUser = getCurrentUser();
    
    if ($currentUser) {
        updateMaterialProgress($currentUser['user_id'], $materialKey);
    }
    
    // Redirect ke halaman materi
    header('Location: pages/' . $materialKey . '.html');
    exit;
}

// Get materials dari database
$currentUser = getCurrentUser();
$userId = $currentUser ? $currentUser['user_id'] : null;
$materials = getMaterialsFromDB($userId);

// Get statistics
$stats = getStudyStatistics($userId);

// Handle search
$searchQuery = $_GET['search'] ?? '';
$searchResults = [];
if (!empty($searchQuery)) {
    $searchResults = searchMaterials($searchQuery);
    logActivity('search', ['query' => $searchQuery, 'results_count' => count($searchResults)]);
}

// Handle messages
$message = $_GET['msg'] ?? '';
$messageType = $_GET['type'] ?? 'info';

// Log page view
logActivity('page_view', ['page' => 'index']);

// Include header
include 'includes/header.php';
?>

    <!-- Main Content -->
    <div class="container py-5 mt-5">
        <!-- Welcome Message/Alert -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert" data-aos="fade-down">
                <i class="bi bi-<?php echo $messageType === 'error' ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                <?php echo e($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
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

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="header-text text-center mb-4 text-primary floating-animation">
                            <?php echo $appConfig['headerText']; ?>
                        </h1>
                        
                        <!-- Search Box -->
                        <div class="mb-4 position-relative" data-aos="fade-up" data-aos-delay="200">
                            <form method="GET" action="index.php">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 rounded-end-pill shadow-sm" 
                                           id="searchInput" 
                                           name="search"
                                           value="<?php echo e($searchQuery); ?>"
                                           placeholder="<?php echo $appConfig['searchPlaceholder']; ?>"
                                           onkeyup="showSuggestions()">
                                    <?php if (!empty($searchQuery)): ?>
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <div id="suggestions" class="list-group position-absolute w-100 z-3 d-none mt-1 rounded-3 shadow-lg"></div>
                        </div>

                        <!-- Search Results Info -->
                        <?php if (!empty($searchQuery)): ?>
                            <div class="search-info mb-4" data-aos="fade-up">
                                <div class="alert alert-info border-0">
                                    <i class="bi bi-search me-2"></i>
                                    Menampilkan <?php echo count($searchResults); ?> hasil untuk "<?php echo e($searchQuery); ?>"
                                    <a href="index.php" class="btn btn-sm btn-outline-primary ms-3">Tampilkan Semua</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Material Buttons -->
                        <div class="row g-3" data-aos="fade-up" data-aos-delay="400">
                            <?php 
                            $materialsToShow = !empty($searchQuery) ? $searchResults : $materials;
                            $delay = 600;
                            foreach ($materialsToShow as $key => $material): 
                                // Handle search results vs materials difference
                                if (!empty($searchQuery)) {
                                    $materialKey = $material['material_key'];
                                    $clicks = 0;
                                    $progress = 0;
                                } else {
                                    $materialKey = $key;
                                    $clicks = $material['clicks'];
                                    $progress = getProgressPercentage($clicks);
                                }
                            ?>
                                <div class="col-6 col-md-4 col-lg-3" 
                                     data-aos="zoom-in" 
                                     data-aos-delay="<?php echo $delay; ?>">
                                    <a href="<?php echo isUserLoggedIn() ? 'index.php?material=' . $materialKey : '#'; ?>" 
                                       class="material-btn d-block text-center text-decoration-none p-4 rounded-3 bg-body shadow-sm h-100 <?php echo !isUserLoggedIn() ? 'requires-login' : ''; ?>"
                                       <?php echo !isUserLoggedIn() ? 'data-bs-toggle="modal" data-bs-target="#loginModal"' : ''; ?>>
                                        <i class="<?php echo $material['icon']; ?> material-icon mb-3"></i>
                                        <div class="h5 mb-2"><?php echo e($material['name']); ?></div>
                                        
                                        <?php if (!empty($material['description'])): ?>
                                            <p class="text-muted small mb-3"><?php echo e($material['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if (isUserLoggedIn()): ?>
                                            <!-- Progress bar -->
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                            
                                            <div class="text-muted small">
                                                <i class="bi bi-mouse"></i> <?php echo $clicks; ?> klik | 
                                                <?php echo getProgressText($clicks); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small">
                                                <i class="bi bi-lock me-1"></i>Login untuk mulai belajar
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php 
                                $delay += 100;
                            endforeach; 
                            ?>
                        </div>

                        <!-- Empty State untuk Search -->
                        <?php if (!empty($searchQuery) && empty($searchResults)): ?>
                            <div class="text-center py-5" data-aos="fade-up">
                                <i class="bi bi-search display-1 text-muted mb-3"></i>
                                <h4 class="text-muted">Tidak ada materi yang ditemukan</h4>
                                <p class="text-muted mb-4">Coba kata kunci lain atau lihat semua materi yang tersedia</p>
                                <a href="index.php" class="btn btn-primary">Lihat Semua Materi</a>
                            </div>
                        <?php endif; ?>

                        <!-- Stats -->
                        <?php if (isUserLoggedIn()): ?>
                            <div class="row mt-5 g-3" data-aos="fade-up" data-aos-delay="800">
                                <div class="col-md-3">
                                    <div class="card border-0 bg-primary text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <h3 class="fw-bold mb-0"><?php echo $stats['total_clicks']; ?></h3>
                                            <p class="mb-0">Total Interaksi</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-success text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <h3 class="fw-bold mb-0"><?php echo $stats['studied_materials']; ?>/<?php echo $stats['total_materials']; ?></h3>
                                            <p class="mb-0">Materi Dipelajari</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-warning text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <h3 class="fw-bold mb-0"><?php echo $stats['completed_materials']; ?></h3>
                                            <p class="mb-0">Selesai</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-info text-white h-100">
                                        <div class="card-body text-center py-3">
                                            <h3 class="fw-bold mb-0"><?php echo round($stats['avg_progress']); ?>%</h3>
                                            <p class="mb-0">Rata-rata Progress</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">
                        <i class="bi bi-box-arrow-in-right text-primary me-2"></i>
                        Masuk ke Bicaranta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="loginUserId" placeholder="ID Pengguna" required>
                            <label for="loginUserId">ID Pengguna</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="loginPassword" placeholder="Password">
                            <label for="loginPassword">Password (Opsional untuk demo)</label>
                        </div>
                        <div id="loginAlert" class="alert d-none" role="alert"></div>
                        
                        <div class="bg-light p-3 rounded mb-3">
                            <h6 class="mb-2"><i class="bi bi-info-circle text-info me-1"></i>Demo Account</h6>
                            <p class="small mb-2">Gunakan ID berikut untuk demo:</p>
                            <ul class="small mb-0">
                                <li><code>00029</code> - Admin Demo</li>
                                <li><code>INST001</code> - Instructor Demo</li>
                                <li><code>STU001</code> - Student Demo</li>
                            </ul>
                            <p class="small mt-2 mb-0">Password: <code>password</code> (atau kosongkan)</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="loginSubmit">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ID Modal (Legacy support) -->
    <div class="modal fade" id="idModal" tabindex="-1" aria-labelledby="idModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idModalLabel">
                        <i class="bi bi-person-check me-2"></i>
                        Demo ID Login
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="userIdInput" placeholder="00029" value="00029">
                        <label for="userIdInput">ID Pengguna</label>
                    </div>
                    <div id="idAlert" class="alert alert-danger mt-3 d-flex align-items-center d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ID tidak ditemukan. Silakan periksa kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="validateIdButton">
                        <i class="bi bi-check-lg me-1"></i>Masuk
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?><?php
// index.php - File utama dengan struktur proper

// Include file konfigurasi dan fungsi
$appConfig = include 'config/app.php';
$materials = include 'config/materials.php';
include 'includes/functions.php';

// Simulasi data klik dari session/database
session_start();
if (!isset($_SESSION['material_clicks'])) {
    $_SESSION['material_clicks'] = [];
}

// Update clicks jika ada parameter
if (isset($_GET['material'])) {
    $material = $_GET['material'];
    updateMaterialClick($material, $materials);
}

// Merge data klik dengan data materi
mergeMaterialClicks($materials);

// Get statistics
$stats = getStudyStatistics($materials);

// Include header
include 'includes/header.php';
?>

    <!-- Main Content -->
    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="header-text text-center mb-4 text-primary floating-animation">
                            <?php echo $appConfig['headerText']; ?>
                        </h1>
                        
                        <!-- Search Box -->
                        <div class="mb-4 position-relative" data-aos="fade-up" data-aos-delay="200">
                            <input type="text" 
                                   class="form-control form-control-lg rounded-pill shadow-sm" 
                                   id="searchInput" 
                                   placeholder="<?php echo $appConfig['searchPlaceholder']; ?>"
                                   onkeyup="showSuggestions()">
                            <div id="suggestions" class="list-group position-absolute w-100 z-3 d-none mt-1 rounded-3 shadow-lg"></div>
                        </div>

                        <!-- Material Buttons -->
                        <div class="row g-3" data-aos="fade-up" data-aos-delay="400">
                            <?php foreach ($materials as $key => $material): ?>
                                <div class="col-6 col-md-4 col-lg-3" 
                                     data-aos="zoom-in" 
                                     data-aos-delay="<?php echo 600 + (array_search($key, array_keys($materials)) * 100); ?>">
                                    <a href="pages/<?php echo $key; ?>.html?material=<?php echo $key; ?>" 
                                       class="material-btn d-block text-center text-decoration-none p-4 rounded-3 bg-body shadow-sm h-100">
                                        <i class="<?php echo $material['icon']; ?> material-icon mb-3"></i>
                                        <div class="h5 mb-2"><?php echo $material['name']; ?></div>
                                        
                                        <!-- Progress bar -->
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar" 
                                                 style="width: <?php echo getProgressPercentage($material['clicks']); ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="text-muted small">
                                            <i class="bi bi-mouse"></i> <?php echo $material['clicks']; ?> klik | 
                                            <?php echo getProgressText($material['clicks']); ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Stats -->
                        <div class="row mt-4 g-3" data-aos="fade-up" data-aos-delay="800">
                            <div class="col-md-6">
                                <div class="card border-0 bg-primary text-white h-100">
                                    <div class="card-body text-center py-3">
                                        <h3 class="fw-bold mb-0"><?php echo $stats['total_clicks']; ?></h3>
                                        <p class="mb-0">Total Interaksi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-success text-white h-100">
                                    <div class="card-body text-center py-3">
                                        <h3 class="fw-bold mb-0"><?php echo $stats['studied_materials']; ?>/<?php echo $stats['total_materials']; ?></h3>
                                        <p class="mb-0">Materi Dipelajari</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk ID (tetap seperti asli) -->
    <div class="modal fade" id="idModal" tabindex="-1" aria-labelledby="idModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idModalLabel">
                        <i class="bi bi-person-check me-2"></i>
                        Masukkan ID Anda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="userIdInput" placeholder="Masukkan ID Anda">
                        <label for="userIdInput">ID Pengguna</label>
                    </div>
                    <div id="idAlert" class="alert alert-danger mt-3 d-flex align-items-center d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ID tidak ditemukan. Silakan periksa kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="validateIdButton">
                        <i class="bi bi-check-lg me-1"></i>Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>        <!-- Material Buttons -->
                        <div class="row g-3" data-aos="fade-up" data-aos-delay="400">
                            <?php foreach ($materials as $key => $material): ?>
                                <div class="col-6 col-md-4 col-lg-3" 
                                     data-aos="zoom-in" 
                                     data-aos-delay="<?php echo 600 + (array_search($key, array_keys($materials)) * 100); ?>">
                                    <a href="<?php echo $key; ?>.html?material=<?php echo $key; ?>" 
                                       class="material-btn d-block text-center text-decoration-none p-4 rounded-3 bg-body shadow-sm h-100">
                                        <i class="<?php echo $material['icon']; ?> material-icon mb-3"></i>
                                        <div class="h5 mb-2"><?php echo $material['name']; ?></div>
                                        
                                        <!-- Progress bar -->
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar" 
                                                 style="width: <?php echo getProgressPercentage($material['clicks']); ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="text-muted small">
                                            <i class="bi bi-mouse"></i> <?php echo $material['clicks']; ?> klik | 
                                            <?php echo getProgressText($material['clicks']); ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Stats -->
                        <div class="row mt-4 g-3" data-aos="fade-up" data-aos-delay="800">
                            <div class="col-md-6">
                                <div class="card border-0 bg-primary text-white h-100">
                                    <div class="card-body text-center py-3">
                                        <h3 class="fw-bold mb-0"><?php echo $stats['total_clicks']; ?></h3>
                                        <p class="mb-0">Total Interaksi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-success text-white h-100">
                                    <div class="card-body text-center py-3">
                                        <h3 class="fw-bold mb-0"><?php echo $stats['studied_materials']; ?>/<?php echo $stats['total_materials']; ?></h3>
                                        <p class="mb-0">Materi Dipelajari</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk ID (tetap seperti asli) -->
    <div class="modal fade" id="idModal" tabindex="-1" aria-labelledby="idModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idModalLabel">
                        <i class="bi bi-person-check me-2"></i>
                        Masukkan ID Anda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="userIdInput" placeholder="Masukkan ID Anda">
                        <label for="userIdInput">ID Pengguna</label>
                    </div>
                    <div id="idAlert" class="alert alert-danger mt-3 d-flex align-items-center d-none" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ID tidak ditemukan. Silakan periksa kembali.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="validateIdButton">
                        <i class="bi bi-check-lg me-1"></i>Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>