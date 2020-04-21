<?php
/* 
	Appointment: Диалоги
	File: im.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Gramatic;
use System\Libs\Cache;
use System\Libs\Validation;

class ImController extends Module{

    public function send($params){
        $tpl = Registry::get('tpl');
        //$lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

//            AntiSpam('messages');

            $for_user_id = intval($_POST['for_user_id']);
            $msg = Validation::ajax_utf8($_POST['msg']);
            //$attach_files = Validation::ajax_utf8($_POST['attach_files']); //what? \/
            $my_ava = Validation::ajax_utf8($_POST['my_ava'], false, true);
            $my_name = Validation::ajax_utf8($_POST['my_name'], false, true);
            $attach_files = Validation::ajax_utf8($_POST['attach_files'], false, true);

            $attach_files = str_replace('vote|', 'hack|', $attach_files);

//            AntiSpam('identical', $msg.$attach_files);

            if(isset($msg) AND !empty($msg) OR isset($attach_files) OR !empty($attach_files)){

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '".$for_user_id."'");

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

//                        AntiSpamLogInsert('identical', $msg.$attach_files);

//                        if(!Tools::CheckFriends($for_user_id)) AntiSpamLogInsert('messages');

                        $server_time = intval($_SERVER['REQUEST_TIME']);

                        //Отправляем сообщение получателю
                        $db->query("INSERT INTO `".PREFIX."_messages` SET theme = '...', text = '".$msg."', for_user_id = '".$for_user_id."', from_user_id = '".$user_id."', date = '".$server_time."', pm_read = 'no', folder = 'inbox', history_user_id = '".$user_id."', attach = '".$attach_files."'");
                        $dbid2 = $db->insert_id();

                        //Сохраняем сообщение в папку отправленные
                        $db->query("INSERT INTO `".PREFIX."_messages` SET theme = '...', text = '".$msg."', for_user_id = '".$user_id."', from_user_id = '".$for_user_id."', date = '".$server_time."', pm_read = 'no', folder = 'outbox', history_user_id = '".$user_id."', attach = '".$attach_files."'");
                        $dbid = $db->insert_id();

                        //Обновляем кол-во новых сообщения у получателя
                        $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num+1 WHERE user_id = '".$for_user_id."'");

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

                            $msg_lnk = '/messages#'.$user_id;
                            // if($check2['user_msg_type'])
                            // else
                            // 	$msg_lnk = '/messages/show/'.$dbid2;

                            $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_id}', type = '8', date = '{$server_time}', text = '{$msg}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '{$msg_lnk}'");

                            Cache::mozg_create_cache("user_{$for_user_id}/updates", 1);

                        }

                        //Ответ скрипта
                        $tpl->load_template('im/msg.tpl');
                        $tpl->set('{ava}', $my_ava);
                        $tpl->set('{name}', $my_name);
                        $tpl->set('{user-id}', $user_id);

                        //Прикрипленные файлы
                        if($attach_files){
                            $attach_arr = explode('||', $attach_files);
                            $cnt_attach = 1;
                            //$jid = 0;
                            $attach_result = '';

                            $data = ''; //bug !!!
                            foreach($attach_arr as $attach_file){
                                $attach_type = explode('|', $attach_file);

                                //Фото
                                if($attach_type[0] == 'photo_u'){
                                    $attauthor_user_id = $user_id;

                                    if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                        $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                        $cnt_attach++;
                                    } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                        $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}");

                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                        $cnt_attach++;
                                    }

                                    //Видео
                                } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}"))
                                    $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                                //Музыка

                            elseif($attach_type[0] == 'audio'){
                                    $audioId = intval($data[0]);
                                    $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `".PREFIX."_audio` WHERE id = '{$audioId}'");
                                    if($row_audio){
                                        $stime = gmdate("i:s", $row_audio['duration']);
                                        if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                                        if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                                        $plname = 'wall';
                                        if($row_audio['oid'] != $user_info['user_id']) $q_s = '
									<div class="audioSettingsBut"><li class="icon-plus-6"
									onClick="gSearch.addAudio(\''.$row_audio['id'].'_'.$row_audio['oid'].'_'.$plname.'\')" onmouseover="showTooltip(this, {text: \'Добавить в мой список\', shift: [6,5,0]});"
									id="no_play"></li><div class="clear"></div></div>';
                                        else $q_s = '';
                                        $qauido = "<div class=\"audioPage audioElem search search_item\"
									id=\"audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									onclick=\"playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);\"><div
									class=\"area\"><table cellspacing=\"0\" cellpadding=\"0\"
									width=\"100%\"><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
									class=\"bl\"><div class=\"figure\"></div></div></div><input type=\"hidden\"
									value=\"{$row_audio['url']},{$row_audio['duration']},page\"
									id=\"audio_url_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"></td><td
									class=\"info\"><div class=\"audioNames\" style=\"width: 275px;\"><b class=\"author\"
									onclick=\"Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);\"
									id=\"artist\">{$row_audio['artist']}</b> – <span class=\"name\"
									id=\"name\">{$row_audio['title']}</span> <div class=\"clear\"></div></div><div
									class=\"audioElTime\"
									id=\"audio_time_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\">{$stime}</div>{$q_s}</td
									></tr></tbody></table><div id=\"player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" border=\"0\"
									cellpadding=\"0\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tbody><tr><td
									style=\"width: 100%;\"><div class=\"progressBar fl_l\" style=\"width: 100%;\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.progressDown(event, this);\"
									id=\"no_play\" onmousemove=\"audio_player.playerPrMove(event, this)\"
									onmouseout=\"audio_player.playerPrOut()\"><div class=\"audioTimesAP\"
									id=\"main_timeView\"><div class=\"audioTAP_strlka\">100%</div></div><div
									class=\"audioBGProgress\"></div><div class=\"audioLoadProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerPlayLine\"><div
									class=\"audioSlider\"></div></div></div></td><td><div class=\"audioVolumeBar fl_l\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.volumeDown(event, this);\"
									id=\"no_play\"><div class=\"audioTimesAP\"><div
									class=\"audioTAP_strlka\">100%</div></div><div class=\"audioBGProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerVolumeBar\"><div
									class=\"audioSlider\"></div></div></div> </td></tr></tbody></table></div></div></div>";
                                        $attach_result .= $qauido;
                                    }
                                    $resLinkTitle = '';

                                    //Смайлик
                                } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                    $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                    //Если документ
                                } elseif($attach_type[0] == 'doc'){

                                    $doc_id = intval($attach_type[1]);

                                    $row_doc = $db->super_query("SELECT dname, dsize FROM `".PREFIX."_doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                                    if($row_doc){

                                        $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$dbid.'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$dbid.'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                        $cnt_attach++;
                                    }

                                } else

                                    $attach_result .= '';
                            }
                            if($attach_result)
                                $msg = '<div style="width:442px;overflow:hidden">'.preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $msg).$attach_result.'</div><div class="clear"></div>';
                        } else
                            $msg = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $msg).$attach_result;

                        $tpl->set('{text}', stripslashes($msg));

                        $tpl->set('{msg-id}', $dbid);
                        $tpl->set('{new}', 'im_class_new');
                        $tpl->set('{date}', langdate('H:i:s', $server_time));
                        $tpl->compile('content');

                        //Читисм кеш обновлений
                        Cache::mozg_clear_cache_file('user_'.$for_user_id.'/im');
                        Cache::mozg_create_cache('user_'.$for_user_id.'/im_update', '1');
                        Cache::mozg_create_cache("user_{$for_user_id}/typograf{$user_id}", "");

                        Tools::AjaxTpl($tpl);

                        $params['tpl'] = $tpl;
                        Page::generate($params);
                        return true;
                    } else
                        echo 'err_privacy';
                } else
                    echo 'no_user';
            } else
                echo 'max_strlen';

            die();
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

            Tools::NoAjaxQuery();

            if($user_info['user_msg_type'] == 0)
                $db->query("UPDATE `".PREFIX."_users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

            if($user_info['user_msg_type'] == 1)
                $db->query("UPDATE `".PREFIX."_users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

            die();
        }
    }

    public function read($params){
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

            Tools::NoAjaxQuery();
            $msg_id = intval($_POST['msg_id']);

            $check = $db->super_query("SELECT from_user_id FROM `".PREFIX."_messages` WHERE id = '".$msg_id."' AND folder = 'inbox' AND pm_read = 'no'");

            if($check){
                $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '".$msg_id."'");
                $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '".($msg_id+1)."'");
                $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num-1 WHERE user_id = '".$user_id."'");
                $db->query("UPDATE `".PREFIX."_im` SET msg_num = msg_num-1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$check['from_user_id']."'");

                //Читисм кеш обновлений
                Cache::mozg_clear_cache_file('user_'.$check['from_user_id'].'/im');
            }

            die();
        }
    }

    public function typograf($params){
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

            Tools::NoAjaxQuery();

            $for_user_id = intval($_POST['for_user_id']);

            if($_GET['stop'] == 1)

                Cache::mozg_create_cache("user_{$for_user_id}/typograf{$user_id}", "");

            else

                Cache::mozg_create_cache("user_{$for_user_id}/typograf{$user_id}", 1);

            exit();
        }
    }

    public function update($params){
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

            Tools::NoAjaxQuery();
            $for_user_id = intval($_POST['for_user_id']);
            $last_id = intval($_POST['last_id']);
            $sess_last_id = Cache::mozg_cache('user_'.$user_id.'/im');

            $typograf = Cache::mozg_cache("user_{$user_id}/typograf{$for_user_id}");
            if($typograf) echo "<script>$('#im_typograf').fadeIn()</script>";

            if($last_id == $sess_last_id){
                echo 'no_new';
                die();
            }

            $count = $db->super_query("SELECT msg_num, all_msg_num FROM `".PREFIX."_im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
            if($count['all_msg_num'] > 20)
                $limit = $count['all_msg_num']-20;
            else
                $limit = 0;

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, from_user_id, attach, tell_uid, tell_date, public, tell_comm, tb2.user_name, user_photo FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = '{$for_user_id}' AND tb1.history_user_id = tb2.user_id ORDER by `date` ASC LIMIT ".$limit.", 20", 1);

            Cache::mozg_create_cache('user_'.$user_id.'/im', $last_id);

            if($sql_){
                $tpl->load_template('im/msg.tpl');
                $config = include __DIR__.'/../data/config.php';
                foreach($sql_ as $row){
                    $tpl->set('{name}', $row['user_name']);
                    $tpl->set('{folder}', $row['folder']);
                    $tpl->set('{user-id}', $row['history_user_id']);
                    $tpl->set('{msg-id}', $row['id']);
                    if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time)) $tpl->set('{date}', langdate('H:i:s', $row['date']));
                    else $tpl->set('{date}', langdate('d.m.y', $row['date']));
                    if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['history_user_id'].'/50_'.$row['user_photo']);
                    else $tpl->set('{ava}', '/images/no_ava_50.png');
                    if($row['pm_read'] == 'no'){
                        $tpl->set('{new}', 'im_class_new');
                        $tpl->set('{read-js-func}', 'onMouseOver="im.read(\''.$row['id'].'\', '.$row['history_user_id'].', '.$user_id.')"');
                    } else {
                        $tpl->set('{new}', '');
                        $tpl->set('{read-js-func}', '');
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

                                $size = getimagesize(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}");

                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                if($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                elseif($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;

                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}");

                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" {$size[3]} align=\"left\" /></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM
									`".PREFIX."_audio` WHERE id = '{$audioId}'");
                                if($row_audio){
                                    $stime = gmdate("i:s", $row_audio['duration']);
                                    if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                                    if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                                    $plname = 'wall';
                                    if($row_audio['oid'] != $user_info['user_id']) $q_s = <<<HTML
									<div class="audioSettingsBut"><li class="icon-plus-6"
									onClick="gSearch.addAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}')" onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
									id="no_play"></li><div class="clear"></div></div>
									HTML;
                                    else $q_s = '';
                                    $qauido = "<div class=\"audioPage audioElem search search_item\"
									id=\"audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									onclick=\"playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);\"><div
									class=\"area\"><table cellspacing=\"0\" cellpadding=\"0\"
									width=\"100%\"><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
									class=\"bl\"><div class=\"figure\"></div></div></div><input type=\"hidden\"
									value=\"{$row_audio['url']},{$row_audio['duration']},page\"
									id=\"audio_url_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"></td><td
									class=\"info\"><div class=\"audioNames\" style=\"width: 275px;\"><b class=\"author\"
									onclick=\"Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);\"
									id=\"artist\">{$row_audio['artist']}</b> – <span class=\"name\"
									id=\"name\">{$row_audio['title']}</span> <div class=\"clear\"></div></div><div
									class=\"audioElTime\"
									id=\"audio_time_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\">{$stime}</div>{$q_s}</td
									></tr></tbody></table><div id=\"player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" border=\"0\"
									cellpadding=\"0\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tbody><tr><td
									style=\"width: 100%;\"><div class=\"progressBar fl_l\" style=\"width: 100%;\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.progressDown(event, this);\"
									id=\"no_play\" onmousemove=\"audio_player.playerPrMove(event, this)\"
									onmouseout=\"audio_player.playerPrOut()\"><div class=\"audioTimesAP\"
									id=\"main_timeView\"><div class=\"audioTAP_strlka\">100%</div></div><div
									class=\"audioBGProgress\"></div><div class=\"audioLoadProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerPlayLine\"><div
									class=\"audioSlider\"></div></div></div></td><td><div class=\"audioVolumeBar fl_l\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.volumeDown(event, this);\"
									id=\"no_play\"><div class=\"audioTimesAP\"><div
									class=\"audioTAP_strlka\">100%</div></div><div class=\"audioBGProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerVolumeBar\"><div
									class=\"audioSlider\"></div></div></div> </td></tr></tbody></table></div></div></div>";
                                    $attach_result .= $qauido;
                                }
                                $resLinkTitle = '';

                                //Смайлик
                            } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';
                                //Если ссылка
                            } elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false){
                                $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type[1]);
                                $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                                $attach_type[3] = stripslashes($attach_type[3]);
                                $attach_type[3] = substr($attach_type[3], 0, 200);

                                $attach_type[2] = stripslashes($attach_type[2]);
                                $str_title = substr($attach_type[2], 0, 55);

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
                            $row['text'] = $resLinkTitle.'<div class="clear"></div>'.$attach_result;
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

                    $tpl->compile('content');
                }

                Tools::AjaxTpl($tpl);

//                $params['tpl'] = $tpl;
//                Page::generate($params);
                return true;
            }

            die();
        }
    }

    public function history($params){
        $tpl = Registry::get('tpl');
        //$lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();
            $for_user_id = intval($_POST['for_user_id']);
            $first_id = intval($_POST['first_id']);

            Cache::mozg_create_cache("user_{$for_user_id}/typograf{$user_id}", "");

            $config = include __DIR__.'/data/config.php';

            //FOR MOBILE VERSION 1.0
            if($config['temp'] == 'mobile'){

                $sqlNEW = $db->super_query("SELECT id FROM `".PREFIX."_messages` WHERE history_user_id = '{$for_user_id}' AND for_user_id = '{$user_id}' AND pm_read = 'no'", 1);

                if($sqlNEW){

                    $newMSGnum = 0;

                    foreach($sqlNEW as $rowNEW){

                        $newMSGnum++;

                        //Обновляем кол-во сообщений
                        $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '".$rowNEW['id']."'");
                        $db->query("UPDATE `".PREFIX."_messages` SET pm_read = 'yes' WHERE id = '".($rowNEW['id']+1)."'");

                    }

                    $db->query("UPDATE `".PREFIX."_im` SET msg_num = msg_num-{$newMSGnum} WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                    $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num-{$newMSGnum} WHERE user_id = '".$user_id."'");

                    //Читисм кеш обновлений
                    Cache::mozg_clear_cache_file('user_'.$for_user_id.'/im');

                }

                $limit_msg = 5;

            } else

                $limit_msg = 20;

            if($first_id > 0){
                $count = $db->super_query("SELECT COUNT(*) AS all_msg_num FROM `".PREFIX."_messages` WHERE from_user_id = '".$for_user_id."' AND for_user_id = '".$user_id."' AND id < ".$first_id);
                $sql_sort = "AND id < ".$first_id;

                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            } else {
                $count = $db->super_query("SELECT all_msg_num FROM `".PREFIX."_im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            }

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, from_user_id, attach, tell_uid, tell_date, public, tell_comm, tb2.user_name, user_photo FROM `".PREFIX."_messages` tb1, `".PREFIX."_users` tb2 WHERE tb1.for_user_id = '".$user_id."' AND tb1.from_user_id = '".$for_user_id."' AND tb1.history_user_id = tb2.user_id {$sql_sort} ORDER by `date` DESC LIMIT ".$limit.", ".$limit_msg, 1);
            $tpl->load_template('im/msg.tpl');

            if(!$first_id){
                $tpl->result['content'] .= '<div class="im_scroll">';

                // FOR MOBILE
                if($config['temp'] == 'mobile')
                    $stylesMOB = 'width:100%;margin-bottom:10px';
                else
                    $stylesMOB = 'width:520px';

                if($count['all_msg_num'] > $limit_msg)
                    $tpl->result['content'] .= '<div class="cursor_pointer" onClick="im.page('.$for_user_id.'); return false" id="wall_all_records" style="'.$stylesMOB.'"><div class="public_wall_all_comm" id="load_wall_all_records" style="margin-left:0px">Показать предыдущие сообщения</div></div><div id="prevMsg"></div>';

                $tpl->result['content'] .= '<div id="im_scroll">';

                if(!$sql_)
                    $tpl->result['content'] .= '<div class="info_center"><div style="padding-top:210px">Здесь будет выводиться история переписки.</div></div>';
            }

            if($sql_){
                foreach($sql_ as $row){
                    $tpl->set('{name}', $row['user_name']);
                    $tpl->set('{folder}', $row['folder']);
                    $tpl->set('{user-id}', $row['history_user_id']);
                    $tpl->set('{msg-id}', $row['id']);
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time)) $tpl->set('{date}', langdate('H:i:s', $row['date']));
                    else $tpl->set('{date}', langdate('d.m.y', $row['date']));
                    if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['history_user_id'].'/50_'.$row['user_photo']);
                    else $tpl->set('{ava}', '/images/no_ava_50.png');
                    if($row['pm_read'] == 'no'){
                        $tpl->set('{new}', 'im_class_new');
                        $tpl->set('{read-js-func}', 'onMouseOver="im.read(\''.$row['id'].'\', '.$row['history_user_id'].', '.$user_id.')"');
                    } else {
                        $tpl->set('{new}', '');
                        $tpl->set('{read-js-func}', '');
                    }

                    //Прикрипленные файлы
                    if($row['attach']){
                        $attach_arr = explode('||', $row['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
                        // $jid = 0; //?
                        $attach_result = '';
                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}");

                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                if($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                elseif($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;

                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}");

                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" {$size[3]} align=\"left\" /></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `".PREFIX."_audio` WHERE id = '{$audioId}'");
                                if($row_audio){
                                    $stime = gmdate("i:s", $row_audio['duration']);
                                    if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                                    if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                                    $plname = 'wall';
                                    if($row_audio['oid'] != $user_info['user_id']) $q_s = <<<HTML
									<div class="audioSettingsBut"><li class="icon-plus-6"
									onClick="gSearch.addAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}')" onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
									id="no_play"></li><div class="clear"></div></div>
									HTML;
                                    else $q_s = '';
                                    $qauido = "<div class=\"audioPage audioElem search search_item\"
									id=\"audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									onclick=\"playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);\"><div
									class=\"area\"><table cellspacing=\"0\" cellpadding=\"0\"
									width=\"100%\"><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
									class=\"bl\"><div class=\"figure\"></div></div></div><input type=\"hidden\"
									value=\"{$row_audio['url']},{$row_audio['duration']},page\"
									id=\"audio_url_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"></td><td
									class=\"info\"><div class=\"audioNames\" style=\"width: 275px;\"><b class=\"author\"
									onclick=\"Page.Go('/?go=search&query=&type=5&q='+this.innerHTML);\"
									id=\"artist\">{$row_audio['artist']}</b> – <span class=\"name\"
									id=\"name\">{$row_audio['title']}</span> <div class=\"clear\"></div></div><div
									class=\"audioElTime\"
									id=\"audio_time_{$row_audio['id']}_{$row_audio['oid']}_{$plname}\">{$stime}</div>{$q_s}</td
									></tr></tbody></table><div id=\"player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\"
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" border=\"0\"
									cellpadding=\"0\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tbody><tr><td
									style=\"width: 100%;\"><div class=\"progressBar fl_l\" style=\"width: 100%;\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.progressDown(event, this);\"
									id=\"no_play\" onmousemove=\"audio_player.playerPrMove(event, this)\"
									onmouseout=\"audio_player.playerPrOut()\"><div class=\"audioTimesAP\"
									id=\"main_timeView\"><div class=\"audioTAP_strlka\">100%</div></div><div
									class=\"audioBGProgress\"></div><div class=\"audioLoadProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerPlayLine\"><div
									class=\"audioSlider\"></div></div></div></td><td><div class=\"audioVolumeBar fl_l\"
									onclick=\"cancelEvent(event);\" onmousedown=\"audio_player.volumeDown(event, this);\"
									id=\"no_play\"><div class=\"audioTimesAP\"><div
									class=\"audioTAP_strlka\">100%</div></div><div class=\"audioBGProgress\"></div><div
									class=\"audioPlayProgress\" id=\"playerVolumeBar\"><div
									class=\"audioSlider\"></div></div></div> </td></tr></tbody></table></div></div></div>";
                                    $attach_result .= $qauido;
                                }
                                $resLinkTitle = '';

                                //Смайлик
                            } elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';
                                //Если ссылка
                            } elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false){
                                $count_num = count($attach_type);
                                $domain_url_name = explode('/', $attach_type[1]);
                                $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                                $attach_type[3] = stripslashes($attach_type[3]);
                                $attach_type[3] = substr($attach_type[3], 0, 200);

                                $attach_type[2] = stripslashes($attach_type[2]);
                                $str_title = substr($attach_type[2], 0, 55);

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
                            $row['text'] = $resLinkTitle.'<div class="clear"></div>'.$attach_result;
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

                        $row['text'] = "
                                {$row['tell_comm']}
                                <div class=\"wall_repost_border\">
                                <div class=\"wall_tell_info\"><div class=\"wall_tell_ava\"><a href=\"/{$tell_link}{$row['tell_uid']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$avaTell}\" width=\"30\" /></a></div><div class=\"wall_tell_name\"><a href=\"/{$tell_link}{$row['tell_uid']}\" onClick=\"Page.Go(this.href); return false\"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class=\"wall_tell_date\">{$dateTell}</div></div>{$row['text']}
                                <div class=\"clear\"></div>
                                </div>
                                ";
                    }

                    $tpl->set('{text}', stripslashes($row['text']));

                    $tpl->compile('content');
                }
            }

            if(!$first_id)
                $tpl->result['content'] .= '</div></div>';

            if(!$first_id){
                $tpl->load_template('im/form.tpl');
                $tpl->set('{for_user_id}', $for_user_id);
                //Выводим информцию о том кто смотрит страницу для себя
                $myInfo = $db->super_query("SELECT user_name FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
                $tpl->set('{myuser-id}', $user_id);
                $tpl->set('{my-name}', $myInfo['user_name']);
                if($user_info['user_photo'])
                    $tpl->set('{my-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                else
                    $tpl->set('{my-ava}', '/images/no_ava_50.png');
                $tpl->compile('content');
            }

            Tools::AjaxTpl($tpl);


            return true;
        }
    }

    public function upDialogs($params){
        //$tpl = Registry::get('tpl');
        //$lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();
            $update = Cache::mozg_cache('user_'.$user_id.'/im_update');

            if($update){
                $sql_ = $db->super_query("SELECT tb1.msg_num, im_user_id FROM `".PREFIX."_im` tb1, `".PREFIX."_users` tb2 WHERE tb1.iuser_id = '".$user_id."' AND tb1.im_user_id = tb2.user_id AND msg_num > 0 ORDER by `idate` DESC LIMIT 0, 50", 1);
                $res = '';
                foreach($sql_ as $row){
                    $res .= '$("#upNewMsg'.$row['im_user_id'].'").html(\'<div class="im_new fl_l" id="msg_num'.$row['im_user_id'].'">'.$row['msg_num'].'</div>\').show();';
                }

                if($user_info['user_pm_num']){
                    $user_pm_num_2 = '<span class="badge badge-secondary">+'.$user_info['user_pm_num'].'</span>';
                    $doc_title = 'document.title = \'('.$user_info['user_pm_num'].') Новые сообщения\';';
                } else {
                    $user_pm_num_2 = '';
                    $doc_title = 'document.title = \'Диалоги\';';
                    Cache::mozg_create_cache('user_'.$user_id.'/im_update', '0');
                }

                echo '<script type="text/javascript">
				'.$doc_title.'
				$(\'#new_msg\').html(\''.$user_pm_num_2.'\');
				'.$res.'
				</script>';
            }
            die();
        }
    }

    public function del($params){
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

            $im_user_id = intval($_POST['im_user_id']);

            //Выводим информацию о диалоге
            $row = $db->super_query("SELECT msg_num, all_msg_num FROM `".PREFIX."_im` WHERE iuser_id = '{$user_id}' AND im_user_id = '{$im_user_id}'");

            if($row){

                //Удаляем сообщения
                if($row['all_msg_num']){

                    $db->query("DELETE FROM `".PREFIX."_messages` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$im_user_id}'");

                }

                //Если есть новые сообщения
                if($row['msg_num']){

                    $db->query("UPDATE `".PREFIX."_users` SET user_pm_num = user_pm_num-{$row['msg_num']} WHERE user_id = '{$user_id}'");

                }

                //Удаляем сам диалог
                $db->query("DELETE FROM `".PREFIX."_im` WHERE iuser_id = '{$user_id}' AND im_user_id = '{$im_user_id}'");

            }

            exit;

        }
    }

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