// /assets/js/script.js - JavaScript untuk Bicaranta dengan Login System

// Global variables
let currentUser = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializeSearch();
    initializeLogin();
    initializeUserActions();
    initializeIdValidation();
    
    // Check if user is logged in from PHP
    if (window.BicarantaConfig && window.BicarantaConfig.user) {
        currentUser = window.BicarantaConfig.user;
    }
});

// ===== THEME MANAGEMENT =====
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const navbarLogo = document.getElementById('navbarLogo');
    const html = document.documentElement;

    if (!themeToggle || !themeIcon) return;

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    updateThemeElements(savedTheme);

    themeToggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeElements(newTheme);
    });

    function updateThemeElements(theme) {
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-moon-fill';
            if (navbarLogo) navbarLogo.src = 'https://rbpmedia.id/assets/logo-light.png';
        } else {
            themeIcon.className = 'bi bi-sun-fill';
            if (navbarLogo) navbarLogo.src = 'https://rbpmedia.id/assets/logo.png';
        }
    }
}

// ===== SEARCH FUNCTIONALITY =====
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    
    if (!searchInput || !suggestionsBox) return;

    // Add input event listener
    searchInput.addEventListener('input', showSuggestions);
    searchInput.addEventListener('focus', showSuggestions);

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target !== searchInput && !suggestionsBox.contains(event.target)) {
            suggestionsBox.classList.add('d-none');
        }
    });
}

function showSuggestions() {
    const searchInput = document.getElementById("searchInput");
    const suggestionsBox = document.getElementById("suggestions");
    
    if (!searchInput || !suggestionsBox) return;
    
    const input = searchInput.value.toLowerCase();
    suggestionsBox.innerHTML = "";

    if (input.length > 0 && window.BicarantaConfig && window.BicarantaConfig.materials) {
        const filteredMaterials = window.BicarantaConfig.materials.filter(material => 
            material.name.toLowerCase().includes(input) ||
            (material.description && material.description.toLowerCase().includes(input))
        );
        
        if (filteredMaterials.length > 0) {
            suggestionsBox.classList.remove('d-none');
            filteredMaterials.forEach((material) => {
                const item = document.createElement("a");
                item.className = "list-group-item list-group-item-action";
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="${material.icon} me-3"></i>
                        <div class="flex-grow-1">
                            <div>${material.name}</div>
                            ${material.description ? `<small class="text-muted">${material.description}</small>` : ''}
                        </div>
                        ${material.clicks > 0 ? `<small class="text-muted">${material.clicks} klik</small>` : ''}
                    </div>
                `;
                item.onclick = () => {
                    if (currentUser) {
                        window.location.href = `index.php?material=${material.material_key}`;
                    } else {
                        showLoginModal();
                    }
                };
                suggestionsBox.appendChild(item);
            });
        } else {
            suggestionsBox.classList.add('d-none');
        }
    } else {
        suggestionsBox.classList.add('d-none');
    }
}

// ===== LOGIN SYSTEM =====
function initializeLogin() {
    // Login form submission if exists
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
}

function initializeUserActions() {
    // User dropdown actions
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
}

function showLoginModal() {
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        const modal = new bootstrap.Modal(loginModal);
        modal.show();
    }
}

function handleLogin(event) {
    event.preventDefault();
    // Add your login logic here
    // This would typically send data to server
    console.log('Login form submitted');
}

function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        // Clear current user
        currentUser = null;
        
        // Redirect to logout page or reload
        window.location.href = 'logout.php';
    }
}

// ===== ID VALIDATION =====
function initializeIdValidation() {
    const validateButton = document.getElementById('validateIdButton');
    const userIdInput = document.getElementById('userIdInput');
    const idAlert = document.getElementById('idAlert');
    const idModal = document.getElementById('idModal');
    
    if (validateButton && userIdInput) {
        validateButton.addEventListener('click', function() {
            const userId = userIdInput.value;
            const validId = '00029'; // Dari config
            
            if (userId.trim() === validId) {
                if (idAlert) idAlert.classList.add('d-none');
                
                // Success - tutup modal
                if (idModal) {
                    const modal = bootstrap.Modal.getInstance(idModal);
                    if (modal) modal.hide();
                }
                
                // Show success message
                showToast('ID berhasil divalidasi!', 'success');
            } else {
                if (idAlert) idAlert.classList.remove('d-none');
                
                // Shake animation
                userIdInput.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    userIdInput.style.animation = '';
                }, 500);
            }
        });

        // Allow Enter key to validate
        userIdInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                validateButton.click();
            }
        });
    }

    // Reset modal when closed
    if (idModal) {
        idModal.addEventListener('hidden.bs.modal', function() {
            if (idAlert) idAlert.classList.add('d-none');
            if (userIdInput) userIdInput.value = '';
        });
    }
}

// ===== UTILITY FUNCTIONS =====
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'primary'} border-0 position-fixed top-0 end-0 m-3" 
             role="alert" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.querySelector('.toast:last-child');
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove element after hide
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function showProgress(show = true) {
    const progressBar = document.getElementById('progressBar');
    if (progressBar) {
        if (show) {
            progressBar.classList.remove('d-none');
        } else {
            progressBar.classList.add('d-none');
        }
    }
}

// ===== CSS ANIMATIONS =====
// Add shake animation styles if not already present
if (!document.getElementById('shakeAnimation')) {
    const style = document.createElement('style');
    style.id = 'shakeAnimation';
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
}

// ===== GLOBAL FUNCTIONS =====
// Export functions to global scope for backwards compatibility
window.logout = logout;
window.showProgress = showProgress;
window.showSuggestions = showSuggestions;
window.showToast = showToast;
window.showLoginModal = showLoginModal;