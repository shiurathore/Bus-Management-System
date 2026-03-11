<?php
include __DIR__ . '/../db.php';
require_once __DIR__ . '/../libs/phpqrcode/qrlib.php';
require_once __DIR__ . '/../libs/fpdf186/fpdf.php';

$message = '';
$qr = '';
$unique_id = '';
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $initial = floatval($_POST['initial_balance'] ?? 0);

    if ($name === '') {
        $message = '❌ Name required.';
    } else {
        // Duplicate validation
        if (!empty($phone)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $message = "❌ Phone number already registered.";
            }
        }

        if (!empty($email) && $message === '') {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $message = "❌ Email already registered.";
            }
        }

        // If no duplicates, insert new user
        if ($message === '') {
            $unique_id = 'USR' . strtoupper(bin2hex(random_bytes(4)));

            $stmt = $conn->prepare("INSERT INTO users (unique_id, name, email, phone, balance) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssd", $unique_id, $name, $email, $phone, $initial);

            if ($stmt->execute()) {
                // Create QR code folder
                $qr_folder = __DIR__ . '/../assets/qrcodes/';
                if (!file_exists($qr_folder)) {
                    mkdir($qr_folder, 0777, true);
                }

                // Generate QR code
                $qr_file = $qr_folder . $unique_id . ".png";
                QRcode::png($unique_id, $qr_file, QR_ECLEVEL_L, 5);

                $qr = "../assets/qrcodes/" . $unique_id . ".png";

                $message = "✅ User created successfully!<br> 
                            Unique ID: <b>$unique_id</b><br>
                            
                            <a href='generate_card_pdf.php?uid=$unique_id' target='_blank'>🎫 Export Card (PDF)</a>";
            } else {
                $message = "❌ Error: " . $conn->error;
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
  <title>Register User</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
/* ===========================
   REGISTER PASSENGER PAGE
=========================== */
.main-passenger {
    flex: 1;
    padding: 50px 60px;
    background-color: #f8fbfd;
    margin-left:5px;
}

.main-passenger .title {
    font-size: 2rem;
    color: #1D3557;
    margin-bottom: 30px;
    position: relative;
}

.main-passenger .title::after {
    content: "";
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 60px;
    height: 4px;
    border-radius: 2px;
}

/* TWO-COLUMN FORM */
.form {
    background: #ffffff;
    padding: 35px 30px;
    border-radius: 15px;
    max-width: 800px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.95rem;
    color: #333;
    margin-bottom: 6px;
    font-weight: 600;
}

.form-group input {
    padding: 12px 14px;
    border-radius: 8px;
    border: 1.5px solid #d0d7e1;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus {
    border-color: #457B9D;
    box-shadow: 0 0 0 3px rgba(69,123,157,0.2);
}

/* Submit Button spans both columns */
.form button {
    grid-column: span 2;
    background: #1D3557;
    color: #ffffff;
    border: none;
    padding: 14px;
    border-radius: 8px;
    font-size: 1.05rem;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

.form button:hover {
    background: #457B9D;
    transform: translateY(-2px);
}

/* Alert Message */
.alert {
    margin-top: 25px;
    background: #f1f5f9;
    border-left: 5px solid #1D3557;
    padding: 15px 20px;
    border-radius: 8px;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #333;
}

.alert b {
    color: #1D3557;
}

/* ===========================
   RESPONSIVE
=========================== */
@media (max-width: 768px) {

    .main,
    .main-passenger {
        padding: 30px 20px;
    }

    .main .title,
    .main-passenger .title {
        font-size: 1.7rem;
    }

    .cards {
        gap: 15px;
    }

    .form {
        grid-template-columns: 1fr;
        gap: 20px;
        max-width: 100%;
    }

    .form button {
        grid-column: span 1;
    }
}
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
    <main class="main-passenger">
      <div id="up">
        <h1 class="title">Register New Passenger</h1>

        <form method="post" class="form">
          <div class="form-group">
            <label for="name">Full Name *</label>
            <input id="name" name="name" placeholder="Enter full name" required>
          </div>

          <div class="form-group">
            <label for="email">Email (optional)</label>
            <input id="email" name="email" placeholder="Enter email">
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" placeholder="Enter phone number">
          </div>

          <div class="form-group">
            <label for="initial_balance">Initial Balance (optional)</label>
            <input id="initial_balance" name="initial_balance" type="number" step="0.01" placeholder="Enter initial balance">
          </div>

          <button type="submit">Register Passenger</button>
        </form>
        <div id="down">
          <?php if ($message): ?>
            <div class="alert">
              <?= $message ?>
              <?php if (!empty($qr)): ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
  </div>

      </div>
    </main>

  <footer class="footer">
    <p>&copy; <?= date("Y") ?> Bus Management System. All rights reserved.</p>
  </footer>
</body>
</html>
