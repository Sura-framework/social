<?php

namespace App\Modules;

use Exception;
use Sura\Libs\Status;
use Sura\Libs\Tools;

class RatingController extends Module{

    /**
     * view
     *
     * @return int
     * @throws \JsonException
     */
    public function view(): int
    {
        Tools::NoAjaxRedirect();
        $logged = $this->logged();
        if($logged){
            $db = $this->db();
            $user_info = $this->user_info();
            $limit_news = 10;

            if($_POST['page_cnt'] > 0) $page_cnt = (int)$_POST['page_cnt'] * $limit_news;
            else $page_cnt = 0;

            $params = array();

            //Выводим список
            $sql_ = $db->super_query("SELECT tb1.user_id, addnum, date, tb2.user_search_pref, user_photo FROM `users_rating` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND for_user_id = '{$user_info['user_id']}' ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_news}", 1);
            if($sql_){
                foreach($sql_ as $key => $row){
                    if($row['user_photo'])
                        $sql_[$key]['ava'] = "/uploads/users/{$row['user_id']}/50_{$row['user_photo']}";
                    else
                        $sql_[$key]['ava'] = "/images/no_ava_50.png";
                    $sql_[$key]['rate'] = $row['addnum'];
                    $date = \Sura\Time\Date::megaDate($row['date']);
                    $sql_[$key]['date'] = $date;
                }
                $params['users'] = $sql_;
            }
                $row =  view_data('profile.rating.view', $params);

            return _e_json(array(
                'content' => $row,
            ) );
        }
        return view('info.info', $params);
    }

    /**
     * add
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function add(): int
    {
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
                    $server_time = \Sura\Time\Date::time();
                    $db->query("INSERT INTO `users_rating` SET user_id = '{$user_id}', for_user_id = '{$for_user_id}', addnum = '{$num}', date = '{$server_time}'");

                    /** Чистим кеш */
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$for_user_id}/user_{$for_user_id}");

                    $status = Status::OK;
                }else{
                    $status = Status::NOT_MONEY;
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
     * index
     *
     * @param $params
     * @return int
     * @throws \JsonException
     */
    public function index($params): int
    {
        Tools::NoAjaxRedirect();
        $logged = $this->logged();
        if($logged){
            $db = $this->db();
            $user_info = $this->user_info();
            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            $params['user_id'] = (int)$_POST['for_user_id'];
            $params['num'] = $row['user_balance']-1;
            $params['balance'] = $row['user_balance'];
            $row = view_data('profile.rating.main', $params);

            return _e_json(array(
                'content' => $row,
            ) );
        }else
            return view('info.info', $params);
    }
}