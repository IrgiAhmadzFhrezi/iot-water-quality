const MAX_POINTS = 20;

// Fungsi untuk membuat grafik sensor
function createChart(ctx, label, color, yMin, yMax) {
  return new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: label,
          data: [],
          borderColor: color,
          backgroundColor: color + "33",
          tension: 0.3,
          fill: true,
          pointRadius: 2,
          pointHoverRadius: 4,
        },
      ],
    },
    options: {
      layout: {
        padding: 10,
      },
      scales: {
        x: {
          display: false,
        },
        y: {
          min: yMin,
          max: yMax,
          beginAtZero: true,
          ticks: {
            stepSize: (yMax - yMin) / 5,
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
      },
      responsive: true,
    },
  });
}

// Inisialisasi chart dan nilai awal
let phChart, turbidityChart, tdsChart;
let phValue = 0,
  turbidityValue = 0,
  tdsValue = 0;

$(document).ready(function () {
  // Buat grafik
  phChart = createChart(
    document.getElementById("phChart"),
    "pH",
    "#3a8dff",
    0,
    14
  );
  turbidityChart = createChart(
    document.getElementById("turbidityChart"),
    "Turbidity",
    "#28a745",
    0,
    100
  );
  tdsChart = createChart(
    document.getElementById("tdsChart"),
    "TDS",
    "#ffb347",
    0,
    1000
  );

  showSection("monitoring"); // Default tampilan

  // Ambil data dari server dan update setiap detik
  setInterval(() => {
    $.get("bacaph.php", function (data) {
      phValue = parseFloat(data);
      $("#bacaph").text(phValue.toFixed(2));
      updateChart(phChart, phValue);
      updateStatus();
    });

    $.get("bacakekeruhan.php", function (data) {
      turbidityValue = parseFloat(data);
      $("#bacakekeruhan").text(turbidityValue.toFixed(2));
      updateChart(turbidityChart, turbidityValue);
      updateStatus();
    });

    $.get("bacatds.php", function (data) {
      tdsValue = parseFloat(data);
      $("#bacatds").text(tdsValue.toFixed(2));
      updateChart(tdsChart, tdsValue);
      updateStatus();
    });
  }, 1000);

  // Saat modal dibuka, isi form tersembunyi
  $("#saveModal").on("shown.bs.modal", function () {
    $("#phForm").val(phValue);
    $("#tdsForm").val(tdsValue);
    $("#turbidityForm").val(turbidityValue);
    $("#statusAirForm").val($("#status-text").text());
  });

  // Simpan data dari form
  $("#saveForm").on("submit", function (e) {
    e.preventDefault();

    if ($("#namaUsaha").val() === "" || $("#kecamatan").val() === "") {
      alert("Mohon lengkapi nama usaha dan kecamatan.");
      return;
    }

    $.post("simpandata.php", $(this).serialize(), function () {
      alert("Data berhasil disimpan!");
      $("#saveModal").modal("hide");
    }).fail(function (xhr) {
      alert("Gagal menyimpan data. " + xhr.statusText);
    });
  });

  // Filter riwayat
  $("#btnFilter").on("click", function () {
    const nama = $("#filterNama").val();
    const kecamatan = $("#filterKecamatan").val();
    loadHistory(nama, kecamatan);
  });

  $("#history-section").on("show", () => loadHistory());
});

// Fungsi menambahkan data ke grafik
function updateChart(chart, value) {
  const time = new Date().toLocaleTimeString();
  if (chart.data.labels.length >= MAX_POINTS) {
    chart.data.labels.shift();
    chart.data.datasets[0].data.shift();
  }
  chart.data.labels.push(time);
  chart.data.datasets[0].data.push(value);
  chart.update();
}

// Fungsi update status semua
function updateStatus() {
  const ph = parseFloat($("#bacaph").text());
  const turb = parseFloat($("#bacakekeruhan").text());
  const tds = parseFloat($("#bacatds").text());

  let normalCount = 0;

  const isPhNormal = ph >= 6.5 && ph <= 8.5;
  const isTurbidityNormal = turb <= 10; // diperbarui agar sesuai label UI
  const isTdsNormal = tds <= 500;

  if (isPhNormal) normalCount++;
  if (isTurbidityNormal) normalCount++;
  if (isTdsNormal) normalCount++;

  // Status utama
  const statusText = $("#status-text");
  const statusCard = $("#status-card");

  if (normalCount === 3) {
    statusText.text("Layak Minum");
    statusCard.css("background", "linear-gradient(90deg, #00b09b, #96c93d)");
  } else if (normalCount === 0) {
    statusText.text("Tidak Layak");
    statusCard.css("background", "linear-gradient(90deg, #e52d27, #b31217)");
  } else {
    statusText.text("Kurang Layak");
    statusCard.css("background", "linear-gradient(90deg, #ffb347, #ffcc33)");
  }

  // Status pH
  const statusPH = $("#status-ph");
  if (ph < 6.5) {
    statusPH.text("Asam").attr("class", "badge bg-danger");
  } else if (ph <= 8.5) {
    statusPH.text("Netral").attr("class", "badge bg-success");
  } else {
    statusPH.text("Basa").attr("class", "badge bg-warning text-dark");
  }

  // Status Kekeruhan
  const statusTurb = $("#status-kekeruhan");
  if (turb <= 10) {
    statusTurb.text("Bersih").attr("class", "badge bg-success");
  } else if (turb <= 40) {
    statusTurb.text("Keruh").attr("class", "badge bg-warning text-dark");
  } else {
    statusTurb.text("Sangat Keruh").attr("class", "badge bg-danger");
  }

  // Status TDS
  const statusTDS = $("#status-tds");
  if (tds <= 500) {
    statusTDS.text("Aman").attr("class", "badge bg-success");
  } else {
    statusTDS.text("Berbahaya").attr("class", "badge bg-danger");
  }
}

// Fungsi untuk load riwayat data
function loadHistory(nama = "", kecamatan = "") {
  $("#tableHistory").html(
    "<tr><td colspan='7' class='text-center'>Memuat data...</td></tr>"
  );

  $.get("get_history.php", { nama, kecamatan }, function (data) {
    if (data.length === 0) {
      $("#tableHistory").html(
        "<tr><td colspan='7' class='text-center'>Data tidak ditemukan</td></tr>"
      );
      return;
    }

    let rows = "";
    data.forEach((d) => {
      rows += `
        <tr>
          <td>${d.tanggal}</td>
          <td>${d.nama_usaha}</td>
          <td>${d.kecamatan}</td>
          <td>${d.ph}</td>
          <td>${d.tds}</td>
          <td>${d.turbidity}</td>
          <td>${d.status_air}</td>
        </tr>
      `;
    });
    $("#tableHistory").html(rows);
  }).fail(() => {
    $("#tableHistory").html(
      "<tr><td colspan='7' class='text-danger text-center'>Gagal mengambil data.</td></tr>"
    );
  });
}

// Fungsi ganti tampilan section
function showSection(section) {
  $(".content-section").hide();
  $("#" + section + "-section").show();

  if (section === "history") loadHistory();

  $(".nav-link").removeClass("active");
  $(".nav-link").each(function () {
    if ($(this).attr("onclick").includes(section)) {
      $(this).addClass("active");
    }
  });
}
