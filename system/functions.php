<?php
/* 
	Appointment: Основные функции сайта
	File: functions.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

use System\Classes\Db;
use System\Libs\Gramatic;
use System\Libs\Langs;
use System\Libs\Registry;

function GetVar(string $v):string
{
	if(ini_get('magic_quotes_gpc'))
		return stripslashes($v) ;
	return $v;
}

function msgbox($title, $text, $tpl_name) {
	global $tpl;
    $result = null;
    if ($tpl_name == 'info') {
        $result = '<div class="err_yellow">'.$text.'</div>';
    }elseif ($tpl_name == 'info_red') {
        $result = '<div class="err_red">'.$text.'</div>';
    }elseif ($tpl_name == 'info_2') {
        $result = '<div class="info_center">'.$text.'</div>';
    }elseif ($tpl_name == 'info_box') {
        $result = '<div class="msg_none">'.$text.'</div>';
    }elseif ($tpl_name == 'info_search') {
        $result = '<div class="margin_top_10"></div><div class="search_result_title" style="border-bottom:1px solid #e4e7eb">Ничего не найдено</div>
    <div class="info_center" style="width:630px;padding-top:140px;padding-bottom:154px">Ваш запрос не дал результатов</div>';
    }elseif ($tpl_name == 'info_yellow') {
        $result = '<div class="err_yellow"><ul class="listing">'.$text.'</ul></div>';
    }
	$tpl->result['info'] .= $result;
    return $result;
}
function check_smartphone(){

	if ( isset($_SESSION['mobile_enable']) ) return true;
	$phone_array = array('iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'mobile windows', 'cellphone', 'opera mobi', 'operamobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'symbos', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser', 'android');
	$agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	foreach ($phone_array as $value) {
		if ( strpos($agent, $value) !== false ) return true;
	}
	return false;
}

function installationSelected($id, $options){
    return str_replace('value="'.$id.'"', 'value="'.$id.'" selected', $options);
}

function xfieldsdataload($id){
	$xfieldsdata = explode( "||", $id );
    $data = [];
	foreach ( $xfieldsdata as $xfielddata ) {
		list ( $xfielddataname, $xfielddatavalue ) = explode( "|", $xfielddata );
		$xfielddataname = str_replace( "&#124;", "|", $xfielddataname );
		$xfielddataname = str_replace( "__NEWL__", "\r\n", $xfielddataname );
		$xfielddatavalue = str_replace( "&#124;", "|", $xfielddatavalue );
		$xfielddatavalue = str_replace( "__NEWL__", "\r\n", $xfielddatavalue );
		$data[$xfielddataname] = $xfielddatavalue;
	}
	return $data;
}

function profileload() {
	$path = __DIR__.'/data/xfields.txt';
	$filecontents = file($path);

	if(!is_array($filecontents)){
		exit('Невозможно загрузить файл');
	}

	foreach($filecontents as $name => $value){
		$filecontents[$name] = explode("|", trim($value));
		foreach($filecontents[$name] as $name2 => $value2){
			$value2 = str_replace("&#124;", "|", $value2);
			$value2 = str_replace("__NEWL__", "\r\n", $value2);
			$filecontents[$name][$name2] = $value2;
		}
	}
	return $filecontents;
}


function Hacking(){
    $ajax = $_POST['ajax'];
    $lang = langs::get_langs();

	if($ajax){
		NoAjaxQuery();
		echo <<<HTML
        <script type="text/javascript">
        document.title = '{$lang['error']}';
        document.getElementById('speedbar').innerHTML = '{$lang['error']}';
        document.getElementById('page').innerHTML = '{$lang['no_notes']}';
        </script>
        HTML;
		die();
	}
//	else
//		return header('Location: /index.php?go=none');
}

function user_age($user_year, $user_month, $user_day){
    $server_time = intval($_SERVER['REQUEST_TIME']);

	if($user_year){
		$current_year = date('Y', $server_time);
		$current_month = date('n', $server_time);
		$current_day = date('j', $server_time);

		$current_str = strtotime($current_year.'-'.$current_month.'-'.$current_day);
		$current_user = strtotime($current_year.'-'.$user_month.'-'.$user_day);

		if($current_str >= $current_user)
			$user_age = $current_year-$user_year;
		else
			$user_age = $current_year-$user_year-1;

		if($user_month AND $user_month AND $user_day){
            $titles = array('год', 'года', 'лет');//c
            return $user_age.' '.Gramatic::declOfNum($user_age, $titles);
        }
		else
			return false;
	}
}
function langdate($format, $stamp){
    $langdate = Langs::get_langdate();
	return strtr(@date($format, $stamp), $langdate);
}

function megaDate($date, $func = false, $full = false){
    $server_time = intval($_SERVER['REQUEST_TIME']);

	if(date('Y-m-d', $date) == date('Y-m-d', $server_time))
		return $date = langdate('сегодня в H:i', $date);
	elseif(date('Y-m-d', $date) == date('Y-m-d', ($server_time-84600)))
		return $date = langdate('вчера в H:i', $date);
	else
		if($func == 'no_year')
			return $date = langdate('j M в H:i', $date);
		else
			if($full)
				return $date = langdate('j F Y в H:i', $date);
			else
				return $date = langdate('j M Y в H:i', $date);
}

function Online($time, $mobile = false){
    $server_time = intval($_SERVER['REQUEST_TIME']);
    $lang = langs::get_langs();
    $config = include __DIR__.'/data/config.php';
    $online_time = $server_time - $config['online_time'];

    //Если человек сидит с мобильнйо версии
    if($mobile) $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online" />';
    else $mobile_icon = '';

    if($time >= $online_time)
        return  $lang['online'].$mobile_icon;
    else
        return'';
}

function GenerateAlbumPhotosPosition($uid, $aid = false){
    $db = Db::getDB();
	//Выводим все фотографии из альбома и обновляем их позицию только для просмотра альбома
	if($uid AND $aid){
		$sql_ = $db->super_query("SELECT id FROM `".PREFIX."_photos` WHERE album_id = '{$aid}' ORDER by `position` ASC", 1);
		$count = 1;
        $photo_info = '';
		foreach($sql_ as $row){
			$db->query("UPDATE LOW_PRIORITY `".PREFIX."_photos` SET position = '{$count}' WHERE id = '{$row['id']}'");
			$photo_info .= $count.'|'.$row['id'].'||';
			$count++;
		}
		System\Libs\Cache::mozg_create_cache('user_'.$uid.'/position_photos_album_'.$aid, $photo_info);
	}
}

//!nb move
function AntiSpam($act, $text = false){
	
	global $db, $user_info, $server_time;
	
	if($text) $text = md5($text);
	
	/* Типы
		1 - Друзья
		2 - Сообщения не друзьям
		3 - Записей на стену
		4 - Проверка на одинаковый текст
		5 - Комментарии к записям (стены групп/людей)
	*/
	
	//Антиспам дата
	$antiDate = date('Y-m-d', $server_time);
	$antiDate = strtotime($antiDate);
	
	//Лимиты на день
	$max_frieds = 40; #макс. заявок в друзья
	$max_msg = 40; #макс. сообщений не друзьям
	$max_wall = 500; #макс. записей на стену
	$max_identical = 100; #макс. одинаковых текстовых данных
	$max_comm = 2000; #макс. комментариев к записям на стенах людей и сообществ
	$max_groups = 5; #макс. сообществ за день
	
	//Если антиспам на друзей
	if($act == 'friends'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '1' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_frieds){

			die('antispam_err');
			
		}
		
	}
	
	//Если антиспам на сообщения
	elseif($act == 'messages'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '2' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_msg){

			die('antispam_err');
			
		}
		
	}
	
	//Если антиспам на проверку стены
	elseif($act == 'wall'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '3' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_wall){

			die('antispam_err');
			
		}
		
	}
	
	//Если антиспам на одинаковые тестовые данные
	elseif($act == 'identical'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '4' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}' AND txt = '{$text}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_identical){

			die('antispam_err');
			
		}
		
	}	

	//Если антиспам на проверку комментов
	elseif($act == 'comments'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '5' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_comm){

			die('antispam_err');
			
		}
		
	}
	
	//Если антиспам на проверку сообществ
	elseif($act == 'groups'){
		
		//Проверяем в таблице
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_antispam` WHERE act = '6' AND user_id = '{$user_info['user_id']}' AND date = '{$antiDate}'");
		
		//Если кол-во, логов больше, то ставим блок
		if($check['cnt'] >= $max_groups){

			die('antispam_err');
			
		}
		
	}

	
}

//!nb move
function AntiSpamLogInsert($act, $text = false){
	
	global $db, $user_info, $server_time;
	
	if($text)
		$text = md5($text);
	
	//Антиспам дата
	$antiDate = date('Y-m-d', $server_time);
	$antiDate = strtotime($antiDate);
	
	//Если антиспам на друзей
	if($act == 'friends'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '1', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");
		
	//Если антиспам на сообщения не друзьям
	} elseif($act == 'messages'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '2', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");
		
	//Если антиспам на стену
	} elseif($act == 'wall'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '3', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");
		
	//Если антиспам на одинаковых текстов
	} elseif($act == 'identical'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '4', user_id = '{$user_info['user_id']}', date = '{$antiDate}', txt = '{$text}'");
		
	//Если антиспам комменты
	} elseif($act == 'comments'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '5', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");
	
	//Если антиспам комменты
	} elseif($act == 'groups'){
	
		$db->query("INSERT INTO `".PREFIX."_antispam` SET act = '6', user_id = '{$user_info['user_id']}', date = '{$antiDate}'");
		
	}
	
}
?>