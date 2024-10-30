<?php
define('DB_DSN', 'mysql:host=mysql57.greenjackal32.sakura.ne.jp;dbname=greenjackal32_yanai;charset=utf8');
define('DB_USER', 'greenjackal32');
define('DB_PASSWORD', 'a01310805Aseed-brain');
ini_set('log_errors', 'off');
ini_set('error_log', 'php.log');
// エラーメッセージを格納する配列
$err_msg = array();

$debug_flg = false;
function debug($str)
{
    global $debug_flg;
    if ($debug_flg) {
        error_log('デバッグ:' . $str);
    }
}
// デバッグログの開始を示す関数
function debugLogStart(){
    debug('//////////////////画面表示処理開始/////////////////////');
    debug('セッションID:' . session_id());
    debug('セッション変数の中身:' . print_r($_SESSION, true));
    debug('現在日時タイムスタンプ:' . time());
}
// define('DB_DSN', 'mysql:host=localhost;dbname=informatica_db02;charset=utf8');
// define('DB_USER', 'root');
// define('DB_PASSWORD', 'root');
?>