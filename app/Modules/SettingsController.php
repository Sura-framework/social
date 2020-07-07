<?php

namespace App\Modules;

use Sura\Libs\Cache;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;


class SettingsController extends Module{

    public function newpass($params){
        //$tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            //$act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            Tools::NoAjaxQuery();

            $_POST['old_pass'] = Validation::ajax_utf8($_POST['old_pass']);
            $_POST['new_pass'] = Validation::ajax_utf8($_POST['new_pass']);
            $_POST['new_pass2'] = Validation::ajax_utf8($_POST['new_pass2']);

            $old_pass = md5(md5(GetVar($_POST['old_pass'])));
            $new_pass = md5(md5(GetVar($_POST['new_pass'])));
            $new_pass2 = md5(md5(GetVar($_POST['new_pass2'])));

            //Выводим текущий пароль
            $row = $db->super_query("SELECT user_password FROM `users` WHERE user_id = '{$user_id}'");
            if($row['user_password'] == $old_pass){
                if($new_pass == $new_pass2)
                    $db->query("UPDATE `users` SET user_password = '{$new_pass2}' WHERE user_id = '{$user_id}'");
                else
                    echo '2';
            } else
                echo '1';

            die();
        }
    }

    public function newname($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            Tools::NoAjaxQuery();
            $user_name = Validation::ajax_utf8(Validation::textFilter($_POST['name']));
            $user_lastname = Validation::ajax_utf8(Validation::textFilter(ucfirst($_POST['lastname'])));

            //Проверка имени
            if(isset($user_name)){
                if(strlen($user_name) >= 2){
                    if(!preg_match("/^[a-zA-Zа-яА-Я]+$/iu", $user_name))
                        $errors = 3;
                } else
                    $errors = 2;
            } else
                $errors = 1;

            //Проверка фамилии
            if(isset($user_lastname)){
                if(strlen($user_lastname) >= 2){
                    if(!preg_match("/^[a-zA-Zа-яА-Я]+$/iu", $user_lastname))
                        $errors_lastname = 3;
                } else
                    $errors_lastname = 2;
            } else
                $errors_lastname = 1;

            if(!$errors){
                if(!$errors_lastname){
                    $user_name = ucfirst($user_name);
                    $user_lastname = ucfirst($user_lastname);

                    $db->query("UPDATE `users` SET user_name = '{$user_name}', user_lastname = '{$user_lastname}', user_search_pref = '{$user_name} {$user_lastname}' WHERE user_id = '{$user_id}'");

                    Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
                    Cache::mozg_clear_cache();
                } else
                    echo $errors;
            } else
                echo $errors;

            die();
        }
    }

    public function saveprivacy($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            Tools::NoAjaxQuery();

            $val_msg = intval($_POST['val_msg']);
            $val_wall1 = intval($_POST['val_wall1']);
            $val_wall2 = intval($_POST['val_wall2']);
            $val_wall3 = intval($_POST['val_wall3']);
            $val_info = intval($_POST['val_info']);

            if($val_msg <= 0 OR $val_msg > 3) $val_msg = 1;
            if($val_wall1 <= 0 OR $val_wall1 > 3) $val_wall1 = 1;
            if($val_wall2 <= 0 OR $val_wall2 > 3) $val_wall2 = 1;
            if($val_wall3 <= 0 OR $val_wall3 > 3) $val_wall3 = 1;
            if($val_info <= 0 OR $val_info > 3) $val_info = 1;

            $user_privacy = "val_msg|{$val_msg}||val_wall1|{$val_wall1}||val_wall2|{$val_wall2}||val_wall3|{$val_wall3}||val_info|{$val_info}||";

            $db->query("UPDATE `users` SET user_privacy = '{$user_privacy}' WHERE user_id = '{$user_id}'");

            Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);

            die();
        }
    }

    public function privacy($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            $sql_ = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$user_id}'");
            $row = xfieldsdataload($sql_['user_privacy']);
            $tpl->load_template('settings/privacy.tpl');
            $tpl->set('{val_msg}', $row['val_msg']);
            $tpl->set('{val_msg_text}', strtr($row['val_msg'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Никто')));
            $tpl->set('{val_wall1}', $row['val_wall1']);
            $tpl->set('{val_wall1_text}', strtr($row['val_wall1'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
            $tpl->set('{val_wall2}', $row['val_wall2']);
            $tpl->set('{val_wall2_text}', strtr($row['val_wall2'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
            $tpl->set('{val_wall3}', $row['val_wall3']);
            $tpl->set('{val_wall3_text}', strtr($row['val_wall3'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
            $tpl->set('{val_info}', $row['val_info']);
            $tpl->set('{val_info_text}', strtr($row['val_info'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
            $tpl->compile('info');
        }
    }

    public function addblacklist($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            Tools::NoAjaxQuery();
            $bad_user_id = intval($_POST['bad_user_id']);

            //Проверяем на существование юзера
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$bad_user_id}'");

            //Выводим свой блеклист для проверка
            $myRow = $db->super_query("SELECT user_blacklist FROM `users` WHERE user_id = '{$user_id}'");
            $array_blacklist = explode('|', $myRow['user_blacklist']);

            if($row['cnt'] AND !in_array($bad_user_id, $array_blacklist) AND $user_id != $bad_user_id){
                $db->query("UPDATE `users` SET user_blacklist_num = user_blacklist_num+1, user_blacklist = '{$myRow['user_blacklist']}|{$bad_user_id}|' WHERE user_id = '{$user_id}'");

                //Если юзер есть в др.
                if(CheckFriends($bad_user_id)){
                    //Удаляем друга из таблицы друзей
                    $db->query("DELETE FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$bad_user_id}' AND subscriptions = 0");

                    //Удаляем у друга из таблицы
                    $db->query("DELETE FROM `friends` WHERE user_id = '{$bad_user_id}' AND friend_id = '{$user_id}' AND subscriptions = 0");

                    //Обновляем кол-друзей у юзера
                    $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$user_id}'");

                    //Обновляем у друга которого удаляем кол-во друзей
                    $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$bad_user_id}'");

                    //Чистим кеш владельцу стр и тому кого удаляем из др.
                    Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
                    Cache::mozg_clear_cache_file('user_'.$bad_user_id.'/profile_'.$bad_user_id);

                    //Удаляем пользователя из кеш файл друзей
                    $openMyList = Cache::mozg_cache("user_{$user_id}/friends");
                    Cache::mozg_create_cache("user_{$user_id}/friends", str_replace("u{$bad_user_id}|", "", $openMyList));

                    $openTakeList = Cache::mozg_cache("user_{$bad_user_id}/friends");
                    Cache::mozg_create_cache("user_{$bad_user_id}/friends", str_replace("u{$user_id}|", "", $openTakeList));
                }

                $openMyList = Cache::mozg_cache("user_{$user_id}/blacklist");
                Cache::mozg_create_cache("user_{$user_id}/blacklist", $openMyList."|{$bad_user_id}|");
            }

            die();
        }
    }

    public function delblacklist($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            Tools::NoAjaxQuery();
            $bad_user_id = intval($_POST['bad_user_id']);

            //Проверяем на существование юзера
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$bad_user_id}'");

            //Выводим свой блеклист для проверка
            $myRow = $db->super_query("SELECT user_blacklist FROM `users` WHERE user_id = '{$user_id}'");
            $array_blacklist = explode('|', $myRow['user_blacklist']);

            if($row['cnt'] AND in_array($bad_user_id, $array_blacklist) AND $user_id != $bad_user_id){
                $myRow['user_blacklist'] = str_replace("|{$bad_user_id}|", "", $myRow['user_blacklist']);
                $db->query("UPDATE `users` SET user_blacklist_num = user_blacklist_num-1, user_blacklist = '{$myRow['user_blacklist']}' WHERE user_id = '{$user_id}'");

                $openMyList = Cache::mozg_cache("user_{$user_id}/blacklist");
                Cache::mozg_create_cache("user_{$user_id}/blacklist", str_replace("|{$bad_user_id}|", "", $openMyList));
            }

            die();
        }
    }

    public function blacklist($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            $row = $db->super_query("SELECT user_blacklist, user_blacklist_num FROM `users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('settings/blacklist.tpl');
            $titles = array('человек', 'человека', 'человек');//fave
            $tpl->set('{cnt}', '<span id="badlistnum">'.$row['user_blacklist_num'].'</span> '.Gramatic::declOfNum($row['user_blacklist_num'], $titles));
            if($row['user_blacklist_num']){
                $tpl->set('[yes-users]', '');
                $tpl->set('[/yes-users]', '');
            } else
                $tpl->set_block("'\\[yes-users\\](.*?)\\[/yes-users\\]'si","");
            $tpl->compile('info');

            if($row['user_blacklist_num'] AND $row['user_blacklist_num'] <= 100){
                $tpl->load_template('settings/baduser.tpl');
                $array_blacklist = explode('|', $row['user_blacklist']);
                foreach($array_blacklist as $user){
                    if($user){
                        $infoUser = $db->super_query("SELECT user_photo, user_search_pref FROM `users` WHERE user_id = '{$user}'");

                        if($infoUser['user_photo'])
                            $tpl->set('{ava}', '/uploads/users/'.$user.'/50_'.$infoUser['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        $tpl->set('{name}', $infoUser['user_search_pref']);
                        $tpl->set('{user-id}', $user);

                        $tpl->compile('content');
                    }
                }
            } else
                msgbox('', $lang['settings_nobaduser'], 'info_2');
        }
    }

    public function change_mail($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            $config = Settings::loadsettings();

            //Отправляем письмо на обе почты
            include_once __DIR__.'/../Classes/mail.php';
            $mail = new \dle_mail($config);

            $email = Validation::textFilter($_POST['email'], false, true);

            //Проверка E-mail
            if(preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email)) $ok_email = true;
            else $ok_email = false;

            $row = $db->super_query("SELECT user_email FROM `users` WHERE user_id = '{$user_id}'");

            $check_email = $db->super_query("SELECT COUNT(*) AS cnt FROM `users`  WHERE user_email = '{$email}'");

            if($row['user_email'] AND $ok_email AND !$check_email['cnt']){

                //Удаляем все пред. заявки
                $db->query("DELETE FROM `restore` WHERE email = '{$email}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $hash = md5($server_time.$row['user_email'].rand(0, 100000).$rand_lost);

                $message = <<<HTML
                        Вы получили это письмо, так как зарегистрированы на сайте
                        {$config['home_url']} и хотите изменить основной почтовый адрес.
                        Вы желаете изменить почтовый адрес с текущего ({$row['user_email']}) на {$email}
                        Для того чтобы Ваш основной e-mail на сайте {$config['home_url']} был
                        изменен, Вам необходимо пройти по ссылке:
                        {$config['home_url']}index.php?go=settings&code1={$hash}
                        
                        Внимание: не забудьте, что после изменения почтового адреса при входе
                        на сайт Вам нужно будет указывать новый адрес электронной почты.
                        
                        Если Вы не посылали запрос на изменение почтового адреса,
                        проигнорируйте это письмо.С уважением,
                        Администрация {$config['home_url']}
                        HTML;
                $mail->send($row['user_email'], 'Изменение почтового адреса', $message);

                //Вставляем в БД код 1
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $hash = md5($server_time.$row['user_email'].rand(0, 300000).$rand_lost);

                $message = <<<HTML
                        Вы получили это письмо, так как зарегистрированы на сайте
                        {$config['home_url']} и хотите изменить основной почтовый адрес.
                        Вы желаете изменить почтовый адрес с текущего ({$row['user_email']}) на {$email}
                        Для того чтобы Ваш основной e-mail на сайте {$config['home_url']} был
                        изменен, Вам необходимо пройти по ссылке:
                        {$config['home_url']}index.php?go=settings&code2={$hash}
                        
                        Внимание: не забудьте, что после изменения почтового адреса при входе
                        на сайт Вам нужно будет указывать новый адрес электронной почты.
                        
                        Если Вы не посылали запрос на изменение почтового адреса,
                        проигнорируйте это письмо.С уважением,
                        Администрация {$config['home_url']}
                        HTML;
                $mail->send($email, 'Изменение почтового адреса', $message);

                //Вставляем в БД код 2
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");

            } else
                echo '1';

            exit;
        }
    }

    public function general($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if(isset($_POST['ajax']) AND $_POST['ajax'] == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $user_id = $user_info['user_id'];
            //$act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            $mobile_speedbar = 'Общие настройки';
            $row = $db->super_query("SELECT user_name, user_lastname, user_email, user_timezona FROM `users` WHERE user_id = '{$user_id}'");

            //Загружаем вверх
            $tpl->load_template('settings/general.tpl');
            $tpl->set('{name}', $row['user_name']);
            $tpl->set('{lastname}', $row['user_lastname']);
            $tpl->set('{id}', $user_id);

            //Завершении смены E-mail
            $tpl->set('{code-1}', 'no_display');
            $tpl->set('{code-2}', 'no_display');
            $tpl->set('{code-3}', 'no_display');

            if(isset($_GET['code1'])){
                $code1 = Validation::strip_data($_GET['code1']);
                $code2 = Validation::strip_data($_GET['code2']);

                if(strlen($code1) == 32){
                    $_IP = null;
                    $code2 = '';

                    $check_code1 = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$code1}' AND ip = '{$_IP}'");

                    if($check_code1['email']){

                        $check_code2 = $db->super_query("SELECT COUNT(*) AS cnt FROM `restore` WHERE hash != '{$code1}' AND email = '{$check_code1['email']}' AND ip = '{$_IP}'");

                        if($check_code2['cnt'])
                            $tpl->set('{code-1}', '');
                        else {
                            $tpl->set('{code-1}', 'no_display');
                            $tpl->set('{code-3}', '');

                            //Меняем
                            $db->query("UPDATE `users` SET user_email = '{$check_code1['email']}' WHERE user_id = '{$user_id}'");
                            $row['user_email'] = $check_code1['email'];

                        }

                        $db->query("DELETE FROM `restore` WHERE hash = '{$code1}' AND ip = '{$_IP}'");

                    }

                }

                if(strlen($code2) == 32){

                    $check_code2 = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$code2}' AND ip = '{$_IP}'");

                    if($check_code2['email']){

                        $check_code1 = $db->super_query("SELECT COUNT(*) AS cnt FROM `restore` WHERE hash != '{$code2}' AND email = '{$check_code2['email']}' AND ip = '{$_IP}'");

                        if($check_code1['cnt'])
                            $tpl->set('{code-2}', '');
                        else {
                            $tpl->set('{code-2}', 'no_display');
                            $tpl->set('{code-3}', '');

                            //Меняем
                            $db->query("UPDATE `users` SET user_email = '{$check_code2['email']}'  WHERE user_id = '{$user_id}'");
                            $row['user_email'] = $check_code2['email'];

                        }

                        $db->query("DELETE FROM `restore` WHERE hash = '{$code2}' AND ip = '{$_IP}'");

                    }

                }

            }

            //Email
            $substre = substr($row['user_email'], 0, 1);
            $epx1 = explode('@', $row['user_email']);
            $tpl->set('{email}', $substre.'*******@'.$epx1[1]);


            $tpl->set('{timezs}', installationSelected($row['user_timezona'], ' <option value="1">GMT-11 "Samoa"</option> 
 <option value="2">GMT-10 "Hawaii"</option>
 <option value="3">GMT-9 "Alaska"</option> 
 <option value="4">GMT-8 "Los Angeles"</option>  
 <option value="5">GMT-7 "Denver"</option>
  <option value="6">GMT-6 "Chicago"</option>
  <option value="7">GMT-5 "New York"</option> 
  <option value="8">GMT-4 "Caracas"</option> 
 <option value="9">GMT-3 "Buenos Aires"</option>
 <option value="10">GMT-2 "Sao Paulo"</option> 
 <option value="11">GMT-1 "Azores"</option>  
 <option value="12">GMT 0 "London"</option>
  <option value="13">GMT1 "Berlin,Paris"</option>
  <option value="14">GMT 2 "Kyiv, Minsk"</option> 
  <option value="15">GMT 3 "Moscow, Saint-Peterburg"</option> 
 <option value="16">GMT 4 "Yerevan"</option>
 <option value="17">GMT 5 "Yekaterinburg, Tashkent"</option> 
 <option value="18">GMT 6 "Novosibirsk"</option>  
 <option value="19">GMT 7 "Krasnoyarsk, Bangkok"</option>
  <option value="20">GMT 8 "Singapore, Hong Kong"</option>
  <option value="21">GMT 9 "Tokyo"</option> 
  <option value="22">GMT 10 "Vladivostok"</option> 
 <option value="23">GMT 11 "Sydney"</option>
 <option value="24">GMT 12 "Kamchatka"</option> '));

            $tpl->compile('info');

            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        Registry::set('tpl', $tpl);
        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }

    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $user_id = $user_info['user_id'];
            //$act = $_GET['act'];
            $params['title'] = $lang['settings'].' | Sura';

            $mobile_speedbar = 'Общие настройки';
            $row = $db->super_query("SELECT user_name, user_lastname, user_email FROM `users` WHERE user_id = '{$user_id}'");

            //Загружаем вверх
            $tpl->load_template('settings/settings.tpl');
            $tpl->set('{name}', $row['user_name']);
            $tpl->set('{lastname}', $row['user_lastname']);
            $tpl->set('{id}', $user_id);


            $tpl->compile('info');

            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        Registry::set('tpl', $tpl);
        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}