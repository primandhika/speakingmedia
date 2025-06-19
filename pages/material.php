<link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/materials.css">
<?php
session_start();
$appConfig = include __DIR__ . '/../config/app.php';
include __DIR__ . '/../includes/functions.php';

// Get parameters from URL - support multiple parameter names for backward compatibility
$materialKey = $_GET['material_key'] ?? $_GET['material_id'] ?? $_GET['material'] ?? null;
$subMaterial = $_GET['sub_material'] ?? null;
$progressId = $_GET['progress_id'] ?? $_SESSION['progress_id'] ?? null;

// Redirect if no material key provided
if (!$materialKey) {
    header('Location: ../index.php?msg=' . urlencode('Parameter tidak valid') . '&type=error');
    exit;
}

// Validate material access
if (!validateMaterialAccess($materialKey, $subMaterial)) {
    header('Location: ../index.php?msg=' . urlencode('Materi tidak ditemukan') . '&type=error');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser ? $currentUser['user_id'] : null;

// Get material info and submaterials
$materialInfo = getMaterialInfo($materialKey);
$submaterials = getSubmaterials($materialKey, $userId, $progressId);

// If no submaterials found, redirect back
if (empty($submaterials)) {
    header('Location: ../index.php?msg=' . urlencode('Sub materi tidak ditemukan untuk materi ini') . '&type=warning');
    exit;
}

// Calculate progress
$completedCount = count(array_filter($submaterials, function($sub) { return $sub['is_completed']; }));
$totalCount = count($submaterials);
$progressPercentage = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5" data-aos="fade-up">
                <div class="d-inline-block p-3 bg-primary bg-opacity-10 rounded-circle mb-3">
                    <i class="<?php echo e($materialInfo['icon']); ?> display-4 text-primary"></i>
                </div>
                <h1 class="display-5 fw-bold text-primary mb-3"><?php echo e($materialInfo['name']); ?></h1>
                <p class="lead text-muted"><?php echo e($materialInfo['description']); ?></p>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="bi bi-signal me-1"></i><?php echo e($materialInfo['difficulty']); ?>
                    </span>
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="bi bi-clock me-1"></i><?php echo e($materialInfo['duration']); ?>
                    </span>
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="bi bi-list-ol me-1"></i><?php echo $totalCount; ?> Sub Materi
                    </span>
                </div>
            </div>

            <!-- Progress Overview -->
            <?php if ($userId || $progressId): ?>
                <div class="card border-0 shadow-sm mb-5" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="bi bi-graph-up me-2"></i>Progress Pembelajaran
                                </h5>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $progressPercentage; ?>%"></div>
                                </div>
                                <small class="text-muted"><?php echo $completedCount; ?> dari <?php echo $totalCount; ?> sub materi selesai</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="display-6 fw-bold text-success"><?php echo $progressPercentage; ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Submaterials Grid -->
            <div class="row g-4">
                <?php 
                $delay = 200;
                foreach ($submaterials as $index => $submaterial): 
                    // Determine if this submaterial is locked
                    $isLocked = false;
                    $canAccess = true;
                    
                    // Lock logic: Level > 1 requires previous level to be completed
                    if ($submaterial['level'] > 1) {
                        $prevLevel = $submaterial['level'] - 1;
                        $prevCompleted = false;
                        
                        // Check if previous level is completed
                        foreach ($submaterials as $sub) {
                            if ($sub['level'] == $prevLevel && $sub['is_completed']) {
                                $prevCompleted = true;
                                break;
                            }
                        }
                        
                        if (!$prevCompleted) {
                            $isLocked = true;
                            $canAccess = false;
                        }
                    }
                    
                    // Status classes
                    $cardClass = 'h-100 border-0 shadow-sm';
                    if ($isLocked) {
                        $cardClass .= ' opacity-75';
                    } else {
                        $cardClass .= ' hover-card';
                    }
                    
                    // Header color based on status
                    if ($submaterial['is_completed']) {
                        $headerClass = 'bg-success text-white';
                        $statusIcon = 'bi-check-circle-fill';
                    } elseif ($isLocked) {
                        $headerClass = 'bg-light text-muted';
                        $statusIcon = 'bi-lock-fill';
                    } else {
                        $headerClass = 'bg-primary text-white';
                        $statusIcon = 'bi-play-circle-fill';
                    }
                ?>
                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="card <?php echo $cardClass; ?>">
                            <?php if ($canAccess): ?>
                                <a href="submaterial.php?material_key=<?php echo urlencode($materialKey); ?>&sub=<?php echo urlencode($submaterial['submaterial_key']); ?><?php echo ($progressId ? '&progress_id=' . urlencode($progressId) : ''); ?>" 
                                   class="text-decoration-none">
                            <?php endif; ?>
                            
                                <div class="card-header <?php echo $headerClass; ?> d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="<?php echo e($submaterial['icon']); ?> me-2"></i>
                                        <?php echo e($submaterial['title']); ?>
                                    </h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Level Badge -->
                                        <span class="badge <?php echo $submaterial['is_completed'] ? 'bg-light text-success' : ($isLocked ? 'bg-secondary' : 'bg-light text-primary'); ?> rounded-pill">
                                            Level <?php echo $submaterial['level']; ?>
                                        </span>
                                        <!-- Status Icon -->
                                        <i class="<?php echo $statusIcon; ?> fs-5"></i>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4">
                                    <p class="text-muted mb-3"><?php echo e($submaterial['description']); ?></p>
                                    
                                    <!-- Duration and Status Info -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if ($submaterial['duration']): ?>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-clock me-1"></i><?php echo e($submaterial['duration']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($submaterial['is_completed']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Selesai
                                                </span>
                                            <?php elseif ($isLocked): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-lock me-1"></i>Terkunci
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-play me-1"></i>Siap dipelajari
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($canAccess): ?>
                                            <span class="btn btn-sm <?php echo $submaterial['is_completed'] ? 'btn-outline-success' : 'btn-outline-primary'; ?>">
                                                <?php echo $submaterial['is_completed'] ? 'Ulangi' : 'Mulai'; ?>
                                                <i class="bi bi-arrow-right ms-1"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Lock Information -->
                                    <?php if ($isLocked): ?>
                                        <div class="alert alert-light border d-flex align-items-center" role="alert">
                                            <i class="bi bi-info-circle me-2 text-muted"></i>
                                            <small class="text-muted mb-0">
                                                Selesaikan Level <?php echo $submaterial['level'] - 1; ?> terlebih dahulu untuk membuka materi ini
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            
                            <?php if ($canAccess): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    $delay += 100;
                endforeach; 
                ?>
            </div>

            <!-- Learning Progress Summary -->
            <?php if ($userId || $progressId): ?>
                <div class="card border-0 shadow-sm mt-5" data-aos="fade-up" data-aos-delay="600">
                    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h4 class="mb-0">
                            <i class="bi bi-trophy me-2"></i>Ringkasan Progress
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="h3 text-success"><?php echo $completedCount; ?></div>
                                <small class="text-muted">Materi Selesai</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 text-warning"><?php echo ($totalCount - $completedCount); ?></div>
                                <small class="text-muted">Belum Selesai</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 text-primary"><?php echo $progressPercentage; ?>%</div>
                                <small class="text-muted">Progress Total</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h3 text-info"><?php echo $totalCount; ?></div>
                                <small class="text-muted">Total Sub Materi</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Learning Tips -->
            <div class="card border-0 shadow-sm mt-5" data-aos="fade-up" data-aos-delay="700">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h4 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Tips Pembelajaran
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    Pelajari sub materi secara berurutan dari Level 1
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    Selesaikan satu level sebelum melanjutkan ke level berikutnya
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    Praktikkan setiap teknik yang dipelajari
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    <?php if ($userId): ?>
                                        Progress Anda akan tersimpan otomatis
                                    <?php else: ?>
                                        Gunakan Progress ID untuk melacak kemajuan
                                    <?php endif; ?>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    Ulangi materi yang sudah dipelajari untuk penguatan
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-arrow-right text-primary me-2"></i>
                                    Terapkan dalam situasi nyata untuk hasil optimal
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="800">
                <a href="../index.php" class="btn btn-outline-primary btn-lg me-3">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                </a>
                <button class="btn btn-primary btn-lg" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Cetak Materi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals (from parent directory) -->
<?php include '../modals/login.php'; ?>
<?php include '../modals/register.php'; ?>
<?php include '../modals/progress_id.php'; ?>

<?php include '../includes/footer.php'; ?>