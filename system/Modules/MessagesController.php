<?php
/* 
	Appointment: Сообщения
	File: messages.php 
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

class MessagesController extends Module{

    public function send($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();

            AntiSpam('messages');

            $for_user_id = intval($_POST['for_user_id']);
            $theme = Validation::ajax_utf8(strip_tags($_POST['theme']));
            $msg = Validation::ajax_utf8($_POST['msg']);
            $attach_files = Validation::ajax_utf8($_POST['attach_files']);

            $attach_files = str_replace('vote|', 'hack|', $attach_files);

            if(!$theme)
                $theme = '...';

            AntiSpam('identical', $msg.$attach_files);

            if(isset($msg) AND !empty($msg) OR isset($attach_files) OR !empty($attach_files)){

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");

                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //ЧС
                    $CheckBlackList = Tools::CheckBlackList($for_user_id);

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_msg'] == 2)
                        $check_friend = Tools::CheckFriends($for_user_id);

                    if(!$CheckBlackList AND $user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $check_friend)
                        $xPrivasy = 1;
                    else
                        $xPrivasy = 0;

                    if($xPrivasy AND $user_id != $for_user_id){

                        AntiSpamLogInsert('identical', $msg.$attach_files);

                        if(!Tools::CheckFriends($for_user_id)) AntiSpamLogInsert('messages');

                        $server_time = intval($_SERVER['REQUEST_TIME']);

                        //Отправляем сообщение получателю
                        $db->query("INSERT INTO `".PREFIX."_messages` SET theme = '{$theme}', text = '{$msg}', for_user_id = '{$for_user_id}', from_user_id = '{$user_id}', date = '{$server_time}', pm_read = 'no', folder = 'inbox', history_user_id = '{$user_id}', attach = '".$attach_files."'");
                        $dbid = $db->insert_id();

                        //Сохраняем сообщение в папку отправленные
                        $db->query("INSERT INTO `".PREFIX."_messages` SET theme = '{$theme}', text = '{$msg}', for_user_id = '{$user_id}', from_user_id = '{$for_user_id}', date = '{$server_time}', pm_read = 'no', folder = 'outbox', history_user_id = '{$user_id}', attach = '".$attach_files."'");

                        //Обновляем кол-во новых сообщения у получателя
                        $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num+1 WHERE user_id = '{$for_user_id}'");

                        //Проверка на наличии созданого диалога у себя
                        $check_im = $db->super_query("SELECT iuser_id FROM `".PREFIX."_im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                        if(!$check_im)
                            $db->query("INSERT INTO ".PREFIX."_im SET iuser_id = '".$user_id."', im_user_id = '".$for_user_id."', idate = '".$server_time."', all_msg_num = 1");
                        else
                            $db->query("UPDATE ".PREFIX."_im  SET idate = '".$server_time."', all_msg_num = all_msg_num+1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");

                        //Проверка на наличии созданого диалога у получателя, а если есть то просто обновляем кол-во новых сообщений в диалоге
                        $check_im_2 = $db->super_query("SELECT iuser_id FROM ".PREFIX."_im WHERE iuser_id = '".$for_user_id."' AND im_user_id = '".$user_id."'");
                        if(!$check_im_2)
                            $db->query("INSERT INTO ".PREFIX."_im SET iuser_id = '".$for_user_id."', im_user_id = '".$user_id."', msg_num = 1, idate = '".$server_time."', all_msg_num = 1");
                        else
                            $db->query("UPDATE ".PREFIX."_im  SET idate = '".$server_time."', msg_num = msg_num+1, all_msg_num = all_msg_num+1 WHERE iuser_id = '".$for_user_id."' AND im_user_id = '".$user_id."'");

                        //Вставляем событие в моментальные оповещания
                        $check2 = $db->super_query("SELECT user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");
                        $update_time = $server_time - 70;

                        if($check2['user_last_visit'] >= $update_time){

                            // if($check2['user_msg_type'])
                            // else
                            // $msg_lnk = '/messages/show/'.$dbid;
                            $msg_lnk = '/messages#'.$user_id;

                            $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_id}', type = '8', date = '{$server_time}', text = '{$msg}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '{$msg_lnk}'");

                            Cache::mozg_create_cache("user_{$for_user_id}/updates", 1);

                        }

                        //Читисм кеш обновлений
                        Cache::mozg_clear_cache_file('user_'.$for_user_id.'/im');
                        Cache::mozg_create_cache('user_'.$for_user_id.'/im_update', '1');

                        $config = include __DIR__.'/../data/config.php';

                        //Отправка уведомления на E-mail
                        if($config['news_mail_8'] == 'yes' AND $user_id != $for_user_id){
                            $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$for_user_id."'");
                            if($rowUserEmail['user_email']){
                                include_once __DIR__.'/../Classes/mail.php';
                                $mail = new \dle_mail($config);
                                $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
                                $rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '8'");
                                $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                                $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                                $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'messages/show/'.$dbid, $rowEmailTpl['text']);
                                $mail->send($rowUserEmail['user_email'], 'Новое персональное сообщение', $rowEmailTpl['text']);
                            }
                        }

                    } else
                        echo 'err_privacy';
                } else
                    echo 'no_user';
            } else
                echo 'max_strlen';

            die();
        }
    }

    public function delet($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();

            $mid = intval($_POST['mid']);
            $folder = $db->safesql($_POST['folder']);

            if($folder == 'inbox')
                $folder = 'inbox';
            else
                $folder = 'outbox';

            //Проверяем на факт существования сообщения для юзера
            $row = $db->super_query("SELECT pm_read, from_user_id FROM `".PREFIX."_messages` WHERE id = '{$mid}' AND for_user_id = '{$user_id}' AND folder = '{$folder}'");
            if($row){
                //Удаляе само сообщение
                $db->query("DELETE FROM `".PREFIX."_messages` WHERE id = '{$mid}' AND folder = '{$folder}' AND for_user_id = '{$user_id}'");

                //Если сообщение не прочитано, то при удалении отнимаем -1 у кол-во новых входящих сообщений
                if($row['pm_read'] == 'no' AND $folder == 'inbox'){
                    $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num-1 WHERE user_id = '{$user_id}'");
                    $db->query("UPDATE `".PREFIX."_im` SET msg_num = msg_num-1, all_msg_num = all_msg_num-1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$row['from_user_id']."'");
                } else
                    $db->query("UPDATE `".PREFIX."_im` SET all_msg_num = all_msg_num-1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$row['from_user_id']."'");
            }

            die();
        }
    }

    public function history($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $for_user_id = intval($_POST['for_user_id']);

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $limit_page = ($page-1)*$gcount;

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, tb2.user_name FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = '{$for_user_id}' AND tb1.history_user_id = tb2.user_id ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
                $tpl->load_template('messages/history.tpl');
                foreach($sql_ as $row){
                    $tpl->set('{name}', $row['user_name']);
                    $tpl->set('{folder}', $row['folder']);
                    $tpl->set('{user-id}', $row['history_user_id']);
                    $tpl->set('{text}', stripslashes($row['text']));
                    $tpl->set('{msg-id}', $row['id']);
                    $tpl->set('{date}', date('d.m.y', $row['date']));

                    if($row['history_user_id'] == $user_id){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    if($row['pm_read'] == 'no'){
                        $tpl->set('[new]', '');
                        $tpl->set('[/new]', '');
                    } else
                        $tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si","");

                    $tpl->compile('content');
                }
                $msg_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_messages` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$for_user_id}'");
                if($msg_count['cnt'] >= $gcount)
                    box_navigation($gcount, $msg_count['cnt'], $for_user_id, 'messages.history', '');

                Tools::AjaxTpl($tpl);
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function review($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $metatags['title'] = $lang['msg_view'];
            $user_speedbar = $lang['msg_view'];

            $mid = intval($_GET['mid']);

            if($mid){
                //SQL Запрос за вывод сообщения
                $row = $db->super_query("SELECT tb1.id, theme, text, from_user_id, history_user_id, date, pm_read, folder, attach, tell_uid, tell_date, public, tell_comm, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.id = '{$mid}' AND tb1.from_user_id = tb2.user_id AND tb1.for_user_id = '{$user_id}'");

                $folder = $row['folder'];

                //header сообщений
                $tpl->load_template('messages/head.tpl');
                $tpl->set('{mid}', $mid);
                $tpl->set('{folder}', $folder);
                $tpl->set('[review]', '');
                $tpl->set('[/review]', '');
                $tpl->set_block("'\\[outbox\\](.*?)\\[/outbox\\]'si","");
                $tpl->set_block("'\\[inbox\\](.*?)\\[/inbox\\]'si","");
                $tpl->compile('info');

                if($row){
                    $tpl->load_template('messages/review.tpl');

                    if($row['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['from_user_id'].'/100_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/100_no_ava.png');

                    if($folder == 'inbox')
                        $tpl->set('{name}', $row['user_search_pref']);
                    else {
                        $name_exp = explode(' ', $row['user_search_pref']);
                        $tpl->set('{name}', Gramatic::gramatikName($name_exp[0]).' '.Gramatic::gramatikName($name_exp[1]));
                    }

                    //Прикрипленные файлы
                    if($row['attach']){
                        $attach_arr = explode('||', $row['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
                        $jid = 0;
                        $attach_result = '';
                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}")){
                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                if($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                elseif($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['id']}\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;
                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row['id']}\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){
                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $audioId = intval($attach_type[1]);
                                $audioInfo = $db->super_query("SELECT artist, name, url FROM `".PREFIX."_audio` WHERE aid = '".$audioId."'");
                                if($audioInfo){
                                    $jid++;
                                    $attach_result .= '<div class="audioForSize'.$row['id'].' player_mini_mbar_wall_all2" id="audioForSize"><div class="audio_onetrack audio_wall_onemus"><div class="audio_playic cursor_pointer fl_l" onClick="music.newStartPlay(\''.$jid.'\', '.$row['id'].')" id="icPlay_'.$row['id'].$jid.'"></div><div id="music_'.$row['id'].$jid.'" data="'.$audioInfo['url'].'" class="fl_l" style="margin-top:-1px"><a href="/?go=search&type=5&query='.$audioInfo['artist'].'" onClick="Page.Go(this.href); return false"><b>'.stripslashes($audioInfo['artist']).'</b></a> &ndash; '.stripslashes($audioInfo['name']).'</div><div id="play_time'.$row['id'].$jid.'" class="color777 fl_r no_display" style="margin-top:2px;margin-right:5px">00:00</div><div class="player_mini_mbar fl_l no_display player_mini_mbar_wall player_mini_mbar_wall_all2" id="ppbarPro'.$row['id'].$jid.'"></div></div></div>';
                                }

                                $resLinkTitle = '';

                                //Смайлик
                            } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';

                                //Если ссылка
                            } elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
                                $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type[1]);
                                $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                                $attach_type[3] = stripslashes($attach_type[3]);
                                $attach_type[3] = iconv_substr($attach_type[3], 0, 200, 'utf-8');

                                $attach_type[2] = stripslashes($attach_type[2]);
                                $str_title = iconv_substr($attach_type[2], 0, 55, 'utf-8');

                                if(stripos($attach_type[4], '/uploads/attach/') === false){
                                    $attach_type[4] = '/images/no_ava_groups_100.gif';
                                    $no_img = false;
                                } else
                                    $no_img = true;

                                if(!$attach_type[3]) $attach_type[3] = '';

                                if($no_img AND $attach_type[2]){

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="border:0px"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                }

                                $cnt_attach_link++;

                                //Если документ
                            } elseif($attach_type[0] == 'doc'){

                                $doc_id = intval($attach_type[1]);

                                $row_doc = $db->super_query("SELECT dname, dsize FROM `".PREFIX."_doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            } elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `".PREFIX."_votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                                if($vote_id){

                                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row['text'])
                                        $row['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$vote_id}");
                                    $answer = array();
                                    foreach($sql_answer as $row_answer){

                                        $answer[$row_answer['answer']]['cnt'] = $row_answer['cnt'];

                                    }

                                    $attach_result .= "<div class=\"clear\" style=\"height:10px\"></div><div id=\"result_vote_block{$vote_id}\"><div class=\"wall_vote_title\">{$row_vote['title']}</div>";

                                    for($ai = 0; $ai < sizeof($arr_answe_list); $ai++){

                                        if(!$checkMyVote['cnt']){

                                            $attach_result .= "<div class=\"wall_vote_oneanswe\" onClick=\"Votes.Send({$ai}, {$vote_id})\" id=\"wall_vote_oneanswe{$ai}\"><input type=\"radio\" name=\"answer\" /><span id=\"answer_load{$ai}\">{$arr_answe_list[$ai]}</span></div>";

                                        } else {

                                            $num = $answer[$ai]['cnt'];

                                            if(!$num ) $num = 0;
                                            if($max != 0) $proc = (100 * $num) / $max;
                                            else $proc = 0;
                                            $proc = round($proc, 2);

                                            $attach_result .= "<div class=\"wall_vote_oneanswe cursor_default\">
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

                                    $attach_result .= "{$answer_text2} <b>{$row_vote['answer_num']}</b> {$answer_num_text}.<div class=\"clear\" style=\"margin-top:10px\"></div></div>";

                                }

                            } else

                                $attach_result .= '';

                        }

                        if($resLinkTitle AND $row['text'] == $resLinkUrl OR !$row['text'])
                            $row['text'] = $resLinkTitle.$attach_result;
                        else if($attach_result)
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']).$attach_result;
                        else
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);

                    } else
                        $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($row['tell_uid']){
                        if($row['public'])
                            $rowUserTell = $db->super_query("SELECT title, photo FROM `".PREFIX."_communities` WHERE id = '{$row['tell_uid']}'", false, "wall/group{$row['tell_uid']}");
                        else
                            $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$row['tell_uid']}'");

                        if(date('Y-m-d', $row['tell_date']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $row['tell_date']);
                        elseif(date('Y-m-d', $row['tell_date']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $row['tell_date']);
                        else
                            $dateTell = langdate('j F Y в H:i', $row['tell_date']);

                        if($row['public']){
                            $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                            $tell_link = 'public';
                            if($rowUserTell['photo'])
                                $avaTell = '/uploads/groups/'.$row['tell_uid'].'/50_'.$rowUserTell['photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        } else {
                            $tell_link = 'u';
                            if($rowUserTell['user_photo'])
                                $avaTell = '/uploads/users/'.$row['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                            else
                                $avaTell = '/images/no_ava_50.png';
                        }

                        $row['text'] = <<<HTML
                                {$row['tell_comm']}
                                <div class="wall_repost_border">
                                <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row['text']}
                                <div class="clear"></div>
                                </div>
                                HTML;
                    }

                    $tpl->set('{text}', stripslashes($row['text']));

                    $tpl->set('{subj}', stripslashes($row['theme']));
                    $tpl->set('{user-id}', $row['from_user_id']);

                    $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                    $tpl->set('{online}', $online);
                    $date = megaDate(strtotime($row['date']), 1, 1);
                    $tpl->set('{date}', $date);

                    $tpl->set('{msg-id}', $mid);

                    if($folder == 'inbox'){
                        $tpl->set('[inbox]', '');
                        $tpl->set('[/inbox]', '');
                        $tpl->set_block("'\\[outbox\\](.*?)\\[/outbox\\]'si","");
                    } else {
                        $tpl->set('[outbox]', '');
                        $tpl->set('[/outbox]', '');
                        $tpl->set_block("'\\[inbox\\](.*?)\\[/inbox\\]'si","");
                    }

                    if($row['pm_read'] == 'no'){
                        $tpl->set('[new]', '');
                        $tpl->set('[/new]', '');
                    } else
                        $tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si","");

                    $tpl->compile('content');

                    //Если статус сообщения не прочитано, то обновляем его
                    if($row['pm_read'] == 'no' AND $folder == 'inbox'){
                        $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '{$mid}'");
                        $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '".($mid+1)."'");
                        $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num-1 WHERE user_id = '{$user_id}'");
                        $db->query("UPDATE `".PREFIX."_im` SET msg_num = msg_num-1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$row['from_user_id']."'");

                        //Читисм кеш обновлений
                        Cache::mozg_clear_cache_file('user_'.$row['from_user_id'].'/im');
                    }
                } else
                    msgbox('', $lang['none_msg'], 'info_box');
            } else
                msgbox('', $lang['none_msg'], 'info_box');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function settTypeMsg($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();

            if($user_info['user_msg_type'] == 0)
                $db->query("UPDATE `".PREFIX."_users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

            if($user_info['user_msg_type'] == 1)
                $db->query("UPDATE `".PREFIX."_users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

            die();
        }
    }

    public function outbox($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $metatags['title'] = $lang['msg_outbox'];
            $user_speedbar = $lang['msg_outbox'];

            //Для поиска
            $se_query = $db->safesql(ajax_utf8(Validation::strip_data(urldecode($_GET['se_query']))));
            if(isset($se_query) AND !empty($se_query)){
                $search_sql = "AND tb2.user_search_pref LIKE '%{$se_query}%'";
                $query_string = '&se_query='.Validation::strip_data($_GET['se_query']);
            } else {
                $se_query = 'Поиск по отправленным сообщениям';
                $search_sql = '';
            }

            $query = "SELECT tb1.id, theme, text, from_user_id, date, pm_read, attach, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = tb2.user_id {$search_sql} AND  tb1.folder = 'outbox' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}";
            $sql_ = $db->super_query($query, 1);

            if($sql_)
                $msg_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = tb2.user_id {$search_sql} AND tb1.folder = 'outbox'");

            //header сообщений
            $tpl->load_template('messages/head.tpl');
            $tpl->set('{query}', $se_query);

            $titles = array('сообщение', 'сообщения', 'сообщений');//msg
            if($search_sql)
                if($sql_)
                    $tpl->set('{msg-cnt}', 'Найдено <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
                else
                    $tpl->set('{msg-cnt}', 'Найденные <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
            else
                if($sql_)
                    $tpl->set('{msg-cnt}', 'Вы отправили <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
                else
                    $tpl->set('{msg-cnt}', 'Нет отправленных сообщений');

            $tpl->set('[outbox]', '');
            $tpl->set('[/outbox]', '');
            $tpl->set_block("'\\[inbox\\](.*?)\\[/inbox\\]'si","");
            $tpl->set_block("'\\[review\\](.*?)\\[/review\\]'si","");
            $tpl->compile('info');

            //Если есть сообщения то продолжаем, если нет, то выводи информацию
            if($sql_){
                $tpl->load_template('messages/message.tpl');
                foreach($sql_ as $row){

                    if($row['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['from_user_id'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    $tpl->set('{subj}', stripslashes($row['theme']));

                    $tpl->set('{text}', iconv_substr(stripslashes(strip_tags($row['text'])), 0, 150, 'utf-8'));

                    $attach_filesPhoto = explode('photo_u|', $row['attach']);
                    if($attach_filesPhoto[1]) $attach_filesP = '<div class="msg_new_mes_ic_photo">Фотография</div>';
                    else $attach_filesP = '';

                    $attach_filesVideo = explode('video|', $row['attach']);
                    if($attach_filesVideo[1]) $attach_filesV = '<div class="msg_new_mes_ic_video">Видеозапись</div>';
                    else $attach_filesV = '';

                    $attach_filesSmile = explode('smile|', $row['attach']);
                    if($attach_filesSmile[1]) $attach_filesS = '<div class="msg_new_mes_ic_smile">Смайлик</div>';
                    else $attach_filesS = '';

                    $attach_filesAudio = explode('audio|', $row['attach']);
                    if($attach_filesAudio[1]) $attach_filesA = '<div class="msg_new_mes_ic_audio">Аудиозапись</div>';
                    else $attach_filesA = '';

                    $attach_filesDoc = explode('doc|', $row['attach']);
                    if($attach_filesDoc[1]) $attach_filesD = 'Файл';
                    else $attach_filesD = '';

                    $attach_filesVote = explode('vote|', $row['attach']);
                    if($attach_filesVote[1]) $attach_filesVX = 'Опрос';
                    else $attach_filesVX = '';

                    $tpl->set('{attach}', $attach_filesP.$attach_filesV.$attach_filesS.$attach_filesA.$attach_filesD.$attach_filesVX);

                    $tpl->set('{user-id}', $row['from_user_id']);
                    $tpl->set('{name}', $row['user_search_pref']);
                    $tpl->set('{mid}', $row['id']);

                    $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                    $tpl->set('{online}', $online);

                    $date = megaDate(strtotime($row['date']), 1, 1);
                    $tpl->set('{date}', $date);

                    if($row['pm_read'] == 'no'){
                        $tpl->set('[new]', '');
                        $tpl->set('[/new]', '');
                    } else
                        $tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si","");

                    $tpl->set('{folder}', 'outbox');
                    $tpl->compile('content');
                }
                if($msg_count['cnt'] >= $gcount)
                    navigation($gcount, $msg_count['cnt'], '/index.php?go=messages&act=outbox'.$query_string.'&page=');
            } else
                msgbox('', $lang['no_outbox_msg'], 'info_2');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Вывод всех полученных сообщений
     */
    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $user_info['user_msg_type']=1;
            $config = include __DIR__.'/data/config.php';

            if($user_info['user_msg_type'] == 1 OR $config['temp'] == 'mobile'){
                $spBar = false;
                //################### Вывод всех диалогов ###################//
                $metatags['title'] = 'Диалоги';
                $mobile_speedbar = '<a href="/messages">Диалоги</a>';

                //Вывод диалогов
                $sql_ = $db->super_query("SELECT tb1.msg_num, im_user_id, tb2.user_search_pref, user_photo FROM `".PREFIX."_im` tb1, `".PREFIX."_users` tb2 WHERE tb1.iuser_id = '".$user_id."' AND tb1.im_user_id = tb2.user_id ORDER by `idate` DESC LIMIT 0, 50", 1);
                $tpl->load_template('im/dialog.tpl');
                foreach($sql_ as $row){
                    $tpl->set('{name}', $row['user_search_pref']);
                    $tpl->set('{uid}', $row['im_user_id']);
                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['im_user_id'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    if($row['msg_num'])
                        $tpl->set('{msg_num}', '<div class="im_new fl_l" id="msg_num'.$row['im_user_id'].'">'.$row['msg_num'].'</div>');
                    else
                        $tpl->set('{msg_num}', '');
                    $tpl->compile('dialog');
                }

                //header сообщений
                $tpl->load_template('im/head.tpl');
                $tpl->set('{dialogs}', $tpl->result['dialog']);
                $tpl->set('[inbox]', '');
                $tpl->set('[/inbox]', '');
                $tpl->set_block("'\\[outbox\\](.*?)\\[/outbox\\]'si","");
                $tpl->set_block("'\\[review\\](.*?)\\[/review\\]'si","");
                $tpl->compile('info');
            } else {
                $metatags['title'] = $lang['msg_inbox'];
                $user_speedbar = $lang['msg_inbox'];

                //Вывод информации после отправки сообщения
                if($_GET['info'] == 1)
                    msgbox('', '<script type="text/javascript">setTimeout(\'$(".err_yellow").fadeOut()\', 1500);</script>Ваше сообщение успешно отправлено.', 'info');

                //Для поиска
                $se_query = $db->safesql(Validation::ajax_utf8(Validation::strip_data(urldecode($_GET['se_query']))));
                if(isset($se_query) AND !empty($se_query)){
                    $search_sql = "AND tb2.user_search_pref LIKE '%{$se_query}%'";
                    $query_string = '&se_query='.Validation::strip_data($_GET['se_query']);
                } else {
                    $se_query = 'Поиск по полученным сообщениям';
                    $search_sql = '';
                }

                //Запрос в БД на вывод сообщений
                $query = "SELECT tb1.id, theme, text, for_user_id, from_user_id, date, pm_read, attach, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.folder = 'inbox' AND tb1.from_user_id = tb2.user_id {$search_sql} ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_ = $db->super_query($query, 1);

                //Если есть ответ из БД, то считаем кол-вот ответа
                if($sql_)
                    $msg_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.folder = 'inbox' AND tb1.from_user_id = tb2.user_id {$search_sql}");

                //header сообщений
                $tpl->load_template('messages/head.tpl');

                if($user_info['user_msg_type'] == 0)
                    $tpl->set('{msg-type}', 'Показать в виде диалогов');
                else
                    $tpl->set('{msg-type}', 'Показать в виде сообщений');

                $tpl->set('{query}', $se_query);

                $titles = array('сообщение', 'сообщения', 'сообщений');//msg
                if($search_sql)
                    if($sql_)
                        $tpl->set('{msg-cnt}', 'Найдено <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
                    else
                        $tpl->set('{msg-cnt}', 'Найденные <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
                else
                    if($sql_)
                        $tpl->set('{msg-cnt}', 'Вы получили <span id="all_msg_num">'.$msg_count['cnt'].'</span> '.Gramatic::declOfNum($msg_count['cnt'], $titles));
                    else
                        $tpl->set('{msg-cnt}', 'Нет полученных сообщений');

                $tpl->set('[inbox]', '');
                $tpl->set('[/inbox]', '');
                $tpl->set_block("'\\[outbox\\](.*?)\\[/outbox\\]'si","");
                $tpl->set_block("'\\[review\\](.*?)\\[/review\\]'si","");
                $tpl->compile('info');

                //Если есть сообщения то продолжаем, если нет, то выводи информацию
                if($sql_){
                    $tpl->load_template('messages/message.tpl');
                    foreach($sql_ as $row){

                        if($row['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['from_user_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        $tpl->set('{subj}', stripslashes($row['theme']));

                        $tpl->set('{text}', iconv_substr(stripslashes(strip_tags($row['text'])), 0, 150, 'utf-8'));

                        $attach_filesPhoto = explode('photo_u|', $row['attach']);
                        if($attach_filesPhoto[1]) $attach_filesP = '<div class="msg_new_mes_ic_photo">Фотография</div>';
                        else $attach_filesP = '';

                        $attach_filesVideo = explode('video|', $row['attach']);
                        if($attach_filesVideo[1]) $attach_filesV = '<div class="msg_new_mes_ic_video">Видеозапись</div>';
                        else $attach_filesV = '';

                        $attach_filesSmile = explode('smile|', $row['attach']);
                        if($attach_filesSmile[1]) $attach_filesS = '<div class="msg_new_mes_ic_smile">Смайлик</div>';
                        else $attach_filesS = '';

                        $attach_filesAudio = explode('audio|', $row['attach']);
                        if($attach_filesAudio[1]) $attach_filesA = '<div class="msg_new_mes_ic_audio">Аудиозапись</div>';
                        else $attach_filesA = '';

                        $attach_filesVote = explode('vote|', $row['attach']);
                        if($attach_filesVote[1]) $attach_filesVX = 'Опрос';
                        else $attach_filesVX = '';

                        $attach_filesDoc = explode('doc|', $row['attach']);
                        if($attach_filesDoc[1]) $attach_filesD = 'Файл';
                        else $attach_filesD = '';

                        $tpl->set('{attach}', $attach_filesP.$attach_filesV.$attach_filesS.$attach_filesA.$attach_filesVX.$attach_filesD);

                        $tpl->set('{user-id}', $row['from_user_id']);
                        $tpl->set('{name}', $row['user_search_pref']);
                        $tpl->set('{mid}', $row['id']);

                        $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                        $tpl->set('{online}', $online);

                        $date = megaDate(strtotime($row['date']), 1, 1);
                        $tpl->set('{date}', $date);

                        if($row['pm_read'] == 'no'){
                            $tpl->set('[new]', '');
                            $tpl->set('[/new]', '');
                        } else
                            $tpl->set_block("'\\[new\\](.*?)\\[/new\\]'si","");

                        $tpl->set('{folder}', 'inbox');
                        $tpl->compile('content');
                    }

                    if($msg_count['cnt'] > $gcount)
                        navigation($gcount, $msg_count['cnt'], '/index.php?go=messages'.$query_string.'&page=');
                } else
                    msgbox('', $lang['no_msg'], 'info_2');
            }
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