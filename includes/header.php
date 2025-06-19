<?php
$currentUser = getCurrentUser();
$userRole = getUserRole();
$progressId = $_SESSION['progress_id'] ?? null;

// Detect if we're in a subdirectory (pages folder)
$isInSubdirectory = strpos($_SERVER['REQUEST_URI'], '/pages/') !== false;
$basePath = $isInSubdirectory ? '../' : '';

// Make variables available for included files (like modals)
$GLOBALS['isInSubdirectory'] = $isInSubdirectory;
$GLOBALS['basePath'] = $basePath;
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appConfig['title']; ?></title>
    <link rel="icon" type="image/png" href="https://rbpmedia.id/assets/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
    <!-- Dark Mode Toggle Button (Bottom Right) -->
    <button class="btn btn-outline-secondary position-fixed" id="themeToggle" title="Toggle Theme" style="bottom: 20px; right: 20px; z-index: 9999; border-radius: 50%; width: 50px; height: 50px;">
        <i class="bi bi-sun-fill" id="themeIcon"></i>
    </button>

    <nav class="navbar navbar-expand-lg bg-body shadow-sm fixed-top" data-aos="fade-down">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $basePath; ?>index.php">
                <img src="https://rbpmedia.id/assets/logo.png" alt="Logo" height="40" id="navbarLogo">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>index.php">
                            <i class="bi bi-house me-1"></i>Beranda
                        </a>
                    </li>
                    
                    <?php if (isUserLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>index.php#materials">
                                <i class="bi bi-grid me-1"></i>Materi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>index.php#progress">
                                <i class="bi bi-graph-up me-1"></i>Progress
                            </a>
                        </li>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear me-1"></i>Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/users.php">Kelola Users</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/materials.php">Kelola Materi</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>admin/reports.php">Laporan</a></li>
                                </ul>
                            </li>
                        <?php elseif ($userRole === 'instructor'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-mortarboard me-1"></i>Pengajar
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>instructor/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>instructor/students.php">Siswa Saya</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $basePath; ?>instructor/materials.php">Materi Saya</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (isUserLoggedIn()): ?>
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <span class="d-none d-md-inline">Halo, <?php echo e($currentUser['name']); ?></span>
                                <span class="d-md-none">User</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-header">
                                        <div class="fw-bold"><?php echo e($currentUser['name']); ?></div>
                                        <small class="text-muted"><?php echo e($currentUser['email']); ?></small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-shield me-1"></i><?php echo ucfirst($userRole); ?>
                                        </small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $basePath; ?>profile.php">
                                        <i class="bi bi-person me-2"></i>Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="showProgress()">
                                        <i class="bi bi-graph-up me-2"></i>Progress Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $basePath; ?>settings.php">
                                        <i class="bi bi-gear me-2"></i>Pengaturan
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="logout()">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                <span class="d-none d-sm-inline">Masuk</span>
                            </button>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#registerModal">
                                <i class="bi bi-person-plus me-1"></i>
                                <span class="d-none d-sm-inline">Daftar</span>
                            </button>
                            <button class="btn btn-sm btn-outline-info <?php echo $progressId ? 'btn-info text-white' : ''; ?>" data-bs-toggle="modal" data-bs-target="#progressIdModal">
                                <i class="bi bi-graph-up me-1"></i>
                                <span class="d-none d-sm-inline"><?php echo $progressId ? 'ID: ' . substr($progressId, 0, 6) . '...' : 'Progress ID'; ?></span>
                                <span class="d-sm-none"><?php echo $progressId ? '✓' : 'ID'; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div id="alertContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9998; margin-top: 70px;"></div>

    <!-- Load JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?php echo $basePath; ?>assets/js/script.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>