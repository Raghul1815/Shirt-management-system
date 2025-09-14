<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

// Only admin allowed for CRUD
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error'=>'Forbidden']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'list';

if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM shirts ORDER BY brand_name, size");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
    exit;
}

if ($action === 'add') {
    $brand = $input['brand_name'] ?? '';
    $size = $input['size'] ?? '';
    $sleeve = $input['sleeve_type'] ?? '';
    $price = $input['price'] ?? 0;

    $stmt = $pdo->prepare("INSERT INTO shirts (brand_name, size, sleeve_type, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$brand, $size, $sleeve, $price]);
    echo json_encode(['success'=>true, 'shirt_id'=>$pdo->lastInsertId()]);
    exit;
}

if ($action === 'edit') {
    $id = (int)$input['shirt_id'];
    $stmt = $pdo->prepare("UPDATE shirts SET brand_name=?, size=?, sleeve_type=?, price=? WHERE shirt_id=?");
    $stmt->execute([$input['brand_name'], $input['size'], $input['sleeve_type'], $input['price'], $id]);
    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'delete') {
    $id = (int)$input['shirt_id'];
    $stmt = $pdo->prepare("DELETE FROM shirts WHERE shirt_id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'Invalid action']);
