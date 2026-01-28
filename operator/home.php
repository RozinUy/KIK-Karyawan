<?php
session_start();
require_once '../koneksidb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ../Login.php');
  exit;
}

// Konfigurasi Batas Waktu Terlambat (Sesuai Request: 09:00)
$jam_masuk_batas = '09:00:00';
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

// 5. Hitung Sudah Pulang (Selesai)
// Asumsi kolom jam_keluar ada. Jika belum, query ini mungkin error atau return 0 jika struktur beda.
// Untuk keamanan, kita gunakan try-catch atau silent error check, tapi di native PHP mysqli procedural agak tricky.
// Kita asumsi user sudah update DB.
$sudah_pulang = 0;
$sql_pulang = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$hari_ini' AND jam_keluar IS NOT NULL";
$res_pulang = $conn->query($sql_pulang);
if ($res_pulang) {
    $row_pulang = $res_pulang->fetch_assoc();
    $sudah_pulang = $row_pulang['total'] ?? 0;
}

// 6. Data Grafik 14 Hari Terakhir
$chart_labels = [];
$chart_data = [];

for ($i = 13; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($tgl));
    
    $sql_chart = "SELECT COUNT(*) as total FROM presensi WHERE tanggal = '$tgl'";
    $res_chart = $conn->query($sql_chart);
    $row_chart = $res_chart->fetch_assoc();
    $chart_data[] = $row_chart['total'] ?? 0;
}

$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../style/home.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    }
    body { background-color: #f0f2f5; }
    
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
        color: white;
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card .card-body { position: relative; z-index: 1; }
    .stat-card i {
        position: absolute;
        right: 15px;
        bottom: 15px;
        font-size: 3.5rem;
        opacity: 0.2;
    }
    
    .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); }
    .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); }
    .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); }
    .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); }
    .bg-gradient-danger { background: linear-gradient(45deg, #e74a3b, #be2617); }

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
        <a class="nav-link active rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Beranda</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
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

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded-3 p-3">
      <div class="container-fluid">
        <button class="btn btn-light d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <h5 class="m-0 fw-bold text-dark">Beranda Admin</h5>
            <small class="text-muted">Ringkasan Aktivitas Hari Ini</small>
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

    <!-- CARDS -->
    <div class="container-fluid p-0">
        <div class="row g-4 mb-4">
          <!-- Total Pegawai -->
          <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-primary h-100">
              <div class="card-body">
                <div class="text-uppercase small fw-bold mb-1 opacity-75">Total Pegawai</div>
                <div class="h2 mb-0 fw-bold"><?php echo $total_pegawai; ?></div>
                <i class="bi bi-people-fill"></i>
              </div>
            </div>
          </div>

          <!-- Hadir Hari Ini -->
          <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-success h-100">
              <div class="card-body">
                <div class="text-uppercase small fw-bold mb-1 opacity-75">Hadir Hari Ini</div>
                <div class="h2 mb-0 fw-bold"><?php echo $sudah_presensi; ?></div>
                <div class="small opacity-75 mt-2">
                    <i class=""></i> <?php echo $sudah_pulang; ?> Selesai Kerja
                </div>
                <i class="bi bi-check-circle-fill"></i>
              </div>
            </div>
          </div>

          <!-- Terlambat -->
          <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-warning h-100">
              <div class="card-body">
                <div class="text-uppercase small fw-bold mb-1 opacity-75">Terlambat (>09:00)</div>
                <div class="h2 mb-0 fw-bold"><?php echo $terlambat; ?></div>
                <i class="bi bi-alarm-fill"></i>
              </div>
            </div>
          </div>

          <!-- Belum Hadir -->
          <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-gradient-danger h-100">
              <div class="card-body">
                <div class="text-uppercase small fw-bold mb-1 opacity-75">Belum Hadir</div>
                <div class="h2 mb-0 fw-bold"><?php echo $belum_presensi; ?></div>
                <i class="bi bi-x-circle-fill"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- CHART -->
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-graph-up me-2"></i>Tren Kehadiran (14 Hari)</h6>
          </div>
          <div class="card-body px-4 pb-4">
            <canvas id="attendanceChart" style="max-height: 350px;"></canvas>
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
        <a class="nav-link text-white active bg-primary rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Beranda</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white rounded" href="pegawai.php"><i class="bi bi-people-fill me-3"></i>Pegawai</a>
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
