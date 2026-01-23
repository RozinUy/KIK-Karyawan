<?php
session_start();
require_once '../koneksidb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../Login.php');
  exit;
}

// Konfigurasi Batas Waktu Terlambat
$jam_masuk_batas = '08:00:00';
$hari_ini = date('Y-m-d');

// 1. Hitung Total Pegawai
$sql_total = "SELECT COUNT(*) as total FROM user";
$res_total = $conn->query($sql_total);
$row_total = $res_total->fetch_assoc();
$total_pegawai = $row_total['total'] ?? 0;

// 2. Hitung Sudah Presensi Hari Ini
$sql_hadir = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$hari_ini'";
$res_hadir = $conn->query($sql_hadir);
$row_hadir = $res_hadir->fetch_assoc();
$sudah_presensi = $row_hadir['total'] ?? 0;

// 3. Hitung Belum Presensi
$belum_presensi = $total_pegawai - $sudah_presensi;
if ($belum_presensi < 0) $belum_presensi = 0;

// 4. Hitung Terlambat
$sql_telat = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$hari_ini' AND jam_masuk > '$jam_masuk_batas'";
$res_telat = $conn->query($sql_telat);
$row_telat = $res_telat->fetch_assoc();
$terlambat = $row_telat['total'] ?? 0;

// 5. Data Grafik 14 Hari Terakhir
$chart_labels = [];
$chart_data = [];

for ($i = 13; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    // Format label tanggal, misal "25 Jan"
    $chart_labels[] = date('d M', strtotime($tgl));
    
    $sql_chart = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$tgl'";
    $res_chart = $conn->query($sql_chart);
    $row_chart = $res_chart->fetch_assoc();
    $chart_data[] = $row_chart['total'] ?? 0;
}

// Convert PHP arrays ke JSON untuk digunakan di JavaScript
$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Presensi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- ChartJS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="../style/home.css">
  <style>
    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }
      .sidebar.show {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 1050;
        width: 250px;
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<div class="d-flex">

  <!-- SIDEBAR -->
  <aside class="sidebar d-none d-md-block" id="sidebar">
    <h5 class="text-white mb-4 ps-2">SMARTPRESENCE</h5>

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="home.php"><i class="bi bi-house me-2"></i> Beranda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="pegawai.php"><i class="bi bi-people me-2"></i> Pegawai</a>
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
        <span class="navbar-brand fw-bold fs-5 text-primary">BERANDA</span>
        
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

    <!-- CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card stat-card bg-success">
          <div class="card-body">
            <i class="bi bi-person-check"></i>
            <h6>Sudah Presensi</h6>
            <h3><?php echo $sudah_presensi; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-danger">
          <div class="card-body">
            <i class="bi bi-person-x"></i>
            <h6>Belum Presensi</h6>
            <h3><?php echo $belum_presensi; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-warning">
          <div class="card-body">
            <i class="bi bi-alarm"></i>
            <h6>Terlambat</h6>
            <h3><?php echo $terlambat; ?></h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-primary">
          <div class="card-body">
            <i class="bi bi-people"></i>
            <h6>Total Pegawai</h6>
            <h3><?php echo $total_pegawai; ?></h3>
          </div>
        </div>
      </div>
    </div>

    <!-- CHART -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">Grafik Kehadiran 14 Hari Terakhir</h6>
      </div>
      <div class="card-body">
        <canvas id="attendanceChart" style="max-height: 400px;"></canvas>
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
        <a class="nav-link text-white-50 active bg-primary text-white rounded" href="home.php"><i class="bi bi-house me-2"></i> Beranda</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white-50" href="pegawai.php"><i class="bi bi-people me-2"></i> Pegawai</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white-50" href="#"><i class="bi bi-calendar-check me-2"></i> Presensi</a>
      </li>
    </ul>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx = document.getElementById('attendanceChart');
// Ambil data dari PHP
const labels = <?php echo $json_labels; ?>;
const dataHadir = <?php echo $json_data; ?>;

new Chart(ctx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Hadir',
      data: dataHadir,
      borderWidth: 2,
      tension: 0.4,
      borderColor: '#0d6efd',
      backgroundColor: 'rgba(13, 110, 253, 0.1)',
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1
        }
      }
    }
  }
});
</script>

</body>
</html>
