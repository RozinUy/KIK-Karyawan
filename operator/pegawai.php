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
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }

    body {
      background-color: #f0f2f5;
    }

    /* Konsistensi Style Card seperti Home */
    .card {
      border: none;
      border-radius: 15px;
    }

    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }
    }
  </style>
</head>

<body>

  <div class="d-flex">
    <!-- SIDEBAR (Updated to match home.php) -->
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
          <a class="nav-link active rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link rounded" href="Jadwal.php"><i class="bi bi-calendar-range-fill me-3"></i>Jadwal</a>
        </li>
      </ul>
    </aside>

    <!-- MAIN -->
    <main class="content flex-fill">
      <!-- NAVBAR (Updated to match home.php) -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded-3 p-3">
        <div class="container-fluid">
          <button class="btn btn-light d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <i class="bi bi-list fs-5"></i>
          </button>
          <div>
            <h5 class="m-0 fw-bold text-dark">Manajemen Pegawai</h5>
            <small class="text-muted">Kelola data karyawan dan akses pengguna</small>
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

        <div class="card border-0 shadow-sm rounded-4">
          <!-- TOOLBAR ATAS TABEL -->
          <div class="card-header bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h6 class="m-0 fw-bold text-primary">
                <i class="bi bi-people-fill me-2"></i>Data Pegawai
              </h6>

              <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm">
                  <i class="bi bi-plus-lg"></i> Create
                </button>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card-body">
            <div class="table-responsive">
              <table id="pegawaiTable" class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th width="5%">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Divisi</th>
                    <th class="text-center" width="18%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $no = 1;
                  while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($row['nama']) ?></td>
                      <td><?= htmlspecialchars($row['email']) ?></td>
                      <td><?= $row['divisi'] ?? '-' ?></td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-primary">Update</button>
                        <a href="?delete=<?= $row['id'] ?>"
                          class="btn btn-sm btn-danger"
                          onclick="return confirm('Yakin hapus data?')">
                          Delete
                        </a>
                        <button class="btn btn-sm btn-success">Detail</button>
                      </td>
                    </tr>
                  <?php endwhile; ?>
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
          <a class="nav-link active bg-primary text-white rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link text-white rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link text-white rounded" href="Jadwal.php"><i class="bi bi-calendar-range-fill me-3"></i>Jadwal</a>
        </li>
        <li class="nav-item mt-3 pt-3 border-top border-secondary">
          <a class="nav-link text-danger rounded" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
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