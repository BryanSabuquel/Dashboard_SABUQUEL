<?php
$host = 'localhost';
$db = 'email_verification';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$whereClause = "";
if ($filter === 'verified') {
  $whereClause = "WHERE email_verified_at IS NOT NULL";
} elseif ($filter === 'unverified') {
  $whereClause = "WHERE email_verified_at IS NULL";
}

$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$verifiedUsers = $conn->query("SELECT COUNT(*) AS count FROM users WHERE email_verified_at IS NOT NULL")->fetch_assoc()['count'];
$unverifiedUsers = $totalUsers - $verifiedUsers;

$monthly = $conn->query("SELECT DATE_FORMAT(email_verified_at, '%Y-%m') as month, COUNT(*) as count FROM users WHERE email_verified_at IS NOT NULL GROUP BY month ORDER BY month ASC");
$labels = [];
$data = [];
while ($row = $monthly->fetch_assoc()) {
  $labels[] = $row['month'];
  $data[] = $row['count'];
}

$tableData = $conn->query("SELECT id, name, email, email_verified_at FROM users $whereClause ORDER BY id DESC LIMIT 10");

$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <aside>
    <div>
      <h2>BRY.POGI</h2>
      <nav>
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="under_cons.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
          <i class="fas fa-users"></i> Users
        </a>
        <a href="under_cons.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>">
          <i class="fas fa-envelope-open-text"></i> Messages
        </a>
        <a href="under_cons.php" class="<?= $currentPage === 'analytics.php' ? 'active' : '' ?>">
          <i class="fas fa-chart-bar"></i> Analytics
        </a>
        <a href="under_cons.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
          <i class="fas fa-cog"></i> Settings
        </a>
        <a href="under_cons.php" class="<?= $currentPage === 'help.php' ? 'active' : '' ?>">
          <i class="fas fa-question-circle"></i> Help
        </a>
        <a href="../logout.php" class="logout">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </nav>
    </div>
  </aside>

  <div class="main">
    <div class="filters">
      <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> All Users
      </a>
      <a href="?filter=verified" class="<?= $filter === 'verified' ? 'active' : '' ?>">
        <i class="fas fa-check-circle"></i> Verified Users
      </a>
      <a href="?filter=unverified" class="<?= $filter === 'unverified' ? 'active' : '' ?>">
        <i class="fas fa-times-circle"></i> Unverified Users
      </a>
    </div>

    <div class="grid">
      <div class="card">
        <h3>User Metrics Overview</h3>
        <canvas id="summaryChart"></canvas>
      </div>
      <div class="card">
        <canvas id="verifyChart"></canvas>
      </div>
      <div class="card">
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>

    <br>
    <br>
    <hr style="height:5px; background: #333333;">
    <br>
    <div class="card">
      <h3>Recent Users</h3>
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Verified At</th></tr>
        </thead>
        <tbody>
          <?php while($row = $tableData->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo $row['name']; ?></td>
              <td><?php echo $row['email']; ?></td>
              <td><?php echo $row['email_verified_at'] ?: 'Not Verified'; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const summaryCtx = document.getElementById('summaryChart').getContext('2d');
    new Chart(summaryCtx, {
      type: 'bar',
      data: {
        labels: ['Total Users', 'Verified Users', 'Unverified Users'],
        datasets: [{
          label: 'User Count',
          data: [<?php echo $totalUsers; ?>, <?php echo $verifiedUsers; ?>, <?php echo $unverifiedUsers; ?>],
          backgroundColor: ['rgb(209, 0, 164)', 'rgb(24, 0, 203)', 'rgb(201, 0, 0)']
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    const verifyCtx = document.getElementById('verifyChart').getContext('2d');
    new Chart(verifyCtx, {
      type: 'doughnut',
      data: {
        labels: ['Verified', 'Unverified'],
        datasets: [{
          data: [<?php echo $verifiedUsers; ?>, <?php echo $unverifiedUsers; ?>],
          backgroundColor: ['rgb(24, 0, 203)', 'rgb(201, 0, 0)'],
        }]
      },
      options: { responsive: true }
    });

    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
          label: 'Verified Users Per Month',
          data: <?php echo json_encode($data); ?>,
          fill: true,
          backgroundColor: 'rgb(209, 0, 164)',
          borderColor: 'rgb(0, 0, 0)',
          tension: 0.3
        }]
      },
      options: { responsive: true }
    });
  </script>
</body>
</html>