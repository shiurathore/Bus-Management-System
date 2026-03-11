<?php
include __DIR__ . '/../db.php';
$message = '';
$fare_per_km = 2; // Fare rate per km

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unique_id = trim($_POST['unique_id'] ?? '');

    if ($unique_id === '') {
        $message = "❌ Please enter or scan a valid User ID.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, balance FROM users WHERE unique_id = ?");
        $stmt->bind_param("s", $unique_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $message = "❌ User not found.";
        } else {
            $user = $res->fetch_assoc();
            $user_id = $user['id'];
            $balance = $user['balance'];

            // Check if there is an ongoing journey
            $stmt = $conn->prepare("SELECT id, start_time, start_lat, start_lng 
                                    FROM journeys WHERE user_id = ? AND status = 'ongoing'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $ongoing = $stmt->get_result();

            if ($ongoing->num_rows > 0) {
                // End journey
                $journey = $ongoing->fetch_assoc();
                $journey_id = $journey['id'];

                // Simulate end coordinates
                $end_lat = rand(260000, 270000) / 10000; 
                $end_lng = rand(750000, 760000) / 10000;

                // Calculate distance (Haversine)
                $R = 6371;
                $lat1 = deg2rad($journey['start_lat']);
                $lng1 = deg2rad($journey['start_lng']);
                $lat2 = deg2rad($end_lat);
                $lng2 = deg2rad($end_lng);
                $dlat = $lat2 - $lat1;
                $dlng = $lng2 - $lng1;
                $a = sin($dlat / 2) * sin($dlat / 2) +
                     cos($lat1) * cos($lat2) *
                     sin($dlng / 2) * sin($dlng / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $R * $c;

                // Calculate fare
                $fare = $distance * $fare_per_km;

                if ($balance < $fare) {
                    $message = "❌ Insufficient balance. Please recharge.";
                } else {
                    $new_balance = $balance - $fare;

                    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                    $stmt->bind_param("di", $new_balance, $user_id);
                    $stmt->execute();

                    $stmt = $conn->prepare("UPDATE journeys 
                        SET end_time = NOW(), end_lat = ?, end_lng = ?, distance_km = ?, fare = ?, status = 'completed' 
                        WHERE id = ?");
                    $stmt->bind_param("ddddi", $end_lat, $end_lng, $distance, $fare, $journey_id);
                    $stmt->execute();

                    $message = "✅ Journey completed. Distance: " . round($distance, 2) . 
                               " km | Fare: ₹" . round($fare, 2) . 
                               " | Remaining Balance: ₹" . round($new_balance, 2);
                }
            } else {
                // Start new journey
                $start_lat = rand(260000, 270000) / 10000;
                $start_lng = rand(750000, 760000) / 10000;

                $stmt = $conn->prepare("INSERT INTO journeys (user_id, start_time, start_lat, start_lng, status) 
                                        VALUES (?, NOW(), ?, ?, 'ongoing')");
                $stmt->bind_param("idd", $user_id, $start_lat, $start_lng);
                $stmt->execute();

                $message = "🚌 Journey started. Scan again to stop.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Journey</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    video { width: 400px; height: 300px; border: 1px solid #333; }
    #result { margin-top: 10px; font-weight: bold; }
    h1 {
      text-align: center;
      color: #2c3e50;
    }

    #php-message {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    #php-message:empty {
      display: none;
    }

    #php-message:before {
      content: "ℹ ";
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 20px;
    }

    input[type="text"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      outline: none;
      transition: 0.3s;
    }

    input[type="text"]:focus {
      border-color: #457B9D;
      box-shadow: 0px 0px 5px rgba(52,152,219,0.5);
    }

    button {
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #457B9D;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background: #457B9D;
    }

    h2 {
      margin-top: 20px;
      color: #34495e;
    }

    video {
      width: 100%;
      height: auto;
      border: 2px solid #457B9D;
      border-radius: 10px;
      margin-top: 10px;
    }

    #result {
      margin-top: 10px;
      font-weight: bold;
      color: #27ae60;
    }
  </style>
</head>
<body>
  <h1>Bus Management System</h1>

  <?php if ($message): ?>
    <div id="php-message" style="margin:10px 0; font-weight:bold;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" id="journeyForm">
    <label>User ID (scan QR or enter manually):</label><br>
    <input type="text" id="unique_id" name="unique_id" placeholder="Scan or enter ID">
    <button type="submit">Submit Journey</button>
  </form>

  <h2>Scan QR Code</h2>
  <video id="preview"></video>
  <div id="result"></div>

  <!-- Instascan JS -->
  <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
  <script>
    let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
    scanner.addListener('scan', function (content) {
        document.getElementById('unique_id').value = content;
        document.getElementById('result').innerHTML = "✅ QR Code: " + content;
        document.getElementById('journeyForm').submit(); // auto-submit to PHP
    });
    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            scanner.start(cameras[0]);
        } else {
            document.getElementById('result').innerHTML = "❌ No cameras found.";
        }
    }).catch(function (e) {
        document.getElementById('result').innerHTML = "❌ Camera init error: " + e;
    });
  </script>
</body>
</html>
