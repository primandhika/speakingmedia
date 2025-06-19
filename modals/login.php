<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">
                    <i class="bi bi-box-arrow-in-right text-primary me-2"></i>
                    Masuk ke Bicaranta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" method="POST" action="index.php">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="loginUserId" name="user_id" placeholder="NIM/ID Pengguna" required>
                        <label for="loginUserId">NIM/ID Pengguna</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
                        <label for="loginPassword">Password</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Belum punya akun? 
                            <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal" class="text-decoration-none">
                                Daftar di sini
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>