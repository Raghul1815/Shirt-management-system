<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

$uid = $user['user_id'];
$week_end = $_GET['week_end'] ?? date('Y-m-d'); // ensure it's Sunday in usage
// compute start date
$start = date('Y-m-d', strtotime("$week_end -6 days"));

$stmt = $pdo->prepare("
  SELECT s.brand_name, s.size, SUM(sr.count) AS total_count, SUM(sr.count * s.price) AS total_price
  FROM stitch_records sr
  JOIN shirts s ON sr.shirt_id = s.shirt_id
  WHERE sr.user_id = ? AND sr.date BETWEEN ? AND ?
  GROUP BY s.brand_name, s.size
");
$stmt->execute([$uid, $start, $week_end]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// totals
$stmt2 = $pdo->prepare("
  SELECT SUM(sr.count) AS total_shirts, SUM(sr.count * s.price) AS total_salary
  FROM stitch_records sr
  JOIN shirts s ON sr.shirt_id = s.shirt_id
  WHERE sr.user_id = ? AND sr.date BETWEEN ? AND ?
");
$stmt2->execute([$uid, $start, $week_end]);
$totals = $stmt2->fetch(PDO::FETCH_ASSOC);

echo json_encode(['rows'=>$rows, 'totals'=>$totals, 'start'=>$start, 'end'=>$week_end]);
