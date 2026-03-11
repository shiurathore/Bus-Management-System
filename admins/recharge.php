<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unique_id = trim($_POST['unique_id'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $admin_id = $_SESSION['admin_id']; // ✅ Logged-in admin

    if ($unique_id === '' || $amount <= 0) {
        $message = '❌ Invalid input.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE unique_id = ?");
        $stmt->bind_param("s", $unique_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $message = '❌ User not found.';
        } else {
            $user = $res->fetch_assoc();
            $uid = $user['id'];

            $conn->begin_transaction();
            try {
                // Update balance
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $uid);
                $stmt->execute();

                // Insert recharge log
                $stmt = $conn->prepare("INSERT INTO recharges (user_id, amount, admin_id) VALUES (?, ?, ?)");
                $stmt->bind_param("idi", $uid, $amount, $admin_id);
                $stmt->execute();

                $conn->commit();
                $message = '✅ Recharge successful.';
            } catch (Exception $e) {
                $conn->rollback();
                $message = '❌ Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recharge Balance</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
 
  </style>
</head>
<body>
  <div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <h2 class="logo">Bus Management</h2>
    <nav>
      <a href="admin_dashboard.php"> Dashboard</a>
      <a href="user_register.php"> Register New Passenger</a>
      <a href="recharge.php"> Recharge Card</a>
      <a href="manage_users.php"> View Passenger</a>
      <a href="logout.php" class="logout"> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="main-recharge">
    <h1 class="title">Recharge User Balance</h1>

    <?php if ($message): ?>
      <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="form">
      <input name="unique_id" placeholder="User Unique ID (e.g. USR...)" required><br>
      <input name="amount" type="number" step="0.01" placeholder="Amount" required><br>
      <button type="submit">Recharge</button>
    </form>

  </main>
    </div>
    <footer class="footer">
    <p>&copy; <?= date("Y") ?> Bus Management System. All rights reserved.</p>
  </footer>
</body>
</html>
