<?php
require_once __DIR__ . '/../includes/config.php';
session_unset();
session_destroy();
echo json_encode(['success'=>true]);
