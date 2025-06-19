// Simple script.js - Focus on core functionality only

// Global variables
let basePath = '';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Detect base path
    const currentPath = window.location.pathname;
    basePath = currentPath.includes('/pages/') ? '../' : '';
    window.basePath = basePath;
    
    // Initialize core functions
    initializeTheme();
    initializeUserActions();
});

// ===== THEME MANAGEMENT =====
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const html = document.documentElement;

    if (!themeToggle || !themeIcon) {
        console.log('Theme toggle elements not found');
        return;
    }

    // Load saved theme
    const savedTheme = localStorage.getItem('bicaranta-theme') || 'light';
    console.log('Loading saved theme:', savedTheme);
    html.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggle.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Theme toggle clicked');
        
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        console.log('Switching from', currentTheme, 'to', newTheme);
        
        html.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('bicaranta-theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        console.log('Updating theme icon to:', theme);
        
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-moon-fill';
            themeToggle.classList.remove('btn-outline-secondary');
            themeToggle.classList.add('btn-outline-light');
        } else {
            themeIcon.className = 'bi bi-sun-fill';
            themeToggle.classList.remove('btn-outline-light');
            themeToggle.classList.add('btn-outline-secondary');
        }
    }
}

// ===== USER ACTIONS =====
function initializeUserActions() {
    // Handle clicks for logout and progress
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('onclick') === 'logout()') {
            e.preventDefault();
            logout();
        }
        
        if (e.target.getAttribute('onclick') === 'showProgress()') {
            e.preventDefault();
            showProgress();
        }
    });
}

function logout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = basePath + 'index.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'logout';
        
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function showProgress() {
    window.location.href = basePath + 'index.php#progress';
}

function goToMaterial(materialKey, subMaterial = null) {
    let url = basePath + 'index.php?material_key=' + encodeURIComponent(materialKey);
    
    if (subMaterial) {
        url += '&sub_material=' + encodeURIComponent(subMaterial);
    }
    
    window.location.href = url;
}

// ===== GLOBAL EXPORTS =====
window.logout = logout;
window.showProgress = showProgress;
window.goToMaterial = goToMaterial;