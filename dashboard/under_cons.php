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
  <style>
    .underCons{
      padding: 50px 150px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      background: #ededed3d;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.187);
      border-radius: 15px;
    }
    h1{
      font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
      font-size: 50px;
      color: rgb(45, 45, 45);
    }
    img{
      width: 250px;
      height: 250px;
      margin-bottom: 50px;
    }
    .leavebtn{
      text-decoration: none;
      font-size: 30px;
      color: rgb(255, 255, 255);
      background: #870000cf;
      padding: 5px 10px;
      border-radius: 5px;
    }
  </style>
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
        <a href="../login.php" class="logout">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </nav>
    </div>
  </aside>

  <div class="main">
    <div class="underCons">
      <h1>UNDER CONSTRUCTION</h1>

      <img src="assets/img/under_construction.png">

      <a class="leavebtn" href="dashboard.php">
        <i class="fas fa-sign-out-alt"></i> Leave
      </a>
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
          backgroundColor: ['#3f51b5', '#4CAF50', '#F44336']
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
          backgroundColor: ['#4CAF50', '#F44336'],
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
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          tension: 0.3
        }]
      },
      options: { responsive: true }
    });
  </script>
</body>
</html>