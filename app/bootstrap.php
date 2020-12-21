<?php

use App\Application;
use Sura\Libs\Db;
use Sura\Libs\Auth;
use Sura\Libs\Langs;
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

$db = Db::getDB();
//Langs::change_lang();

$params = array();
$params['lang'] = Langs::check_lang();

$lang = langs::get_langs();

$user = Auth::index();

$params['user'] = $user;
$user_info = $user['user_info'];
$logged = $user['logged'];

$config = Settings::loadsettings();

if(!$config['home_url']) die("Sura not installed. Please run install.php");

if($config['offline'] == "yes")
    App\Modules\OfflineController::index();

$server_time = intval($_SERVER['REQUEST_TIME']);

if ($user['logged'] == true) {
    if ($user['user_info']['user_delet'] == 1)
        App\Modules\ProfileController::delete();

    if($user['user_info']['user_ban_date'] >= $server_time OR $user['user_info']['user_ban_date'] == '0')
        App\Modules\ProfileController::ban();

    Profile_check::time_zone($user['user_info']['time_zone']);
}
//$sql_banned = $db->super_query("SELECT * FROM ".PREFIX."_banned", true, "banned", true);
//if(isset($sql_banned)) {
//    $blockip = Validation::check_ip($sql_banned);
//    System\Modules\Profile_ban::index($tpl);
//    die();
//}

$app = new Application();
$app->user_online($params);
$params = array($params);
$app->routing($params);