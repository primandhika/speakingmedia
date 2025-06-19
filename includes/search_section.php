<?php
/**
 * includes/search_section.php - Search component
 */
?>

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
                   placeholder="<?php echo e($appConfig['searchPlaceholder']); ?>"
                   autocomplete="off">
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