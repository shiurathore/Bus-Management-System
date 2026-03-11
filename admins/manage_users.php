<?php
include __DIR__ . '/../db.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: manage_users.php");
    exit;
}

$result = $conn->query("SELECT id, unique_id, name, email, phone, balance, created_at FROM users ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Manage Users</title>
  <link rel="stylesheet" href="../assets/css/style.css">
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

  <main class="main">
    <h1 class="title">Registered Passenger</h1>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Sr.</th>
            <th>Unique ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Balance (₹)</th>
            <th>Registered On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['unique_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= number_format($row['balance'], 2) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                  <a href="manage_users.php?delete=<?= $row['id'] ?>" 
                     onclick="return confirm('Are you sure you want to delete this user?');" 
                     class="btn btn-delete">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align:center;">No users found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
          </div>
          <footer class="footer">
    <p>&copy; <?= date("Y") ?> Bus Management System. All rights reserved.</p>
  </footer>
</body>
</html>
