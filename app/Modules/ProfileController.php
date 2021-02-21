<?php
declare(strict_types=1);

namespace App\Modules;

use App\Libs\Friends;
use App\Libs\Wall;
use JetBrains\PhpStorm\NoReturn;
use Sura\Cache\Cache;
use Sura\Cache\Storages\MemcachedStorage;
use Sura\Libs\Db;
use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Gramatic;
use App\Models\Profile;
use Sura\Time\Date;
use Throwable;

class ProfileController extends Module
{

    /**
     * Просмотр страницы пользователей
     *
     * @param $params
     * @return int
     * @throws Throwable
     */
    public function index($params): int
    {
        $lang = $this->get_langs();
        $db = Db::getDB();

        $user_info = $this->user_info();
        $logged = $this->logged();


        $user_id = $user_info['user_id'];
        $params['user_id'] = $user_id;

        $request = (Request::getRequest()->getGlobal());
        $config = Settings::load();

        /*
         * ID user page
         */
        if (isset($params['alias'])) {
            $id = (int)$params['alias'];
        } else {
            $server = Request::getRequest()->server;

            $path = explode('/', $server['REQUEST_URI']);
            $id = str_replace('u', '', $path);
            $id = (int)$id['1'];
        }

        $storage = new MemcachedStorage('localhost');
        $cache = new Cache($storage, 'users');

        $key = $id . '/profile_' . $id;
        $value = $cache->load($key, function (&$dependencies) {
            $dependencies[Cache::EXPIRE] = '20 minutes';
        });

        $Profile = new Profile;

        if ($value == NULL) {
            $row = $Profile->user_row($id);
            $value = serialize($row);
            $cache->save($key, $value);
        } else {
            $row = unserialize($value, $options = []);
        }

        if ($logged) {



            $row_online['user_last_visit'] = $row['user_last_visit'];
            $row_online['user_logged_mobile'] = $row['user_logged_mobile'];

            if ($user_info['user_id'] == $row['user_id']) {
                $params['owner'] = true;
                $row_online['user_last_visit'] = $user_info['user_last_visit'];
            } else {
                $params['owner'] = false;
            }


            //Если есть такой, юзер то продолжаем выполнение скрипта
            if (isset($row)) {
                //Profile_ban = $row['user_search_pref'];
//                $params['title'] = $row['user_search_pref'].' | Sura';

//                $server_time = (int)$_SERVER['REQUEST_TIME'];
                $server_time = Date::time();

                //Если удалена
                if (isset($row['user_delet']) and $row['user_delet'] == 1) {
//                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    self::delete();
                } //Если заблокирована
                elseif (isset($row['user_ban_date']) and $row['user_ban_date'] == 1) {
                    if ($row['user_ban_date'] >= $server_time or $row['user_ban_date'] == '0') {
//                        $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                        self::ban();
                    }
                } //Если все хорошо, то выводим дальше
                else {

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if ($user_id != $id) {
                        $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($row['user_id']);
                        $CheckFriends = (new \App\Libs\Friends)->CheckFriends($row['user_id']);

                    } else {
                        $CheckBlackList = false;
                        $CheckFriends = false;
                    }
                    $user_privacy = xfieldsdataload($row['user_privacy']);
                    $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                    $user_country_city_name_exp = explode('|', $row['user_country_city_name']);

                    /**
                     * Друзья
                     */
                    //                    $row['user_friends_num'] = 0;
                    if (!isset($row['user_friends_num'])) {
                        $row['user_friends_num'] = 0;
                    }

                    if ($row['user_friends_num'] > 0 and $CheckBlackList == false) {
                        $sql_friends = $Profile->friends($id);
                        foreach ($sql_friends as $key => $row_friends) {
                            $friend_info = explode(' ', $row_friends['user_search_pref']);
                            $sql_friends[$key]['user_id'] = $row_friends['friend_id'];
                            $sql_friends[$key]['name'] = $friend_info['0'];
                            if (isset($friend_info['1'])) {
                                $sql_friends[$key]['last_name'] = $friend_info['1'];

                            } else {
                                $sql_friends[$key]['last_name'] = 'Неизвестный пользователь';
                            }

                            if ($row_friends['user_photo']) {
                                $sql_friends[$key]['ava'] = $config['home_url'] . 'uploads/users/' . $row_friends['friend_id'] . '/50_' . $row_friends['user_photo'];
                            } else {
                                $sql_friends[$key]['ava'] = '/images/no_ava_50.png';
                            }
                        }
                        $params['all_friends'] = $sql_friends;
                        $params['all_friends_num'] = $row['user_friends_num'];
                    }


                    /**
                     * Друзья на сайте
                     *
                     * @var $sql_friends_online array
                     */
                    $online_time = $server_time - 60;
                    //Кол-во друзей в онлайне
                    if ($row['user_friends_num'] > 0 and !$CheckBlackList) {
                        $online_friends = $Profile->friends_online_cnt($id, $online_time);
                        //Если друзья на сайте есть то идем дальше
                        if ($online_friends['cnt']) {
                            $sql_friends_online = $Profile->friends_online($id, (int)$online_time);
                            foreach ($sql_friends_online as $key => $row_friends_online) {
                                $friend_info_online = explode(' ', $row_friends_online['user_search_pref']);
                                $sql_friends_online[$key]['user_id'] = $row_friends_online['user_id'];
                                $sql_friends_online[$key]['name'] = $friend_info_online['0'];
//                                $sql_friends_online[$key]['lastname'] = $friend_info_online[1]
                                if (isset($friend_info_online['1'])) {
                                    $sql_friends_online[$key]['last_name'] = $friend_info['1'];

                                } else {
                                    $sql_friends_online[$key]['last_name'] = 'Неизвестный пользователь';
                                }
                                if ($row_friends_online['user_photo']) {
                                    $sql_friends_online[$key]['ava'] = $config['home_url'] . 'uploads/users/' . $row_friends_online['user_id'] . '/50_' . $row_friends_online['user_photo'];
                                } else {
                                    $sql_friends_online[$key]['ava'] = '/images/no_ava_50.png';
                                }
                            }
                            $params['all_online_friends'] = $sql_friends_online;
                            $params['all_online__friends_num'] = $online_friends['cnt'];
                        } else
                            $params['all_online_friends'] = false;
                    } else
                        $params['all_online_friends'] = false;

                    /**
                     * Видеозаписи
                     */
                    if ($row['user_videos_num'] > 0 and $config['video_mod'] == 'yes' and !$CheckBlackList) {
                        //Настройки приватности
                        if ($user_id == $id) {
                            $sql_privacy = "";
                            $cache_pref_videos = '';
                        } elseif ($CheckFriends) {
                            $sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
                            $cache_pref_videos = "_friends";
                        } else {
                            $sql_privacy = "AND privacy = 1";
                            $cache_pref_videos = "_all";
                        }

                        $video_cnt = $Profile->videos_online_cnt($id, $sql_privacy, $cache_pref_videos);
                        $row['user_videos_num'] = $video_cnt['cnt'];

                        $sql_videos = $Profile->videos_online($id, $sql_privacy, $cache_pref_videos);

                        foreach ($sql_videos as $key => $row_videos) {
                            $sql_videos[$key]['photo'] = $row_videos['photo'];
                            $sql_videos[$key]['user_id'] = $id;
                            $sql_videos[$key]['title'] = stripslashes($row_videos['title']);
                            $titles = array('комментарий', 'комментария', 'комментариев');//comments
                            $sql_videos[$key]['comm_num'] = $row_videos['comm_num'] . ' ' . Gramatic::declOfNum($row_videos['comm_num'], $titles);
                            $date = Date::megaDate(strtotime($row_videos['add_date']), '');
                            $sql_videos[$key]['date'] = $date;
                        }
                        $params['videos_num'] = $video_cnt['cnt'];
                        $params['videos'] = $sql_videos;
                    } else
                        $params['videos'] = false;

                    /**
                     * Подписки
                     */
                    if ($row['user_subscriptions_num'] > 0 and !$CheckBlackList) {
                        $cache_pref_subscriptions = '/subscr_user_' . $id;
//                        if(!$subscriptions){
                        $sql_subscriptions = $Profile->subscriptions($id, $cache_pref_subscriptions);
                        foreach ($sql_subscriptions as $key => $row_subscr) {
                            $sql_subscriptions[$key]['user_id'] = $row_subscr['friend_id'];
                            $sql_subscriptions[$key]['name'] = $row_subscr['user_search_pref'];
                            if ($row_subscr['user_status']) {
                                $sql_subscriptions[$key]['info'] = stripslashes(iconv_substr($row_subscr['user_status'], 0, 24, 'utf-8'));
                            } else {
                                $country_city = explode('|', $row_subscr['user_country_city_name']);
                                $sql_subscriptions[$key]['info'] = $country_city[1];
                            }
                            if ($row_subscr['user_photo']) {
                                $sql_subscriptions[$key]['ava'] = $config['home_url'] . 'uploads/users/' . $row_subscr['friend_id'] . '/50_' . $row_subscr['user_photo'];
                            } else {
                                $sql_subscriptions[$key]['ava'] = '/images/no_ava_50.png';
                            }
                        }
                        $params['subscriptions'] = $sql_subscriptions;
                        $params['subscriptions_num'] = $row['user_subscriptions_num'];
                        //                            Cache::mozg_create_cache('/subscr_user_'.$id, $tpl->result['subscriptions']);
//                        }
                    } else
                        $params['subscriptions'] = false;

                    /**
                     * Музыка
                     */
                    if ($row['user_audio'] and !$CheckBlackList and $config['audio_mod'] == 'yes') {
                        $sql_audio = $Profile->audio($id);
                        foreach ($sql_audio as $key => $row_audio) {
                            $sql_audio[$key]['stime'] = gmdate("i:s", (int)$row_audio['duration']);
                            if (!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                            if (!$row_audio['title']) $row_audio['title'] = 'Без названия';
                            $sql_audio[$key]['search_artist'] = urlencode($row_audio['artist']);
                            $sql_audio[$key]['plname'] = 'audios' . $id;
                        }
                        $titles = array('песня', 'песни', 'песен');//audio
                        $params['audios_num'] = $row['user_audio'] . ' ' . Gramatic::declOfNum($row['user_audio'], $titles);
                        $params['audios'] = $sql_audio;
                    } else
                        $params['audios'] = false;

                    /**
                     * Праздники друзей
                     */
                    if ($user_id == $id and !isset($_SESSION['happy_friends_block_hide']) and !$CheckBlackList) {
                        $sql_happy_friends = $Profile->happy_friends($id, $server_time);
                        //                        $tpl->load_template('/profile/profile_happy_friends.tpl');
                        $cnt_happfr = 0;
                        foreach ($sql_happy_friends as $key => $happy_row_friends) {
                            $cnt_happfr++;
                            $sql_happy_friends[$key]['user_id'] = $happy_row_friends['friend_id'];
                            $sql_happy_friends[$key]['name'] = $happy_row_friends['user_search_pref'];
                            $user_birthday = explode('-', $happy_row_friends['user_birthday']);
                            $sql_happy_friends[$key]['age'] = \App\Libs\Profile::user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);
                            if ($happy_row_friends['user_photo']) {
                                $sql_happy_friends[$key]['ava'] = '/uploads/users/' . $happy_row_friends['friend_id'] . '/100_' . $happy_row_friends['user_photo'];
                            } else {
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
                    if ($user_privacy['val_wall1'] == 1 or $user_privacy['val_wall1'] == 2 and $CheckFriends or $user_id == $id) {
                        //                        $tpl->set('[privacy-wall]', '');
                        $params['privacy_wall_block'] = true;
                    } elseif ($user_privacy['val_wall2'] == 1 or $user_privacy['val_wall2'] == 2 and $CheckFriends or $user_id == $id) {
                        //                        $tpl->set('[privacy-wall]', '');
                        $params['privacy_wall_block'] = true;
                    } else {
                        //                        $tpl->set_block("'\\[privacy-wall\\](.*?)\\[/privacy-wall\\]'si","");
                        $params['privacy_wall_block'] = false;
                    }

                    if ($user_id != $id) {
                        if ($user_privacy['val_wall1'] == 3 or $user_privacy['val_wall1'] == 2 and !$CheckFriends) {
                            $cnt_rec = $Profile->cnt_rec($id);
                            $row['user_wall_num'] = $cnt_rec['cnt'];
                            $params['wall_rec_num'] = $row['user_wall_num'];
                        } else
                            $params['wall_rec_num'] = $row['user_wall_num'];
                    } else
                        $params['wall_rec_num'] = $row['user_wall_num'];

                    $row['user_wall_num'] = $row['user_wall_num'] ? $row['user_wall_num'] : '';
                    if ($row['user_wall_num'] > 10) {
                        $params['wall_link_block'] = true;
                    } else {
                        $params['wall_link_block'] = true;
                    }

                    if ($row['user_wall_num'] > 0) {
                        $params['wall_rec_num_block'] = true;
                    } else {
                        $params['wall_rec_num_block'] = false;
                    }


                    if ($row['user_wall_num'] and !$CheckBlackList) {
                        //################### Показ последних 10 записей ###################//

                        //Если вызвана страница стены, не со страницы юзера
                        if (!$id) {
                            if (isset($request['rid'])) {
                                $rid = (int)$request['rid'];
                            } else {
                                $rid = null;
                            }

                            if (isset($request['uid'])) {
                                $id = (int)$request['uid'];
                            } else {
                                $id = $user_id;
                            }

//                            $walluid = $id;
                            $params['title'] = $lang['wall_title'];

                            if ($request['page'] > 0) {
                                $page = (int)$request['page'];
                            } else {
                                $page = 1;
                            }
                            $gcount = 10;
                            $limit_page = ($page - 1) * $gcount;
                            //not used row_user['user_privacy']
                            //$row_user = $db->super_query("SELECT user_name, user_wall_num, user_privacy FROM `users` WHERE user_id = '{$id}'");
                            $user_privacy = xfieldsdataload($row['user_privacy']);

                            if ($row['user_wall_num'] > 0) {
                                //ЧС
                                $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($id);
                                if (!$CheckBlackList) {

                                    if ($user_privacy['val_wall1'] == 1 or $user_privacy['val_wall1'] == 2 and $CheckFriends or $user_id == $id)
                                        $cnt_rec['cnt'] = $row['user_wall_num'];
                                    else
                                        $cnt_rec = $Profile->cnt_rec($id);

                                    /**
                                     * record_tab
                                     */
                                    if ($request['type'] == 'own') {
                                        $params['record_tab'] = false;
                                        $cnt_rec = $Profile->cnt_rec($id);
                                        $where_sql = "AND tb1.author_user_id = '{$id}'";
//                                        $page_type = '/wall'.$id.'_sec=own&page=';
                                    } else if ($request['type'] == 'record') {
                                        $params['record_tab'] = true;
                                        $where_sql = "AND tb1.id = '{$rid}'";
                                        $wallAuthorId = $Profile->author_user_id($rid);
                                    } else {
                                        $params['record_tab'] = false;
                                        $request['type'] = '';
                                        $where_sql = '';
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
                                    $params['wall_head']['activetab_' . $request['type']] = 'activetab';

                                    if ($cnt_rec['cnt'] < 1) {
                                        // msgbox('', $lang['wall_no_rec'], 'info_2');
                                        $params['msg_box'] = $lang['wall_no_rec'];
                                    }

                                } else {
                                    //                                    $user_speedbar = $lang['error'];
                                    //msgbox('', $lang['no_notes'], 'info');
                                    $params['msg_box'] = $lang['no_notes'];
                                }
                            } else {
                                //msgbox('', $lang['wall_no_rec'], 'info_2');
                                $params['msg_box'] = $lang['wall_no_rec'];
                            }

                        }

                        if (!isset($wallAuthorId)){
                            $wallAuthorId = array();//FIXME
                        }

                        if (!$CheckBlackList) {
                            if (!isset($where_sql))
                                $where_sql = '';
                            if ($user_privacy['val_wall1'] == 1 or $user_privacy['val_wall1'] == 2 and $CheckFriends or $user_id == $id) {
                                $query = $db->super_query("SELECT tb1.id, author_user_id, for_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            } elseif ($wallAuthorId['author_user_id'] == $id) {
                                $query = $db->super_query("SELECT tb1.id, author_user_id, for_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 {$where_sql} ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            } else {
                                $query = $db->super_query("SELECT tb1.id, author_user_id, for_user_id, text, add_date, fasts_num, likes_num, likes_users, tell_uid, type, tell_date, public, attach, tell_comm, tb2.user_photo, user_search_pref, user_last_visit, user_logged_mobile FROM `wall` tb1, `users` tb2 WHERE for_user_id = '{$id}' AND tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = 0 AND tb1.author_user_id = '{$id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$limit_select}", 1);
                            }


//                                if (isset($request['rid']))
//                                    $rid = (int)$request['rid'];
//                                else
                            $rid = null;

//                                if (isset($request['uid']))
//                                    $id = (int)$request['uid'];
//                                else
//                                    $id = $user_id;

//                                $walluid = $id;

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
                            $config = Settings::load();

                            /**
                             * wall records
                             *
                             */
                            $params['wall_records'] = Wall::build($query);

                        }
                    }

                    //Общие друзья
                    if ($row['user_friends_num'] and $id != $user_info['user_id'] and !$CheckBlackList) {
                        $count_common = $Profile->count_common($id, $user_info['user_id']);
                        if ($count_common['cnt']) {
                            $sql_mutual = $Profile->mutual($id, $user_info['user_id']);
                            foreach ($sql_mutual as $key => $row_mutual) {
                                $friend_info_mutual = explode(' ', $row_mutual['user_search_pref']);
                                $sql_mutual[$key]['user_id'] = $row_mutual['friend_id'];
                                $sql_mutual[$key]['name'] = $friend_info_mutual[0];
                                $sql_mutual[$key]['last_name'] = $friend_info_mutual[1];
                                if ($row_mutual['user_photo']) {
                                    $sql_mutual[$key]['ava'] = $config['home_url'] . 'uploads/users/' . $row_mutual['friend_id'] . '/50_' . $row_mutual['user_photo'];
                                } else {
                                    $sql_mutual[$key]['ava'] = '/images/no_ava_50.png';
                                }
                            }
                            $params['mutual_friends'] = $sql_mutual;
                        } else
                            $params['mutual_friends'] = false;
                        $params['mutual_num'] = $count_common['cnt'];
                    } else {
                        $params['mutual_friends'] = false;
                    }

                    /**
                     * Загрузка самого профиля
                     */
                    $params['user_id'] = $row['user_id'];

                    //Страна и город
                    if ($row['user_city'] and $row['user_country']) {
                        $params['city'] = $user_country_city_name_exp['1'];
                        $params['city_id'] = $row['user_city'];
                        $params['not_all_city_block'] = true;
                    } else {
                        $params['not_all_city_block'] = false;
                    }

                    if ($row['user_country']) {
                        $params['country'] = $user_country_city_name_exp['0'];
                        $params['country_id'] = $row['user_country'];
                        $params['not_all_country_block'] = true;
                    } else {
                        $params['not_all_country_block'] = false;
                    }

                    //Если человек сидит с мобильнйо версии
                    if ($row_online['user_logged_mobile']) {
                        $mobile_icon = '<img src="/images/spacer.gif" class="mobile_online"  alt=\"\" />';
                    } else {
                        $mobile_icon = '';
                    }

                    if ($row_online['user_last_visit'] >= $online_time) {
                        $params['online'] = $lang['online'] . $mobile_icon;
                    } else {

                        if ($row_online['user_last_visit'] <= 0) {
                            $row_online['user_last_visit'] = 0; //FIXME
                        }

                        if (date('Y-m-d', (int)$row_online['user_last_visit']) == date('Y-m-d', $server_time)) {
                            $dateTell = Langs::lang_date('сегодня в H:i', (int)$row_online['user_last_visit']);
                        } elseif (date('Y-m-d', (int)$row_online['user_last_visit']) == date('Y-m-d', ($server_time - 84600)))
                            $dateTell = Langs::lang_date('вчера в H:i', (int)$row_online['user_last_visit']);
                        else
                            $dateTell = Langs::lang_date('j F Y в H:i', (int)$row_online['user_last_visit']);
                        if ($row['user_sex'] == 2) {
                            $params['online'] = 'последний раз была ' . $dateTell . $mobile_icon;
                        } else {
                            $params['online'] = 'последний раз был ' . $dateTell . $mobile_icon;
                        }
                    }

                    //Конакты
                    $xfields = xfieldsdataload($row['user_xfields']);
//                    $preg_safq_name_exp = explode(', ', 'phone, vk, od, skype, fb, icq, site');
//                    foreach($preg_safq_name_exp as $preg_safq_name){
//                        if(isset($xfields[$preg_safq_name])){
//                            $params['not_contact'.$preg_safq_name] = true;
//                        } else{
//                            $params['not_contact'.$preg_safq_name] = false;
//                        }
//                    }
                    if (isset($xfields['vk']))
                        $params['vk'] = '<a href="' . stripslashes($xfields['vk']) . '" target="_blank">' . stripslashes($xfields['vk']) . '</a>';
                    else
                        $params['vk'] = '';
                    if (isset($xfields['od']))
                        $params['od'] = '<a href="' . stripslashes($xfields['od']) . '" target="_blank">' . stripslashes($xfields['od']) . '</a>';
                    else
                        $params['od'] = '';
                    if (isset($xfields['fb']))
                        $params['fb'] = '<a href="' . stripslashes($xfields['fb']) . '" target="_blank">' . stripslashes($xfields['fb']) . '</a>';
                    else
                        $params['fb'] = '';
                    if (isset($xfields['skype']))
                        $params['skype'] = stripslashes($xfields['skype']);
                    else
                        $params['skype'] = '';
                    if (isset($xfields['icq']))
                        $params['icq'] = stripslashes($xfields['icq']);
                    else
                        $params['icq'] = '';
                    if (isset($xfields['phone']))
                        $params['phone'] = stripslashes($xfields['phone']);
                    else
                        $params['phone'] = '';

                    if (!empty($xfields['site'])) {
                        if (preg_match('/https:\/\//i', $xfields['site'])) {
                            if (preg_match('/\.ru|\.com|\.net|\.su|\.in\.ua|\.ua/i', $xfields['site'])) {
                                //                                $tpl->set('{site}', '<a href="' . stripslashes($xfields['site']) . '" target="_blank">' . stripslashes($xfields['site']) . '</a>');
                                $params['phone'] = '<a href="' . stripslashes($xfields['site']) . '" target="_blank">' . stripslashes($xfields['site']) . '</a>';
                            } else {
                                //                                $tpl->set('{site}', stripslashes($xfields['site']));
                                $params['site'] = stripslashes($xfields['site']);
                            }
                        } else {
                            $params['site'] = 'https://' . stripslashes($xfields['site']);
                        }
                    } else {
                        $params['site'] = '';
                    }

                    if (empty($xfields['vk'])
                        && empty($xfields['od'])
                        && empty($xfields['fb'])
                        && empty($xfields['skype'])
                        && empty($xfields['icq'])
                        && empty($xfields['phone'])
                        && empty($xfields['site'])) {
                        $params['not_block_contact'] = false;
                    } else {
                        $params['not_block_contact'] = true;
                    }

                    //Интересы
                    $xfields_all = xfieldsdataload($row['user_xfields_all']);
                    $preg_safq_name_exp = explode(', ', 'activity, interests, myinfo, music, kino, books, games, quote');

                    if (empty($xfields_all['activity']) and empty($xfields_all['interests'])
                        and empty($xfields_all['myinfo']) and empty($xfields_all['music'])
                        and empty($xfields_all['kino']) and empty($xfields_all['books'])
                        and empty($xfields_all['games']) and empty($xfields_all['quote'])) {
                        $params['not_block_info'] = false;
                    } else {
                        $params['not_block_info'] = true;
                    }

                    foreach ($preg_safq_name_exp as $preg_safq_name) {
                        if (!empty($xfields_all[$preg_safq_name])) {
                            $params['not_info_' . $preg_safq_name] = true;
                        } else {
                            $params['not_info_' . $preg_safq_name] = false;
                        }
                    }

                    //                    $tpl->set('{activity}', nl2br(stripslashes($xfields_all['activity'])));
                    //                    $params['activity'] = nl2br(stripslashes($xfields_all['activity']));
                    //                    $tpl->set('{interests}', nl2br(stripslashes($xfields_all['interests'])));
                    if (!empty($xfields_all['myinfo'])) {
                        //                        $tpl->set('{myinfo}', nl2br(stripslashes($xfields_all['myinfo'])));
                        $params['myinfo'] = nl2br(stripslashes($xfields_all['myinfo']));
                    } else {
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
                    $params['lastname'] = $user_name_lastname_exp[1];
                    //                    $tpl->set('{lastname}', $user_name_lastname_exp[1]);

                    //День рождение
                    $user_birthday = explode('-', $row['user_birthday']);
                    $row['user_day'] = $user_birthday[2];
                    $row['user_month'] = $user_birthday[1];
                    $row['user_year'] = $user_birthday[0];
                    if ($row['user_day'] > 0 && $row['user_day'] <= 31 && $row['user_month'] > 0 && $row['user_month'] < 13) {
                        $params['not_all_birthday_block'] = true;
                        if ($row['user_day'] && $row['user_month'] && $row['user_year'] > 1929 && $row['user_year'] < 2012) {
                            //                            $tpl->set('{birth-day}', '<a href="/?go=search&day='.$row['user_day'].'&month='.$row['user_month'].'&year='.$row['user_year'].'" onClick="Page.Go(this.href); return false">'.langdate('j F Y', strtotime($row['user_year'].'-'.$row['user_month'].'-'.$row['user_day'])).' г.</a>');
                            $params['birth_day'] = '<a href="/?go=search&day=' . $row['user_day'] . '&month=' . $row['user_month'] . '&year=' . $row['user_year'] . '" onClick="Page.Go(this.href); return false">' . Langs::lang_date('j F Y', strtotime($row['user_year'] . '-' . $row['user_month'] . '-' . $row['user_day'])) . ' г.</a>';
                        } else {
                            $params['birth_day'] = '<a href="/?go=search&day=' . $row['user_day'] . '&month=' . $row['user_month'] . '" onClick="Page.Go(this.href); return false">' . Langs::lang_date('j F', strtotime($row['user_year'] . '-' . $row['user_month'] . '-' . $row['user_day'])) . '</a>';
                        }
                    } else {
                        $params['not_all_birthday_block'] = false;
                    }

                    //Показ скрытых текста только для владельца страницы
                    if ($user_info['user_id'] == $row['user_id']) {
                        $params['owner'] = true;
                    } else {
                        $params['owner'] = false;
                    }

                    // FOR MOBILE VERSION 1.0
                    if ($config['temp'] == 'mobile') {
                        $avaPREFver = '50_';
                        $noAvaPrf = 'no_ava_50.png';
                    } else {
                        $avaPREFver = '';
                        $noAvaPrf = 'no_ava.gif';
                    }

                    /**
                     * Аватарка
                     */
                    if ($row['user_photo']) {
                        $params['ava'] = $config['home_url'] . 'uploads/users/' . $row['user_id'] . '/' . $avaPREFver . $row['user_photo'];
                        $params['display_ava'] = 'style="display:block;"';
                    } else {
                        $params['ava'] = '/images/' . $noAvaPrf;
                        $params['display_ava'] = 'style="display:none;"';
                    }

                    /**
                     * Альбомы
                     */
                    if ($user_id == $id) {
                        $albums_privacy = '';
                        $albums_count['cnt'] = $row['user_albums_num'];
                    } else if ($CheckFriends) {
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) regexp '[[:<:]](1|2)[[:>:]]'";
                        $albums_count = $Profile->albums_count($id, $albums_privacy, 1);
                        $cache_pref = "_friends";
                    } else {
                        $albums_privacy = "AND SUBSTRING(privacy, 1, 1) = 1";
                        $albums_count = $Profile->albums_count($id, $albums_privacy, 2);
                        $cache_pref = "_all";
                    }

                    if (!isset($cache_pref))
                        $cache_pref = null;

                    $sql_albums = $Profile->row_albums($id, $albums_privacy, $cache_pref);//cache_pref undefined
                    unset($key2);
                    if ($sql_albums and $config['album_mod'] == 'yes') {
                        foreach ($sql_albums as $key2 => $row_albums) {
                            $sql_albums[$key2]['name'] = stripslashes($row_albums['name']);
                            $sql_albums[$key2]['date'] = Date::megaDate($row_albums['adate']);
                            $titles = array('фотография', 'фотографии', 'фотографий');//photos
                            $sql_albums[$key2]['albums_photonums'] = Gramatic::declOfNum($row_albums['photo_num'], $titles);
                            if ($row_albums['cover']) {
                                $sql_albums[$key2]['album_cover'] = "/uploads/users/{$id}/albums/{$row_albums['aid']}/c_{$row_albums['cover']}";
                            } else {
                                $sql_albums[$key2]['album_cover'] = '/images/no_cover.png';
                            }
                        }
                        $params['albums_num'] = $albums_count['cnt'];
                        $params['albums'] = $sql_albums;
                    } else
                        $params['albums'] = false;

                    //Делаем проверки на существования запрашиваемого юзера у себя в друзьяз, заклаках, в подписка, делаем всё это если страницу смотрет другой человек
                    if ($user_id != $id) {
                        $check_yes_demands = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$id}' AND from_user_id = '{$user_info['user_id']}'");
                        if (isset($check_yes_demands['for_user_id'])) {
                            $params['yesf'] = true;
                        } else {
                            $params['yesf'] = false;
                        }

                        $check_yes_demands = $db->super_query("SELECT for_user_id FROM `friends_demands` WHERE for_user_id = '{$user_info['user_id']}' AND from_user_id = '{$id}'");
                        if (isset($check_yes_demands['for_user_id'])) {
                            $params['yes_friend'] = true;
                        } else {
                            $params['yes_friend'] = false;
                        }

                        $CheckFriends = (new \App\Libs\Friends)->CheckFriends($row['user_id']);

                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        if ($CheckFriends == true) {
                            $params['yes_friends'] = true;
                        } else {
                            $params['yes_friends'] = false;
                        }


                        //Проверка естьли запрашиваемый юзер в закладках у юзера который смотрит стр
                        $check_fave = $Profile->check_fave($id, $user_info['user_id']);
                        if ($check_fave) {
                            $params['yes_fave_block'] = true;
                            $params['no_fave_block'] = false;
                        } else {
                            $params['yes_fave_block'] = false;
                            $params['no_fave_block'] = true;
                        }

                        //Проверка естьли запрашиваемый юзер в подписках у юзера который смотрит стр
                        $check_subscr = $Profile->check_subscr($id, $user_info['user_id']);
                        if ($check_subscr) {
                            $params['yes_subscription'] = true;
                        } else {
                            $params['yes_subscription'] = false;
                        }

                        //Проверка естьли запрашиваемый юзер в черном списке
                        $MyCheckBlackList = (new \App\Libs\Friends)->CheckBlackList($id);
                        if ($MyCheckBlackList) {
                            $params['yes_blacklist_block'] = true;
                            $params['no_$server_time'] = false;
                        } else {
                            $params['yes_blacklist_block'] = false;
                            $params['no_blacklist_block'] = true;
                        }

                    }
//                    else{
//                                 }
                    $author_info = explode(' ', $row['user_search_pref']);
                    $params['gram_name'] = Gramatic::gramatikName($author_info[0]);

                    //Стена
                    //                    $tpl->set('{records}', $tpl->result['wall']);
                    //                    $params['records'] = $tpl->result['wall'];

                    if (!$CheckBlackList and !$params['owner']) {
                        $params['status_text'] = stripslashes($row['user_status']);

                    } elseif ($params['owner']) {
                        $params['status_text'] = '<div><a href="/" id="new_status" onClick="gStatus.open(); return false">' . stripslashes($row['user_status'] . '</a></div>');

                    }

                    if ($row['user_status']) {
                        $params['status_block'] = 'class="no_display"';
                        $params['status_block2'] = '<div class="button_div_gray fl_r status_but margin_left"><button>Отмена</button></div>';
                    } else {
                        $params['status_block'] = '';
                        $params['status_block2'] = '';
                    }
                    //Приватность сообщений
                    if ($user_privacy['val_msg'] == 1 or $user_privacy['val_msg'] == 2 and $CheckFriends and !$CheckBlackList) {
                        $params['privacy_msg'] = '<a href="#" onClick="messages.new_(' . $params['user_id'] . '); return false">
                                        <svg class="bi bi-envelope" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M14 3H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2z"/>
                                            <path d="M.05 3.555C.017 3.698 0 3.847 0 4v.697l5.803 3.546L0 11.801V12c0 .306.069.596.192.856l6.57-4.027L8 9.586l1.239-.757 6.57 4.027c.122-.26.191-.55.191-.856v-.2l-5.803-3.557L16 4.697V4c0-.153-.017-.302-.05-.445L8 8.414.05 3.555z"/>
                                        </svg>
                                        <span>Написать сообщение</span></a>';
                    } else {
                        $params['privacy_msg'] = '';
                    }

                    //Приватность информации
                    if ($user_privacy['val_info'] == 1 or $user_privacy['val_info'] == 2 and $CheckFriends or $user_id == $id) {
                        $params['privacy_info'] = true;
                    } else {
                        $params['privacy_info'] = false;
                    }

                    //Семейное положение
                    $user_sp = explode('|', $row['user_sp']);
                    if (isset($user_sp['1'])) {
                        $rowSpUserName = $Profile->user_sp((int)$user_sp['1']);
                        if ($row['user_sex'] == 1) $check_sex = 2;
                        if ($row['user_sex'] == 2) $check_sex = 1;
                        if ($rowSpUserName['user_sp'] == $user_sp['0'] . '|' . $id or $user_sp['0'] == 5 and $rowSpUserName['user_sex'] == $check_sex) {
                            $spExpName = explode(' ', $rowSpUserName['user_search_pref']);
                            $spUserName = $spExpName['0'] . ' ' . $spExpName['1'];
                        }
                    }

                    if (!isset($spUserName))
                        $spUserName = '';

                    if ($row['user_sex'] == 1) {
                        $sp1 = '<a href="/search/?sp=1" onClick="Page.Go(this.href); return false">не женат</a>';
                        $sp2 = "подруга <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp2_2 = '<a href="/search/?sp=2" onClick="Page.Go(this.href); return false">есть подруга</a>';
                        $sp3 = "невеста <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp3_3 = '<a href="/search/?sp=3" onClick="Page.Go(this.href); return false">помовлен</a>';
                        $sp4 = "жена <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp4_4 = '<a href="/search/?sp=4" onClick="Page.Go(this.href); return false">женат</a>';
                        $sp5 = "любимая <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp5_5 = '<a href="/search/?sp=5" onClick="Page.Go(this.href); return false">влюблён</a>';
                    }
                    if ($row['user_sex'] == 2) {
                        $sp1 = '<a href="/search/?sp=1" onClick="Page.Go(this.href); return false">не замужем</a>';
                        $sp2 = "друг <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp2_2 = '<a href="/search/?sp=2" onClick="Page.Go(this.href); return false">есть друг</a>';
                        $sp3 = "жених <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp3_3 = '<a href="/search/?sp=3" onClick="Page.Go(this.href); return false">помовлена</a>';
                        $sp4 = "муж <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp4_4 = '<a href="/search/?sp=4" onClick="Page.Go(this.href); return false">замужем</a>';
                        $sp5 = "любимый <a href=\"/u{$user_sp['1']}\" onClick=\"Page.Go(this.href); return false\">{$spUserName}</a>";
                        $sp5_5 = '<a href="/search/?sp=5" onClick="Page.Go(this.href); return false">влюблена</a>';
                    }
                    if (!isset($spUserName))
                        $spUserName = 'erorr';//bug

                    if (!isset($user_sp['1']))
                        $user_sp['1'] = ''; //FIXME

                    $sp6 = "партнёр <a href=\"/u" . $user_sp['1'] . "\" onClick=\"Page.Go(this.href); return false\">" . $spUserName . "</a>";
                    $sp6_6 = '<a href="/search/?sp=6" onClick="Page.Go(this.href); return false">всё сложно</a>';

                    if ($user_sp[0] == 1) {
                        $params['sp'] = $sp1;
                    } else if ($user_sp[0] == 2)
                        if ($spUserName) {
                            $params['sp'] = $sp2;
                        } else {
                            $params['sp'] = $sp2_2;
                        }
                    else if ($user_sp[0] == 3)
                        if ($spUserName) {
                            $params['sp'] = $sp3;
                        } else {
                            $params['sp'] = $sp3_3;
                        }
                    else if ($user_sp[0] == 4)
                        if ($spUserName) {
                            $params['sp'] = $sp4;
                        } else {
                            $params['sp'] = $sp4_4;
                        }
                    else if ($user_sp[0] == 5)
                        if ($spUserName) {
                            $params['sp'] = $sp5;
                        } else {
                            $params['sp'] = $sp5_5;
                        }
                    else if ($user_sp[0] == 6)
                        if ($spUserName) {
                            $params['sp'] = $sp6;
                        } else {
                            $params['sp'] = $sp6_6;
                        }
                    else if ($user_sp[0] == 7) {
                        $params['sp'] = '<a href="/search/?sp=7" onClick="Page.Go(this.href); return false">в активном поиске</a>';
                    } else {
                        $params['sp'] = false;
                    }

                    //ЧС
                    if ($CheckBlackList) {
                        $params['blacklist'] = true;
                    } else {
                        $params['blacklist'] = false;
                    }

                    //################### Подарки ###################//
                    if ($row['user_gifts'] > 0 and !$CheckBlackList) {
                        $sql_gifts = $Profile->gifts($id);
                        $titles = array('подарок', 'подарка', 'подарков');//gifts
                        $params['gifts_num'] = $row['user_gifts'] . ' ' . Gramatic::declOfNum($row['user_gifts'], $titles);
                        $params['gifts'] = $sql_gifts;
                    } else {
                        $params['gifts'] = false;
                    }

                    /**
                     * Сообщества
                     */
                    if ($row['user_public_num'] > 0 and !$CheckBlackList) {
                        $sql_groups = $Profile->groups($id);
                        if (is_array($sql_groups)) {
                            foreach ($sql_groups as $key => $row_groups) {
                                if (isset($row_groups['adres'])) {
                                    $sql_groups[$key]['adres'] = $row_groups['adres'];
                                } else {
                                    $sql_groups[$key]['adres']
                                        = 'public' . $row_groups['id'];
                                }
                                if (isset($row_groups['photo'])) {
                                    $sql_groups[$key]['ava'] = "/uploads/groups/{$row_groups['id']}/50_{$row_groups['photo']}";
                                } else {
                                    $sql_groups[$key]['ava'] = "/images/no_ava_50.png";
                                }
                                $row_groups['info'] = iconv_substr($row_groups['status_text'], 0, 24, 'utf-8');
                                //                            $groups .= '<div class="onesubscription onesubscriptio2n cursor_pointer" onClick="Page.Go(\'/'.$adres.'\')"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false"><img src="'.$ava_groups.'" /></a><div class="onesubscriptiontitle"><a href="/'.$adres.'" onClick="Page.Go(this.href); return false">'.stripslashes($row_groups['title']).'</a></div><span class="color777 size10">'.stripslashes($row_groups['status_text']).'</span></div>';
                                $sql_groups[$key]['user_id'] = $row_groups['id'];
                                $sql_groups[$key]['name'] = $row_groups['title'];
                                $sql_groups[$key]['title'] = $row_groups['title'];
                            }
                        } else {
                            $params['groups'] = false;
                        }

                        $params['groups'] = $sql_groups;
                        $params['groups_num'] = $row['user_public_num'];
                    } else {
                        $params['groups'] = false;
                    }

                    /**
                     * Праздники друзей
                     */
                    if (!isset($cnt_happfr))
                        $cnt_happfr = null;

                    if ($cnt_happfr and $params['owner'] == true) {
                        $params['happy-friends'] = '';
                        $params['happy-friends-num'] = $cnt_happfr;
                        $params['happy_friends_block'] = true;
                    } else {
                        $params['happy_friends_block'] = false;
                    }

                    //################### Обработка дополнительных полей ###################//
//                    $xfieldsdata = xfieldsdataload($row['xfields']);
//                    $xfields = profileload();

//                    foreach ($xfields as $value) {
//                        $preg_safe_name = preg_quote($value[0], "'");
//                        if (empty($xfieldsdata[$value[0]])) {
                    //                            $tpl->copy_template = preg_replace("'\\[xfgiven_{$preg_safe_name}](.*?)\\[/xfgiven_{$preg_safe_name}]'is", "", $tpl->copy_template);

//                        } else {
                    //                            $tpl->copy_template = str_replace("[xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);
                    //                            $tpl->copy_template = str_replace("[/xfgiven_{$preg_safe_name}]", "", $tpl->copy_template);
//                        }
                    //                        $tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}]'i", stripslashes($xfieldsdata[$value[0]]), $tpl->copy_template);
//                    }

                    //what? (deprecated)
                    //                    if($id == 7) $tpl->set('{group}', '<font color="#f87d7d">Модератор</font>');
                    //else $tpl->set('{group}', '');

                    //Rating
                    if ($row['user_rating'] > 1000) {
                        $params['rating_class_left'] = 'profile_rate_1000_left';
                        $params['rating_class_right'] = 'profile_rate_1000_right';
                        $params['rating_class_head'] = 'profile_rate_1000_head';
                    } elseif ($row['user_rating'] > 500) {
                        $params['rating_class_left'] = 'profile_rate_500_left';
                        $params['rating_class_right'] = 'profile_rate_500_right';
                        $params['rating_class_head'] = 'profile_rate_500_head';
                    } else {
                        $params['rating_class_left'] = '';
                        $params['rating_class_right'] = '';
                        $params['rating_class_head'] = '';
                    }

                    if (!$row['user_rating'])
                        $row['user_rating'] = 0;

                    //                    $tpl->set('{rating}', $row['user_rating']);
                    $params['rating'] = $row['user_rating'];
                    //                    $tpl->compile('content');


                    //TODO to on
                    //Обновляем кол-во посищений на страницу, если юзер есть у меня в друзьях
//                    if($CheckFriends == true)
//                        $Profile->friend_visit($id, $user_info['user_id']);

                    //Вставляем в статистику
                    //!NB optimize generate users stat
                    if ($user_info['user_id'] != $id) {

                        /**
                         * StatsUser::add($id, $user_info['user_id']);
                         * Cron Generate stats
                         */

                        //start old
                        $stat_date = date('Ymd', $server_time);
                        $stat_x_date = date('Ym', $server_time);

                        $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats_log` WHERE user_id = '{$user_info['user_id']}' AND for_user_id = '{$id}' AND date = '{$stat_date}'");

                        if (!$check_user_stat['cnt']) {
                            $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `users_stats` WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            if ($check_stat['cnt'])
                                $db->query("UPDATE `users_stats` SET users = users + 1, views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                            else
                                $db->query("INSERT INTO `users_stats` SET user_id = '{$id}', date = '{$stat_date}', users = '1', views = '1', date_x = '{$stat_x_date}'");
                            $db->query("INSERT INTO `users_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', for_user_id = '{$id}'");
                        } else {
                            $db->query("UPDATE `users_stats` SET views = views + 1 WHERE user_id = '{$id}' AND date = '{$stat_date}'");
                        }
                        //end old
                    }

                }
                $params['title'] = $row['user_search_pref'] . ' | Sura';
                return view('profile.profile', $params);
            } else {
                $params['title'] = $lang['no_infooo'];
                $params['info'] = $lang['no_upage'];
                return view('info.info', $params);
            }

        } else {

            if (isset($row)) {


                $user_name_lastname_exp = explode(' ', $row['user_search_pref']);
                $params['name'] = $user_name_lastname_exp[0];
                $params['lastname'] = $user_name_lastname_exp[1];

                /**
                 * Аватарка
                 */

                $avaPREFver = '';
                $noAvaPrf = 'no_ava.gif';

                if ($row['user_photo']) {
                    $params['ava'] = $config['home_url'] . 'uploads/users/' . $row['user_id'] . '/' . $avaPREFver . $row['user_photo'];
                    $params['display_ava'] = 'style="display:block;"';
                } else {
                    $params['ava'] = '/images/' . $noAvaPrf;
                    $params['display_ava'] = 'style="display:none;"';
                }
            }


            $params['title'] = $row['user_search_pref'] . ' | Sura';
            return view('profile.profile', $params);
        }
    }

    /**
     * @return int
     */
    #[NoReturn] public static function ban(): int
    {
//        $tpl = new Templates();
//        $config = Settings::load();
//        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $user_info = Registry::get('user_info');
        if ($user_info['user_group'] != '1') {
//            $tpl->load_template('profile/profile_baned.tpl');
            if ($user_info['user_ban_date']) {
                return 1;
//                $tpl->set('{date}', langdate('j F Y в H:i', $user_info['user_ban_date']));
            } else {
                return 1;
                //                $tpl->set('{date}', 'Неограниченно');
            }
//            $tpl->compile('main');
//            echo $tpl->result['main'];
        }

        return 1;
    }

    /**
     * @return int
     */
    #[NoReturn] public static function delete(): int
    {
//        $tpl = new Templates();
//        $config = Settings::load();
//        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $user_info = Registry::get('user_info');
        if ($user_info['user_group'] != '1') {
//            $tpl->load_template('profile_deleted.tpl');
//            $tpl->compile('main');
            //echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
//            echo $tpl->result['main'];
            return 1;
        }

        return 1;
    }
}
