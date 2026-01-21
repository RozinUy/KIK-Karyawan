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

  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="d-flex">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <h5 class="text-white mb-4">SMARTPRESENCE</h5>

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="#"><i class="bi bi-house"></i> Beranda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-people"></i> Pegawai</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-calendar-check"></i> Presensi</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-gear"></i> Pengaturan</a>
      </li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="content flex-fill">

    <!-- NAVBAR -->
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
      <div class="container-fluid">
        <input class="form-control w-50" placeholder="Cari sesuatu...">
        <div>
          <span class="me-3">SMARTPRESENCE TRIAL</span>
          <i class="bi bi-box-arrow-right"></i>
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
            <h3>0</h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-danger">
          <div class="card-body">
            <i class="bi bi-person-x"></i>
            <h6>Belum Presensi</h6>
            <h3>12</h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-warning">
          <div class="card-body">
            <i class="bi bi-alarm"></i>
            <h6>Terlambat</h6>
            <h3>0</h3>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card stat-card bg-primary">
          <div class="card-body">
            <i class="bi bi-people"></i>
            <h6>Total Pegawai</h6>
            <h3>53</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- CHART -->
    <div class="card">
      <div class="card-body">
        <h6>Grafik Kehadiran 14 Hari</h6>
        <canvas id="attendanceChart"></canvas>
      </div>
    </div>

  </main>
</div>

<script>
const ctx = document.getElementById('attendanceChart');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['Jan 25','Jan 26','Jan 27','Jan 28','Jan 29','Jan 30','Jan 31'],
    datasets: [{
      label: 'Hadir',
      data: [0, 0, 0, 0, 0, 0, 0],
      borderWidth: 2,
      tension: 0.4
    }]
  }
});
</script>

</body>
</html>
