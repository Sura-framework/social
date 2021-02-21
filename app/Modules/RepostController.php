<?php

namespace App\Modules;

use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class RepostController extends Module{

    /**
     * Если выбрано "Друзья и подписчики"
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function for_wall(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

//        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            $rid = (int)$request['rec_id'];
            $comm = Validation::ajax_utf8(Validation::textFilter($request['comm']));

            //Проверка на существование записи
            if($request['g_tell'] == 1){
                $row = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `wall` WHERE fast_comm_id = '0' AND id = '{$rid}'");
                if (empty($row)){
                    $row2 = $db->super_query("SELECT obj_id FROM `news` WHERE ac_id = '{$rid}'");
                    $row = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `wall` WHERE fast_comm_id = '0' AND id = '{$row2['obj_id']}'");
                }
                $author_user_id = $row['author_user_id'];
            } else {
                $row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");
                if (empty($row)){
                    $row2 = $db->super_query("SELECT obj_id FROM `news` WHERE ac_id = '{$rid}'");
                    $row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `communities_wall` WHERE fast_comm_id = 0 AND id = '{$row2['obj_id']}'");
                }
                $row['author_user_id'] = $row['tell_uid'];
                $author_user_id = $row['tell_uid'];
            }
            if($row){
                if($author_user_id !== $user_id){
                    if($row['tell_uid']){
                        $row['add_date'] = $row['tell_date'];
                        $row['author_user_id'] = $row['tell_uid'];
                    } elseif($request['g_tell'] == 2){
                        $row['public'] = 1;
                        $row['author_user_id'] = $row['public_id'];
                    }

                    //Проверяем на существование этой записи у себя на стене
                    $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE tell_uid = '{$author_user_id}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}'");
                    if($myRow['cnt'] == false){
                        $row['text'] = $db->safesql($row['text']);
                        $row['attach'] = $db->safesql($row['attach']);

                        //Всталвяем себе на стену
                        $server_time = \Sura\Time\Date::time();
                        $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$author_user_id}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '{$row['attach']}', tell_comm = '{$comm}'");
                        $dbid = $db->insert_id();
                        $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                        //Вставляем в ленту новостей
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                        //Чистим кеш
//                        Cache::mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");

//                        $Cache = cache_init(array('type' => 'file'));
//                        $Cache->delete("users/{$user_id}/profile_{$user_id}");

                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $key = $user_id.'/profile_'.$user_id;
                        $cache->remove($key);

                        $status = Status::OK;
                    }else{
                        $status = Status::OWNER_FOUND;
                    }
                }else{
                    $status = Status::NOT_USER;
                }
            }else{
                $status = Status::NOT_FOUND;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Если выбрано "Подписчики сообщества"
     *
     * @return int
     * @throws \JsonException
     */
    public function groups(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            $rid = (int)$request['rec_id'];
            $sel_group = (int)$request['sel_group'];
            $comm = Validation::ajax_utf8(Validation::textFilter($request['comm']));

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `wall` WHERE fast_comm_id = '0' AND id = '{$rid}'");

            if($row['tell_uid']){
                $row['add_date'] = $row['tell_date'];
                $row['author_user_id'] = $row['tell_uid'];
            }

            //ДЛя проверки что записи нет в сообществе
            if($row['public']) {
                $check_IdGR = $row['tell_uid'];
            }
            else {
                $check_IdGR = '';
            }

            $server_time = \Sura\Time\Date::time();

            //Проверка на админа
            $rowGroup = $db->super_query("SELECT admin, del, ban FROM `communities` WHERE id = '{$sel_group}'");

            //Проверяем на существование этой записи В сообществе
            $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_wall` WHERE tell_uid = '{$row['author_user_id']}' AND public_id = '{$sel_group}' AND tell_date = '{$row['add_date']}'");

            if($sel_group != $check_IdGR AND $myRow['cnt'] == false AND stripos($rowGroup['admin'], "u{$user_id}|") !== false AND $rowGroup['del'] == 0 AND $rowGroup['ban'] == 0){
                $row['text'] = $db->safesql($row['text']);
                $row['attach'] = $db->safesql($row['attach']);

                //Вставляем саму запись в БД
                $db->query("INSERT INTO `communities_wall` SET public_id = '{$sel_group}', text = '{$row['text']}', attach = '{$row['attach']}', add_date = '{$server_time}', tell_uid = '{$row['author_user_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', tell_comm = '{$comm}'");
                $dbid = $db->insert_id();
                $db->query("UPDATE `communities` SET rec_num = rec_num+1 WHERE id = '{$sel_group}'");

                //Вставляем в ленту новотсей
                $db->query("INSERT INTO `news` SET ac_user_id = '{$sel_group}', action_type = 11, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                $status = Status::OK;
            }else{
                $status = Status::BAD_RIGHTS;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Если выбрано "Подписчики сообщества" из СООБЩЕСТВ
     *
     * @return int
     * @throws \JsonException
     */
    public function groups_2(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if ($logged) {

            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            $rid = (int)$request['rec_id'];
            $sel_group = (int)$request['sel_group'];
            $comm = Validation::ajax_utf8(Validation::textFilter($request['comm']));

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

            if ($row['tell_uid']) {
                $tell_uid = $row['tell_uid'];
                $tell_date = $row['tell_date'];
                if ($row['public'])
                    $row['public_id'] = $tell_uid;
            } else {
                $tell_uid = $row['public_id'];
                $tell_date = $row['add_date'];
                $row['public'] = 1;
            }

            //Проверка на админа
            $rowGroup = $db->super_query("SELECT admin, del, ban FROM `communities` WHERE id = '{$sel_group}'");

            //Проверяем на существование этой записи В сообществе
            $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_wall` WHERE tell_uid = '{$tell_uid}' AND public_id = '{$sel_group}' AND tell_date = '{$tell_date}'");

            if ($sel_group != $row['public_id'] and $myRow['cnt'] == false and stripos($rowGroup['admin'], "u{$user_id}|") !== false and $rowGroup['del'] == 0 and $rowGroup['ban'] == 0) {

                $row['text'] = $db->safesql($row['text']);
                $row['attach'] = $db->safesql($row['attach']);

                $server_time = \Sura\Time\Date::time();

                //Вставляем саму запись в БД
                $db->query("INSERT INTO `communities_wall` SET public_id = '{$sel_group}', text = '{$row['text']}', attach = '{$row['attach']}', add_date = '{$server_time}', tell_uid = '{$tell_uid}', tell_date = '{$tell_date}', public = '{$row['public']}', tell_comm = '{$comm}'");
                $dbid = $db->insert_id();
                $db->query("UPDATE `communities` SET rec_num = rec_num+1 WHERE id = '{$sel_group}'");

                //Вставляем в ленту новотсей
                $db->query("INSERT INTO `news` SET ac_user_id = '{$sel_group}', action_type = 11, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                $status = Status::OK;
            } else {
                $status = Status::BAD_RIGHTS;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ));
    }

    /**
     * Если выбрано " Отправить личным сообщением"
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function message(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());


            $for_user_id = (int)$request['for_user_id'];
            $tell_comm = Validation::ajax_utf8(Validation::textFilter($request['comm']));
            $rid = (int)$request['rec_id'];

            if($user_id !== $for_user_id){

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$for_user_id}'");

                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //ЧС
                    $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($for_user_id);

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_msg'] == 2)
                        $check_friend = (new \App\Libs\Friends)->CheckFriends($for_user_id);

                    if(!$CheckBlackList AND $user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $check_friend)
                        $xPrivasy = 1;
                    else
                        $xPrivasy = 0;

                    if($xPrivasy){

                        //Выводим данные о записи
                        if($request['g_tell'] == 1)

                            $row_rec = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

                        else

                            $row_rec = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `wall` WHERE fast_comm_id = '0' AND id = '{$rid}'");

                        if($row_rec){
                            $msg = $db->safesql($row_rec['text']);
                            $attach_files = $db->safesql($row_rec['attach']);
                            $theme = 'Запись на стене';

                            if($row_rec['tell_uid']){

                                $tell_uid = $row_rec['tell_uid'];
                                $tell_date = $row_rec['tell_date'];

                            } else {

                                if($request['g_tell'] == 1){

                                    $row_rec['author_user_id'] = $row_rec['public_id'];
                                    $row_rec['public'] = 1;

                                }

                                $tell_uid = $row_rec['author_user_id'];
                                $tell_date = $row_rec['add_date'];

                            }

                            $server_time = \Sura\Time\Date::time();

                            //Отправляем сообщение получателю
                            $db->query("INSERT INTO `messages` SET theme = '{$theme}', text = '{$msg}', for_user_id = '{$for_user_id}', from_user_id = '{$user_id}', date = '{$server_time}', pm_read = 'no', folder = 'inbox', history_user_id = '{$user_id}', attach = '".$attach_files."', tell_uid = '{$tell_uid}', tell_date = '{$tell_date}', public = '{$row_rec['public']}', tell_comm = '{$tell_comm}'");
                            $dbid = $db->insert_id();

                            //Сохраняем сообщение в папку отправленные
                            $db->query("INSERT INTO `messages` SET theme = '{$theme}', text = '{$msg}', for_user_id = '{$user_id}', from_user_id = '{$for_user_id}', date = '{$server_time}', pm_read = 'no', folder = 'outbox', history_user_id = '{$user_id}', attach = '".$attach_files."', tell_uid = '{$tell_uid}', tell_date = '{$tell_date}', public = '{$row_rec['public']}', tell_comm = '{$tell_comm}'");

                            //Обновляем кол-во новых сообщения у получателя
                            $db->query("UPDATE `users` SET user_pm_num = user_pm_num+1 WHERE user_id = '{$for_user_id}'");

                            //Проверка на наличии созданого диалога у себя
                            $check_im = $db->super_query("SELECT iuser_id FROM `im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                            if(!$check_im) {
                                $db->query("INSERT INTO im SET iuser_id = '" . $user_id . "', im_user_id = '" . $for_user_id . "', idate = '" . $server_time . "', all_msg_num = 1");
                            }
                            else {
                                $db->query("UPDATE im  SET idate = '" . $server_time . "', all_msg_num = all_msg_num+1 WHERE iuser_id = '" . $user_id . "' AND im_user_id = '" . $for_user_id . "'");
                            }

                            //Проверка на наличии созданого диалога у получателя, а если есть то просто обновляем кол-во новых сообщений в диалоге
                            $check_im_2 = $db->super_query("SELECT iuser_id FROM im WHERE iuser_id = '".$for_user_id."' AND im_user_id = '".$user_id."'");
                            if(!$check_im_2) {
                                $db->query("INSERT INTO im SET iuser_id = '" . $for_user_id . "', im_user_id = '" . $user_id . "', msg_num = 1, idate = '" . $server_time . "', all_msg_num = 1");
                            }
                            else {
                                $db->query("UPDATE im  SET idate = '" . $server_time . "', msg_num = msg_num+1, all_msg_num = all_msg_num+1 WHERE iuser_id = '" . $for_user_id . "' AND im_user_id = '" . $user_id . "'");
                            }

                            //Читисм кеш обновлений
//                            Cache::mozg_clear_cache_file('user_'.$for_user_id.'/im');
//                            Cache::mozg_create_cache('user_'.$for_user_id.'/im_update', '1');

                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'users');
                            $cache->remove("{$for_user_id}/im");
                            $cache->save("{$for_user_id}/im_update", 1);

                            $config = Settings::load();

                            $test = false;
                            //Отправка уведомления на E-mail
                            if($config['news_mail_8'] == 'yes' AND $user_id != $for_user_id AND $test == true){
                                $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$for_user_id."'");
                                if($rowUserEmail['user_email']){
                                    include_once __DIR__.'/../Classes/mail.php';
                                    $mail = new \dle_mail($config);
                                    $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
                                    $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '8'");
                                    $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                                    $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                                    $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'messages/show/'.$dbid, $rowEmailTpl['text']);
                                    $mail->send($rowUserEmail['user_email'], 'Новое персональное сообщение', $rowEmailTpl['text']);
                                }
                            }

                        }

                        $status = Status::OK;
                    } else {
                        $status = Status::PRIVACY;
                    }
                } else {
                    $status = Status::NOT_USER;
                }
            }
            else {
                //TODO update to OWNER
                $status = Status::NOT_DATA;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Страница отправки
     *
     * @return int
     */
    public function index(): int
    {
//        $tpl = $params['tpl'];
        $params = array();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            //Выводим сообщества
            $sql_ = $db->super_query("SELECT id, title FROM `communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT 0, 50", 1);
            //Выводим список друзей
            $sql_fr = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 ORDER by `views` DESC LIMIT 0, 50", 1);
            $groups_list = '';
            if($sql_){
                foreach($sql_ as $row) {
                    $groups_list .= '<option value="' . $row['id'] . '">' . stripslashes($row['title']) . '</option>';
                }
            }
            $params['groups_list'] = $groups_list;
            $friends_list = '';
            if($sql_fr){
                foreach($sql_fr as $row_fr) {
                    $friends_list .= '<option value="' . $row_fr['friend_id'] . '">' . $row_fr['user_search_pref'] . '</option>';
                }
            }
            $params['friends_list'] = $friends_list;
            if(!$friends_list) {
                $params['disabled_friends'] = 'disabled';
            }
            else {
//                $tpl->set('{disabled-friends}', '');
                $params['disabled_friends'] = '';
            }
            if(!$groups_list) {
                $params['groups_friends'] = 'disabled';
            }
            else {
                $params['groups_friends'] = '';
            }
            return view('repost.send', $params);
        }
        return view('info.info', $params);
    }
}