<?php

namespace App\Modules;

use App\Libs\Antispam;
use App\Libs\Wall;
use App\Models\Profile;
use Exception;
use Intervention\Image\ImageManager;
use Sura\Libs\Db;
use Sura\Libs\Langs;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class WallController extends Module{

    /**
     * Добвление новой записи на стену
     *
     * @return int
     * @throws \Throwable
     */
    public function send(): int
    {
        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
        $cache = new \Sura\Cache\Cache($storage, 'users');

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];
            $limit_select = 10;
            //$limit_page = 0;

            $request = (Request::getRequest()->getGlobal());

            if (isset($request['wall_text'])){
                $wall_text = Validation::ajax_utf8($request['wall_text']);
            }else{
                $wall_text = '';
            }
            if (isset($request['attach_files'])){
                $attach_files = Validation::ajax_utf8($request['attach_files']);
            }else{
                $attach_files = '';
            }

            if (isset($request['for_user_id'])) {
                $for_user_id = (int)$request['for_user_id'];
            }
            else {
                $for_user_id = false;
            }
//            $for_user_id = (int)$request['for_user_id'];
            if (isset($request['rid'])) {
                $fast_comm_id = (int)$request['rid'];
            }
            else {
                $fast_comm_id = false;
            }

            if (isset($request['answer_comm_id'])) {
                $answer_comm_id = (int)$request['answer_comm_id'];
            }
            else {
                $answer_comm_id = false;
            }

            $str_date = time();

            if(!$fast_comm_id) {
                Antispam::Check(3, $user_id);
            }else {
                Antispam::Check(5, $user_id);
            }

            //Проверка на наличии юзера которую отправляется запись
            $check = $db->super_query("SELECT user_privacy, user_last_visit FROM `users` WHERE user_id = '{$for_user_id}'");

            if($check){

                if(isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files)){

                    //Приватность
                    $user_privacy = xfieldsdataload($check['user_privacy']);

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall2'] == 2 OR $user_privacy['val_wall1'] == 2 OR $user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
                        $check_friend = (new \App\Libs\Friends)->CheckFriends($for_user_id);

                    if(!$fast_comm_id){
                        if($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $check_friend OR $user_id == $for_user_id)
                            $xPrivasy = true;
                        else
                            $xPrivasy = false;
                    } else {
                        if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id)
                            $xPrivasy = true;
                        else
                            $xPrivasy = false;
                    }

                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id)
                        $xPrivasyX = true;
                    else
                        $xPrivasyX = false;

                    //ЧС
                    $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($for_user_id);
                    if(!$CheckBlackList){
                        if($xPrivasy){

                            //Опредиление изображения к ссылке
                            if(stripos($attach_files, 'link|') !== false){
                                $attach_arr = explode('||', $attach_files);
                                $cnt_attach_link = 1;
                                foreach($attach_arr as $attach_file){
                                    $attach_type = explode('|', $attach_file);
                                    if($attach_type[0] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type['1']) AND $cnt_attach_link == 1){
                                        //$domain_url_name = explode('/', $attach_type[1]);
                                        //$rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);
                                        $rImgUrl = $attach_type[4];
                                        $rImgUrl = str_replace("\\", "/", $rImgUrl);
                                        $img_name_arr = explode(".", $rImgUrl);
                                        $img_format = Gramatic::totranslit(end($img_name_arr));
                                        $server_time = \Sura\Time\Date::time();
                                        $image_rename = substr(md5($server_time.md5($rImgUrl)), 0, 15);

                                        //Разришенные форматы
                                        $allowed_files = array('jpg', 'jpeg', 'jpe', 'png');

                                        //Загружаем картинку на сайт
                                        if(in_array(strtolower($img_format), $allowed_files) AND preg_match("/https:\/\/(.*?)(.jpg|.png|.jpeg|.jpe)/i", $rImgUrl)){

                                            //Директория загрузки фото
                                            $upload_dir = __DIR__.'/../../public/uploads/attach/'.$user_id.'/';
                                            $res_type = '.'.$img_format;

                                            //Если нет папки юзера, то создаём её
                                            if(!is_dir($upload_dir)){
                                                if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                                                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                                                }
                                                chmod($upload_dir, 0777);
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

                            $attach_files = str_replace(array('vote|', '&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('hack|', '&#124;', '&raquo;', '&quot;'), $attach_files);

                            //Голосование
                            if (isset($request['vote_title'])) {
                                $vote_title = Validation::ajax_utf8($request['vote_title']);
                            }
                            else{
                                $vote_title = '';
                            }
                            if (isset($request['vote_answer_1'])) {
                                $vote_answer_1 = Validation::ajax_utf8($request['vote_answer_1']);
                            }
                            else{
                                $vote_answer_1 = '';
                            }

                            $ansver_list = array();

                            if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

                                for($vote_i = 1; $vote_i <= 10; $vote_i++){

                                    $vote_answer = Validation::ajax_utf8($request['vote_answer_'.$vote_i]);
                                    $vote_answer = str_replace('|', '&#124;', $vote_answer);

                                    if($vote_answer)
                                        $ansver_list[] = $vote_answer;

                                }

                                $sql_answers_list = implode('|', $ansver_list);

                                //Вставляем голосование в БД
                                $db->query("INSERT INTO `votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");

                                $attach_files = $attach_files."vote|{$db->insert_id()}||";

                            }

                            //Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
                            if($answer_comm_id){

                                //Выводим ид владельца комменатрия
                                $row_owner2 = $db->super_query("SELECT author_user_id FROM `wall` WHERE id = '{$answer_comm_id}' AND fast_comm_id != '0'");

                                //Проверка на то, что юзер не отвечает сам себе
                                if($user_id != $row_owner2['author_user_id'] AND $row_owner2){

                                    $check2 = $db->super_query("SELECT user_last_visit, user_name FROM `users` WHERE user_id = '{$row_owner2['author_user_id']}'");

                                    $wall_text = str_replace($check2['user_name'], "<a href=\"/u{$row_owner2['author_user_id']}\" onClick=\"Page.Go(this.href); return false\" class=\"newcolor000\">{$check2['user_name']}</a>", $wall_text);

                                    //Вставляем в ленту новостей
                                    $server_time = \Sura\Time\Date::time();
                                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$answer_comm_id}', for_user_id = '{$row_owner2['author_user_id']}', action_time = '{$server_time}'");

                                    //Вставляем событие в моментальные оповещания
                                    $update_time = $server_time - 70;

                                    if($check2['user_last_visit'] >= $update_time){

                                        $db->query("INSERT INTO `updates` SET for_user_id = '{$row_owner2['author_user_id']}', from_user_id = '{$user_id}', type = '5', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$for_user_id}_{$fast_comm_id}'");

                                        $cache->save("{$row_owner2['author_user_id']}/updates", 1);
                                        //ИНАЧЕ Добавляем +1 юзеру для оповещания
                                    } else {
                                        $value = $cache->load("{$row_owner2['author_user_id']}/new_news");
                                        $cache->save("{$row_owner2['author_user_id']}/new_news", $value+1);
                                    }

                                }

                            }

                            //Вставляем саму запись в БД
                            $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$for_user_id}', text = '{$wall_text}', add_date = '{$str_date}', fast_comm_id = '{$fast_comm_id}', attach = '".$attach_files."'");
                            $dbid = $db->insert_id();

                            //Если пользователь пишет сам у себя на стене, то вносим это в "Мои Новости"
                            if($user_id == $for_user_id AND !$fast_comm_id){
                                $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$str_date}'");
                            }

                            //Если добавляется комментарий к записи то вносим в ленту новостей "ответы"
                            if($fast_comm_id AND !$answer_comm_id){
                                //Выводим ид владельца записи
                                $row_owner = $db->super_query("SELECT author_user_id FROM `wall` WHERE id = '{$fast_comm_id}'");

                                if($user_id != $row_owner['author_user_id'] AND $row_owner){
                                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$fast_comm_id}', for_user_id = '{$row_owner['author_user_id']}', action_time = '{$str_date}'");

                                    //Вставляем событие в моментальные оповещания
                                    $server_time = \Sura\Time\Date::time();
                                    $update_time = $server_time - 70;

                                    if($check['user_last_visit'] >= $update_time){
                                        $db->query("INSERT INTO `updates` SET for_user_id = '{$row_owner['author_user_id']}', from_user_id = '{$user_id}', type = '1', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$for_user_id}_{$fast_comm_id}'");
                                        $cache->save("{$row_owner['author_user_id']}/updates", 1);
                                        //ИНАЧЕ Добавляем +1 юзеру для оповещания
                                    } else {
                                        $value = $cache->load("{$row_owner['author_user_id']}/new_news");
                                        if ($value == NULL){
                                            $value = 0;
                                        }
                                        $cache->save("{$row_owner['author_user_id']}/new_news", $value+1);
                                    }

                                    $config = Settings::load();

                                    //Отправка уведомления на E-mail
                                    if($config['news_mail_2'] == 'yes'){
                                        $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$row_owner['author_user_id']."'");
                                        if($rowUserEmail['user_email']){
//                                            $mail = new \dle_mail($config);
//                                            $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
//                                            $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '2'");
//                                            $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
//                                            $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
//                                            $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$row_owner['author_user_id'].'_'.$fast_comm_id, $rowEmailTpl['text']);
//                                            $mail->send($rowUserEmail['user_email'], 'Ответ на запись', $rowEmailTpl['text']);
                                        }
                                    }
                                }
                            }

                            if($fast_comm_id)
                                $db->query("UPDATE `wall` SET fasts_num = fasts_num+1 WHERE id = '{$fast_comm_id}'");
                            else{
                                $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$for_user_id}'");
//                                $Cache = cache_init(array('type' => 'file'));
                                //TODO update code
                                /*                                try {
                                //                                    $row = $Cache->get("users/{$for_user_id}/profile_{$for_user_id}");
                                //                                    $row['user_wall_num'] = $row['user_wall_num'] + 1;
                                //                                    $Cache->set("users/{$for_user_id}/profile_{$for_user_id}", $row);
                                                                }catch (Exception $e){

                                                                }*/
                            }

                            //Если добавлена просто запись, то сразу обновляем все записи на стене
                            Antispam::LogInsert(3, $user_id);

                            if(!$fast_comm_id){

//                                $CheckFriends = Tools::CheckFriends($for_user_id);

                                if($xPrivasyX){
                                    $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$for_user_id}' ORDER by `add_date` DESC LIMIT 0, {$limit_select}", true);

                                    //$wall->query("SELECT tb1.id, author_user_id, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}");
                                    $query = $db->super_query("SELECT tb1.id, author_user_id,       add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}", true);


                                    /*
                                    //                                    $tpl->load_template('wall/record.tpl');
                                    //                                    $compile = 'content';

                                    //                                    $server_time = \Sura\Time\Date::time();
//                                    //                                  $config = Settings::load();

                                    //                                    $Profile = new Profile;

                                                                        //$this->template;
                                    /*
                                                                        foreach($query as $key => $row_wall){
                                                                            $query[$key]['rec_id'] = $row_wall['id']; //!

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
                                    //                                        $jid = 0;
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

                                                                                        if (!isset($rodImHeigh))
                                                                                            $rodImHeigh = null;

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
                                                                                                $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\" align=\"left\"  alt=\"\"/></div>";
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

                                                                                            $row_video = $Profile->row_video($video_id);
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

                                                                                            if ($row_video['download'] == '1') {//bug: undefined
                                                                                                $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";
                                                                                            }else{
                                                                                                $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";
                                                                                            }
                                                                                        }

                                                                                        $resLinkTitle = '';

                                                                                        //Музыка
                                                                                    } elseif($attach_type[0] == 'audio'){
                                                                                        $data = explode('_', $attach_type[1]);
                                                                                        $audio_id = intval($data[0]);
                                                                                        $row_audio = $Profile->row_audio($audio_id);
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
                                    //                                                $count_num = count($attach_type);
                                                                                        $domain_url_name = explode('/', $attach_type['1']);
                                                                                        $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);

                                                                                        $attach_type['3'] = stripslashes($attach_type['3']);
                                                                                        $attach_type['3'] = iconv_substr($attach_type['3'], 0, 200, 'utf-8');

                                                                                        $attach_type['2'] = stripslashes($attach_type['2']);
                                                                                        $str_title = iconv_substr($attach_type['2'], 0, 55, 'utf-8');

                                                                                        if(stripos($attach_type[4], '/uploads/attach/') === false){
                                                                                            $attach_type['4'] = '/images/no_ava_groups_100.gif';
                                                                                            $no_img = false;
                                                                                        } else
                                                                                            $no_img = true;

                                                                                        if(!$attach_type['3']) $attach_type['3'] = '';

                                                                                        if($no_img AND $attach_type['2']){
                                                                                            if($row_wall['tell_comm']) $no_border_link = 'border:0px';

                                                                                            $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                                                                            $resLinkTitle = $attach_type['2'];
                                                                                            $resLinkUrl = $attach_type['1'];
                                                                                        } else if($attach_type['1'] AND $attach_type['2']){
                                                                                            $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                                                                            $resLinkTitle = $attach_type['2'];
                                                                                            $resLinkUrl = $attach_type['1'];
                                                                                        }

                                                                                        $cnt_attach_link++;

                                                                                        //Если документ
                                                                                    } elseif($attach_type['0'] == 'doc'){

                                                                                        $doc_id = intval($attach_type['1']);

                                                                                        $row_doc = $Profile->row_doc($doc_id);

                                                                                        if($row_doc){

                                                                                            $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                                                                            $cnt_attach++;
                                                                                        }

                                                                                        //Если опрос
                                                                                    } elseif($attach_type[0] == 'vote'){

                                                                                        $vote_id = intval($attach_type[1]);

                                                                                        $row_vote = $Profile->row_vote($vote_id);

                                                                                        if($vote_id){

                                                                                            $checkMyVote = $Profile->vote_check($vote_id, $user_id);

                                                                                            $row_vote['title'] = stripslashes($row_vote['title']);

                                                                                            if(!$row_wall['text'])
                                                                                                $row_wall['text'] = $row_vote['title'];

                                                                                            $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                                                                            $max = $row_vote['answer_num'];

                                                                                            $sql_answer = $Profile->vote_answer($vote_id);
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
                                                                                    $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 2);
                                                                                else
                                                                                    $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 1);

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

                                                                            $query[$key]['text'] = stripslashes($row_wall['text']);
                                                                            $query[$key]['name'] = $row_wall['user_search_pref'];
                                                                            $query[$key]['user_id'] = $row_wall['author_user_id'];
                                                                            $online = Online($row_wall['user_last_visit'], $row_wall['user_logged_mobile']);
                                                                            $query[$key]['online'] = $online;
                                                                            $date = megaDate($row_wall['add_date']);
                                                                            $query[$key]['date'] = $date;

                                                                            if($row_wall['user_photo']){
                                                                                $query[$key]['ava'] = '/uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo'];
                                                                            }
                                                                            else{
                                                                                $query[$key]['ava'] = '/images/no_ava_50.png';
                                                                            }

                                                                            //Мне нравится
                                                                            if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                                                                                $query[$key]['yes_like'] = 'public_wall_like_yes';
                                                                                $query[$key]['yes_like_color'] = 'public_wall_like_yes_color';
                                                                                $query[$key]['like_js_function'] = 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.', \'uPages\')';
                                                                            } else {
                                                                                $query[$key]['yes_like'] = '';
                                                                                $query[$key]['yes_like_color'] = '';
                                                                                $query[$key]['like_js_function'] = 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.', \'uPages\')';
                                                                            }

                                                                            if($row_wall['likes_num']){
                                                                                $query[$key]['likes'] = $row_wall['likes_num'];
                                                                                $titles = array('человеку', 'людям', 'людям');//like
                                                                                $query[$key]['likes_text'] = '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], $titles);
                                                                            } else {
                                                                                $query[$key]['likes'] = '';
                                                                                $query[$key]['likes_text'] = '<span id="like_text_num'.$row_wall['id'].'">0</span> человеку';
                                                                            }

                                                                            //Выводим информцию о том кто смотрит страницу для себя
                                                                            $query[$key]['viewer_id'] = $user_id;
                                                                            if($user_info['user_photo']){
                                                                                $query[$key]['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                                                                            }else{
                                                                                $query[$key]['viewer_ava'] = '/images/no_ava_50.png';
                                                                            }

                                                                            if($row_wall['type']){
                                                                                $query[$key]['type'] = $row_wall['type'];
                                                                            }else{
                                                                                $query[$key]['type'] = '';
                                                                            }

                                                                            //времменно
                                                                            if (!isset($for_user_id))
                                                                                $for_user_id = null;

                                                                            if(!isset($id))
                                                                                $id = $for_user_id;//bug: undefined

                                                                            //Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
                                                                            if($user_id == $row_wall['author_user_id'] OR $user_id == $id){
                                                                                $query[$key]['owner'] = true;
                                                                            } else{
                                                                                $query[$key]['owner'] = false;
                                                                            }

                                                                            //Показа кнопки "Рассказать др" только если это записи владельца стр.
                                                                            if($row_wall['author_user_id'] == $id AND $user_id != $id){
                                                                                $query[$key]['author_user_id'] = true;
                                                                            } else{
                                                                                $query[$key]['author_user_id'] = false;
                                                                            }

                                                                            //Если есть комменты к записи, то выполняем след. действия / Приватность
                                                                            if($row_wall['fasts_num']){
                                                                                $query[$key]['if_comments'] = false;
                                                                            } else {
                                                                                $query[$key]['if_comments'] = true;
                                                                            }

                                                                            //Приватность комментирования записей
                                                                            if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                                                                                $query[$key]['privacy_comment'] = true;
                                                                            } else{
                                                                                $query[$key]['privacy_comment'] = false;
                                                                            }

                                                                            $query[$key]['record'] = true;
                                                                            $query[$key]['comment'] = false;
                                                                            $query[$key]['comment_form'] = false;
                                                                            $query[$key]['all_comm'] = false;

                                                                            //Помещаем все комменты в id wall_fast_block_{id} это для JS
                                    //                                    $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'">';

                                                                            //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                                                                            if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                                                                                if($row_wall['fasts_num']){

                                                                                    if($row_wall['fasts_num'] > 3)
                                                                                        $comments_limit = $row_wall['fasts_num']-3;
                                                                                    else
                                                                                        $comments_limit = 0;

                                                                                    $sql_comments = $Profile->comments($row_wall['id'], $comments_limit);

                                                                                    //Загружаем кнопку "Показать N запсии"
                                                                                    $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                                                                                    $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                                                                                    $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2);

                                                                                    if($row_wall['fasts_num'] < 4){
                                                                                        $query[$key]['all_comm_block'] = false;
                                                                                    }else {
                                                                                        $query[$key]['rec_id'] = $row_wall['id'];
                                                                                    }
                                                                                    $query[$key]['author_id'] = $id;

                                                                                    $query[$key]['record_block'] = false;
                                                                                    $query[$key]['comment_form_block'] = false;
                                                                                    $query[$key]['comment_block'] = false;

                                                                                    //Сообственно выводим комменты
                                                                                    foreach($sql_comments as $key => $row_comments){
                                                                                        $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                                                                                        if($row_comments['user_photo']){
                                                                                            $sql_comments[$key]['ava'] = '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                                                                                        }else{
                                                                                            $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                                                                                        }

                                                                                        $sql_comments[$key]['rec_id'] = $row_wall['id'];
                                                                                        $sql_comments[$key]['comm_id'] = $row_comments['id'];
                                                                                        $sql_comments[$key]['user_id'] = $row_comments['author_user_id'];

                                                                                        $expBR2 = explode('<br />', $row_comments['text']);
                                                                                        $textLength2 = count($expBR2);
                                                                                        $strTXT2 = strlen($row_comments['text']);
                                                                                        if($textLength2 > 6 OR $strTXT2 > 470)
                                                                                            $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                                                                        //Обрабатываем ссылки
                                                                                        $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

                                                                                        $sql_comments[$key]['text'] = stripslashes($row_comments['text']);

                                                                                        $date = megaDate($row_comments['add_date']);
                                                                                        $sql_comments[$key]['date'] = $date;
                                                                                        if($user_id == $row_comments['author_user_id'] || $user_id == $id){
                                                                                            $sql_comments[$key]['owner_block'] = true;
                                                                                        } else{
                                                                                            $sql_comments[$key]['owner_block'] = false;
                                                                                        }

                                                                                        if($user_id == $row_comments['author_user_id']){
                                                                                            $sql_comments[$key]['not_owner'] = false;
                                                                                        }else {
                                                                                            $sql_comments[$key]['not_owner_block'] = true;
                                                                                        }

                                                                                        $sql_comments[$key]['comment_block'] = true;
                                                                                        $sql_comments[$key]['record_block'] = false;
                                                                                        $sql_comments[$key]['comment_form_block'] = false;
                                                                                        $sql_comments[$key]['all_comm_block'] = false;
                                                                                    }

                                                                                    //Загружаем форму ответа
                                                                                    $query[$key]['rec_id'] = $row_wall['id'];
                                                                                    $query[$key]['author_id'] = $id;
                                                                                    $query[$key]['comment_form_block'] = true;
                                                                                    $query[$key]['record_block'] = false;
                                                                                    $query[$key]['comment_block'] = false;
                                                                                    $query[$key]['all-comm_block'] = false;
                                                                                }
                                                                            }
                                                                        }
                                                                        */
//                                    $params['wall_records'] = $query;


                                }
                                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                                $cache = new \Sura\Cache\Cache($storage, 'users');
                                $cache->remove("{$for_user_id}/profile_{$for_user_id}");

//                                $config = Settings::load();

                                //Отправка уведомления на E-mail
                                if($config['news_mail_7'] == 'yes' AND $user_id != $for_user_id){
                                    $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$for_user_id."'");
                                    if($rowUserEmail['user_email']){
//                                        include_once __DIR__.'/../Classes/mail.php';
//                                        $mail = new \dle_mail($config);
//                                        $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
//                                        $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '7'");
//                                        $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
//                                        $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
//                                        $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'wall'.$for_user_id.'_'.$dbid, $rowEmailTpl['text']);
//                                        $mail->send($rowUserEmail['user_email'], 'Новая запись на стене', $rowEmailTpl['text']);
                                    }
                                }

//                                if(!isset($query))
//                                {
//                                    $query = array();
//                                }

                                //Если добавлен комментарий к записи то просто обновляем нужную часть, тоесть только часть комментариев, но не всю стену
                            } else {

                                Antispam::LogInsert(5, $user_id);
//
//                                //Выводим кол-во комментов к записи
                                $row = $db->super_query("SELECT fasts_num FROM `wall` WHERE id = '{$fast_comm_id}'");
                                $record_fasts_num = $row['fasts_num'];
                                if($record_fasts_num > 3)
                                    $limit_comm_num = $row['fasts_num']-3;
                                else
                                    $limit_comm_num = 0;
//
//                                $wall->comm_query("");
//
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT {$limit_comm_num}, 3", true);

//                                if($request['type'] == 1)
//                                    $wall->comm_template('news/news.tpl');
//                                else if($request['type'] == 2)
//                                    $wall->comm_template('wall/one_record.tpl');
//                                else
//                                    $wall->comm_template('wall/record.tpl');
//
//                                $wall->comm_compile('content');
//                                $wall->comm_select();

                            }

                            $params['wall_records'] = Wall::build($query);

                            return view('wall.one_record', array('wall_records' => $params['wall_records']));

                        }else{
                            $status = Status::PRIVACY;
                        }
//                        return _e('err_privacy');//PRIVACY
                    }else{
                        $status = Status::BLACKLIST;
                    }
//                    return _e('err_blacklist');//BLACKLIST
                }else{
                    $status = Status::NOT_DATA;
                }
//                return _e('err_not_content');//NOT_DATA
            }else{
                $status = Status::NOT_FOUND;
            }
//            return _e('err_check_user');//BAD_USER
        }else{
            $status = Status::BAD_LOGGED;
        }
//        return _e('err_auth');//BAD_LOGGED
        //FIXME update response
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаление записи со стены
     *
     * @return int
     * @throws \Throwable
     */
    public function delete(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

            $rid = (int)$request['rid'];
            //Проверка на существование записи и выводим ID владельца записи и кому предназначена запись
            $row = $db->super_query("SELECT author_user_id, for_user_id, fast_comm_id, add_date, attach FROM `wall` WHERE id = '{$rid}'", false);
            if($row['author_user_id'] == $user_id OR $row['for_user_id'] == $user_id){

                //удаляем саму запись
                $db->query("DELETE FROM `wall` WHERE id = '{$rid}'");

                //Если удаляется НЕ комментарий к записи
                if(!$row['fast_comm_id']){
                    //удаляем комменты к записиы
                    $db->query("DELETE FROM `wall` WHERE fast_comm_id = '{$rid}'");

                    //удаляем "мне нравится"
                    $db->query("DELETE FROM `wall_like` WHERE rec_id = '{$rid}'");

                    //обновляем кол-во записей
                    $db->query("UPDATE `users` SET user_wall_num = user_wall_num-1 WHERE user_id = '{$row['for_user_id']}'");

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$row['for_user_id']}/profile_{$row['for_user_id']}");

                    //удаляем из ленты новостей
                    $db->query("DELETE FROM `news` WHERE obj_id = '{$rid}' AND action_type = 6");

                    //Удаляем фотку из прикрипленой ссылки, если она есть
                    if(stripos($row['attach'], 'link|') !== false){
                        $attach_arr = explode('link|', $row['attach']);
                        $attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
                        $attach_arr3 = explode('||', $attach_arr2[1]);
                        if($attach_arr3['0']){
                            if (file_exists(__DIR__.'/../../public/uploads/attach/'.$user_id.'/'.$attach_arr3['0']))
                                unlink(__DIR__.'/../../public/uploads/attach/'.$user_id.'/'.$attach_arr3['0']);
                            //else error
                        }
                    }
                    $action_type = 1;
                }

                //Если удаляется комментарий к записи
                if($row['fast_comm_id']){
                    $db->query("UPDATE `wall` SET fasts_num = fasts_num-1 WHERE id = '{$row['fast_comm_id']}'");
                    $rid = $row['fast_comm_id'];

                    //удаляем из ленты новостей
                    $db->query("DELETE FROM `news` WHERE action_time = '{$row['add_date']}' AND action_type = '6' AND ac_user_id = '{$row['author_user_id']}'");
                    $action_type = 6;
                }
                //удаляем из ленты новостей
                $db->query("DELETE FROM `news` WHERE obj_id = '{$rid}' AND action_time = '{$row['add_date']}' AND action_type = {$action_type}");
//                return _e('true');
                $status = Status::OK;
            }else{
                $status = Status::NOT_FOUND;
//            return _e('err|not found|');//NOT_FOUND
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
//        return _e('err');//BAD_LOGGED
        //FIXME update response
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Ставим "Мне нравится"
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function like_yes(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $rid = (int)$request['rid'];
            //Проверка на существование записи
            $row = $db->super_query("SELECT text, likes_users, author_user_id FROM `wall` WHERE id = '{$rid}'");
            if($row){
                //Проверка на то что этот юзер ставил уже мне нрав или нет
                $likes_users = explode('|', str_replace('u', '', $row['likes_users']));
                if(!in_array($user_id, $likes_users, true)){
                    $server_time = \Sura\Time\Date::time();
                    $db->query("INSERT INTO `wall_like` SET rec_id = '{$rid}', user_id = '{$user_id}', date = '{$server_time}'");

                    $db->query("UPDATE `wall` SET likes_num = likes_num+1, likes_users = '|u{$user_id}|{$row['likes_users']}' WHERE id = '{$rid}'");

                    if($user_id != $row['author_user_id']){

                        //Вставляем событие в моментальные оповещения
                        $row_owner = $db->super_query("SELECT user_last_visit, notifications_list FROM `users` WHERE user_id = '{$row['author_user_id']}'");
                        $update_time = $server_time - 70;

                        if($row_owner['user_last_visit'] >= $update_time){

                            $row['text'] = strip_tags($row['text']);
                            if($row['text'])
                                $wall_text = ' &laquo;'.iconv_substr($row['text'], 0, 70, 'utf-8').'&raquo;';
                            else
                                $wall_text = '.';

                            $myRow = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_info['user_id']}'");
                            if($myRow['user_sex'] == 2)
                                $action_update_text = 'оценила Вашу запись'.$wall_text;
                            else
                                $action_update_text = 'оценил Вашу запись'.$wall_text;

                            $db->query("INSERT INTO `updates` SET for_user_id = '{$row['author_user_id']}', from_user_id = '{$user_info['user_id']}', type = '10', date = '{$server_time}', text = '{$action_update_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/wall{$row['author_user_id']}_{$rid}'");

                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'users');
                            $cache->save("{$row['author_user_id']}/updates", 1);
                        }

                        //Добавляем в ленту новостей "ответы"

                        $generateLastTime = $server_time-10800;
                        $row_news = $db->super_query("SELECT ac_id, action_text, action_time FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 7 AND obj_id = '{$rid}'");
                        if($row_news) {
                            $db->query("UPDATE `news` SET action_text = '|u{$user_id}|{$row_news['action_text']}', action_time = '{$server_time}' WHERE obj_id = '{$rid}' AND action_type = 7 AND action_time = '{$row_news['action_time']}'");
                        }
                        else {
                            $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 7, action_text = '|u{$user_id}|', obj_id = '{$rid}', for_user_id = '{$row['author_user_id']}', action_time = '{$server_time}'");
                        }

                        if(stripos($row_owner['notifications_list'], "settings_likes_gifts|") === false){

                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'users');
                            $cntCacheNews = $cache->load("{$row['author_user_id']}/new_news");
                            //FIXME
                            if (!is_int($cntCacheNews)){
                                $cntCacheNews = 1;
                            }else{
                                $cntCacheNews =+1;
                            }
                            $cache->save("{$row['author_user_id']}/new_news", $cntCacheNews);
                            $cache->save("{$row['author_user_id']}/updates", 1);
                        }
                        $status = Status::OK;
                    }else {
                        $status = Status::OWNER;
                    }
                }else {
                    $status = Status::FOUND;
//                    $err =  'Вы уже ставили like';
                }
            }else {
                $status = Status::NOT_FOUND;
            }
        }else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаляем "Мне нравится"
     *
     * @return int
     * @throws \JsonException
     */
    public function like_no(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $rid = (int)$request['rid'];
            //Проверка на существование записи
            $row = $db->super_query("SELECT likes_users FROM `wall` WHERE id = '{$rid}'");
            if($row){
                //Проверка на то что этот юзер ставил уже мне нрав или нет
                $likes_users = explode('|', str_replace('u', '', $row['likes_users']));
                if(in_array($user_id, $likes_users)){
                    $db->query("DELETE FROM `wall_like` WHERE rec_id = '{$rid}' AND user_id = '{$user_id}'");
                    $newListLikesUsers = strtr($row['likes_users'], array('|u'.$user_id.'|' => ''));
                    $db->query("UPDATE `wall` SET likes_num = likes_num-1, likes_users = '{$newListLikesUsers}' WHERE id = '{$rid}'");

                    //удаляем из ленты новостей
                    $row_news = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_type = 7 AND obj_id = '{$rid}'");
                    $row_news['action_text'] = strtr($row_news['action_text'], array('|u'.$user_id.'|' => ''));
                    if($row_news['action_text']) {
                        $db->query("UPDATE `news` SET action_text = '{$row_news['action_text']}' WHERE obj_id = '{$rid}' AND action_type = 7");
                    }
                    else {
                        $db->query("DELETE FROM `news` WHERE obj_id = '{$rid}' AND action_type = 7");
                    }
                }
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
     * Выводим первых 7 юзеров
     * которые поставили "мне нравится"
     *
     * @return int
     * @throws \JsonException
     */
    public function liked_users(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){

            $request = (Request::getRequest()->getGlobal());
//
            $rid = (int)$request['rid'];
            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo FROM `wall_like` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT 0, 7", true);

            if($sql_){
                $response = '';
                foreach($sql_ as $row){
                    if($row['user_photo']) {
                        $ava = '/uploads/users/' . $row['user_id'] . '/50_' . $row['user_photo'];
                    }
                    else {
                        $ava = '/images/no_ava_50.png';
                    }
                    $response .= '<a href="/u' . $row['user_id'] . '" id="Xlike_user' . $row['user_id'] . '_' . $rid . '" onClick="Page.Go(this.href); return false"><img src="' . $ava . '" width="32" alt="' . $row['user_id'] . '" /></a>';
                }
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
     * Выводим всех юзеров
     * которые поставили "мне нравится"
     *
     * @return int
     */
    public function all_liked_users(): int
    {
        $db = $this->db();
        $logged = $this->logged();
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $rid = (int)$request['rid'];
            $liked_num = (int)$request['liked_num'];
            if($request['page'] > 0) {
                $page = (int)$request['page'];
            } else {
                $page = 1;
            }
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            if(!$liked_num) {
                $liked_num = 24;
            }

            if($rid AND $liked_num){
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref FROM `wall_like` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", true);

                if($sql_){
                    $params['top'] = true;
//                    $tpl->load_template('profile_subscription_box_top.tpl');
                    $titles = array('человеку', 'людям', 'людям');//like
                    $params['subcr_num'] = 'Понравилось '.$liked_num.' '.Gramatic::declOfNum($liked_num, $titles);
                    $params['bottom'] = false;
//                    $tpl->compile('content');
//                    $tpl->result['content'] = str_replace('Всего', '', $tpl->result['content']);
//                    $tpl->load_template('profile_friends.tpl');
                    $config = Settings::load();
                    foreach($sql_ as $key => $row){
                        if($row['user_photo'])
                        {
                            $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                        }
                        else
                        {
                            $sql_[$key]['ava'] = '/images/no_ava_50.png';
                        }
                        $friend_info_online = explode(' ', $row['user_search_pref']);
                        $sql_[$key]['user_id'] = $row['user_id'];
                        $sql_[$key]['name'] = $friend_info_online[0];
                        $sql_[$key]['last_name'] = $friend_info_online[1];
                    }
                    $params['sql_'] = $sql_;
                    $navigation = Tools::box_navigation($gcount, $liked_num, $rid, 'wall.all_liked_users', $liked_num);
                    $params['navigation'] = $navigation;
                    return view('profile.profile_subscription_box_top', $params);
                }
            }
        }
        return _e('');
    }

    /**
     * Показ всех комментариев к записи
     *
     * @return int
     * @throws \JsonException
     */
    public function all_comm(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $fast_comm_id = (int)$request['fast_comm_id'];
            $for_user_id = (int)$request['for_user_id'];
            if($fast_comm_id AND $for_user_id){
                //Подгружаем и объявляем класс для стены
//                include __DIR__.'/../Classes/wall.php';
//                $wall = new \wall();

                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$for_user_id}'");
                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall3'] == 2 AND $user_id != $for_user_id)
                        $check_friend = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");

                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $check_friend OR $user_id == $for_user_id){
//                        $wall->comm_query();
                        $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, tb2.user_photo, user_search_pref, user_last_visit FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$fast_comm_id}' ORDER by `add_date` ASC LIMIT 0, 200", true);
//                        if($_POST['type'] == 1)
//                            $wall->comm_template('news/news.tpl');
//                        else if($_POST['type'] == 2)
//                            $wall->comm_template('wall/one_record.tpl');
//                        else
//                            $wall->comm_template('wall/record.tpl');
//                        $wall->comm_compile('content');
//                        $wall->comm_select();

                        $params['wall_records'] = Wall::build($query);
                        $status = Status::OK;
                    } else {
                        $status = Status::PRIVACY;
                    }
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
     * Показ предыдущих записей
     *
     * @return int
     */
    public function page(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $Profile = new Profile;
//        $config = Settings::load();
        $lang = langs::get_langs();
        $server_time = (int)$_SERVER['REQUEST_TIME'];

        if($logged){
            $user_id = $user_info['user_id'];
            $limit_select = 10;

            $request = (Request::getRequest()->getGlobal());

//
//            $path = explode('/', $_SERVER['REQUEST_URI']);
//
//            $for_user_id = $path['2'];
//            $last_id = $path['4'];

            $last_id = (int)$request['last_id'];
            $for_user_id = (int)$request['for_user_id'];

            //ЧС
            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($for_user_id);

            if(!$CheckBlackList AND $for_user_id AND $last_id){
                //Проверка на существование получателя
                $row = $db->super_query("SELECT user_privacy, user_wall_num FROM `users` WHERE user_id = '{$for_user_id}'");

                $params['blacklist_block'] = true;

                if($row['user_wall_num'] > 0){
                    $params['wall_rec_num_block'] = true;
                }else {
                    $params['wall_rec_num_block'] = false;
                }

                if($row){
                    //Приватность
                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    //Если приватность "Только друщья" то Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_privacy['val_wall1'] == 2 AND $user_id != $for_user_id) {
                        $check_friend = $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$for_user_id}' AND subscriptions = 0");
                    }

                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $for_user_id) {
                        $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_select}", true);
                    }
                    else {
                        $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, type, tell_uid, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE tb1.id < '{$last_id}' AND for_user_id = '{$for_user_id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '0' AND tb1.author_user_id = '{$for_user_id}' ORDER by `add_date` DESC LIMIT 0, {$limit_select}", true);
                    }

                    $params['wall_records'] = Wall::build($query);
                }
            }
            else{
                $params['blacklist_block'] = false;
            }

            $params['title'] = 'Wall';
            return view('profile.wall', $params);
        }

        $params['title'] = $lang['no_infooo'];
        $params['info'] = $lang['no_upage'];
        return view('info.info', $params);
    }

    /**
     * Рассказать друзьям "Мне нравится"
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function tell(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $rid = (int)$request['rid'];

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, author_user_id, tell_uid, tell_date, public, attach FROM `wall` WHERE fast_comm_id = '0' AND id = '{$rid}'");

            if($row){
                if($row['author_user_id'] != $user_id){
                    if($row['tell_uid']){
                        $row['add_date'] = $row['tell_date'];
                        $row['author_user_id'] = $row['tell_uid'];
                    }

                    //Проверяем на существование этой записи у себя на стене
                    $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE tell_uid = '{$row['author_user_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}'");
                    if(!$myRow['cnt']){
                        $row['text'] = $db->safesql($row['text']);
                        $row['attach'] = $db->safesql($row['attach']);

                        //Вставляем себе на стену
                        $server_time = \Sura\Time\Date::time();
                        $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['author_user_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '{$row['attach']}'");
                        $dbid = $db->insert_id();
                        $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                        //Вставляем в ленту новостей
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                        //Чистим кеш
                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $cache->remove("{$user_id}/profile_{$user_id}");


                        $status = Status::OK;
                    } else {
                        $status = Status::NOT_FOUND;
                    }
                } else {
                    $status = Status::OWNER;
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
     * Парсер информации о ссылке
     *
     * @return int
     * @throws \JsonException
     */
    public function parse_link(): int
    {
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $lnk = 'https://'.str_replace('https://', '', trim($_POST['lnk']));
            $check_url = get_headers(stripslashes($lnk));

            if(strpos($check_url['0'], '200')){
                $open_lnk = @file_get_contents($lnk);

                if(stripos(strtolower($open_lnk), 'charset=utf-8') OR stripos(strtolower($check_url['2']), 'charset=utf-8')) {
                    $open_lnk = Validation::ajax_utf8($open_lnk);
                }
                else {
                    $open_lnk = iconv('windows-1251', 'utf-8', $open_lnk);
                }

                if(stripos(strtolower($open_lnk), 'charset=KOI8-R')) {
                    $open_lnk = iconv('KOI8-R', 'utf-8', $open_lnk);
                }

                preg_match("/<meta property=([\"'])og:title([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_title);
                if(!$parse_title['4']) {
                    preg_match("/<meta name=([\"'])title([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_title);
                }

                $res_title = $parse_title['4'];

                if(!$res_title){
                    preg_match_all('`(<title>[^\[]+</title>)`si', $open_lnk, $parse);
                    $res_title = str_replace(array('<title>', '</title>'), '', $parse['1']['0']);
                }

                preg_match("/<meta property=([\"'])og:description([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_descr);
                if(!$parse_descr['4']) {
                    preg_match("/<meta name=([\"'])description([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_descr);
                }

                $res_descr = strip_tags($parse_descr['4']);
                $res_title = strip_tags($res_title);

                $open_lnk = preg_replace('`(<!--noindex-->|<noindex>).+?(<!--/noindex-->|</noindex>)`si', '', $open_lnk);

                preg_match("/<meta property=([\"'])og:image([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_img);
                if(!$parse_img['4']) {
                    preg_match_all('/<img(.*?)src=\"(.*?)\"/', $open_lnk, $array);
                }
                else {
                    $array['2']['0'] = $parse_img['4'];
                }

                $res_title = str_replace("|", "&#124;", $res_title);
                $res_descr = str_replace("|", "&#124;", $res_descr);
                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png');
                $expImgs = explode('<img', $open_lnk);
                if($expImgs['1']){
                    foreach($expImgs as $key => $img){
                        $exp1 = explode('src="', $img);
                        $exp2 = explode('/>', $exp1['1']);
                        $exp3 = explode('"', $exp2['0']);
                        $array1 = explode('.', $exp3['0']);
                        $expFormat = end($array1);
                        if(in_array(strtolower($expFormat), $allowed_files)){
                            $domain_url_name = explode('/', $lnk);
                            $rdomain_url_name = str_replace('http://', '', $domain_url_name[2]);
                            $new_imgs = '';
                            if(stripos(strtolower($exp3['0']), 'http://') === false)
                                $new_imgs .= 'http://'.$rdomain_url_name.'/'.$exp3['0'].'|';
                            else
                                $new_imgs .= $exp3['0'].'|';
                            if($key == 0)
                                $img_link = str_replace('|', '', $new_imgs);
                        }

                    }

                }

                preg_match("/<meta property=([\"'])og:image([\"']) content=([\"'])(.*?)([\"'])(.*?)>/is", $open_lnk, $parse_img);
//                preg_match("/<meta property=(\"|')og:image(\"|') content=(\"|')(.*?)(\"|')(.*?)>/is", $open_lnk, $parse_img);
                if($parse_img['4']){
                    $rIMGx = explode('?', $parse_img['4']);
                    $img_link = $rIMGx['0'];
                    if(!$new_imgs) {
                        $new_imgs = $img_link;
                    }
                }
                echo $res_title.'<f>'.$res_descr.'<f>'.$img_link.'<f>'.$new_imgs;
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
     * Показ последних 10 записей
     *
     * @return int
     * @throws \Throwable
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = Db::getDB();
        $Profile = new Profile;

        $user_info = $this->user_info();

        $requests = Request::getRequest();
//        $request = ($requests->getGlobal());
        $request = (Request::getRequest()->getGlobal());
        $server = $requests->server;

        $path = explode('/', $server['REQUEST_URI']);
        $id = (int)$path['2'];

        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
        $cache = new \Sura\Cache\Cache($storage, 'users');
        $key = "{$id}/profile_{$id}";
        $value = $cache->load($key, function (&$dependencies) {
            $dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
        });
        if ($value == NULL){
            $dir = __DIR__.'/../cache/users/'.$id.'/';
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \Exception(sprintf('Directory "%s" was not created', $dir));
            }
            $row = $Profile->user_row($id);
            $value = serialize($row);
            $cache->save($key, $value);
        }else{
            $row = unserialize($value, $options = []);
        }

        $user_id = $user_info['user_id'];

        if($user_id !== $id){
            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($row['user_id']);
            $CheckFriends = (new \App\Libs\Friends)->CheckFriends($row['user_id']);
        }else{
            $CheckBlackList = false;
            $CheckFriends = false;
        }

        $user_privacy = xfieldsdataload($row['user_privacy']);

        $limit_select = 10;
        $limit_page = 0;

        /**
         * Стена
         */

        //Приватность стены
        //кто может писать на стене
        if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $CheckFriends OR $user_id == $id){
            //                        $tpl->set('[privacy-wall]', '');
            $params['privacy_wall_block'] = true;
        } elseif($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $CheckFriends OR $user_id == $id){
            //                        $tpl->set('[privacy-wall]', '');
            $params['privacy_wall_block'] = true;
        } else{
            //                        $tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");
            $params['privacy_wall_block'] = false;
        }

        if($user_id != $id){
            if($user_privacy['val_wall1'] == 3 OR $user_privacy['val_wall1'] == 2 AND !$CheckFriends){
                $cnt_rec = $Profile->cnt_rec($id);
                $row['user_wall_num'] = $cnt_rec['cnt'];
                $params['wall_rec_num'] = $row['user_wall_num'];
            }else {
                $params['wall_rec_num'] = $row['user_wall_num'];
            }
        }else {
            $params['wall_rec_num'] = $row['user_wall_num'];
        }

        $row['user_wall_num'] = $row['user_wall_num'] ? $row['user_wall_num'] : '';
        if($row['user_wall_num'] > 10){
            $params['wall_link_block'] = true;
        }else{
            $params['wall_link_block'] = true;
        }

        if($row['user_wall_num'] > 0){
            $params['wall_rec_num_block'] = true;
        }else {
            $params['wall_rec_num_block'] = false;
        }


        if($row['user_wall_num']  AND !$CheckBlackList){
            //################### Показ последних 10 записей ###################//

            //Если вызвана страница стены, не со страницы юзера
            if(!$id){
                if (isset($request['rid'])) {
                    $rid = (int)$request['rid'];
                }
                else {
                    $rid = null;
                }

                if (isset($request['uid'])) {
                    $id = (int)$request['uid'];
                }
                else {
                    $id = $user_id;
                }

//                            $walluid = $id;
                $params['title'] = $lang['wall_title'];

                if($request['page'] > 0) {
                    $page = (int)$request['page'];
                } else {
                    $page = 1;
                }
                $gcount = 10;
                $limit_page = ($page-1)*$gcount;
                //not used row_user['user_privacy']
                //$row_user = $db->super_query("SELECT user_name, user_wall_num, user_privacy FROM `users` WHERE user_id = '{$id}'");
                $user_privacy = xfieldsdataload($row['user_privacy']);

                if($row['user_wall_num'] > 0){
                    //ЧС
                    $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($id);
                    if(!$CheckBlackList){

                        if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $CheckFriends OR $user_id == $id)
                            $cnt_rec['cnt'] = $row['user_wall_num'];
                        else
                            $cnt_rec = $Profile->cnt_rec($id);

                        /**
                         * record_tab
                         */
                        if($request['type'] == 'own'){
                            $params['record_tab'] = false;
                            $cnt_rec = $Profile->cnt_rec($id);
                            $where_sql = "AND tb1.author_user_id = '{$id}'";
//                                        $page_type = '/wall'.$id.'_sec=own&page=';
                            $wallAuthorId = null;
                        } else if($request['type'] == 'record'){
                            $params['record_tab'] = true;
                            $where_sql = "AND tb1.id = '{$rid}'";
                            $wallAuthorId = $Profile->author_user_id($rid);
                        } else {
                            $params['record_tab'] = false;
                            $request['type'] = '';
                            $where_sql = '';
                            $wallAuthorId = null;
                            //                                        $tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
//                                        $page_type = '/wall'.$id.'/page/';
                        }

                        //$titles = array('запись', 'записи', 'записей');//rec
                        //                                    if($cnt_rec['cnt'] > 0)
                        //                                        $user_speedbar = 'На стене '.$cnt_rec['cnt'].' '.Gramatic::declOfNum($cnt_rec['cnt'], $titles);

                        //                                    $tpl->load_template('wall/head.tpl');
                        $params['wall_head']['name'] = Gramatic::gramatikName($row['user_name']);
                        $params['wall_head']['uid'] = $id;
                        $params['wall_head']['rec_id'] = $rid;
                        $params['wall_head']['activetab_'.$request['type']] = 'activetab';

                        if($cnt_rec['cnt'] < 1){
                            // msgbox('', $lang['wall_no_rec'], 'info_2');
                            $params['msg_box'] = $lang['wall_no_rec'];
                        }

                    } else {
                        //                                    $user_speedbar = $lang['error'];
                        //msgbox('', $lang['no_notes'], 'info');
                        $params['msg_box'] = $lang['no_notes'];
                    }
                } else{
                    //msgbox('', $lang['wall_no_rec'], 'info_2');
                    $params['msg_box'] = $lang['wall_no_rec'];
                }

            }

            if(!isset($where_sql))
                $where_sql = null;

            if(!$CheckBlackList){
                if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $CheckFriends OR $user_id == $id) {
                    $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                }
                elseif($wallAuthorId['author_user_id'] == $id) {
                    $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                }
                else {
                    $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                }


//                                if (isset($request['rid']))
//                                    $rid = (int)$request['rid'];
//                                else
                $rid = null;

//                                if (isset($request['uid']))
//                                    $id = (int)$request['uid'];
//                                else
//                                    $id = $user_id;

                $walluid = $id;

                /**
                 * @deprecated
                 */
                /*                                if($rid OR $walluid){
                                                    $params['compile'] = 'content';
                                    //                                    if($cnt_rec['cnt'] > $gcount AND $_GET['type'] == '' OR $request['type'] == 'own'){
                                                                            //$tpl = Tools::navigation($gcount, $cnt_rec['cnt'], $page_type, $tpl);
                                                                            //bug !!!
                                    //                                    }
                                                } else {
                                                    $params['compile'] = 'wall';
                                                }*/

                $server_time = (int)$_SERVER['REQUEST_TIME'];
//                $config = Settings::load();

                /**
                 * wall records
                 *
                 * @var $query array
                 */
                /*
                                foreach($query as $key => $row_wall){
                                    $query[$key]['rec_id'] = $row_wall['id']; //!

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
                                        //                                        $jid = 0;
                                        $attach_result = '';
                                        $attach_result .= '<div class="clear"></div>';
                                        foreach($attach_arr as $attach_file){
                                            $attach_type = explode('|', $attach_file);

                                            //Фото со стены сообщества
                                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}")){
                                                if($cnt_attach < 2)
                                                    $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/{$attach_type[1]}\"  alt=\"\" /></div>";
                                                else
                                                    $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$row_wall['tell_uid']}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\"  alt=\"\"/>";

                                                $cnt_attach++;

                                                $resLinkTitle = '';

                                                //Фото со стены юзера
                                            } elseif($attach_type[0] == 'photo_u'){

                                                if (!isset($rodImHeigh))
                                                    $rodImHeigh = null;

                                                if($row_wall['tell_uid']) $attauthor_user_id = $row_wall['tell_uid'];
                                                else $attauthor_user_id = $row_wall['author_user_id'];

                                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){

                                                    if($cnt_attach == 1)

                                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\"  alt=\"\"/></div>";

                                                    else

                                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" height=\"{$rodImHeigh}\"  alt=\"\"/>";


                                                    $cnt_attach++;


                                                } elseif(file_exists(__DIR__."/../../public/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}")){

                                                    if($cnt_attach < 2)
                                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/{$attach_type[1]}\"  alt=\"\"/></div>";
                                                    else
                                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/users/{$attauthor_user_id}/albums/{$attach_type[2]}/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$row_wall['tell_uid']}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\"  alt=\"\"/>";

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

                                                    $row_video = $Profile->row_video($video_id);
                                                    $row_video['title'] = stripslashes($row_video['title']);
                                                    $row_video['video'] = stripslashes($row_video['video']);
                                                    $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));


                                                    if ($row_video['download'] == '1') {
                                                        $attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
                                                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;max-width: 500px;\" height=\"350\"  alt=\"\"/></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
                                                    }else{
                                                        $attach_result .= "<div class=\"cursor_pointer clear\" href=\"/video{$attauthor_user_id}_{$video_id}_sec=wall/fuser={$attauthor_user_id}\" id=\"no_video_frame{$video_id}\" onClick=\"videos.show({$video_id}, this.href, '/u{$attauthor_user_id}')\">
                                                                        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;max-width: 500px;\" height=\"350\"  alt=\"\"/></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div>";
                                                    }
                                                } else {

                                                    if ($row_video['download'] == '1') {//bug: undefined
                                                        $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\"  alt=\"\"/></a></div>";
                                                    }else{
                                                        $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"width: 175px;height: 131px;margin-top:3px;margin-right:3px\"  alt=\"\"/></a></div>";
                                                    }
                                                }

                                                $resLinkTitle = '';

                                                //Музыка
                                            } elseif($attach_type[0] == 'audio'){
                                                $data = explode('_', $attach_type[1]);
                                                $audio_id = intval($data[0]);
                                                $row_audio = $Profile->row_audio($audio_id);
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
                                                                    cellpadding=\"0\"><table cellpadding=\"0\" width=\"100%\"><tbody><tr><td
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
                                                //                                                $count_num = count($attach_type);
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

                                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'"  alt=""/></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                                    $resLinkTitle = $attach_type[2];
                                                    $resLinkUrl = $attach_type[1];
                                                } else if($attach_type[1] AND $attach_type[2]){
                                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away/?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

                                                    $resLinkTitle = $attach_type[2];
                                                    $resLinkUrl = $attach_type[1];
                                                }

                                                $cnt_attach_link++;

                                                //Если документ
                                            } elseif($attach_type[0] == 'doc'){

                                                $doc_id = intval($attach_type[1]);

                                                $row_doc = $Profile->row_doc($doc_id);

                                                if($row_doc){

                                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                                    $cnt_attach++;
                                                }

                                                //Если опрос
                                            } elseif($attach_type[0] == 'vote'){

                                                $vote_id = intval($attach_type[1]);

                                                $row_vote = $Profile->row_vote($vote_id);

                                                if($vote_id){

                                                    $checkMyVote = $Profile->vote_check($vote_id, $user_id);

                                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                                    if(!$row_wall['text'])
                                                        $row_wall['text'] = $row_vote['title'];

                                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                                    $max = $row_vote['answer_num'];

                                                    $sql_answer = $Profile->vote_answer($vote_id);
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
                                            $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 2);
                                        else
                                            $rowUserTell = $Profile->user_tell_info($row_wall['tell_uid'], 1);

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
                                                        <div class="wall_tell_info"><div class="wall_tell_ava"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><img src="{$avaTell}" width="30"  alt=""/></a></div><div class="wall_tell_name"><a href="/{$tell_link}{$row_wall['tell_uid']}" onClick="Page.Go(this.href); return false"><b>{$rowUserTell['user_search_pref']}</b></a></div><div class="wall_tell_date">{$dateTell}</div></div>{$row_wall['text']}
                                                        <div class="clear"></div>
                                                        </div>
                                                        HTML;
                                    }

                                    $query[$key]['text'] = stripslashes($row_wall['text']);
                                    $query[$key]['name'] = $row_wall['user_search_pref'];
                                    $query[$key]['user_id'] = $row_wall['author_user_id'];
                                    $online = Online($row_wall['user_last_visit'], $row_wall['user_logged_mobile']);
                                    $query[$key]['online'] = $online;
                                    $date = megaDate($row_wall['add_date']);
                                    $query[$key]['date'] = $date;

                                    if($row_wall['user_photo']){
                                        $query[$key]['ava'] = '/uploads/users/'.$row_wall['author_user_id'].'/50_'.$row_wall['user_photo'];
                                    }
                                    else{
                                        $query[$key]['ava'] = '/images/no_ava_50.png';
                                    }

                                    //Мне нравится
                                    if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                                        $query[$key]['yes_like'] = 'public_wall_like_yes';
                                        $query[$key]['yes_like_color'] = 'public_wall_like_yes_color';
                                        $query[$key]['like_js_function'] = 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.', \'uPages\')';
                                    } else {
                                        $query[$key]['yes_like'] = '';
                                        $query[$key]['yes_like_color'] = '';
                                        $query[$key]['like_js_function'] = 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.', \'uPages\')';
                                    }

                                    if($row_wall['likes_num']){
                                        $query[$key]['likes'] = $row_wall['likes_num'];
                                        $titles = array('человеку', 'людям', 'людям');//like
                                        $query[$key]['likes_text'] = '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], $titles);
                                    } else {
                                        $query[$key]['likes'] = '';
                                        $query[$key]['likes_text'] = '<span id="like_text_num'.$row_wall['id'].'">0</span> человеку';
                                    }

                                    //Выводим информцию о том кто смотрит страницу для себя
                                    $query[$key]['viewer_id'] = $user_id;
                                    if($user_info['user_photo']){
                                        $query[$key]['viewer_ava'] = '/uploads/users/'.$user_id.'/50_'.$user_info['user_photo'];
                                    }else{
                                        $query[$key]['viewer_ava'] = '/images/no_ava_50.png';
                                    }

                                    if($row_wall['type']){
                                        $query[$key]['type'] = $row_wall['type'];
                                    }else{
                                        $query[$key]['type'] = '';
                                    }

                                    //времменно
                                    if (!isset($for_user_id))
                                        $for_user_id = null;

                                    if(!$id)
                                        $id = $for_user_id;//bug: undefined

                                    //Тег Owner означает показ записей только для владельца страницы или для того кто оставил запись
                                    if($user_id == $row_wall['author_user_id'] OR $user_id == $id){
                                        $query[$key]['owner'] = true;
                                    } else{
                                        $query[$key]['owner'] = false;
                                    }

                                    //Показа кнопки "Рассказать др" только если это записи владельца стр.
                                    if($row_wall['author_user_id'] == $id AND $user_id != $id){
                                        $query[$key]['author_user_id'] = true;
                                    } else{
                                        $query[$key]['author_user_id'] = false;
                                    }

                                    //Если есть комменты к записи, то выполняем след. действия / Приватность
                                    if($row_wall['fasts_num']){
                                        $query[$key]['if_comments'] = false;
                                    } else {
                                        $query[$key]['if_comments'] = true;
                                    }

                                    //Приватность комментирования записей
                                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                                        $query[$key]['privacy_comment'] = true;
                                    } else{
                                        $query[$key]['privacy_comment'] = false;
                                    }

                                    $query[$key]['record'] = true;
                                    $query[$key]['comment'] = false;
                                    $query[$key]['comment_form'] = false;
                                    $query[$key]['all_comm'] = false;

                                    //Помещаем все комменты в id wall_fast_block_{id} это для JS
                                    //                                    $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'">';

                                    //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                                    if($user_privacy['val_wall3'] == 1 OR $user_privacy['val_wall3'] == 2 AND $CheckFriends OR $user_id == $id){
                                        if($row_wall['fasts_num']){

                                            if($row_wall['fasts_num'] > 3)
                                                $comments_limit = $row_wall['fasts_num']-3;
                                            else
                                                $comments_limit = 0;

                                            $sql_comments = $Profile->comments($row_wall['id'], $comments_limit);

                                            //Загружаем кнопку "Показать N запсии"
                                            $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                                            $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                                            $query[$key]['gram_record_all_comm'] = Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles1).' '.($row_wall['fasts_num']-3).' '.Gramatic::declOfNum(($row_wall['fasts_num']-3), $titles2);

                                            if($row_wall['fasts_num'] < 4){
                                                $query[$key]['all_comm_block'] = false;
                                            }else {
                                                $query[$key]['rec_id'] = $row_wall['id'];
                                            }
                                            $query[$key]['author_id'] = $id;

                                            $query[$key]['record_block'] = false;
                                            $query[$key]['comment_form_block'] = false;
                                            $query[$key]['comment_block'] = false;

                                            //Сообственно выводим комменты
                                            foreach($sql_comments as $key => $row_comments){
                                                $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                                                if($row_comments['user_photo']){
                                                    $sql_comments[$key]['ava'] = '/uploads/users/'.$row_comments['author_user_id'].'/50_'.$row_comments['user_photo'];
                                                }else{
                                                    $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                                                }

                                                $sql_comments[$key]['rec_id'] = $row_wall['id'];
                                                $sql_comments[$key]['comm_id'] = $row_comments['id'];
                                                $sql_comments[$key]['user_id'] = $row_comments['author_user_id'];

                                                $expBR2 = explode('<br />', $row_comments['text']);
                                                $textLength2 = count($expBR2);
                                                $strTXT2 = strlen($row_comments['text']);
                                                if($textLength2 > 6 OR $strTXT2 > 470)
                                                    $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                                //Обрабатываем ссылки
                                                $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away/?url=$1" target="_blank">$1</a>', $row_comments['text']);

                                                $sql_comments[$key]['text'] = stripslashes($row_comments['text']);

                                                $date = megaDate($row_comments['add_date']);
                                                $sql_comments[$key]['date'] = $date;
                                                if($user_id == $row_comments['author_user_id'] || $user_id == $id){
                                                    $sql_comments[$key]['owner_block'] = true;
                                                } else{
                                                    $sql_comments[$key]['owner_block'] = false;
                                                }

                                                if($user_id == $row_comments['author_user_id']){
                                                    $sql_comments[$key]['not_owner'] = false;
                                                }else {
                                                    $sql_comments[$key]['not_owner_block'] = true;
                                                }

                                                $sql_comments[$key]['comment_block'] = true;
                                                $sql_comments[$key]['record_block'] = false;
                                                $sql_comments[$key]['comment_form_block'] = false;
                                                $sql_comments[$key]['all_comm_block'] = false;
                                            }

                                            //Загружаем форму ответа
                                            $query[$key]['rec_id'] = $row_wall['id'];
                                            $query[$key]['author_id'] = $id;
                                            $query[$key]['comment_form_block'] = true;
                                            $query[$key]['record_block'] = false;
                                            $query[$key]['comment_block'] = false;
                                            $query[$key]['all-comm_block'] = false;
                                        }
                                    }

                                    //Закрываем блок для JS
                                    //                                    $tpl->result[$compile] .= '</div>';
                                }
                                */
//                $params['wall_records'] = $query;
                $params['wall_records'] = Wall::build($query);
            }
        }

        $lang = $this->get_langs();
        $params['title'] = $lang['no_infooo'];
        $params['info'] = $lang['no_upage'];
        return view('wall.wall', $params);
    }
}