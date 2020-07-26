<?php

namespace App\Modules;

use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Templates;
use Sura\Libs\Tools;
use Sura\Libs\Cache;
use Sura\Libs\Gramatic;
use App\Models\Profile;

class ProfileController extends Module{

    public function index($params){
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $user_id = $user_info['user_id'];

        if($logged){

            $config = Settings::loadsettings();

            /*
             * ID user page
             */
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $id = str_replace('u', '', $path);
            $id = intval($id['1']);

            $cache_folder = 'user_'.$id;
            //Читаем кеш
            $row = unserialize(Cache::mozg_cache($cache_folder.'/profile_'.$id));

            //Проверяем на наличие кеша, если нету то выводи из БД и создаём его
            if(!$row){
                $row = Profile::user_row($id);
                if($row){
                    Cache::mozg_create_folder_cache($cache_folder);
                    Cache::mozg_create_cache($cache_folder.'/profile_'.$id, serialize($row));
                }
                $row_online['user_last_visit'] = $row['user_last_visit'];
                $row_online['user_logged_mobile'] = $row['user_logged_mobile'];
            } else{
                $row_online = Profile::user_online($id);
            }

            //Если есть такой, юзер то продолжаем выполнение скрипта
            if($row){
                //Profile_ban = $row['user_search_pref'];
                $params['title'] = $row['user_search_pref'].' | Sura';

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Если удалена
                if($row['user_delet']){
                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    //Если заблокирована
                } elseif($row['user_ban_date'] >= $server_time OR $row['user_ban_date'] == '0'){
                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    //Если все хорошо, то выводим дальше
                } else {


                    if($user_id != $id){
                        $CheckBlackList = Tools::CheckBlackList($row['user_id']);
                        $CheckFriends = Tools::CheckFriends($row['user_id']);

                    }else{
                        $CheckBlackList = false;
                        $CheckFriends = false;

                    }



                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    $user_country_city_name_exp = explode('|', $row['user_country_city_name']);

                    /**
                     * Друзья
                     */
                    if($row['user_friends_num'] > 0 AND !$CheckBlackList){
                        $sql_friends = Profile::friends($id);
                        foreach($sql_friends as $key => $row_friends){
                            $friend_info = explode(' ', $row_friends['user_search_pref']);
                            $sql_friends[$key]['user_id'] = $row_friends['friend_id'];
                            $sql_friends[$key]['name'] = $friend_info['0'];
                            $sql_friends[$key]['lastname'] = $friend_info['1'];
                            if($row_friends['user_photo']){
                                $sql_friends[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_friends['friend_id'].'/50_'.$row_friends['user_photo'];
                            }else{
                                $sql_friends[$key]['ava'] =  '/images/no_ava_50.png';
                            }
                        }
                        $params['all_friends'] = $sql_friends;
                        $params['all_friends_num'] = $row['user_friends_num'];
                    }


                    /**
                     * Друзья на сайте
                     *
                     * @var $sql_friends_online array()
                     */


                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        $check_friend = Tools::CheckFriends($row['user_id']);

                    $online_time = $server_time - 60;
                    //Кол-во друзей в онлайне
                    if($row['user_friends_num'] > 0 AND !$CheckBlackList ){
                        $online_friends = Profile::friends_online_cnt($id, $online_time);
                        //Если друзья на сайте есть то идем дальше
                        if($online_friends['cnt']){
                            $sql_friends_online = Profile::friends_online($id, $online_time);
                            foreach($sql_friends_online as $key => $row_friends_online){
                                $friend_info_online = explode(' ', $row_friends_online['user_search_pref']);
                                $sql_friends_online[$key]['user_id'] = $row_friends_online['user_id'];
                                $sql_friends_online[$key]['name'] = $friend_info_online[0];
                                $sql_friends_online[$key]['lastname'] = $friend_info_online[1];
                                if($row_friends_online['user_photo']){
                                    $sql_friends_online[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_friends_online['user_id'].'/50_'.$row_friends_online['user_photo'];
                                }else{
                                    $sql_friends_online[$key]['ava'] = '/images/no_ava_50.png';
                                }
                            }
                            $params['all_online_friends'] = $sql_friends_online;
                            $params['all_online__friends_num'] = $online_friends['cnt'];
                        }else
                            $params['all_online_friends'] = false;
                    }else
                        $params['all_online_friends'] = false;

                    /**
                     * Видеозаписи
                     */
                    if($row['user_videos_num'] > 0 AND $config['video_mod'] == 'yes' AND !$CheckBlackList){
                        //Настройки приватности
                        if($user_id == $id){
                            $sql_privacy = "";
                            $cache_pref_videos = '';
                        }elseif(isset($CheckFriends)){//bug: undefined
                            $sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
                            $cache_pref_videos = "_friends";
                        } else {
                            $sql_privacy = "AND privacy = 1";
                            $cache_pref_videos = "_all";
                        }

                        //Если страницу смотрит другой юзер, то считаем кол-во видео
//                        if($user_id != $id){
//                            $row['user_videos_num'] = $video_cnt['cnt'];
//                        }

                        $video_cnt = Profile::videos_online_cnt($id, $sql_privacy, $cache_pref_videos);
                        $row['user_videos_num'] = $video_cnt['cnt'];

                        $sql_videos = Profile::videos_online($id, $sql_privacy, $cache_pref_videos);

                        foreach($sql_videos as $key => $row_videos){
                            $sql_videos[$key]['photo'] =  $row_videos['photo'];
                            $sql_videos[$key]['user_id'] = $id;
                            $sql_videos[$key]['title'] = stripslashes($row_videos['title']);
                            $titles = array('комментарий', 'комментария', 'комментариев');//comments
                            $sql_videos[$key]['comm_num'] = $row_videos['comm_num'].' '.Gramatic::declOfNum($row_videos['comm_num'], $titles);
                            $date = megaDate(strtotime($row_videos['add_date']), '');
                            $sql_videos[$key]['date'] = $date;
                        }
                        $params['videos_num'] = $video_cnt['cnt'];
                        $params['videos'] = $sql_videos;
                    }else
                        $params['videos'] = false;

                    /**
                     * Подписки
                     */
                    if($row['user_subscriptions_num'] > 0 AND !$CheckBlackList){
                        $subscriptions = Cache::mozg_cache('/subscr_user_'.$id);
                        if(!isset($subscriptions)){
                            $sql_subscriptions = Profile::subscriptions($id);
                            foreach($sql_subscriptions as $key => $row_subscr){
                                $sql_subscriptions[$key]['user_id'] = $row_subscr['friend_id'];
                                $sql_subscriptions[$key]['name'] = $row_subscr['user_search_pref'];
                                if($row_subscr['user_status']){
                                    $sql_subscriptions[$key]['info'] = stripslashes(iconv_substr($row_subscr['user_status'], 0, 24, 'utf-8'));
                                }else {
                                    $country_city = explode('|', $row_subscr['user_country_city_name']);
                                    $sql_subscriptions[$key]['info'] = $country_city[1];
                                }
                                if($row_subscr['user_photo']){
                                    $sql_subscriptions[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_subscr['friend_id'].'/50_'.$row_subscr['user_photo'];
                                }else{
                                    $sql_subscriptions[$key]['ava'] = '/images/no_ava_50.png';
                                }
                            }
                            $params['subscriptions'] = $sql_subscriptions;
                            $params['subscriptions_num'] = $row['user_subscriptions_num'];
//                            Cache::mozg_create_cache('/subscr_user_'.$id, $tpl->result['subscriptions']);
                        }
                    }else
                        $params['subscriptions'] = false;

                    /**
                     * Музыка
                     */
                    if($row['user_audio']  AND !$CheckBlackList AND $config['audio_mod'] == 'yes'){
                        $sql_audio = Profile::audio($id);
                        foreach($sql_audio as $key => $row_audio){
                            $sql_audio[$key]['stime'] = gmdate("i:s", $row_audio['duration']);
                            if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                            if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                            $sql_audio[$key]['search_artist'] = urlencode($row_audio['artist']);
                            $sql_audio[$key]['plname'] = 'audios'.$id;
                        }
                        $params['audios_num'] = $row['user_audio'].' '.Gramatic::declOfNum($row['user_audio'], $titles);
                        $params['audios'] = $sql_audio;
                    }else
                        $params['audios'] = false;

                    /**
                     * Праздники друзей
                     */
                    if($user_id == $id AND !$_SESSION['happy_friends_block_hide']  AND !$CheckBlackList){
                        $sql_happy_friends = Profile::happy_friends($id, $server_time);
//                        $tpl->load_template('/profile/profile_happy_friends.tpl');
                        $cnt_happfr = 0;
                        foreach($sql_happy_friends as $key => $happy_row_friends){
                            $cnt_happfr++;
                            $sql_happy_friends[$key]['user_id'] = $happy_row_friends['friend_id'];
                            $sql_happy_friends[$key]['name'] = $happy_row_friends['user_search_pref'];
                            $user_birthday = explode('-', $happy_row_friends['user_birthday']);
                            $sql_happy_friends[$key]['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);
                            if($happy_row_friends['user_photo']) {
                                $sql_happy_friends[$key]['ava'] = '/uploads/users/'.$happy_row_friends['friend_id'].'/100_'.$happy_row_friends['user_photo'];
                            }else{
                                $sql_happy_friends[$key]['ava'] = '/images/100_no_ava.png';
                            }
                        }
                    }

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
                            $cnt_rec = Profile::cnt_rec($id);
                            $row['user_wall_num'] = $cnt_rec['cnt'];
                            $params['wall_rec_num'] = $row['user_wall_num'];
                        }else
                            $params['wall_rec_num'] = $row['user_wall_num'];
                    }else
                        $params['wall_rec_num'] = $row['user_wall_num'];

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
                            $rid = intval($_GET['rid']);
                            $id = intval($_GET['uid']);
                            if(empty($id))
                                $id = $user_id;

                            $walluid = $id;
                            $params['title'] = $lang['wall_title'];

                            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
                            $gcount = 10;
                            $limit_page = ($page-1)*$gcount;
                            //not used row_user['user_privacy']
                            //$row_user = $db->super_query("SELECT user_name, user_wall_num, user_privacy FROM `users` WHERE user_id = '{$id}'");
                            $user_privacy = xfieldsdataload($row['user_privacy']);

                            if($row['user_wall_num'] > 0){
                                //ЧС
                                $CheckBlackList = Tools::CheckBlackList($id);
                                if(!$CheckBlackList){
                                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                                    //$CheckFriends
//                                    if($user_id != $id)
//                                        $check_friend = Tools::CheckFriends($id);


                                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $CheckFriends OR $user_id == $id)
                                        $cnt_rec['cnt'] = $row['user_wall_num'];
                                    else
                                        $cnt_rec = Profile::cnt_rec($id);

                                    /**
                                     * record_tab
                                     */
                                    if($_GET['type'] == 'own'){
                                        $params['record_tab'] = false;
                                        $cnt_rec = Profile::cnt_rec($id);
                                        $where_sql = "AND tb1.author_user_id = '{$id}'";
                                        $page_type = '/wall'.$id.'_sec=own&page=';
                                    } else if($_GET['type'] == 'record'){
                                        $params['record_tab'] = true;
                                        $where_sql = "AND tb1.id = '{$rid}'";
                                        $wallAuthorId = Profile::author_user_id($rid);
                                    } else {
                                        $params['record_tab'] = false;
                                        $_GET['type'] = '';
                                        $where_sql = '';
//                                        $tpl->set_block("'\\[record-tab\\](.*?)\\[/record-tab\\]'si","");
                                        $page_type = '/wall'.$id.'/page/';
                                    }

                                    //$titles = array('запись', 'записи', 'записей');//rec
//                                    if($cnt_rec['cnt'] > 0)
//                                        $user_speedbar = 'На стене '.$cnt_rec['cnt'].' '.Gramatic::declOfNum($cnt_rec['cnt'], $titles);

//                                    $tpl->load_template('wall/head.tpl');
                                    $params['wall_head']['name'] = Gramatic::gramatikName($row['user_name']);
                                    $params['wall_head']['uid'] = $id;
                                    $params['wall_head']['rec_id'] = $rid;
                                    $params['wall_head']['activetab_'.$_GET['type']] = 'activetab';

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

                        if(!$CheckBlackList){
                            if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $CheckFriends OR $user_id == $id)
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            elseif($wallAuthorId['author_user_id'] == $id)
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            else {
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);

                                if($wallAuthorId['author_user_id'])
                                    $Hacking = true;
                            }
                            //Если вызвана страница стены, не со страницы юзера
                            if(!$Hacking){
                                if($rid OR $walluid){
                                    //$tpl = $wall->template('wall/one_record.tpl', $tpl);
//                                    $tpl->load_template('wall/one_record.tpl');
                                    //$wall->compile('content');
                                    $compile = 'content';
                                    $params['compile'] = 'content';
                                    //$wall->select();

                                    if($cnt_rec['cnt'] > $gcount AND $_GET['type'] == '' OR $_GET['type'] == 'own'){
                                        //$tpl = Tools::navigation($gcount, $cnt_rec['cnt'], $page_type, $tpl);
                                        //bug !!!
                                    }
                                } else {
                                    //$wall->template('wall/record.tpl', $tpl);
//                                    $tpl->load_template('wall/one_record.tpl');
                                    //$wall->compile('wall');
                                    $compile = 'wall';
                                    $params['compile'] = 'wall';
                                    //$wall->select();
                                }

                                $server_time = intval($_SERVER['REQUEST_TIME']);
                                $config = Settings::loadsettings();

                                /**
                                 * wall records
                                 *
                                 * @var $query array
                                 */
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

                                                    $row_video = Profile::row_video($video_id);
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
                                                $row_audio = Profile::row_audio($audio_id);
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

                                                $row_doc = Profile::row_doc($doc_id);

                                                if($row_doc){

                                                    $attach_result .= '<div style="margin-top:5px;margin-bottom:5px" class="clear"><div class="doc_attach_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Файл <a href="/index.php?go=doc&act=download&did='.$doc_id.'" target="_blank" onMouseOver="myhtml.title(\''.$doc_id.$cnt_attach.$row_wall['id'].'\', \'<b>Размер файла: '.$row_doc['dsize'].'</b>\', \'doc_\')" id="doc_'.$doc_id.$cnt_attach.$row_wall['id'].'">'.$row_doc['dname'].'</a></div></div></div><div class="clear"></div>';

                                                    $cnt_attach++;
                                                }

                                                //Если опрос
                                            } elseif($attach_type[0] == 'vote'){

                                                $vote_id = intval($attach_type[1]);

                                                $row_vote = Profile::row_vote($vote_id);

                                                if($vote_id){

                                                    $checkMyVote = Profile::vote_check($vote_id, $user_id);

                                                    $row_vote['title'] = stripslashes($row_vote['title']);

                                                    if(!$row_wall['text'])
                                                        $row_wall['text'] = $row_vote['title'];

                                                    $arr_answe_list = explode('|', stripslashes($row_vote['answers']));
                                                    $max = $row_vote['answer_num'];

                                                    $sql_answer = Profile::vote_answer($vote_id);
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
                                            $rowUserTell = Profile::user_tell_info($row_wall['tell_uid'], 2);
                                        else
                                            $rowUserTell = Profile::user_tell_info($row_wall['tell_uid'], 1);

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

                                            $sql_comments = Profile::comments($row_wall['id'], $comments_limit);

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
                                    $params['wall_records'] = $query;
                                }
                            }else{
                                $params['wall_records'] = false;
                            }
                        }
                    }

                    //Общие друзья
                    if($row['user_friends_num'] AND $id != $user_info['user_id']  AND !$CheckBlackList){
                        $count_common = Profile::count_common($id, $user_info['user_id']);
                        if($count_common['cnt']){
                            $sql_mutual = Profile::mutual($id, $user_info['user_id']);
                            foreach($sql_mutual as $key => $row_mutual){
                                $friend_info_mutual = explode(' ', $row_mutual['user_search_pref']);
                                $sql_mutual[$key]['user_id'] = $row_mutual['friend_id'];
                                $sql_mutual[$key]['name'] = $friend_info_mutual[0];
                                $sql_mutual[$key]['last_name'] = $friend_info_mutual[1];
                                if($row_mutual['user_photo']){
                                    $sql_mutual[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_mutual['friend_id'].'/50_'.$row_mutual['user_photo'];
                                }else{
                                    $sql_mutual[$key]['ava'] = '/images/no_ava_50.png';
                                }
                            }
                            $params['mutual_friends'] = $sql_mutual;
                        }else
                            $params['mutual_friends'] = false;
                        $params['mutual_num'] = $count_common['cnt'];
                    }else{
                        $params['mutual_friends'] = false;
                    }

                    /**
                     * Загрузка самого профиля
                     */
//                    $tpl->load_template('/profile/profile.tpl');

                    $params['user_id'] = $row['user_id'];

                    //Страна и город
                    if($row['user_city'] AND $row['user_country']){
                        $params['city'] =$user_country_city_name_exp[1];
                        $params['city_id'] = $row['user_city'];
                        $params['not_all_city_block'] = true;
                    } else{
                        $params['not_all_city_block'] = false;
                    }

                    if($row['user_country']){
                        $params['country'] = $user_country_city_name_exp[0];
                        $params['country_id'] =$row['user_country'];
                        $params['not_all_country_block'] = true;
                    } else{
                        $params['not_all_country_block'] = false;
                    }

                    //Если человек сидит с мобильнйо версии
                    if($row_online['user_logged_mobile'])
                        $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online"  alt=\"\" />';
                    else
                        $mobile_icon = '';

                    if($row_online['user_last_visit'] >= $online_time){
                        $params['online'] = $lang['online'].$mobile_icon;
                    }else {
                        if(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $row_online['user_last_visit']);
                        elseif(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $row_online['user_last_visit']);
                        else
                            $dateTell = langdate('j F Y в H:i', $row_online['user_last_visit']);
                        if($row['user_sex'] == 2){
//                            $tpl->set('{online}', 'последний раз была '.$dateTell.$mobile_icon);
                            $params['online'] = 'последний раз была '.$dateTell.$mobile_icon;
                        }else{
//                            $tpl->set('{online}', 'последний раз был '.$dateTell.$mobile_icon);
                            $params['online'] = 'последний раз был '.$dateTell.$mobile_icon;
                        }
                    }

                    //Конакты
                    $xfields = xfieldsdataload($row['user_xfields']);
                    $preg_safq_name_exp = explode(', ', 'phone, vk, od, skype, fb, icq, site');
                    foreach($preg_safq_name_exp as $preg_safq_name){
                        if($xfields[$preg_safq_name]){
                            $params['not_contact'.$preg_safq_name.'_block'] = true;
                        } else{
                            $params['not_contact'.$preg_safq_name.'_block'] = false;
                        }
                    }
                    $params['vk'] = '<a href="'.stripslashes($xfields['vk']).'" target="_blank">'.stripslashes($xfields['vk']).'</a>';
                    $params['od'] = '<a href="'.stripslashes($xfields['od']).'" target="_blank">'.stripslashes($xfields['od']).'</a>';
                    $params['fb'] = '<a href="'.stripslashes($xfields['fb']).'" target="_blank">'.stripslashes($xfields['fb']).'</a>';
                    $params['skype'] = stripslashes($xfields['skype']);
                    $params['icq'] = stripslashes($xfields['icq']);
                    $params['phone'] = stripslashes($xfields['phone']);

                    if (!empty($xfields['site'])){
                        if(preg_match('/https:\/\//i', $xfields['site'])) {
                            if (preg_match('/\.ru|\.com|\.net|\.su|\.in\.ua|\.ua/i', $xfields['site'])) {
//                                $tpl->set('{site}', '<a href="' . stripslashes($xfields['site']) . '" target="_blank">' . stripslashes($xfields['site']) . '</a>');
                                $params['phone'] = '<a href="' . stripslashes($xfields['site']) . '" target="_blank">' . stripslashes($xfields['site']) . '</a>';
                            } else {
//                                $tpl->set('{site}', stripslashes($xfields['site']));
                                $params['site'] = stripslashes($xfields['site']);
                            }
                        }else{
//                            $tpl->set('{site}', 'https://'.stripslashes($xfields['site']));
                            $params['site'] = 'https://'.stripslashes($xfields['site']);
                        }
                    }else{
//                        $tpl->set('{site}', '');
                        $params['site'] = '';
                    }

                    if(empty($xfields['vk']) && empty($xfields['od']) && empty($xfields['fb'])
                        && empty($xfields['skype']) && empty($xfields['icq'])
                        && empty($xfields['phone']) && empty($xfields['site'])){
                        $params['not_block_contact'] = false;
                    }else {
                        $params['not_block_contact'] = true;
                    }

                    //Интересы
                    $xfields_all = xfieldsdataload($row['user_xfields_all']);
                    $preg_safq_name_exp = explode(', ', 'activity, interests, myinfo, music, kino, books, games, quote');

                    if(empty($xfields_all['activity']) AND empty($xfields_all['interests'])
                        AND empty($xfields_all['myinfo']) AND empty($xfields_all['music'])
                        AND empty($xfields_all['kino']) AND empty($xfields_all['books'])
                        AND empty($xfields_all['games']) AND empty($xfields_all['quote'])){
                        $params['not_block_info'] = '<div style="color:#999;">Информация отсутствует.</div>';
                    }else{
                        $params['not_block_info'] = '';
                    }

                    foreach($preg_safq_name_exp as $preg_safq_name){
                        if(!empty($xfields_all[$preg_safq_name])){
//                            $tpl->set("[not-info-{$preg_safq_name}]", '');
//                            $tpl->set("[/not-info-{$preg_safq_name}]", '');
                            $params['not_info_'.$preg_safq_name.'_block'] = true;
                        } else{
//                            $tpl->set_block("'\\[not-info-{$preg_safq_name}\\](.*?)\\[/not-info-{$preg_safq_name}\\]'si","");
                            $params['not_info_'.$preg_safq_name.'_block'] = false;
                        }
                    }

//                    $tpl->set('{activity}', nl2br(stripslashes($xfields_all['activity'])));
//                    $params['activity'] = nl2br(stripslashes($xfields_all['activity']));
//                    $tpl->set('{interests}', nl2br(stripslashes($xfields_all['interests'])));
                    if (!empty($xfields_all['myinfo'])){
//                        $tpl->set('{myinfo}', nl2br(stripslashes($xfields_all['myinfo'])));
                        $params['myinfo'] = nl2br(stripslashes($xfields_all['myinfo']));
                    }else{
//                        $tpl->set('{myinfo}', '');
                        $params['myinfo'] = '';
                    }
//                    $tpl->set('{music}', nl2br(stripslashes($xfields_all['music'])));
//                    $tpl->set('{kino}', nl2br(stripslashes($xfields_all['kino'])));
//                    $tpl->set('{books}', nl2br(stripslashes($xfields_all['books'])));
//                    $tpl->set('{games}', nl2br(stripslashes($xfields_all['games'])));
//                    $tpl->set('{quote}', nl2br(stripslashes($xfields_all['quote'])));
//                    $params['quote'] = nl2br(stripslashes($xfields_all['quote']));
//                    $tpl->set('{name}', $user_name_lastname_exp[0]);
                    $params['name'] = $user_name_lastname_exp[0];
//                    $tpl->set('{lastname}', $user_name_lastname_exp[1]);
                    $params['lastname'] = $user_name_lastname_exp[1];

                    //День рождение
                    $user_birthday = explode('-', $row['user_birthday']);
                    $row['user_day'] = $user_birthday[2];
                    $row['user_month'] = $user_birthday[1];
                    $row['user_year'] = $user_birthday[0];
                    if($row['user_day'] > 0 && $row['user_day'] <= 31 && $row['user_month'] > 0 && $row['user_month'] < 13){
                        $params['not_all_birthday_block_block'] = true;
                        if($row['user_day'] && $row['user_month'] && $row['user_year'] > 1929 && $row['user_year'] < 2012 ){
//                            $tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'&year='.$row['user_year'].'" onClick="Page.Go(this.href); return false">'.langdate('j F Y', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).' г.</a>');
                            $params['birth_day'] = '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'&year='.$row['user_year'].'" onClick="Page.Go(this.href); return false">'.langdate('j F Y', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).' г.</a>';
                        }else{
                            $params['birth_day'] = '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'" onClick="Page.Go(this.href); return false">'.langdate('j F', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).'</a>';
                        }
                    } else {
                        $params['not_all_birthday_block'] = false;
                    }

                    //Показ скрытых текста только для владельца страницы
                    if($user_info['user_id'] == $row['user_id']){
                        $params['owner'] = true;
                        $params['not_owner'] = false;
                    } else {
                        $params['owner'] = false;
                        $params['not_owner'] = true;
                    }

                    // FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile'){
                        $avaPREFver = '50_';
                        $noAvaPrf = 'no_ava_50.png';
                    } else {
                        $avaPREFver = '';
                        $noAvaPrf = 'no_ava.gif';
                    }

                    /**
                     * Аватарка
                     */
                    if($row['user_photo']){
//                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/'.$avaPREFver.$row['user_photo']);
                        $params['ava'] = $config['home_url'].'uploads/users/'.$row['user_id'].'/'.$avaPREFver.$row['user_photo'];
//                        $tpl->set('{display-ava}', 'style="display:block;"');
                        $params['display_ava'] = 'style="display:block;"';
                    } else {
//                        $tpl->set('{ava}', '/images/'.$noAvaPrf);
                        $params['ava'] = '/images/'.$noAvaPrf;
//                        $tpl->set('{display-ava}', 'style="display:none;"');
                        $params['display_ava'] = 'style="display:none;"';
                    }

                    /**
                     * Альбомы
                     */
                    if($user_id == $id){
                        $albums_privacy = false;
                        $albums_count['cnt'] = $row['user_albums_num'];
                    } else if($CheckFriends){
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) regexp '[[:<:]](1|2)[[:>:]]'";
                        $albums_count = Profile::albums_count($id, $albums_privacy, 1);
                        $cache_pref = "_friends";
                    } else {
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) = 1";
                        $albums_count = Profile::albums_count($id, $albums_privacy, 2);
                        $cache_pref = "_all";
                    }

                    if (!isset($cache_pref))
                        $cache_pref = null;

                    $sql_albums = Profile::row_albums($id, $albums_privacy, $cache_pref);//cache_pref undefined
                    if($sql_albums AND $config['album_mod'] == 'yes'){
                        foreach($sql_albums as $key => $row_albums){
                            $sql_albums[$key]['name'] = stripslashes($row_albums['name']);
                            $sql_albums[$key]['date'] = megaDate($row_albums['adate']);
                            $titles = array('фотография', 'фотографии', 'фотографий');//photos
                            $sql_albums[$key]['albums_photonums'] = Gramatic::declOfNum($row_albums['photo_num'], $titles);
                            if($row_albums['cover'])
                                $sql_albums[$key]['album_cover'] = "/uploads/users/{$id}/albums/{$row_albums['aid']}/c_{$row_albums['cover']}";
                            else
                                $sql_albums[$key]['album_cover'] = '/images/no_cover.png';
                        }
                        $params['albums_num'] = $albums_count['cnt'];
                        $params['albums'] = $sql_albums;
                    }else
                        $params['albums'] = false;



                    //Делаем проверки на существования запрашиваемого юзера у себя в друзьяз, заклаках, в подписка, делаем всё это если страницу смотрет другой человек
                    if($user_id != $id){

                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        if($CheckFriends == true){
                            $params['yes_friends_block'] = true;
                            $params['no_friends_block'] = false;
                        } else {
                            $params['yes_friends_block'] = false;
                            $params['no_friends_block'] = true;
                        }

                        //Проверка естьли запрашиваемый юзер в закладках у юзера который смотрит стр
                        $check_fave = Profile::check_fave($id, $user_info['user_id']);
                        if($check_fave){
                            $params['yes_fave_block'] = true;
                            $params['no_fave_block'] = false;
                        } else {
                            $params['yes_fave_block'] = false;
                            $params['no_fave_block'] = true;
                        }

                        //Проверка естьли запрашиваемый юзер в подписках у юзера который смотрит стр
                        $check_subscr = Profile::check_subscr($id, $user_info['user_id']);
                        if($check_subscr){
                            $params['yes_subscription_block'] = false;
                            $params['no_subscription_block'] = true;
                        } else {
                            $params['yes_subscription_block'] = true;
                            $params['no_subscription_block'] = false;
                        }

                        //Проверка естьли запрашиваемый юзер в черном списке
                        $MyCheckBlackList = Tools::MyCheckBlackList($id);
                        if($MyCheckBlackList){
                            $params['yes_blacklist_block'] = true;
                            $params['no_blacklist_block'] = false;
                        } else {
                            $params['yes-blacklist_block'] = false;
                            $params['no_blacklist_block'] = true;
                        }

                    }

                    $author_info = explode(' ', $row['user_search_pref']);
                    $params['gram_name'] = Gramatic::gramatikName($author_info[0]);


                    //Если человек пришел после реги, то открываем ему окно загрузи фотографии
//                    if(intval($_GET['after'])){
//                        $tpl->set('[after-reg]', '');
//                        $tpl->set('[/after-reg]', '');
//                    } else
//                        $tpl->set_block("'\\[after-reg\\](.*?)\\[/after-reg\\]'si","");

                    //Стена
//                    $tpl->set('{records}', $tpl->result['wall']);
//                    $params['records'] = $tpl->result['wall'];


                    //Статус
//                    $tpl->set('{status-text}', stripslashes($row['user_status']));

                    if (!$CheckBlackList AND $params['not_owner']){
                        $params['status_text'] = stripslashes($row['user_status']);

                    }elseif($params['owner']){
                        $params['status_text'] = '<div><a href="/" id="new_status" onClick="gStatus.open(); return false">'.stripslashes($row['user_status'].'</a></div>');

                    }

                    if($row['user_status']){
                        $params['status_block'] = 'class="no_display"';
                        $params['status_block2'] = '<div class="button_div_gray fl_r status_but margin_left"><button>Отмена</button></div>';
                    }
                    //Приватность сообщений
                    if($user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $CheckFriends AND !$CheckBlackList){
                        $params['privacy_msg'] = '<a href="#" onClick="messages.new_('.$params['user_id'].'); return false">
                                        <svg class="bi bi-envelope" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M14 3H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2z"/>
                                            <path d="M.05 3.555C.017 3.698 0 3.847 0 4v.697l5.803 3.546L0 11.801V12c0 .306.069.596.192.856l6.57-4.027L8 9.586l1.239-.757 6.57 4.027c.122-.26.191-.55.191-.856v-.2l-5.803-3.557L16 4.697V4c0-.153-.017-.302-.05-.445L8 8.414.05 3.555z"/>
                                        </svg>
                                        <span>Написать сообщение</span></a>';
                    } else{
                        $params['privacy_msg'] = '';
                    }

                    //Приватность информации
                    if($user_privacy['val_info'] == 1 OR $user_privacy['val_info'] == 2 AND $CheckFriends OR $user_id == $id){
                        $params['privacy_info_block'] = true;
                    } else{
                        $params['privacy_info_block'] = false;
                    }

                    //Семейное положение
                    $user_sp = explode('|', $row['user_sp']);
                    if(isset($user_sp['1'])){
                        $rowSpUserName = Profile::user_sp($user_sp['1']);
                        if($row['user_sex'] == 1) $check_sex = 2;
                        if($row['user_sex'] == 2) $check_sex = 1;
                        if($rowSpUserName['user_sp'] == $user_sp[0].'|'.$id OR $user_sp[0] == 5 AND $rowSpUserName['user_sex'] == $check_sex){
                            $spExpName = explode(' ', $rowSpUserName['user_search_pref']);
                            $spUserName = $spExpName[0].' '.$spExpName[1];
                        }
                    }
                    if($row['user_sex'] == 1){
                        $sp1 = '<a href="/search/?sp=1" onClick="Page.Go(this.href); return false">не женат</a>';
                        $sp2 = "подруга <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp2_2 = '<a href="/search/?sp=2" onClick="Page.Go(this.href); return false">есть подруга</a>';
                        $sp3 = "невеста <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp3_3 = '<a href="/search/?sp=3" onClick="Page.Go(this.href); return false">помовлен</a>';
                        $sp4 = "жена <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp4_4 = '<a href="/search/?sp=4" onClick="Page.Go(this.href); return false">женат</a>';
                        $sp5 = "любимая <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp5_5 = '<a href="/search/?sp=5" onClick="Page.Go(this.href); return false">влюблён</a>';
                    }
                    if($row['user_sex'] == 2){
                        $sp1 = '<a href="/search/?sp=1" onClick="Page.Go(this.href); return false">не замужем</a>';
                        $sp2 = "друг <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp2_2 = '<a href="/search/?sp=2" onClick="Page.Go(this.href); return false">есть друг</a>';
                        $sp3 = "жених <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp3_3 = '<a href="/search/?sp=3" onClick="Page.Go(this.href); return false">помовлена</a>';
                        $sp4 = "муж <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp4_4 = '<a href="/search/?sp=4" onClick="Page.Go(this.href); return false">замужем</a>';
                        $sp5 = "любимый <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp5_5 = '<a href="/search/?sp=5" onClick="Page.Go(this.href); return false">влюблена</a>';
                    }
                    if ( !isset($spUserName) )
                        $spUserName = 'erorr';//bug

                    if (!isset($user_sp['1']))
                        $user_sp['1'] = null; //bug: undefined

                    $sp6 = "партнёр <a href=\"/u".$user_sp['1']."\" onClick=\"Page.Go(this.href); return false\">".$spUserName."</a>";
                    $sp6_6 = '<a href="/search/?sp=6" onClick="Page.Go(this.href); return false">всё сложно</a>';

                    if($user_sp[0] == 1){
//                        $tpl->set('{sp}', $sp1);
                        $params['sp'] = $sp1;
                    }
                    else if($user_sp[0] == 2)
                        if($spUserName){
//                            $tpl->set('{sp}', $sp2);
                            $params['sp'] = $sp2;
                        }else{
//                            $tpl->set('{sp}', $sp2_2);
                            $params['sp'] = $sp2_2;
                        }
                    else if($user_sp[0] == 3)
                        if($spUserName){
//                            $tpl->set('{sp}', $sp3);
                            $params['sp'] = $sp3;
                        }else{
//                            $tpl->set('{sp}', $sp3_3);
                            $params['sp'] = $sp3_3;
                        }
                    else if($user_sp[0] == 4)
                        if($spUserName){
//                            $tpl->set('{sp}', $sp4);
                            $params['sp'] = $sp4;
                        }else{
//                            $tpl->set('{sp}', $sp4_4);
                            $params['sp'] = $sp4_4;
                        }
                    else if($user_sp[0] == 5)
                        if($spUserName){
//                            $tpl->set('{sp}', $sp5);
                            $params['sp'] = $sp5;
                        }else {
//                            $tpl->set('{sp}', $sp5_5);
                            $params['sp'] = $sp5_5;
                        }
                    else if($user_sp[0] == 6)
                        if($spUserName) {
//                            $tpl->set('{sp}', $sp6);
                            $params['sp'] = $sp6;
                        }else{
//                            $tpl->set('{sp}', $sp6_6);
                            $params['sp'] = $sp6_6;
                        }
                    else if($user_sp[0] == 7){
//                        $tpl->set('{sp}', '<a href="/search/?sp=7" onClick="Page.Go(this.href); return false">в активном поиске</a>');
                        $params['sp'] = '<a href="/search/?sp=7" onClick="Page.Go(this.href); return false">в активном поиске</a>';
                    }else{
                        $params['sp'] = false;
                    }

                    //ЧС
                    if(!$CheckBlackList){
                        $params['blacklist_block'] = true;
                        $params['not-blacklist_block'] = false;
                    } else {
                        $params['blacklist_block'] = false;
                        $params['not_blacklist_block'] = true;
                    }

                    //################### Подарки ###################//
                    if($row['user_gifts'] > 0 AND !$CheckBlackList){
                        $sql_gifts = Profile::gifts($id);
                        $titles = array('подарок', 'подарка', 'подарков');//gifts
                        $params['gifts_num'] = $row['user_gifts'].' '.Gramatic::declOfNum($row['user_gifts'], $titles);
                        $params['gifts'] = $sql_gifts;
                    } else{
                        $params['gifts'] = false;
                    }

                    /**
                     * Сообщества
                     */
                    if($row['user_public_num'] > 0  AND !$CheckBlackList){
                        $sql_groups = Profile::groups($id);
                        foreach($sql_groups as $row_groups){
                            if($row_groups['adres']) {
//                                $adres = $row_groups['adres'];
                                $sql_groups['adres'] = $row_groups['adres'];
                            }else{
//                                $adres = 'public'.$row_groups['id'];
                                $sql_groups['adres'] = 'public'.$row_groups['id'];
                            }
                            if($row_groups['photo']){
                                $ava_groups = "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}";
                                $sql_groups['ava'] = "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}";
                            }else{
                                $ava_groups = "/images/no_ava_50.png";
                                $sql_groups['ava'] = "/images/no_ava_50.png";
                            }
                            $row_groups['info'] = iconv_substr($row_groups['status_text'], 0, 24, 'utf-8');
//                            $groups .= '<div class="onesubscription onesubscriptio2n cursor_pointer" onClick="Page.Go(\'/'.$adres.'\')"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false"><img src="'.$ava_groups.'" /></a><div class="onesubscriptiontitle"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false">'.stripslashes($row_groups['title']).'</a></div><span class="color777 size10">'.stripslashes($row_groups['status_text']).'</span></div>';
                        }
                        $params['groups'] = $sql_groups;
                        $params['groups_num'] = $row['user_public_num'];
                    } else{
                        $params['groups'] = false;
                    }

                    /**
                     * Праздники друзей
                     */
                    if (!isset($cnt_happfr))
                        $cnt_happfr = null;

                    if($cnt_happfr AND $params['owner'] == true){
                        $params['happy-friends'] = $tpl->result['happy_all_friends'];
                        $params['happy-friends-num'] = $cnt_happfr;
                        $params['happy_friends_block'] = true;
                    } else{
                        $params['happy_friends_block'] = false;
                    }

                    //################### Обработка дополнительных полей ###################//
                    $xfieldsdata = xfieldsdataload($row['xfields']);
                    $xfields = profileload();

                    foreach($xfields as $value){
                        $preg_safe_name = preg_quote($value[0], "'");
                        if(empty($xfieldsdata[$value[0]])){
                            $tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}](.*?)\\[/xfgiven_{$preg_safe_name}]'is", "", $tpl->copy_template);
                        } else {
                            $tpl->copy_template = str_replace("[xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);
                            $tpl->copy_template = str_replace("[/xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);
                        }
                        $tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}]'i", stripslashes($xfieldsdata[$value[0]]), $tpl->copy_template);
                    }

                    //what? (deprecated)
//                    if($id == 7) $tpl->set('{group}', '<font color="#f87d7d">Модератор</font>');
                    //else $tpl->set('{group}', '');

                    //Rating
                    if($row['user_rating'] > 1000){
                        $params['rating_class_left'] = 'profile_rate_1000_left';
                        $params['rating_class_right'] = 'profile_rate_1000_right';
                        $params['rating_class_head'] = 'profile_rate_1000_head';
                    } elseif($row['user_rating'] > 500){
                        $params['rating_class_left'] = 'profile_rate_500_left';
                        $params['rating_class_right'] = 'profile_rate_500_right';
                        $params['rating_class_head'] = 'profile_rate_500_head';
                    } else {
                        $params['rating_class_left'] = '';
                        $params['rating_class_right'] = '';
                        $params['rating_class_head'] = '';
                    }

                    if(!$row['user_rating'])
                        $row['user_rating'] = 0;

//                    $tpl->set('{rating}', $row['user_rating']);
                    $params['rating'] = $row['user_rating'];
//                    $tpl->compile('content');

                    //Обновляем кол-во посищений на страницу, если юзер есть у меня в друзьях
                    if($CheckFriends == true)
                        Profile::friend_visit($id, $user_info['user_id']);

                    //Вставляем в статистику
                    //!NB optimize generate users stat
                    if($user_info['user_id'] != $id){

                        /**
                         * StatsUser::add($id, $user_info['user_id']);
                         * Cron Generate stats
                         */

                        //start old
                        $stat_date = date('Ymd', $server_time);
                        $stat_x_date = date('Ym', $server_time);

                        $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats_log` WHERE user_id = '{$user_info['user_id']}' AND for_user_id = '{$id}' AND date = '{$stat_date}'");

                        if(!$check_user_stat['cnt']){
                            $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats` WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            if($check_stat['cnt'])
                                $db->query("UPDATE `users_stats` SET users = users + 1, views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            else
                                $db->query("INSERT INTO `users_stats` SET user_id = '{$id}', date = '{$stat_date}', users = '1', views = '1', date_x = '{$stat_x_date}'");
                            $db->query("INSERT INTO `users_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', for_user_id = '{$id}'");
                        } else {
                            $db->query("UPDATE `users_stats` SET views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                        }
                        //end old
                    }

                    return view('profile.profile', $params);
                }

            } else {
                $params['title'] = $lang['no_infooo'];
                $params['info'] = $lang['no_upage'];
                return view('info.info', $params);
            }
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }

    }

    /**
     * 
     */
    public static function ban()
    {
        $tpl = new Templates();
        $config = Settings::loadsettings();
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $user_info = Registry::get('user_info');
        if($user_info['user_group'] != '1'){
//            $tpl->load_template('profile/profile_baned.tpl');
            if($user_info['user_ban_date']){
//                $tpl->set('{date}', langdate('j F Y в H:i', $user_info['user_ban_date']));
            }else{
//                $tpl->set('{date}', 'Неограниченно');
            }
//            $tpl->compile('main');
//            echo $tpl->result['main'];
        }

        return die();
    }

    /**
     *
     */
    public static function delete()
    {
        $tpl = new Templates();
        $config = Settings::loadsettings();
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $user_info = Registry::get('user_info');
        if($user_info['user_group'] != '1'){
//            $tpl->load_template('profile_deleted.tpl');
//            $tpl->compile('main');
            //echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
//            echo $tpl->result['main'];
        }

        return die();
    }
}
