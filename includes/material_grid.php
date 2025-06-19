<?php
/**
 * includes/material_grid.php - Material grid component
 */
?>

<!-- Material Buttons -->
<div class="row g-3" data-aos="fade-up" data-aos-delay="400">
    <?php 
    $materialsToShow = [];
    if (!empty($searchQuery)) {
        // Convert search results to same format as materials
        foreach ($searchResults as $material) {
            $progress = $userId ? getUserProgress($userId, $material['material_key']) : 
                       ($progressId ? getGuestProgress($progressId, $material['material_key']) : 
                       ['clicks' => 0, 'progress_percentage' => 0, 'status' => 'not_started']);
            
            $materialsToShow[$material['material_key']] = [
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
    } else {
        $materialsToShow = $materials;
    }
    
    $delay = 600;
    foreach ($materialsToShow as $key => $material): 
        $materialKey = $material['material_key'] ?? $key;
        $clicks = $material['clicks'] ?? 0;
        $progress = $material['progress_percentage'] ?? 0;
        $status = $material['status'] ?? 'not_started';
        
        // Status color
        $statusColor = $status === 'completed' ? 'success' : ($status === 'in_progress' ? 'warning' : 'secondary');
    ?>
        <div class="col-6 col-md-4 col-lg-3" 
             data-aos="zoom-in" 
             data-aos-delay="<?php echo $delay; ?>">
            
            <!-- REVISED: Using JavaScript function instead of direct link -->
            <a href="javascript:void(0)" 
               onclick="goToMaterial('<?php echo e($materialKey); ?>')"
               class="material-btn d-block text-center text-decoration-none p-4 rounded-3 bg-body shadow-sm h-100 position-relative">
                
                <!-- Status badge -->
                <?php if (($userId || $progressId) && $status !== 'not_started'): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-<?php echo $statusColor; ?>">
                        <?php echo $status === 'completed' ? '✓' : $progress . '%'; ?>
                    </span>
                <?php endif; ?>
                
                <i class="<?php echo e($material['icon']); ?> material-icon mb-3"></i>
                <div class="h5 mb-2"><?php echo e($material['name']); ?></div>
                
                <?php if (!empty($material['description'])): ?>
                    <p class="text-muted small mb-3"><?php echo e($material['description']); ?></p>
                <?php endif; ?>
                
                <!-- Difficulty and Duration -->
                <div class="mb-2">
                    <span class="badge bg-light text-dark me-1">
                        <i class="bi bi-signal me-1"></i><?php echo e($material['difficulty']); ?>
                    </span>
                    <?php if (!empty($material['duration'])): ?>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-clock me-1"></i><?php echo e($material['duration']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($userId || $progressId): ?>
                    <!-- Progress bar -->
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-<?php echo $statusColor; ?>" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    
                    <div class="text-muted small">
                        <i class="bi bi-mouse"></i> <?php echo $clicks; ?> klik | 
                        <?php echo getProgressText($clicks); ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted small">
                        <i class="bi bi-play-circle me-1"></i>Mulai belajar sekarang
                    </div>
                <?php endif; ?>
            </a>
        </div>
    <?php 
        $delay += 100;
    endforeach; 
    ?>
</div>