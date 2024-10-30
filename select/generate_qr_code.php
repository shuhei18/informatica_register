<?php
require 'vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (!isset($_POST['login_id'])) {
            throw new Exception('Login ID is missing');
        }

        $loginID = $_POST['login_id'];

        $qrCode = new QrCode($loginID);
        $qrCode->setSize(300);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);

        header('Content-Type: ' . $qrCode->getContentType());
        echo $qrCode->writeString();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
