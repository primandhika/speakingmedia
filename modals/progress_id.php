<!-- Progress ID Modal -->
<div class="modal fade" id="progressIdModal" tabindex="-1" aria-labelledby="progressIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressIdModalLabel">
                    <i class="bi bi-graph-up text-info me-2"></i>
                    Progress ID
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($progressId): ?>
                    <!-- Current Progress ID -->
                    <div class="alert alert-success border-0 mb-3">
                        <i class="bi bi-check-circle me-2"></i>
                        Progress ID aktif: <strong><?php echo e($progressId); ?></strong>
                    </div>
                    
                    <div class="text-center mb-3">
                        <button class="btn btn-outline-warning" onclick="clearProgressId()">
                            <i class="bi bi-trash me-1"></i>Hapus Progress ID
                        </button>
                    </div>
                    
                    <hr>
                    <h6 class="mb-3">Ganti Progress ID</h6>
                <?php endif; ?>
                
                <form id="progressIdForm">
                    <div class="form-floating mb-3">
                        <input type="text" 
                               class="form-control" 
                               id="progressIdInput" 
                               name="progress_id" 
                               placeholder="Progress ID" 
                               pattern="[a-zA-Z0-9_-]{3,20}"
                               title="3-20 karakter, huruf, angka, underscore, atau dash"
                               required>
                        <label for="progressIdInput">Progress ID</label>
                        <div class="form-text">3-20 karakter (huruf, angka, _, -)</div>
                    </div>
                    
                    <div class="alert alert-info border-0 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            Progress ID membantu Anda melacak kemajuan belajar tanpa perlu login. 
                            Gunakan ID yang mudah diingat seperti nama atau NIM Anda.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-save me-1"></i>Simpan Progress ID
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Atau <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">daftar akun</a> 
                        untuk fitur yang lebih lengkap
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle progress ID form
document.getElementById('progressIdForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'set_progress_id');
    formData.append('progress_id', document.getElementById('progressIdInput').value);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan');
    });
});

// Clear progress ID
function clearProgressId() {
    if (confirm('Hapus Progress ID? Data progress akan hilang.')) {
        const formData = new FormData();
        formData.append('action', 'clear_progress_id');
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert('info', 'Progress ID telah dihapus');
            setTimeout(() => location.reload(), 1000);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

// Helper function for alerts
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || document.body;
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>