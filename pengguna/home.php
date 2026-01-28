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
$hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i:s');

// Ambil Data User (Divisi & Jadwal Pribadi)
$stmt_user = $conn->prepare("SELECT divisi, jam_masuk, jam_keluar FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user_data = $res_user->fetch_assoc();

$user_divisi = $user_data['divisi'] ?? 'Karyawan';
$jam_masuk_kantor = $user_data['jam_masuk'] ?? '08:00:00';
$jam_pulang_kantor = $user_data['jam_keluar'] ?? '17:00:00';

// Handle Presensi
$notif = '';
$notif_type = '';

// 1. Absen Masuk
if (isset($_POST['absen_masuk'])) {
    // Cek duplikasi
    $check_sql = "SELECT id FROM presensi WHERE user_id = ? AND tanggal = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $user_id, $hari_ini);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $notif = "Anda sudah melakukan presensi masuk hari ini.";
        $notif_type = "warning";
    } else {
        $status = ($jam_sekarang > $jam_masuk_kantor) ? 'Terlambat' : 'Tepat Waktu';
        $insert_sql = "INSERT INTO presensi (user_id, tanggal, jam_masuk, status) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("isss", $user_id, $hari_ini, $jam_sekarang, $status);
        
        if ($stmt_insert->execute()) {
            $notif = "Check-in berhasil! Selamat bekerja.";
            $notif_type = "success";
        } else {
            $notif = "Gagal melakukan presensi.";
            $notif_type = "danger";
        }
    }
}

// 2. Absen Keluar
if (isset($_POST['absen_keluar'])) {
    // Cek apakah sudah waktunya pulang
    if ($jam_sekarang < $jam_pulang_kantor) {
        $notif = "Belum waktunya pulang! Jadwal pulang Anda: " . substr($jam_pulang_kantor, 0, 5);
        $notif_type = "warning";
    } else {
        // Update jam_keluar where user_id & tanggal & jam_keluar IS NULL
        $update_sql = "UPDATE presensi SET jam_keluar = ? WHERE user_id = ? AND tanggal = ? AND jam_keluar IS NULL";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("sis", $jam_sekarang, $user_id, $hari_ini);
        
        if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
            $notif = "Check-out berhasil! Terima kasih atas kerja keras Anda hari ini.";
            $notif_type = "success";
        } else {
            $notif = "Gagal check-out atau Anda sudah check-out sebelumnya.";
            $notif_type = "danger";
        }
    }
}

// Ambil Data Presensi Hari Ini
$data_hari_ini = null;
$stmt_today = $conn->prepare("SELECT * FROM presensi WHERE user_id = ? AND tanggal = ?");
$stmt_today->bind_param("is", $user_id, $hari_ini);
$stmt_today->execute();
$res_today = $stmt_today->get_result();
if ($res_today->num_rows > 0) {
    $data_hari_ini = $res_today->fetch_assoc();
}

// Ambil Riwayat Presensi (5 Terakhir)
$riwayat = [];
$stmt_history = $conn->prepare("SELECT * FROM presensi WHERE user_id = ? ORDER BY tanggal DESC LIMIT 5");
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$res_history = $stmt_history->get_result();
while ($row = $res_history->fetch_assoc()) {
    $riwayat[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Karyawan - SmartPresence</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/home.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
      --glass-bg: rgba(255, 255, 255, 0.95);
    }
    body {
        background-color: #f0f2f5;
    }
    .hero-card {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 15px;
        position: relative;
        overflow: hidden;
    }
    .hero-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .action-btn {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 50px;
        padding: 15px 40px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .status-badge {
        font-size: 0.9rem;
        padding: 8px 15px;
        border-radius: 30px;
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
        <h5 class="text-white m-0 fw-bold"><i ></i>SmartPresence</h5>
    </div>
    <ul class="nav flex-column px-2">
      <li class="nav-item mb-1">
        <a class="nav-link active rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Dashboard</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
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
            <h5 class="m-0 fw-bold text-dark">Dashboard Karyawan</h5>
            <small class="text-muted"><?php echo date('l, d F Y'); ?></small>
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
        
        <?php if ($notif): ?>
            <div class="alert alert-<?php echo $notif_type; ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                <i class="bi <?php echo $notif_type == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                <?php echo $notif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Welcome Card & Clock -->
            <div class="col-12">
                <div class="hero-card p-4 p-md-5 shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <h2 class="fw-bold mb-2">Halo, <?php echo htmlspecialchars($nama_user); ?>! 👋</h2>
                            <p class="opacity-75 mb-0">Jangan lupa untuk melakukan presensi tepat waktu. Semangat bekerja!</p>
                        </div>
                        <div class="col-md-4 text-md-end text-center">
                            <div class="display-4 fw-bold font-monospace" id="liveClock">00:00:00</div>
                            <div class="small opacity-75">Waktu Server Saat Ini</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Card -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                        <div class="mb-4">
                            <div class="bg-light rounded-circle p-4 d-inline-block">
                                <i class="bi bi-fingerprint fs-1 text-primary"></i>
                            </div>
                        </div>
                        
                        <h4 class="fw-bold mb-3">Status Kehadiran</h4>
                        
                        <?php if (!$data_hari_ini): ?>
                            <!-- Belum Absen -->
                            <p class="text-muted mb-4">Anda belum melakukan check-in hari ini.<br>Jadwal Masuk: <strong><?php echo substr($jam_masuk_kantor, 0, 5); ?></strong></p>
                            <form method="POST">
                                <button type="submit" name="absen_masuk" class="btn btn-primary btn-lg action-btn shadow-lg w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> CHECK IN SEKARANG
                                </button>
                            </form>

                        <?php elseif ($data_hari_ini['jam_keluar'] === null): ?>
                            <!-- Sudah Absen Masuk, Belum Pulang -->
                            
                            <?php if ($data_hari_ini['status'] == 'Terlambat'): ?>
                                <div class="alert alert-warning border-0 shadow-sm mb-3 p-2 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                    <small class="fw-bold">Anda Terlambat Hari Ini!</small>
                                </div>
                                <div class="status-badge bg-warning bg-opacity-10 text-warning mb-3">
                                    <i class="bi bi-check-circle-fill me-1"></i> Masuk: <?php echo substr($data_hari_ini['jam_masuk'], 0, 5); ?> (Terlambat)
                                </div>
                            <?php else: ?>
                                <div class="status-badge bg-success bg-opacity-10 text-success mb-3">
                                    <i class="bi bi-check-circle-fill me-1"></i> Masuk: <?php echo substr($data_hari_ini['jam_masuk'], 0, 5); ?>
                                </div>
                            <?php endif; ?>

                            <p class="text-muted mb-4">Selamat bekerja! Jangan lupa check-out saat pulang.<br>Jadwal Pulang: <strong><?php echo substr($jam_pulang_kantor, 0, 5); ?></strong></p>
                            
                            <?php if ($jam_sekarang < $jam_pulang_kantor): ?>
                                <div class="alert alert-light border-warning text-warning d-flex align-items-center justify-content-center gap-2 mb-3">
                                    <i class="bi bi-lock-fill"></i> Belum Waktunya Pulang
                                </div>
                                <button class="btn btn-secondary btn-lg action-btn w-100" disabled>
                                    <i class="bi bi-lock-fill me-2"></i> CHECK OUT TERKUNCI
                                </button>
                            <?php else: ?>
                                <form method="POST">
                                    <button type="submit" name="absen_keluar" class="btn btn-danger btn-lg action-btn shadow-lg w-100" onclick="return confirm('Apakah Anda yakin ingin check-out sekarang?');">
                                        <i class="bi bi-box-arrow-right me-2"></i> CHECK OUT
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Sudah Selesai -->
                            <div class="d-flex gap-2 mb-4 justify-content-center">
                                <span class="status-badge bg-success bg-opacity-10 text-success border border-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk: <?php echo substr($data_hari_ini['jam_masuk'], 0, 5); ?>
                                </span>
                                <span class="status-badge bg-danger bg-opacity-10 text-danger border border-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i> Keluar: <?php echo substr($data_hari_ini['jam_keluar'], 0, 5); ?>
                                </span>
                            </div>
                            <h3 class="text-primary fw-bold mb-2">SELESAI</h3>
                            
                            <?php if ($data_hari_ini['status'] == 'Terlambat'): ?>
                                <div class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Status: Terlambat
                                </div>
                            <?php else: ?>
                                <div class="badge bg-success mb-3 px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> Status: Tepat Waktu
                                </div>
                            <?php endif; ?>

                            <p class="text-muted">Presensi hari ini telah lengkap.<br>Sampai jumpa besok!</p>
                            <button class="btn btn-secondary btn-lg action-btn w-100" disabled>
                                <i class="bi bi-check-all me-2"></i> PRESENSI SELESAI
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- Riwayat Card -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat 5 Hari Terakhir</h6>
                        <a href="#" class="text-decoration-none small fw-bold">Lihat Semua</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3 border-0">Tanggal</th>
                                        <th class="py-3 border-0 text-center">Masuk</th>
                                        <th class="py-3 border-0 text-center">Keluar</th>
                                        <th class="py-3 border-0 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($riwayat) > 0): ?>
                                        <?php foreach ($riwayat as $row): ?>
                                        <tr>
                                            <td class="ps-4 fw-medium"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                            <td class="text-center font-monospace text-primary"><?php echo substr($row['jam_masuk'], 0, 5); ?></td>
                                            <td class="text-center font-monospace text-danger">
                                                <?php echo $row['jam_keluar'] ? substr($row['jam_keluar'], 0, 5) : '-'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    if ($row['jam_keluar']) {
                                                        echo '<span class="badge bg-primary rounded-pill px-3">Selesai</span>';
                                                    } else {
                                                        $badgeClass = ($row['status'] == 'Tepat Waktu') ? 'bg-success' : 'bg-warning text-dark';
                                                        echo '<span class="badge '.$badgeClass.' rounded-pill px-3">'.$row['status'].'</span>';
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                                Belum ada riwayat presensi.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
        <a class="nav-link text-white active bg-primary rounded" href="home.php"><i class="bi bi-grid-fill me-3"></i>Dashboard</a>
      </li>
      <li class="nav-item mb-1">
        <a class="nav-link text-white-50 rounded" href="riwayat.php"><i class="bi bi-clock-history me-3"></i>Riwayat Absensi</a>
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
<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        // Force Timezone to Asia/Jakarta (WIB)
        const timeString = now.toLocaleTimeString('en-GB', { 
            hour12: false, 
            timeZone: 'Asia/Jakarta' 
        });
        document.getElementById('liveClock').innerText = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
</body>
</html>
