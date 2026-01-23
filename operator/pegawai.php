<?php
session_start();
require_once '../koneksidb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../Login.php');
  exit;
}

// Handle CRUD Operations
$success_msg = '';
$error_msg = '';

// Add Employee
if (isset($_POST['add_employee'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error_msg = "Email sudah terdaftar!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (nama, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $email, $hash);
        if ($stmt->execute()) {
            $success_msg = "Pegawai berhasil ditambahkan.";
        } else {
            $error_msg = "Gagal menambahkan pegawai.";
        }
    }
}

// Edit Employee
if (isset($_POST['edit_employee'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    
    $sql = "UPDATE user SET nama = ?, email = ?";
    $params = [$nama, $email];
    $types = "ss";
    
    // Only update password if provided
    if (!empty($_POST['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $success_msg = "Data pegawai berhasil diperbarui.";
    } else {
        $error_msg = "Gagal memperbarui data pegawai.";
    }
}

// Delete Employee
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_msg = "Pegawai berhasil dihapus.";
    } else {
        $error_msg = "Gagal menghapus pegawai.";
    }
}

// Fetch Employees
$result = $conn->query("SELECT * FROM user ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Pegawai - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/home.css">
  <style>
    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }
    }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- SIDEBAR -->
  <aside class="sidebar d-none d-md-block">
    <h5 class="text-white mb-4 ps-2">SMARTPRESENCE</h5>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="home.php"><i class="bi bi-house me-2"></i> Beranda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="pegawai.php"><i class="bi bi-people me-2"></i> Pegawai</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-calendar-check me-2"></i> Presensi</a>
      </li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="content flex-fill bg-light min-vh-100">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded">
      <div class="container-fluid">
        <button class="btn btn-outline-primary d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list"></i>
        </button>
        <span class="navbar-brand fw-bold fs-5 text-primary">MANAJEMEN PEGAWAI</span>
        
        <div class="ms-auto">
          <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
               <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-person-fill"></i>
               </div>
               <span class="d-none d-sm-inline">Admin</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item text-danger" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid p-0">
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h6 class="m-0 fw-bold text-primary">Daftar Pegawai</h6>
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Pegawai
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0" width="5%">No</th>
                                <th class="py-3 border-0">Nama Lengkap</th>
                                <th class="py-3 border-0">Email</th>
                                <th class="py-3 border-0 text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light text-primary me-1 edit-btn shadow-sm border" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEmployeeModal"
                                                title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-light text-danger shadow-sm border" onclick="return confirm('Yakin ingin menghapus pegawai ini?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada data pegawai.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </main>
</div>

<!-- Mobile Sidebar Offcanvas -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title text-white">SMARTPRESENCE</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="nav flex-column p-2">
      <li class="nav-item mb-2">
        <a class="nav-link text-white-50" href="home.php"><i class="bi bi-house me-2"></i> Beranda</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white-50 active bg-primary text-white rounded" href="pegawai.php"><i class="bi bi-people me-2"></i> Pegawai</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white-50" href="#"><i class="bi bi-calendar-check me-2"></i> Presensi</a>
      </li>
    </ul>
  </div>
</div>


<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pegawai Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="add_employee" class="btn btn-primary">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Pegawai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST">
          <input type="hidden" name="id" id="edit_id">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="form-control" placeholder="******">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="edit_employee" class="btn btn-primary">Update</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle Edit Button Click
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
        });
    });
</script>
</body>
</html>