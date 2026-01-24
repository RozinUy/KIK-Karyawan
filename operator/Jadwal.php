<?php
session_start();
require_once '../koneksidb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../Login.php');
  exit;
}

// --- AUTO MIGRATION LOGIC (Add columns if not exist) ---
try {
    $check = $conn->query("SHOW COLUMNS FROM user LIKE 'divisi'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE user ADD COLUMN divisi VARCHAR(50) DEFAULT 'Staff' AFTER email");
    }
    $check = $conn->query("SHOW COLUMNS FROM user LIKE 'jam_masuk'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE user ADD COLUMN jam_masuk TIME DEFAULT '08:00:00' AFTER divisi");
    }
    $check = $conn->query("SHOW COLUMNS FROM user LIKE 'jam_keluar'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE user ADD COLUMN jam_keluar TIME DEFAULT '17:00:00' AFTER jam_masuk");
    }
} catch (Exception $e) {
    // Silent error or log
}

$success_msg = '';
$error_msg = '';

// Handle Bulk Update (Default Setting)
if (isset($_POST['bulk_update'])) {
    $jam_masuk = $_POST['default_masuk'];
    $jam_keluar = $_POST['default_keluar'];
    
    $stmt = $conn->prepare("UPDATE user SET jam_masuk = ?, jam_keluar = ?");
    $stmt->bind_param("ss", $jam_masuk, $jam_keluar);
    
    if ($stmt->execute()) {
        $success_msg = "Jadwal default berhasil diterapkan ke SEMUA pegawai.";
    } else {
        $error_msg = "Gagal memperbarui jadwal default.";
    }
}

// Handle Individual Update
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $divisi = trim($_POST['divisi']);
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];
    
    $stmt = $conn->prepare("UPDATE user SET divisi = ?, jam_masuk = ?, jam_keluar = ? WHERE id = ?");
    $stmt->bind_param("sssi", $divisi, $jam_masuk, $jam_keluar, $id);
    
    if ($stmt->execute()) {
        $success_msg = "Data pegawai berhasil diperbarui.";
    } else {
        $error_msg = "Gagal memperbarui data pegawai.";
    }
}

// Fetch Users
$result = $conn->query("SELECT * FROM user ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pengaturan Jadwal - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/home.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }
    body { background-color: #f0f2f5; }
    .card { border: none; border-radius: 15px; }
    @media (max-width: 768px) { .sidebar { display: none; } }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- SIDEBAR -->
  <aside class="sidebar d-none d-md-block shadow-sm">
    <div class="d-flex align-items-center p-4 mb-3 border-bottom border-secondary" style="height: 90px;">
        <i class="bi bi-shield-lock-fill fs-3 text-white me-3"></i>
        <h5 class="text-white m-0 fw-bold">AdminPanel</h5>
    </div>
    <ul class="nav flex-column px-2">
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Beranda</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link active rounded" href="Jadwal.php"><i class="bi bi-calendar-range-fill me-3"></i>Jadwal</a>
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
            <h5 class="m-0 fw-bold text-dark">Pengaturan Jadwal</h5>
            <small class="text-muted">Kelola jam kerja dan divisi pegawai</small>
        </div>
        <div class="ms-auto">
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent" type="button" data-bs-toggle="dropdown">
               <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                  <i class="bi bi-person-fill"></i>
               </div>
               <span class="d-none d-sm-inline fw-semibold">Administrator</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
              <li><a class="dropdown-item text-danger py-2" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid p-0">
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Default Settings Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-sliders me-2"></i>Atur Jadwal Default (Semua Pegawai)</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="" method="POST" class="row g-3 align-items-end" onsubmit="return confirm('PERINGATAN: Ini akan mengubah jadwal SEMUA pegawai menjadi jam yang dipilih. Lanjutkan?');">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small">Jam Masuk Default</label>
                        <input type="time" name="default_masuk" class="form-control" value="08:00" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small">Jam Keluar Default</label>
                        <input type="time" name="default_keluar" class="form-control" value="17:00" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="bulk_update" class="btn btn-primary w-100"><i class="bi bi-check-all me-2"></i>Terapkan ke Semua</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Employee List -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-people me-2"></i>Daftar Jadwal Pegawai</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0 text-secondary small text-uppercase fw-bold" width="5%">No</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold">Pegawai</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold">Divisi</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center">Jadwal Masuk</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center">Jadwal Keluar</th>
                                <th class="py-3 border-0 text-center text-secondary small text-uppercase fw-bold" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nama']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border border-secondary-subtle">
                                            <?php echo htmlspecialchars($row['divisi'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace text-primary">
                                        <?php echo substr($row['jam_masuk'] ?? '00:00:00', 0, 5); ?>
                                    </td>
                                    <td class="text-center font-monospace text-danger">
                                        <?php echo substr($row['jam_keluar'] ?? '00:00:00', 0, 5); ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light text-primary shadow-sm border edit-btn" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                                data-divisi="<?php echo htmlspecialchars($row['divisi'] ?? ''); ?>"
                                                data-masuk="<?php echo $row['jam_masuk']; ?>"
                                                data-keluar="<?php echo $row['jam_keluar']; ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data pegawai.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </main>
</div>

<!-- Mobile Sidebar -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
  <div class="offcanvas-header border-bottom border-secondary" style="height: 90px;">
    <div class="d-flex align-items-center">
        <i class="bi bi-shield-lock-fill fs-3 text-white me-3"></i>
        <h5 class="offcanvas-title text-white fw-bold">AdminPanel</h5>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="nav flex-column p-2">
      <li class="nav-item mb-1">
        <a class="nav-link text-white rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Beranda</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link active bg-primary text-white rounded" href="Jadwal.php"><i class="bi bi-calendar-range-fill me-3"></i>Jadwal</a>
      </li>
       <li class="nav-item mt-3 pt-3 border-top border-secondary">
        <a class="nav-link text-danger rounded" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
      </li>
    </ul>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Jadwal & Divisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST">
          <input type="hidden" name="id" id="edit_id">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Pegawai</label>
                <input type="text" id="edit_nama" class="form-control" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Divisi</label>
                <input type="text" name="divisi" id="edit_divisi" class="form-control" placeholder="Contoh: Staff IT, HRD, Marketing" required>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" id="edit_masuk" class="form-control" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Jam Keluar</label>
                    <input type="time" name="jam_keluar" id="edit_keluar" class="form-control" required>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="update_user" class="btn btn-primary">Simpan Perubahan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_divisi').value = this.getAttribute('data-divisi');
            document.getElementById('edit_masuk').value = this.getAttribute('data-masuk').substring(0, 5);
            document.getElementById('edit_keluar').value = this.getAttribute('data-keluar').substring(0, 5);
        });
    });
</script>
</body>
</html>