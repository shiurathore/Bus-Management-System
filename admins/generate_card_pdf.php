<?php
include __DIR__ . '/../db.php';
require_once __DIR__ . '/../libs/fpdf186/fpdf.php'; 

if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    die("Invalid request. UID missing.");
}
$uid = $_GET['uid'];

$stmt = $conn->prepare("SELECT name, unique_id FROM users WHERE unique_id = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("User not found.");
}
$user = $res->fetch_assoc();
$name = $user['name'];
$unique_id = $user['unique_id'];

$qr_file = __DIR__ . '/../assets/qrcodes/' . $unique_id . '.png';
$bg_file = __DIR__ . '/../assets/card_template.png'; 


if (ob_get_length()) {
    @ob_end_clean();
}

// (ID card size 85 x 54 mm)
$pdf = new FPDF('L', 'mm', array(85, 54));
$pdf->SetAutoPageBreak(false);
$pdf->AddPage(); 


if (file_exists($bg_file)) {
    $pdf->Image($bg_file, 0, 0, 85, 54);
} else {
    $pdf->SetFillColor(255,255,255);
    $pdf->Rect(0,0,85,54,'F');
    $pdf->SetDrawColor(200,200,200);
    $pdf->Rect(2,2,81,50);
}


$pdf->SetTextColor(0,0,0); // balck

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(7, 6);
$pdf->Cell(60, 8, $name, 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(7, 15);
$pdf->Cell(60, 2, $unique_id, 0, 1, 'L');

// Place QR on right side if exists
if (file_exists($qr_file)) {
    // x=58mm, y=14mm, width=25mm (adjust if needed)
    $pdf->Image($qr_file, 58, 14, 25, 25);
}

// Output single page PDF to browser and stop
$pdf->Output('I', "Card_{$unique_id}.pdf");
exit;
