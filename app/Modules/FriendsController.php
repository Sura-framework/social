<?php

namespace App\Modules;

use App\Services\Cache;
use Exception;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class FriendsController extends Module{

    public function sends()
    {
        echo 'true';
    }

    /**
     * Отправка заявки в друзья
     * @param $params
     */
    public function send($params){

        $path = explode('/', $_SERVER['REQUEST_URI']);

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
        //$ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
//        if($ajax == 'yes')
//            Tools::NoAjaxQuery();

        if($logged){
            $params['title'] = $lang['friends'].' | Sura';
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Tools::NoAjaxQuery();

            AntiSpam('friends');

            $for_user_id = intval($path['3']);
            $from_user_id = $user_info['user_id'];

            //Проверяем на факт сушествования заявки для пользователя, если она уже есть, то даёт ответ "yes_demand"
            $check = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$for_user_id}' AND from_user_id = '{$from_user_id}'");

            if($for_user_id AND !$check AND $for_user_id != $from_user_id){

                //Проверяем существования заявки у себя в заявках
                $check_demands = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$from_user_id}' AND from_user_id = '{$for_user_id}'");
                if(!$check_demands){

                    //Проверяем нетли этого юзера уже в списке друзей
                    $check_friendlist = $db->super_query("SELECT user_id FROM `friends` WHERE friend_id = '{$for_user_id}' AND user_id = '{$from_user_id}' AND subscriptions = 0");
                    if(!$check_friendlist){
                        $db->query("INSERT INTO `friends_demands` (for_user_id, from_user_id, demand_date) VALUES ('{$for_user_id}', '{$from_user_id}', NOW())");
                        AntiSpamLogInsert('friends');
                        $db->query("UPDATE `users` SET user_friends_demands = user_friends_demands+1 WHERE user_id = '{$for_user_id}'");
                        echo 'ok';

                        $server_time = intval($_SERVER['REQUEST_TIME']);
                        //Вставляем событие в моментальные оповещания
                        $row_owner = $db->super_query("SELECT user_last_visit FROM `users` WHERE user_id = '{$for_user_id}'");
                        $update_time = $server_time - 70;

                        if($row_owner['user_last_visit'] >= $update_time){

                            $action_update_text = 'хочет добавить Вас в друзья.';

                            $db->query("INSERT INTO `updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_info['user_id']}', type = '11', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/friends/requests'");

                            $Cache = Cache::initialize();
                            $Cache->set("users/{$for_user_id}/updates", '1');
                        }

//                        $config = Settings::loadsettings();

                        //Отправка уведомления на E-mail
//                        if($config['news_mail_1'] == 'yes'){
//                            $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$for_user_id."'");
//                            if($rowUserEmail['user_email']){
//                                include_once __DIR__.'/../Classes/mail.php';
//                                $mail = new \dle_mail($config);
//                                $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
//                                $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '1'");
//                                $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
//                                $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
//                                $mail->send($rowUserEmail['user_email'], 'Новая заявка в друзья', $rowEmailTpl['text']);
//                            }
//                        }
                    } else
                        echo 'yes_friend';
                } else
                    echo 'yes_demand2';
            } else
                echo 'yes_demand';

            die();
        }
    }

    /**
     * Принятие заявки на дружбу
     * @param $params
     */
    public function take($params){
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
//        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
//        if($ajax == 'no')
//            Tools::NoAjaxQuery();

        if($logged){
            $params['title'] = $lang['friends'].' | Sura';
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Tools::NoAjaxQuery();

            $path = explode('/', $_SERVER['REQUEST_URI']);
            var_dump($path);
            //$take_user_id = intval($_GET['take_user_id']);
            $take_user_id = $path['3'];


            $user_id = $user_info['user_id'];

            //Проверяем на существования юзера в таблице заявок в друзья
            $check = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$take_user_id}'");

            if($check){

                //Добавляем юзера который кинул заявку в список друзей
                $db->query("INSERT INTO `friends` SET user_id = '{$user_id}', friend_id = '{$take_user_id}', friends_date = NOW()");

                //Тому кто предлогал дружбу, добавляем ему в друзья себя
                $db->query("INSERT INTO `friends` SET user_id = '{$take_user_id}', friend_id = '{$user_id}', friends_date = NOW()");

                //Обновляем кол-во заявок и кол-друзей у юзера
                $db->query("UPDATE `users` SET user_friends_demands = user_friends_demands-1, user_friends_num = user_friends_num+1 WHERE user_id = '{$user_id}'");

                //Тому кто предлогал дружбу, обновляем кол-друзей
                $db->query("UPDATE `users` SET user_friends_num = user_friends_num+1 WHERE user_id = '{$take_user_id}'");

                //Удаляем заявку из таблицы заявок
                $db->query("DELETE FROM `friends_demands` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$take_user_id}'");

                $server_time = intval($_SERVER['REQUEST_TIME']);
                $generateLastTime = $server_time-10800;

                //Добавляем действия в ленту новостей кто подавал заявку
                $rowX = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 4 AND ac_user_id = '{$take_user_id}'");
                if($rowX['ac_id'])
                    if(!preg_match("/{$rowX['action_text']}/i", $user_id))
                        $db->query("UPDATE `news` SET action_text = '{$rowX['action_text']}||{$user_id}', action_time = '{$server_time}' WHERE ac_id = '{$rowX['ac_id']}'");
                    else
                        echo '';
                else
                    $db->query("INSERT INTO `news` SET ac_user_id = '{$take_user_id}', action_type = 4, action_text = '{$user_id}', action_time = '{$server_time}'");

                //Вставляем событие в моментальные оповещания
                $row_owner = $db->super_query("SELECT user_last_visit FROM `users` WHERE user_id = '{$take_user_id}'");
                $update_time = $server_time - 70;

                if($row_owner['user_last_visit'] >= $update_time){

                    $myRow = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_info['user_id']}'");
                    if($myRow['user_sex'] == 2) $action_update_text = 'подтвердила Вашу заявку на дружбу.';
                    else $action_update_text = 'подтвердил Вашу заявку на дружбу.';

                    $db->query("INSERT INTO `updates` SET for_user_id = '{$take_user_id}', from_user_id = '{$user_info['user_id']}', type = '12', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/u{$take_user_id}'");

                    $Cache = Cache::initialize();
                    $Cache->set("users/{$take_user_id}/updates", '1');
                }

                //Добавляем действия в ленту новостей себе
                $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 4 AND ac_user_id = '{$user_id}'");
                if($row)
                    if(!preg_match("/{$row['action_text']}/i", $take_user_id))
                        $db->query("UPDATE `news` SET action_text = '{$row['action_text']}||{$take_user_id}', action_time = '{$server_time}' WHERE ac_id = '{$row['ac_id']}'");
                    else
                        echo '';
                else
                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 4, action_text = '{$take_user_id}', action_time = '{$server_time}'");

                //Чистим кеш владельцу стр и тому кого добавляем в др.
                $Cache = Cache::initialize();
                $Cache->delete("users/{$user_id}/profile_".$user_id);
                $Cache->delete("users/{$take_user_id}/profile_".$take_user_id);

                //Записываем пользователя в кеш файл друзей
                $openMyList = $Cache->get("users/{$user_id}/friends");
                $Cache->set("users/{$user_id}/friends", $openMyList."u{$take_user_id}|");

                $openTakeList = $Cache->get("users/{$take_user_id}/friends");
                $Cache->set("users/{$take_user_id}/friends", $openTakeList."u{$user_id}|");
            } else
                echo 'no_request';

            die();
        }
    }

    /**
     * Отклонение заявки на дружбу
     */
    public function reject($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $params['title'] = $lang['friends'].' | Sura';
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Tools::NoAjaxQuery();
            $reject_user_id = $db->safesql(intval($_GET['reject_user_id']));
            $user_id = $user_info['user_id'];

            //Проверяем на существования юзера в таблице заявок в друзья
            $check = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$reject_user_id}'");
            if($check){
                //Обновляем кол-во заявок у юзера
                $db->query("UPDATE `users` SET user_friends_demands = user_friends_demands-1 WHERE user_id = '{$user_id}'");

                //Удаляем заявку из таблицы заявок
                $db->query("DELETE FROM `friends_demands` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$reject_user_id}'");

            } else
                echo 'no_request';
        }
    }

    /**
     * Удаления друга из списка друзей
     * @param $params
     */
    public function delete($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Tools::NoAjaxQuery();
            $delet_user_id = $db->safesql(intval($_POST['delet_user_id']));
            $user_id = $user_info['user_id'];

            //Проверяем на существования юзера в списке друзей
            $check = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$delet_user_id}' AND subscriptions = 0");
            if($check){
                //Удаляем друга из таблицы друзей
                $db->query("DELETE FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$delet_user_id}' AND subscriptions = 0");

                //Удаляем у друга из таблицы
                $db->query("DELETE FROM `friends` WHERE user_id = '{$delet_user_id}' AND friend_id = '{$user_id}' AND subscriptions = 0");

                //Обновляем кол-друзей у юзера
                $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$user_id}'");

                //Обновляем у друга которого удаляем кол-во друзей
                $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$delet_user_id}'");

                //Чистим кеш владельцу стр и тому кого удаляем из др.
                $Cache = Cache::initialize();
                $Cache->delete("users/{$user_id}/profile_".$user_id);
                $Cache->delete("users/{$delet_user_id}/profile_".$delet_user_id);

                //Удаляем пользователя из кеш файл друзей
                $openMyList = $Cache->get("users/{$user_id}/friends");
                $Cache->set("users/{$user_id}/friends", str_replace("u{$delet_user_id}|", "", $openMyList));

                $openTakeList = $Cache->get("users/{$delet_user_id}/friends");
                $Cache->set("users/{$delet_user_id}/friends", str_replace("u{$user_id}|", "", $openTakeList));
            } else
                echo 'no_friend';
        }
    }

    /**
     * Удаления друга из списка друзей
     * @param $params
     * @return string
     * @throws Exception
     */
    public function requests($params): string
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $params['title'] = $lang['friends'].' | Sura';
            $page = 1;
            if (isset($_GET['page'] )){
                if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            }
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

//            $mobile_speedbar = 'Заявки в друзья';

            $user_id = $user_info['user_id'];

//            $titles = array('заявка в друзья', 'заявки в друзья', 'заявок в друзья');//friends_demands
//            if($user_info['user_friends_demands'])
//                $user_speedbar = $user_info['user_friends_demands'].' '.Gramatic::declOfNum($user_info['user_friends_demands'], $titles);
//            else
//                $user_speedbar = $lang['no_requests'];

            //Верх
//            $tpl->load_template('friends/head.tpl');
//            $tpl->set('{user-id}', );
            $params['user_id'] =$user_id;
            if($user_info['user_friends_demands'])
//                $tpl->set('{demands}', );
            $params['demands'] ='('.$user_info['user_friends_demands'].')';
            else
//                $tpl->set('{demands}', '');
            $params['demands'] ='';
//                $tpl->set('[request-friends]', '');
//            $tpl->set('[/request-friends]', '');
            $params['request_friends'] = true;
//                $tpl->set_block("'\\[all-friends\\](.*?)\\[/all-friends\\]'si","");
            $params['all_friends'] = false;
//                $tpl->set_block("'\\[online-friends\\](.*?)\\[/online-friends\\]'si","");
            $params['online_friends'] = false;
//                $tpl->compile('info');

            //Выводим заявки в друзья если они есть
            if($user_info['user_friends_demands']){
                $sql_ = $db->super_query("SELECT tb1.from_user_id, demand_date, tb2.user_photo, user_search_pref, user_country_city_name, user_birthday FROM `friends_demands` tb1, `users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = tb2.user_id ORDER by `demand_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
//                $tpl->load_template('friends/request.tpl');

                $config = Settings::loadsettings();

                foreach($sql_ as $key => $row){
                    $user_country_city_name = explode('|', $row['user_country_city_name']);
//                    $tpl->set('{country}', );
                    $sql_[$key]['country'] = $user_country_city_name[0];
//                        $tpl->set('{city}', );
                    $sql_[$key]['city'] = ', '.$user_country_city_name[1];
//                        $tpl->set('{user-id}', );
                    $sql_[$key]['user_id'] = $row['from_user_id'];
//                        $tpl->set('{name}', );
                    $sql_[$key]['name'] = $row['user_search_pref'];

                    // FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile'){

                        $avaPREFver = '50_';
                        $noAvaPrf = 'no_ava_50.png';

                    } else {

                        $avaPREFver = '100_';
                        $noAvaPrf = '100_no_ava.png';

                    }

                    if($row['user_photo'])
//                        $tpl->set('{ava}', );
                    $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['from_user_id'].'/'.$avaPREFver.$row['user_photo'];
                    else
//                        $tpl->set('{ava}', );
                    $sql_[$key]['ava'] = "/images/{$noAvaPrf}";

                        //Возраст юзера
                    $user_birthday = explode('-', $row['user_birthday']);
//                    $tpl->set('{age}', );
                    $sql_[$key]['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

//                        $tpl->compile('content');
                }
                $params['friends'] = $sql_;
//                Tools::navigation($gcount, $user_info['user_friends_demands'], $config['home_url'].'friends/requests/page/', $tpl);

            } else{
//                msgbox('', $lang['no_requests'], 'info_2');

            }

            $params['menu'] = \App\Models\Menu::friends();

            return view('friends.request', $params);

        }else{
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Просмотр всех онлайн друзей
     * @param $params
     * @return string
     * @throws Exception
     */
    public function online($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            $params['title'] = $lang['friends'].' | Sura';

            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $path = explode('/', $_SERVER['REQUEST_URI']);
            if (empty($path['3'])) {
                $get_user_id = $user_info['user_id'];
            } else {
                $get_user_id = $path['3'];
            }
            $params['user_id'] = $get_user_id;

            //ЧС
            $CheckBlackList = Tools::CheckBlackList($get_user_id);
            if(!$CheckBlackList){

                if($get_user_id == $user_info['user_id'])
                    $sql_order = "ORDER by `views`";
                else
                    $sql_order = "ORDER by `friends_date`";

                $server_time = intval($_SERVER['REQUEST_TIME']);
                $online_time = $server_time - 60;

                $sql_ = $db->super_query("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo, user_logged_mobile FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$get_user_id}' AND tb1.user_last_visit >= '{$online_time}' AND tb2.subscriptions = 0 {$sql_order} DESC LIMIT {$limit_page}, {$gcount}", 1);

                //Выводим имя юзера
                $friends_sql = $db->super_query("SELECT user_name, user_friends_num FROM `users` WHERE user_id = '{$get_user_id}'");
                if($user_info['user_id'] != $get_user_id)
                    $gram_name = Gramatic::gramatikName($friends_sql['user_name']);
                else
                    $gram_name = 'Вас';

                if($sql_)
                    //Кол-во друзей в онлайне
                {
//                    $online_friends = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$get_user_id}' AND tb1.user_last_visit >= '{$online_time}' AND tb2.subscriptions = 0");
                }

                //Верх
                if($user_info['user_id'] != $get_user_id)
                    $params['name'] = $gram_name;
                else
                    $params['name'] = '';

                if($get_user_id == $user_info['user_id']){
                    $params['owner'] = true;
                    $params['not_owner'] = false;
                    if($user_info['user_friends_demands'])
                        $params['demands'] = '('.$user_info['user_friends_demands'].')';
                    else
                        $params['demands'] = '';
                } else {
                    $params['not_owner'] = true;
                    $params['not_owner'] = false;
                }
                $params['online_friends'] = true;
                $params['request_friends'] = false;
                $params['all_friends'] = false;

                if($sql_){
                    $config = Settings::loadsettings();
                    foreach($sql_ as $key => $row){
                        $user_country_city_name = explode('|', $row['user_country_city_name']);
                        $sql_[$key]['country'] = $user_country_city_name[0];
                        if($user_country_city_name[1])
                            $sql_[$key]['city'] = ', '.$user_country_city_name[1];
                        else
                            $sql_[$key]['city'] = '';
                            $sql_[$key]['user_id'] = $row['user_id'];
                            $sql_[$key]['name'] = $row['user_search_pref'];

                        if($row['user_photo'])
                            $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['user_id'].'/100_'.$row['user_photo'];
                        else
                            $sql_[$key]['ava'] = '/images/100_no_ava.png';

                            $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                        $sql_[$key]['online'] = $online;

                            //Возраст юзера
                        $user_birthday = explode('-', $row['user_birthday']);
                        $sql_[$key]['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

                        if($get_user_id == $user_info['user_id']){
                            $sql_[$key]['owner'] = true;
                        } else
                            $sql_[$key]['owner'] = false;

                        if($row['user_id'] == $user_info['user_id'])
                            $sql_[$key]['viewer'] = false;
                        else {
                            $sql_[$key]['viewer'] = true;
                        }

                    }
                    $params['friends'] = $sql_;
//                    $tpl = Tools::navigation($gcount, $online_friends['cnt'], $config['home_url'].'friends/online/'.$get_user_id.'/page/', $tpl);
                } else{
//                    msgbox('', $lang['no_requests_online'], 'info_2');

                }
            } else {
                //$user_speedbar = $lang['error'];
//                msgbox('', $lang['no_notes'], 'info');
            }

            $params['menu'] = \App\Models\Menu::friends();

            return view('friends.online', $params);
        }else{
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Загрузка друзей в окне для выбора СП
     * @param $params
     * @return bool
     */
    public function box($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['friends'].' | Sura';
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Tools::NoAjaxQuery();

            $user_id = $user_info['user_id'];

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 18;
            $limit_page = ($page-1)*$gcount;

            if($_POST['user_sex'] == 1)
                $sql_usSex = 2;
            elseif($_POST['user_sex'] == 2)
                $sql_usSex = 1;
            else
                $sql_usSex = false;

            //Все друзья
            if($sql_usSex){
                $count = $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND tb2.user_sex = '{$sql_usSex}'");

                if($count['cnt']){
                    $config = Settings::loadsettings();

                    $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_photo, user_search_pref FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND tb2.user_sex = '{$sql_usSex}' ORDER by `views` DESC LIMIT {$limit_page}, {$gcount}", 1);
                    $tpl->load_template('friends/box_friend.tpl');
                    foreach($sql_ as $row){
                        $tpl->set('{user-id}', $row['friend_id']);
                        $tpl->set('{name}', $row['user_search_pref']);

                        if($row['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/100_no_ava.png');

                        $tpl->compile('content');
                    }
//                    box_navigation($gcount, $count['cnt'], "''", 'sp.openfriends', '');
                } else
                    msg_box( '<div class="clear" style="margin-top:140px"></div>'.$lang['no_requests'], 'info_2');
            } else
                msg_box( '<div class="clear" style="margin-top:140px"></div>'.$lang['no_requests'], 'info_2');

//            Tools::AjaxTpl($tpl);

//            $params['tpl'] = $tpl;
//            Page::generate($params);
            return true;
        }
    }

    /**
     * Общие друзья
     * @param $params
     * @return string
     * @throws Exception
     */
    public function common($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице
//        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
//        if($ajax == 'yes')
//            Tools::NoAjaxQuery();
        if($logged) {
//            $params['title'] = $lang['friends'].' | Sura';
            if ($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page - 1) * $gcount;

            $params['title'] = 'Общие друзья' . ' | Sura';

            $path = explode('/', $_SERVER['REQUEST_URI']);
            if (empty($path['3'])) {
                $get_user_id = $user_info['user_id'];
            } else {
                $get_user_id = $path['3'];
            }
            $params['user_id'] = $get_user_id;

            //Выводим информацию о человеке, у которого смотрим общих друзей
            $owner = $db->super_query("SELECT user_friends_num, user_name FROM `users` WHERE user_id = '{$get_user_id}'");

            //Если есть такой юзер и есть вообще друзья
            if ($owner and $owner['user_friends_num'] and $get_user_id != $user_info['user_id']) {

                //Считаем кол-во общих дузей
                $count_common = $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_info['user_id']}' AND tb2.friend_id = '{$get_user_id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0");

                //Верх
//                $tpl->load_template('friends/head_common.tpl');

//                $tpl->set('{name}', );
                $params['name'] = Gramatic::gramatikName($owner['user_name']);
//                $tpl->set('{user-id}', );
                $params['user_id'] = $get_user_id;

                if ($count_common['cnt']) {
//                    $tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
                    $params['no'] = false;
                } else {
//                    $tpl->set('[no]', '');
//                    $tpl->set('[/no]', '');
                    $params['no'] = true;
                }

//                $tpl->compile('info');

                //Если есть на вывод
                if ($count_common['cnt']) {
//                    $titles = array('общий друг', 'общих друга', 'общих друзей');//friends_common
//                    $user_speedbar = $count_common['cnt'].' '.Gramatic::declOfNum($count_common['cnt'], $titles);

                    //SQL запрос на вывод друзей, по дате новых 20
                    $sql_ = $db->super_query("SELECT tb1.friend_id, tb3.user_birthday, user_photo, user_search_pref, user_country_city_name, user_last_visit, user_logged_mobile FROM `users` tb3, `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_info['user_id']}' AND tb2.friend_id = '{$get_user_id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by `friends_date` LIMIT {$limit_page}, {$gcount}", 1);

                    if ($sql_) {

//                        $tpl->load_template('friends/friend.tpl');
                        $config = Settings::loadsettings();
                        foreach ($sql_ as $key => $row) {

                            $user_country_city_name = explode('|', $row['user_country_city_name']);
//                            $tpl->set('{country}', );
                            $sql_[$key]['country'] = $user_country_city_name[0];
                            if ($user_country_city_name[1])
//                             $tpl->set('{city}', );
                                $sql_[$key]['city'] = ', ' . $user_country_city_name[1];
                            else
//                              $tpl->set('{city}', '');
                                $sql_[$key]['city'] = '';

//                            $tpl->set('{user-id}', );
                            $sql_[$key]['user_id'] = $row['friend_id'];
//                                $tpl->set('{name}', );
                            $sql_[$key]['name'] = $row['user_search_pref'];

                            if (!isset($noAvaPrf))
                                $noAvaPrf = null;

                            if (empty($avaPREFver))
                                $avaPREFver = null;

                            if ($row['user_photo'])
//                                $tpl->set('{ava}', );
                                $sql_[$key]['ava'] = $config['home_url'] . 'uploads/users/' . $row['friend_id'] . '/' . $avaPREFver . $row['user_photo'];
                            else
//                                $tpl->set('{ava}', );
                                $sql_[$key]['ava'] = "/images/{$noAvaPrf}";

                            $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
//                            $tpl->set('{online}', );
                            $sql_[$key]['online'] = $online;

                            //Возраст юзера
                            $user_birthday = explode('-', $row['user_birthday']);
//                            $tpl->set('{age}', );
                            $sql_[$key]['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

                            if (!isset($get_user_id))
                                $get_user_id = null;

                            if ($get_user_id == $user_info['user_id']) {

//                                $tpl->set('[owner]', '');
//                                $tpl->set('[/owner]', '');
                                $sql_[$key]['owner'] = true;
                            } else
//                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                $sql_[$key]['owner'] = false;

//                                $tpl->set('[viewer]', '');
//                            $tpl->set('[/viewer]', '');
                            $sql_[$key]['viewer'] = true;

//                                $tpl->compile('content');

                        }
                        $params['friends'] = $sql_;
//                        navigation($gcount, $count_common['cnt'], $config['home_url'].'friends/common/'.$get_user_id.'/page/');

                    }

                }

            }
            else {
//            msg_box('', 'У Вас с этим пользователем нет общих друзей.', 'info_2');
            }

            $params['menu'] = \App\Models\Menu::friends();

            return view('friends.common', $params);

        }else{
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Просмотр всех друзей
     * @param $params
     * @return string
     * @throws Exception
     */
    public function index($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        //Если страница вызвана через AJAX то включаем защиту, чтоб не могли обращаться напрямую к странице

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['friends'].' | Sura';

            if(isset($_GET['page']) AND $_GET['page'] > 0)
                $page = intval($_GET['page']);
            else
                $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $path = explode('/', $_SERVER['REQUEST_URI']);
            if (empty($path['2']) ){
                $get_user_id = $user_info['user_id'];
            }else{
                $get_user_id = $path['2'];
            }

            $params['user_id'] = $get_user_id;

            //ЧС
            $CheckBlackList = Tools::CheckBlackList($get_user_id);
            if(!$CheckBlackList){
                //Выводим кол-во друзей из таблицы юзеров
                $friends_sql = $db->super_query("SELECT user_name, user_friends_num FROM `users` WHERE user_id = '{$get_user_id}'");

                if($user_info['user_id'] != $get_user_id){
                    $gram_name = Gramatic::gramatikName($friends_sql['user_name']);

                }
                else{
                    $gram_name = 'Вас';

                }

                //Верх
                if($user_info['user_id'] != $get_user_id){
                    $params['name'] = $gram_name;
                }
                else{
                    $params['name'] = '';
                }

                if($get_user_id == $user_info['user_id']){
                    $params['owner'] = true;
                    $params['not_owner'] = false;
                    if($user_info['user_friends_demands']){
                        $params['demands'] = '('.$user_info['user_friends_demands'].')';
                    }
                    else{
                        $params['demands'] = '';
                    }
                } else {
                    $params['not_owner'] = true;
                    $params['not_owner'] = false;
                }

                $params['all_friends'] = true;
                $params['request_friends'] = false;
                $params['online_friends'] = false;

                //Все друзья
                if($friends_sql['user_friends_num']){

                    if($get_user_id == $user_info['user_id']){
                        $sql_order = "ORDER by `views`";
                    }
                    else{
                        $sql_order = "ORDER by `friends_date`";
                    }

                    $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_birthday, user_photo, user_search_pref, user_country_city_name, user_last_visit, user_logged_mobile FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$get_user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 {$sql_order} DESC LIMIT {$limit_page}, {$gcount}", 1);
                    if($sql_){
                        $config = Settings::loadsettings();
                        foreach($sql_ as $key => $row){
                            $user_country_city_name = explode('|', $row['user_country_city_name']);
                            $sql_[$key]['country'] = $user_country_city_name[0];
                            if(isset($user_country_city_name[1])){
                                $sql_[$key]['city'] = ', '.$user_country_city_name[1];
                            }
                            else{
                                $sql_[$key]['city'] = '';
                            }

                            $sql_[$key]['user_id'] = $row['friend_id'];
                            $sql_[$key]['name'] = $row['user_search_pref'];

                            // FOR MOBILE VERSION 1.0
                            if($config['temp'] == 'mobile'){
                                $avaPREFver = '50_';
                                $noAvaPrf = 'no_ava_50.png';
                            } else {
                                $avaPREFver = '100_';
                                $noAvaPrf = '100_no_ava.png';
                            }

                            if($row['user_photo']){
                                $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['friend_id'].'/'.$avaPREFver.$row['user_photo'];
                            }
                            else{
                                $sql_[$key]['ava'] = "/images/{$noAvaPrf}";
                            }

                            $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                            $sql_[$key]['online'] = $online;
                            $user_birthday = explode('-', $row['user_birthday']);
                            $sql_[$key]['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);
                            if($get_user_id == $user_info['user_id']){
                                $sql_[$key]['owner'] = true;
                            } else{
                                $sql_[$key]['owner'] = false;
                            }

                            if($row['friend_id'] == $user_info['user_id']){
                                $sql_[$key]['viewer'] = false;
                            }
                            else {
                                $sql_[$key]['viewer'] = true;
                            }

                        }
//                        $tpl = Tools::navigation($gcount, $friends_sql['user_friends_num'], $config['home_url'].'friends/'.$get_user_id.'/page/', $tpl);

                        $params['friends'] = $sql_;
                    } else{
//                        msg_box('', $lang['no_requests'], 'info_2');

                    }

                } else{
//                    msg_box('', $lang['no_requests'], 'info_2');

                }
            } else {
//                $user_speedbar = $lang['error'];
//                msg_box('', $lang['no_notes'], 'info');
            }
//            $db->free();
//            $tpl->clear();

            $params['menu'] = \App\Models\Menu::friends();

            return view('friends.friends', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }

//        $params['tpl'] = $tpl;
//        Page::generate($params);
//        return true;
    }
}