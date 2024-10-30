<?php
session_start();  // セッションを開始してセッション情報を使います

// セッション情報が設定されていない場合のエラーハンドリング
if (!isset($_SESSION['selected_session'])) {
    echo "セッション情報が送信されていません。";
    exit;
}
$session = $_SESSION['selected_session'];  // セッション変数からセッション情報を取得

// セッション名に対応する表示用の名前を定義
$sessions = [
    'main' => 'メインセッション',
   'mini_a_1' => 'ミニセッションA-1<br>NTTデータ<br>【現場技術者が語る！<br>NTTデータのインフォマティカプロジェクトでのチャレンジ！】',
    'mini_a_2' => 'ミニセッションA-2<br>NTT Com<br>【「データの在処」と「人」をつなぐ<br>ICT基盤の現在地】',
    'mini_a_3' => 'ミニセッションA-3<br>ALSI_2<br>【未来を開拓する<br>データ流通・活用基盤「Xzilla」】',
    'mini_b_1' => 'ミニセッションB-1<br>SCSK<br>【DX成功のカギ！<br>データをスピーディーな意思決定に活かすには】',
    'mini_b_2' => 'ミニセッションB-2<br>CTC<br>【データ活用に悩む方必見！<br>スモールスタートで始めるデータ活用】'
];



// 表示用のセッション名を取得
$selectedSessionName = $sessions[$_SESSION['session']] ?? 'セッション未選択';


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRコードスキャン</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #f8f9fa;
    }

    .container {
        text-align: center;
    }

    video {
        width: 100%;
        max-width: 400px;
        border-radius: 10px;
        border: 2px solid #ced4da;
    }

    canvas {
        display: none;
    }

    #qr-result {
        margin-top: 20px;
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
    </style>
</head>

<body>
    <div class="container">

        <h2>QRコードスキャン</h2>

        <div class="button-container">
            <a href="main_reception.php" class="back-button">セッション選択に戻る</a>
        </div>

        <video id="video" autoplay></video>
        <canvas id="canvas" hidden></canvas>
        <p id="qr-result">スキャン中...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const resultDisplay = document.getElementById('qr-result');
    const session = "<?php echo htmlspecialchars($session, ENT_QUOTES, 'UTF-8'); ?>"; // セッション情報をJSに渡す

    // カメラにアクセスしてビデオを表示する
    navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'environment'
            }
        })
        .then(stream => {
            video.srcObject = stream;
            video.setAttribute('playsinline', true); // iOS向け設定
            video.play();
            requestAnimationFrame(tick);
        })
        .catch(err => {
            console.error('カメラにアクセスできませんでした: ', err);
            resultDisplay.textContent = 'カメラにアクセスできませんでした';
        });

    function tick() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
                resultDisplay.textContent = `QRコードが読み取れました: ${code.data}`;
                // QRコードの内容をサーバーに送信するか、次の処理に進む
                window.location.href =
                    `qr_process.php?data=${encodeURIComponent(code.data)}&session=${encodeURIComponent(session)}`;
            }
        }
        requestAnimationFrame(tick);
    }
    </script>
</body>

</html>