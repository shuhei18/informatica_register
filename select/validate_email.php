<?php
require 'config.php';

$email = $_GET['email'];
$response = ['exists' => false];

if (!empty($email)) {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE work_email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $response['exists'] = true;
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>