<?php
session_start();
include __DIR__ . '/../db.php';

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $err = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $err = 'Passwords do not match.';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $err = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            if ($stmt->execute()) {
                $success = 'Admin registered successfully. <a href="login.php" class="text-blue-600">Login now</a>';
            } else {
                $err = 'Database error. Try again.';
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
  <title>Register Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-6 rounded shadow-md">
    <h1 class="text-2xl mb-4">Admin Registration</h1>
    <?php if ($err): ?>
      <div class="bg-red-100 text-red-800 p-2 mb-4 rounded"><?=htmlspecialchars($err)?></div>
    <?php elseif ($success): ?>
      <div class="bg-green-100 text-green-800 p-2 mb-4 rounded"><?= $success ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-3">
      <input name="username" placeholder="Username" class="w-full p-2 border rounded" required>
      <input name="password" type="password" placeholder="Password" class="w-full p-2 border rounded" required>
      <input name="confirm" type="password" placeholder="Confirm Password" class="w-full p-2 border rounded" required>
      <button class="w-full bg-blue-600 text-white p-2 rounded">Register</button>
    </form>
    <p class="mt-3 text-sm">Already have an account? <a href="login.php" class="text-blue-600">Login</a></p>
  </div>
</body>
</html>
