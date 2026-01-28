<?php
session_start();
require_once '../koneksidb.php';

// Cek Role User
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header('Location: ../Login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'] ?? 'Pengguna';

// Ambil Divisi User
$stmt_user = $conn->prepare("SELECT divisi FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_divisi = $stmt_user->get_result()->fetch_assoc()['divisi'] ?? 'Karyawan';

// Filter Bulan & Tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query Riwayat Presensi
$sql = "SELECT * FROM presensi WHERE user_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();

// Statistik Bulanan
$hadir = 0;
$terlambat = 0;
while ($row = $result->fetch_assoc()) {
    $hadir++;
    if ($row['status'] == 'Terlambat') {
        $terlambat++;
    }
}
// Reset pointer result set untuk ditampilkan di tabel
$result->data_seek(0);

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Absensi - SmartPresence</title>
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
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
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
        <a class="nav-link active rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="profil.php"><i class="bi bi-person-circle me-3"></i>Profil Saya</a>
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
            <h5 class="m-0 fw-bold text-dark">Riwayat Absensi</h5>
            <small class="text-muted">Laporan kehadiran bulanan Anda</small>
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

        <!-- Filter & Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <form method="GET" class="row g-3 w-100 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small text-muted fw-bold">Bulan</label>
                                <select name="bulan" class="form-select">
                                    <?php foreach ($nama_bulan as $key => $val): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $key == $bulan ? 'selected' : ''; ?>>
                                            <?php echo $val; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Tahun</label>
                                <select name="tahun" class="form-select">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $y == $tahun ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-2"></i>Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="opacity-75 m-0">Total Kehadiran</h6>
                            <i class="bi bi-calendar-check fs-4 opacity-50"></i>
                        </div>
                        <h2 class="fw-bold mb-0"><?php echo $hadir; ?> <span class="fs-6 opacity-75 fw-normal">Hari</span></h2>
                        <div class="mt-3 pt-3 border-top border-white border-opacity-25 small">
                            <span class="opacity-75">Terlambat: </span>
                            <span class="fw-bold bg-white text-primary px-2 py-1 rounded-pill ms-1"><?php echo $terlambat; ?>x</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Tanggal</th>
                                <th class="py-3 border-0 text-center">Jam Masuk</th>
                                <th class="py-3 border-0 text-center">Jam Keluar</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="py-3 border-0 text-center">Durasi Kerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php 
                                        $tgl = date('d F Y', strtotime($row['tanggal']));
                                        $masuk = substr($row['jam_masuk'], 0, 5);
                                        $keluar = $row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-';
                                        
                                        // Hitung durasi
                                        $durasi = '-';
                                        if ($row['jam_keluar']) {
                                            $start = strtotime($row['jam_masuk']);
                                            $end = strtotime($row['jam_keluar']);
                                            $diff = $end - $start;
                                            $hours = floor($diff / 3600);
                                            $minutes = floor(($diff % 3600) / 60);
                                            $durasi = "{$hours}j {$minutes}m";
                                        }
                                    ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?php echo $tgl; ?></td>
                                    <td class="text-center font-monospace text-primary"><?php echo $masuk; ?></td>
                                    <td class="text-center font-monospace text-danger"><?php echo $keluar; ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $badgeClass = ($row['status'] == 'Tepat Waktu') ? 'bg-success' : 'bg-warning text-dark';
                                            echo '<span class="badge '.$badgeClass.' rounded-pill px-3">'.$row['status'].'</span>';
                                        ?>
                                    </td>
                                    <td class="text-center text-muted small"><?php echo $durasi; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                        Tidak ada data presensi pada bulan ini.
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
        <a class="nav-link active bg-primary text-white rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white-50 rounded" href="profil.php"><i class="bi bi-person-circle me-3"></i>Profil Saya</a>
      </li>
      <li class="nav-item mt-3 pt-3 border-top border-secondary">
        <a class="nav-link text-danger rounded" href="../Login.php?logout=1"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
      </li>
    </ul>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
