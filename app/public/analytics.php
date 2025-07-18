<?php
require_once __DIR__ . '/../connection/init.php';
require_once 'header.php';

// Fetch Email Statistics
try {
    $emailStats = $db->query("
        SELECT DATE(sent_at) as date, COUNT(*) as total
        FROM sent_emails
        GROUP BY DATE(sent_at)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Email DB Error: " . $e->getMessage());
}

// Fetch WhatsApp Statistics
try {
    $whatsappStats = $db->query("
        SELECT DATE(sent_at) as date, SUM(recipients) as total
        FROM whatsapp_sends
        GROUP BY DATE(sent_at)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("WhatsApp DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Statistics - StackVerify</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --bg-color: #f6f8fb;
      --text-color: #333;
      --card-bg: rgba(255, 255, 255, 0.8);
      --card-shadow: rgba(0, 0, 0, 0.1);
      --button-bg: #4c8bf5;
      --button-text: white;
      --nav-icon-filter: none;
    }
    body.dark {
      --bg-color: #121212;
      --text-color: #f0f0f0;
      --card-bg: rgba(18, 18, 18, 0.8);
      --card-shadow: rgba(0, 0, 0, 0.6);
      --button-bg: #6a5acd;
      --button-text: white;
      --nav-icon-filter: invert(1);
    }
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg-color);
      color: var(--text-color);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      transition: background 0.3s, color 0.3s;
    }
    .header {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      box-shadow: 0 2px 10px var(--card-shadow);
    }

    .header .logo-section {
      display: flex;
      align-items: center;
    }

    .header img.logo {
      width: 35px;
      height: 35px;
      margin-right: 10px;
    }

    .header .app-name {
      font-weight: bold;
      font-size: 1.1em;
    }

    .header img.profile {
      width: 35px;
      height: 35px;
      border-radius: 50%;
    }

    .theme-toggle {
      margin-left: 10px;
      background: var(--button-bg);
      color: var(--button-text);
      border: none;
      border-radius: 8px;
      padding: 6px 10px;
      cursor: pointer;
    }
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
      border-top: 1px solid #ddd;
      box-shadow: 0 -2px 10px var(--card-shadow);
    }

    .bottom-nav button {
      background: none;
      border: none;
      font-size: 0.8em;
      display: flex;
      flex-direction: column;
      align-items: center;
      color: var(--text-color);
      cursor: pointer;
    }

    .bottom-nav button img {
      width: 25px;
      height: 25px;
      margin-bottom: 3px;
      filter: var(--nav-icon-filter);
    }
    .content {
      flex: 1;
      padding: 20px 15px 70px;
    }
    h3 { text-align: center; margin-bottom: 20px; }
    .chart-container {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 8px 20px var(--card-shadow);
      padding: 20px;
      margin-bottom: 30px;
    }
    canvas { max-width: 100%; }
  </style>
</head>
<body>

<?php require_once 'header.php'; ?>

<div class="content">
  <div class="chart-container">
    <h3>Bulk WhatsApp Send Statistics</h3>
    <canvas id="whatsappChart"></canvas>
  </div>

  <div class="chart-container">
    <h3>Bulk Email Send Statistics</h3>
    <canvas id="emailChart"></canvas>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const whatsappLabels = <?php echo json_encode(array_column($whatsappStats, 'date')); ?>;
  const whatsappData = <?php echo json_encode(array_map('intval', array_column($whatsappStats, 'total'))); ?>;

  const emailLabels = <?php echo json_encode(array_column($emailStats, 'date')); ?>;
  const emailData = <?php echo json_encode(array_map('intval', array_column($emailStats, 'total'))); ?>;

  renderChart('whatsappChart', whatsappLabels, whatsappData, 'WhatsApp Recipients');
  renderChart('emailChart', emailLabels, emailData, 'Email Sends');

  function renderChart(canvasId, labels, dataPoints, label) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: label,
          data: dataPoints,
          borderColor: '#4c8bf5',
          backgroundColor: 'rgba(76, 139, 245, 0.4)',
          fill: true,
          tension: 0.3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#4c8bf5',
          pointHoverRadius: 6,
          pointRadius: 4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: 'var(--text-color)' } },
          tooltip: { mode: 'index', intersect: false }
        },
        interaction: { mode: 'nearest', axis: 'x', intersect: false },
        scales: {
          x: { title: { display: true, text: 'Date', color: 'var(--text-color)' }, ticks: { color: 'var(--text-color)' } },
          y: { beginAtZero: true, title: { display: true, text: 'Count', color: 'var(--text-color)' }, ticks: { color: 'var(--text-color)' } }
        }
      }
    });
  }
</script>
</body>
</html>