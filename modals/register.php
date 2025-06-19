<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">
                    <i class="bi bi-person-plus text-success me-2"></i>
                    Daftar Akun Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm" method="POST" action="auth/register.php">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="registerNim" name="nim" placeholder="NIM" required pattern="[0-9]{8,12}" title="NIM harus berupa angka 8-12 digit">
                        <label for="registerNim">NIM</label>
                        <div class="form-text">Masukkan Nomor Induk Mahasiswa (8-12 digit)</div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="registerName" name="name" placeholder="Nama Lengkap" required minlength="3">
                        <label for="registerName">Nama Lengkap</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Email" required>
                        <label for="registerEmail">Email</label>
                        <div class="form-text">Gunakan email aktif untuk konfirmasi akun</div>
                    </div>
                    
                    <div class="alert alert-info border-0 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            Setelah mendaftar, Anda akan menerima email konfirmasi. 
                            Silakan cek inbox dan folder spam untuk mengaktifkan akun.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success" id="registerSubmit">
                            <i class="bi bi-person-plus me-1"></i>
                            <span class="submit-text">Daftar Sekarang</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Sudah punya akun? 
                            <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-decoration-none">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Handle register form submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('registerSubmit');
    const submitText = submitBtn.querySelector('.submit-text');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Mengirim...';
    spinner.classList.remove('d-none');
    
    // Prepare form data
    const formData = new FormData(this);
    
    // Send request
    fetch('auth/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message);
            
            // Close modal and reset form
            const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
            modal.hide();
            this.reset();
            
            // Optionally show login modal after successful registration
            setTimeout(() => {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            }, 500);
            
        } else {
            // Show error message
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitText.textContent = 'Daftar Sekarang';
        spinner.classList.add('d-none');
    });
});

// Helper function to show alerts
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || document.body;
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>