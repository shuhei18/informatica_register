<?php
// header部分
$header = file_get_contents('header.html');
if ($header === FALSE) {
    die("header.htmlを読み込めませんでした。");
}
// 読み込んだHTMLを結合して表示
echo $header;
?>
<title>パスワード再設定完了</title>
 <div class="profileform-container">
        <h1 class="profileform-text">パスワード再設定完了</h1>
        <center><p>パスワードの再設定が完了しました。<br>新しいパスワードでログインしてください。</p></center>
<div class="select-btn-box" style="margin-top:30px">
<button type="button"  onclick="location.href='login.php'" class="select-btn">ログインページへ</button>
</div>
    </div>
</body>

</html>