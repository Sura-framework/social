<?php

namespace System\Modules;

use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Gramatic;
use System\Libs\Tools;
use System\Libs\Validation;

class AudioController extends Module{

    public function upload_box($params){
        //$tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        //$db = $this->db();
        //$user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            //$offset = $count * $page;
            //$act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            echo <<<HTML
            <div class="audio_upload_cont">
            
            <div class="alert alert-info" role="alert">
                <div class="upload_limits_title" dir="auto">Ограничения</div>
                <ul class="upload_limits_list" dir="auto">
                <li><span>Аудиофайл не должен превышать 200 Мб и должен быть в формате MP3.</span></li>
                <li><span>Аудиофайл не должен нарушать авторские права.</span></li>
                </ul>
            </div>
            
            <div class="audio_upload_but_wrap">
            <div id="audio_choose_wrap">
            <div class="button_div fl_l">
            <button onClick="this.nextSibling.click();">Выбрать файл</button><input type="file" accept="audio/mp3" multiple="true" onChange="audio.onFile(this);" style="display:none;" id="audio_upload_inp">
            </div>
            </div>
            <div class="audio_upload_progress no_display">
            <div class="audio_progress_text">
            <div class="str" id="progress_str">0%</div>
            </div>
            <div class="audio_upload_pr_line">
            <div class="audio_progress_text">
            <div class="str">0%</div>
            </div>
            </div>
            </div>
            <div id="audio_num_download"></div>
            </div>
            <div class="audio_upload_drop">
            <div class="audio_upload_drop_wrap">
            <div class="audio_upload_drop_text bsbb">Отпустите файлы для начала загрузки</div>
            </div>
            <div class="audio_drop_wrap"></div>
            </div>
            </div>
            HTML;
            die();
        }
    }
    public function loadFriends($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $res = array();
            $offset = 6*$page;
            $sql_count_ = $db->super_query("SELECT count(*) as cnt FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_info['user_id']}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND user_audio > '0'");
            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_birthday, user_photo, user_search_pref, user_audio, user_last_visit, user_logged_mobile FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_info['user_id']}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND user_audio > '0' ORDER by `views` DESC LIMIT {$offset}, 6", 1);

            $config = include __DIR__.'/../data/config.php';

            foreach($sql_ as $row){
                $row['user_photo'] = ($row['user_photo']) ? $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo'] : '/templates/'.$config['temp'].'/images/no_ava_50.png';
                $res[] = array('count' => $row['user_audio'],'fid' => $row['friend_id'], 'uid' => $row['friend_id'], 'name' => $row['user_search_pref'], 'ava' => $row['user_photo'], 'js' => 'audio');
            }
            if($res) echo json_encode(array('res' => $res, 'count' => $sql_count_['cnt']));
            else echo json_encode(array('reset' => 1,'res' => $res, 'count' => $sql_count_['cnt']));
            die();
        }
    }
    public function del_audio($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $id = intval($_POST['id']);
            $check = $db->super_query("SELECT oid, url, filename, original, public FROM `".PREFIX."_audio` WHERE id = '{$id}'");
            if($check['public']) $info = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$check['oid']}'");
            if(!$check['public'] && $check['oid'] == $user_info['user_id'] || stripos($info['admin'], "u{$user_info['user_id']}|") !== false){
                $db->query("DELETE FROM `".PREFIX."_audio` WHERE id = '{$id}'");
                if(!$check['public'])
                {$db->query("UPDATE `".PREFIX."_users` SET user_audio = user_audio - 1 WHERE user_id = '{$user_info['user_id']}'");
                    Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);
                } else $db->query("UPDATE `".PREFIX."_communities` SET audio_num = audio_num - 1 WHERE id = '{$check['oid']}'");
                if($check['original']) @$db->query("UPDATE `".PREFIX."_audio` SET add_count = add_count - 1 WHERE id = '{$check['original']}'");
            } else echo 'error';
            die();
        }
    }
    public function add($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $id = intval($_POST['id']);
            $check = $db->super_query("SELECT url, artist, title, duration, filename FROM `".PREFIX."_audio` WHERE id = '{$id}'");
            if($check){
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_audio` SET filename = '{$check['filename']}', original = '{$id}', duration = '{$check['duration']}',oid = '{$user_info['user_id']}', url = '{$db->safesql($check['url'])}', artist = '{$db->safesql($check['artist'])}', title = '{$db->safesql($check['title'])}', date = '{$server_time}'");
                $dbid = $db->insert_id();
                $db->query("UPDATE `".PREFIX."_users` SET user_audio = user_audio + 1 WHERE user_id = '{$user_info['user_id']}'");
                $db->query("UPDATE `".PREFIX."_audio` SET add_count = add_count + 1 WHERE id = '{$id}'");
                Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);
            }
            die();
        }
    }
    public function allMyAudiosBox($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $gcount = 20;
            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $limit_page = ($page-1)*$gcount;

            $sql_ = $db->super_query("SELECT id, url, oid, artist, title, duration FROM `".PREFIX."_audio` WHERE oid = '{$user_info['user_id']}' and public = '0' ORDER by `id` DESC LIMIT {$limit_page}, {$gcount}", 1);

            $count = $db->super_query("SELECT user_audio FROM `".PREFIX."_users` WHERE user_id = '".$user_info['user_id']."'");

            if($count['user_audio']){
                echo '<div id="jquery_jplayer"></div><input type="hidden" id="teck_id" value="0" /><input type="hidden" id="typePlay" value="standart" />';
                $tpl->load_template('/albums/albums_editcover.tpl');
                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $titles = array('песня', 'песни', 'песен');//audio
                $tpl->set('{photo-num}', $count['user_audio'].' '.Gramatic::declOfNum($count['user_audio'], $titles));
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('content');

                $plname = 'attach';
                foreach($sql_ as $row_audio){
                    $stime = gmdate("i:s", $row_audio['duration']);
                    if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                    if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                    $tpl->result['content'] .= <<<HTML
                            <div class="audioPage audioElem" id="audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}" onclick="playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);">
                            <div class="fl_l" style="width: 556px;">
                            <div class="area">
                            <table cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                            <tr>
                            <td>
                            <div class="audioPlayBut new_play_btn"><div class="bl"><div class="figure"></div></div></div>
                            <input type="hidden" value="{$row_audio['url']},{$row_audio['duration']},page" id="audio_url_{$row_audio['id']}_{$row_audio['oid']}_{$plname}">
                            </td>
                            <td class="info">
                            <div class="audioNames"><b class="author" id="artist">{$row_audio['artist']}</b>  –  <span class="name" id="name">{$row_audio['title']}</span> <div class="clear"></div></div>
                            <div class="audioElTime" id="audio_time_{$row_audio['id']}_{$row_audio['oid']}_{$plname}">{$stime}</div>
                            </td>
                            </tr>
                            </tbody>
                            </table>
                            <div id="player{$row_audio['id']}_{$row_audio['oid']}_{$plname}" class="audioPlayer" border="0" cellpadding="0">
                            <table cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                            <tr>
                            <td style="width: 100%;">
                            <div class="progressBar fl_l" style="width: 100%;" onclick="cancelEvent(event);" onmousedown="audio_player.progressDown(event, this);" id="no_play" onmousemove="audio_player.playerPrMove(event, this)" onmouseout="audio_player.playerPrOut()">
                            <div class="audioTimesAP" id="main_timeView"><div class="audioTAP_strlka">100%</div></div>
                            <div class="audioBGProgress"></div>
                            <div class="audioLoadProgress"></div>
                            <div class="audioPlayProgress" id="playerPlayLine"><div class="audioSlider"></div></div>
                            </div>
                            </td>
                            <td>
                            <div class="audioVolumeBar fl_l" onclick="cancelEvent(event);" onmousedown="audio_player.volumeDown(event, this);" id="no_play">
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
                            <div id="no_play" class="fl_r"><div class="cursor_pointer audioMusicBlock" style="font-size: 17px; color: rgb(255, 255, 255); float: right; padding: 4px 2px; background: rgb(92, 122, 153);" id="audioAttach_{$row_audio['id']}" onClick="wall.attach_insert('audio', {aid: {$row_audio['id']}, url: '{$row_audio['url']}', name: '{$row_audio['title']}', artist: '{$row_audio['artist']}', time: {$row_audio['duration']}, stime: '{$stime}', uid: {$row_audio['oid']}}); return false;"><i class="icon-plus-4"></i></div></div>
                            <div class="clear"></div>
                            </div>
                            HTML;
                }
                box_navigation($gcount, $count['user_audio'], $page, 'wall.attach_addaudio', '');

                $tpl->load_template('/albums/albums_editcover.tpl');
                $tpl->set('[bottom]', '');
                $tpl->set('[/bottom]', '');
                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
                $tpl->compile('content');
                Tools::AjaxTpl($tpl);

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            } else echo $lang['audio_box_none'];
            die();
        }
    }
    public function save_edit($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $id = intval($_POST['id']);
            $genre = intval($_POST['genre']);
            $artist = Validation::textfilter($_POST['artist']);
            $title = Validation::textfilter($_POST['name']);
            $text = Validation::textfilter($_POST['text']);
            if($genre > -1 && $genre < 18) $access = true;
            $row = $db->super_query("SELECT id, oid, public FROM `".PREFIX."_audio` WHERE id = '{$id}'");
            if(!$row['public'] && $row['oid'] == $user_info['user_id'] && $access) $db->query("UPDATE `".PREFIX."_audio` SET artist = '{$artist}', title = '{$title}', text = '{$text}', genre = '{$genre}' WHERE id = '{$id}'");
            else if($row['public'] == 1){
                $info = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['oid']}'");
                if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false && $access)
                    $db->query("UPDATE `".PREFIX."_audio` SET artist = '{$artist}', title = '{$title}', text = '{$text}', genre = '{$genre}' WHERE id = '{$id}'");
            }
            die();
        }
    }
    public function upload($params){
        //$tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $count = 40;
            //$page = intval($_REQUEST['page']);
            //$offset = $count * $page;
            //$act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            include __DIR__.'/../../vendor/james-heinrich/getid3/getid3.php';
            $getID3 = new \getID3;
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_name = Gramatic::totranslit($_FILES['file']['name']);
            $server_time = intval($_SERVER['REQUEST_TIME']);
            $file_rename = substr(md5($server_time+rand(1,100000)), 0, 15);
            $file_size = $_FILES['file']['size'];
            $tmp = explode('.', $file_name);
            $file_extension = end($tmp);
            $type = strtolower($file_extension);


            $config = include __DIR__.'/../data/config.php';

            if($type == 'mp3' AND $config['audio_mod_add'] == 'yes' AND $file_size < 200000000){
                $res_type = '.'.$type;
                if(move_uploaded_file($file_tmp, __DIR__.'/../../public/uploads/audio_tmp/'.$file_rename.'.mp3')){
                    $res = $getID3->analyze(__DIR__.'/../../public/uploads/audio_tmp/'.$file_rename.'.mp3');

                    if(!$res['error'] && $res['playtime_seconds']){

                        if($res['tags']['id3v2']){
                            $artist = Validation::textFilter($res['tags']['id3v2']['artist'][0]);
                            $name = Validation::textFilter($res['tags']['id3v2']['title'][0]);
                        } else if($res['tags']['id3v1']){
                            $artist = Validation::textFilter($res['tags']['id3v1']['artist'][0]);
                            $name = Validation::textFilter($res['tags']['id3v1']['title'][0]);
                        }

                        $time_sec = round(str_replace(',','.',$res['playtime_seconds']));

                        $lnk = '/uploads/audio_tmp/'.$file_rename.'.mp3';
                        $db->query("INSERT INTO `".PREFIX."_audio` SET duration = '{$time_sec}', filename = '{$file_rename}{$res_type}', oid = '{$user_info['user_id']}', url = '{$lnk}', artist = '{$artist}', title = '{$name}',  date = '{$server_time}'");
                        $dbid = $db->insert_id();
                        $db->query("UPDATE `".PREFIX."_users` SET user_audio = user_audio + 1 WHERE user_id = '{$user_info['user_id']}'");
                        Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);
                    }

                    //@unlink(ROOT_DIR.'/uploads/audio_tmp/'.$file_rename.'.mp3');
                    echo json_encode(array('status' => 1));
                } else json_encode(array('status' => 0));
            } else json_encode(array('status' => 0));
            die();
        }
    }
    public function search_all($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $pid = intval($_POST['pid']);
            $audios = array();

            $query = $db->safesql(Validation::strip_data(urldecode($_POST['q'])));
            $query = strtr($query, array(' ' => '%'));

            if($pid) $info = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$pid}'");

            $sql_count_ = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_audio` WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%'");

            $plname = 'search';

            $sql_ = $db->super_query("SELECT ".PREFIX."_audio.id, url, artist, title, oid, duration, text, ".PREFIX."_users.user_search_pref FROM ".PREFIX."_audio LEFT JOIN ".PREFIX."_users ON ".PREFIX."_audio.oid = ".PREFIX."_users.user_id WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%' ORDER by add_count,id DESC LIMIT {$offset}, {$count}", 1);
            foreach($sql_ as $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if(!$row['title']) $row['title'] = 'Без названия';
                $audios[] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], $stime, $plname/*'audios'.$row['oid']*/, 'page', ($row['text']) ? 1 : 0);


                if($pid) $function = <<<HTML
                        <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}', {$pid})" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>
                        HTML;
                else $function = <<<HTML
                        <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}')" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>
                        HTML;
                $res = <<<HTML
                        <div class="audio" id="audio_{$row['id']}_{$row['oid']}_{$plname}" onclick="playNewAudio('{$row['id']}_{$row['oid']}_{$plname}', event);">
                        <div class="audio_cont">
                        <div class="play_btn icon-play-4"></div>
                        <div class="name mt-3"><span id="artist" onClick="Page.Go('/?go=search&query=&type=5&q={$row['artist']}')">{$row['artist']}</span> – <span id="name" class="{is_text}" onClick="audio_player.get_text('{$row['id']}_{$row['oid']}_{$plname}', this);">{$row['title']}</span></div>
                        <div class="fl_r">
                        <div class="time" id="audio_time_{$row['id']}_{$row['oid']}_{$plname}">{$stime}</div>
                        <div class="tools">
                        <div class="vk_audio_dl_btn cursor_pointer fl_l" href="{$row['url']}" onclick="vkDownloadFile(this,'{$row['artist']} - {$row['title']} - kalibri.co.ua'); cancelEvent(event);" onMouseOver="myhtml.title('{$row['id']}', 'Скачать песню', 'ddtrack_', 4)" id="ddtrack_{$row['id']}"></div>
                        
                        [tools]
                        <li class="icon-pencil-7" onclick="audio.edit_box('{$row['id']}_{$row['oid']}_{$plname}')" id="edit_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Редактировать аудиозапись', shift:[0,7,0]});"></li>
                        {$function}
                        [/tools]
                        [add]<li class="icon-plus-6" onclick="audio.add('{$row['id']}_{$row['oid']}_{$plname}')" id="add_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Добавить аудиозапись', shift:[0,7,0]});"></li>[/add]
                        <div class="clear"></div>
                        </div>
                        </div>
                        <input type="hidden" value="{$row['url']},{$row['duration']},page" id="audio_url_{$row['id']}_{$row['oid']}_{$plname}"/>
                        <div class="clear"></div>
                        </div>
                        <div id="audio_text_res"></div>
                        </div>
                        HTML;

                if(!$pid && $row['oid'] == $user_info['user_id'] || $pid && stripos($info['admin'], "u{$user_info['user_id']}|") !== false && $row['oid'] == $pid){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;
            }
            echo json_encode(array('search_cnt' => $sql_count_['cnt'], 'audios' => $audios, 'search' => $audios_res));
            die();
        }
    }
    public function load_all($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $uid = intval($_REQUEST['uid']);
            $audios = array();
            if(!$uid) $uid = $user_info['user_id'];
            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `".PREFIX."_audio` WHERE oid = '{$uid}' ORDER by `id` DESC", 1);
            foreach($sql_ as $row){
                if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if(!$row['title']) $row['title'] = 'Без названия';
                $audios['a_'.$row['id']] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], gmdate("i:s", $row['duration']), 'audios'.$row['oid'], 'user_audios', ($row['text']) ? 1 : 0);
            }
            if($audios) echo json_encode(array('loaded' => 1, 'res' => $audios));
            else echo json_encode(array('loaded' => 0));
            die();
        }
    }
    public function load_play_list($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $audios = array();
            $data = explode('_', $_POST['data']);
            $id = $data[0];
            $uid = $data[1];
            $plname = $data[2];
            if($plname == 'publicaudios'.$uid){
                $group = $db->super_query("SELECT audio_num, title FROM `".PREFIX."_communities` WHERE id = '{$uid}'");
                $pname = 'Сейчас играют аудиозаписи '.$group['title'].' | '.$group['audio_num'].' '.Gramatic::declOfNum($group['audio_num'], array('аудиозапись','аудиозаписи','аудиозаписей'));
                $sql_dop = "and public = '1'";
            } elseif($plname == 'popular'){
                $user = $db->super_query("SELECT user_audio, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$uid}'");
                $pname = 'Сейчас играют популярные аудиозаписи';
                $sql_dop = "";
            } else {
                $user = $db->super_query("SELECT user_audio, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$uid}'");
                $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$user['user_audio'].' '.Gramatic::declOfNum($user['user_audio'], array('аудиозапись','аудиозаписи','аудиозаписей'));
                $sql_dop = "and public = '0'";
            }
            if($plname == 'popular') $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `".PREFIX."_audio` ORDER by `add_count` DESC", 1);
            else $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `".PREFIX."_audio` WHERE oid = '{$uid}' {$sql_dop} ORDER by `id` DESC", 1);
            foreach($sql_ as $row){
                if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if(!$row['title']) $row['title'] = 'Без названия';
                $audios[] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], gmdate("i:s", $row['duration']), $plname, 'user_audios', ($row['text']) ? 1 : 0);
            }
            echo json_encode(array('playList' => $audios, 'plname' => 'user_audios', 'pname' => $pname));
            die();
        }
    }
    public function get_text($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $data = explode('_', $_POST['id']);
            $id = $data[0];
            $row = $db->super_query("SELECT text FROM `".PREFIX."_audio` WHERE id = '{$id}'");
            echo $row['text'];
            die();
        }
    }
    public function get_info($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];

            $id = intval($_POST['id']);
            $genres = array(array(0,"Other"),array(1,"Rock"),array(2,"Pop"),array(3,"Rap & Hip-Hop"),array(4,"House & Dance"),array(5,"Alternative"),array(6,"Instrumental"),array(7,"Easy Listening"),array(8,"Metal"),array(9,"Dubstep"),array(10,"Indie Pop"),array(11,"Drum & Bass"),array(12,"Trance"),array(13,"Ethnic"),array(14,"Acoustic & Vocal"),array(15,"Reggae"),array(16,"Classical"),array(17,"Electropop & Disco"));
            $row = $db->super_query("SELECT id, artist, title, text, genre FROM `".PREFIX."_audio` WHERE id = '{$id}'");
            if($row) echo json_encode(array('artist' => $row['artist'],'name' => $row['title'],'genre' => $row['genre'],'text' => $row['text'],'genres' => $genres));
            else echo json_encode(array('error' => 1));
            die();
        }
    }

    public function index($params)
    {
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){

            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;

            $act = $_REQUEST['act'];
            $metatags['title'] = $lang['audio'];



            $path = explode('/', $_SERVER['REQUEST_URI']);
            $uid = ($path['2']);

            if(isset($path['2']) ){
                $type = $path['2'];
            }else{
                $type = null;
            }

            $uid = intval($_REQUEST['uid']);
            if(!$uid) $uid = $user_info['user_id'];

            if($type == 'popular'){
                $sql_dop = "ORDER by `add_count`";
                $plname = 'popular';
            } elseif($type == 'recommendations'){
                $sql_dop = "ORDER by `add_count`";
            } elseif($type == 'feed'){
                $sql_dop = "ORDER by `add_date`";
            } else {
                $sql_dop = "WHERE oid = '{$uid}' and public = '0' ORDER by `id`";
                $plname = 'audios'.$uid;
                $type = 'my_music';
            }

            $audios = array();

            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `".PREFIX."_users` WHERE user_id = '{$uid}'");

            if($user) $sql_count_['cnt'] = $user['user_audio'];
            else $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `".PREFIX."_audio`");
            $jid = 0;

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `".PREFIX."_audio` {$sql_dop} DESC LIMIT {$offset}, {$count}", 1);
            foreach($sql_ as $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if(!$row['title']) $row['title'] = 'Без названия';


                if($row['text']) $is_text = 'text_avilable';
                else $is_text = '';

                $audios['a_'.$row['id']] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], $stime, $plname, 'user_audios', ($row['text']) ? 1 : 0);



                $res = <<<HTML
                <div class="audio" id="audio_{$row['id']}_{$row['oid']}_{$plname}" onclick="playNewAudio('{$row['id']}_{$row['oid']}_{$plname}', event);">
                <div class="audio_cont">
                <div class="play_btn icon-play-4"></div>
                <div class="name mt-3 d-inline-block text-truncate" style="max-width: 65%;">
                <span id="artist" onClick="Page.Go('/?go=search&query=&type=5&q={$row['artist']}')">{$row['artist']}</span> – <span id="name" class="{$is_text}" onClick="audio_player.get_text('{$row['id']}_{$row['oid']}_{$plname}', this);">{$row['title']}</span></div>
                <div class="fl_r">
                <div class="time" id="audio_time_{$row['id']}_{$row['oid']}_{$plname}">{$stime}</div>
                <div class="tools">
                <div class="vk_audio_dl_btn cursor_pointer fl_l" href="{$row['url']}" onclick="vkDownloadFile(this,'{$row['artist']} - {$row['title']} - kalibri.co.ua'); cancelEvent(event);" onMouseOver="myhtml.title('{$row['id']}', 'Скачать песню', 'ddtrack_', 4)" id="ddtrack_{$row['id']}"></div>
                [tools]<li class="icon-pencil-7" onclick="audio.edit_box('{$row['id']}_{$row['oid']}_{$plname}')" id="edit_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Редактировать аудиозапись', shift:[0,7,0]});"></li>
                <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}')" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>[/tools]
                [add]<li class="icon-plus-6" onclick="audio.add('{$row['id']}_{$row['oid']}_{$plname}')" id="add_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Добавить аудиозапись', shift:[0,7,0]});"></li>[/add]
                <div class="clear"></div>
                </div>
                </div>
                <input type="hidden" value="{$row['url']},{$row['duration']},user_audios" id="audio_url_{$row['id']}_{$row['oid']}_{$plname}"/>
                <div class="clear"></div>
                </div>
                <div id="audio_text_res"></div>
                </div>
                HTML;
                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));



            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);

            if($uid == $user_info['user_id'] && $type == 'my_music') $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            else if($uid != $user_info['user_id']) $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';


            if($_POST['doload']){

                echo json_encode(array('result' => $audios_res, 'playList' => $audios, 'pname' => $pname,'title' => $title,'plname' => $plname, 'but' => ($sql_count_['cnt'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : ''));
                die();
            }



            $tpl->load_template('audio/main.html');

            $tpl->set('[is_user]', '');
            $tpl->set('[/is_user]', '');

            $tpl->set('[friends_block]', '');
            $tpl->set('[/friends_block]', '');

            $tpl->set('{'.$type.'-active}', 'active');

            $tpl->set('{plname}', $plname);
            $tpl->set('{public_audios}', 'style="display:none"');
            $tpl->set('{uid}', $uid);
            $tpl->set('{title}', $title);
            $tpl->set('{audios_res}', $audios_res);
            $tpl->set_block("'\\[public\\](.*?)\\[/public\\]'si","");
            $tpl->set('{user_name}', $user['user_search_pref']);


            $tpl->set('{init}', json_encode($audio_json));
            $tpl->compile('content');

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

