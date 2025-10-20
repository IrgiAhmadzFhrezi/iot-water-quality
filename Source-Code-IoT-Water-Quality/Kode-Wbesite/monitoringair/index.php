<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Water Quality Monitoring Dashboard</title>

  <!-- Bootstrap & Chart.js -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <!-- External CSS -->
  <link href="style.css" rel="stylesheet" />
  <!-- Gunakan CDN alternatif -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <script type="text/javascript" src="jquery/jquery.min.js"></script>
  
  <!-- Load otomatis/ realtime -->
  <script type="text/javascript">
    $(document).ready(function() {
      setInterval(function(){
        $("#bacaph").load("bacaph.php");
        $("#bacatds").load("bacatds.php");
        $("#bacakekeruhan").load("bacakekeruhan.php");
      }, 1000);
    });
  </script>

</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <a class="navbar-brand" href="#">Monitoring Air</a>
    <div class="navbar-nav ms-auto">
      <a href="#" class="nav-link active" onclick="showSection('monitoring'); return false;">Monitoring</a>
      <a href="#" class="nav-link" onclick="showSection('history'); return false;">History</a>
      <a href="#" class="nav-link" onclick="showSection('about'); return false;">About</a>
    </div>
  </nav>

  <div class="container mt-5">
    <!-- MONITORING SECTION -->
    <div id="monitoring-section" class="content-section">
      <h1 class="text-center mb-4" style="font-weight: 900; color: #004466;">
        <i class="fa-solid fa-droplet"></i> Water Quality Monitoring Dashboard
      </h1>

      <!-- Status Air -->
      <div class="row mb-4">
        <div class="col">
          <div id="status-card">
            <span id="status-text" class="text-secondary">
              <i class="fa-solid fa-spinner fa-spin"></i> Mengambil data...
            </span>
          </div>
        </div>
      </div>

      <!-- Sensor Cards -->
      <div class="row text-center g-4">
        <!-- pH -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-header ph">
              <i class="fa-solid fa-vial"></i> pH Level
            </div>
            <div class="card-body">
              <div class="value-display">
                <span id="bacaph">0</span>
                <i class="fa-solid fa-water"></i>
                <div>: <span id="status-ph" class="badge bg-secondary">-</span></div>
              </div>
              <p>Normal Range: 6.5 - 8.5</p>
              <canvas id="phChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Turbidity -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-header turbidity">
              <i class="fa-solid fa-cloud"></i> Turbidity
            </div>
            <div class="card-body">
              <div class="value-display">
                <span id="bacakekeruhan">0</span>
                <i class="fa-solid fa-droplet"></i>
                <div>: <span id="status-kekeruhan" class="badge bg-secondary">-</span></div>
              </div>
              <p>Normal Range: 0 - 100 Turbidity</p>
              <canvas id="turbidityChart"></canvas>
            </div>
          </div>
        </div>

        <!-- TDS -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-header tds">
              <i class="fa-solid fa-filter"></i> TDS
            </div>
            <div class="card-body">
              <div class="value-display">
                <span id="bacatds">0</span>
                <i class="fa-solid fa-flask"></i>
                <div>: <span id="status-tds" class="badge bg-secondary">-</span></div>
              </div>
              <p>Normal Range: 0 - 500 ppm</p>
              <canvas id="tdsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Tombol Simpan -->
      <div class="text-center mb-3 mt-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#saveModal">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Data
        </button>
      </div>
    </div>

    <!-- HISTORY SECTION -->
    <div id="history-section" class="content-section" style="display: none;">
  <h2 class="text-center my-5">
    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Data
  </h2>

  <!-- Filter Form -->
  <div class="row mb-3">
    <div class="col-md-5">
      <input type="text" id="filterNama" class="form-control" placeholder="Cari Nama Usaha">
    </div>
    <div class="col-md-5">
      <select id="filterKecamatan" class="form-select">
        <option value="">-- Semua Kecamatan --</option>
        <option>Tarakan Timur</option>
        <option>Tarakan Tengah</option>
        <option>Tarakan Barat</option>
        <option>Tarakan Utara</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100" id="btnFilter">Filter</button>
    </div>
  </div>

  <!-- Table -->
<div class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>Waktu</th>
        <th>Nama Usaha</th>
        <th>Kecamatan</th>
        <th>pH</th>
        <th>TDS</th>
        <th>NTU</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="tableHistory">
      <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<nav>
  <ul class="pagination justify-content-center" id="pagination"></ul>
</nav>


    <!-- ABOUT SECTION -->
    <div id="about-section" class="content-section" style="display: none;">
      <h2 class="text-center my-5"><i class="fa-solid fa-info-circle"></i> Tentang Aplikasi</h2>
      <p class="text-center">Sistem Monitoring Air Minum berbasis IoT dengan pH, TDS, dan Turbidity.</p>
    </div>
  </div>

  <!-- Modal Form Simpan Data -->
  <div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="saveForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="saveModalLabel">Simpan Data Sensor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="namaUsaha" class="form-label">Nama Usaha Depot</label>
            <input type="text" class="form-control" id="namaUsaha" name="nama_usaha" required>
          </div>
          <div class="mb-3">
            <label for="kecamatan" class="form-label">Kecamatan</label>
            <select class="form-select" id="kecamatan" name="kecamatan" required>
              <option value="">-- Pilih Kecamatan --</option>
              <option>Tarakan Timur</option>
              <option>Tarakan Tengah</option>
              <option>Tarakan Barat</option>
              <option>Tarakan Utara</option>
            </select>
          </div>
          <input type="hidden" id="phForm" name="ph">
          <input type="hidden" id="tdsForm" name="tds">
          <input type="hidden" id="turbidityForm" name="turbidity">
          <input type="hidden" id="statusAirForm" name="status_air">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <footer class="mt-5 text-center">
    &copy; 2025 Water Quality Monitoring by Irgi Ahmadz
  </footer>

  <!-- Bootstrap & Font Awesome JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <!-- External JS -->
  <script src="script.js"></script>
</body>
</html>
