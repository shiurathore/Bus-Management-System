<?php
// login.php
session_start();
include __DIR__ . '/../db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = 'Provide username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['last_activity'] = time(); // track session activity
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $err = 'Invalid credentials.';
            }
        } else {
            $err = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
  body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background-color: #F1FAEE;
  position: relative;
  overflow: hidden;
}

#login-page {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.8)),
    url('https://png.pngtree.com/thumb_back/fh260/background/20240630/pngtree-sitting-on-bus-public-transport-details-blue-passenger-seat-image_15830791.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
}

.login-container {
  background: rgba(255, 255, 255, 0.95);
  padding: 40px 30px;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 500px;
}

h1 {
  text-align: center;
  margin-bottom: 24px;
  font-size: 26px;
  color: #333;
}

form {
  display: flex;
  flex-direction: column;
}

input[type="text"],
input[type="password"] {
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 16px;
  transition: border 0.3s ease;
}

input[type="text"]:focus,
input[type="password"]:focus {
  border-color: #1D3557;
  outline: none;
}

button {
  background-color: #1D3557;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}

button:hover {
  background-color: #0f213a;
}

.error-message {
  background-color: #ffe5e5;
  color: #d8000c;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  font-size: 14px;
}

.footer {
  margin-top: 20px;
  text-align: center;
  font-size: 14px;
}

.footer a {
  color: #4a90e2;
  text-decoration: none;
}

.footer a:hover {
  text-decoration: underline;
}
  </style>
</head>
<body>
  <div id="login-page"></div>

  <div class="login-container">
    <h1>Admin Login</h1>

    <?php if ($err): ?>
      <div class="error-message"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <div class="footer">
      No admin? <a href="admin_register.php">Create one</a>
    </div>
  </div>

</body>
</html>
