<?php
session_start();
include __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (strlen($username) < 3 || strlen($password) < 6) {
        die('Invalid input');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hash);
    if ($stmt->execute()) {
        echo "Admin created.";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<form method="post">
  <input name="username" placeholder="username" required>
  <input name="password" type="password" placeholder="password" required>
  <button>Create admin</button>
</form>
