<?php
// /components/search-box.php - Komponen Kotak Pencarian
$placeholder = $appConfig['ui']['searchPlaceholder'] ?? 'Cari materi pembelajaran...';
?>

<!-- Search Box Component -->
<div class="search-container position-relative mb-4" data-aos="fade-up" data-aos-delay="200">
    <div class="input-group input-group-lg">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" 
               class="form-control border-start-0 rounded-end-pill shadow-sm" 
               id="searchInput" 
               placeholder="<?php echo e($placeholder); ?>"
               autocomplete="off"
               aria-label="Pencarian materi"
               aria-describedby="searchHelp">
        <button class="btn btn-outline-secondary rounded-pill ms-2" 
                type="button" 
                id="clearSearch"
                title="Bersihkan pencarian"
                style="display: none;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <!-- Search Help Text -->
    <div id="searchHelp" class="form-text mt-2">
        <i class="bi bi-lightbulb me-1"></i>
        Ketik kata kunci untuk mencari materi pembelajaran yang sesuai
    </div>
    
    <!-- Search Suggestions -->
    <div id="suggestions" 
         class="suggestions-container list-group position-absolute w-100 d-none mt-1 rounded-3 shadow-lg"
         style="z-index: 1000; max-height: 400px; overflow-y: auto;">
    </div>
    
    <!-- Quick Search Tags -->
    <div class="quick-tags mt-3" id="quickTags">
        <small class="text-muted me-2">Pencarian populer:</small>
        <div class="d-inline-flex flex-wrap gap-1">
            <button class="btn btn-sm btn-outline-secondary rounded-pill" 
                    type="button" 
                    onclick="quickSearch('bicara')">
                Bicara
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" 
                    type="button" 
                    onclick="quickSearch('retorika')">
                Retorika
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" 
                    type="button" 
                    onclick="quickSearch('presentasi')">
                Presentasi
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" 
                    type="button" 
                    onclick="quickSearch('komunikasi')">
                Komunikasi
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" 
                    type="button" 
                    onclick="quickSearch('debat')">
                Debat
            </button>
        </div>
    </div>
    
    <!-- Search Results Counter -->
    <div id="searchResults" class="search-results mt-2 d-none">
        <small class="text-muted">
            <i class="bi bi-funnel me-1"></i>
            Menampilkan <span id="resultCount">0</span> hasil untuk "<span id="searchQuery"></span>"
            <button class="btn btn-sm btn-link p-0 ms-2" onclick="clearSearch()">
                Tampilkan semua
            </button>
        </small>
    </div>
</div>

<style>
/* Custom styles untuk search component */
.search-container .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.suggestions-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

[data-bs-theme="dark"] .suggestions-container {
    background: rgba(33, 37, 41, 0.95);
    border-color: rgba(255, 255, 255, 0.1);
}

.suggestion-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: rgba(13, 110, 253, 0.1);
    transform: translateX(5px);
}

.suggestion-highlight {
    background-color: yellow;
    padding: 0 2px;
    border-radius: 2px;
}

[data-bs-theme="dark"] .suggestion-highlight {
    background-color: #ffc107;
    color: #000;
}

.quick-tags .btn {
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.quick-tags .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-loading {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
}

/* Animation untuk suggestions */
.suggestions-container.show {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .quick-tags {
        text-align: center;
    }
    
    .quick-tags .btn {
        font-size: 0.7rem;
        margin: 1px;
    }
}
</style>

<script>
// Search functionality will be handled by assets/js/search.js
// This script provides component-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearSearch');
    
    // Show/hide clear button based on input
    searchInput.addEventListener('input', function() {
        if (this.value.trim()) {
            clearButton.style.display = 'block';
        } else {
            clearButton.style.display = 'none';
        }
    });
    
    // Clear search functionality
    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        clearButton.style.display = 'none';
        clearSearch();
        searchInput.focus();
    });
    
    // Enter key handling
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const suggestions = document.getElementById('suggestions');
            const firstSuggestion = suggestions.querySelector('.suggestion-item');
            if (firstSuggestion) {
                firstSuggestion.click();
            }
        }
    });
});

// Quick search function
function quickSearch(query) {
    const searchInput = document.getElementById('searchInput');
    searchInput.value = query;
    searchInput.dispatchEvent(new Event('input'));
    searchInput.focus();
}
</script>