<?php
require 'vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

// AWS SESクライアントの設定
$client = new SesClient([
    'version' => 'latest',
    'region'  => 'us-east-1', // SESが使用されるAWSリージョンを指定
    'credentials' => [
        'key'    => 'AKIAXF53I65F66G6TK6R',
        'secret' => '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',
    ],
]);

// メールの設定
$sender_email = 'iwt2024_registration@s-bev.jp';
$recipient_email = 'iwt2024_registration@s-bev.jp';
$subject = 'Test email with attachment';
$body_text = 'This is the body of the email';
$body_html = '<h1>This is the body of the email</h1>';

// PDFファイルの読み込みとエンコード
$file_path = 'test.pdf';
$file_content = file_get_contents($file_path);
$file_encoded = chunk_split(base64_encode($file_content));

// MIME境界の作成
$boundary = uniqid('np');

$raw_message = "From: $sender_email\r\n";
$raw_message .= "To: $recipient_email\r\n";
$raw_message .= "Subject: $subject\r\n";
$raw_message .= "MIME-Version: 1.0\r\n";
$raw_message .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";
$raw_message .= "--$boundary\r\n";
$raw_message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$raw_message .= "$body_text\r\n";
$raw_message .= "--$boundary\r\n";
$raw_message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$raw_message .= "$body_html\r\n";
$raw_message .= "--$boundary\r\n";
$raw_message .= "Content-Type: application/pdf; name=\"file.pdf\"\r\n";
$raw_message .= "Content-Disposition: attachment; filename=\"file.pdf\"\r\n";
$raw_message .= "Content-Transfer-Encoding: base64\r\n\r\n";
$raw_message .= "$file_encoded\r\n";
$raw_message .= "--$boundary--";

// SESメールのパラメータ
$params = [
    'RawMessage' => [
        'Data' => $raw_message,
    ],
];

// メールの送信
try {
    $result = $client->sendRawEmail($params);
    echo "Email sent! Message ID: " . $result['MessageId'] . "\n";
} catch (AwsException $e) {
    echo "The email was not sent. Error message: " . $e->getAwsErrorMessage() . "\n";
}


?>