<?php

use App\Application;
use Sura\Libs\Db;
use Sura\Libs\Auth;
use Sura\Libs\Langs;
use App\Models\Profile;
use Sura\Libs\Profile_check;
use Sura\Libs\Settings;

//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

//header('Content-type: text/html; charset=utf-8');

if(isset($_POST["PHPSESSID"])){
    session_id($_POST["PHPSESSID"]);
}
session_start();
ob_start();
ob_implicit_flush(0);


$config = Settings::loadsettings();

$params['config'] = $config;

if(!$config['home_url']) die("Vii Engine not installed. Please run install.php");
include __DIR__.'/functions.php';

//FOR MOBILE VERSION 1.0
//if($_GET['act'] == 'change_mobile')
//    $_SESSION['mobile'] = 1;
//if($_GET['act'] == 'change_fullver'){
//    $_SESSION['mobile'] = 2;
//    header('Location: /');
//}
//
//if($_SESSION['mobile'] == 1)
//    $config['temp'] = "mobile";

$db = Db::getDB();
//$params['db'] = $db;

//Langs::change_lang();
$params['lang'] = Langs::check_lang();

$lang = langs::get_langs();

$user = Auth::index();
$params['user'] = $user;
$user_info = $user['user_info'];
$logged = $user['logged'];

if($config['offline'] == "yes")
    App\Modules\OfflineController::index();

$server_time = intval($_SERVER['REQUEST_TIME']);

if ($user['logged'] == true) {
    if ($user['user_info']['user_delet'] == 1)
        App\Modules\ProfileController::delete();

    if($user['user_info']['user_ban_date'] >= $server_time OR $user['user_info']['user_ban_date'] == '0')
        App\Modules\ProfileController::ban();

    Profile_check::timezona($user['user_info']['user_timezona']);
}
//$sql_banned = $db->super_query("SELECT * FROM ".PREFIX."_banned", true, "banned", true);
//if(isset($sql_banned)) {
//    $blockip = Validation::check_ip($sql_banned);
//    System\Modules\Profile_ban::index($tpl);
//    die();
//}

//Настройки групп пользователей
//$user_group = unserialize(serialize(array(1 => array('addnews' => '1', ), /*Администрация*/2 => array('addnews' => '0', ), /*Главный модератор*/3 => array('addnews' => '0', ), /*Модератор*/4 => array('addnews' => '0', ), /*Техподдержка*/5 => array('addnews' => '0', ), /*Пользователи*/)));

$app = new Application();

$tpl = $app->view();
//$tpl = null;
$params['tpl'] = $tpl;

$app->user_online($params);
$params = array($params);
$app->routing($params);