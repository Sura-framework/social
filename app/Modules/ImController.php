<?php

declare(strict_types=1);

namespace App\Modules;

use App\Libs\Antispam;
use App\Libs\Friends;
use Sura\Cache\Cache;
use Sura\Cache\Storages\MemcachedStorage;
use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;
use Sura\Time\Date;

class ImController extends Module{

    /**
     * Отправка сообщения
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function send(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            Antispam::Check(2, $user_id);
            //FIXME update response antispam

            $request = (Request::getRequest()->getGlobal());

            $for_user_id = (int)$request['for_user_id'];
            $msg = Validation::ajax_utf8($request['msg']);
            $my_ava = Validation::ajax_utf8($request['my_ava']);
            $my_name = Validation::ajax_utf8($request['my_name']);
            $attach_files = Validation::ajax_utf8($request['attach_files']);

            $attach_files = str_replace('vote|', 'hack|', $attach_files);

            Antispam::Check(4, $user_id, $msg.$attach_files);
            //FIXME update response antispam

            if(isset($msg) AND !empty($msg) OR isset($attach_files) OR !empty($attach_files)){

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '".$for_user_id."'");

                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //ЧС
                    $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($for_user_id);

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_msg'] == 2)
                        $check_friend = (new \App\Libs\Friends)->CheckFriends($for_user_id);
                    else{
                        $check_friend = false;
                    }

                    if(!$CheckBlackList AND $user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $check_friend)
                        $Privacy = true;
                    else
                        $Privacy = false;

                    if($Privacy AND $user_id != $for_user_id){

                        Antispam::LogInsert(4, $user_id, $msg.$attach_files );

                        if(!(new \App\Libs\Friends)->CheckFriends($for_user_id))
                            Antispam::LogInsert(2, $user_id);

                        $server_time = Date::time();

                        //Отправляем сообщение получателю
                        $db->query("INSERT INTO `messages` SET theme = '...', text = '".$msg."', for_user_id = '".$for_user_id."', from_user_id = '".$user_id."', date = '".$server_time."', pm_read = 'no', folder = 'inbox', history_user_id = '".$user_id."', attach = '".$attach_files."'");
//                        $dbid2 = $db->insert_id();

                        //Сохраняем сообщение в папку отправленные
                        $db->query("INSERT INTO `messages` SET theme = '...', text = '".$msg."', for_user_id = '".$user_id."', from_user_id = '".$for_user_id."', date = '".$server_time."', pm_read = 'no', folder = 'outbox', history_user_id = '".$user_id."', attach = '".$attach_files."'");
                        $dbid = $db->insert_id();

                        //Обновляем кол-во новых сообщения у получателя
                        $db->query("UPDATE `users` SET user_pm_num = user_pm_num+1 WHERE user_id = '".$for_user_id."'");

                        //Проверка на наличии созданного диалога у себя
                        $check_im = $db->super_query("SELECT iuser_id FROM `im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                        if(!$check_im){
                            $db->query("INSERT INTO im SET iuser_id = '".$user_id."', im_user_id = '".$for_user_id."', idate = '".$server_time."', all_msg_num = 1");
                        }else{
                            $db->query("UPDATE im  SET idate = '".$server_time."', all_msg_num = all_msg_num+1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                        }

                        //Проверка на наличии созданного диалога у получателя, а если есть то просто обновляем кол-во новых сообщений в диалоге
                        $check_im_2 = $db->super_query("SELECT iuser_id FROM im WHERE iuser_id = '".$for_user_id."' AND im_user_id = '".$user_id."'");
                        if(!$check_im_2){
                            $db->query("INSERT INTO im SET iuser_id = '".$for_user_id."', im_user_id = '".$user_id."', msg_num = 1, idate = '".$server_time."', all_msg_num = 1");
                        }else{
                            $db->query("UPDATE im  SET idate = '".$server_time."', msg_num = msg_num+1, all_msg_num = all_msg_num+1 WHERE iuser_id = '".$for_user_id."' AND im_user_id = '".$user_id."'");
                        }

                        //Вставляем событие в моментальные оповещения
                        $check2 = $db->super_query("SELECT user_last_visit FROM `users` WHERE user_id = '{$for_user_id}'");
                        $update_time = $server_time - 70;

                        if($check2['user_last_visit'] >= $update_time){

                            $msg_lnk = '/messages#'.$user_id;
                            // if($check2['user_msg_type'])
                            // else
                            // 	$msg_lnk = '/messages/show/'.$dbid2;

                            $db->query("INSERT INTO `updates` SET for_user_id = '{$for_user_id}', from_user_id = '{$user_id}', type = '8', date = '{$server_time}', text = '{$msg}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '{$msg_lnk}'");

                            $storage = new MemcachedStorage('localhost');
                            $cache = new Cache($storage, 'users');
                            $cache->save("{$for_user_id}/updates", 1);
                        }

                        //Ответ скрипта
//                        $tpl->load_template('im/msg.tpl');
                        $params['ava'] = $my_ava;
                        $params['name'] = $my_name;
                        $params['user_id'] = $user_id;

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

                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                        $cnt_attach++;
                                    } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                        $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}");

                                        $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                        $cnt_attach++;
                                    }

                                    //Видео
                                }
                                elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){
                                    $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\"  alt=\"photo\"/></a></div>";

                                }
                                //Музыка
                                elseif($attach_type[0] == 'audio'){
                                    $audioId = intval($data[0]);
                                    $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$audioId}'");
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
									class=\"area\"><table 
									><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
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
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" ><table ><tbody><tr><td
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
//                                    $resLinkTitle = '';

                                    //Смайлик
                                }
                                elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                    $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';
                                    //Если документ
                                }
                                elseif($attach_type[0] == 'doc'){

                                    $doc_id = intval($attach_type[1]);

                                    $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false);

                                    if($row_doc){

                                        $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$dbid.'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$dbid.'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                        $cnt_attach++;
                                    }

                                } else

                                    $attach_result .= '';
                            }
                            if($attach_result)
                                $msg = '<div style="width:442px;overflow:hidden">'.preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $msg).$attach_result.'</div><div class="clear"></div>';
                        } else{
                            $attach_result = '';
                            $msg = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $msg).$attach_result;
                        }

                        $params['text'] = stripslashes($msg);
                        $params['msg_id'] = $dbid;
                        $params['new'] = 'im_class_new';
                        $params['date'] = Langs::lang_date('H:i:s', $server_time);

                        //Читисм кеш обновлений
                        $storage = new MemcachedStorage('localhost');
                        $cache = new Cache($storage, 'users');
                        $cache->remove("{$for_user_id}/im");
                        $cache->save("{$for_user_id}/im_update", 1);
                        $cache->save("{$for_user_id}/typograf{$user_id}", "");
//

                        //$params['tpl'] = $tpl;
                        //Page::generate();
//                        return view('im.msg', $params);
                        $status = Status::OK;
                        $res =  view('im.msg', $params);
                    }
                    else {
                        $status = Status::PRIVACY;
                        $res = null;
                    }
                }
                else {
                    $status = Status::NOT_USER;
                    $res = null;
                }
            } else {
                $status = Status::NOT_DATA;
                $res = null;
            }
        }
        else{
            $status = Status::BAD_LOGGED;
            $res = null;
        }
        return _e_json(array(
            'status' => $status,
            'res' => $res
        ) );
    }

    public function user(): int
    {
//        $request = (Request::getRequest()->getGlobal());
        $server = Request::getRequest()->server;
        $path = explode('/', $server['REQUEST_URI']);
        $first_id = (int)$_POST['first_id'];
        $for_user_id = $path['2'];
        $params['for_user_id'] = $for_user_id;
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];

            //Вывод диалогов
            $sql_ = $db->super_query("SELECT tb1.msg_num, im_user_id, tb2.user_search_pref, user_photo FROM `im` tb1, `users` tb2 WHERE tb1.iuser_id = '".$user_id."' AND tb1.im_user_id = tb2.user_id ORDER by `idate` DESC LIMIT 0, 50", 1);
//            $tpl->load_template('im/dialog.tpl');
            foreach($sql_ as $key => $row){
                $sql_[$key]['name'] = $row['user_search_pref'];
                $sql_[$key]['uid'] = $row['im_user_id'];
                if($row['user_photo']){
                    $sql_[$key]['ava'] = '/uploads/users/'.$row['im_user_id'].'/50_'.$row['user_photo'];
                }else{
                    $sql_[$key]['ava'] = '/images/no_ava_50.png';
                }
                if($row['msg_num']){
                    $sql_[$key]['msg_num'] = '<div class="im_new fl_l" id="msg_num'.$row['im_user_id'].'">'.$row['msg_num'].'</div>';
                }else{
                    $sql_[$key]['msg_num'] = '';
                }
            }
            $params['dialog'] = $sql_;
            $sql_ = null;

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');

            try {
                $cache->save("{$for_user_id}/typograf{$user_id}", "");
            } catch (\Throwable $e) {
            }


            $config = Settings::load();

            //FOR MOBILE VERSION 1.0
            if($config['temp'] == 'mobile'){
                $sqlNEW = $db->super_query("SELECT id FROM `messages` WHERE history_user_id = '{$for_user_id}' AND for_user_id = '{$user_id}' AND pm_read = 'no'", 1);
                if($sqlNEW){
                    $newMSGnum = 0;
                    foreach($sqlNEW as $rowNEW){
                        $newMSGnum++;
                        //Обновляем кол-во сообщений
                        $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".$rowNEW['id']."'");
                        $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".($rowNEW['id']+1)."'");
                    }
                    $db->query("UPDATE `im` SET msg_num = msg_num-{$newMSGnum} WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                    $db->query("UPDATE `users` SET user_pm_num = user_pm_num-{$newMSGnum} WHERE user_id = '".$user_id."'");
                    //Читисм кеш обновлений

                    try {
                        $cache->remove("{$for_user_id}/im");
                    } catch (\Throwable $e) {
                    }

                }
                $limit_msg = 5;
            } else {
                $limit_msg = 20;
            }
            if($first_id > 0){
                $count = $db->super_query("SELECT COUNT(*) AS all_msg_num FROM `messages` WHERE from_user_id = '".$for_user_id."' AND for_user_id = '".$user_id."' AND id < ".$first_id);
                $sql_sort = "AND id < ".$first_id;
                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            } else {
                $count = $db->super_query("SELECT all_msg_num FROM `im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            }
            if (!isset($sql_sort)){
                $sql_sort = '';//FIXME
            }

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, from_user_id, attach, tell_uid, tell_date, public, tell_comm, tb2.user_name, user_photo FROM `messages` tb1, `users` tb2 WHERE tb1.for_user_id = '".$user_id."' AND tb1.from_user_id = '".$for_user_id."' AND tb1.history_user_id = tb2.user_id {$sql_sort} ORDER by `date` DESC LIMIT ".$limit.", ".$limit_msg, true);
//            if(!$first_id){

//                if($count['all_msg_num'] > $limit_msg){
//                    $stylesMOB = 'width:520px';
//                    $tpl->result['content'] .= '<div class="cursor_pointer" onClick="im.page('.$for_user_id.'); return false" id="wall_all_records" style="'.$stylesMOB.'"><div class="public_wall_all_comm" id="load_wall_all_records" style="margin-left:0px">Показать предыдущие сообщения</div></div><div id="prevMsg"></div>';
//                }

                // $tpl->result['content'] .= '<div id="im_scroll">';
//            }

            if($sql_){
                foreach($sql_ as $key => $row){
                    $sql_[$key]['name'] = $row['user_name'];
                    $sql_[$key]['folder'] = $row['folder'];
                    $sql_[$key]['user-id'] = $row['history_user_id'];
                    $sql_[$key]['msg-id'] = $row['id'];
                    $server_time = Date::time();
                    if(date('Y-m-d', (int)$row['date']) == date('Y-m-d', $server_time)) {
                        $sql_[$key]['date'] = Langs::lang_date('H:i:s', (int)$row['date']);
                    }else{
                        $sql_[$key]['date'] = Langs::lang_date('d.m.y', (int)$row['date']);
                    }
                    if($row['user_photo']){
                        $sql_[$key]['ava'] = '/uploads/users/'.$row['history_user_id'].'/50_'.$row['user_photo'];
                    }else{
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }
                    if($row['pm_read'] == 'no'){
                        $sql_[$key]['new'] = 'im_class_new';
                        $sql_[$key]['read_js_func'] = 'onMouseOver="im.read(\''.$row['id'].'\', '.$row['history_user_id'].', '.$user_id.')"';
                    } else {
                        $sql_[$key]['new'] = '';
                        $sql_[$key]['read_js_func'] =  '';
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

                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            }
                            elseif($attach_type[0] == 'photo_u'){
                                if($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                elseif($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                    $cnt_attach++;

                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            }
                            elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}");

                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" {$size[3]}  alt=\"photo\"/></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            }
                            elseif($attach_type[0] == 'audio'){
                                if (!isset($audioId))
                                    $audioId = null;//bug:undefined
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$audioId}'");
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
									class=\"area\"><table><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
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
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" ><table ><tbody><tr><td
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
                            }
                            elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';
                                //Если ссылка
                            }
                            elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false){
//                                $count_num = count($attach_type);
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

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="border:0px"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=\"photo\"/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                }

                                $cnt_attach_link++;

                                //Если документ
                            }
                            elseif($attach_type[0] == 'doc'){

                                $doc_id = intval($attach_type[1]);

                                $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false);

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            }
                            elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false);

                                if($vote_id){

                                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false);

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row['text'])
                                        $row['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", true);
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

                            }
                            else{
                                $attach_result .= '';
                                $resLinkTitle = '';
                                $resLinkUrl = '';
                            }
                        }

                        if(!empty($resLinkTitle) AND $row['text'] == $resLinkUrl OR !$row['text'])
                            $row['text'] = $resLinkTitle.'<div class="clear"></div>'.$attach_result;
                        else if($attach_result)
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']).$attach_result;
                        else
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);

                    } else{
                        $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);
                    }

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($row['tell_uid']){
                        if($row['public'])
                            $rowUserTell = $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$row['tell_uid']}'", false);
                        else
                            $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$row['tell_uid']}'");

                        if ($row['tell_date']){
                            if(date('Y-m-d', $row['tell_date']) == date('Y-m-d', $server_time)) {
                                $row = Langs::lang_date('сегодня в H:i', $row['tell_date']);
                            }
                            elseif(date('Y-m-d', $row['tell_date']) == date('Y-m-d', ($server_time-84600))) {
                                $row = Langs::lang_date('вчера в H:i', $row['tell_date']);
                            }
                            else {
                                $row = Langs::lang_date('j F Y в H:i', $row['tell_date']);
                            }
                        }else{
                            $row = 'N/A';
                        }

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

//                    $tpl->set('{text}', stripslashes($row['text']));
//                    $tpl->compile('content');
                }
            }
//            else{
                //$tpl->result['content'] .= '<div class="info_center"><div style="padding-top:210px">Здесь будет выводиться история переписки.</div></div>';

//            }
            $params['im'] = $sql_;
            if(!$first_id){
                $params['first_id'] = false;
//                $tpl->result['content'] .= '</div></div>';

//                $tpl->load_template('im/form.tpl');
//                $tpl->set('{for_user_id}', $for_user_id);
                $params['for_user_id'] = $for_user_id;
                //Выводим информцию о том кто смотрит страницу для себя
                $myInfo = $db->super_query("SELECT user_name FROM `users` WHERE user_id = '".$user_id."'");
//                $tpl->set('{myuser-id}', $user_id);
                $params['myuser_id'] = $user_id;
//                $tpl->set('{my-name}', $myInfo['user_name']);
                $params['my_name'] = $myInfo['user_name'];
                if($user_info['user_photo']){
//                    $tpl->set('{my-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                    $params['my_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                }
                else{
//                    $tpl->set('{my-ava}', '/images/no_ava_50.png');
                    $params['my_ava'] = '/images/no_ava_50.png';
                }
//                $tpl->compile('content');
            }else{
                $params['first_id'] = true;
            }

            //header сообщений
            $params['inbox'] = true;
            $params['outbox'] = false;
            $params['review'] = false;

            $params['title'] = 'Чаты';
            $params['info'] = $lang['not_logged'];
            return view('im.user', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Смена типа сообщений
     * @deprecated
     */
    public function settTypeMsg(){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){

//            Tools::NoAjaxQuery();

            if($user_info['user_msg_type'] == 0)
                $db->query("UPDATE `users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

            if($user_info['user_msg_type'] == 1)
                $db->query("UPDATE `users` SET user_msg_type = 1 WHERE user_id = '".$user_info['user_id']."'");

        }
    }

    /**
     * Прочтение сообщения
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function read(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();
            $msg_id = intval($_POST['msg_id']);

            $check = $db->super_query("SELECT from_user_id FROM `messages` WHERE id = '".$msg_id."' AND folder = 'inbox' AND pm_read = 'no'");

            if($check){
                $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".$msg_id."'");
                $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".($msg_id+1)."'");
                $db->query("UPDATE `users` SET user_pm_num = user_pm_num-1 WHERE user_id = '".$user_id."'");
                $db->query("UPDATE `im` SET msg_num = msg_num-1 WHERE iuser_id = '".$user_id."' AND im_user_id = '".$check['from_user_id']."'");

                //Читисм кеш обновлений
                $storage = new MemcachedStorage('localhost');
                $cache = new Cache($storage, 'users');
                $cache->remove("{$check['from_user_id']}/im");

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
     * Отправка инф. что набираем сообщение
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function typograf(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
//        $db = $this->db();

        $storage = new MemcachedStorage('localhost');
        $cache = new Cache($storage, 'users');

        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $user_id = $user_info['user_id'];
            $for_user_id = intval($request['for_user_id']);
            if ($for_user_id > 0){
                if(isset($request['stop'] ) AND $request['stop'] == 1){
                    $cache->save("{$for_user_id}/typograf{$user_id}", "");
                }
                else{
                    $cache->save("{$for_user_id}/typograf{$user_id}", "1");
                }

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
     *  Обновление окна сообщений каждые 2 сек
     *
     * @return int
     * @throws \Throwable
     */
    public function update(): int
    {
        if (!isset($params))
        {
            $params = array();
        }
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();


        if($logged){
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();
            $for_user_id = intval($_POST['for_user_id']);
            $last_id = intval($_POST['last_id']);

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');
            $sess_last_id = $cache->load(''.$user_id.'/im');
            $typograf = $cache->load("{$user_id}/typograf{$for_user_id}");

            if($typograf) {
                echo "<script>$('#im_typograf').fadeIn()</script>";
            }

            if($last_id == $sess_last_id){
                echo 'no_new';
                die();
            }

            $count = $db->super_query("SELECT msg_num, all_msg_num FROM `im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
            if($count['all_msg_num'] > 20)
                $limit = $count['all_msg_num']-20;
            else
                $limit = 0;

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, from_user_id, attach, tell_uid, tell_date, public, tell_comm, tb2.user_name, user_photo FROM `messages` tb1, `users` tb2 WHERE tb1.for_user_id = '{$user_id}' AND tb1.from_user_id = '{$for_user_id}' AND tb1.history_user_id = tb2.user_id ORDER by `date` ASC LIMIT ".$limit.", 20", 1);

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');
            $cache->save("{$user_id}/im", $last_id);

            if($sql_){
//                $tpl->load_template('im/msg.tpl');
                $config = Settings::load();
                foreach($sql_ as $row){
//                    $tpl->set('{name}', $row['user_name']);
//                    $tpl->set('{folder}', $row['folder']);
//                    $tpl->set('{user-id}', $row['history_user_id']);
//                    $tpl->set('{msg-id}', $row['id']);
                    $server_time = Date::time();
                    if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time)) {
//                        $tpl->set('{date}', Langs::lang_date('H:i:s', $row['date']));
                    }
                    else {
//                        $tpl->set('{date}', Langs::lang_date('d.m.y', $row['date']));
                    }
                    if($row['user_photo'])
                    {
//                        $tpl->set('{ava}', '/uploads/users/'.$row['history_user_id'].'/50_'.$row['user_photo']);
                    }
                    else {
//                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    }
                    if($row['pm_read'] == 'no'){
//                        $tpl->set('{new}', 'im_class_new');
//                        $tpl->set('{read-js-func}', 'onMouseOver="im.read(\''.$row['id'].'\', '.$row['history_user_id'].', '.$user_id.')"');
                    } else {
//                        $tpl->set('{new}', '');
//                        $tpl->set('{read-js-func}', '');
                    }

                    //Прикрипленные файлы
                    if($row['attach']){
                        $attach_arr = explode('||', $row['attach']);
                        $cnt_attach = 1;
                        $cnt_attach_link = 1;
//                        $jid = 0;
                        $attach_result = '';


                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}");

                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                if($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                elseif($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                    $cnt_attach++;

                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\" />";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}");

                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" {$size[3]}  alt=\"photo\"/></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $audioId = intval($attach_type[1]);
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM
									`audio` WHERE id = '{$audioId}'");
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
									class=\"area\"><table ><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
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
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" ><table ><tbody><tr><td
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
//                                $count_num = count($attach_type);
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

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="border:0px"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=\"photo\"/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

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

                                $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false);

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            } elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false);

                                if($vote_id){

                                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false);

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row['text'])
                                        $row['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", true);
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
                            $rowUserTell = $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$row['tell_uid']}'", false);
                        else
                            $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$row['tell_uid']}'");

                        if ($row['tell_date']){
                            if(date('Y-m-d', $row['tell_date']) == date('Y-m-d', $server_time)) {
                                $row = Langs::lang_date('сегодня в H:i', $row['tell_date']);
                            }
                            elseif(date('Y-m-d', $row['tell_date']) == date('Y-m-d', ($server_time-84600))) {
                                $row = Langs::lang_date('вчера в H:i', $row['tell_date']);
                            }
                            else {
                                $row = Langs::lang_date('j F Y в H:i', $row['tell_date']);
                            }
                        }else{
                            $row = 'N/A';
                        }

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
                                <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=\"photo\"/></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row['text']}
                                <div class="clear"></div>
                                </div>
                                HTML;
                    }

//                    $tpl->set('{text}', stripslashes($row['text']));

//                    $tpl->compile('content');
                }

                return view('info.info', $params);
            }

        }
        return view('info.info', $params);
    }

    /**
     *  Просмотр истории сообщений с юзером
     *
     * @return int
     * @throws \Throwable
     */
    public function history(): int
    {
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $for_user_id = intval($_POST['for_user_id']);
            $first_id = intval($_POST['first_id']);

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');
            $cache->save("{$for_user_id}/typograf{$user_id}", "");

            $config = Settings::load();

            //FOR MOBILE VERSION 1.0
            if($config['temp'] == 'mobile'){
                $sqlNEW = $db->super_query("SELECT id FROM `messages` WHERE history_user_id = '{$for_user_id}' AND for_user_id = '{$user_id}' AND pm_read = 'no'", 1);
                if($sqlNEW){
                    $newMSGnum = 0;
                    foreach($sqlNEW as $rowNEW){
                        $newMSGnum++;
                        //Обновляем кол-во сообщений
                        $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".$rowNEW['id']."'");
                        $db->query("UPDATE `messages` SET pm_read = 'yes' WHERE id = '".($rowNEW['id']+1)."'");
                    }
                    $db->query("UPDATE `im` SET msg_num = msg_num-{$newMSGnum} WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                    $db->query("UPDATE `users` SET user_pm_num = user_pm_num-{$newMSGnum} WHERE user_id = '".$user_id."'");
                    //Читисм кеш обновлений
                    $cache->remove("{$for_user_id}/im");
                }
                $limit_msg = 5;
            } else {
                $limit_msg = 20;
            }
            if($first_id > 0){
                $count = $db->super_query("SELECT COUNT(*) AS all_msg_num FROM `messages` WHERE from_user_id = '".$for_user_id."' AND for_user_id = '".$user_id."' AND id < ".$first_id);
                $sql_sort = "AND id < ".$first_id;
                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            } else {
                $count = $db->super_query("SELECT all_msg_num FROM `im` WHERE iuser_id = '".$user_id."' AND im_user_id = '".$for_user_id."'");
                if($count['all_msg_num'] > $limit_msg)
                    $limit = $count['all_msg_num']-$limit_msg;
                else
                    $limit = 0;
            }

            $sql_ = $db->super_query("SELECT tb1.id, text, date, pm_read, folder, history_user_id, from_user_id, attach, tell_uid, tell_date, public, tell_comm, tb2.user_name, user_photo FROM `messages` tb1, `users` tb2 WHERE tb1.for_user_id = '".$user_id."' AND tb1.from_user_id = '".$for_user_id."' AND tb1.history_user_id = tb2.user_id {$sql_sort} ORDER by `date` DESC LIMIT ".$limit.", ".$limit_msg, true);
            if(!$first_id){

                if($count['all_msg_num'] > $limit_msg){
//                    $stylesMOB = 'width:520px';
//                    $tpl->result['content'] .= '<div class="cursor_pointer" onClick="im.page('.$for_user_id.'); return false" id="wall_all_records" style="'.$stylesMOB.'"><div class="public_wall_all_comm" id="load_wall_all_records" style="margin-left:0px">Показать предыдущие сообщения</div></div><div id="prevMsg"></div>';
                }

//                $tpl->result['content'] .= '<div id="im_scroll">';
            }

            if($sql_){
                foreach($sql_ as $key => $row){
                    $sql_[$key]['name'] = $row['user_name'];
                    $sql_[$key]['folder'] = $row['folder'];
                    $sql_[$key]['user-id'] = $row['history_user_id'];
                    $sql_[$key]['msg-id'] = $row['id'];
                    $server_time = Date::time();

                    if(date('Y-m-d', (int)$row['date']) == date('Y-m-d', $server_time)) {
                        $sql_[$key]['date'] = Langs::lang_date('H:i:s', (int)$row['date']);
                    }else{
                        $sql_[$key]['date'] = Langs::lang_date('d.m.y', (int)$row['date']);
                    }
                    if($row['user_photo']){
                        $sql_[$key]['ava'] = '/uploads/users/'.$row['history_user_id'].'/50_'.$row['user_photo'];
                    }else{
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }
                    if($row['pm_read'] == 'no'){
                        $sql_[$key]['new'] = 'im_class_new';
                        $sql_[$key]['read_js_func'] = 'onMouseOver="im.read(\''.$row['id'].'\', '.$row['history_user_id'].', '.$user_id.')"';
                    } else {
                        $sql_[$key]['new'] = '';
                        $sql_[$key]['read_js_func'] =  '';
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

                                $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row['tell_uid']}/photos/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '{$row['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            }
                            elseif($attach_type[0] == 'photo_u'){
                                if($row['tell_uid']) $attauthor_user_id = $row['tell_uid'];
                                elseif($row['history_user_id'] == $user_id) $attauthor_user_id = $user_id;
                                else $attauthor_user_id = $row['from_user_id'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                    $cnt_attach++;

                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                    $size = getimagesize(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}");

                                    $attach_result .= "<img id=\"photo_wall_{$row['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" {$size[3]} style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row['id']}\"  alt=\"photo\"/>";

                                    $cnt_attach++;
                                }

                                $resLinkTitle = '';

                                //Видео
                            }
                            elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                $size = getimagesize(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}");

                                $attach_result .= "<div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" {$size[3]}  alt=\"photo\"/></a></div>";

                                $resLinkTitle = '';

                                //Музыка
                            }
                            elseif($attach_type[0] == 'audio'){
                                if (!isset($audioId))
                                    $audioId = null;//bug:undefined
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$audioId}'");
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
									class=\"area\"><table ><tbody><tr><td><div class=\"audioPlayBut new_play_btn\"><div
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
									class=\"audioPlayer player{$row_audio['id']}_{$row_audio['oid']}_{$plname}\" ><table ><tbody><tr><td
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
                            }
                            elseif($attach_type[0] == 'smile' AND file_exists(__DIR__."/../../public/uploads/smiles/{$attach_type[1]}")){
                                $attach_result .= '<img src=\"/uploads/smiles/'.$attach_type[1].'\" style="margin-right:5px" />';

                                $resLinkTitle = '';
                                //Если ссылка
                            }
                            elseif($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1 AND stripos(str_replace('http://www.', 'http://', $attach_type[1]), $config['home_url']) === false){
//                                $count_num = count($attach_type);
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

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="border:0px"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=\"photo\"/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                }

                                $cnt_attach_link++;

                                //Если документ
                            }
                            elseif($attach_type[0] == 'doc'){

                                $doc_id = intval($attach_type[1]);

                                $row_doc = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$doc_id}'", false);

                                if($row_doc){

                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                    $cnt_attach++;
                                }

                                //Если опрос
                            }
                            elseif($attach_type[0] == 'vote'){

                                $vote_id = intval($attach_type[1]);

                                $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$vote_id}'", false);

                                if($vote_id){

                                    $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false);

                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                    if(!$row['text'])
                                        $row['text'] = $row_vote['title'];

                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                    $max = $row_vote['answer_num'];

                                    $sql_answer = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$vote_id}' GROUP BY answer", true);
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

                            }
                            else
                                $attach_result .= '';
                        }

                        if($resLinkTitle AND $row['text'] == $resLinkUrl OR !$row['text'])
                            $row['text'] = $resLinkTitle.'<div class="clear"></div>'.$attach_result;
                        else if($attach_result)
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']).$attach_result;
                        else
                            $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);

                    } else{
                        $row['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row['text']);
                    }

                    $resLinkTitle = '';

                    //Если это запись с "рассказать друзьям"
                    if($row['tell_uid']){
                        if($row['public'])
                            $rowUserTell = $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$row['tell_uid']}'", false);
                        else
                            $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$row['tell_uid']}'");

                        if ($row['tell_date']){
                            if(date('Y-m-d', $row['tell_date']) == date('Y-m-d', $server_time)) {
                                $row = Langs::lang_date('сегодня в H:i', $row['tell_date']);
                            }
                            elseif(date('Y-m-d', $row['tell_date']) == date('Y-m-d', ($server_time-84600))) {
                                $row = Langs::lang_date('вчера в H:i', $row['tell_date']);
                            }
                            else {
                                $row = Langs::lang_date('j F Y в H:i', $row['tell_date']);
                            }
                        }else{
                            $row = 'N/A';
                        }

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

                        $dateTell = '';//FIXME

                        $row['text'] = "
                                {$row['tell_comm']}
                                <div class=\"wall_repost_border\">
                                <div class=\"wall_tell_info\"><div class=\"wall_tell_ava\"><a href=\"/{$tell_link}{$row['tell_uid']}\" onClick=\"Page.Go(this.href); return false\"><img src=\"{$avaTell}\" width=\"30\"  alt=\"photo\" /></a></div><div class=\"wall_tell_name\"><a href=\"/{$tell_link}{$row['tell_uid']}\" onClick=\"Page.Go(this.href); return false\"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class=\"wall_tell_date\">{$dateTell}</div></div>{$row['text']}
                                <div class=\"clear\"></div>
                                </div>
                                ";
                    }

//                    $tpl->set('{text}', stripslashes($row['text']));
//                    $tpl->compile('content');
                }
            }
//            else{
//                    $tpl->result['content'] .= '<div class="info_center"><div style="padding-top:210px">Здесь будет выводиться история переписки.</div></div>';

//            }
            $params['im'] = $sql_;
            if(!$first_id){
                $params['first_id'] = false;
//                $tpl->result['content'] .= '</div></div>';

//                $tpl->load_template('im/form.tpl');
//                $tpl->set('{for_user_id}', $for_user_id);
                $params['for_user_id'] = $for_user_id;
                //Выводим информцию о том кто смотрит страницу для себя
                $myInfo = $db->super_query("SELECT user_name FROM `users` WHERE user_id = '".$user_id."'");
//                $tpl->set('{myuser-id}', $user_id);
                $params['myuser_id'] = $user_id;
//                $tpl->set('{my-name}', $myInfo['user_name']);
                $params['my_name'] = $myInfo['user_name'];
                if($user_info['user_photo']){
//                    $tpl->set('{my-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                    $params['my_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                }
                else{
//                    $tpl->set('{my-ava}', '/images/no_ava_50.png');
                    $params['my_ava'] = '/images/no_ava_50.png';
                }
//                $tpl->compile('content');
            }else{
                $params['first_id'] = true;
            }
            return view('im.history', $params);
        }
        return 1;
    }

    /**
     * Обновление диалогов
     *
     * @throws \Throwable
     * @throws \Throwable
     */
    public function upDialogs(): int
    {
        //$tpl = $params['tpl'];
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];

//            Tools::NoAjaxQuery();

            $storage = new MemcachedStorage('localhost');
            $cache = new Cache($storage, 'users');
            $update = $cache->load("{$user_id}/im_update");

            if($update){
                $sql_ = $db->super_query("SELECT tb1.msg_num, im_user_id FROM `im` tb1, `users` tb2 WHERE tb1.iuser_id = '".$user_id."' AND tb1.im_user_id = tb2.user_id AND msg_num > 0 ORDER by `idate` DESC LIMIT 0, 50", 1);
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

                    $storage = new MemcachedStorage('localhost');
                    $cache = new Cache($storage, 'users');
                    $cache->save("{$user_id}/im_update", "0");
                }

                echo '<script type="text/javascript">
				'.$doc_title.'
				$(\'#new_msg\').html(\''.$user_pm_num_2.'\');
				'.$res.'
				</script>';
            }
        }
    }

    /**
     * Удаление диалога
     *
     * @return int
     * @throws \JsonException
     */
    public function del(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $im_user_id = intval($_POST['im_user_id']);

            //Выводим информацию о диалоге
            $row = $db->super_query("SELECT msg_num, all_msg_num FROM `im` WHERE iuser_id = '{$user_id}' AND im_user_id = '{$im_user_id}'");

            if($row){
                //Удаляем сообщения
                if($row['all_msg_num']){
                    $db->query("DELETE FROM `messages` WHERE for_user_id = '{$user_id}' AND from_user_id = '{$im_user_id}'");
                }

                //Если есть новые сообщения
                if($row['msg_num']){
                    $db->query("UPDATE `users` SET user_pm_num = user_pm_num-{$row['msg_num']} WHERE user_id = '{$user_id}'");
                }

                //Удаляем сам диалог
                $db->query("DELETE FROM `im` WHERE iuser_id = '{$user_id}' AND im_user_id = '{$im_user_id}'");

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
     * @return int
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

//        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            //################### Вывод всех диалогов ###################//
            $params['title'] = 'Диалоги'.' | Sura';

            //Вывод диалогов
            $sql_ = $db->super_query("SELECT tb1.msg_num, im_user_id, tb2.user_search_pref, user_photo FROM `im` tb1, `users` tb2 WHERE tb1.iuser_id = '".$user_id."' AND tb1.im_user_id = tb2.user_id ORDER by `idate` DESC LIMIT 0, 50", 1);
//            $tpl->load_template('im/dialog.tpl');
            foreach($sql_ as $key => $row){
                $sql_[$key]['name'] = $row['user_search_pref'];
                $sql_[$key]['uid'] = $row['im_user_id'];
                if($row['user_photo']){
                    $sql_[$key]['ava'] = '/uploads/users/'.$row['im_user_id'].'/50_'.$row['user_photo'];
                }else{
                    $sql_[$key]['ava'] = '/images/no_ava_50.png';
                }
                if($row['msg_num']){
                    $sql_[$key]['msg_num'] = '<div class="im_new fl_l" id="msg_num'.$row['im_user_id'].'">'.$row['msg_num'].'</div>';
                }else{
                    $sql_[$key]['msg_num'] = '';
                }
            }
            $params['dialog'] = $sql_;

            //header сообщений
//            $tpl->load_template('im/head.tpl');
            $params['inbox'] = true;
            $params['outbox'] = false;
            $params['review'] = false;
//
            $params['title'] = 'Чаты';
//            $params['info'] = 'not_logged';
            return view('im.im', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}