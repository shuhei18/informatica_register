<?php
require_once 'config.php';
require_once 'phpqrcode/qrlib.php';
require_once 'tcpdf/tcpdf.php'; // TCPDFライブラリを読み込む

class CustomPDF extends TCPDF {
    // ヘッダーを無効化
    public function Header() {}

    // フッターを無効化 
    public function Footer() {}
}

use TCPDF;

// フォームが送信されたかチェック
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ログインIDをPOSTで取得
    $loginID = $_POST['loginID'];

    // データベース接続
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザー情報取得
    $stmt = $pdo->prepare("SELECT surname, given_name, company_name, corporate_type, corporate_prefix FROM users WHERE loginID = :loginID");
    $stmt->execute(['loginID' => $loginID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "ログインIDが見つかりませんでした。";
        exit;
    }

    // corporate_type を company_name に結合
    $company = trim($user['corporate_prefix']) === '前' 
        ? trim($user['corporate_type']) . trim($user['company_name']) 
        : (trim($user['corporate_prefix']) === '後' 
            ? trim($user['company_name']) . trim($user['corporate_type']) 
            : trim($user['company_name']));

    // 講演情報取得
    $stmt = $pdo->prepare("SELECT lecture_time, lecture_title FROM user_lectures WHERE loginID = :loginID");
    $stmt->execute(['loginID' => $loginID]);
    $lectures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // PDF生成
    $pdf = new CustomPDF();
    $pdf->AddPage();

    // メイリオフォントを設定
    $pdf->SetFont('meiryo01', '', 10);

    // 四分割の線を引く
    $pdf->Line(105, 0, 105, 297); // 縦線
    $pdf->Line(0, 148.5, 210, 148.5); // 横線

    // QRコード生成
    $qrCodeFile = '/tmp/qrcode.png';
    QRcode::png($loginID, $qrCodeFile);

    // 左上のブロック
    $pdf->SetXY(0, 0);
    $pdf->Image($qrCodeFile, 32.5, 25, 40);
    $pdf->SetFont("", '', 10);
    $pdf->MultiCell(85, 0, htmlspecialchars($loginID, ENT_QUOTES, 'UTF-8'), 0, 'C', false, 0,  10, 61);

// 最大文字サイズを30に設定
$maxFontSize = 30;

// PDFの幅を取得 (余白を考慮)
$maxWidth = 89;

// フォントを設定
$pdf->SetFont("meiryob01", '', $maxFontSize);

// 現在のフォントサイズでの文字幅を計算
$currentWidth = $pdf->GetStringWidth($company);

// 文字が枠内に収まるようにフォントサイズを調整
if ($currentWidth > $maxWidth) {
    $newFontSize = ($maxWidth / $currentWidth) * $maxFontSize;
    $pdf->SetFont("meiryob01", '', $newFontSize);
}

$xPosition = (105 - $maxWidth) / 2;

// テキストを描画 (Cellメソッドを使用)
$pdf->SetXY($xPosition, 90);
$pdf->Cell($maxWidth, 0, $company, 0, 0, 'C');


// 氏名を追加
$maxWidth = 85; // 収めたい幅
$fontSize = 30; // 初期フォントサイズ
$pdf->SetFont("meiryob01", '', $fontSize);

// 氏名を取得
$name = htmlspecialchars($user['surname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($user['given_name'], ENT_QUOTES, 'UTF-8');

// 氏名の文字幅を測定
$nameWidth = $pdf->GetStringWidth($name);

// 氏名が指定の幅を超える場合、フォントサイズを縮小
while ($nameWidth > $maxWidth && $fontSize > 1) {
    $fontSize--; // フォントサイズを1ずつ縮小
    $pdf->SetFont("meiryob01", '', $fontSize);
    $nameWidth = $pdf->GetStringWidth($name);
}

// 氏名を描画
$pdf->MultiCell($maxWidth, 0, $name, 0, "C", false, 0, 10, 110);

              // 右上のブロック
              $pdf->SetFont('meiryo01');
              $pdf->SetXY(110, 5);
          
              // ご登録セッション
              $pdf->SetFont('meiryob01', '', 14);
              $pdf->MultiCell(110, 0, '【ご登録セッション】', 0, 'L', false,);
              $pdf->Ln(4); // 行の余白
          
// 各セッションの内容を表示
$pdf->SetFont('meiryo01', '', 9);
foreach ($lectures as $lecture) {
    // セッション情報 時間 (背景色を薄い灰色に設定)
    $pdf->SetFillColor(230, 230, 230); // 薄い灰色
    $pdf->SetX(110);
    $pdf->MultiCell(95, 6, $lecture['lecture_time'], 1, 'L', true, 0, '', '', true, 0, false, true, 6, 'M', false);
    $pdf->Ln();
    
    // セッション情報 セッション名（行数に応じてセルの高さを設定）
    $pdf->SetX(110);
$pdf->SetFont('meiryo01', '', 9);

    // 特定の文字列が含まれているかをチェックし、対応するテキストを表示
    if (strpos($lecture['lecture_title'], 'データが切り拓く生成') !== false) {
        $lectureTitle = "【データが切り拓く生成 AIの未来" . PHP_EOL . "　～Everybody’s ready for AI except your data～】" . PHP_EOL . "【データ連係基盤の統合／拡大、データマネジメントへ】";
    } elseif (strpos($lecture['lecture_title'], 'ビジネスに革新をもたらす') !== false) {
        $lectureTitle = "【ビジネスに革新をもたらす生成AIと" . PHP_EOL . "　モダン・データマネジメント】";
    } elseif (strpos($lecture['lecture_title'], 'データ活用の課題') !== false) {
        $lectureTitle = "【データ活用の課題と最新動向｜" . PHP_EOL . "　現場で広げるデータの利活用とは】";
    } elseif (strpos($lecture['lecture_title'], 'データ戦略を支える') !== false) {
        $lectureTitle = "【データ戦略を支える" . PHP_EOL . "　インフォマティカ・プラットフォームの全体像" . PHP_EOL . "　〜ETL/ELTからマスタデータ管理、データガバナンスまで〜】";
    } elseif (strpos($lecture['lecture_title'], 'Snowflake, Databricks,') !== false) {
        $lectureTitle = "【Snowflake, Databricks, AWS, Microsoft, GCP…" . PHP_EOL . "　マルチクラウドで創る最強データプラットフォーム】";
    } elseif (strpos($lecture['lecture_title'], 'SUBARU流') !== false) {
        $lectureTitle = "【SUBARU流 全社データ活用で笑顔を作る" . PHP_EOL . "　「モノづくり革新」と「価値づくり」】";
    } elseif (strpos($lecture['lecture_title'], '現場技術者が語る！') !== false) {
        $lectureTitle = "【現場技術者が語る！NTTデータの" . PHP_EOL . "　インフォマティカプロジェクトでのチャレンジ！】";
    } elseif (strpos($lecture['lecture_title'], 'DX成功のカギ！') !== false) {
        $lectureTitle = "【DX成功のカギ！" . PHP_EOL . "　データをスピーディーな意思決定に活かすには】";
    } elseif (strpos($lecture['lecture_title'], 'データ活用に悩む方') !== false) {
        $lectureTitle = "【データ活用に悩む方必見！" . PHP_EOL . "　スモールスタートで始めるデータ活用】";
    } else {
        // 元のセッション名を表示
        $lectureTitle = $lecture['lecture_title'];
    }


    // 改行を調整して行数を計算
    $formattedTitle = trim(preg_replace('/\s+/', ' ', $lectureTitle));

    // テキストの行数を計算
    $lineCount = $pdf->getNumLines($formattedTitle, 95);
    
    // 行数に応じてセルの高さを設定
    if ($lineCount == 1) {
        $cellHeight = 7;
    } elseif ($lineCount == 2) {
        $cellHeight = 11;
    } else {
        $cellHeight = 15;
    }
    
    // MultiCell表示
    $pdf->MultiCell(95, $cellHeight, $lectureTitle, 1, 'L', false, 0, '', '', true, 0, false, true, $cellHeight, 'M', false);
    $pdf->Ln();
}



    // 左下のブロック
    $pdf->SetXY(5, 155);
    $pdf->SetFont('meiryob01', '', 14);
    $pdf->MultiCell(0, 0, '【開催日程】', '0', 'L');
    $pdf->Ln();
    $pdf->SetFont('meiryo01', '', 12);
    $pdf->MultiCell(0, 0, '2024年9月13日（金）13:30 – 18:00', '0', 'L');
    $pdf->MultiCell(0, 0, '受付開始：13:00～', '0', 'L');
    $pdf->MultiCell(0, 0, '展示会場：13:00～18:00', '0', 'L');

    $pdf->Ln(12);
    $pdf->SetFont('meiryob01', '', 14);
    $pdf->SetX(5);
    $pdf->MultiCell(0, 0, '【会場】', '0', 'L');
    $pdf->Ln();
    $pdf->SetFont('meiryo01', '', 12);
    $pdf->MultiCell(0, 0, '大手町プレイス ホール＆カンファレンス', '0', 'L');
    $pdf->MultiCell(0, 0, '東京都千代田区大手町2-3-1', '0', 'L');
    $pdf->MultiCell(0, 0, '大手町プレイス(イーストタワー) 1F/2F', '0', 'L');

    $pdf->Ln(12);
    $pdf->SetFont('meiryob01', '', 14);
    $pdf->SetX(5);
    $pdf->MultiCell(0, 0, '【ご来場に際して】', '0', 'L');
    $pdf->Ln();
    $pdf->SetFont('meiryo01', '', 12);
    $pdf->MultiCell(0, 0, "受講票をA4サイズ・100%で印刷し\n四つ折りにして会場へお持ちください。\n受付にてホルダーをお渡しいたしますので\n受講票をホルダーに入れてご入場ください。", '0', 'L');


    // 右下のブロック
    $pdf->SetXY(110, 155);
    $pdf->SetFont('meiryob01', '', 14);
    $pdf->MultiCell(0, 0, '【会場アクセス】', '0', 'L');
    $pdf->Image('img-cnct/map00.png', 120.5, 166, 75);
    $pdf->Ln();
    $pdf->SetXY(115, 248);
    $pdf->SetFont('meiryo01', '', 12);
    $pdf->MultiCell(0, 0, "東京メトロ\n丸の内線・東西線・千代田線・半蔵門線\n「大手町」駅A5出口直結\n", '0', 'L');
    $pdf->Ln(2);
   $pdf->SetX(115);
    $pdf->MultiCell(0, 0, "都営三田線\n「大手町」駅A5出口直結", '0', 'L');



    // PDFを表示
    $pdf->Output('受講票.pdf', 'I');

    // 一時ファイルの削除
    unlink($qrCodeFile);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインID入力</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 350px;
            width: 100%;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 10px;
            position: absolute;
            top: 0;
            right: 0;
            margin-right: 10px;
        }

        .header button {
            background-color: #cac2be;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .header button:hover {
            background-color: #f45b00;
        }

        /* iPhone向けのボタンスタイル */
        @media (max-width: 480px) {
            .header {
                flex-direction: row;
                justify-content: space-between;
                padding: 5px;
                gap: 5px;
            }

            .header button {
                padding: 8px 12px;
                font-size: 12px;
            }

            .container {
                padding: 15px;
                max-width: 300px;
            }

            input[type="text"] {
                padding: 8px;
            }

            input[type="submit"] {
                padding: 8px;
            }

            h1 {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <!-- メニューボタンとログアウトボタン -->
    <div class="header">
        <form method="post" action="adminMenu.php">
            <button type="submit">メニュー</button>
        </form>
        <form method="post" action="adminsLogin_iwt2024.php">
            <button type="submit" name="logout">ログアウト</button>
        </form>
    </div>

    <div class="container">
        <h1>ログインIDを入力してください</h1>
        <form action="" method="post">
            <label for="loginID">ログインID:</label>
            <input type="text" id="loginID" name="loginID" required>
            <br><br>
            <input type="submit" value="PDF生成">
        </form>
    </div>
</body>

</html>