<?php

namespace System\Libs;

use System\Classes\Db;
use System\Libs\Langs;
use System\Libs\Registry;
use System\Libs\Tools;

/**
 * Авторизация пользователей
 */
class Auth
{
	
	public static function index()
	{
		$db = Db::getDB();
		$_IP = $db->safesql($_SERVER['REMOTE_ADDR']);
		$_BROWSER = $db->safesql($_SERVER['HTTP_USER_AGENT']);

        //Если юзер перешел по реф ссылке, то добавляем ид реферала в сессию
        //if($_GET['reg']) $_SESSION['ref_id'] = intval($_GET['reg']);

		//Если есть данные сесии
		if(isset($_SESSION['user_id']) > 0){
			$logged = true;
			$logged_user_id = intval($_SESSION['user_id']);
			$user_info = $db->super_query("SELECT notifications_list, user_id, user_email, user_group, user_friends_demands, user_pm_num, user_support, user_lastupdate, user_photo, user_msg_type, user_delet, user_ban_date, user_new_mark_photos, user_search_pref, user_status, user_last_visit, invties_pub_num FROM `".PREFIX."_users` WHERE user_id = '".$logged_user_id."'");
			//Если есть данные о сесии, но нет инфы о юзере, то выкидываем его
			if(!$user_info['user_id'])
				header('Location: /logout/');

			//Если юзер нажимает "Главная" и он зашел не с моб версии. то скидываем на его стр.
			$host_site = $_SERVER['QUERY_STRING'];
			
			Registry::set('logged', $logged);

            //$config = include __DIR__.'/../data/config.php';

//			if($logged AND !$host_site AND $config['temp'] != 'mobile' AND $_SERVER['REQUEST_URI'] !== '/news/')
//				header('Location: /news/');

			Registry::set('user_info', $user_info);
		//Если есть данные о COOKIE то проверяем
		} elseif(isset($_COOKIE['user_id']) > 0 AND $_COOKIE['password'] AND $_COOKIE['hid']){
			$cookie_user_id = intval($_COOKIE['user_id']);
			$user_info = $db->super_query("SELECT notifications_list, user_id, user_email, user_group, user_password, user_hid, user_friends_demands, user_pm_num, user_support, user_lastupdate, user_photo, user_msg_type, user_delet, user_ban_date, user_new_mark_photos, user_search_pref, user_status, user_last_visit, invties_pub_num FROM `".PREFIX."_users` WHERE user_id = '".$cookie_user_id."'");

			//Если пароль и HID совпадает то пропускаем
			if($user_info['user_password'] == $_COOKIE['password'] AND $user_info['user_hid'] == $_COOKIE['password'].md5(md5($_IP))){
				$_SESSION['user_id'] = $user_info['user_id'];
				
				//Вставляем лог в бд
				$db->query("UPDATE `".PREFIX."_log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$user_info['user_id']."'");
				
				//Удаляем все рание события
				$db->query("DELETE FROM `".PREFIX."_updates` WHERE for_user_id = '{$user_info['user_id']}'");
						
				$logged = true;
			} else {
				$user_info = array();
				$logged = false;
			}

            $config = include __DIR__.'/../data/config.php';

			//Если юзер нажимает "Главная" и он зашел не с моб версии. то скидываем на его стр.
			$host_site = $_SERVER['QUERY_STRING'];
			if($logged AND !$host_site AND $config['temp'] != 'mobile')
				header('Location: /u'.$user_info['user_id']);
				
			Registry::set('logged', $logged);
			Registry::set('user_info', $user_info);
		} else {
			$user_info = array();
			$logged = false;
			// Registry::set('logged', $logged);
		}

		//Если данные поступили через пост и пользователь не авторизован
		if(isset($_POST['log_in']) AND !$logged){

			//Приготавливаем данные
			$email = strip_tags($_POST['email']);

			$password = password_hash(GetVar($_POST['password']), PASSWORD_DEFAULT);

			// if( _strlen( $name, $config['charset'] ) > 40 OR _strlen(trim($name), $config['charset']) < 3) $stop = 'error';

            $lang = langs::get_langs();

			//Проверяем правильность e-mail
			if(Validation::check_email($email) == false) {
				msgbox('', $lang['not_loggin'].'<br /><a href="/restore" onClick="Page.Go(this.href); return false">Забыли пароль?r</a>', 'info_red');
			} else {

                $check_user = $db->super_query("SELECT user_id FROM `".PREFIX."_users` WHERE user_email = '".$email."' AND user_password = '".$password."'");

                //Если есть юзер то пропускаем
                if($check_user){
                    //Hash ID
                    $hid = $password.md5(md5($_IP));

                    //Обновляем хэш входа
                    $db->query("UPDATE `".PREFIX."_users` SET user_hid = '".$hid."' WHERE user_id = '".$check_user['user_id']."'");

                    //Удаляем все рание события
                    $db->query("DELETE FROM `".PREFIX."_updates` WHERE for_user_id = '{$check_user['user_id']}'");

                    //Устанавливаем в сессию ИД юзера
                    $_SESSION['user_id'] = intval($check_user['user_id']);

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", intval($check_user['user_id']), 365);
                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hid", $hid, 365);

                    //Вставляем лог в бд
                    $db->query("UPDATE `".PREFIX."_log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");

                    $config = include __DIR__.'/../data/config.php';

                    if($config['temp'] != 'mobile')
                        header('Location: /u'.$check_user['user_id']);
                    else
                        header('Location: /');
                } else
                    msgbox('', $lang['not_loggin'].'<br /><br /><a href="/restore/" onClick="Page.Go(this.href); return false">Забыли пароль?</a>', 'info_red');
			}
		}

		return array('user_info' => $user_info, 'logged' => $logged);
	}

    public static function logout()
    {
        $redirect = false;
        if(!empty($_SESSION['user_id'])){
            $redirect = true;
        }
        Tools::set_cookie("user_id", "", 0);
        Tools::set_cookie("password", "", 0);
        Tools::set_cookie("hid", "", 0);
        unset($_SESSION['user_id']);
        session_destroy();
        session_unset();
        if ($redirect == true){
            header('Location: https://'.$_SERVER['HTTP_HOST'].'/');
        }
        die();
    }
}

