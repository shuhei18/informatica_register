<?php
session_start();
$response = [];

if (isset($_SESSION['timeout'])) {
    $inactive = 1800; // セッションの有効期限
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        $response['session_active'] = false;
    } else {
        $_SESSION['timeout'] = time(); // セッションが有効ならタイムアウトをリセット
        $response['session_active'] = true;
    }
} else {
    $response['session_active'] = false;
}

echo json_encode($response);
?>