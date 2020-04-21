<?php
/* 
	Appointment: Сообщества -> Обсуждения
	File: groups_forum.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Gramatic;
use System\Libs\Validation;

class Groups_forum extends Module{

    /**
     * Отправка темы в БД
     */
    public function new_send($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $public_id = intval($_POST['public_id']);
            $title = Validation::ajax_utf8(Validation::textFilter($_POST['title'], false, true));
            $attach_files = Validation::ajax_utf8(Validation::textFilter($_POST['attach_files'], false, true));
            $text = Validation::ajax_utf8(Validation::textFilter($_POST['text']));

            $row = $db->super_query("SELECT ulist, discussion FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if(stripos($row['ulist'], "|{$user_id}|") !== false AND $row['discussion'] AND isset($title) AND !empty($title) AND isset($text) AND !empty($text) OR isset($attach_files) AND !empty($attach_files)){

                //Вставляем тему в БД
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_communities_forum` SET public_id = '{$public_id}', fuser_id = '{$user_id}', title = '{$title}', text = '{$text}', attach = '{$attach_files}', fdate = '{$server_time}', lastuser_id = '{$user_id}', lastdate = '{$server_time}', msg_num = 1");
                $dbid = $db->insert_id();

                //Обновляем кол-во тем в сообществе
                $db->query("UPDATE `".PREFIX."_communities` SET forum_num = forum_num+1 WHERE id = '{$public_id}'");

                Cache::mozg_clear_cache_file("groups_forum/forum{$public_id}");

                echo $dbid;

            }

            exit();
        }
    }

    /**
     * Страница создания новой темы
     */
    public function new($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $public_id = intval($_GET['public_id']);

            $row = $db->super_query("SELECT ulist, discussion FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if(stripos($row['ulist'], "|{$user_id}|") !== false AND $row['discussion']){

                $tpl->load_template('forum/new.tpl');
                $tpl->set('{id}', $public_id);
                $tpl->compile('content');

            } else
                msgbox('', '<br /><br />Ошибка доступа.<br /><br /><br />', 'info_2');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Добавления сообщения к теме
     */
    public function add_msg($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);
            $answer_id = intval($_POST['answer_id']);
            $msg = Validation::ajax_utf8(Validation::textFilter($_POST['msg']));

            $row = $db->super_query("SELECT status, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if($row AND !$row['status'] AND $row2['discussion']){

                //Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
                if($answer_id){

                    //Выводим ид владельца комменатрия
                    $row_owner2 = $db->super_query("SELECT muser_id, msg FROM `".PREFIX."_communities_forum_msg` WHERE mid = '{$answer_id}'");

                    //Проверка на то, что юзер не отвечает сам себе
                    if($user_id != $row_owner2['muser_id'] AND $row_owner2){

                        $row_owner2['msg'] = $db->safesql($row_owner2['msg']);

                        $check2 = $db->super_query("SELECT user_last_visit, user_name FROM `".PREFIX."_users` WHERE user_id = '{$row_owner2['muser_id']}'");

                        $msg = str_replace($check2['user_name'], "<a href=\"/u{$row_owner2['muser_id']}\" onClick=\"Page.Go(this.href); return false\">{$check2['user_name']}</a>", $msg);

                        //Всталвяем саму запись в БД
                        $server_time = intval($_SERVER['REQUEST_TIME']);
                        $db->query("INSERT INTO `".PREFIX."_communities_forum_msg` SET fid = '{$fid}', muser_id = '{$user_id}', msg = '{$msg}', mdate = '{$server_time}'");
                        $dbid = $db->insert_id();

                        //Вставляем в ленту новостей
                        $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$msg}', obj_id = '{$dbid}', for_user_id = '{$row_owner2['muser_id']}', action_time = '{$server_time}', answer_text = '{$row_owner2['msg']}', link = '/forum{$row['public_id']}?act=view&id={$fid}'");

                        //Вставляем событие в моментальные оповещания
                        $update_time = $server_time - 70;

                        if($check2['user_last_visit'] >= $update_time){

                            $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row_owner2['muser_id']}', from_user_id = '{$user_id}', type = '6', date = '{$server_time}', text = '{$msg}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/forum{$row['public_id']}?act=view&id={$fid}'");

                            Cache::mozg_create_cache("user_{$row_owner2['muser_id']}/updates", 1);

                            //ИНАЧЕ Добавляем +1 юзеру для оповещания
                        } else {

                            $cntCacheNews = Cache::mozg_cache("user_{$row_owner2['muser_id']}/new_news");
                            Cache::mozg_create_cache("user_{$row_owner2['muser_id']}/new_news", ($cntCacheNews+1));

                        }

                    }

                } else {

                    //Всталвяем саму запись в БД
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $db->query("INSERT INTO `".PREFIX."_communities_forum_msg` SET fid = '{$fid}', muser_id = '{$user_id}', msg = '{$msg}', mdate = '{$server_time}'");
                    $dbid = $db->insert_id();

                }

                Cache::mozg_clear_cache_file("groups_forum/forum{$row['public_id']}");

                //Обновляем данные в теме
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("UPDATE `".PREFIX."_communities_forum` SET msg_num = msg_num+1, lastdate = '{$server_time}', lastuser_id = '{$user_id}' WHERE fid = '{$fid}'");

                $tpl->load_template('forum/msg.tpl');

                $msg = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $msg);

                $tpl->set('{text}', stripslashes($msg));
                $tpl->set('{name}', $user_info['user_search_pref']);
                $tpl->set('{online}', $lang['online']);
                $tpl->set('{mid}', $dbid);
                $tpl->set('{user-id}', $user_info['user_id']);
                $tpl->set('{date}', langdate('сегодня в H:i', $server_time));
                $tpl->set('[admin-2]', '');
                $tpl->set('[/admin-2]', '');
                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                if($user_info['user_photo'])
                    $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
                else
                    $tpl->set('{ava}', '/images/no_ava_50.png');

                $tpl->compile('content');

                Tools::AjaxTpl($tpl);

            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Показах предыдущих сообщений
     */
    public function prev_msg($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $id = intval($_POST['fid']);
            $pid = intval($_POST['pid']);

            //SQL запрос на вывод
            $row = $db->super_query("SELECT msg_num, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$id}'");

            //Выводим данные о сообществе
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");
            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            $limit = 10;

            $first_id = intval($_POST['first_id']);
            $page_post = intval($_POST['page']);
            if($page_post <= 0) $page_post = 1;

            $start_limit = $row['msg_num']-($page_post*$limit)-10;
            if($start_limit < 0) $start_limit = 0;

            $sql_ = $db->super_query("SELECT tb1.mid, muser_id, msg, mdate, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_communities_forum_msg` tb1, `".PREFIX."_users` tb2 WHERE tb1.muser_id = tb2.user_id AND tb1.fid = '{$id}' AND mid < '{$first_id}' ORDER by `mdate` ASC LIMIT {$start_limit}, {$limit}", 1);

            if($sql_ AND $row2['discussion']){

                $tpl->load_template('forum/msg.tpl');

                foreach($sql_ as $row_comm){

                    $tpl->set('{name}', $row_comm['user_search_pref']);

                    $row_comm['msg'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comm['msg']);

                    $tpl->set('{text}', stripslashes($row_comm['msg']));
                    $tpl->set('{user-id}', $row_comm['muser_id']);
                    $tpl->set('{mid}', $row_comm['mid']);
                    $date = megaDate(strtotime($row_comm['mdate']));
                    $tpl->set('{date}', $date);
                    $online = Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                    $tpl->set('{online}', $online);

                    //ADMIN 2
                    if($user_info['user_group'] == 1 OR $public_admin OR $row_comm['muser_id'] == $user_id){

                        $tpl->set('[admin-2]', '');
                        $tpl->set('[/admin-2]', '');

                    } else
                        $tpl->set_block("'\\[admin-2\\](.*?)\\[/admin-2\\]'si","");

                    if($row_comm['muser_id'] == $user_id){

                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                    } else {

                        $tpl->set('[not-owner]', '');
                        $tpl->set('[/not-owner]', '');

                    }

                    if($row_comm['user_photo'])
                        $tpl->set('{ava}', "/uploads/users/{$row_comm['muser_id']}/50_{$row_comm['user_photo']}");
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    $tpl->compile('content');

                }

            }

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Сохранение отред. данных темы
     */
    public function saveedit($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);
            $text = Validation::ajax_utf8(Validation::textFilter($_POST['text']));

            $row = $db->super_query("SELECT fuser_id, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id AND $row2['discussion']){

                $db->query("UPDATE `".PREFIX."_communities_forum` SET text = '{$text}' WHERE fid = '{$fid}'");

                echo $text;

            }

            exit();
        }
    }

    /**
     * Сохранение отред. названия
     */
    public function savetitle($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);
            $title = Validation::ajax_utf8(Validation::textFilter($_POST['title'], false, true));

            $row = $db->super_query("SELECT fuser_id, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false AND $row2['discussion'])
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id){

                $db->query("UPDATE `".PREFIX."_communities_forum` SET title = '{$title}' WHERE fid = '{$fid}'");

                Cache::mozg_clear_cache_file("groups_forum/forum{$row['public_id']}");

            }

            exit();
        }
    }

    /**
     * Фиксирование темы . закрипление
     */
    public function fix($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);

            $row = $db->super_query("SELECT fuser_id, public_id, fixed FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin AND $row2['discussion']){

                if(!$row['fixed']) $fixed = 1;
                else $fixed = 0;

                $db->query("UPDATE `".PREFIX."_communities_forum` SET fixed = '{$fixed}' WHERE fid = '{$fid}'");

                Cache::mozg_clear_cache_file("groups_forum/forum{$row['public_id']}");

            }

            exit();
        }
    }

    /**
     * Открытие - закрытие тему
     */
    public function status($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);

            $row = $db->super_query("SELECT fuser_id, public_id, status FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin AND $row2['discussion']){

                if(!$row['status']) $status = 1;
                else $status = 0;

                $db->query("UPDATE `".PREFIX."_communities_forum` SET status = '{$status}' WHERE fid = '{$fid}'");

            }

            exit();
        }
    }

    /**
     *  Уадаление темы
     */
    public function del($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);

            $row = $db->super_query("SELECT fuser_id, public_id, vote FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id AND $row2['discussion']){

                $db->query("UPDATE `".PREFIX."_communities` SET forum_num = forum_num-1 WHERE id = '{$row['public_id']}'");
                $db->query("DELETE FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
                $db->query("DELETE FROM `".PREFIX."_communities_forum_msg` WHERE fid = '{$fid}'");

                $db->query("DELETE FROM `".PREFIX."_votes` WHERE id = '{$row['vote']}'");
                $db->query("DELETE FROM `".PREFIX."_votes_result` WHERE vote_id = '{$row['vote']}'");

                Cache::mozg_mass_clear_cache_file("votes/vote_{$row['vote']}|votes/vote_answer_cnt_{$row['vote']}|groups_forum/forum{$row['public_id']}");

            }

            exit();
        }
    }

    /**
     * Уадаление опроса
     */
    public function delvote($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);

            $row = $db->super_query("SELECT fuser_id, vote, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id AND $row2['discussion']){

                $db->query("UPDATE `".PREFIX."_communities_forum` SET vote = '0' WHERE fid = '{$fid}'");
                $db->query("DELETE FROM `".PREFIX."_votes` WHERE id = '{$row['vote']}'");
                $db->query("DELETE FROM `".PREFIX."_votes_result` WHERE vote_id = '{$row['vote']}'");

                Cache::mozg_mass_clear_cache_file("votes/vote_{$row['vote']}|votes/vote_answer_cnt_{$row['vote']}");

            }

            exit();
        }
    }

    /**
     * Уадаление сообщения
     */
    public function delmsg($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $mid = intval($_POST['mid']);

            $row = $db->super_query("SELECT muser_id, fid, mdate FROM `".PREFIX."_communities_forum_msg` WHERE mid = '{$mid}'");
            $row2 = $db->super_query("SELECT public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$row['fid']}'");
            $row3 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row2['public_id']}'");

            if(stripos($row3['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($row AND $user_info['user_group'] == 1 OR $public_admin OR $row['muser_id'] == $user_id AND $row3['discussion']){

                $db->query("UPDATE `".PREFIX."_communities_forum` SET msg_num = msg_num-1 WHERE fid = '{$row['fid']}'");
                $db->query("DELETE FROM `".PREFIX."_communities_forum_msg` WHERE mid = '{$mid}'");

                //Удаляем из ленты новостей
                $db->query("DELETE FROM `".PREFIX."_news` WHERE action_type = '6' AND obj_id = '{$mid}' AND action_time = '{$row['mdate']}'");

                Cache::mozg_clear_cache_file("groups_forum/forum{$row2['public_id']}");

            }

            exit();
        }
    }

    /**
     * Прикрипление опроса
     */
    public function createvote($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $fid = intval($_POST['fid']);

            $row = $db->super_query("SELECT fuser_id, public_id FROM `".PREFIX."_communities_forum` WHERE fid = '{$fid}'");
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row2['admin'], "u{$user_id}|") !== false)
                $public_admin = true;
            else
                $public_admin = false;

            if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id AND $row2['discussion']){

                //Голосование
                $vote_title = Validation::ajax_utf8(Validation::textFilter($_POST['vote_title'], false, true));
                $vote_answer_1 = Validation::ajax_utf8(Validation::textFilter($_POST['vote_answer_1'], false, true));

                $ansers_list = array();

                if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

                    for($vote_i = 1; $vote_i <= 10; $vote_i++){

                        $vote_answer = Validation::ajax_utf8(Validation::textFilter($_POST['vote_answer_'.$vote_i], false, true));
                        $vote_answer = str_replace('|', '&#124;', $vote_answer);

                        if($vote_answer)
                            $ansers_list[] = $vote_answer;

                    }

                    $sql_answers_list = implode('|', $ansers_list);

                    //Вставляем голосование в БД
                    $db->query("INSERT INTO `".PREFIX."_votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");

                    $db->query("UPDATE `".PREFIX."_communities_forum` SET vote = '{$db->insert_id()}' WHERE fid = '{$fid}'");

                }

            }

            exit();
        }
    }

    /**
     * Просмотр темы
     */
    public function view($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $public_id = intval($_GET['public_id']);
            $id = intval($_GET['id']);

            //Выводим данные о теме
            $row = $db->super_query("SELECT tb1.fid, fixed, title, text, status, fdate, fuser_id, attach, vote, msg_num, public_id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_communities_forum` tb1, `".PREFIX."_users` tb2 WHERE tb1.fid = '{$id}' AND tb1.fuser_id = tb2.user_id");

            //Выводим данные о сообществе
            $row2 = $db->super_query("SELECT admin, discussion FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if($row AND $row2['discussion']){

                if(stripos($row2['admin'], "u{$user_id}|") !== false)
                    $public_admin = true;
                else
                    $public_admin = false;

                //Выводимо сообщения к теме
                if($row['msg_num'] > 1){

                    //Выводим комменты
                    $limit_msg = 10;
                    if($row['msg_num'] >= 10) $sLimit_msg = $row['msg_num']-$limit_msg;
                    else $sLimit_msg = 0;

                    $sql_ = $db->super_query("SELECT tb1.mid, muser_id, msg, mdate, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_communities_forum_msg` tb1, `".PREFIX."_users` tb2 WHERE tb1.muser_id = tb2.user_id AND tb1.fid = '{$id}' ORDER by `mdate` ASC LIMIT {$sLimit_msg}, {$limit_msg}", 1);

                    $tpl->load_template('forum/msg.tpl');

                    foreach($sql_ as $row_comm){

                        $tpl->set('{name}', $row_comm['user_search_pref']);

                        $row_comm['msg'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comm['msg']);

                        $tpl->set('{text}', stripslashes($row_comm['msg']));
                        $tpl->set('{mid}', $row_comm['mid']);
                        $tpl->set('{user-id}', $row_comm['muser_id']);

                        $date = megaDate(strtotime($row_comm['mdate']));
                        $tpl->set('{date}', $date);
                        $online = Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                        $tpl->set('{online}', $online);

                        if($row_comm['user_photo'])
                            $tpl->set('{ava}', "/uploads/users/{$row_comm['muser_id']}/50_{$row_comm['user_photo']}");
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        //ADMIN 2
                        if($user_info['user_group'] == 1 OR $public_admin OR $row_comm['muser_id'] == $user_id){

                            $tpl->set('[admin-2]', '');
                            $tpl->set('[/admin-2]', '');

                        } else
                            $tpl->set_block("'\\[admin-2\\](.*?)\\[/admin-2\\]'si","");

                        if($row_comm['muser_id'] == $user_id){

                            $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                        } else {

                            $tpl->set('[not-owner]', '');
                            $tpl->set('[/not-owner]', '');

                        }

                        $tpl->compile('msg');

                    }

                }

                //Кнопка показ пред сообщений
                if($row['msg_num'] > 10){

                    $tpl->set('[msg]', '');
                    $tpl->set('[/msg]', '');

                } else
                    $tpl->set_block("'\\[msg\\](.*?)\\[/msg\\]'si","");

                $tpl->load_template('forum/view.tpl');

                $tpl->set('{id}', $public_id);
                $tpl->set('{fid}', $row['fid']);
                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{edit-text}', stripslashes(myBrRn($row['text'])));

                //Прикрипленные файлы
                if($row['attach']){
                    $attach_arr = explode('||', $row['attach']);
                    $cnt_attach = 1;
                    $cnt_attach_link = 1;
                    $jid = 0;
                    $attach_result = '';
                    foreach($attach_arr as $attach_file){
                        $attach_type = explode('|', $attach_file);

                        //Фото со стены юзера
                        if($attach_type[0] == 'photo_u'){
                            $attauthor_user_id = $row['fuser_id'];

                            if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                $attach_result .= "<img id=\"photo_wall_{$row['fid']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['fid']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['fid']}\" height=\"{$rodImHeigh}\" />";


                                $cnt_attach++;


                            } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                $attach_result .= "<img id=\"photo_wall_{$row['fid']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['fid']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['fid']}\" />";

                                $cnt_attach++;
                            }

                            //Видео
                        } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){
                            $attach_result .= "<div class=\"clear\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                            //Музыка
                        } elseif($attach_type[0] == 'audio'){
                            $audioId = intval($attach_type[1]);
                            $audioInfo = $db->super_query("SELECT artist, name, url FROM `".PREFIX."_audio` WHERE aid = '".$audioId."'");
                            if($audioInfo){

                                $jid++;

                                $attach_result .= '<div class="audioForSize'.$row['fid'].' clear" style="width:690px;float:none" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['fid'].')" id="icPlay_'.$row['fid'].$jid.'"></div><div id="music_'.$row['fid'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'&n=1" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['name']).'</div><div id="play_time'.$row['fid'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall" style="width:690px" id="ppbarPro'.$row['fid'].$jid.'"></div></div></div>';

                            }

                            //Смайлик
                        } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                            $attach_result_smiles .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                            //Если документ
                        } elseif($attach_type[0] == 'doc'){

                            $doc_id = intval($attach_type[1]);

                            $row_doc = $db->super_query("SELECT dname, dsize FROM `".PREFIX."_doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                            if($row_doc){

                                $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['fid'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['fid'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear" style="margin-bottom:5px"></div>';

                                $cnt_attach++;
                            }

                        } else

                            $attach_result .= '';

                    }

                    if($attach_result OR $attach_result_smiles)
                        $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']).'<span id="attach">'.$attach_result_smiles.'<div class="clear"></div>'.$attach_result.'</span>';
                    else
                        $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);
                } else
                    $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);

                $tpl->set('{text}', stripslashes($row['text']));

                $tpl->set('{name}', $row['user_search_pref']);
                $tpl->set('{user-id}', $row['fuser_id']);
                $tpl->set('{my-uid}', $user_id);
                $tpl->set('{msg-num}', $row['msg_num']);
                $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                $tpl->set('{online}', $online);
                $date = megaDate(strtotime($row['fdate']));
                $tpl->set('{date}', $date);
                if($row['user_photo'])
                    $tpl->set('{ava}', "/uploads/users/{$row['fuser_id']}/50_{$row['user_photo']}");
                else
                    $tpl->set('{ava}', '/images/no_ava_50.png');

                if($user_info['user_photo'])
                    $tpl->set('{my-ava}', "/uploads/users/{$user_id}/50_{$user_info['user_photo']}");
                else
                    $tpl->set('{my-ava}', '/images/no_ava_50.png');

                $tpl->set('{msg}', $tpl->result['msg']);

                //FIXED
                if($row['fixed']) $tpl->set('{fix-text}', 'Не закреплять тему');
                else $tpl->set('{fix-text}', 'Закрепить тему');

                //STATUS
                if($row['status']){

                    $tpl->set('{status-text}', 'Открыть тему');
                    $tpl->set('[add-form]', '');
                    $tpl->set('[/add-form]', '');

                } else {

                    $tpl->set('{status-text}', 'Закрыть тему');
                    $tpl->set_block("'\\[add-form\\](.*?)\\[/add-form\\]'si","");

                }

                //ADMIN
                if($public_admin OR $user_info['user_group'] == 1){

                    $tpl->set('[admin]', '');
                    $tpl->set('[/admin]', '');

                } else
                    $tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");

                //ADMIN 2
                if($user_info['user_group'] == 1 OR $public_admin OR $row['fuser_id'] == $user_id){

                    $tpl->set('[admin-2]', '');
                    $tpl->set('[/admin-2]', '');

                } else
                    $tpl->set_block("'\\[admin-2\\](.*?)\\[/admin-2\\]'si","");

                //Опрос
                $vote_id = $row['vote'];
                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `".PREFIX."_votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                if($row_vote){

                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                    $row_vote['title'] = stripslashes($row_vote['title']);

                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                    $max = $row_vote['answer_num'];

                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
                    $answer = array();
                    foreach($sql_answer as $row_answer){

                        $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                    }

                    $vote_result .= "<div class=\"clear\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}<div class=\"fl_r\"><a href=\"\" style=\"font-weight:normal\" onClick=\"Forum.VoteDelBox({$row['fid']}); return false\">Удалить опрос</a></div></div>";

                    for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                        if(!$checkMyVote['cnt']){

                            $vote_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                        } else {

                            $num = $answer[$ai]['cnt'];

                            if(!$num ) $num = 0;
                            if($max != 0) $proc = (100 * $num) / $max;
                            else $proc = 0;
                            $proc = round($proc, 2);

                            $vote_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
							{$arr_answe_list[$ai]}<br />
							<div class=\"wall_vote_proc fl_l\"><div class=\"wall_vote_proc_bg\" style=\"width:".intval($proc)."%\"></div><div style=\"margin-top:-16px\">{$num}</div></div>
							<div class=\"fl_l\" style=\"margin-top:-1px\"><b>{$proc}%</b></div>
							</div><div class=\"clear\"></div>";

                        }

                    }

                    $titles = array('человек', 'человека', 'человек');//fave
                    if($row_vote['answer_num']) $answer_num_text = Gramatic::declOfNum($row_vote['answer_num'], $titles);
                    else $answer_num_text = 'человек';

                    if($row_vote['answer_num'] <= 1) $answer_text2 = 'Проголосовал';
                    else $answer_text2 = 'Проголосовало';

                    $vote_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"clear\" style=\"margin-top:10px\"></div></div>";

                    $tpl->set('{vote}', $vote_result);
                    $tpl->set('{vote-link}', '');

                } else {

                    $tpl->set('{vote}', '');
                    $tpl->set('{vote-link}', '<div class="sett_hover" onClick="settings.privacyClose(\'msg\'); $(\'#attach_block_vote\').slideDown(100); $(\'#vote_title\').focus()">Прикрепить опрос</div>');

                }

                $tpl->compile('content');

            } else
                msgbox('', '<br /><br />Тема не найдена.<br /><br /><br />', 'info_2');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }


    /**
     * Вывод всех обсуждений в сообществе
     */
    public function index($params)
    {
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = $_POST['ajax'];
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            //Если вызвана Forum.Page()
            if($_POST['a'])
                Tools::NoAjaxQuery();

            $public_id = intval($_GET['public_id']);

            $row = $db->super_query("SELECT forum_num, discussion, ulist FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if($row['discussion']){

                //Верхушка
                if(!$_POST['a']){
                    $tpl->load_template('forum/head.tpl');
                    $tpl->set('{id}', $public_id);
                    if(!$row['forum_num']) $row['forum_num'] = '';
                    $tpl->set('{forum-num}', $row['forum_num']);
                    $forum_num = $row['forum_num'];

                    //Проверка подписан юзер или нет
                    if(stripos($row['ulist'], "|{$user_id}|") !== false)
                        $tpl->set('{yes}', 'no_display');
                    else
                        $tpl->set('{no}', 'no_display');

                    $tpl->compile('info');
                }

                //SQL запрос на вывод
                $limit = 20;
                $page_post = intval($_POST['page']);
                if($page_post > 0)
                    $page = $page_post*$limit;
                else
                    $page = 0;

                $sql_ = $db->super_query("SELECT fid, title, lastuser_id, lastdate, msg_num, status, fixed FROM `".PREFIX."_communities_forum` WHERE public_id = '{$public_id}' ORDER by `fixed` DESC, `lastdate` DESC, `fdate` DESC LIMIT {$page}, {$limit}", 1);

                if($sql_){

                    $tpl->load_template('forum/theme.tpl');
                    foreach($sql_ as $row){

                        $row_last_user = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$row['lastuser_id']}'");
                        $last_userX = explode(' ', $row_last_user['user_search_pref']);
                        $row_last_user['user_search_pref'] = Gramatic::gramatikName($last_userX[0]).' '.Gramatic::gramatikName($last_userX[1]);

                        $tpl->set('{name}', $row_last_user['user_search_pref']);

                        $titles = array('сообщение', 'сообщения', 'сообщений');//msg
                        $tpl->set('{msg-num}', '<b>'.$row['msg_num'].'</b> '.Gramatic::declOfNum($row['msg_num'], $titles));
                        $tpl->set('{title}', stripslashes($row['title']));
                        $tpl->set('{fid}', $row['fid']);
                        $tpl->set('{user-id}', $row['lastuser_id']);
                        $tpl->set('{pid}', $public_id);

                        //STATUS
                        if($row['status'] AND $row['fixed']) $tpl->set('{status}', 'тема закреплена и закрыта');
                        else if($row['status']) $tpl->set('{status}', 'тема закрыта');
                        else if($row['fixed']) $tpl->set('{status}', 'тема закреплена');
                        else $tpl->set('{status}', '');

                        $date = megaDate(strtotime($row['lastdate']));
                        $tpl->set('{date}', $date);

                        $tpl->compile('content');

                    }

                } else
                    if(!$_POST['a'])
                        msgbox('', '<br /><br />В сообществе ещё нет тем.<br /><br /><br />', 'info_2');

                //Низ
                if(!$_POST['a'] AND $forum_num > 20){
                    $tpl->load_template('forum/bottom.tpl');
                    $tpl->set('{id}', $public_id);
                    if(!$row['forum_num']) $row['forum_num'] = '';
                    $tpl->set('{forum-num}', $row['forum_num']);
                    $tpl->compile('content');
                }

                //Если вызвана Forum.Page()
                if($_POST['a']){

                    Tools::AjaxTpl($tpl);
                    exit();

                }

            } else
                if(!$_POST['a'])
                    msgbox('', '<br /><br />Ошибка доступа.<br /><br /><br />', 'info_2');

            $tpl->clear();
            $db->free();

        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}