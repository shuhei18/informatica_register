<?php
define('DB_DSN', 'mysql:host=mysql57.greenjackal32.sakura.ne.jp;dbname=greenjackal32_yanai;charset=utf8');
define('DB_USER', 'greenjackal32');
define('DB_PASSWORD', 'a01310805Aseed-brain');
ini_set('log_errors', 'off');
ini_set('error_log', 'php.log');
// ���顼��å��������Ǽ��������
$err_msg = array();

$debug_flg = false;
function debug($str)
{
    global $debug_flg;
    if ($debug_flg) {
        error_log('�ǥХå�:' . $str);
    }
}
// �ǥХå����γ��Ϥ򼨤��ؿ�
function debugLogStart(){
    debug('//////////////////����ɽ����������/////////////////////');
    debug('���å����ID:' . session_id());
    debug('���å�����ѿ������:' . print_r($_SESSION, true));
    debug('�������������ॹ�����:' . time());
}
// define('DB_DSN', 'mysql:host=localhost;dbname=informatica_db02;charset=utf8');
// define('DB_USER', 'root');
// define('DB_PASSWORD', 'root');
?>