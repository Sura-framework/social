<?php

namespace System\Modules;

use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Gramatic;
use System\Libs\Tools;

class NotificationsController extends Module{

    public function settings($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_POST['act'];

            if(stripos($user_info['notifications_list'], "settings_likes_posts|") === false) $settings_likes_posts = 'html_checked';
            else $settings_likes_posts = '';
            if(stripos($user_info['notifications_list'], "settings_likes_photos|") === false) $settings_likes_photos = 'html_checked';
            else $settings_likes_photos = '';
            if(stripos($user_info['notifications_list'], "settings_likes_compare|") === false) $settings_likes_compare = 'html_checked';
            else $settings_likes_compare = '';
            if(stripos($user_info['notifications_list'], "settings_likes_gifts|") === false) $settings_likes_gifts = 'html_checked';
            else $settings_likes_gifts = '';
            echo '<div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_posts");><i class="icn icn-gray icn-like"></i><span>Оценки записей</span> <div class="html_checkbox '.$settings_likes_posts.'" id="settings_likes_posts"></div></div>
<div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_photos");><i class="icn icn-gray icn-like"></i><span>Оценки фотографий</span> <div class="html_checkbox '.$settings_likes_photos.'" id="settings_likes_photos"></div></div>
<div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_compare");><i class="icn icn-gray icn-like"></i><span>Оценки в дуэлях</span> <div class="html_checkbox '.$settings_likes_compare.'" id="settings_likes_compare"></div></div>
<div class="settings_elem" onclick=QNotifications.settings_save("settings_likes_gifts");><i class="icn icn-gray icn-gift"></i><span>Новый подарок</span> <div class="html_checkbox '.$settings_likes_gifts.'" id="settings_likes_gifts"></div></div>';

        }
    }

    public function save_settings($params){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $settings_likes_posts = intval($_POST['settings_likes_posts']);
            $settings_likes_photos = intval($_POST['settings_likes_photos']);
            $settings_likes_compare = intval($_POST['settings_likes_compare']);
            $settings_likes_gifts = intval($_POST['settings_likes_gifts']);
            $notifications_list = '';
            if($settings_likes_posts) $notifications_list .= '|settings_likes_posts|';
            if($settings_likes_photos) $notifications_list .= '|settings_likes_photos|';
            if($settings_likes_compare) $notifications_list .= '|settings_likes_compare|';
            if($settings_likes_gifts) $notifications_list .= '|settings_likes_gifts|';
            $db->super_query("UPDATE `".PREFIX."_users` SET notifications_list = '{$notifications_list}' WHERE user_id = '{$user_info['user_id']}'");
            echo 1;
        }
    }

    public function del($params){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_POST['act'];

            $id = intval($_POST['id']);
            if($id){
                $sql_ = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_news` WHERE ac_id = '{$id}' AND action_type IN (7,20,21,22) AND for_user_id = '{$user_info['user_id']}'");
                if($sql_){
                    $db->query("DELETE FROM `".PREFIX."_news` WHERE ac_id = '{$id}'");
                    echo 1;
                }
            }
        }
    }

    public function notification($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_POST['act'];

            $id = intval($_POST['id']);
            if($id){
                $row = $db->super_query("SELECT ac_id, action_text, action_time, action_type, obj_id FROM `".PREFIX."_news` WHERE ac_id = '{$id}' AND action_type IN (7,20,21,22) AND for_user_id = '{$user_info['user_id']}'");
                if($row){
                    $likesUseList = explode('|', str_replace('u', '', $row['action_text']));
                    $tpl->load_template('news/notification3.tpl');
                    foreach($likesUseList as $likeUser){
                        if($likeUser){
                            $rowUser = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$likeUser}'");
                            if($rowUser['user_photo']) $luAva = '/uploads/users/'.$likeUser.'/100_'.$rowUser['user_photo'];
                            else $luAva = '/images/100_no_ava.png';
                            if($row['action_type'] == 7){
                                $a = $db->super_query("SELECT date FROM `".PREFIX."_wall_like` WHERE rec_id = '{$row['obj_id']}' and user_id = '{$likeUser}'");
                                $row['action_time'] = $a['date'];
                                $tpl->set('{icon}', 'like');
                                $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                            } else if($row['action_type'] == 20){
                                $tpl->set('{icon}', 'like');
                                $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                            } else if($row['action_type'] == 21){
                                $tpl->set('{icon}', '');
                                $tpl->set('{gift}', $row['obj_id']);
                                $tpl->set('[gifts]', '');
                                $tpl->set('[/gifts]', '');
                            } else if($row['action_type'] == 22){
                                $tpl->set('{icon}', 'like');
                                $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                            }
                            $tpl->set('{id}', $row['ac_id']);
                            $tpl->set('{ava}', $luAva);
                            $tpl->set('{uid}', $likeUser);
                            $tpl->set('{name}', $rowUser['user_search_pref']);
                            $date = megaDate(strtotime($row['action_time']), 1, 1);
                            $tpl->set('{date}', $date);
                            //$last_date = date('d.m.Y', $row['action_time']);
                            $tpl->compile('content');
                        }
                    }
                    Tools::AjaxTpl($tpl);
                }
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function index($params){
        $tpl = Registry::get('tpl');

        //$lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $limit_news = 15;
        $last_id = intval($_POST['last_id']);
        $CacheNews = Cache::mozg_cache('user_'.$_SESSION['user_id'].'/new_news');
        if($CacheNews) Cache::mozg_create_cache('user_'.$user_info['user_id'].'/new_news', '');

        $count = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}'");

        if($last_id) $sql_ = $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `".PREFIX."_news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}' AND tb1.ac_id < '{$last_id}' ORDER BY tb1.action_time DESC LIMIT 0, {$limit_news}", 1);
        $sql_ = $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `".PREFIX."_news` tb1 WHERE tb1.action_type IN (7,20,21,22) AND tb1.for_user_id = '{$user_info['user_id']}' ORDER BY tb1.action_time DESC LIMIT 0, {$limit_news}", 1);
        if($sql_){

            /*
            Лайки фотографий 20
            Лайки записей 7
            Полученные подарки 21
            Оценки дуелей 22
            */

            $tpl->load_template('news/notifications2.tpl');
            foreach($sql_ as $row){

                if(!$last_date || $last_date != date('d.m.Y', $row['action_time'])) $tpl->result['content'] .= '<div class="unp-date-separator">'.Gramatic::megaDateNoTpl2($row['action_time']).'</div>';

                $likesUseList = explode('|', str_replace('u', '', $row['action_text']));
                $rList = '';
                $cntUse = 0;
                foreach($likesUseList as $likeUser){
                    if($likeUser){
                        if($cntUse < 4){
                            $rowUser = $db->super_query("SELECT user_photo FROM `".PREFIX."_users` WHERE user_id = '{$likeUser}'");
                            if($rowUser['user_photo']) $luAva = '/uploads/users/'.$likeUser.'/100_'.$rowUser['user_photo'];
                            else $luAva = '/images/100_no_ava.png';
                            $rList .= '<a class="user" href="/u'.$likeUser.'" onClick="Page.Go(this.href); return false"><div><img src="'.$luAva.'" style="margin: 4px 4px 0px 0px;width: 64px;border-radius: 0;" /></div></a>';
                        }
                        $cntUse++;
                    }
                }

                if($cntUse > 4) $rList .= '<div class="show_all">+'.($cntUse-4).'</div>';

                $type = '';
                if($row['action_type'] == 7){
                    $row_info_likes = $db->super_query("SELECT for_user_id FROM `".PREFIX."_wall` WHERE id = '{$row['obj_id']}'");

                    if(!$row_info_likes){
                        $db->query("DELETE FROM `".PREFIX."_news` WHERE ac_id = '{$row['ac_id']}'");
                        continue;
                    }

                    $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                    $type = $type.' <a class="user" href="/wall'.$row_info_likes['for_user_id'].'_'.$row['obj_id'].'" onClick="Page.Go(this.href); return false;">записи со стены</a>';
                    $tpl->set('{icon}', 'like');
                    $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                } else if($row['action_type'] == 20){
                    $row_info_likes = $db->super_query("SELECT album_id FROM `".PREFIX."_photos` WHERE id = '{$row['obj_id']}'");
                    $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                    $type = $type.' <a class="user" href="/photo'.$user_info['user_id'].'_'.$row['obj_id'].'_'.$row_info_likes['album_id'].'" onClick="Photo.Show(this.href); return false;">фотографии</a>';
                    $tpl->set('{icon}', 'like');
                    $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                } else if($row['action_type'] == 21){
                    $type = 'Вам подарили <a class="user" href="/" onClick="gifts.browse('.$user_info['user_id'].'); return false;">'.$cntUse.' '.Gramatic::declOfNum($cntUse, array('подарок','подарка','подарков')).'</a>';
                    $tpl->set('{icon}', '');
                    $tpl->set('{gift}', $row['obj_id']);
                    $tpl->set('[gifts]', '');
                    $tpl->set('[/gifts]', '');

                } else if($row['action_type'] == 22){
                    $type = $cntUse.' '.Gramatic::declOfNum($cntUse, array('оценка','оценки','оценок'));
                    $type = $type.' <a class="user" href="/?go=compare&act=choose&out=1" onClick="Page.Go(this.href); return false;">в дуэлях</a>';
                    $tpl->set('{icon}', 'like');
                    $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");
                }

                $tpl->set('{id}', $row['ac_id']);
                $tpl->set('{type}', $type);
                $tpl->set('{users}', $rList);
                $date = megaDate(strtotime($row['action_time']), 1, 1);
                $tpl->set('{date}', $date);
                $last_date = date('d.m.Y', $row['action_time']);
                $tpl->compile('content');
            }

            if(!$last_id && $count['cnt'] > $limit_news) $tpl->result['content'] .= '<div class="show_all_button" onclick="QNotifications.MoreShow();">Показать больше уведомлений</div>';

        } else if(!$last_id && !$_POST['page_cnt']) msgbox('', 'Нет оповещений.', 'info_2');

        if($last_id){
            $config = include __DIR__.'/../data/config.php';
            echo json_encode(array('content' => str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['content']), 'count' => $count['cnt']));
        } else Tools::AjaxTpl($tpl);

    }
}