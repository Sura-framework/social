<?php
/* 
	Appointment: Стена
	File: wall.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use Intervention\Image\ImageManager;
use System\Classes\Thumb;
use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Gramatic;
use System\Libs\Validation;

class WallController extends Module{

    public function send($params){
        $tpl = Registry::get('tpl');
        //$lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            //$limit_page = 0;

            Tools::NoAjaxQuery();
            $wall_text = Validation::ajax_utf8($_POST['wall_text']);
            $attach_files = Validation::ajax_utf8($_POST['attach_files'], false, true);
            $for_user_id = intval($_POST['for_user_id']);
            $fast_comm_id = intval($_POST['rid']);
            $answer_comm_id = intval($_POST['answer_comm_id']);
            $str_date = time();

            if(!$fast_comm_id) AntiSpam('wall');
            else AntiSpam('comments');

            //Проверка на наличии юзера которум отправляется запись
            $check = $db->super_query("SELECT user_privacy, user_last_visit FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");

            if($check){

                if(isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files)){

                    //Приватность
                    $user_privacy = xfieldsdataload($check['user_privacy']);

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall2'] == 2 OR $user_privacy['val_wall1'] == 2 OR $user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
                        $check_friend = CheckFriends($for_user_id);

                    if(!$fast_comm_id){
                        if($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $check_friend OR $user_id == $for_user_id)
                            $xPrivasy = 1;
                        else
                            $xPrivasy = 0;
                    } else {
                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id)
                            $xPrivasy = 1;
                        else
                            $xPrivasy = 0;
                    }

                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id)
                        $xPrivasyX = 1;
                    else
                        $xPrivasyX = 0;

                    //ЧС
                    $CheckBlackList = Tools::CheckBlackList($for_user_id);
                    if(!$CheckBlackList){
                        if($xPrivasy){

                            //Оприделение изображения к ссылке
                            if(stripos($attach_files, 'link|') !== false){
                                $attach_arr = explode('||', $attach_files);
                                $cnt_attach_link = 1;
                                foreach($attach_arr as $attach_file){
                                    $attach_type = explode('|', $attach_file);
                                    if($attach_type[0] == 'link' AND preg_match('/http:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
                                        $domain_url_name = explode('/', $attach_type[1]);
                                        $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);
                                        $rImgUrl = $attach_type[4];
                                        $rImgUrl = str_replace("\\", "/", $rImgUrl);
                                        $img_name_arr = explode(".", $rImgUrl);
                                        $img_format = Gramatic::totranslit(end($img_name_arr));
                                        $server_time = intval($_SERVER['REQUEST_TIME']);
                                        $image_rename = substr(md5($server_time.md5($rImgUrl)), 0, 15);

                                        //Разришенные форматы
                                        $allowed_files = array('jpg', 'jpeg', 'jpe', 'png');

                                        //Загружаем картинку на сайт
                                        if(in_array(strtolower($img_format), $allowed_files) AND preg_match("/http:\/\/(.*?)(.jpg|.png|.jpeg|.jpe)/i", $rImgUrl)){

                                            //Директория загрузки фото
                                            $upload_dir = __DIR__.'/../../public/uploads/attach/'.$user_id.'/';
                                            $res_type = '.'.$img_format;

                                            //Если нет папки юзера, то создаём её
                                            if(!is_dir($upload_dir)){
                                                @mkdir($upload_dir, 0777);
                                                @chmod($upload_dir, 0777);
                                            }

                                            if(copy($rImgUrl, $upload_dir.$image_rename.$res_type)){
                                                $manager = new ImageManager(array('driver' => 'gd'));

                                                //Создание оригинала
                                                $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(100, 80);
                                                $image->save($upload_dir.$image_rename.'.webp', 90);

                                                unlink($upload_dir.$image_rename.$res_type);
                                                $res_type = '.webp';

                                                $attach_files = str_replace($attach_type[4], '/uploads/attach/'.$user_id.'/'.$image_rename.$res_type, $attach_files);
                                            }
                                        }
                                        $cnt_attach_link++;
                                    }
                                }
                            }

                            $attach_files = str_replace('vote|', 'hack|', $attach_files);
                            $attach_files = str_replace(array('&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('&#124;', '&raquo;', '&quot;'), $attach_files);

                            //Голосование
                            $vote_title = Validation::ajax_utf8($_POST['vote_title'], false, true);
                            $vote_answer_1 = Validation::ajax_utf8($_POST['vote_answer_1'], false, true);

                            $ansers_list = array();

                            if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

                                for($vote_i = 1; $vote_i <= 10; $vote_i++){

                                    $vote_answer = Validation::ajax_utf8($_POST['vote_answer_'.$vote_i], false, true);
                                    $vote_answer = str_replace('|', '&#124;', $vote_answer);

                                    if($vote_answer)
                                        $ansers_list[] = $vote_answer;

                                }

                                $sql_answers_list = implode('|', $ansers_list);

                                //Вставляем голосование в БД
                                $db->query("INSERT INTO `".PREFIX."_votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");

                                $attach_files = $attach_files."vote|{$db->insert_id()}||";

                            }

                            //Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
                            if($answer_comm_id){

                                //Выводим ид владельца комменатрия
                                $row_owner2 = $db->super_query("SELECT author_user_id FROM `".PREFIX."_wall` WHERE id = '{$answer_comm_id}' AND fast_comm_id != '0'");

                                //Проверка на то, что юзер не отвечает сам себе
                                if($user_id != $row_owner2['author_user_id'] AND $row_owner2){

                                    $check2 = $db->super_query("SELECT user_last_visit, user_name FROM `".PREFIX."_users` WHERE user_id = '{$row_owner2['author_user_id']}'");

                                    $wall_text = str_replace($check2['user_name'], "<a href=\"/u{$row_owner2['author_user_id']}\" onClick=\"Page.Go(this.href); return false\" class=\"newcolor000\">{$check2['user_name']}</a>", $wall_text);

                                    //Вставляем в ленту новостей
                                    $server_time = intval($_SERVER['REQUEST_TIME']);
                                    $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$answer_comm_id}', for_user_id = '{$row_owner2['author_user_id']}', action_time = '{$server_time}'");

                                    //Вставляем событие в моментальные оповещания
                                    $update_time = $server_time - 70;

                                    if($check2['user_last_visit'] >= $update_time){

                                        $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row_owner2['author_user_id']}', from_user_id = '{$user_id}', type = '5', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$for_user_id}_{$fast_comm_id}'");

                                        Cache::mozg_create_cache("user_{$row_owner2['author_user_id']}/updates", 1);

                                        //ИНАЧЕ Добавляем +1 юзеру для оповещания
                                    } else {

                                        $cntCacheNews = Cache::mozg_cache("user_{$row_owner2['author_user_id']}/new_news");
                                        Cache::mozg_create_cache("user_{$row_owner2['author_user_id']}/new_news", ($cntCacheNews+1));

                                    }

                                }

                            }

                            //Вставляем саму запись в БД
                            $db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$for_user_id}', text = '{$wall_text}', add_date = '{$str_date}', fast_comm_id = '{$fast_comm_id}', attach = '".$attach_files."'");
                            $dbid = $db->insert_id();

                            //Если пользователь пишет сам у себя на стене, то вносим это в "Мои Новости"
                            if($user_id == $for_user_id AND !$fast_comm_id){
                                $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$str_date}'");
                            }

                            //Если добавляется комментарий к записи то вносим в ленту новостей "ответы"
                            if($fast_comm_id AND !$answer_comm_id){
                                //Выводим ид владельца записи
                                $row_owner = $db->super_query("SELECT author_user_id FROM `".PREFIX."_wall` WHERE id = '{$fast_comm_id}'");

                                if($user_id != $row_owner['author_user_id'] AND $row_owner){
                                    $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$fast_comm_id}', for_user_id = '{$row_owner['author_user_id']}', action_time = '{$str_date}'");

                                    //Вставляем событие в моментальные оповещания
                                    $server_time = intval($_SERVER['REQUEST_TIME']);
                                    $update_time = $server_time - 70;

                                    if($check['user_last_visit'] >= $update_time){

                                        $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row_owner['author_user_id']}', from_user_id = '{$user_id}', type = '1', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$for_user_id}_{$fast_comm_id}'");

                                        Cache::mozg_create_cache("user_{$row_owner['author_user_id']}/updates", 1);

                                        //ИНАЧЕ Добавляем +1 юзеру для оповещания
                                    } else {

                                        $cntCacheNews = Cache::mozg_cache('user_'.$row_owner['author_user_id'].'/new_news');
                                        Cache::mozg_create_cache('user_'.$row_owner['author_user_id'].'/new_news', ($cntCacheNews+1));

                                    }

                                    $config = include __DIR__.'/../data/config.php';

                                    //Отправка уведомления на E-mail
                                    if($config['news_mail_2'] == 'yes'){
                                        $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$row_owner['author_user_id']."'");
                                        if($rowUserEmail['user_email']){
                                            include_once __DIR__.'/../Classes/mail.php';
                                            $mail = new \dle_mail($config);
                                            $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
                                            $rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '2'");
                                            $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                                            $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                                            $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$row_owner['author_user_id'].'_'.$fast_comm_id, $rowEmailTpl['text']);
                                            $mail->send($rowUserEmail['user_email'], 'Ответ на запись', $rowEmailTpl['text']);
                                        }
                                    }
                                }
                            }

                            if($fast_comm_id)
                                $db->query("UPDATE `".PREFIX."_wall` SET fasts_num = fasts_num+1 WHERE id = '{$fast_comm_id}'");
                            else
                                $db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$for_user_id}'");

                            //Подгружаем и объявляем класс для стены
//                            include __DIR__.'/../Classes/wall.php';
//                            $wall = new \wall();

                            //Если добавлена просто запись, то сразу обновляем все записи на стене
                            AntiSpamLogInsert('wall');
                            if(!$fast_comm_id){

                                if($xPrivasyX){
                                    //$wall->query("SELECT tb1.id, author_user_id, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
                                    $query = $db->super_query("SELECT tb1.id, author_user_id, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}", 1);
                                    $tpl->load_template('wall/record.tpl');
                                    $compile = 'content';

                                    $server_time = intval($_SERVER['REQUEST_TIME']);
                                    $config = include __DIR__.'/data/config.php';

                                    //$this->template;
                                    foreach($query as $row_wall){
                                        $tpl->set('{rec-id}', $row_wall['id']);

                                        //КНопка Показать полностью..
                                        $expBR = explode('<br />', $row_wall['text']);
                                        $textLength = count($expBR);
                                        $strTXT = strlen($row_wall['text']);
                                        if($textLength > 9 OR $strTXT > 600)
                                            $row_wall['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_wall['id'].'">'.$row_wall['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_wall['id'].', this.id)" id="hide_wall_rec_lnk'.$row_wall['id'].'">Показать полностью..</div>';

                                        //Прикрипленные файлы
                                        if($row_wall['attach']){
                                            $attach_arr = explode('||', $row_wall['attach']);
                                            $cnt_attach = 1;
                                            $cnt_attach_link = 1;
                                            $jid = 0;
                                            $attach_result = '';
                                            $attach_result .= '<div class="clear"></div>';
                                            foreach($attach_arr as $attach_file){
                                                $attach_type = explode('|', $attach_file);

                                                //Фото со стены сообщества
                                                if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}")){
                                                    if($cnt_attach < 2)
                                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
                                                    else
                                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                                    $cnt_attach++;

                                                    $resLinkTitle = '';

                                                    //Фото со стены юзера
                                                } elseif($attach_type[0] == 'photo_u'){
                                                    if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];
                                                    else $attauthor_user_id = $row_wall['author_user_id'];

                                                    if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                                        if($cnt_attach == 1)

                                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>";

                                                        else

                                                            $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" height=\"{$rodImHeigh}\" />";


                                                        $cnt_attach++;


                                                    } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                                        if($cnt_attach < 2)
                                                            $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\" /></div>";
                                                        else
                                                            $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                                        $cnt_attach++;
                                                    }

                                                    $resLinkTitle = '';

                                                    //Видео
                                                } elseif($attach_type[0] == 'video' AND file_exists(__DIR__."/../../public/uploads/videos/{$attach_type[3]}/{$attach_type[1]}")){

                                                    $for_cnt_attach_video = explode('video|', $row_wall['attach']);
                                                    $cnt_attach_video = count($for_cnt_attach_video)-1;

                                                    if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];

                                                    if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $row_wall['attach']) == false){

                                                        $video_id = intval($attach_type[2]);

                                                        $row_video = $db->super_query("SELECT video, title, download FROM `".PREFIX."_videos` WHERE id = '{$video_id}'", false, "wall/video{$video_id}");
                                                        $row_video['title'] = stripslashes($row_video['title']);
                                                        $row_video['video'] = stripslashes($row_video['video']);
                                                        $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));


                                                        if ($row_video['download'] == '1') {
                                                            $attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
							                                <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;max-width: 500px;\" height=\"350\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
                                                        }else{
                                                            $attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
							                                <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;max-width: 500px;\" height=\"350\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
                                                        }



                                                    } else {
                                                        if ($row_video['download'] == '1') {
                                                            $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";
                                                        }else{
                                                            $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                                                        }


                                                    }

                                                    $resLinkTitle = '';

                                                    //Музыка
                                                } elseif($attach_type[0] == 'audio'){
                                                    $data = explode('_', $attach_type[1]);
                                                    $audioId = intval($data[0]);
                                                    $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM `".PREFIX."_audio` WHERE id = '{$audioId}'");
                                                    if($row_audio){
                                                        $stime = gmdate("i:s", $row_audio['duration']);
                                                        if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                                                        if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                                                        $plname = 'wall';
                                                        if($row_audio['oid'] != $user_info['user_id']) $q_s = <<<HTML
                                                        <div class="audioSettingsBut"><li class="icon-plus-6"
                                                        onClick="gSearch.addAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}')"
                                                        onmouseover="showTooltip(this, {text: 'Добавить в мой список', shift: [6,5,0]});"
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
                                                        class=\"audioSlider\"></div></div></div></td><td><div class=\"audioVolumeBar fl_l ml-2\"
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
                                                        if($row_wall['tell_comm']) $no_border_link = 'border:0px';

                                                        $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                                        $resLinkTitle = $attach_type[2];
                                                        $resLinkUrl = $attach_type[1];
                                                    } else if($attach_type[1] AND $attach_type[2]){
                                                        $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                                        $resLinkTitle = $attach_type[2];
                                                        $resLinkUrl = $attach_type[1];
                                                    }

                                                    $cnt_attach_link++;

                                                    //Если документ
                                                } elseif($attach_type[0] == 'doc'){

                                                    $doc_id = intval($attach_type[1]);

                                                    $row_doc = $db->super_query("SELECT dname, dsize FROM `".PREFIX."_doc` WHERE did = '{$doc_id}'", false, "wall/doc{$doc_id}");

                                                    if($row_doc){

                                                        $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                                        $cnt_attach++;
                                                    }

                                                    //Если опрос
                                                } elseif($attach_type[0] == 'vote'){

                                                    $vote_id = intval($attach_type[1]);

                                                    $row_vote = $db->super_query("SELECT title, answers, answer_num FROM `".PREFIX."_votes` WHERE id = '{$vote_id}'", false, "votes/vote_{$vote_id}");

                                                    if($vote_id){

                                                        $checkMyVote = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$vote_id}'", false, "votes/check{$user_id}_{$vote_id}");

                                                        $row_vote['title'] = stripslashes($row_vote['title']);

                                                        if(!$row_wall['text'])
                                                            $row_wall['text'] = $row_vote['title'];

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

                                            if($resLinkTitle AND $row_wall['text'] == $resLinkUrl OR !$row_wall['text'])
                                                $row_wall['text'] = $resLinkTitle.$attach_result;
                                            else if($attach_result)
                                                $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']).$attach_result;
                                            else
                                                $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']);
                                        } else
                                            $row_wall['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_wall['text']);

                                        $resLinkTitle = '';

                                        //Если это запись с "рассказать друзьям"
                                        if($row_wall['tell_uid']){
                                            if($row_wall['public'])
                                                $rowUserTell = $db->super_query("SELECT title, photo FROM `".PREFIX."_communities` WHERE id = '{$row_wall['tell_uid']}'", false, "wall/group{$row_wall['tell_uid']}");
                                            else
                                                $rowUserTell = $db->super_query("SELECT user_search_pref, user_photo FROM `".PREFIX."_users` WHERE user_id = '{$row_wall['tell_uid']}'");

                                            if(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', $server_time))
                                                $dateTell = langdate('сегодня в H:i', $row_wall['tell_date']);
                                            elseif(date('Y-m-d', $row_wall['tell_date']) == date('Y-m-d', ($server_time-84600)))
                                                $dateTell = langdate('вчера в H:i', $row_wall['tell_date']);
                                            else
                                                $dateTell = langdate('j F Y в H:i', $row_wall['tell_date']);

                                            if($row_wall['public']){
                                                $rowUserTell['user_search_pref'] = stripslashes($rowUserTell['title']);
                                                $tell_link = 'public';
                                                if($rowUserTell['photo'])
                                                    $avaTell = '/uploads/groups/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['photo'];
                                                else
                                                    $avaTell = '/images/no_ava_50.png';
                                            } else {
                                                $tell_link = 'u';
                                                if($rowUserTell['user_photo'])
                                                    $avaTell = '/uploads/users/'.$row_wall['tell_uid'].'/50_'.$rowUserTell['user_photo'];
                                                else
                                                    $avaTell = '/images/no_ava_50.png';
                                            }

                                            if($row_wall['tell_comm']) $border_tell_class = 'wall_repost_border'; else $border_tell_class = 'wall_repost_border2';

                                            $row_wall['text'] = <<<HTML
                                            {$row_wall['tell_comm']}
                                            <div class="{$border_tell_class}">
                                            <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30" /></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row_wall['text']}
                                            <div class="clear"></div>
                                            </div>
                                            HTML;
                                        }

                                        $tpl->set('{text}', stripslashes($row_wall['text']));

                                        $tpl->set('{name}', $row_wall['user_search_pref']);
                                        $tpl->set('{user-id}', $row_wall['author_user_id']);

                                        $online = Online($row_wall['user_last_visit'], $row_wall['user_logged_mobile']);
                                        $tpl->set('{online}', $online);
                                        $date = megaDate($row_wall['add_date']);
                                        $tpl->set('{date}', $date);

                                        if($row_wall['user_photo'])
                                            $tpl->set('{ava}', '/uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo']);
                                        else
                                            $tpl->set('{ava}', '/images/no_ava_50.png');

                                        //Мне нравится
                                        if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                                            $tpl->set('{yes-like}', 'public_wall_like_yes');
                                            $tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
                                            $tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.', \'uPages\')');
                                        } else {
                                            $tpl->set('{yes-like}', '');
                                            $tpl->set('{yes-like-color}', '');
                                            $tpl->set('{like-js-function}', 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.', \'uPages\')');
                                        }

                                        if($row_wall['likes_num']){
                                            $tpl->set('{likes}', $row_wall['likes_num']);
                                            $titles = array('человеку', 'людям', 'людям');//like
                                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], $titles));
                                        } else {
                                            $tpl->set('{likes}', '');
                                            $tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">0</span> человеку');
                                        }

                                        //Выводим информцию о том кто смотрит страницу для себя
                                        $tpl->set('{viewer-id}', $user_id);
                                        if($user_info['user_photo'])
                                            $tpl->set('{viewer-ava}', '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);
                                        else
                                            $tpl->set('{viewer-ava}', '/images/no_ava_50.png');

                                        if($row_wall['type'])
                                            $tpl->set('{type}', $row_wall['type']);
                                        else
                                            $tpl->set('{type}', '');

                                        if(!$id)
                                            $id = $for_user_id;

                                        //Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
                                        if($user_id == $row_wall['author_user_id'] OR $user_id == $id){
                                            $tpl->set('[owner]', '');
                                            $tpl->set('[/owner]', '');
                                        } else
                                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                                        //Показа кнопки "Рассказать др" только если это записи владельца стр.
                                        if($row_wall['author_user_id'] == $id AND $user_id != $id){
                                            $tpl->set('[owner-record]', '');
                                            $tpl->set('[/owner-record]', '');
                                        } else
                                            $tpl->set_block("'\\[owner-record\\](.*?)\\[/owner-record\\]'si","");

                                        //Если есть комменты к записи, то выполняем след. действия / Приватность
                                        if($row_wall['fasts_num']){
                                            $tpl->set('[if-comments]', '');
                                            $tpl->set('[/if-comments]', '');
                                            $tpl->set_block("'\\[comments-link\\](.*?)\\[/comments-link\\]'si","");
                                        } else {
                                            $tpl->set('[comments-link]', '');
                                            $tpl->set('[/comments-link]', '');
                                            $tpl->set_block("'\\[if-comments\\](.*?)\\[/if-comments\\]'si","");
                                        }

                                        //Приватность комментирования записей
                                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
                                            $tpl->set('[privacy-comment]', '');
                                            $tpl->set('[/privacy-comment]', '');
                                        } else
                                            $tpl->set_block("'\\[privacy-comment\\](.*?)\\[/privacy-comment\\]'si","");

                                        $tpl->set('[record]', '');
                                        $tpl->set('[/record]', '');
                                        $tpl->set('{author-id}', $id);
                                        $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                        $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                        $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                        $tpl->compile($compile);

                                        //Помещаем все комменты в id wall_fast_block_{id} это для JS
                                        $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'">';

                                        //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $id){
                                            if($row_wall['fasts_num']){

                                                if($row_wall['fasts_num'] > 3)
                                                    $comments_limit = $row_wall['fasts_num']-3;
                                                else
                                                    $comments_limit = 0;

                                                $sql_comments = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

                                                //Загружаем кнопку "Показать N запсии"
                                                $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                                                $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                                                $tpl->set('{gram-record-all-comm}', Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2));
                                                if($row_wall['fasts_num'] < 4)
                                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                                else {
                                                    $tpl->set('{rec-id}', $row_wall['id']);
                                                    $tpl->set('[all-comm]', '');
                                                    $tpl->set('[/all-comm]', '');
                                                }
                                                $tpl->set('{author-id}', $id);
                                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                                $tpl->compile($compile);

                                                //Сообственно выводим комменты
                                                foreach($sql_comments as $row_comments){
                                                    $tpl->set('{name}', $row_comments['user_search_pref']);
                                                    if($row_comments['user_photo'])
                                                        $tpl->set('{ava}', '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo']);
                                                    else
                                                        $tpl->set('{ava}', '/images/no_ava_50.png');

                                                    $tpl->set('{rec-id}', $row_wall['id']);
                                                    $tpl->set('{comm-id}', $row_comments['id']);
                                                    $tpl->set('{user-id}', $row_comments['author_user_id']);

                                                    $expBR2 = explode('<br />', $row_comments['text']);
                                                    $textLength2 = count($expBR2);
                                                    $strTXT2 = strlen($row_comments['text']);
                                                    if($textLength2 > 6 OR $strTXT2 > 470)
                                                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                                    //Обрабатываем ссылки
                                                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

                                                    $tpl->set('{text}', stripslashes($row_comments['text']));

                                                    $date = megaDate($row_comments['add_date']);
                                                    $tpl->set('{date}', $date);
                                                    if($user_id == $row_comments['author_user_id'] || $user_id == $id){
                                                        $tpl->set('[owner]', '');
                                                        $tpl->set('[/owner]', '');
                                                    } else
                                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                                                    if($user_id == $row_comments['author_user_id'])

                                                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                                                    else {

                                                        $tpl->set('[not-owner]', '');
                                                        $tpl->set('[/not-owner]', '');

                                                    }

                                                    $tpl->set('[comment]', '');
                                                    $tpl->set('[/comment]', '');
                                                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                                                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                                    $tpl->compile($compile);
                                                }

                                                //Загружаем форму ответа
                                                $tpl->set('{rec-id}', $row_wall['id']);
                                                $tpl->set('{author-id}', $id);
                                                $tpl->set('[comment-form]', '');
                                                $tpl->set('[/comment-form]', '');
                                                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                                                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                                                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                                                $tpl->compile($compile);
                                            }
                                        }

                                        //Закрываем блок для JS
                                        $tpl->result[$compile] .= '</div>';

                                    }

                                }

                                Cache::mozg_clear_cache_file('user_'.$for_user_id.'/profile_'.$for_user_id);

                                $config = include __DIR__.'/../data/config.php';

                                //Отправка уведомления на E-mail
                                if($config['news_mail_7'] == 'yes' AND $user_id != $for_user_id){
                                    $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `".PREFIX."_users` WHERE user_id = '".$for_user_id."'");
                                    if($rowUserEmail['user_email']){
                                        include_once __DIR__.'/../Classes/mail.php';
                                        $mail = new \dle_mail($config);
                                        $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `".PREFIX."_users` WHERE user_id = '".$user_id."'");
                                        $rowEmailTpl = $db->super_query("SELECT text FROM `".PREFIX."_mail_tpl` WHERE id = '7'");
                                        $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
                                        $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
                                        $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$for_user_id.'_'.$dbid, $rowEmailTpl['text']);
                                        $mail->send($rowUserEmail['user_email'], 'Новая запись на стене', $rowEmailTpl['text']);
                                    }
                                }

                                //Если добавлен комментарий к записи то просто обновляем нужную часть, тоесть только часть комментариев, но не всю стену
                            } else {

                                AntiSpamLogInsert('comments');

                                //Выводим кол-во комментов к записи
                                $row = $db->super_query("SELECT fasts_num FROM `".PREFIX."_wall` WHERE id = '{$fast_comm_id}'");
                                $record_fasts_num = $row['fasts_num'];
                                if($record_fasts_num > 3)
                                    $limit_comm_num = $row['fasts_num']-3;
                                else
                                    $limit_comm_num = 0;

                                $wall->comm_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT {$limit_comm_num}, 3");

                                if($_POST['type'] == 1)
                                    $wall->comm_template('news/news.tpl');
                                else if($_POST['type'] == 2)
                                    $wall->comm_template('wall/one_record.tpl');
                                else
                                    $wall->comm_template('wall/record.tpl');

                                $wall->comm_compile('content');
                                $wall->comm_select();
                            }

                            Tools::AjaxTpl($tpl);

                            $params['tpl'] = $tpl;
                            Page::generate($params);
                            return true;

                        } else
                            echo 'err_privacy';
                    } else
                        echo 'err_privacy';
                }
            }

            die();
        }
    }

    public function delet($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            //Проверка на существование записи и выводим ID владельца записи и кому предназначена запись
            $row = $db->super_query("SELECT author_user_id, for_user_id, fast_comm_id, add_date, attach FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
            if($row['author_user_id'] == $user_id OR $row['for_user_id'] == $user_id){

                //удаляем саму запись
                $db->query("DELETE FROM `".PREFIX."_wall` WHERE id = '{$rid}'");

                //Если удаляется НЕ комментарий к записи
                if(!$row['fast_comm_id']){
                    //удаляем комменты к записиы
                    $db->query("DELETE FROM `".PREFIX."_wall` WHERE fast_comm_id = '{$rid}'");

                    //удаляем "мне нравится"
                    $db->query("DELETE FROM `".PREFIX."_wall_like` WHERE rec_id = '{$rid}'");

                    //обновляем кол-во записей
                    $db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num-1 WHERE user_id = '{$row['for_user_id']}'");

                    //Чистим кеш
                    Cache::mozg_clear_cache_file('user_'.$row['for_user_id'].'/profile_'.$row['for_user_id']);

                    //удаляем из ленты новостей
                    $db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_type = 6");

                    //Удаляем фотку из прикрипленой ссылке, если она есть
                    if(stripos($row['attach'], 'link|') !== false){
                        $attach_arr = explode('link|', $row['attach']);
                        $attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
                        $attach_arr3 = explode('||', $attach_arr2[1]);
                        if($attach_arr3[0])
                            @unlink(__DIR__.'/../../public/uploads/attach/'.$user_id.'/'.$attach_arr3[0]);
                    }

                    $action_type = 1;
                }

                //Если удаляется комментарий к записи
                if($row['fast_comm_id']){
                    $db->query("UPDATE `".PREFIX."_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row['fast_comm_id']}'");
                    $rid = $row['fast_comm_id'];

                    //удаляем из ленты новостей
                    $db->query("DELETE FROM `".PREFIX."_news` WHERE action_time = '{$row['add_date']}' AND action_type = '6' AND ac_user_id = '{$row['author_user_id']}'");

                    $action_type = 6;
                }

                //удаляем из ленты новостей
                $db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_time = '{$row['add_date']}' AND action_type = {$action_type}");
            }

            die();
        }
    }

    public function like_yes($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            //Проверка на существование записи
            $row = $db->super_query("SELECT text, likes_users, author_user_id FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
            if($row){
                //Проверка на то что этот юзер ставил уже мне нрав или нет
                $likes_users = explode('|', str_replace('u', '', $row['likes_users']));
                if(!in_array($user_id, $likes_users)){
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $db->query("INSERT INTO `".PREFIX."_wall_like` SET rec_id = '{$rid}', user_id = '{$user_id}', date = '{$server_time}'");

                    $db->query("UPDATE `".PREFIX."_wall` SET likes_num = likes_num+1, likes_users = '|u{$user_id}|{$row['likes_users']}' WHERE id = '{$rid}'");

                    if($user_id != $row['author_user_id']){

                        //Вставляем событие в моментальные оповещания
                        $row_owner = $db->super_query("SELECT user_last_visit, notifications_list FROM `".PREFIX."_users` WHERE user_id = '{$row['author_user_id']}'");
                        $update_time = $server_time - 70;

                        if($row_owner['user_last_visit'] >= $update_time){

                            $row['text'] = strip_tags($row['text']);
                            if($row['text']) $wall_text = ' &laquo;'.iconv_substr($row['text'], 0, 70, 'utf-8').'&raquo;';
                            else $wall_text = '.';

                            $myRow = $db->super_query("SELECT user_sex FROM `".PREFIX."_users` WHERE user_id = '{$user_info['user_id']}'");
                            if($myRow['user_sex'] == 2) $action_update_text = 'оценила Вашу запись'.$wall_text;
                            else $action_update_text = 'оценил Вашу запись'.$wall_text;

                            $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row['author_user_id']}', from_user_id = '{$user_info['user_id']}', type = '10', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$row['author_user_id']}_{$rid}'");

                            Cache::mozg_create_cache("user_{$row['author_user_id']}/updates", 1);

                        }

                        //Добавляем в ленту новостей "ответы"

                        $generateLastTime = $server_time-10800;
                        $row_news = $db->super_query("SELECT ac_id, action_text, action_time FROM `".PREFIX."_news` WHERE action_time > '{$generateLastTime}' AND action_type = 7 AND obj_id = '{$rid}'");
                        if($row_news)
                            $db->query("UPDATE `".PREFIX."_news` SET action_text = '|u{$user_id}|{$row_news['action_text']}', action_time = '{$server_time}' WHERE obj_id = '{$rid}' AND action_type = 7 AND action_time = '{$row_news['action_time']}'");
                        else
                            $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 7, action_text = '|u{$user_id}|', obj_id = '{$rid}', for_user_id = '{$row['author_user_id']}', action_time = '{$server_time}'");

                        if(stripos($row_owner['notifications_list'], "settings_likes_gifts|") === false){
                            $cntCacheNews = Cache::mozg_cache('user_'.$row['author_user_id'].'/new_news');
                            Cache::mozg_create_cache('user_'.$row['author_user_id'].'/new_news', ($cntCacheNews+1));
                        }
                    }
                }
            }

            die();
        }
    }

    public function like_no($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            //Проверка на существование записи
            $row = $db->super_query("SELECT likes_users FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
            if($row){
                //Проверка на то что этот юзер ставил уже мне нрав или нет
                $likes_users = explode('|', str_replace('u', '', $row['likes_users']));
                if(in_array($user_id, $likes_users)){
                    $db->query("DELETE FROM `".PREFIX."_wall_like` WHERE rec_id = '{$rid}' AND user_id = '{$user_id}'");
                    $newListLikesUsers = strtr($row['likes_users'], array('|u'.$user_id.'|' => ''));
                    $db->query("UPDATE `".PREFIX."_wall` SET likes_num = likes_num-1, likes_users = '{$newListLikesUsers}' WHERE id = '{$rid}'");

                    //удаляем из ленты новостей
                    $row_news = $db->super_query("SELECT ac_id, action_text FROM `".PREFIX."_news` WHERE action_type = 7 AND obj_id = '{$rid}'");
                    $row_news['action_text'] = strtr($row_news['action_text'], array('|u'.$user_id.'|' => ''));
                    if($row_news['action_text'])
                        $db->query("UPDATE `".PREFIX."_news` SET action_text = '{$row_news['action_text']}' WHERE obj_id = '{$rid}' AND action_type = 7");
                    else
                        $db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rid}' AND action_type = 7");
                }
            }

            die();
        }
    }

    public function liked_users($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo FROM `".PREFIX."_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT 0, 7", 1);
            if($sql_){
                foreach($sql_ as $row){
                    if($row['user_photo']) $ava = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                    else $ava = '/images/no_ava_50.png';
                    echo '<a href="/u'.$row['user_id'].'" id="Xlike_user'.$row['user_id'].'_'.$rid.'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" width="32" /></a>';
                }
            }
            die();
        }
    }

    public function all_liked_users($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            $liked_num = intval($_POST['liked_num']);

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            if(!$liked_num)
                $liked_num = 24;

            if($rid AND $liked_num){
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref FROM `".PREFIX."_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);

                if($sql_){
                    $tpl->load_template('profile_subscription_box_top.tpl');
                    $tpl->set('[top]', '');
                    $tpl->set('[/top]', '');
                    $titles = array('человеку', 'людям', 'людям');//like
                    $tpl->set('{subcr-num}', 'Понравилось '.$liked_num.' '.Gramatic::declOfNum($liked_num, $titles));
                    $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                    $tpl->compile('content');

                    $tpl->result['content'] = str_replace('Всего', '', $tpl->result['content']);

                    $tpl->load_template('profile_friends.tpl');

                    $config = include __DIR__.'/../data/config.php';

                    foreach($sql_ as $row){
                        if($row['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        $friend_info_online = explode(' ', $row['user_search_pref']);
                        $tpl->set('{user-id}', $row['user_id']);
                        $tpl->set('{name}', $friend_info_online[0]);
                        $tpl->set('{last-name}', $friend_info_online[1]);
                        $tpl->compile('content');
                    }
                    box_navigation($gcount, $liked_num, $rid, 'wall.all_liked_users', $liked_num);

                    Tools::AjaxTpl($tpl);

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                }
            }
            die();
        }
    }

    public function all_comm($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $fast_comm_id = intval($_POST['fast_comm_id']);
            $for_user_id = intval($_POST['for_user_id']);
            if($fast_comm_id AND $for_user_id){
                //Подгружаем и объявляем класс для стены
                include __DIR__.'/../Classes/wall.php';
                $wall = new \wall();

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");
                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
                        $check_friend = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");

                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id){
                        $wall->comm_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT 0, 200", '');

                        if($_POST['type'] == 1)
                            $wall->comm_template('news/news.tpl');
                        else if($_POST['type'] == 2)
                            $wall->comm_template('wall/one_record.tpl');
                        else
                            $wall->comm_template('wall/record.tpl');
                        $wall->comm_compile('content');
                        $wall->comm_select();

                        Tools::AjaxTpl($tpl);

                        $params['tpl'] = $tpl;
                        Page::generate($params);
                        return true;
                    } else
                        echo 'err_privacy';
                }
            }
            die();
        }
    }

    public function page($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $last_id = intval($_POST['last_id']);
            $for_user_id = intval($_POST['for_user_id']);

            //ЧС
            $CheckBlackList = CheckBlackList($for_user_id);

            if(!$CheckBlackList AND $for_user_id AND $last_id){
                include __DIR__.'/../Classes/wall.php';
                $wall = new \wall();

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$for_user_id}'");

                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall1'] == 2 AND $user_id != $for_user_id)
                        $check_friend = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");

                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id)
                        $wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
                    else
                        $wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' AND tb1.author_user_id = '{$for_user_id}' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");

                    $wall->template('wall/record.tpl');
                    $wall->compile('content');
                    $wall->select();
                    Tools::AjaxTpl($tpl);

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                }
            }
            die();
        }
    }

    public function tell($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `".PREFIX."_wall` WHERE fast_comm_id = '0' AND id = '{$rid}'");

            if($row){
                if($row['author_user_id'] != $user_id){
                    if($row['tell_uid']){
                        $row['add_date'] = $row['tell_date'];
                        $row['author_user_id'] = $row['tell_uid'];
                    }

                    //Проверяем на существование этой записи у себя на стене
                    $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE tell_uid = '{$row['author_user_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}'");
                    if(!$myRow['cnt']){
                        $row['text'] = $db->safesql($row['text']);
                        $row['attach'] = $db->safesql($row['attach']);

                        //Всталвяем себе на стену
                        $server_time = intval($_SERVER['REQUEST_TIME']);
                        $db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['author_user_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '{$row['attach']}'");
                        $dbid = $db->insert_id();
                        $db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                        //Вставляем в ленту новостей
                        $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                        //Чистим кеш
                        Cache::mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");
                    } else
                        echo 1;
                } else
                    echo 1;
            }
            die();
        }
    }

    public function parse_link($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            $lnk = 'http://'.str_replace('http://', '', trim($_POST['lnk']));
            $check_url = @get_headers(stripslashes($lnk));

            if(strpos($check_url[0], '200')){
                $open_lnk = @file_get_contents($lnk);

                if(stripos(strtolower($open_lnk), 'charset=utf-8') OR stripos(strtolower($check_url[2]), 'charset=utf-8'))
                    $open_lnk = Validation::ajax_utf8($open_lnk);
                else
                    $open_lnk = iconv('windows-1251', 'utf-8', $open_lnk);

                if(stripos(strtolower($open_lnk), 'charset=KOI8-R'))
                    $open_lnk = iconv('KOI8-R', 'utf-8', $open_lnk);

                preg_match("/<meta property=(\"|')og:title(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_title);
                if(!$parse_title[4])
                    preg_match("/<meta name=(\"|')title(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_title);

                $res_title = $parse_title[4];

                if(!$res_title){
                    preg_match_all('`(<title>[^\[]+\</title>)`si', $open_lnk, $parse);
                    $res_title = str_replace(array('<title>', '</title>'), '', $parse[1][0]);
                }

                preg_match("/<meta property=(\"|')og:description(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_descr);
                if(!$parse_descr[4])
                    preg_match("/<meta name=(\"|')description(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_descr);

                $res_descr = strip_tags($parse_descr[4]);
                $res_title = strip_tags($res_title);

                $open_lnk = preg_replace('`(<!--noindex-->|<noindex>).+?(<!--/noindex-->|</noindex>)`si', '', $open_lnk);

                preg_match("/<meta property=(\"|')og:image(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_img);
                if(!$parse_img[4])
                    preg_match_all('/<img(.*?)src=\"(.*?)\"/', $open_lnk, $array);
                else
                    $array[2][0] = $parse_img[4];

                $res_title = str_replace("|", "&#124;", $res_title);
                $res_descr = str_replace("|", "&#124;", $res_descr);

                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png');

                $expImgs = explode('<img', $open_lnk);

                if($expImgs[1]){

                    $i = 0;

                    foreach($expImgs as $img){

                        $exp1 = explode('src="', $img);

                        $exp2 = explode('/>', $exp1[1]);

                        $exp3 = explode('"', $exp2[0]);

                        $expFormat = end(explode('.', $exp3[0]));

                        if(in_array(strtolower($expFormat), $allowed_files)){

                            $i++;

                            $domain_url_name = explode('/', $lnk);
                            $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                            if(stripos(strtolower($exp3[0]), 'http://') === false)

                                $new_imgs .= 'http://'.$rdomain_url_name.'/'.$exp3[0].'|';

                            else

                                $new_imgs .= $exp3[0].'|';

                            if($i == 1)
                                $img_link = str_replace('|', '', $new_imgs);
                        }

                    }

                }

                preg_match("/<meta property=(\"|')og:image(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_img);
                if($parse_img[4]){
                    $rIMGx = explode('?', $parse_img[4]);
                    $img_link = $rIMGx[0];
                    if(!$new_imgs)
                        $new_imgs = $img_link;
                }

                echo $res_title.'<f>'.$res_descr.'<f>'.$img_link.'<f>'.$new_imgs;

            } else
                echo 1;
            die();
        }
    }

    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            $limit_page = 0;

            //################### Показ последних 10 записей ###################//

            //Если вызвана страница стены, не со страницы юзера
            if(!$id){
                $rid = intval($_GET['rid']);

                $id = intval($_GET['uid']);
                if(!$id)
                    $id = $user_id;

                $walluid = $id;
                $metatags['title'] = $lang['wall_title'];
                $user_speedbar = 'На стене нет записей';

                if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
                $gcount = 10;
                $limit_page = ($page-1)*$gcount;

                //Выводим имя юзера и настройки приватности
                $row_user = $db->super_query("SELECT user_name, user_wall_num, user_privacy FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
                $user_privacy = xfieldsdataload($row_user['user_privacy']);

                if($row_user){
                    //ЧС
                    $CheckBlackList = CheckBlackList($id);
                    if(!$CheckBlackList){
                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        if($user_id != $id)
                            $check_friend = CheckFriends($id);

                        if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id)
                            $cnt_rec['cnt'] = $row_user['user_wall_num'];
                        else
                            $cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");

                        if($_GET['type'] == 'own'){
                            $cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
                            $where_sql = "AND tb1.author_user_id = '{$id}'";
                            $tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
                            $page_type = '/wall'.$id.'_sec=own&page=';
                        } else if($_GET['type'] == 'record'){
                            $where_sql = "AND tb1.id = '{$rid}'";
                            $tpl->set('[record-tab]', '');
                            $tpl->set('[/record-tab]', '');
                            $wallAuthorId = $db->super_query("SELECT author_user_id FROM `".PREFIX."_wall` WHERE id = '{$rid}'");
                        } else {
                            $_GET['type'] = '';
                            $where_sql = '';
                            $tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
                            $page_type = '/wall'.$id.'/page/';
                        }

                        if($cnt_rec['cnt'] > 0){
                            $titles = array('запись', 'записи', 'записей');//rec
                            $user_speedbar = 'На стене '.$cnt_rec['cnt'].' '.Gramatic::declOfNum($cnt_rec['cnt'], $titles);
                        }

                        $tpl->load_template('wall/head.tpl');
                        $tpl->set('{name}', Gramatic::gramatikName($row_user['user_name']));
                        $tpl->set('{uid}', $id);
                        $tpl->set('{rec-id}', $rid);
                        $tpl->set("{activetab-{$_GET['type']}}", 'activetab');
                        $tpl->compile('info');

                        if($cnt_rec['cnt'] < 1)
                            msgbox('', $lang['wall_no_rec'], 'info_2');
                    } else {
                        $user_speedbar = $lang['error'];
                        msgbox('', $lang['no_notes'], 'info');
                    }
                } else
                    msgbox('', $lang['wall_no_rec'], 'info_2');
            }

            if(!$CheckBlackList){
                include __DIR__.'/../Classes/wall.php';
                $wall = new \wall();

                if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id)
                    $wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                elseif($wallAuthorId['author_user_id'] == $id)
                    $wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                else {
                    $wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                    if($wallAuthorId['author_user_id'])
                        $Hacking = true;
                }
                //Если вызвана страница стены, не со страницы юзера
                if(!$Hacking){
                    if($rid OR $walluid){
                        $wall->template('wall/one_record.tpl');
                        $wall->compile('content');
                        $wall->select();

                        if($cnt_rec['cnt'] > $gcount AND $_GET['type'] == '' OR $_GET['type'] == 'own')
                            navigation($gcount, $cnt_rec['cnt'], $page_type);
                    } else {
                        $wall->template('wall/record.tpl');
                        $wall->compile('wall');
                        $wall->select();
                    }
                }
            }
            $tpl->clear();
            $db->free();

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        } else
            echo 'no_log';

    }
}