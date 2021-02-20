<?php

namespace App\Modules;

use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class GiftsController extends Module{

    /**
     * Страница всех подарков
     *
     */
    public function view(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();
            $for_user_id = (int)$request['user_id'];

            $sql_ = $db->super_query("SELECT gid, img, price FROM `gifts_list` ORDER by `gid` DESC", 1);

            $config = Settings::load();

            foreach($sql_ as $gift){

                if($config['temp'] == 'mobile')
                    echo "<a href=\"\" class=\"gifts_onegif\" onClick=\"gifts.select('{$gift['img']}', '{$for_user_id}'); return false\"><img src=\"/uploads/gifts/{$gift['img']}.png\"  alt=\"\"/><div class=\"gift_count\" id=\"g{$gift['img']}\">{$gift['price']} голос</div></a>";
                else
                    echo "<a href=\"\" class=\"gifts_onegif\" onMouseOver=\"gifts.showgift('{$gift['img']}')\" onMouseOut=\"gifts.showhide('{$gift['img']}')\" onClick=\"gifts.select('{$gift['img']}', '{$for_user_id}'); return false\"><img src=\"/uploads/gifts/{$gift['img']}.png\"  alt=\"\"/><div class=\"gift_count no_display\" id=\"g{$gift['img']}\">{$gift['price']} голос</div></a>";

            }

            $row = $db->super_query("SELECT user_balance FROM `users` WHERE user_id = '{$user_id}'");

            echo "<style>#box_bottom_left_text{padding-top:6px;float:left}</style><script>$('#box_bottom_left_text').html('У Вас <b>{$row['user_balance']} голос.</b>&nbsp;');</script><div class=\"clr\"></div>";
        }
    }

    /**
     * Отправка подарка в БД
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function send(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();
            $for_user_id = (int)$request['for_user_id'];
            $gift = (int)$request['gift'];
            $privacy = (int)$request['privacy'];
            if($privacy < 0 OR $privacy > 3) $privacy = 1;
            $msg = Validation::ajax_utf8(Validation::textFilter($request['msg']));
            $gifts = $db->super_query("SELECT price FROM `gifts_list` WHERE img = '".$gift."'");

            //Выводим текущий баланс свой
            $row = $db->super_query("SELECT user_balance FROM `users` WHERE user_id = '{$user_id}'");
            if($gifts['price'] AND $user_id != $for_user_id){
                if($row['user_balance'] >= $gifts['price']){
                    $server_time = \Sura\Time\Date::time();
                    $db->query("INSERT INTO `gifts` SET uid = '{$for_user_id}', gift = '{$gift}', msg = '{$msg}', privacy = '{$privacy}', gdate = '{$server_time}', from_uid = '{$user_id}', status = 1");
                    $db->query("UPDATE `users` SET user_balance = user_balance-{$gifts['price']} WHERE user_id = '{$user_id}'");
                    $db->query("UPDATE `users` SET user_gifts = user_gifts+1 WHERE user_id = '{$for_user_id}'");

                    //Вставляем событие в моментальные оповещания
                    $check2 = $db->super_query("SELECT user_last_visit, notifications_list FROM `users` WHERE user_id = '{$for_user_id}'");

                    $update_time = $server_time - 70;

                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');

                    if($check2['user_last_visit'] >= $update_time){

                        if($privacy == 3){

                            $user_info['user_photo'] = '';
                            $user_info['user_search_pref'] = 'Неизвестный отправитель';
                            $from_user_id = $for_user_id;

                        } else
                            $from_user_id = $user_id;

                        $action_update_text = "<img src=\"/uploads/gifts/{$gift}.png\" width=\"50\" align=\"right\"  alt=\"\"/>{$msg}";

                        $db->query("INSERT INTO `updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$from_user_id}', type = '7', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/gifts{$for_user_id}?new=1'");

                        $cache->remove("{$for_user_id}/updates");
                        //ИНАЧЕ Добавляем +1 юзеру для оповещания
                    } else {
                        $cntCacheNews = $cache->load("{$for_user_id}/new_gift");
                        $cache->save("{$for_user_id}/new_gift", $cntCacheNews+1);
                    }

                    $generateLastTime = $server_time-10800;
                    $row_news = $db->super_query("SELECT ac_id, action_text, action_time FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 21 AND obj_id = '{$gift}'");
                    if($row_news) {
                        $db->query("UPDATE `news` SET action_text = '|u{$user_info['user_id']}|{$row_news['action_text']}', action_time = '{$server_time}' WHERE obj_id = '{$gift}' AND action_type = 21 AND action_time = '{$row_news['action_time']}'");
                    } else {
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_info['user_id']}', action_type = 21, action_text = '|u{$user_info['user_id']}|', obj_id = '{$gift}', for_user_id = '{$for_user_id}', action_time = '{$server_time}'");
                    }
                    if(stripos($check2['notifications_list'], "settings_likes_gifts|") === false){
                        $cntCacheNews = $cache->load("{$for_user_id}/new_news");
                        $cache->save("{$for_user_id}/new_news", $cntCacheNews+1);
                    }

                    $cache->remove("{$for_user_id}/profile_{$for_user_id}");
                    $cache->remove("{$for_user_id}/gifts");

                    $config = Settings::load();

                    //Если цена подарка выше бонусного, то начисляем цену подарка на рейтинг
                    if($gifts['price'] > $config['bonus_rate']){

                        //Начисляем
                        $db->query("UPDATE `users` SET user_rating = user_rating + {$gifts['price']} WHERE user_id = '{$user_id}'");

                        //Чистим кеш
                        $cache->remove("{$user_id}/profile_{$user_id}");
                    }

                    //Отправка уведомления на E-mail
                    if($config['news_mail_6'] == 'yes'){
                        $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$for_user_id."'");
                        if($rowUserEmail['user_email']){
//                            include_once __DIR__.'/../Classes/mail.php';
//                            $mail = new \dle_mail($config);
                            $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
                            $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '6'");
                            $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                            $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                            $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'gifts'.$for_user_id, $rowEmailTpl['text']);
//                            $mail->send($rowUserEmail['user_email'], 'Вам отправили новый подарок', $rowEmailTpl['text']);
                        }
                    }

                    $status = Status::OK;
                } else {
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
     * Удаление подарка
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function del(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();
            $gid = (int)$request['gid'];
            $row = $db->super_query("SELECT uid FROM `gifts` WHERE gid = '{$gid}'");
            if($user_id == $row['uid']){
                $db->query("DELETE FROM `gifts` WHERE gid = '{$gid}'");
                $db->query("UPDATE `users` SET user_gifts = user_gifts-1 WHERE user_id = '{$user_id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache->remove("{$user_id}/gifts");

                $status = Status::OK;
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
     * Всех подарков пользователя
     *
     * @throws \Throwable
     */
    public function index(): int
    {
//        $tpl = $params['tpl'];

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

            $params['title'] = $lang['gifts'].' | Sura';
            $uid = (int)$request['uid'];

            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
            $gcount = 15;
            $limit_page = ($page-1)*$gcount;

            $owner = $db->super_query("SELECT user_name, user_gifts FROM `users` WHERE user_id = '{$uid}'");

//            $tpl->load_template('gifts/head.tpl');
//            $tpl->set('{uid}', $uid);
            if($user_id == $uid){
//                $tpl->set('[owner]', '');
//                $tpl->set('[/owner]', '');
//                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
            } else {
//                $tpl->set('[not-owner]', '');
//                $tpl->set('[/not-owner]', '');
//                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
            }
//            $tpl->set('{name}', Gramatic::gramatikName($owner['user_name']));

            $titles = array('подарок', 'подарка', 'подарков');//gifts
//            $tpl->set('{gifts-num}', '<span id="num">'.$owner['user_gifts'].'</span> '.Gramatic::declOfNum($owner['user_gifts'], $titles));
            if($owner['user_gifts']){
//                $tpl->set('[yes]', '');
//                $tpl->set('[/yes]', '');
//                $tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
            } else {
//                $tpl->set('[no]', '');
//                $tpl->set('[/no]', '');
//                $tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
            }

            if($request['new'] AND $user_id == $uid){
//                $tpl->set('[new]', '');
//                $tpl->set('[/new]', '');
//                $tpl->set_block("'\\[no-new\\](.*?)\\[/no-new\\]'si","");
                $sql_where = "AND status = 1";
                $gcount = 50;

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->save("{$user_id}/new_gift", '');
            } else {
                $sql_where = '';
//                $tpl->set('[no-new]', '');
//                $tpl->set('[/no-new]', '');
//                $tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si","");
            }

//            $tpl->compile('info');
            if($owner['user_gifts']){
                $sql_ = $db->super_query("SELECT tb1.gid, gift, from_uid, msg, gdate, privacy, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `gifts` tb1, `users` tb2 WHERE tb1.uid = '{$uid}' AND tb1.from_uid = tb2.user_id {$sql_where} ORDER by `gdate` DESC LIMIT {$limit_page}, {$gcount}", 1);
//                $tpl->load_template('gifts/gift.tpl');
                foreach($sql_ as $row){
//                    $tpl->set('{id}', $row['gid']);
//                    $tpl->set('{uid}', $row['from_uid']);
                    if($row['privacy'] == 1 OR $user_id == $row['from_uid'] OR $user_id == $uid AND $row['privacy'] != 3){
//                        $tpl->set('{author}', $row['user_search_pref']);
//                        $tpl->set('{msg}', stripslashes($row['msg']));
//                        $tpl->set('[link]', '<a href="/u'.$row['from_uid'].'" onClick="Page.Go(this.href); return false">');
//                        $tpl->set('[/link]', '</a>');
//                        $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
//                        $tpl->set('{online}', $online);
                    } else {
//                        $tpl->set('{author}', 'Неизвестный отправитель');
//                        $tpl->set('{msg}', '');
//                        $tpl->set('{online}', '');
//                        $tpl->set('[link]', '');
//                        $tpl->set('[/link]', '');
                    }
//                    $tpl->set('{gift}', $row['gift']);
                    $date = \Sura\Time\Date::megaDate(strtotime($row['gdate']), 1, 1);
//                    $tpl->set('{date}', $date);
//                    $tpl->set('[privacy]', '');
//                    $tpl->set('[/privacy]', '');
                    if($row['privacy'] == 3 AND $user_id == $uid){
//                        $tpl->set('{msg}', stripslashes($row['msg']));
//                        $tpl->set_block("'\\[privacy\\](.*?)\\[/privacy\\]'si","");
                    }
                    if($row['privacy'] == 1 OR $user_id == $row['from_uid'] OR $user_id == $uid AND $row['privacy'] != 3)
                        if($row['user_photo'])
                        {
//                            $tpl->set('{ava}', '/uploads/users/'.$row['from_uid'].'/50_'.$row['user_photo']);
                        }
                        else
                        {
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        }
                    else
                    {
//                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    }

                    if($user_id == $uid){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
                    } else
                    {
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }

                    if($sql_where)
                        $db->query("UPDATE `gifts` SET status = 0 WHERE gid = '{$row['gid']}'");

//                    $tpl->compile('content');
                }
//                navigation($gcount, $owner['user_gifts'], "/gifts{$uid}?page=");

                if($sql_where AND !$sql_)
                    msg_box('<br /><br />Новых подарков еще нет.<br /><br /><br />', 'info_2');
            }
//            $tpl->clear();
//            $db->free();
            return view('info.info', $params);
        }
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);

    }
}