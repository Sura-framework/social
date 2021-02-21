<?php

namespace App\Modules;

use App\Libs\Friends;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class SubscriptionsController extends Module{

    /**
     * Добвление юзера в подписки
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function add(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];

            $for_user_id = intval($request['for_user_id']);

            //Проверка на существование юзера в подписках
            $check = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 1");

            //ЧС
            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($check['user_id']);

            if ($for_user_id != $user_id){
                if (!$check){
                    if(!$CheckBlackList){
                        $db->query("INSERT INTO `friends` SET user_id = '{$user_id}', friend_id = '{$for_user_id}', friends_date = NOW(), subscriptions = 1");
                        $db->query("UPDATE `users` SET user_subscriptions_num = user_subscriptions_num+1 WHERE user_id = '{$user_id}'");

                        //Вставляем событие в моментальные оповещания
                        $row_owner = $db->super_query("SELECT user_last_visit, user_sex FROM `users` WHERE user_id = '{$for_user_id}'");

                        $server_time = \Sura\Time\Date::time();

                        $update_time = $server_time - 70;

                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');

                        if($row_owner['user_last_visit'] >= $update_time){

                            $myRow = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_info['user_id']}'");

                            if($myRow['user_sex'] == 1)
                                $action_update_text = 'подписался на Ваши обновления.';
                            else
                                $action_update_text = 'подписалась на Ваши обновления.';

                            $db->query("INSERT INTO `updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_info['user_id']}', type = '13', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/u{$user_info['user_id']}'");

                            $cache->save("{$for_user_id}/updates", 1);
                        }

                        //Чистим кеш
                        $cache->remove("{$user_id}/profile_{$user_id}");
                        $cache->remove("{$user_id}/subscr");

                        $status = Status::OK;
                    }else{
                        $status = Status::BLACKLIST;
                    }
                }else{
                    $status = Status::SUBSCRIPTION;
                }
            }else{
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
     * Удаление юзера из подписок
     *
     * @throws \Throwable
     */
    public function del(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $del_user_id = intval($request['del_user_id']);

            //Проверка на существование юзера в подписках
            $check = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
            if($check){
                $db->query("DELETE FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$del_user_id}' AND subscriptions = 1");
                $db->query("UPDATE `users` SET user_subscriptions_num = user_subscriptions_num-1 WHERE user_id = '{$user_id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');

                //Чистим кеш
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache->remove("{$user_id}/subscr");

                $status = Status::OK;
            }else{
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
     * Показ всех подписок юзера
     *
     * @return int
     */
    public function index(): int
    {
        $tpl = $params['tpl'];

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

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
                $config = Settings::load();
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