<?php
session_start();
require 'config.php';

$sessions = $_SESSION['sessions'] ?? null;

if (empty($sessions)) {
    header("Location: index.php");
    exit;
}

?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>お申込みフォーム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            box-sizing: border-box;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 10px;
            vertical-align: top;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background-color: #ff6a13;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            background-color: #e65b00;
        }
        .error {
            color: red;
            font-size: 12px;
        }
        .note {
            font-size: 12px;
            color: #777;
        }
        .required::after {
            content: "（※）";
            color: red;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>お申込みフォーム</h1>
        <form id="profileForm" action="confirm.php" method="post">
            <?php foreach ($sessions as $session) : ?>
                <?php list($time, $title, $hall) = explode(";", $session); ?>
                <p>講演内容の時間帯: <span><?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?></span></p>
                <input type="hidden" name="lecture_time[]" value="<?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?>">
                <p>講演タイトル: <span><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></span></p>
                <input type="hidden" name="lecture_title[]" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                <p>会場: <span><?php echo htmlspecialchars($hall, ENT_QUOTES, 'UTF-8'); ?></span></p>
                <input type="hidden" name="hall[]" value="<?php echo htmlspecialchars($hall, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endforeach; ?>

            <table>
                <tr>
                    <td><label class="required">名前:</label></td>
                    <td>
                        <input type="text" id="surname" name="surname" placeholder="姓" value="<?php echo htmlspecialchars($user['surname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="given_name" name="given_name" placeholder="名" value="<?php echo htmlspecialchars($user['given_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="error" id="error_surname"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">お名前フリガナ:</label></td>
                    <td>
                        <input type="text" id="surname_kana" name="surname_kana" placeholder="セイ" value="<?php echo htmlspecialchars($user['surname_kana'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="given_name_kana" name="given_name_kana" placeholder="メイ" value="<?php echo htmlspecialchars($user['given_name_kana'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="error" id="error_given_name_kana"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">法人名:</label></td>
                    <td>
                        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($user['company_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="note">注意事項："株式会社"や"合同会社"を含めた正式名称を全角でご入力下さい。</span>
                        <span class="error" id="error_company_name"></span>
                    </td>
                </tr>
                <tr>
                    <td><label for="company_name_kana" class="required">法人名フリガナ</label></td>
                    <td>
                        <input type="text" id="company_name_kana" name="company_name_kana" value="<?php echo htmlspecialchars($user['company_name_kana'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="note">注意事項："株式会社"や"合同会社"を含めた正式名称をカタカナでご入力下さい。</span>
                        <span class="error" id="error_company_name_kana"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">部署名:</label></td>
                    <td>
                        <input type="text" id="department_name" name="department_name" value="<?php echo htmlspecialchars($user['department_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="note">注意事項：全角でご記入ください（無い場合は「ナシ」とご記入ください。）</span>
                        <span class="error" id="error_department_name"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">役職名:</label></td>
                    <td>
                        <input type="text" id="position_name" name="position_name" value="<?php echo htmlspecialchars($user['position_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="error" id="error_position_name"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">勤務先住所:</label></td>
                    <td>
                        <input type="text" id="postal_code" name="postal_code" placeholder="郵便番号" value="<?php echo htmlspecialchars($user['postal_code'], ENT_QUOTES, 'UTF-8'); ?>" required pattern="\d{3}-\d{4}">
                        <span class="note">例 123-4567</span>
                        <span class="error" id="error_postal_code"></span>
                        <input type="text" id="prefecture" name="prefecture" placeholder="勤務先都道府県" value="<?php echo htmlspecialchars($user['prefecture'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="city" name="city" placeholder="市区郡名" value="<?php echo htmlspecialchars($user['city'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="town" name="town" placeholder="町村名" value="<?php echo htmlspecialchars($user['town'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="street" name="street" placeholder="丁目番地" value="<?php echo htmlspecialchars($user['street'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="text" id="building" name="building" placeholder="建物名" value="<?php echo htmlspecialchars($user['building'], ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                </tr>
                <tr>
                    <td><label class="required">電話番号:</label></td>
                    <td>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?>" required pattern="\d{2,4}-\d{2,4}-\d{4}">
                        <span class="note">例 090-1234-5678</span>
                        <span class="error" id="error_phone"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">勤務先メールアドレス:</label></td>
                    <td>
                        <input type="email" id="work_email" name="work_email" value="<?php echo htmlspecialchars($user['work_email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="error" id="error_work_email"></span>
                        <input type="email" id="work_email_confirm" name="work_email_confirm" placeholder="確認用" required>
                        <span class="error" id="error_work_email_confirm"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">パスワード:</label></td>
                    <td>
                        <input type="password" id="password" name="password" required pattern=".{8,12}">
                        <span class="note">半角英数字記号8~12文字</span>
                        <span class="error" id="error_password"></span>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="確認用" required>
                        <span class="error" id="error_password_confirm"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたの業種区分を選択してください。</label></td>
                    <td>
                        <select id="industry_category" name="industry_category" required>
                            <option value="">お選びください</option>
                            <option value="製造業" <?php if ($user['industry_category'] == '製造業') echo 'selected'; ?>>製造業</option>
                            <option value="運輸/鉄道/航空/船舶/倉庫/商社/貿易" <?php if ($user['industry_category'] == '運輸/鉄道/航空/船舶/倉庫/商社/貿易') echo 'selected'; ?>>運輸/鉄道/航空/船舶/倉庫/商社/貿易</option>
                            <option value="小売/卸業" <?php if ($user['industry_category'] == '小売/卸業') echo 'selected'; ?>>小売/卸業</option>
                            <option value="建設/建築/不動産/プラント/化学/鉄鋼/電気機器" <?php if ($user['industry_category'] == '建設/建築/不動産/プラント/化学/鉄鋼/電気機器') echo 'selected'; ?>>建設/建築/不動産/プラント/化学/鉄鋼/電気機器</option>
                            <option value="通信/電話" <?php if ($user['industry_category'] == '通信/電話') echo 'selected'; ?>>通信/電話</option>
                            <option value="自動車" <?php if ($user['industry_category'] == '自動車') echo 'selected'; ?>>自動車</option>
                            <option value="非営利団体/官公庁/政府機関" <?php if ($user['industry_category'] == '非営利団体/官公庁/政府機関') echo 'selected'; ?>>非営利団体/官公庁/政府機関</option>
                            <option value="銀行/信託銀行/信用金庫" <?php if ($user['industry_category'] == '銀行/信託銀行/信用金庫') echo 'selected'; ?>>銀行/信託銀行/信用金庫</option>
                            <option value="証券/リース/クレジットカード/金融業全般" <?php if ($user['industry_category'] == '証券/リース/クレジットカード/金融業全般') echo 'selected'; ?>>証券/リース/クレジットカード/金融業全般</option>
                            <option value="メディア/エンターテインメント" <?php if ($user['industry_category'] == 'メディア/エンターテインメント') echo 'selected'; ?>>メディア/エンターテインメント</option>
                            <option value="生命保険/損害保険" <?php if ($user['industry_category'] == '生命保険/損害保険') echo 'selected'; ?>>生命保険/損害保険</option>
                            <option value="医薬品" <?php if ($user['industry_category'] == '医薬品') echo 'selected'; ?>>医薬品</option>
                            <option value="消費財" <?php if ($user['industry_category'] == '消費財') echo 'selected'; ?>>消費財</option>
                            <option value="電力/水道/ガス" <?php if ($user['industry_category'] == '電力/水道/ガス') echo 'selected'; ?>>電力/水道/ガス</option>
                            <option value="ホテル/旅行/サービス/人材サービス" <?php if ($user['industry_category'] == 'ホテル/旅行/サービス/人材サービス') echo 'selected'; ?>>ホテル/旅行/サービス/人材サービス</option>
                            <option value="ヘルスケア" <?php if ($user['industry_category'] == 'ヘルスケア') echo 'selected'; ?>>ヘルスケア</option>
                            <option value="航空宇宙/防衛" <?php if ($user['industry_category'] == '航空宇宙/防衛') echo 'selected'; ?>>航空宇宙/防衛</option>
                            <option value="鉱業/石油/防衛" <?php if ($user['industry_category'] == '鉱業/石油/防衛') echo 'selected'; ?>>鉱業/石油/防衛</option>
                            <option value="大学/大学院/教育機関/リサーチ/調査" <?php if ($user['industry_category'] == '大学/大学院/教育機関/リサーチ/調査') echo 'selected'; ?>>大学/大学院/教育機関/リサーチ/調査</option>
                            <option value="システムインテグレーター/ITコンサルティング" <?php if ($user['industry_category'] == 'システムインテグレーター/ITコンサルティング') echo 'selected'; ?>>システムインテグレーター/ITコンサルティング</option>
                            <option value="ベンダー(ハード/ソフト）" <?php if ($user['industry_category'] == 'ベンダー(ハード/ソフト）') echo 'selected'; ?>>ベンダー(ハード/ソフト）</option>
                            <option value="監査法人/シンクタンク/コンサルティングファーム" <?php if ($user['industry_category'] == '監査法人/シンクタンク/コンサルティングファーム') echo 'selected'; ?>>監査法人/シンクタンク/コンサルティングファーム</option>
                            <option value="ハイテクノロジー" <?php if ($user['industry_category'] == 'ハイテクノロジー') echo 'selected'; ?>>ハイテクノロジー</option>
                            <option value="その他" <?php if ($user['industry_category'] == 'その他') echo 'selected'; ?>>その他</option>
                        </select>
                        <span class="error" id="error_industry_category"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたの部署カテゴリーを選択してください。</label></td>
                    <td>
                        <select id="department_category" name="department_category" required>
                            <option value="">お選びください</option>
                            <option value="情報システム アプリケーション" <?php if ($user['department_category'] == '情報システム アプリケーション') echo 'selected'; ?>>情報システム アプリケーション</option>
                            <option value="情報システム その他部門" <?php if ($user['department_category'] == '情報システム その他部門') echo 'selected'; ?>>情報システム その他部門</option>
                            <option value="情報システム データウェアハウス/データ統合" <?php if ($user['department_category'] == '情報システム データウェアハウス/データ統合') echo 'selected'; ?>>情報システム データウェアハウス/データ統合</option>
                            <option value="情報システム アーキテクチャ" <?php if ($user['department_category'] == '情報システム アーキテクチャ') echo 'selected'; ?>>情報システム アーキテクチャ</option>
                            <option value="情報システム 役員" <?php if ($user['department_category'] == '情報システム 役員') echo 'selected'; ?>>情報システム 役員</option>
                            <option value="情報システム セキュリティ" <?php if ($user['department_category'] == '情報システム セキュリティ') echo 'selected'; ?>>情報システム セキュリティ</option>
                            <option value="営業 / プリセールス" <?php if ($user['department_category'] == '営業 / プリセールス') echo 'selected'; ?>>営業 / プリセールス</option>
                            <option value="研究 / 開発" <?php if ($user['department_category'] == '研究 / 開発') echo 'selected'; ?>>研究 / 開発</option>
                            <option value="マーケティング" <?php if ($user['department_category'] == 'マーケティング') echo 'selected'; ?>>マーケティング</option>
                            <option value="製造" <?php if ($user['department_category'] == '製造') echo 'selected'; ?>>製造</option>
                            <option value="オペレーション" <?php if ($user['department_category'] == 'オペレーション') echo 'selected'; ?>>オペレーション</option>
                            <option value="調達 / 購買" <?php if ($user['department_category'] == '調達 / 購買') echo 'selected'; ?>>調達 / 購買</option>
                            <option value="カスタマーサービス" <?php if ($user['department_category'] == 'カスタマーサービス') echo 'selected'; ?>>カスタマーサービス</option>
                            <option value="人事 /教育" <?php if ($user['department_category'] == '人事 /教育') echo 'selected'; ?>>人事 /教育</option>
                            <option value="財務" <?php if ($user['department_category'] == '財務') echo 'selected'; ?>>財務</option>
                            <option value="その他部門" <?php if ($user['department_category'] == 'その他部門') echo 'selected'; ?>>その他部門</option>
                        </select>
                        <span class="error" id="error_department_category"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたの役職区分を選択してください。</label></td>
                    <td>
                        <select id="position_category" name="position_category" required>
                            <option value="">お選びください</option>
                            <option value="役員" <?php if ($user['position_category'] == '役員') echo 'selected'; ?>>役員</option>
                            <option value="部長 / 本部長" <?php if ($user['position_category'] == '部長 / 本部長') echo 'selected'; ?>>部長 / 本部長</option>
                            <option value="課長 / 係長" <?php if ($user['position_category'] == '課長 / 係長') echo 'selected'; ?>>課長 / 係長</option>
                            <option value="その他役職" <?php if ($user['position_category'] == 'その他役職') echo 'selected'; ?>>その他役職</option>
                            <option value="開発エンジニア" <?php if ($user['position_category'] == '開発エンジニア') echo 'selected'; ?>>開発エンジニア</option>
                            <option value="プロジェクトマネージャー" <?php if ($user['position_category'] == 'プロジェクトマネージャー') echo 'selected'; ?>>プロジェクトマネージャー</option>
                            <option value="アーキテクト" <?php if ($user['position_category'] == 'アーキテクト') echo 'selected'; ?>>アーキテクト</option>
                            <option value="アナリスト" <?php if ($user['position_category'] == 'アナリスト') echo 'selected'; ?>>アナリスト</option>
                        </select>
                        <span class="error" id="error_position_category"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたのお勤め先の従業員数は、次のうちどのくらいですか？</label></td>
                    <td>
                        <select id="employees_count" name="employees_count" required>
                            <option value="">お選びください</option>
                            <option value="25,000名以上" <?php if ($user['employees_count'] == '25,000名以上') echo 'selected'; ?>>25,000名以上</option>
                            <option value="5,000名～24,999名" <?php if ($user['employees_count'] == '5,000名～24,999名') echo 'selected'; ?>>5,000名～24,999名</option>
                            <option value="1,000名～4,999名" <?php if ($user['employees_count'] == '1,000名～4,999名') echo 'selected'; ?>>1,000名～4,999名</option>
                            <option value="500名～999名" <?php if ($user['employees_count'] == '500名～999名') echo 'selected'; ?>>500名～999名</option>
                            <option value="100名～499名" <?php if ($user['employees_count'] == '100名～499名') echo 'selected'; ?>>100名～499名</option>
                            <option value="100名未満" <?php if ($user['employees_count'] == '100名未満') echo 'selected'; ?>>100名未満</option>
                            <option value="不明" <?php if ($user['employees_count'] == '不明') echo 'selected'; ?>>不明</option>
                        </select>
                        <span class="error" id="error_employees_count"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたのお勤め先の年商規模は、次のうちどのくらいですか？</label></td>
                    <td>
                        <select id="annual_revenue" name="annual_revenue" required>
                            <option value="">お選びください</option>
                            <option value="1兆円以上" <?php if ($user['annual_revenue'] == '1兆円以上') echo 'selected'; ?>>1兆円以上</option>
                            <option value="1,000億円～1兆円未満" <?php if ($user['annual_revenue'] == '1,000億円～1兆円未満') echo 'selected'; ?>>1,000億円～1兆円未満</option>
                            <option value="300億円～1,000億円未満" <?php if ($user['annual_revenue'] == '300億円～1,000億円未満') echo 'selected'; ?>>300億円～1,000億円未満</option>
                            <option value="100億円～300億円未満" <?php if ($user['annual_revenue'] == '100億円～300億円未満') echo 'selected'; ?>>100億円～300億円未満</option>
                            <option value="100億円未満" <?php if ($user['annual_revenue'] == '100億円未満') echo 'selected'; ?>>100億円未満</option>
                            <option value="不明" <?php if ($user['annual_revenue'] == '不明') echo 'selected'; ?>>不明</option>
                        </select>
                        <span class="error" id="error_annual_revenue"></span>
                    </td>
                </tr>
                <tr>
                    <td><label class="required">あなたは本イベントに関連する製品/サービス導入に、主にどのように関与されていますか？</label></td>
                    <td>
                        <select id="event_involvement" name="event_involvement" required>
                            <option value="">お選びください</option>
                            <option value="導入についての決定権のメンバー" <?php if ($user['event_involvement'] == '導入についての決定権のメンバー') echo 'selected'; ?>>導入についての決定権のメンバー</option>
                            <option value="導入についての製品決定権のメンバー" <?php if ($user['event_involvement'] == '導入についての製品決定権のメンバー') echo 'selected'; ?>>導入についての製品決定権のメンバー</option>
                            <option value="導入についてのアドバイスメンバー" <?php if ($user['event_involvement'] == '導入についてのアドバイスメンバー') echo 'selected'; ?>>導入についてのアドバイスメンバー</option>
                            <option value="他者への提案を行うSI・リセラー" <?php if ($user['event_involvement'] == '他者への提案を行うSI・リセラー') echo 'selected'; ?>>他者への提案を行うSI・リセラー</option>
                            <option value="特に導入には関与していない立場" <?php if ($user['event_involvement'] == '特に導入には関与していない立場') echo 'selected'; ?>>特に導入には関与していない立場</option>
                            <option value="その他" <?php if ($user['event_involvement'] == 'その他') echo 'selected'; ?>>その他</option>
                        </select>
                        <span class="error" id="error_event_involvement"></span>
                    </td>
                </tr>
                <tr>
                    <td>本イベントに関連する製品/サービス導入関与に「その他」を選択された方はご記入ください。</td>
                    <td>
                        <textarea id="event_involvement_other" name="event_involvement_other" rows="4" cols="50"><?php echo htmlspecialchars($user['event_involvement_other'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </td>
                </tr>
            </table>
            <button type="submit">次へ</button>
        </form>
    </div>
</body>
</html>