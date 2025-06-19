<?php
/**
 * includes/statistics.php - Statistics component
 */
?>

<?php if ($userId || $progressId): ?>
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