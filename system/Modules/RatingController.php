<?php
/* 
	Appointment: Рейтинг
	File: raing.php 
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
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;

class RatingController extends Module{

    public function view($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            //$act = $_GET['act'];

            $limit_news = 10;

            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt']) * $limit_news;
            else $page_cnt = 0;

            //Выводим список
            $sql_ = $db->super_query("SELECT tb1.user_id, addnum, date, tb2.user_search_pref, user_photo FROM `".PREFIX."_users_rating` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND for_user_id = '{$user_id}' ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_news}", 1);

            if($sql_){

                $i = 0;

                $tpl->load_template('rating/user.tpl');
                foreach($sql_ as $row){

                    $i++;

                    if($row['user_photo']) $tpl->set('{ava}', "/uploads/users/{$row['user_id']}/50_{$row['user_photo']}");
                    else $tpl->set('{ava}', "/images/no_ava_50.png");

                    $tpl->set('{user-id}', $row['user_id']);
                    $tpl->set('{name}', $row['user_search_pref']);
                    $tpl->set('{rate}', $row['addnum']);

                    $date = megaDate(strtotime($row['date']));
                    $tpl->set('{date}', $date);

                    $tpl->compile('users');

                }

            } else
                if(!$page_cnt)
                    $tpl->result['users'] = '<div class="info_center"><br /><br />Пока что никто не повышал Ваш рейтинг.<br /><br /><br /></div>';

            if(!$page_cnt){

                $tpl->load_template('rating/view.tpl');
                $tpl->set('{users}', $tpl->result['users']);

                if($i == 10){

                    $tpl->set('[prev]', '');
                    $tpl->set('[/prev]', '');

                } else
                    $tpl->set_block("'\\[prev\\](.*?)\\[/prev\\]'si","");

                $tpl->compile('content');

            } else
                $tpl->result['content'] = $tpl->result['users'];

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }
    public function add($params){
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];
            $act = $_GET['act'];

            $for_user_id = intval($_POST['for_user_id']);
            $num = intval($_POST['num']);
            if($num < 0) $num = 0;

            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            //Проверка что такой юзер есть
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");

            if($row['user_balance'] < 0) $row['user_balance'] = 0;

            if($check['cnt'] AND $num > 0){

                if($row['user_balance'] >= $num){

                    //Обновляем баланс у того кто повышал
                    $db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance - {$num} WHERE user_id = '{$user_id}'");

                    //Начисляем рейтинг
                    $db->query("UPDATE `".PREFIX."_users` SET user_rating = user_rating + {$num} WHERE user_id = '{$for_user_id}'");

                    //Вставляем в лог
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $db->query("INSERT INTO `".PREFIX."_users_rating` SET user_id = '{$user_id}', for_user_id = '{$for_user_id}', addnum = '{$num}', date = '{$server_time}'");

                    //Чистим кеш
                    Cache::mozg_clear_cache_file("user_{$for_user_id}/profile_{$for_user_id}");

                } else
                    echo 1;

            } else
                echo 1;
        }
        die();
    }

    public function index($params){
        $tpl = Registry::get('tpl');

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxQuery();

        if($logged){

            $user_id = $user_info['user_id'];
            $act = $_GET['act'];

            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('rating/main.tpl');

            $tpl->set('{user-id}', intval($_POST['for_user_id']));

            $tpl->set('{num}', $row['user_balance']-1);
            $tpl->set('{balance}', $row['user_balance']);

            $tpl->compile('content');

            Tools::AjaxTpl($tpl);

        }
        $tpl->clear();
        $db->free();

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}