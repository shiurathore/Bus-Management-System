<?php
session_start();
include __DIR__ . '/../db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$total_users = intval($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c']);
$total_balance = floatval($conn->query("SELECT IFNULL(SUM(balance),0) as s FROM users")->fetch_assoc()['s']);
$total_journeys = intval($conn->query("SELECT COUNT(*) as c FROM journeys")->fetch_assoc()['c']);
$today_revenue = floatval($conn->query("SELECT IFNULL(SUM(fare),0) as s FROM journeys WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['s']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <h2 class="logo">Bus Management</h2>
    <nav>
      <a href="admin_dashboard.php"> Dashboard</a>
      <a href="user_register.php">Register New Passenger</a>
      <a href="recharge.php"> Recharge Card</a>
      <a href="manage_users.php"> View Passenger</a>
      <a href="logout.php" class="logout"> Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main">
    <h1 class="title">Dashboard Overview</h1>

    <div class="cards">
      <div class="card blue">
        <div class="label">Total Users</div>
        <div class="value"><?=htmlspecialchars($total_users)?></div>
      </div>

       <div class="card yellow">
        <div class="label">Today's Revenue</div>
        <div class="value">&#8377; <?=number_format($today_revenue,2)?></div>
      </div>

      <div class="card green">
        <div class="label">Total Balance</div>
        <div class="value">&#8377; <?=number_format($total_balance,2)?></div>
      </div>

      <div class="card purple">
        <div class="label">Total Journeys</div>
        <div class="value"><?=htmlspecialchars($total_journeys)?></div>
      </div>

    </div>
  </main>
   </div>
  <footer class="footer">
    <p>&copy; <?= date("Y") ?> Bus Management System. All rights reserved.</p>
  </footer>
</body>
</html>
