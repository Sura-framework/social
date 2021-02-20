<?php

declare(strict_types=1);

namespace App\Modules;

use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Exception;
use Sura\Libs\Status;

class NotificationsController extends Module{

    /**
     * settings
     *
     */
    public function settings(): int
    {
//        $lang = $this->get_langs();
//        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $act = $request['act'];

            if(stripos($user_info['notifications_list'], "settings_likes_posts|") === false)
                $settings_likes_posts = 'html_checked';
            else
                $settings_likes_posts = '';
            if(stripos($user_info['notifications_list'], "settings_likes_photos|") === false)
                $settings_likes_photos = 'html_checked';
            else
                $settings_likes_photos = '';
            if(stripos($user_info['notifications_list'], "settings_likes_compare|") === false)
                $settings_likes_compare = 'html_checked';
            else
                $settings_likes_compare = '';
            if(stripos($user_info['notifications_list'], "settings_likes_gifts|") === false)
                $settings_likes_gifts = 'html_checked';
            else
                $settings_likes_gifts = '';

            return _e('<div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_posts");><i class="icn icn-gray icn-like"></i><span>Оценки записей</span> <div class="html_checkbox '.$settings_likes_posts.'" id="settings_likes_posts"></div></div>
            <div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_photos");><i class="icn icn-gray icn-like"></i><span>Оценки фотографий</span> <div class="html_checkbox '.$settings_likes_photos.'" id="settings_likes_photos"></div></div>
            <div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_compare");><i class="icn icn-gray icn-like"></i><span>Оценки в дуэлях</span> <div class="html_checkbox '.$settings_likes_compare.'" id="settings_likes_compare"></div></div>
            <div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_gifts");><i class="icn icn-gray icn-gift"></i><span>Новый подарок</span> <div class="html_checkbox '.$settings_likes_gifts.'" id="settings_likes_gifts"></div></div>');
        }else{
            return _e('');
        }
    }

    /**
     * save settings
     *
     * @return int
     * @throws \JsonException
     */
    public function save_settings(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $settings_likes_posts = intval($request['settings_likes_posts']);
            $settings_likes_photos = intval($request['settings_likes_photos']);
            $settings_likes_compare = intval($request['settings_likes_compare']);
            $settings_likes_gifts = intval($request['settings_likes_gifts']);
            $notifications_list = '';
            if($settings_likes_posts)
                $notifications_list .= '|settings_likes_posts|';
            if($settings_likes_photos)
                $notifications_list .= '|settings_likes_photos|';
            if($settings_likes_compare)
                $notifications_list .= '|settings_likes_compare|';
            if($settings_likes_gifts)
                $notifications_list .= '|settings_likes_gifts|';
            $db->super_query("UPDATE `users` SET notifications_list = '{$notifications_list}' WHERE user_id = '{$user_info['user_id']}'");

            $status = Status::OK;
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * del
     *
     * @return int
     * @throws \JsonException
     */
    public function del(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $id = intval($request['id']);
            if($id){
                $sql_ = $db->super_query("SELECT COUNT(*) as cnt FROM `news` WHERE ac_id = '{$id}' AND action_type IN (7,20,21,22) AND for_user_id = '{$user_info['user_id']}'");
                if($sql_){
                    $db->query("DELETE FROM `news` WHERE ac_id = '{$id}'");
                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
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
     * notification
     *
     * @return int
     */
    //FIXME full code upgrade
    public function notification(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $params = array();
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $id = (int)$request['id'];
            if($id){
                $row = $db->super_query("SELECT ac_id, action_text, action_time, action_type, obj_id FROM `news` WHERE ac_id = '{$id}' AND action_type IN (7,20,21,22) AND for_user_id = '{$user_info['user_id']}'");
                if($row){
                    $likesUseList = explode('|', str_replace('u', '', $row['action_text']));

//                    $tpl->load_template('news/notification3.tpl');
                    foreach($likesUseList as $key => $likeUser){
                        if($likeUser){
                            $rowUser = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$likeUser}'");
                            if($rowUser['user_photo']) $luAva = '/uploads/users/'.$likeUser.'/100_'.$rowUser['user_photo'];
                            else $luAva = '/images/100_no_ava.png';
                            if($row['action_type'] == 7){
                                $a = $db->super_query("SELECT date FROM `wall_like` WHERE rec_id = '{$row['obj_id']}' and user_id = '{$likeUser}'");
                                $row['action_time'] = $a['date'];
                                $likesUseList[$key]['icon'] = 'like';
                                $likesUseList[$key]['gifts'] = false;
                            } else if($row['action_type'] == 20){
                                $likesUseList[$key]['icon'] = 'like';
                                $likesUseList[$key]['gifts'] = false;
                            } else if($row['action_type'] == 21){
                                $likesUseList[$key]['icon'] = '';
                                $likesUseList[$key]['gift'] = $row['obj_id'];
                                $likesUseList[$key]['gifts'] = true;
                            } else if($row['action_type'] == 22){
                                $likesUseList[$key]['icon'] = 'like';
                                $likesUseList[$key]['gifts'] = false;
                            }
                            $params['likesUseList'] = $likesUseList;
                            $params['id'] = $row['ac_id'];
                            $params['ava'] = $luAva;
                            $params['uid'] = $likeUser;
                            $params['name'] = $rowUser['user_search_pref'];
                            $date = \Sura\Time\Date::megaDate(strtotime($row['action_time']), 1, 1);
                            $params['date'] = $date;
                            //$last_date = date('d.m.Y', $row['action_time']);
                        }
                    }
                    $params['list'] = $likesUseList;

                }
            }

            return view('news.notification3', $params);
        }
        return view('info.info', $params);
    }

    /**
     * index
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function index(): int
    {

        $logged = $this->logged();
        if ($logged){
            $content = '';//временно

            //$lang = $this->get_langs();
            $db = $this->db();
            $user_info = $this->user_info();

            $request = (Request::getRequest()->getGlobal());
            $limit_news = 15;
            $last_id = (int)$request['last_id'];

            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
            $cache = new \Sura\Cache\Cache($storage, 'users');
            $value = $cache->load("{$_SESSION['user_id']}/new_news");
            if ($value == NULL){
                $cache->save("{$_SESSION['user_id']}/new_news", "");
            }

            $count = $db->super_query("SELECT COUNT(*) as cnt FROM `news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}'");

            $last = true;
            if($last)
            {
                if ($last_id)
                    $sql_ = $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}' AND tb1.ac_id < '{$last_id}' ORDER BY tb1.action_time DESC LIMIT 0, {$limit_news}", 1);
                else
                    $sql_ = $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}' ORDER BY tb1.action_time DESC LIMIT 0, {$limit_news}", true);

                /*
                Лайки фотографий 20
                Лайки записей 7
                Полученные подарки 21
                Оценки дуэлей 22
                */

//            $tpl->load_template('news/notifications2.tpl');
                foreach($sql_ as $key => $row){

//                    if(!$last_date || $last_date != date('d.m.Y', $row['action_time']))
//                    {
//                        $content .= '<div class="unp-date-separator">'.Gramatic::megaDate($row['action_time']).'</div>';
//                    }

                    $likesUseList = explode('|', str_replace('u', '', $row['action_text']));
                    $rList = '';
                    $cntUse = 0;
                    foreach($likesUseList as $likeUser){
                        if($likeUser){
                            if($cntUse < 4){
                                $rowUser = $db->super_query("SELECT user_photo FROM `users` WHERE user_id = '{$likeUser}'");
                                if($rowUser['user_photo'])
                                    $luAva = '/uploads/users/'.$likeUser.'/100_'.$rowUser['user_photo'];
                                else
                                    $luAva = '/images/100_no_ava.png';
                                $rList .= '<a class="user" href="/u'.$likeUser.'" onClick="Page.Go(this.href); return false"><div><img src="'.$luAva.'" style="margin: 4px 4px 0px 0px;width: 64px;border-radius: 0;" /></div></a>';
                            }
                            $cntUse++;
                        }
                    }

                    if($cntUse > 4)
                    {
                        $rList .= '<div class="show_all">+'.($cntUse-4).'</div>';
                    }

                    if($row['action_type'] == 7){
                        $row_info_likes = $db->super_query("SELECT for_user_id FROM `wall` WHERE id = '{$row['obj_id']}'");

                        if(!$row_info_likes){
                            $db->query("DELETE FROM `news` WHERE ac_id = '{$row['ac_id']}'");
                            continue;
                        }

                        $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                        $type = $type.' <a class="user" href="/wall'.$row_info_likes['for_user_id'].'_'.$row['obj_id'].'" onClick="Page.Go(this.href); return false;">записи со стены</a>';
                        $sql_[$key]['icon'] = 'like';
                        $sql_[$key]['gifts'] = false;
                    } else if($row['action_type'] == 20){
                        $row_info_likes = $db->super_query("SELECT album_id FROM `photos` WHERE id = '{$row['obj_id']}'");
                        $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                        $type .= ' <a class="user" href="/photo'.$user_info['user_id'].'_'.$row['obj_id'].'_'.$row_info_likes['album_id'].'" onClick="Photo.Show(this.href); return false;">фотографии</a>';
                        $sql_[$key]['icon'] = 'like';
                        $sql_[$key]['gifts'] = false;
                    } else if($row['action_type'] == 21){
                        $type = 'Вам подарили <a class="user" href="/" onClick="gifts.browse('.$user_info['user_id'].'); return false;">'.$cntUse.' '.Gramatic::declOfNum($cntUse, array('подарок','подарка','подарков')).'</a>';
                        $sql_[$key]['icon'] = '';
                        $sql_[$key]['ttt'] = $row['obj_id'];
                        $sql_[$key]['gifts'] = true;
                    } else if($row['action_type'] == 22){
                        $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                        $type = $type.' <a class="user" href="/?go=compare&act=choose&out=1" onClick="Page.Go(this.href); return false;">в дуэлях</a>';
                        $sql_[$key]['icon'] = 'like';
                        $sql_[$key]['gifts'] = false;
                    }

                    $sql_[$key]['id'] = $row['ac_id'];
                    $sql_[$key]['type'] = $type;
                    $sql_[$key]['users'] = $rList;
//                    $date = megaDate(strtotime($row['action_time']), 1, 1);
//                    $sql_[$key]['date'] = $date;
                    $sql_[$key]['date'] = Date::megaDate($row['action_time'], 1, 1);
                    $last_date = date('d.m.Y', $row['action_time']);
                }

                $params['notifications'] = $sql_;

                $content = view_data('news.notification2', $params);

                if($count['cnt'] > $limit_news)
                {
                    $content .= '<div class="show_all_button" onclick="QNotifications.MoreShow();">Показать больше уведомлений</div>';
                }

                return _e_json(array('content' => $content, 'count' => $count['cnt']));
            }else{
                return _e_json(array('content' => '<p>Нет оповещений.</p>', 'count' => $count['cnt']));
            }
        }
        return _e_json(array('content' => '<p>Нет оповещений.</p>', 'count' => 0));
    }
}