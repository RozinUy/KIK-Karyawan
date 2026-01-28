<?php
session_start();
require_once '../koneksidb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../Login.php');
  exit;
}

$success_msg = '';
$error_msg = '';

// Handle Delete Presensi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM presensi WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_msg = "Data presensi berhasil dihapus. Pegawai dapat melakukan presensi ulang.";
    } else {
        $error_msg = "Gagal menghapus data presensi.";
    }
}

// Filter Logic
$filter_tgl = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$is_filter_bulan = !empty($filter_bulan);

$sql = "SELECT p.id as presensi_id, p.*, u.nama 
        FROM presensi p 
        JOIN user u ON p.user_id = u.id ";

if ($is_filter_bulan) {
    // Filter by Month (Format YYYY-MM)
    $sql .= "WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_bulan' ";
} else {
    // Filter by Date (Default Today)
    $sql .= "WHERE p.tanggal = '$filter_tgl' ";
}

$sql .= "ORDER BY p.jam_masuk DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Presensi - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/home.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }
    body { background-color: #f0f2f5; }
    
    .card {
        border: none;
        border-radius: 15px;
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
        <a class="nav-link active rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="Jadwal.php"><i class="bi bi-calendar-range-fill me-3"></i>Jadwal</a>
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
            <h5 class="m-0 fw-bold text-dark">Data Presensi</h5>
            <small class="text-muted">Pantau kehadiran pegawai secara real-time</small>
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

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Filter Tanggal (Harian)</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo $is_filter_bulan ? '' : $filter_tgl; ?>" <?php echo $is_filter_bulan ? 'disabled' : ''; ?> id="inputTanggal">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Atau Bulan</label>
                        <input type="month" name="bulan" class="form-control" value="<?php echo $filter_bulan; ?>" id="inputBulan">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-filter me-2"></i>Terapkan</button>
                        <a href="presensi.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="bi bi-table me-2"></i>
                        Data Presensi: <?php echo $is_filter_bulan ? date('F Y', strtotime($filter_bulan)) : date('d F Y', strtotime($filter_tgl)); ?>
                    </h6>
                    <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3">
                        Total: <?php echo $result->num_rows; ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0 text-secondary small text-uppercase fw-bold" width="5%">No</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold">Pegawai</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold">Tanggal</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center">Jam Masuk</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center">Jam Keluar</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center">Status</th>
                                <th class="py-3 border-0 text-secondary small text-uppercase fw-bold text-center" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nama']); ?></div>
                                    </td>
                                    <td class="text-muted"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td class="text-center font-monospace text-primary bg-primary-subtle rounded border border-primary-subtle mx-1">
                                        <?php echo substr($row['jam_masuk'], 0, 5); ?>
                                    </td>
                                    <td class="text-center font-monospace text-danger bg-danger-subtle rounded border border-danger-subtle mx-1">
                                        <?php echo $row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $badgeClass = ($row['status'] == 'Tepat Waktu') ? 'bg-success' : 'bg-warning text-dark';
                                            echo '<span class="badge '.$badgeClass.' rounded-pill px-3">'.$row['status'].'</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?delete=<?php echo $row['presensi_id']; ?>&tanggal=<?php echo $filter_tgl; ?>&bulan=<?php echo $filter_bulan; ?>" 
                                           class="btn btn-sm btn-light text-danger shadow-sm border" 
                                           onclick="return confirm('Hapus data presensi ini? Pegawai harus melakukan check-in ulang.')" 
                                           title="Hapus & Reset">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                        Tidak ada data presensi pada periode ini.
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
        <a class="nav-link active bg-primary text-white rounded" href="presensi.php"><i class="bi bi-calendar-check-fill me-3"></i>Presensi</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple logic to toggle disable state
    const inputTanggal = document.getElementById('inputTanggal');
    const inputBulan = document.getElementById('inputBulan');

    inputBulan.addEventListener('change', function() {
        if(this.value) {
            inputTanggal.value = '';
            inputTanggal.disabled = true;
        } else {
            inputTanggal.disabled = false;
        }
    });

    inputTanggal.addEventListener('change', function() {
        if(this.value) {
            inputBulan.value = '';
        }
    });
</script>
</body>
</html>