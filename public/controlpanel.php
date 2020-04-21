<?php
/* 
	Appointment:  Панель управления
	File: controlpanel.php
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/
@session_start();
@ob_start();
@ob_implicit_flush(0);

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

// define('MOZG', true);
// define('ROOT_DIR', '/../../'.dirname (__FILE__));
// define('ENGINE_DIR', ROOT_DIR.'/system');
// define('ADMIN_DIR', ROOT_DIR.'/system/inc');

// @include ENGINE_DIR.'/data/config.php';
$config = include __DIR__.'/../system/data/config.php';

if(!$config['home_url']) die("Vii Engine not installed. Please run install.php");

$admin_link = $config['home_url'].'controlpanel.php';

// include ENGINE_DIR.'/classes/mysql.php';
// include ENGINE_DIR.'/data/db.php';

include __DIR__.'/../system/data/db.php';

include __DIR__.'/../system/classes/mysql.php';
$db = new db;

include __DIR__.'/../system/inc/functions.php';
include __DIR__.'/../system/inc/login.php';

$db->close();
?>