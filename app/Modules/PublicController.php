<?php

namespace App\Modules;

use App\Libs\Wall;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Validation;

class PublicController extends Module{

    /**
     *  Сообщества -> Публичные страницы
     *
     * @param $params
     * @return int
     */
    public function index($params): int
    {

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        $config = Settings::load();

        if($logged){
            $user_id = $user_info['user_id'];
            //$pid = intval($_GET['pid']);

            /*
             * ID page
             */
//            $path = explode('/', $_SERVER['REQUEST_URI']);
//            $id = str_replace('public', '', $path);
//            $pid = (int)$id['1'];
//            $id = (int)$id['1'];

            if ($params['alias']){
                $id = $pid = (int)$params['alias'];
            }else{
                $server = Request::getRequest()->server;

                $path = explode('/', $server['REQUEST_URI']);
                $id = str_replace('public', '', $path);
                $id = $pid = (int)$id['1'];
            }
//var_dump($pid);
//            $mobile_speedbar = 'Сообщество';

//            if(preg_match("/^[a-zA-Z0-9_-]+$/", $_GET['get_adres']))
//                $get_adres = $db->safesql($_GET['get_adres']);

            $sql_where = "id = '".$pid."'";

            if($pid > 0){
                $get_adres = '';
                $sql_where = "id = '".$pid."'";
            }
            if(!empty($get_adres) ){
                $pid = '';
                $sql_where = "adres = '".$get_adres."'";
            }


            //Если страница вывзана через "к предыдущим записям"
            $limit_select = 20;
//            if(isset($request['page_cnt']) AND $request['page_cnt'] > 0)
//                $page_cnt = (int)$request['page_cnt'] *$limit_select;
//            else
                $page_cnt = 0;

            if($page_cnt){
                $row = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$pid}'");
                $row['id'] = $pid;
            } else
            {
//                $row = $db->super_query("SELECT id, title, descr, traf, ulist, photo, date, admin, feedback, comments, real_admin, rec_num, del, ban, adres, audio_num, forum_num, discussion, status_text, web, videos_num, cover, cover_pos FROM `communities` WHERE ".$sql_where);
            }

            $row = $db->super_query("SELECT id, title, descr, traf, ulist, photo, date, admin, feedback, comments, real_admin, rec_num, del, ban, adres, audio_num, forum_num, discussion, status_text, web, videos_num, cover, cover_pos FROM `communities` WHERE ".$sql_where);

            if(!empty($row['del']) AND $row['del'] == 1){
//                $user_speedbar = 'Страница удалена';
//                msgbox('', '<br /><br />Сообщество удалено администрацией.<br /><br /><br />', 'info_2');
            } elseif(!empty($row['del']) AND $row['ban'] == 1){
                //                msgbox('', '<br /><br />Сообщество заблокировано администрацией.<br /><br /><br />', 'info_2');
            }
            else{
                $params['title'] = stripslashes($row['title']).' | Sura';

                if(stripos($row['admin'], "u{$user_id}|") !== false) {
                    $public_admin = true;
                }
                else {
                    $public_admin = false;
                }

                //Стена
                //Если страница вывзана через "к предыдущим записям"
//                if($page_cnt)
//                    Tools::NoAjaxQuery();

                // include __DIR__.'/../Classes/Public_wall.php';


                //$wall = new Public_wall();
                $query = $db->super_query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, fixed, tb2.title, photo, comments, adres FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT {$page_cnt}, {$limit_select}", true);

                $server_time = \Sura\Time\Date::time();

                //Если страница вывзана через "к предыдущим записям"
//                if($page_cnt){
//                    $compile = 'content';
//                    //$tpl = $wall->compile('content', $tpl);
//                }else{
//                    $compile = 'wall';
//                    //$tpl = $wall->compile('wall', $tpl);
//                }

                $user_id = $user_info['user_id'];

                $params['wall_records'] = Wall::build($query);

                //Стена
                if($row['rec_num'] > 10){
                    $params['wall_page_display'] = '';
                }
                else{
                    $params['wall_page_display'] = 'no_display';
                }
                $titles = array('запись', 'записи', 'записей');//rec
                if($row['rec_num']){
                    $params['rec_num'] = '<b id="rec_num">'.$row['rec_num'].'</b> '.Gramatic::declOfNum($row['rec_num'], $titles);
                }
                else {
                    $params['rec_num'] = '<b id="rec_num">Нет записей</b>';
                    if($public_admin){
                        $params['records'] = '<div class="wall_none" style="border-top:0px">Новостей пока нет.</div>';
                    }
                    else{
                        $params['records'] = '<div class="wall_none">Новостей пока нет.</div>';
                    }
                }

                //Если страница вывзана через "к предыдущим записям"

//                $tpl->load_template('public/main.tpl');

                $params['title'] = stripslashes($row['title']);
                $config = Settings::load();

                if($row['photo']){

                    //FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile')
                        $row['photo'] = '50_'.$row['photo'];

                    $params['photo'] = "/uploads/groups/{$row['id']}/{$row['photo']}";
                    $params['display_ava'] = '';
                }
                else {

                    //FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile'){
                        $params['photo'] = "/images/no_ava_50.png";
                    }
                    else{
                        $params['photo'] = "/images/no_ava.gif";
                    }

                    $params['display_ava'] = 'no_display';
                }

                if($row['descr']){
                    $params['descr_css'] = '';
                }
                else{
                    $params['descr_css'] = 'no_display';
                }

                $params['edit_descr'] = Validation::myBrRn(stripslashes($row['descr']));

                //КНопка Показать полностью..
                $expBR = explode('<br />', $row['descr']);
                $textLength = count($expBR);
                $strTXT = strlen($row['descr']);
                if($textLength > 9 OR $strTXT > 600)
                    $row['descr'] = '<div class="wall_strlen" id="hide_wall_rec'.$row['id'].'">'.$row['descr'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row['id'].', this.id)" id="hide_wall_rec_lnk'.$row['id'].'">Показать полностью..</div>';

                $params['descr'] = stripslashes($row['descr']);

                $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                $params['num'] = '<span id="traf">'.$row['traf'].'</span> '.Gramatic::declOfNum($row['traf'], $titles);
                if($row['traf']){
                    $titles = array('человек', 'человека', 'человек');//subscribers2
                    $params['num_2'] = '<a href="/public'.$row['id'].'" onClick="groups.all_people(\''.$row['id'].'\'); return false">'.$row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles).'</a>';
                    $params['subscribed'] = true;
                } else {
                    $params['num_2'] = '<span class="color777">Вы будете первым.</span>';
                    $params['subscribed'] = false;
                }

                //Права админа
                if($public_admin){
                    $params['admin'] = true;
                } else {
                    $params['admin'] = false;
                }

                //Проверка подписан юзер или нет
                if(stripos($row['ulist'], "|{$user_id}|") !== false){
                    $params['yes'] = 'no_display';
                }
                else{
                    $params['no'] = 'no_display';
                }

                //Контакты
                if($row['feedback']){
//                    $params['yes'] = true;
                    $titles = array('контакт', 'контакта', 'контактов');//feedback
                    $params['num_feedback'] = '<span id="fnumu">'.$row['feedback'].'</span> '.Gramatic::declOfNum($row['feedback'], $titles);
                    $sql_feedbackusers = $db->super_query("SELECT tb1.fuser_id, office, tb2.user_search_pref, user_photo FROM `communities_feedback` tb1, `users` tb2 WHERE tb1.cid = '{$row['id']}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC LIMIT 0, 5", 1);
                    $feedback_users = '';
                    foreach($sql_feedbackusers as $row_feedbackusers){
                        if($row_feedbackusers['user_photo']) $ava = "/uploads/users/{$row_feedbackusers['fuser_id']}/50_{$row_feedbackusers['user_photo']}";
                        else $ava = "/images/no_ava_50.png";
                        $row_feedbackusers['office'] = stripslashes($row_feedbackusers['office']);
                        $feedback_users .= "<div class=\"onesubscription onesubscriptio2n\" id=\"fb{$row_feedbackusers['fuser_id']}\"><a href=\"/u{$row_feedbackusers['fuser_id']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava}\" alt=\"\" /><div class=\"onesubscriptiontitle\">{$row_feedbackusers['user_search_pref']}</div></a><div class=\"nesubscriptstatus\">{$row_feedbackusers['office']}</div></div>";
                    }
                    $params['feedback_users'] = $feedback_users;
                    $params['feedback'] = true;
                }
                else {
                    $params['feedback'] = false;
//                    $params['no'] = true;
//                    $params['yes'] = false;
                    $params['feedback_users'] = false;
                    if($public_admin){
                        $params['feedback'] = true;
                    } else{
                        $params['feedback'] = false;
                    }
                }

                //Выводим подписчиков
                $sql_users = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.friend_id = '{$row['id']}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by rand() LIMIT 0, 6", 1);
                $users = '';
                foreach($sql_users as $row_users){
                    if($row_users['user_photo']) $ava = "/uploads/users/{$row_users['user_id']}/50_{$row_users['user_photo']}";
                    else $ava = "/images/no_ava_50.png";
                    $users .= "<div class=\"onefriend oneusers\" id=\"subUser{$row_users['user_id']}\"><a href=\"/u{$row_users['user_id']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava}\"  style=\"margin-bottom:3px\" /></a><a href=\"/u{$row_users['user_id']}\" onClick=\"Page.Go(this.href); return false\">{$row_users['user_name']}<br /><span>{$row_users['user_lastname']}</span></a></div>";
                }
                    $params['users'] = $users;

                $params['id'] = $row['id'];

                $params['date'] = \Sura\Time\Date::megaDate(strtotime($row['date']), 1, 1);
                //Комментарии включены
                if($row['comments']){
                    $params['settings_comments'] = 'comments';
                }
                else{
                    $params['settings_comments'] = 'none';
                }

                //Выводим админов при ред. страницы
                if($public_admin){
                    $admins_arr = str_replace('|', '', explode('u', $row['admin']));
                    $adminO = '';
                    foreach($admins_arr as $admin_id){
                        if($admin_id){
                            $row_admin = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$admin_id}'");
                            if($row_admin['user_photo']) {
                                $ava_admin = "/uploads/users/{$admin_id}/50_{$row_admin['user_photo']}";
                            }
                            else
                            {
                                $ava_admin = "/images/no_ava_50.png";
                            }
                            if($admin_id != $row['real_admin'])
                            {
                                $admin_del_href = "<a href=\"/\" onClick=\"groups.deladmin('{$row['id']}', '{$admin_id}'); return false\"><small>Удалить</small></a>";
                            }else{
                                $admin_del_href = '';
                            }
                            $adminO .= "<div class=\"public_oneadmin\" id=\"admin{$admin_id}\"><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$ava_admin}\" align=\"left\" width=\"32\" /></a><a href=\"/u{$admin_id}\" onClick=\"Page.Go(this.href); return false\">{$row_admin['user_search_pref']}</a><br />{$admin_del_href}</div>";
                        }
                    }
                    $params['admins'] = $adminO;
                }

//                $tpl->set('{records}', $tpl->result['wall']);



                //Выводим информцию о том кто смотрит страницу для себя
                $params['viewer_id'] = $user_id;
                if(!$row['adres'])
                {
                    $row['adres'] = 'public'.$row['id'];
                }
                $params['adres'] = $row['adres'];

                //Аудиозаписи
                if($row['audio_num']){
                    $sql_audios = $db->super_query("SELECT url, artist, name FROM `communities_audio` WHERE public_id = '{$row['id']}' ORDER by `adate` DESC LIMIT 0, 3", 1, "groups/audio{$row['id']}");
                    $jid = 0;
                    $audios = '';
                    foreach($sql_audios as $row_audios){
                        $jid++;

                        $row_audios['artist'] = stripslashes($row_audios['artist']);
                        $row_audios['name'] = stripslashes($row_audios['name']);

                        $audios .= "<div class=\"audio_onetrack\"><div class=\"audio_playic cursor_pointer fl_l\" onClick=\"music.newStartPlay('{$jid}')\" id=\"icPlay_{$jid}\"></div><span id=\"music_{$jid}\" data=\"{$row_audios['url']}\"><a href=\"/?go=search&query={$row_audios['artist']}&type=5\" onClick=\"Page.Go(this.href); return false\"><b><span id=\"artis{aid}\">{$row_audios['artist']}</span></b></a> &ndash; <span id=\"name{aid}\">{$row_audios['name']}</span></span><div id=\"play_time{$jid}\" class=\"color777 fl_r no_display\" style=\"margin-top:2px;margin-right:5px\"></div> <div class=\"clear\"></div><div class=\"player_mini_mbar fl_l no_display\" id=\"ppbarPro{$jid}\" style=\"width:178px\"></div> </div>";

                    }

                    $params['audios'] = $audios;
                    $params['audios_num'] = $row['audio_num'];
                    $params['audios'] = true;
                    $params['yes_audio'] = true;
                }
                else {
                    $params['audios_num'] = $row['audio_num'];
                    $params['audios'] = true;
                    $params['yes_audio'] = false;

                    if($public_admin){
                        $params['audios'] = true;
                    } else{
                        $params['audios'] = false;
                    }

                }

                //Обсуждения
                if($row['discussion']){
                    $params['settings_discussion'] = '';
                    $params['discussion'] = true;
                } else {
                    $params['settings_discussion'] = '';
                    $params['discussion'] = false;
                }

                if(!$row['forum_num'])
                {
                    $row['forum_num'] = '';
                }
                $params['forum_num'] = $row['forum_num'];

                if($row['forum_num'] AND $row['discussion']){

                    $sql_forum = $db->super_query("SELECT fid, title, lastuser_id, lastdate, msg_num FROM `communities_forum` WHERE public_id = '{$row['id']}' ORDER by `fixed` DESC, `lastdate` DESC, `fdate` DESC LIMIT 0, 5", 1, "groups_forum/forum{$row['id']}");
                    $thems = '';

                    foreach($sql_forum as $row_forum){

                        $row_last_user = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '{$row_forum['lastuser_id']}'");
                        $last_userX = explode(' ', $row_last_user['user_search_pref']);
                        $row_last_user['user_search_pref'] = gramatikName($last_userX[0]).' '.gramatikName($last_userX[1]);

                        $row_forum['title'] = stripslashes($row_forum['title']);

                        $titles = array('сообщение', 'сообщения', 'сообщений');//msg
                        $msg_num = $row_forum['msg_num'].' '.Gramatic::declOfNum($row_forum['msg_num'], $titles);

                        $last_date = \Sura\Time\Date::megaDate($row_forum['lastdate']);

                        $thems .= "<div class=\"forum_bg\"><div class=\"forum_title cursor_pointer\" onClick=\"Page.Go('/forum{$row['id']}?act=view&id={$row_forum['fid']}'); return false\">{$row_forum['title']}</div><div class=\"forum_bottom\">{$msg_num}. Последнее от <a href=\"/u{$row_forum['lastuser_id']}\" onClick=\"Page.Go(this.href); return false\">{$row_last_user['user_search_pref']}</a>, {$last_date}</div></div>";

                    }

                    $params['thems'] = $thems;
                } else{
                    $params['thems'] = '<div class="wall_none">В сообществе ещё нет тем.</div>';
                }

                //Видеозаписи
                if($row['videos_num']){
                    $sql_videos = $db->super_query("SELECT id, title, photo, add_date, comm_num, owner_user_id FROM `videos` WHERE public_id = '{$row['id']}' ORDER by `add_date` DESC LIMIT 0, 2", 1, "groups/video{$row['id']}");
                    $videos = '';
                    foreach($sql_videos as $row_video){
                        $row_video['title'] = stripslashes($row_video['title']);
                        $date_video = \Sura\Time\Date::megaDate(strtotime($row_video['add_date']));
                        $titles = array('комментарий', 'комментария', 'комментариев');//comments
                        $comm_num = $row_video['comm_num'].' '.Gramatic::declOfNum($row_video['comm_num'], $titles);
                        $videos .= "
                            <div class=\"profile_one_video\"><a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\"><img src=\"{$row_video['photo']}\" alt=\"\" width=\"185\" /></a><div class=\"video_profile_title\"><a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\">{$row_video['title']}</a></div><div class=\"nesubscriptstatus\">{$date_video} - <a href=\"/video{$row_video['owner_user_id']}_{$row_video['id']}\" onClick=\"videos.show({$row_video['id']}, this.href, '/{$row['adres']}'); return false\">{$comm_num}</a></div></div>
				        ";

                    }

                    $params['videos'] = $videos;
                    $params['videos_num'] = $row['videos_num'];
//                    $params['videos'] = true;
                    $params['yes_video'] = true;
                }
                else {
                    $params['videos_num'] = $row['videos_num'];
                    $params['videos'] = false;
                    if($public_admin){
                        $params['yes_video'] = true;
                    } else{
                        $params['yes_video'] = false;
                    }
                }

                //Статус
//                $tpl->set('{status-text}', );
                $params['status_text'] = stripslashes($row['status_text']);

                if($row['status_text']){
                    $params['status'] = true;
                    $params['no_status'] = false;
                } else {
                    $params['status'] = false;
                    $params['no_status'] = true;
                }
                $params['web'] = $row['web'];

                if($row['web']){
                    $params['web'] = true;
                } else{
                    $params['web'] = false;
                }

                //Обложка
                if($row['photo']){
                    $avaImgIsinfo = getimagesize(ROOT_DIR."/uploads/groups/{$row['id']}/{$row['photo']}");
                    if($avaImgIsinfo[1] < 200){
                        $rForme = 230 - $avaImgIsinfo[1];
                        $ava_marg_top = 'style="margin-top:-'.$rForme.'px"';
                    }else{
                        $ava_marg_top = '';
                    }
                    $params['cover_param_7'] = $ava_marg_top;
                } else{
                    $params['cover_param_7'] = '';
                }

                if($row['cover']){

                    $imgIsinfo = getimagesize(ROOT_DIR."/uploads/groups/{$row['id']}/{$row['cover']}");

                    $params['cover'] =  "/uploads/groups/{$row['id']}/{$row['cover']}";
                    $params['cover_height'] = $imgIsinfo[1];
                    $params['cover_param'] = '';
                    $params['cover_param_2'] = 'no_display';
                    $params['cover_param_3'] = 'style="position:absolute;z-index:2;display:block;margin-left:397px"';
                    $params['cover_param_4'] = 'style="cursor:default"';
                    $params['cover_param_5'] = 'style="top:-'.$row['cover_pos'].'px;position:relative"';
                    $params['cover_pos'] = $row['cover_pos'];
                    $params['cover'] = true;
                }
                else {
                    $params['cover'] = '';
                    $params['cover_param'] = 'no_display';
                    $params['cover_param_2'] = '';
                    $params['cover_param_3'] = '';
                    $params['cover_param_4'] = '';
                    $params['cover_param_5'] = '';
                    $params['cover_pos'] = '';
                    $params['cover'] = false;
                }

                //Записываем в статистику "Уникальные посетители"
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats` WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats_log` WHERE gid = '{$row['id']}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '1'");

                if(!$check_user_stat['cnt']){

                    if($check_stat['cnt']){

                        $db->query("UPDATE `communities_stats` SET cnt = cnt + 1 WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");

                    } else {

                        $db->query("INSERT INTO `communities_stats` SET gid = '{$row['id']}', date = '{$stat_date}', cnt = '1', date_x = '{$stat_x_date}'");

                    }

                    $db->query("INSERT INTO `communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', gid = '{$row['id']}', act = '1'");

                }

                //Записываем в статистику "Просмотры"
                $db->query("UPDATE `communities_stats` SET hits = hits + 1 WHERE gid = '{$row['id']}' AND date = '{$stat_date}'");

//                $tpl->compile('content');

                $params['title'] = $row['title'];
                return view('groups.public', $params);
            }
//            else {
//                $params['title'] = $lang['no_infooo'];
//                $params['info'] = $lang['not_logged'];
//                return view('info.info', $params);
//            }
        }
        else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}