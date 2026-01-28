<?php
session_start();
require_once '../koneksidb.php';

// Cek Role User
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header('Location: ../Login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update Profil (Nama & Email)
    if (isset($_POST['update_profile'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        
        if (empty($nama) || empty($email)) {
            $error_msg = "Nama dan Email tidak boleh kosong.";
        } else {
            // Cek email duplikat jika email berubah
            $stmt_check = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
            $stmt_check->bind_param("si", $email, $user_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $error_msg = "Email sudah digunakan oleh pengguna lain.";
            } else {
                $stmt_update = $conn->prepare("UPDATE user SET nama = ?, email = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $nama, $email, $user_id);
                if ($stmt_update->execute()) {
                    $_SESSION['nama'] = $nama; // Update session
                    $success_msg = "Profil berhasil diperbarui.";
                } else {
                    $error_msg = "Gagal memperbarui profil.";
                }
            }
        }
    }

    // 2. Ganti Password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = "Semua kolom password wajib diisi.";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "Konfirmasi password baru tidak cocok.";
        } elseif (strlen($new_password) < 6) {
            $error_msg = "Password baru minimal 6 karakter.";
        } else {
            // Ambil password lama dari DB
            $stmt_get_pass = $conn->prepare("SELECT password FROM user WHERE id = ?");
            $stmt_get_pass->bind_param("i", $user_id);
            $stmt_get_pass->execute();
            $res_pass = $stmt_get_pass->get_result();
            if ($row_pass = $res_pass->fetch_assoc()) {
                if (password_verify($current_password, $row_pass['password']) || $current_password === $row_pass['password']) {
                    // Hash password baru
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_update_pass = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
                    $stmt_update_pass->bind_param("si", $new_hash, $user_id);
                    if ($stmt_update_pass->execute()) {
                        $success_msg = "Password berhasil diubah.";
                    } else {
                        $error_msg = "Gagal mengubah password.";
                    }
                } else {
                    $error_msg = "Password saat ini salah.";
                }
            }
        }
    }
}

// Ambil Data User Terbaru
$stmt_user = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$nama_user = $user_data['nama'];
$user_divisi = $user_data['divisi'] ?? 'Karyawan';

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/home.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }
    body {
        background-color: #f0f2f5;
    }
    .profile-header-card {
        background: var(--primary-gradient);
        color: white;
        border-radius: 15px;
        position: relative;
        overflow: hidden;
    }
    .profile-header-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 250px;
        height: 250px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .avatar-upload {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    .avatar-upload img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,0.3);
    }
    @media (max-width: 768px) {
      .sidebar { display: none; }
    }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- SIDEBAR -->
  <aside class="sidebar d-none d-md-block shadow-sm">
    <div class="p-4 mb-3 border-bottom border-secondary">
        <h5 class="text-white m-0 fw-bold">SmartPresence</h5>
    </div>
    <ul class="nav flex-column px-2">
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Dashboard</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link active rounded" href="profil.php"><i class="bi bi-person-circle me-3"></i>Profil Saya</a>
      </li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="content flex-fill">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded-3 p-3">
      <div class="container-fluid">
        <button class="btn btn-light d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <h5 class="m-0 fw-bold text-dark">Profil Saya</h5>
            <small class="text-muted">Kelola informasi akun Anda</small>
        </div>
        
        <div class="ms-auto">
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent" type="button" data-bs-toggle="dropdown">
               <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nama_user); ?>&background=random" class="rounded-circle" width="38" height="38" alt="Profile">
               <div class="d-none d-sm-block text-start">
                   <div class="fw-semibold small"><?php echo htmlspecialchars($nama_user); ?></div>
                   <span class="badge bg-primary text-white rounded-pill" style="font-size: 11px;"><?php echo htmlspecialchars($user_divisi); ?></span>
               </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
              <li><a class="dropdown-item text-danger py-2" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid p-0">

        <!-- Modals for Alerts -->
        <?php if ($success_msg): ?>
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white border-0">
                        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Berhasil</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-0 fs-5"><?php echo $success_msg; ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-0 fs-5"><?php echo $error_msg; ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Profile Info Column -->
            <div class="col-lg-4">
                <div class="profile-header-card p-4 text-center shadow-sm mb-4">
                    <div class="avatar-upload mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nama_user); ?>&background=random&size=128" class="rounded-circle shadow" alt="Avatar">
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($nama_user); ?></h4>
                    <p class="opacity-75 mb-3"><?php echo htmlspecialchars($user_divisi); ?></p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge border border-white text-white bg-transparent"><i class="bi bi-building me-1"></i> Karyawan</span>
                        <span class="badge border border-white text-white bg-transparent"><i class="bi bi-geo-alt me-1"></i> Indonesia</span>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Informasi Kerja</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="bi bi-calendar-check me-2"></i>Bergabung</span>
                                <span class="fw-medium">01 Jan 2024</span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="bi bi-clock me-2"></i>Jam Masuk</span>
                                <span class="fw-medium"><?php echo substr($user_data['jam_masuk'] ?? '08:00', 0, 5); ?></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Jam Pulang</span>
                                <span class="fw-medium"><?php echo substr($user_data['jam_keluar'] ?? '17:00', 0, 5); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Edit Forms Column -->
            <div class="col-lg-8">
                <!-- Edit Profile Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold m-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Profil</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($nama_user); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" name="update_profile" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <h6 class="fw-bold m-0"><i class="bi bi-shield-lock me-2 text-primary"></i>Ganti Password</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Password Saat Ini</label>
                                    <input type="password" name="current_password" class="form-control" placeholder="Masukkan password lama" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Password Baru</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" name="change_password" class="btn btn-warning px-4 text-white">
                                        <i class="bi bi-key me-2"></i>Ubah Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
  </main>
</div>

<!-- Mobile Sidebar Offcanvas -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title text-white"><i class="bi bi-fingerprint me-2"></i>SmartPresence</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="p-3 text-center border-bottom border-secondary">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nama_user); ?>&background=random" class="rounded-circle mb-2" width="60" height="60">
        <h6 class="m-0"><?php echo htmlspecialchars($nama_user); ?></h6>
        <small class="text-white-50"><?php echo htmlspecialchars($user_divisi); ?></small>
    </div>
    <ul class="nav flex-column p-2">
      <li class="nav-item mb-1">
        <a class="nav-link text-white-50 rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Dashboard</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white-50 rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link active bg-primary text-white rounded" href="profil.php"><i class="bi bi-person-circle me-3"></i>Profil Saya</a>
      </li>
      <li class="nav-item mt-3 pt-3 border-top border-secondary">
        <a class="nav-link text-danger rounded" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
      </li>
    </ul>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show success modal if exists
        <?php if ($success_msg): ?>
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        <?php endif; ?>

        // Show error modal if exists
        <?php if ($error_msg): ?>
        var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
        <?php endif; ?>
    });
</script>
</body>
</html>
