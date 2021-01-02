<?php

namespace App\Modules;

use App\Services\Cache;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class SubscriptionsController extends Module{

    /**
     * Добвление юзера в подписки
     *
     * @param $params
     */
    public function add($params){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $for_user_id = intval($request['for_user_id']);

            //Проверка на существование юзера в подписках
            $check = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 1");

            //ЧС
            $CheckBlackList = CheckBlackList($check['user_id']);

            if(!$CheckBlackList AND !$check AND $for_user_id != $user_id){
                $db->query("INSERT INTO `friends` SET user_id = '{$user_id}', friend_id = '{$for_user_id}', friends_date = NOW(), subscriptions = 1");
                $db->query("UPDATE `users` SET user_subscriptions_num = user_subscriptions_num+1 WHERE user_id = '{$user_id}'");

                //Вставляем событие в моментальные оповещания
                $row_owner = $db->super_query("SELECT user_last_visit, user_sex FROM `users` WHERE user_id = '{$for_user_id}'");

                $server_time = \Sura\Libs\Tools::time();

                $update_time = $server_time - 70;

                $Cache = cache_init(array('type' => 'file'));

                if($row_owner['user_last_visit'] >= $update_time){

                    $myRow = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_info['user_id']}'");

                    if($myRow['user_sex'] == 1)
                        $action_update_text = 'подписался на Ваши обновления.';
                    else
                        $action_update_text = 'подписалась на Ваши обновления.';

                    $db->query("INSERT INTO `updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_info['user_id']}', type = '13', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/u{$user_info['user_id']}'");

//                    Cache::mozg_create_cache("user_{$for_user_id}/updates", 1);

                    $Cache->set("users/{$for_user_id}/updates", 1);

                }


                //Чистим кеш
//                Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
//                Cache::mozg_clear_cache_file('subscr_user_'.$user_id);

                $Cache->delete("users/{$user_id}/profile_{$user_id}");
                $Cache->delete("users/{$user_id}/subscr");
            }
        }
    }

    /**
     * Удаление юзера из подписок
     *
     */
    public function del(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $del_user_id = intval($request['del_user_id']);

            $Cache = cache_init(array('type' => 'file'));

            //Проверка на существование юзера в подписках
            $check = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
            if($check){
                $db->query("DELETE FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
                $db->query("UPDATE `users` SET user_subscriptions_num = user_subscriptions_num-1 WHERE user_id = '{$user_id}'");

                //Чистим кеш
//                Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
//                Cache::mozg_clear_cache_file('subscr_user_'.$user_id);

                $Cache->delete("users/{$user_id}/profile_{$user_id}");
                $Cache->delete("users/{$user_id}/subscr");
            }
        }
    }

    /**
     * Показ всех подпискок юзера
     *
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function index($params): string
    {
        $tpl = $params['tpl'];

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            //################### Показ всех подпискок юзера ###################//
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;
            $for_user_id = intval($request['for_user_id']);
            $subscr_num = intval($request['subscr_num']);

            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
                $tpl->load_template('profile_subscription_box_top.tpl');
                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $titles = array('подписка', 'подписки', 'подписок');//subscr
                $tpl->set('{subcr-num}', $subscr_num.' '.Gramatic::declOfNum($subscr_num, $titles));
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('content');

                $tpl->load_template('profile_friends.tpl');
                $config = Settings::loadsettings();
                foreach($sql_ as $row){
                    if($row['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    $friend_info_online = explode(' ', $row['user_search_pref']);
                    $tpl->set('{user-id}', $row['friend_id']);
                    $tpl->set('{name}', $friend_info_online[0]);
                    $tpl->set('{last-name}', $friend_info_online[1]);
                    $tpl->compile('content');
                }
//                box_navigation($gcount, $subscr_num, $for_user_id, 'subscriptions.all', $subscr_num);
            }
//            Tools::AjaxTpl($tpl);
//            $tpl->clear();
//            $db->free();

            return view('info.info', $params);
        } else
            return view('info.info', $params);


    }
}