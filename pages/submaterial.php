<?php
session_start();
$appConfig = include __DIR__ . '/../config/app.php';
include __DIR__ . '/../includes/functions.php';

// Get parameters from URL
$materialKey = $_GET['material_key'] ?? $_GET['material'] ?? null;
$submaterialKey = $_GET['sub'] ?? $_GET['submaterial_key'] ?? null;
$progressId = $_GET['progress_id'] ?? $_SESSION['progress_id'] ?? null;

// Redirect if no material or submaterial key provided
if (!$materialKey || !$submaterialKey) {
    header('Location: ../index.php?msg=' . urlencode('Parameter tidak lengkap') . '&type=error');
    exit;
}

// Validate material access
if (!validateMaterialAccess($materialKey, $submaterialKey)) {
    header('Location: material.php?material_key=' . urlencode($materialKey) . '&msg=' . urlencode('Sub materi tidak ditemukan') . '&type=error');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser ? $currentUser['user_id'] : null;

// Get material and submaterial info
$materialInfo = getMaterialInfo($materialKey);
$submaterials = getSubmaterials($materialKey, $userId, $progressId);

// Find current submaterial
$currentSubmaterial = null;
foreach ($submaterials as $sub) {
    if ($sub['submaterial_key'] === $submaterialKey) {
        $currentSubmaterial = $sub;
        break;
    }
}

if (!$currentSubmaterial) {
    header('Location: material.php?material_key=' . urlencode($materialKey) . '&msg=' . urlencode('Sub materi tidak ditemukan') . '&type=error');
    exit;
}

// Check if submaterial is accessible (not locked)
$isLocked = false;
if ($currentSubmaterial['level'] > 1) {
    $prevLevel = $currentSubmaterial['level'] - 1;
    $prevCompleted = false;
    
    foreach ($submaterials as $sub) {
        if ($sub['level'] == $prevLevel && $sub['is_completed']) {
            $prevCompleted = true;
            break;
        }
    }
    
    if (!$prevCompleted) {
        $isLocked = true;
    }
}

if ($isLocked) {
    header('Location: material.php?material_key=' . urlencode($materialKey) . '&msg=' . urlencode('Sub materi ini masih terkunci. Selesaikan level sebelumnya terlebih dahulu.') . '&type=warning');
    exit;
}

// Get submaterial content (video, text, etc.)
$submaterialContent = getSubmaterialContent($materialKey, $submaterialKey);

include __DIR__ . '/../includes/header.php';
?>

<div class="bg-body min-vh-100 py-5">
    <div class="container py-4">
        
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Beranda</a></li>
                        <li class="breadcrumb-item">
                            <a href="material.php?material_key=<?php echo urlencode($materialKey); ?><?php echo ($progressId ? '&progress_id=' . urlencode($progressId) : ''); ?>" 
                               class="text-decoration-none"><?php echo e($materialInfo['name']); ?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo e($currentSubmaterial['title']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Content Section -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Submaterial Header -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="<?php echo e($currentSubmaterial['icon']); ?> text-primary me-3 fs-3"></i>
                                    <div>
                                        <h1 class="h3 mb-1"><?php echo e($currentSubmaterial['title']); ?></h1>
                                        <p class="text-muted mb-0"><?php echo e($currentSubmaterial['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-primary fs-6 px-3 py-2 me-2">
                                    Level <?php echo $currentSubmaterial['level']; ?>
                                </span>
                                <?php if ($currentSubmaterial['duration']): ?>
                                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                        <i class="bi bi-clock me-1"></i><?php echo e($currentSubmaterial['duration']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($currentSubmaterial['is_completed']): ?>
                                    <span class="badge bg-success fs-6 px-3 py-2 ms-2">
                                        <i class="bi bi-check-circle me-1"></i>Selesai
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Content -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        
                        <?php if (!empty($submaterialContent)): ?>
                            <?php foreach ($submaterialContent as $content): ?>
                                <?php if ($content['content_type'] === 'video'): ?>
                                    <!-- Video Content with Minimal Controls -->
                                    <div class="ratio ratio-16x9">
                                        <iframe src="<?php echo e(convertYouTubeURL($content['content_url'], true)); ?>" 
                                                allowfullscreen 
                                                title="<?php echo e($content['title']); ?>"
                                                class="rounded-top"
                                                allow="autoplay; encrypted-media"
                                                frameborder="0"></iframe>
                                    </div>
                                    
                                    <!-- Video Control Bar -->
                                    <div class="p-3 bg-light border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo e($content['title']); ?></h6>
                                                <small class="text-muted">Klik play pada video untuk memulai pembelajaran</small>
                                            </div>
                                            <button class="btn btn-outline-primary btn-sm" onclick="reloadVideo(this)">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Reload Video
                                            </button>
                                        </div>
                                    </div>
                                    
                                <?php elseif ($content['content_type'] === 'text'): ?>
                                    <!-- Text Content -->
                                    <div class="p-4">
                                        <h4><?php echo e($content['title']); ?></h4>
                                        <div class="text-body">
                                            <?php echo $content['content_data']; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default Content -->
                            <div class="ratio ratio-16x9">
                                <iframe src="<?php echo e(convertYouTubeURL('https://www.youtube.com/embed/fuGlAULKJ0g', true)); ?>" 
                                        allowfullscreen 
                                        title="<?php echo e($currentSubmaterial['title']); ?>"
                                        class="rounded-top"
                                        allow="autoplay; encrypted-media"
                                        frameborder="0"></iframe>
                            </div>
                            
                            <!-- Default Video Control Bar -->
                            <div class="p-3 bg-light border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Video Pembelajaran Demo</h6>
                                        <small class="text-muted">Klik play pada video untuk memulai pembelajaran</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" onclick="reloadVideo(this)">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reload Video
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <h4>Materi Pembelajaran</h4>
                                <p class="text-body"><?php echo e($currentSubmaterial['description']); ?></p>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Konten pembelajaran untuk materi ini masih dalam pengembangan. 
                                    Video demo ditampilkan sebagai contoh.
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="text-muted mb-4">
                                    Apakah Anda sudah memahami materi ini dan siap melanjutkan ke materi berikutnya?
                                </p>
                                
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <a href="material.php?material_key=<?php echo urlencode($materialKey); ?><?php echo ($progressId ? '&progress_id=' . urlencode($progressId) : ''); ?>" 
                                       class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Materi
                                    </a>
                                    
                                    <?php if (!$currentSubmaterial['is_completed']): ?>
                                        <button type="button" class="btn btn-success btn-lg" id="completeButton">
                                            <i class="bi bi-check-circle me-2"></i>Tandai Selesai
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-lg" id="reviewButton">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Ulangi Materi
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Completion Confirmation Modal -->
<div class="modal fade" id="completionModal" tabindex="-1" aria-labelledby="completionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completionModalLabel">Konfirmasi Penyelesaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin sudah memahami materi <strong><?php echo e($currentSubmaterial['title']); ?></strong>?</p>
                <p><small class="text-muted">Setelah menandai selesai, Anda dapat melanjutkan ke level berikutnya.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Belum</button>
                <button type="button" class="btn btn-success" id="confirmComplete">Ya, Sudah Paham</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const completeButton = document.getElementById('completeButton');
    const reviewButton = document.getElementById('reviewButton');
    const confirmCompleteBtn = document.getElementById('confirmComplete');

    if (completeButton) {
        completeButton.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('completionModal'));
            modal.show();
        });
    }

    if (confirmCompleteBtn) {
        confirmCompleteBtn.addEventListener('click', () => {
            markAsCompleted();
        });
    }

    if (reviewButton) {
        reviewButton.addEventListener('click', () => {
            // Scroll to top to review content
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    function markAsCompleted() {
        // Show loading state
        confirmCompleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        confirmCompleteBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'complete_submaterial');
        formData.append('material_key', '<?php echo $materialKey; ?>');
        formData.append('submaterial_key', '<?php echo $submaterialKey; ?>');
        <?php if ($progressId): ?>
        formData.append('progress_id', '<?php echo $progressId; ?>');
        <?php endif; ?>
        
        fetch('<?php echo ($userId || $progressId) ? '../index.php' : '#'; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and redirect
                const modal = bootstrap.Modal.getInstance(document.getElementById('completionModal'));
                modal.hide();
                
                // Show success message and redirect
                setTimeout(() => {
                    window.location.href = 'material.php?material_key=<?php echo urlencode($materialKey); ?><?php echo ($progressId ? '&progress_id=' . urlencode($progressId) : ''); ?>&msg=' + encodeURIComponent('Materi berhasil diselesaikan!') + '&type=success';
                }, 500);
            } else {
                alert('Terjadi kesalahan: ' + data.message);
                // Reset button
                confirmCompleteBtn.innerHTML = 'Ya, Sudah Paham';
                confirmCompleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
            // Reset button
            confirmCompleteBtn.innerHTML = 'Ya, Sudah Paham';
            confirmCompleteBtn.disabled = false;
        });
    }
});

function reloadVideo(button) {
    // Find the iframe in the same card
    let iframe = button.closest('.card').querySelector('iframe');
    if (iframe) {
        let currentSrc = iframe.src;
        
        // Remove autoplay parameter if exists to reset video
        currentSrc = currentSrc.replace(/[?&]autoplay=1/, '');
        
        // Reload the iframe
        iframe.src = '';
        setTimeout(() => {
            iframe.src = currentSrc;
        }, 100);
        
        // Show feedback
        let originalHtml = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check me-1"></i>Video Direload';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.disabled = false;
        }, 2000);
    }
}
</script>

<?php include '../includes/footer.php'; ?>