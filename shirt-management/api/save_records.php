<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

// only users (or admin acting as user) can save their records
$uid = $user['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$date = $input['date'] ?? date('Y-m-d');
$status = $input['status'] ?? 'Present'; // or Absent
$entries = $input['entries'] ?? []; // array of {shirt_id, count, sleeve_type(optional), size(optional)}

if (!is_array($entries)) $entries = [];

$pdo->beginTransaction();
try {
    // Option: store one row per shirt entry. If user marked Absent, set count=0 and status=Absent
    foreach ($entries as $e) {
        $shirt_id = (int)($e['shirt_id'] ?? 0);
        $count = (int)($e['count'] ?? 0);
        $st = $e['status'] ?? $status;

        // Upsert: if record exists for this user + shirt + date -> update; else insert
        $qry = "SELECT record_id FROM stitch_records WHERE user_id=? AND shirt_id=? AND date=?";
        $s = $pdo->prepare($qry);
        $s->execute([$uid, $shirt_id, $date]);
        $found = $s->fetchColumn();

        if ($found) {
            $upd = $pdo->prepare("UPDATE stitch_records SET count=?, status=? WHERE record_id=?");
            $upd->execute([$count, $st, $found]);
        } else {
            $ins = $pdo->prepare("INSERT INTO stitch_records (user_id, shirt_id, date, count, status) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$uid, $shirt_id, $date, $count, $st]);
        }
    }
    $pdo->commit();
    echo json_encode(['success'=>true]);
} catch (Exception $ex) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error'=>$ex->getMessage()]);
}
