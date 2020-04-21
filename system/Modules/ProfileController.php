<?php
/* 
	Appointment: Просмотр страницы пользователей
	File: profile.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/
namespace System\Modules;

use System\Classes\Db;
use System\Classes\Wall;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Cache;
use System\Libs\Gramatic;

class ProfileController extends Module{

    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();

        $db = Db::getDB();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        $user_id = $user_info['user_id'];

        if($logged){

            $config = include __DIR__.'/../data/config.php';

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $id = str_replace('u', '', $path);
            $id = intval($id['1']);


//            $id = intval($_GET['id']);
            $cache_folder = 'user_'.$id;

            //Читаем кеш
            $row = unserialize(Cache::mozg_cache($cache_folder.'/profile_'.$id));

//            var_dump($row);
            //Проверяем на наличие кеша, если нету то выводи из БД и создаём его
            if(!$row){
                $row = $db->super_query("SELECT user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
                if($row){
                    Cache::mozg_create_folder_cache($cache_folder);
                    Cache::mozg_create_cache($cache_folder.'/profile_'.$id, serialize($row));
                }
                $row_online['user_last_visit'] = $row['user_last_visit'];
                $row_online['user_logged_mobile'] = $row['user_logged_mobile'];
            } else{
                $row_online = $db->super_query("SELECT user_last_visit, user_logged_mobile FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
            }

            //Если есть такой, юзер то продолжаем выполнение скрипта
            if($row){
                $mobile_speedbar = $row['user_search_pref'];
                $user_speedbar = $row['user_search_pref'];
                $metatags['title'] = $row['user_search_pref'];

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Если удалена
                if($row['user_delet']){
                    $tpl->load_template("/profile/profile_delete_all.tpl");
                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    $tpl->set('{name}', $user_name_lastname_exp[0]);
                    $tpl->set('{lastname}', $user_name_lastname_exp[1]);
                    $tpl->compile('content');
                    //Если заблокирована
                } elseif($row['user_ban_date'] >= $server_time OR $row['user_ban_date'] == '0'){
                    $tpl->load_template("/profile/profile_baned_all.tpl");
                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    $tpl->set('{name}', $user_name_lastname_exp[0]);
                    $tpl->set('{lastname}', $user_name_lastname_exp[1]);
                    $tpl->compile('content');
                    //Если все хорошо, то выводим дальше
                } else {
                    $CheckBlackList = Tools::CheckBlackList($id);

                    $user_privacy = xfieldsdataload($row['user_privacy']);

                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    $user_country_city_name_exp = explode('|', $row['user_country_city_name']);

                    //################### Друзья ###################//
                    if($row['user_friends_num']){
                        $sql_friends = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
                        $tpl->load_template('/profile/profile_friends.tpl');

                        foreach($sql_friends as $row_friends){
                            $friend_info = explode(' ', $row_friends['user_search_pref']);
                            $tpl->set('{user-id}', $row_friends['friend_id']);
                            $tpl->set('{name}', $friend_info[0]);
                            $tpl->set('{last-name}', $friend_info[1]);
                            if($row_friends['user_photo'])
                                $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_friends['friend_id'].'/50_'.$row_friends['user_photo']);
                            else
                                $tpl->set('{ava}', '/images/no_ava_50.png');
                            $tpl->compile('all_friends');
                        }
                    }

                    //################### Друзья на сайте ###################//
                    if($user_id != $id)
                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        $check_friend = Tools::CheckFriends($row['user_id']);

                    //Кол-во друзей в онлайне
                    if($row['user_friends_num']){
                        $online_friends = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` tb1, `".PREFIX."_friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}' AND subscriptions = 0");

                        //Если друзья на сайте есть то идем дальше
                        if($online_friends['cnt']){
                            $sql_friends_online = $db->super_query("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `".PREFIX."_users` tb1, `".PREFIX."_friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}'  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
                            $tpl->load_template('/profile/profile_friends.tpl');
                            foreach($sql_friends_online as $row_friends_online){
                                $friend_info_online = explode(' ', $row_friends_online['user_search_pref']);
                                $tpl->set('{user-id}', $row_friends_online['user_id']);
                                $tpl->set('{name}', $friend_info_online[0]);
                                $tpl->set('{last-name}', $friend_info_online[1]);
                                if($row_friends_online['user_photo'])
                                    $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_friends_online['user_id'].'/50_'.$row_friends_online['user_photo']);
                                else
                                    $tpl->set('{ava}', '/images/no_ava_50.png');
                                $tpl->compile('all_online_friends');
                            }
                        }
                    }

                    //################### Заметки ###################//
                    if($row['user_notes_num']){
                        $tpl->result['notes'] = Cache::mozg_cache($cache_folder.'/notes_user_'.$id);
                        if(!$tpl->result['notes']){
                            $sql_notes = $db->super_query("SELECT id, title, date, comm_num FROM `".PREFIX."_notes` WHERE owner_user_id = '{$id}' ORDER by `date` DESC LIMIT 0,5", 1);
                            $tpl->load_template('/profile/profile_note.tpl');
                            foreach($sql_notes as $row_notes){
                                $tpl->set('{id}', $row_notes['id']);
                                $tpl->set('{title}', stripslashes($row_notes['title']));
                                $tpl->set('{comm-num}', $row_notes['comm_num'].' '.Gramatic::gram_record($row_notes['comm_num'], 'comments'));
                                $date = megaDate(strtotime($row_notes['date']), 'no_year');
                                $tpl->set('{date}', $date);
                                $tpl->compile('notes');
                            }
                            Cache::mozg_create_cache($cache_folder.'/notes_user_'.$id, $tpl->result['notes']);
                        }
                    }

                    //################### Видеозаписи ###################//
                    if($row['user_videos_num']){
                        //Настройки приватности
                        if($user_id == $id)
                            $sql_privacy = "";
                        elseif($check_friend){
                            $sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
                            $cache_pref_videos = "_friends";
                        } else {
                            $sql_privacy = "AND privacy = 1";
                            $cache_pref_videos = "_all";
                        }

                        //Если страницу смотрит другой юзер, то считаем кол-во видео
                        if($user_id != $id){
                            $video_cnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0'", false, "user_{$id}/videos_num{$cache_pref_videos}");
                            $row['user_videos_num'] = $video_cnt['cnt'];
                        }

                        $sql_videos = $db->super_query("SELECT id, title, add_date, comm_num, photo FROM `".PREFIX."_videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2", 1, "user_{$id}/page_videos_user{$cache_pref_videos}");

                        $tpl->load_template('/profile/profile_video.tpl');
                        foreach($sql_videos as $row_videos){
                            $tpl->set('{photo}', $row_videos['photo']);
                            $tpl->set('{id}', $row_videos['id']);
                            $tpl->set('{user-id}', $id);
                            $tpl->set('{title}', stripslashes($row_videos['title']));
                            $titles = array('комментарий', 'комментария', 'комментариев');//comments
                            $tpl->set('{comm-num}', $row_videos['comm_num'].' '.Gramatic::declOfNum($row_videos['comm_num'], $titles));
                            $date = megaDate(strtotime($row_videos['add_date']), '');
                            $tpl->set('{date}', $date);
                            $tpl->compile('videos');
                        }
                    }

                    //################### Подписки ###################//
                    if($row['user_subscriptions_num']){
                        $tpl->result['subscriptions'] = Cache::mozg_cache('/subscr_user_'.$id);
                        if(!$tpl->result['subscriptions']){
                            $sql_subscriptions = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id AND  	tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5", 1);
                            $tpl->load_template('/profile/profile_subscription.tpl');
                            foreach($sql_subscriptions as $row_subscr){
                                $tpl->set('{user-id}', $row_subscr['friend_id']);
                                $tpl->set('{name}', $row_subscr['user_search_pref']);

                                if($row_subscr['user_status'])
                                    $tpl->set('{info}', stripslashes(iconv_substr($row_subscr['user_status'], 0, 24, 'utf-8')));
                                else {
                                    $country_city = explode('|', $row_subscr['user_country_city_name']);
                                    $tpl->set('{info}', $country_city[1]);
                                }

                                if($row_subscr['user_photo'])
                                    $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_subscr['friend_id'].'/50_'.$row_subscr['user_photo']);
                                else
                                    $tpl->set('{ava}', '/images/no_ava_50.png');
                                $tpl->compile('subscriptions');
                            }
                            Cache::mozg_create_cache('/subscr_user_'.$id, $tpl->result['subscriptions']);
                        }
                    }

                    //################### Музыка ###################//
                    if($row['user_audio']){
                        $sql_audio = $db->super_query("SELECT id, url, artist, title, duration FROM `".PREFIX."_audio` WHERE oid = '{$id}' and public = '0' ORDER by `id` DESC LIMIT 0, 3", 1);
                        foreach($sql_audio as $row_audio){
                            $stime = gmdate("i:s", $row_audio['duration']);
                            if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                            if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                            $search_artist = urlencode($row_audio['artist']);
                            $plname = 'audios'.$id;
                            $tpl->result['audios'] .= <<<HTML
                            <div class="audioPage audioElem" id="audio_{$row_audio['id']}_{$id}_{$plname}"
                            onclick="playNewAudio('{$row_audio['id']}_{$id}_{$plname}', event);">
                            <div class="area">
                            <table cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                            <tr>
                            <td>
                            <div class="audioPlayBut new_play_btn"><div class="bl"><div class="figure"></div></div></div>
                            <input type="hidden" value="{$row_audio['url']},{$row_audio['duration']},page"
                            id="audio_url_{$row_audio['id']}_{$id}_{$plname}">
                            </td>
                            <td class="info">
                            <div class="audioNames"><b class="author" onclick="Page.Go('/?go=search&query={$search_artist}&type=5&n=1'); return false;"
                            id="artist">{$row_audio['artist']}</b> – <span class="name"
                            id="name">{$row_audio['title']}</span> <div class="clear"></div></div>
                            <div class="audioElTime" id="audio_time_{$row_audio['id']}_{$id}_{$plname}">{$stime}</div>
                            </td>
                            </tr>
                            </tbody>
                            </table>
                            <div id="player{$row_audio['id']}_{$id}_{$plname}" class="audioPlayer" border="0"
                            cellpadding="0">
                            <table cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                            <tr>
                            <td style="width: 100%;">
                            <div class="progressBar fl_l" style="width: 100%;" onclick="cancelEvent(event);"
                            onmousedown="audio_player.progressDown(event, this);" id="no_play"
                            onmousemove="audio_player.playerPrMove(event, this)"
                            onmouseout="audio_player.playerPrOut()">
                            <div class="audioTimesAP" id="main_timeView"><div
                            class="audioTAP_strlka">100%</div></div>
                            <div class="audioBGProgress"></div>
                            <div class="audioLoadProgress"></div>
                            <div class="audioPlayProgress" id="playerPlayLine"><div class="audioSlider"></div></div>
                            </div>
                            </td>
                            <td>
                            <div class="audioVolumeBar fl_l ml-2" onclick="cancelEvent(event);"
                            onmousedown="audio_player.volumeDown(event, this);" id="no_play">
                            <div class="audioTimesAP"><div class="audioTAP_strlka">100%</div></div>
                            <div class="audioBGProgress"></div>
                            <div class="audioPlayProgress" id="playerVolumeBar"><div class="audioSlider"></div></div>
                            </div>
                            </td>
                            </tr>
                            </tbody>
                            </table>
                            </div>
                            </div>
                            </div>
                            HTML;
                        }
                    }

                    //################### Праздники друзей ###################//
                    if($user_id == $id AND !$_SESSION['happy_friends_block_hide']){
                        $sql_happy_friends = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '".$id."' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '".date('j', $server_time)."' AND user_month = '".date('n', $server_time)."' ORDER by `user_last_visit` DESC LIMIT 0, 50", 1);
                        $tpl->load_template('/profile/profile_happy_friends.tpl');
                        $cnt_happfr = 0;
                        foreach($sql_happy_friends as $happy_row_friends){
                            $cnt_happfr++;
                            $tpl->set('{user-id}', $happy_row_friends['friend_id']);
                            $tpl->set('{user-name}', $happy_row_friends['user_search_pref']);
                            $user_birthday = explode('-', $happy_row_friends['user_birthday']);
                            $tpl->set('{user-age}', user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]));
                            if($happy_row_friends['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$happy_row_friends['friend_id'].'/100_'.$happy_row_friends['user_photo']);
                            else $tpl->set('{ava}', '/images/100_no_ava.png');
                            $tpl->compile('happy_all_friends');
                        }
                    }

                    $act = $_GET['act'];
                    $user_id = $user_info['user_id'];
                    $limit_select = 10;
                    $limit_page = 0;

                    //################### Загрузка стены ###################//
                    if($row['user_wall_num']){
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
                                $CheckBlackList = Tools::CheckBlackList($id);
                                if(!$CheckBlackList){
                                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                                    if($user_id != $id)
                                        $check_friend = Tools::CheckFriends($id);

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

                                    $titles = array('запись', 'записи', 'записей');//rec
                                    if($cnt_rec['cnt'] > 0)
                                        $user_speedbar = 'На стене '.$cnt_rec['cnt'].' '.Gramatic::declOfNum($cnt_rec['cnt'], $titles);

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
                            //include __DIR__.'/../Classes/Wall.php';
                            //$wall = new Wall();

                            if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id)
                                //$wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            elseif($wallAuthorId['author_user_id'] == $id)
                                //$wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            else {
                                //$wall->query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}");
                                $query = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `".PREFIX."_wall` tb1, `".PREFIX."_users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                                if($wallAuthorId['author_user_id'])
                                    $Hacking = true;
                            }
                            //Если вызвана страница стены, не со страницы юзера
                            if(!$Hacking){
                                if($rid OR $walluid){
                                    //$tpl = $wall->template('wall/one_record.tpl', $tpl);
                                    $tpl->load_template('wall/one_record.tpl');
                                    //$wall->compile('content');
                                    $compile = 'content';
                                    //$wall->select();

                                    if($cnt_rec['cnt'] > $gcount AND $_GET['type'] == '' OR $_GET['type'] == 'own')
                                        navigation($gcount, $cnt_rec['cnt'], $page_type);
                                } else {
                                    //$wall->template('wall/record.tpl', $tpl);
                                    $tpl->load_template('wall/one_record.tpl');
                                    //$wall->compile('wall');
                                    $compile = 'wall';
                                    //$wall->select();
                                }

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
                        }
                    }

                    //Общие друзья
                    if($row['user_friends_num'] AND $id != $user_info['user_id']){

                        $count_common = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_friends` tb1 INNER JOIN `".PREFIX."_friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_info['user_id']}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0");

                        if($count_common['cnt']){

                            $sql_mutual = $db->super_query("SELECT tb1.friend_id, tb3.user_photo, user_search_pref FROM `".PREFIX."_users` tb3, `".PREFIX."_friends` tb1 INNER JOIN `".PREFIX."_friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_info['user_id']}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by rand() LIMIT 0, 3", 1);

                            $tpl->load_template('/profile/profile_friends.tpl');

                            foreach($sql_mutual as $row_mutual){

                                $friend_info_mutual = explode(' ', $row_mutual['user_search_pref']);

                                $tpl->set('{user-id}', $row_mutual['friend_id']);
                                $tpl->set('{name}', $friend_info_mutual[0]);
                                $tpl->set('{last-name}', $friend_info_mutual[1]);

                                if($row_mutual['user_photo'])
                                    $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_mutual['friend_id'].'/50_'.$row_mutual['user_photo']);
                                else
                                    $tpl->set('{ava}', '/images/no_ava_50.png');

                                $tpl->compile('mutual_friends');
                            }
                        }
                    }

                    //################### Загрузка самого профиля ###################//
                    $tpl->load_template('/profile/profile.tpl');

                    if($count_common['cnt']){
                        $tpl->set('{mutual_friends}', $tpl->result['mutual_friends']);
                        $tpl->set('{mutual-num}', $count_common['cnt']);
                        $tpl->set('[common-friends]', '');
                        $tpl->set('[/common-friends]', '');
                    } else
                        $tpl->set_block("'\\[common-friends\\](.*?)\\[/common-friends\\]'si","");

                    $tpl->set('{user-id}', $row['user_id']);

                    //Страна и город
                    $tpl->set('{country}', $user_country_city_name_exp[0]);
                    $tpl->set('{country-id}', $row['user_country']);
                    $tpl->set('{city}', $user_country_city_name_exp[1]);
                    $tpl->set('{city-id}', $row['user_city']);

                    //Если человек сидит с мобильнйо версии
                    if($row_online['user_logged_mobile']) $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online" />';
                    else $mobile_icon = '';

                    if($row_online['user_last_visit'] >= $online_time)
                        $tpl->set('{online}', $lang['online'].$mobile_icon);
                    else {
                        if(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', $server_time))
                            $dateTell = langdate('сегодня в H:i', $row_online['user_last_visit']);
                        elseif(date('Y-m-d', $row_online['user_last_visit']) == date('Y-m-d', ($server_time-84600)))
                            $dateTell = langdate('вчера в H:i', $row_online['user_last_visit']);
                        else
                            $dateTell = langdate('j F Y в H:i', $row_online['user_last_visit']);
                        if($row['user_sex'] == 2)
                            $tpl->set('{online}', 'последний раз была '.$dateTell.$mobile_icon);
                        else
                            $tpl->set('{online}', 'последний раз был '.$dateTell.$mobile_icon);
                    }

                    if($row['user_city'] AND $row['user_country']){
                        $tpl->set('[not-all-city]','');
                        $tpl->set('[/not-all-city]','');
                    } else
                        $tpl->set_block("'\\[not-all-city\\](.*?)\\[/not-all-city\\]'si","");

                    if($row['user_country']){
                        $tpl->set('[not-all-country]','');
                        $tpl->set('[/not-all-country]','');
                    } else
                        $tpl->set_block("'\\[not-all-country\\](.*?)\\[/not-all-country\\]'si","");

                    //Конакты
                    $xfields = xfieldsdataload($row['user_xfields']);
                    $preg_safq_name_exp = explode(', ', 'phone, vk, od, skype, fb, icq, site');
                    foreach($preg_safq_name_exp as $preg_safq_name){
                        if($xfields[$preg_safq_name]){
                            $tpl->set("[not-contact-{$preg_safq_name}]", '');
                            $tpl->set("[/not-contact-{$preg_safq_name}]", '');
                        } else
                            $tpl->set_block("'\\[not-contact-{$preg_safq_name}\\](.*?)\\[/not-contact-{$preg_safq_name}\\]'si","");
                    }
                    $tpl->set('{vk}', '<a href="'.stripslashes($xfields['vk']).'" target="_blank">'.stripslashes($xfields['vk']).'</a>');
                    $tpl->set('{od}', '<a href="'.stripslashes($xfields['od']).'" target="_blank">'.stripslashes($xfields['od']).'</a>');
                    $tpl->set('{fb}', '<a href="'.stripslashes($xfields['fb']).'" target="_blank">'.stripslashes($xfields['fb']).'</a>');
                    $tpl->set('{skype}', stripslashes($xfields['skype']));
                    $tpl->set('{icq}', stripslashes($xfields['icq']));
                    $tpl->set('{phone}', stripslashes($xfields['phone']));

                    if(preg_match('/http:\/\//i', $xfields['site']))
                        if(preg_match('/\.ru|\.com|\.net|\.su|\.in\.ua|\.ua/i', $xfields['site']))
                            $tpl->set('{site}', '<a href="'.stripslashes($xfields['site']).'" target="_blank">'.stripslashes($xfields['site']).'</a>');
                        else
                            $tpl->set('{site}', stripslashes($xfields['site']));
                    else
                        $tpl->set('{site}', 'http://'.stripslashes($xfields['site']));

                    if(!$xfields['vk'] && !$xfields['od'] && !$xfields['fb'] && !$xfields['skype'] && !$xfields['icq'] && !$xfields['phone'] && !$xfields['site'])
                        $tpl->set_block("'\\[not-block-contact\\](.*?)\\[/not-block-contact\\]'si","");
                    else {
                        $tpl->set('[not-block-contact]', '');
                        $tpl->set('[/not-block-contact]', '');
                    }

                    //Интересы
                    $xfields_all = xfieldsdataload($row['user_xfields_all']);
                    $preg_safq_name_exp = explode(', ', 'activity, interests, myinfo, music, kino, books, games, quote');

                    if(!$xfields_all['activity'] AND !$xfields_all['interests'] AND !$xfields_all['myinfo'] AND !$xfields_all['music'] AND !$xfields_all['kino'] AND !$xfields_all['books'] AND !$xfields_all['games'] AND !$xfields_all['quote'])
                        $tpl->set('{not-block-info}', '<div align="center" style="color:#999;">Информация отсутствует.</div>');
                    else
                        $tpl->set('{not-block-info}', '');

                    foreach($preg_safq_name_exp as $preg_safq_name){
                        if($xfields_all[$preg_safq_name]){
                            $tpl->set("[not-info-{$preg_safq_name}]", '');
                            $tpl->set("[/not-info-{$preg_safq_name}]", '');
                        } else
                            $tpl->set_block("'\\[not-info-{$preg_safq_name}\\](.*?)\\[/not-info-{$preg_safq_name}\\]'si","");
                    }

                    $tpl->set('{activity}', nl2br(stripslashes($xfields_all['activity'])));
                    $tpl->set('{interests}', nl2br(stripslashes($xfields_all['interests'])));
                    $tpl->set('{myinfo}', nl2br(stripslashes($xfields_all['myinfo'])));
                    $tpl->set('{music}', nl2br(stripslashes($xfields_all['music'])));
                    $tpl->set('{kino}', nl2br(stripslashes($xfields_all['kino'])));
                    $tpl->set('{books}', nl2br(stripslashes($xfields_all['books'])));
                    $tpl->set('{games}', nl2br(stripslashes($xfields_all['games'])));
                    $tpl->set('{quote}', nl2br(stripslashes($xfields_all['quote'])));
                    $tpl->set('{name}', $user_name_lastname_exp[0]);
                    $tpl->set('{lastname}', $user_name_lastname_exp[1]);

                    //День рождение
                    $user_birthday = explode('-', $row['user_birthday']);
                    $row['user_day'] = $user_birthday[2];
                    $row['user_month'] = $user_birthday[1];
                    $row['user_year'] = $user_birthday[0];

                    if($row['user_day'] > 0 && $row['user_day'] <= 31 && $row['user_month'] > 0 && $row['user_month'] < 13){
                        $tpl->set('[not-all-birthday]', '');
                        $tpl->set('[/not-all-birthday]', '');

                        if($row['user_day'] && $row['user_month'] && $row['user_year'] > 1929 && $row['user_year'] < 2012)
                            $tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'&year='.$row['user_year'].'" onClick="Page.Go(this.href); return false">'.langdate('j F Y', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).' г.</a>');
                        else
                            $tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'" onClick="Page.Go(this.href); return false">'.langdate('j F', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).'</a>');
                    } else {
                        $tpl->set_block("'\\[not-all-birthday\\](.*?)\\[/not-all-birthday\\]'si","");
                    }

                    //Показ скрытых текста только для владельца страницы
                    if($user_info['user_id'] == $row['user_id']){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                    } else {
                        $tpl->set('[not-owner]', '');
                        $tpl->set('[/not-owner]', '');
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }

                    // FOR MOBILE VERSION 1.0
                    if($config['temp'] == 'mobile'){
                        $avaPREFver = '50_';
                        $noAvaPrf = 'no_ava_50.png';
                    } else {
                        $avaPREFver = '';
                        $noAvaPrf = 'no_ava.gif';
                    }

                    //Аватарка
                    if($row['user_photo']){
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/'.$avaPREFver.$row['user_photo']);
                        $tpl->set('{display-ava}', 'style="display:block;"');
                    } else {
                        $tpl->set('{ava}', '/images/'.$noAvaPrf);
                        $tpl->set('{display-ava}', 'style="display:none;"');
                    }

                    //################### Альбомы ###################//
                    if($user_id == $id){
                        $albums_privacy = false;
                        $albums_count['cnt'] = $row['user_albums_num'];
                    } else if($check_friend){
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) regexp '[[:<:]](1|2)[[:>:]]'";
                        $albums_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_albums` WHERE user_id = '{$id}' {$albums_privacy}", false, "user_{$id}/albums_cnt_friends");
                        $cache_pref = "_friends";
                    } else {
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) = 1";
                        $albums_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_albums` WHERE user_id = '{$id}' {$albums_privacy}", false, "user_{$id}/albums_cnt_all");
                        $cache_pref = "_all";
                    }
                    $sql_albums = $db->super_query("SELECT aid, name, adate, photo_num, cover FROM `".PREFIX."_albums` WHERE user_id = '{$id}' {$albums_privacy} ORDER by `position` ASC LIMIT 0, 4", 1, "user_{$id}/albums{$cache_pref}");
                    if($sql_albums){
                        foreach($sql_albums as $row_albums){
                            $row_albums['name'] = stripslashes($row_albums['name']);
                            $album_date = megaDate($row_albums['adate']);
                            $titles = array('фотография', 'фотографии', 'фотографий');//photos
                            $albums_photonums = Gramatic::declOfNum($row_albums['photo_num'], $titles);
                            if($row_albums['cover'])
                                $album_cover = "/uploads/users/{$id}/albums/{$row_albums['aid']}/c_{$row_albums['cover']}";
                            else
                                $album_cover = '/images/no_cover.png';
                            $albums .= "<a href=\"/albums/view/{$row_albums['aid']}\" onClick=\"Page.Go(this.href); return false\" style=\"text-decoration:none\"><div class=\"profile_albums\"><img src=\"{$album_cover}\" /><div class=\"profile_title_album\">{$row_albums['name']}</div>{$row_albums['photo_num']} {$albums_photonums}<br />Обновлён {$album_date}<div class=\"clear\"></div></div></a>";
                        }
                    }
                    $tpl->set('{albums}', $albums);
                    $tpl->set('{albums-num}', $albums_count['cnt']);
                    if($albums_count['cnt'] AND $config['album_mod'] == 'yes'){
                        $tpl->set('[albums]', '');
                        $tpl->set('[/albums]', '');
                    } else
                        $tpl->set_block("'\\[albums\\](.*?)\\[/albums\\]'si","");

                    //Делаем проверки на существования запрашиваемого юзера у себя в друзьяз, заклаках, в подписка, делаем всё это если страницу смотрет другой человек
                    if($user_id != $id){

                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        if($check_friend){
                            $tpl->set('[yes-friends]', '');
                            $tpl->set('[/yes-friends]', '');
                            $tpl->set_block("'\\[no-friends\\](.*?)\\[/no-friends\\]'si","");
                        } else {
                            $tpl->set('[no-friends]', '');
                            $tpl->set('[/no-friends]', '');
                            $tpl->set_block("'\\[yes-friends\\](.*?)\\[/yes-friends\\]'si","");
                        }

                        //Проверка естьли запрашиваемый юзер в закладках у юзера который смотрит стр
                        $check_fave = $db->super_query("SELECT user_id FROM `".PREFIX."_fave` WHERE user_id = '{$user_info['user_id']}' AND fave_id = '{$id}'");
                        if($check_fave){
                            $tpl->set('[yes-fave]', '');
                            $tpl->set('[/yes-fave]', '');
                            $tpl->set_block("'\\[no-fave\\](.*?)\\[/no-fave\\]'si","");
                        } else {
                            $tpl->set('[no-fave]', '');
                            $tpl->set('[/no-fave]', '');
                            $tpl->set_block("'\\[yes-fave\\](.*?)\\[/yes-fave\\]'si","");
                        }

                        //Проверка естьли запрашиваемый юзер в подписках у юзера который смотрит стр
                        $check_subscr = $db->super_query("SELECT user_id FROM `".PREFIX."_friends` WHERE user_id = '{$user_info['user_id']}' AND friend_id = '{$id}' AND subscriptions = 1");
                        if($check_subscr){
                            $tpl->set('[yes-subscription]', '');
                            $tpl->set('[/yes-subscription]', '');
                            $tpl->set_block("'\\[no-subscription\\](.*?)\\[/no-subscription\\]'si","");
                        } else {
                            $tpl->set('[no-subscription]', '');
                            $tpl->set('[/no-subscription]', '');
                            $tpl->set_block("'\\[yes-subscription\\](.*?)\\[/yes-subscription\\]'si","");
                        }

                        //Проверка естьли запрашиваемый юзер в черном списке
                        $MyCheckBlackList = Tools::MyCheckBlackList($id);
                        if($MyCheckBlackList){
                            $tpl->set('[yes-blacklist]', '');
                            $tpl->set('[/yes-blacklist]', '');
                            $tpl->set_block("'\\[no-blacklist\\](.*?)\\[/no-blacklist\\]'si","");
                        } else {
                            $tpl->set('[no-blacklist]', '');
                            $tpl->set('[/no-blacklist]', '');
                            $tpl->set_block("'\\[yes-blacklist\\](.*?)\\[/yes-blacklist\\]'si","");
                        }

                    }

                    $author_info = explode(' ', $row['user_search_pref']);
                    $tpl->set('{gram-name}', Gramatic::gramatikName($author_info[0]));

                    $tpl->set('{friends-num}', $row['user_friends_num']);
                    $tpl->set('{online-friends-num}', $online_friends['cnt']);
                    $tpl->set('{notes-num}', $row['user_notes_num']);
                    $tpl->set('{subscriptions-num}', $row['user_subscriptions_num']);
                    $tpl->set('{videos-num}', $row['user_videos_num']);

                    //Если есть заметки то выводим
                    if($row['user_notes_num']){
                        $tpl->set('[notes]', '');
                        $tpl->set('[/notes]', '');
                        $tpl->set('{notes}', $tpl->result['notes']);
                    } else
                        $tpl->set_block("'\\[notes\\](.*?)\\[/notes\\]'si","");

                    //Если есть видео то выводим
                    if($row['user_videos_num'] AND $config['video_mod'] == 'yes'){
                        $tpl->set('[videos]', '');
                        $tpl->set('[/videos]', '');
                        $tpl->set('{videos}', $tpl->result['videos']);
                    } else
                        $tpl->set_block("'\\[videos\\](.*?)\\[/videos\\]'si","");

                    //Если есть друзья, то выводим
                    if($row['user_friends_num']){
                        $tpl->set('[friends]', '');
                        $tpl->set('[/friends]', '');
                        $tpl->set('{friends}', $tpl->result['all_friends']);
                    } else
                        $tpl->set_block("'\\[friends\\](.*?)\\[/friends\\]'si","");

                    //Кол-во подписок и Если есть друзья, то выводим
                    if($row['user_subscriptions_num']){
                        $tpl->set('[subscriptions]', '');
                        $tpl->set('[/subscriptions]', '');
                        $tpl->set('{subscriptions}', $tpl->result['subscriptions']);
                    } else
                        $tpl->set_block("'\\[subscriptions\\](.*?)\\[/subscriptions\\]'si","");

                    //Если есть друзья на сайте, то выводим
                    if($online_friends['cnt']){
                        $tpl->set('[online-friends]', '');
                        $tpl->set('[/online-friends]', '');
                        $tpl->set('{online-friends}', $tpl->result['all_online_friends']);
                    } else
                        $tpl->set_block("'\\[online-friends\\](.*?)\\[/online-friends\\]'si","");

                    //Если человек пришел после реги, то открываем ему окно загрузи фотографии
//                    if(intval($_GET['after'])){
//                        $tpl->set('[after-reg]', '');
//                        $tpl->set('[/after-reg]', '');
//                    } else
//                        $tpl->set_block("'\\[after-reg\\](.*?)\\[/after-reg\\]'si","");

                    //Стена
                    $tpl->set('{records}', $tpl->result['wall']);

                    if($user_id != $id){
                        if($user_privacy['val_wall1'] == 3 OR $user_privacy['val_wall1'] == 2 AND !$check_friend){
                            $cnt_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
                            $row['user_wall_num'] = $cnt_rec['cnt'];
                        }
                    }

                    $row['user_wall_num'] = $row['user_wall_num'] ? $row['user_wall_num'] : '';
                    if($row['user_wall_num'] > 10){
                        $tpl->set('[wall-link]', '');
                        $tpl->set('[/wall-link]', '');
                    } else
                        $tpl->set_block("'\\[wall-link\\](.*?)\\[/wall-link\\]'si","");

                    $tpl->set('{wall-rec-num}', $row['user_wall_num']);

                    if($row['user_wall_num'])
                        $tpl->set_block("'\\[no-records\\](.*?)\\[/no-records\\]'si","");
                    else {
                        $tpl->set('[no-records]', '');
                        $tpl->set('[/no-records]', '');
                    }

                    //Статус
                    $tpl->set('{status-text}', stripslashes($row['user_status']));

                    if($row['user_status']){
                        $tpl->set('[status]', '');
                        $tpl->set('[/status]', '');
                        $tpl->set_block("'\\[no-status\\](.*?)\\[/no-status\\]'si","");
                    } else {
                        $tpl->set_block("'\\[status\\](.*?)\\[/status\\]'si","");
                        $tpl->set('[no-status]', '');
                        $tpl->set('[/no-status]', '');
                    }

                    //Приватность сообщений
                    if($user_privacy['val_msg'] == 1 OR $user_privacy['val_msg'] == 2 AND $check_friend){
                        $tpl->set('[privacy-msg]', '');
                        $tpl->set('[/privacy-msg]', '');
                    } else
                        $tpl->set_block("'\\[privacy-msg\\](.*?)\\[/privacy-msg\\]'si","");

                    //Приватность стены
                    if($user_privacy['val_wall1'] == 1 OR $user_privacy['val_wall1'] == 2 AND $check_friend OR $user_id == $id){
                        $tpl->set('[privacy-wall]', '');
                        $tpl->set('[/privacy-wall]', '');
                    } else
                        $tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");

                    if($user_privacy['val_wall2'] == 1 OR $user_privacy['val_wall2'] == 2 AND $check_friend OR $user_id == $id){
                        $tpl->set('[privacy-wall]', '');
                        $tpl->set('[/privacy-wall]', '');
                    } else
                        $tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");

                    //Приватность информации
                    if($user_privacy['val_info'] == 1 OR $user_privacy['val_info'] == 2 AND $check_friend OR $user_id == $id){
                        $tpl->set('[privacy-info]', '');
                        $tpl->set('[/privacy-info]', '');
                    } else
                        $tpl->set_block("'\\[privacy-info\\](.*?)\\[/privacy-info\\]'si","");

                    //Семейное положение
                    $user_sp = explode('|', $row['user_sp']);
                    if($user_sp[1]){
                        $rowSpUserName = $db->super_query("SELECT user_search_pref, user_sp, user_sex FROM `".PREFIX."_users` WHERE user_id = '{$user_sp[1]}'");
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
                    $sp6 = "партнёр <a href=\"/u{$user_sp[1]}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                    $sp6_6 = '<a href="/search/?sp=6" onClick="Page.Go(this.href); return false">всё сложно</a>';
                    $tpl->set('[sp]', '');
                    $tpl->set('[/sp]', '');
                    if($user_sp[0] == 1)
                        $tpl->set('{sp}', $sp1);
                    else if($user_sp[0] == 2)
                        if($spUserName) $tpl->set('{sp}', $sp2);
                        else $tpl->set('{sp}', $sp2_2);
                    else if($user_sp[0] == 3)
                        if($spUserName) $tpl->set('{sp}', $sp3);
                        else $tpl->set('{sp}', $sp3_3);
                    else if($user_sp[0] == 4)
                        if($spUserName) $tpl->set('{sp}', $sp4);
                        else $tpl->set('{sp}', $sp4_4);
                    else if($user_sp[0] == 5)
                        if($spUserName) $tpl->set('{sp}', $sp5);
                        else $tpl->set('{sp}', $sp5_5);
                    else if($user_sp[0] == 6)
                        if($spUserName) $tpl->set('{sp}', $sp6);
                        else $tpl->set('{sp}', $sp6_6);
                    else if($user_sp[0] == 7)
                        $tpl->set('{sp}', '<a href="/search/?sp=7" onClick="Page.Go(this.href); return false">в активном поиске</a>');
                    else
                        $tpl->set_block("'\\[sp\\](.*?)\\[/sp\\]'si","");

                    //ЧС
                    if(!$CheckBlackList){
                        $tpl->set('[blacklist]', '');
                        $tpl->set('[/blacklist]', '');
                        $tpl->set_block("'\\[not-blacklist\\](.*?)\\[/not-blacklist\\]'si","");
                    } else {
                        $tpl->set('[not-blacklist]', '');
                        $tpl->set('[/not-blacklist]', '');
                        $tpl->set_block("'\\[blacklist\\](.*?)\\[/blacklist\\]'si","");
                    }

                    //################### Подарки ###################//
                    if($row['user_gifts']){
                        $sql_gifts = $db->super_query("SELECT gift FROM `".PREFIX."_gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 5", 1, "user_{$id}/gifts");
                        foreach($sql_gifts as $row_gift){
                            $gifts .= "<img src=\"/uploads/gifts/{$row_gift['gift']}.png\" class=\"gift_onepage\" />";
                        }
                        $tpl->set('[gifts]', '');
                        $tpl->set('[/gifts]', '');
                        $tpl->set('{gifts}', $gifts);
                        $titles = array('подарок', 'подарка', 'подарков');//gifts
                        $tpl->set('{gifts-text}', $row['user_gifts'].' '.Gramatic::declOfNum($row['user_gifts'], $titles));
                    } else
                        $tpl->set_block("'\\[gifts\\](.*?)\\[/gifts\\]'si","");

                    //################### Интересные страницы ###################//
                    if($row['user_public_num']){
                        $sql_groups = $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5", 1, "groups/".$id);
                        foreach($sql_groups as $row_groups){
                            if($row_groups['adres']) $adres = $row_groups['adres'];
                            else $adres = 'public'.$row_groups['id'];
                            if($row_groups['photo']) $ava_groups = "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}";
                            else $ava_groups = "/images/no_ava_50.png";
                            $row_groups['status_text'] = iconv_substr($row_groups['status_text'], 0, 24, 'utf-8');
                            $groups .= '<div class="onesubscription onesubscriptio2n cursor_pointer" onClick="Page.Go(\'/'.$adres.'\')"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false"><img src="'.$ava_groups.'" /></a><div class="onesubscriptiontitle"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false">'.stripslashes($row_groups['title']).'</a></div><span class="color777 size10">'.stripslashes($row_groups['status_text']).'</span></div>';
                        }
                        $tpl->set('[groups]', '');
                        $tpl->set('[/groups]', '');
                        $tpl->set('{groups}', $groups);
                        $tpl->set('{groups-num}', $row['user_public_num']);
                    } else
                        $tpl->set_block("'\\[groups\\](.*?)\\[/groups\\]'si","");

                    //################### Музыка ###################//
                    if($row['user_audio'] AND $config['audio_mod'] == 'yes'){
                        $tpl->set('[audios]', '');
                        $tpl->set('[/audios]', '');
                        $tpl->set('{audios}', $tpl->result['audios']);
                        $titles = array('песня', 'песни', 'песен');//audio
                        $tpl->set('{audios-num}', $row['user_audio'].' '.Gramatic::declOfNum($row['user_audio'], $titles));
                    } else
                        $tpl->set_block("'\\[audios\\](.*?)\\[/audios\\]'si","");

                    //################### Праздники друзей ###################//
                    if($cnt_happfr){
                        $tpl->set('{happy-friends}', $tpl->result['happy_all_friends']);
                        $tpl->set('{happy-friends-num}', $cnt_happfr);
                        $tpl->set('[happy-friends]', '');
                        $tpl->set('[/happy-friends]', '');
                    } else
                        $tpl->set_block("'\\[happy-friends\\](.*?)\\[/happy-friends\\]'si","");

                    //################### Обработка дополнительных полей ###################//
                    $xfieldsdata = xfieldsdataload($row['xfields']);
                    $xfields = profileload();

                    foreach($xfields as $value){

                        $preg_safe_name = preg_quote($value[0], "'");

                        if(empty($xfieldsdata[$value[0]])){

                            $tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template);

                        } else {

                            $tpl->copy_template = str_replace("[xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);
                            $tpl->copy_template = str_replace("[/xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);

                        }

                        $tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", stripslashes($xfieldsdata[$value[0]]), $tpl->copy_template);

                    }

                    if($id == 7) $tpl->set('{group}', '<font color="#f87d7d">Модератор</font>');
                    else $tpl->set('{group}', '');

                    //Обложка
                    if($row['user_photo']){

                        $avaImgIsinfo = getimagesize(__DIR__."/../../public/uploads/users/{$row['user_id']}/{$row['user_photo']}");

                        if($avaImgIsinfo[1] < 200){

                            $rForme = $avaImgIsinfo[1] * 100 / 230 * 2;

                            $ava_marg_top = 'style="margin-top:-'.$rForme.'px"';

                        }

                        $tpl->set('{cover-param-7}', $ava_marg_top);

                    } else
                        $tpl->set('{cover-param-7}', "");

                    if($row['user_cover']){

                        $imgIsinfo = getimagesize(__DIR__."/../../public/uploads/users/{$id}/{$row['user_cover']}");

                        $tpl->set('{cover}', "/uploads/users/{$id}/{$row['user_cover']}");
                        $tpl->set('{cover-height}', $imgIsinfo[1]);
                        $tpl->set('{cover-param}', '');
                        $tpl->set('{cover-param-2}', 'no_display');
                        $tpl->set('{cover-param-3}', 'style="position:absolute;z-index:2;display:block;margin-left:397px"');
                        $tpl->set('{cover-param-4}', 'style="cursor:default"');
                        $tpl->set('{cover-param-5}', 'style="top:-'.$row['user_cover_pos'].'px;position:relative"');
                        $tpl->set('{cover-pos}', $row['user_cover_pos']);

                        $tpl->set('[cover]', '');
                        $tpl->set('[/cover]', '');

                    } else {

                        $tpl->set('{cover}', "");
                        $tpl->set('{cover-param}', 'no_display');
                        $tpl->set('{cover-param-2}', '');
                        $tpl->set('{cover-param-3}', '');
                        $tpl->set('{cover-param-4}', '');
                        $tpl->set('{cover-param-5}', '');
                        $tpl->set('{cover-pos}', '');
                        $tpl->set_block("'\\[cover\\](.*?)\\[/cover\\]'si","");
                    }

                    //Rating
                    if($row['user_rating'] > 1000){
                        $tpl->set('{rating-class-left}', 'profile_rate_1000_left');
                        $tpl->set('{rating-class-right}', 'profile_rate_1000_right');
                        $tpl->set('{rating-class-head}', 'profile_rate_1000_head');
                    } elseif($row['user_rating'] > 500){
                        $tpl->set('{rating-class-left}', 'profile_rate_500_left');
                        $tpl->set('{rating-class-right}', 'profile_rate_500_right');
                        $tpl->set('{rating-class-head}', 'profile_rate_500_head');
                    } else {
                        $tpl->set('{rating-class-left}', '');
                        $tpl->set('{rating-class-right}', '');
                        $tpl->set('{rating-class-head}', '');
                    }

                    if(!$row['user_rating']) $row['user_rating'] = 0;
                    $tpl->set('{rating}', $row['user_rating']);

                    $tpl->compile('content');

                    //Гости
                    if($id != $user_info['user_id']){
                        $checkGuest = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_guests` WHERE ouid = '{$id}' AND guid = '{$user_id}'");
                        if($checkGuest['cnt'])
                            $db->query("UPDATE `".PREFIX."_guests` SET gdate = '{$server_time}', new = '1' WHERE ouid = '{$id}' AND guid = '{$user_id}'");
                        else
                            $db->query("INSERT INTO `".PREFIX."_guests` SET gdate = '{$server_time}', ouid = '{$id}', guid = '{$user_id}', new = '1'");
                        $db->super_query("UPDATE `".PREFIX."_users` SET guests = guests + 1 WHERE user_id = '{$id}'");
                    }

                    //Обновляем кол-во посищений на страницу, если юзер есть у меня в друзьях
                    if($check_friend)
                        $db->query("UPDATE LOW_PRIORITY `".PREFIX."_friends` SET views = views+1 WHERE user_id = '{$user_info['user_id']}' AND friend_id = '{$id}' AND subscriptions = 0");

                    //Вставляем в статистику
                    if($user_info['user_id'] != $id){

                        $stat_date = date('Ymd', $server_time);
                        $stat_x_date = date('Ym', $server_time);

                        $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users_stats_log` WHERE user_id = '{$user_info['user_id']}' AND for_user_id = '{$id}' AND date = '{$stat_date}'");

                        if(!$check_user_stat['cnt']){
                            $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users_stats` WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            if($check_stat['cnt'])
                                $db->query("UPDATE `".PREFIX."_users_stats` SET users = users + 1, views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            else
                                $db->query("INSERT INTO `".PREFIX."_users_stats` SET user_id = '{$id}', date = '{$stat_date}', users = '1', views = '1', date_x = '{$stat_x_date}'");
                            $db->query("INSERT INTO `".PREFIX."_users_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', for_user_id = '{$id}'");
                        } else {
                            $db->query("UPDATE `".PREFIX."_users_stats` SET views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                        }
                    }


                }
            } else {
                $user_speedbar = $lang['no_infooo'];
                msgbox('', $lang['no_upage'], 'info'); //Страница удалена, либо еще не создана.
            }

            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = 'Информация';
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}
