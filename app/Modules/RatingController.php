<?php

namespace App\Modules;

use App\Services\Cache;
use Exception;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Tools;

class RatingController extends Module{

    /**
     * @param $params
     * @return string
     * @throws Exception
     */
    public function view($params){
        Tools::NoAjaxRedirect();
        $logged = $this->logged();
        if($logged){
            $db = $this->db();
            $user_info = $this->user_info();
            $limit_news = 10;

            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt']) * $limit_news;
            else $page_cnt = 0;

            //Выводим список
            $sql_ = $db->super_query("SELECT tb1.user_id, addnum, date, tb2.user_search_pref, user_photo FROM `users_rating` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND for_user_id = '{$user_info['user_id']}' ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_news}", 1);
            if($sql_){
                foreach($sql_ as $key => $row){
                    if($row['user_photo'])
                        $sql_[$key]['ava'] = "/uploads/users/{$row['user_id']}/50_{$row['user_photo']}";
                    else
                        $sql_[$key]['ava'] = "/images/no_ava_50.png";
                    $sql_[$key]['rate'] = $row['addnum'];
                    $date = megaDate(strtotime($row['date']));
                    $sql_[$key]['date'] = $date;
                }
                $params['users'] = $sql_;
            }
                return view('profile.rating.view', $params);
        }
    }

    /**
     *
     * @param $params
     */
    public function add($params){
        Tools::NoAjaxRedirect();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];
            if (!isset($_POST['for_user_id']) AND !isset($_POST['num']))
                die('1');

            $for_user_id = intval($_POST['for_user_id']);
            $num = intval($_POST['num']);
            if($num < 0) $num = 0;

            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            //Проверка что такой юзер есть
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$user_info['user_id']}'");

            if($row['user_balance'] < 0)
                $row['user_balance'] = 0;

            if($check['cnt'] AND $num > 0){
                if($row['user_balance'] >= $num){

                    //Обновляем баланс у того кто повышал
                    $db->query("UPDATE `users` SET user_balance = user_balance - {$num} WHERE user_id = '{$user_info['user_id']}'");

                    //Начисляем рейтинг
                    $db->query("UPDATE `users` SET user_rating = user_rating + {$num} WHERE user_id = '{$for_user_id}'");

                    //Вставляем в лог
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $db->query("INSERT INTO `users_rating` SET user_id = '{$user_id}', for_user_id = '{$for_user_id}', addnum = '{$num}', date = '{$server_time}'");

                    //Чистим кеш
//                    Cache::mozg_clear_cache_file("user_{$for_user_id}/profile_{$for_user_id}");

                    $Cache = Cache::initialize();
                    $Cache->delete("users/{$for_user_id}/user_{$for_user_id}");

                } else
                    echo 1;
            } else
                echo 1;
        }
        //TODO JSON output
    }

    /**
     * @param $params
     * @return false|string
     * @throws Exception
     */
    public function index($params){
        Tools::NoAjaxRedirect();
        $logged = $this->logged();
        if($logged){
            $db = $this->db();
            $user_info = $this->user_info();
            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            $params['user_id'] = intval($_POST['for_user_id']);
            $params['num'] = $row['user_balance']-1;
            $params['balance'] = $row['user_balance'];
            return view('profile.rating.main', $params);
        }else
            return false;
    }
}