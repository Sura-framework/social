<?php

namespace App\Modules;

use Exception;
use getID3;
use Sura\Libs\Langs;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Validation;
use function Sura\resolve;

class AudioController extends Module{

    /**
     *
     * @param $params
     */
    public function upload_box(): void
    {
        $lang = langs::get_langs();
        $logged = $this->logged();
        if($logged){
            $params['title'] = $lang['audio'].' | Sura';
            echo <<<HTML
            <div class="audio_upload_cont">
            <script src="https://sura.qd2.ru/js/Uploader.js"></script>
            <div class="alert alert-info" role="alert">
                <div class="upload_limits_title" dir="auto">Ограничения</div>
                <ul class="upload_limits_list" dir="auto">
                <li><span>Аудиофайл не должен превышать 200 Мб и должен быть в формате MP3.</span></li>
                <li><span>Аудиофайл не должен нарушать авторские права.</span></li>
                </ul>
            </div>
            
            <div class="audio_upload_but_wrap">
            <div id="audio_choose_wrap">
            <div class="">
            <div class="form-file">

                
                <button id="uploadBtn" class="btn btn-large btn-primary d-none">Choose File</button>
                
                <div class="load_photo_but">
                    <div class="button_div fl_l">
                        <button id="upload" onclick="audio.onUpload()">Выбрать файл</button>
                    </div>
                </div>
                
            </div>
            
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
        }
    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function loadFriends(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $count = 40;
            if (isset($request['page'])) {
                $page = (int)$request['page'];
            }
            else {
                $page = 1;
            }
            $params['title'] = $lang['audio'].' | Sura';

            $res = array();
            $offset = 6*$page;
            $sql_count_ = $db->super_query("SELECT count(*) as cnt FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_info['user_id']}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND user_audio > '0'");
            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_birthday, user_photo, user_search_pref, user_audio, user_last_visit, user_logged_mobile FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_info['user_id']}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 AND user_audio > '0' ORDER by `views` DESC LIMIT {$offset}, 6", 1);

            $config = Settings::load();

            foreach($sql_ as $row){
                $row['user_photo'] = ($row['user_photo']) ? $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo'] : '/images/no_ava_50.png';
                $res[] = array('count' => $row['user_audio'],'fid' => $row['friend_id'], 'uid' => $row['friend_id'], 'name' => $row['user_search_pref'], 'ava' => $row['user_photo'], 'js' => 'audio');
            }
            if($res) {
                return _e_json(array(
                	'res' => $res,
	                'count' => $sql_count_['cnt'],
	                'status' => Status::OK,
                ) );
            }
            else {
                return _e_json(array(
                	'reset' => 1,
	                'res' => $res,
	                'count' => $sql_count_['cnt'],
	                'status' => Status::NOT_FOUND,
                ) );
            }
        }else{
	        return _e_json(array(
		        'status' => Status::BAD_LOGGED,
	        ) );
        }
    }

    /**
     * Удаление песни из БД
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function del_audio(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
//            $count = 40;
//            $page = intval($_REQUEST['page']);
//            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];
            $check = $db->super_query("SELECT oid, url, filename, original, public FROM `audio` WHERE id = '{$id}'");
            if($check['public']) {
                $info = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$check['oid']}'");
            }
            if((!$check['public'] && $check['oid'] == $user_info['user_id']) || stripos($info['admin'], "u{$user_info['user_id']}|") !== false){
                $db->query("DELETE FROM `audio` WHERE id = '{$id}'");
                if(!$check['public'])
                {
                    $db->query("UPDATE `users` SET user_audio = user_audio - 1 WHERE user_id = '{$user_info['user_id']}'");
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_info['user_id']}/profile_{$user_info['user_id']}");

                } else {
                    $db->query("UPDATE `communities` SET audio_num = audio_num - 1 WHERE id = '{$check['oid']}'");
                }
                if($check['original'])
                {
                    $db->query("UPDATE `audio` SET add_count = add_count - 1 WHERE id = '{$check['original']}'");
                }
	            $status = Status::OK;
            } else {
//                echo 'error';
	            $status = Status::BAD_RIGHTS;
            }
        }else{
	        $status = Status::BAD_LOGGED;
        }
	    return _e_json(array(
		    'status' => $status,
	    ) );
    }

    /**
     * Отправление песни в БД
     *
     * @throws \Throwable
     */
    public function add(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){

            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];
            $check = $db->super_query("SELECT url, artist, title, duration, filename FROM `audio` WHERE id = '{$id}'");
            if($check){
                $server_time = \Sura\Time\Date::time();
                $db->query("INSERT INTO `audio` SET filename = '{$check['filename']}', original = '{$id}', duration = '{$check['duration']}',oid = '{$user_info['user_id']}', url = '{$db->safesql($check['url'])}', artist = '{$db->safesql($check['artist'])}', title = '{$db->safesql($check['title'])}', date = '{$server_time}'");
//                $dbid = $db->insert_id();
                $db->query("UPDATE `users` SET user_audio = user_audio + 1 WHERE user_id = '{$user_info['user_id']}'");
                $db->query("UPDATE `audio` SET add_count = add_count + 1 WHERE id = '{$id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_info['user_id']}/profile_{$user_info['user_id']}");
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
     * Вывод всех аудио (BOX)
     *
     * @return int
     */
    public function allMyAudiosBox(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $params['title'] = $lang['audio'].' | Sura';

            $gcount = 20;
            if($request['page'] > 0) {
                $page = (int)$request['page'];
            } else {
                $page = 1;
            }
            $limit_page = ($page-1)*$gcount;

            $sql_ = $db->super_query("SELECT id, url, oid, artist, title, duration FROM `audio` WHERE oid = '{$user_info['user_id']}' and public = '0' ORDER by `id` DESC LIMIT {$limit_page}, {$gcount}", 1);

            $count = $db->super_query("SELECT user_audio FROM `users` WHERE user_id = '".$user_info['user_id']."'");

            if($count['user_audio']){
                echo '<div id="jquery_jplayer"></div><input type="hidden" id="teck_id" value="0" /><input type="hidden" id="typePlay" value="standart" />';
//                $tpl->load_template('/albums/albums_editcover.tpl');
//                $tpl->set('[top]', '');
//                $tpl->set('[/top]', '');
                $params['top'] = true;
                $titles = array('песня', 'песни', 'песен');//audio
//                $tpl->set('{photo-num}', $count['user_audio'].' '.Gramatic::declOfNum($count['user_audio'], $titles));
                $params['photo_num'] = $count['user_audio'].' '.Gramatic::declOfNum($count['user_audio'], $titles);
//                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $params['tttt'] =
//                $tpl->compile('content');

                $plname = 'attach';
                foreach($sql_ as $key => $row_audio){
                    $stime = gmdate("i:s", $row_audio['duration']);
                    if(!$row_audio['artist']) $row_audio['artist'] = 'Неизвестный исполнитель';
                    if(!$row_audio['title']) $row_audio['title'] = 'Без названия';
                    $sql_[$key]['content'] = <<<HTML
                            <div class="audioPage audioElem" id="audio_{$row_audio['id']}_{$row_audio['oid']}_{$plname}" onclick="playNewAudio('{$row_audio['id']}_{$row_audio['oid']}_{$plname}', event);">
                            <div class="fl_l" style="width: 556px;">
                            <div class="area">
                            <table cellspacing="0" width="100%">
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
                            <div id="player{$row_audio['id']}_{$row_audio['oid']}_{$plname}" class="audioPlayer" cellpadding="0">
                            <table cellspacing="0" width="100%">
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
                $params['sql_'] = $sql_;
//                box_navigation($gcount, $count['user_audio'], $page, 'wall.attach_addaudio', '');

//                $tpl->load_template('/albums/albums_editcover.tpl');
//                $tpl->set('[bottom]', '');
//                $tpl->set('[/bottom]', '');
                $params['bottom'] = true;
//                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
                $params['top'] = false;
//                $tpl->compile('content');
//                Tools::AjaxTpl($tpl);
//
                return view('audio.editcover', $params);
            } else {
                echo $lang['audio_box_none'];
            }
        }
        return view('info.info', $params);
    }
	
	/**
	 * Сохранение отредактированых данных
	 *
	 * @throws \JsonException
	 */
    public function save_edit(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $count = 40;
//            $page = intval($_REQUEST['page']);
//            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $id = (int)$request['id'];
            $genre = (int)$request['genre'];
            $artist = Validation::textfilter($_POST['artist']);
            $title = Validation::textfilter($_POST['name']);
            $text = Validation::textfilter($_POST['text']);
            if($genre > -1 && $genre < 18) {
                $access = true;
            }else{
                $access = false;
            }
            $row = $db->super_query("SELECT id, oid, public FROM `audio` WHERE id = '{$id}'");
            if(!$row['public'] && $row['oid'] == $user_info['user_id'] && $access) {
                $db->query("UPDATE `audio` SET artist = '{$artist}', title = '{$title}', text = '{$text}', genre = '{$genre}' WHERE id = '{$id}'");
            }
            else if($row['public'] == 1){
                $info = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['oid']}'");
                if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false && $access) {
                    $db->query("UPDATE `audio` SET artist = '{$artist}', title = '{$title}', text = '{$text}', genre = '{$genre}' WHERE id = '{$id}'");
                }
            }
	        $status = Status::OK;
        }else{
	        $status = Status::BAD_LOGGED;
        }
        //TODO response update
	    return _e_json(array(
		    'status' => $status,
	    ) );
    }

    /**
     * Загрузка с компьютера
     *
     * @return int
     * @throws \Throwable
     */
    public function upload(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

//        var_dump($_FILES);

        if($logged){
            $count = 40;
            //$page = intval($_REQUEST['page']);
            //$offset = $count * $page;
            //$act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            if (!file_exists($_FILES['uploadfile']['tmp_name']))
            {

                return _e( json_encode(array('status' => 5)) );
            }

            $dir = resolve('app')->get('path.base');
            include $dir.'/vendor/james-heinrich/getid3/getid3/getid3.php';
            $getID3 = new getID3;
            $file_tmp = $_FILES['uploadfile']['tmp_name'];
            $file_name = Gramatic::totranslit($_FILES['uploadfile']['name']);
            $server_time = \Sura\Time\Date::time();
            $file_rename = substr(md5($server_time+rand(1,100000)), 0, 15);
            $file_size = $_FILES['uploadfile']['size'];
            $tmp = explode('.', $file_name);
            $file_extension = end($tmp);
            $type = strtolower($file_extension);

            $config = Settings::load();

            if($type == 'mp3' AND $config['audio_mod_add'] == 'yes'){
                if ($file_size < 200000000){
                    $res_type = '.'.$type;

//                $file_tmp = file_get_contents($_FILES['uploadfile']['tmp_name']);

                    if(move_uploaded_file($file_tmp, $dir.'/public/uploads/audio/'.$file_rename.'.mp3')){
                        $res = $getID3->analyze($dir.'/public/uploads/audio/'.$file_rename.'.mp3');
                        $check_res = in_array("tags", $res);
//                    if($check_res){

                        if($check_res == true AND $res['tags']['id3v2']){
                            $artist = Validation::textFilter($res['tags']['id3v2']['artist']['0']);
                            $name = Validation::textFilter($res['tags']['id3v2']['title']['0']);
                        } else if($check_res == true AND $res['tags']['id3v1']){
                            $artist = Validation::textFilter($res['tags']['id3v1']['artist']['0']);
                            $name = Validation::textFilter($res['tags']['id3v1']['title']['0']);
                        }else{
                            $artist = 'Неизвестный исполнитель';
                            $name = 'Без названия';
                        }

                        $time_sec = round(str_replace(',','.',$res['playtime_seconds']));

                        $lnk = '/uploads/audio/'.$file_rename.'.mp3';
                        $db->query("INSERT INTO `audio` SET duration = '{$time_sec}', filename = '{$file_rename}{$res_type}', oid = '{$user_info['user_id']}', url = '{$lnk}', artist = '{$artist}', title = '{$name}',  date = '{$server_time}'");
//                        $dbid = $db->insert_id();
                        $db->query("UPDATE `users` SET user_audio = user_audio + 1 WHERE user_id = '{$user_info['user_id']}'");


                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $cache->remove("{$user_info['user_id']}/profile_{$user_info['user_id']}");

                        return _e( json_encode(array('status' => Status::OK)) );
//                    }else{
//                        echo json_encode(array('status' => 0));
//                    }
                        //@unlink(ROOT_DIR.'/uploads/audio/'.$file_rename.'.mp3');
                    } else{
                        return _e(json_encode(
                            array(
                                'status' => Status::BAD_MOVE,
                                'file' => $dir.'/public/uploads/audio/'.$file_rename.'.mp3'
                            )
                        ));
                    }

                } else{
                    $status = Status::BIG_SIZE;
                    return _e( json_encode(array('status' => $status)) );
                }

            } else{
	            $status = Status::BAD_RIGHTS;
            	return _e( json_encode(array('status' => $status)) );
            }
        }else{
	        $status = Status::BAD_LOGGED;
	        return _e( json_encode(array('status' => $status)) );
        }
    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function search_all(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if ($logged) {
            $count = 40;
            $page = (int)$request['page'];
            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'] . ' | Sura';

            $pid = (int)$request['pid'];
            $audios = array();

            $query = $db->safesql(Validation::strip_data(urldecode($request['q'])));
            $query = strtr($query, array(' ' => '%'));

            if ($pid) $info = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$pid}'");

            $sql_count_ = $db->super_query("SELECT COUNT(*) AS cnt FROM `audio` WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%'");

            $plname = 'search';

            $sql_ = $db->super_query("SELECT audio.id, url, artist, title, oid, duration, text, users.user_search_pref FROM audio LEFT JOIN users ON audio.oid = users.user_id WHERE MATCH (title, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR title LIKE '%{$query}%' ORDER by add_count,id DESC LIMIT {$offset}, {$count}", 1);
            foreach ($sql_ as $row) {
                $stime = gmdate("i:s", $row['duration']);
                if (!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if (!$row['title']) $row['title'] = 'Без названия';
                $audios[] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], $stime, $plname/*'audios'.$row['oid']*/, 'page', ($row['text']) ? 1 : 0);


                if ($pid) {
                    $function = <<<HTML
                        <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}', {$pid})" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>
                        HTML;
                }
                else {
                    $function = <<<HTML
                        <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}')" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>
                        HTML;
                }
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

                if (!$pid && $row['oid'] == $user_info['user_id'] || $pid && stripos($info['admin'], "u{$user_info['user_id']}|") !== false && $row['oid'] == $pid) {
                    $res = str_replace(array('[tools]', '[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]', '[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);

                    $audios_res = '';
                    $audios_res .= $res;
                }
	            $status = Status::BAD_LOGGED;
            }
            return _e(json_encode(array(
                'search_cnt' => $sql_count_['cnt'],
                'audios' => $audios,
                'search' => $audios_res
                )
            ));
        }else{
	        $status = Status::BAD_LOGGED;
            //FIXME
            return _e_json(array(
                'status' => $status,
            ) );
        }

    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function load_all(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $params['title'] = $lang['audio'].' | Sura';
            $uid = (int)$request['uid'];
            $audios = array();
            if(!$uid) {
                $uid = $user_info['user_id'];
            }
            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` WHERE oid = '{$uid}' ORDER by `id` DESC", 1);
            foreach($sql_ as $row){
                if(!$row['artist']) {
                    $row['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']) {
                    $row['title'] = 'Без названия';
                }
                $audios['a_'.$row['id']] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], gmdate("i:s", $row['duration']), 'audios'.$row['oid'], 'user_audios', ($row['text']) ? 1 : 0);
            }
            if($audios) {
	            $status = Status::BAD_LOGGED;
                return _e( json_encode(array('loaded' => 1, 'res' => $audios), JSON_THROW_ON_ERROR) );
            }
            else {
	            $status = Status::BAD_LOGGED;
                //FIXME
                return _e( json_encode(array('loaded' => 0), JSON_THROW_ON_ERROR) );
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
    public function load_play_list(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $count = 40;
//            $page = intval($_REQUEST['page']);
//            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $audios = array();
            $data = explode('_', $request['data']);
//            $id = $data[0];
            $uid = $data[1];
            $plname = $data[2];
            if($plname == 'publicaudios'.$uid){
                $group = $db->super_query("SELECT audio_num, title FROM `communities` WHERE id = '{$uid}'");
                $pname = 'Сейчас играют аудиозаписи '.$group['title'].' | '.$group['audio_num'].' '.Gramatic::declOfNum($group['audio_num'], array('аудиозапись','аудиозаписи','аудиозаписей'));
                $sql_dop = "and public = '1'";
            } elseif($plname == 'popular'){
//                $user = $db->super_query("SELECT user_audio, user_search_pref FROM `users` WHERE user_id = '{$uid}'");
                $pname = 'Сейчас играют популярные аудиозаписи';
                $sql_dop = "";
            } else {
                $user = $db->super_query("SELECT user_audio, user_search_pref FROM `users` WHERE user_id = '{$uid}'");
                $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$user['user_audio'].' '.Gramatic::declOfNum($user['user_audio'], array('аудиозапись','аудиозаписи','аудиозаписей'));
                $sql_dop = "and public = '0'";
            }
            if($plname == 'popular') {
                $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` ORDER by `add_count` DESC", 1);
            }
            else {
                $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` WHERE oid = '{$uid}' {$sql_dop} ORDER by `id` DESC", 1);
            }
            foreach($sql_ as $row){
                if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                if(!$row['title']) $row['title'] = 'Без названия';
                $audios[] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], gmdate("i:s", $row['duration']), $plname, 'user_audios', ($row['text']) ? 1 : 0);
            }
	        $status = Status::BAD_LOGGED;
            //FIXME
            return _e( json_encode(array('playList' => $audios, 'plname' => 'user_audios', 'pname' => $pname)) );
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
    public function get_text(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $count = 40;
//            $page = intval($_REQUEST['page']);
//            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $data = explode('_', $request['id']);
            $id = $data[0];
            $row = $db->super_query("SELECT text FROM `audio` WHERE id = '{$id}'");
            return _e( $row['text']);
        }
    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function get_info(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $count = 40;
            $page = (int)$request['page'];
//            $offset = $count * $page;
//            $act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $id = (int)$request['id'];
            $genres = array(array(0,"Other"),array(1,"Rock"),array(2,"Pop"),array(3,"Rap & Hip-Hop"),array(4,"House & Dance"),array(5,"Alternative"),array(6,"Instrumental"),array(7,"Easy Listening"),array(8,"Metal"),array(9,"Dubstep"),array(10,"Indie Pop"),array(11,"Drum & Bass"),array(12,"Trance"),array(13,"Ethnic"),array(14,"Acoustic & Vocal"),array(15,"Reggae"),array(16,"Classical"),array(17,"Electropop & Disco"));
            $row = $db->super_query("SELECT id, artist, title, text, genre FROM `audio` WHERE id = '{$id}'");
            if($row) {
	            $status = Status::OK;
                return _e( json_encode(array('artist' => $row['artist'],'name' => $row['title'],'genre' => $row['genre'],'text' => $row['text'],'genres' => $genres,'status' => $status)) );
            }
            else {
	            $status = Status::NOT_FOUND;
                return _e( json_encode(array('error' => $status)) );
            }
        }else{
            //FIXME
	        $status = Status::BAD_LOGGED;
            return _e_json(array(
                'status' => $status,
            ) );
        }

    }

    /**
     *
     * @throws \JsonException
     */
    public function my_music(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $count = 40;
            if (isset($request['page'])) {
                $page = (int)$request['page'];
            }
            else {
                $page = 0;
            }
            $offset = $count * $page;
            $params['title'] = $lang['audio'].' | Sura';
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $uid = ($path['2']);
            $type = $path['2'] ?? null;
            if (isset($request['uid'])) {
                $uid = (int)$request['uid'];
            }
            else {
                $uid = $user_info['user_id'];
            }

            $sql_dop = "WHERE oid = '{$uid}' and public = '0' ORDER by `id`";
            $plname = 'audios'.$uid;
            $type = 'my_music';

            $audios = array();
            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `users` WHERE user_id = '{$uid}'");

//            if($user)
//                $sql_count_['cnt'] = $user['user_audio'];
//            else
            $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `audio` WHERE oid = '{$uid}'");
//            $jid = 0;

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` {$sql_dop} DESC LIMIT {$offset}, {$count}", true);
            $audios_res = '';
            foreach($sql_ as $key => $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) {
                    $sql_[$key]['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']){
                    $sql_[$key]['title'] = 'Без названия';
                }

                if($row['text'])
                    $is_text = 'text_avilable';
                else
                    $is_text = '';

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

                $sql_[$key]['res'] = $res;

                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $params['sql'] = $sql_;
            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));
            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);
            if($uid == $user_info['user_id'] && $type == 'my_music')
            {
                $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            }else
            {
                if($uid != $user_info['user_id'])
                {
                    $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
                }else{
                    $title = 'N\A';
                }
            }

            if(isset($request['doload']) AND $request['doload']){

                return _e( json_encode(array(
                    'result' => $audios_res,
                    'playList' => $audios, '
                    pname' => $pname,
                    'title' => $title,
                    'plname' => $plname,
                    'but' => ($sql_count_['cnt'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : '')) );
            }else{
                $params['is_user'] = true;
                $params['friends_block'] = true;
                $params[$type.'_active'] = 'active';
                $params['plname'] = $plname;
                $params['public_audios'] = 'style="display:none"';
                $params['uid'] = $uid;
                $params['audio_title'] = $title;
                $params['audios_res'] = $audios_res;
                $params['public'] = false;
                $params['user_name'] = $user['user_search_pref'];
                $params['init'] = json_encode($audio_json, JSON_THROW_ON_ERROR);
                return view('audio.audio', $params);
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
    public function popular(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $count = 40;
            if (isset($request['page'])) {
                $page = (int)$request['page'];
            }
            else {
                $page = 0;
            }
            $offset = $count * $page;
            $params['title'] = $lang['audio'].' | Sura';
            $path = explode('/', $_SERVER['REQUEST_URI']);
//            $uid = ($path['2']);
            if(isset($path['2']) ){
                $type = $path['2'];
            }else{
                $type = null;
            }
            if (isset($request['uid']))
                $uid = (int)$_REQUEST['uid'];
            else
                $uid = $user_info['user_id'];

            $sql_dop = "ORDER by `add_count`";
            $plname = 'popular';
            $type = 'popular';

            $audios = array();
            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `users` WHERE user_id = '{$uid}'");

//            if($user)
//                $sql_count_['cnt'] = $user['user_audio'];
//            else
            $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `audio` WHERE oid = '{$uid}'");
//            $jid = 0;

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` {$sql_dop} DESC LIMIT {$offset}, {$count}", true);
            $audios_res = '';
            foreach($sql_ as $key => $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) {
                    $sql_[$key]['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']){
                    $sql_[$key]['title'] = 'Без названия';
                }

                if($row['text'])
                    $is_text = 'text_avilable';
                else
                    $is_text = '';

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

                $sql_[$key]['res'] = $res;

                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $params['sql'] = $sql_;
            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));
            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);
            if($uid == $user_info['user_id'] && $type == 'my_music')
                $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            else
                if($uid != $user_info['user_id'])
                    $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';

            if(isset($_POST['doload']) AND $_POST['doload']){

                return _e( json_encode(array(
                    'result' => $audios_res,
                    'playList' => $audios, '
                    pname' => $pname,
                    'title' => $title,
                    'plname' => $plname,
                    'but' => ($sql_count_['cnt'] > $count + $offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : ''), JSON_THROW_ON_ERROR) );
            }else{
                $params['is_user'] = true;
                $params['friends_block'] = true;
                $params[$type.'_active'] = 'active';
                $params['plname'] = $plname;
                $params['public_audios'] = 'style="display:none"';
                $params['uid'] = $uid;
                $params['audio_title'] = $title;
                $params['audios_res'] = $audios_res;
                $params['public'] = false;
                $params['user_name'] = $user['user_search_pref'];
                $params['init'] = json_encode($audio_json, JSON_THROW_ON_ERROR);
                return view('audio.audio', $params);
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
    public function recommendations(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $count = 40;
            if (isset($request['page']))
                $page = (int)$request['page'];
            else
                $page = 0;
            $offset = $count * $page;
            $params['title'] = $lang['audio'].' | Sura';
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $uid = ($path['2']);
            if(isset($path['2']) ){
                $type = $path['2'];
            }else{
                $type = null;
            }
            if (isset($request['uid']))
                $uid = (int)$request['uid'];
            else
                $uid = $user_info['user_id'];

            $sql_dop = "ORDER by `add_count`";
            $plname = 'audios'.$uid;
            $type = 'recommendations';

            $audios = array();
            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `users` WHERE user_id = '{$uid}'");

//            if($user)
//                $sql_count_['cnt'] = $user['user_audio'];
//            else
            $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `audio` WHERE oid = '{$uid}'");
//            $jid = 0;

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` {$sql_dop} DESC LIMIT {$offset}, {$count}", true);
            $audios_res = '';
            foreach($sql_ as $key => $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) {
                    $sql_[$key]['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']){
                    $sql_[$key]['title'] = 'Без названия';
                }

                if($row['text'])
                    $is_text = 'text_avilable';
                else
                    $is_text = '';

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

                $sql_[$key]['res'] = $res;

                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $params['sql'] = $sql_;
            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));
            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);
            if($uid == $user_info['user_id'] && $type == 'my_music')
                $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            else
                if($uid != $user_info['user_id'])
                    $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';

            if(isset($request['doload']) AND $request['doload']){

                return _e( json_encode(array(
                    'result' => $audios_res,
                    'playList' => $audios, '
                    pname' => $pname,
                    'title' => $title,
                    'plname' => $plname,
                    'but' => ($sql_count_['cnt'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : '')) );
            }else{
                $params['is_user'] = true;
                $params['friends_block'] = true;
                $params[$type.'_active'] = 'active';
                $params['plname'] = $plname;
                $params['public_audios'] = 'style="display:none"';
                $params['uid'] = $uid;
                $params['audio_title'] = $title;
                $params['audios_res'] = $audios_res;
                $params['public'] = false;
                $params['user_name'] = $user['user_search_pref'];
                $params['init'] = json_encode($audio_json);
                return view('audio.audio', $params);
            }


        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * @return int
     */
    public function feed(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $count = 40;
            if (isset($request['page']))
                $page = (int)$request['page'];
            else
                $page = 0;
            $offset = $count * $page;
            $params['title'] = $lang['audio'].' | Sura';
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $uid = ($path['2']);
            if(isset($path['2']) ){
                $type = $path['2'];
            }else{
                $type = null;
            }
            if (isset($request['uid']))
                $uid = (int)$request['uid'];
            else
                $uid = $user_info['user_id'];

            $plname = 'audios'.$uid;
            $type = 'feed';

            $audios = array();
            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `users` WHERE user_id = '{$uid}'");

//            if($user)
//                $sql_count_['cnt'] = $user['user_audio'];
//            else
            $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `audio` WHERE oid = '{$uid}'");
//            $jid = 0;

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` ORDER by `add_date` DESC LIMIT {$offset}, {$count}", true);
            $audios_res = '';
            foreach($sql_ as $key => $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) {
                    $sql_[$key]['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']){
                    $sql_[$key]['title'] = 'Без названия';
                }

                if($row['text'])
                    $is_text = 'text_avilable';
                else
                    $is_text = '';

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

                $sql_[$key]['res'] = $res;

                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $params['sql'] = $sql_;
            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));
            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);
            if($uid == $user_info['user_id'] && $type == 'my_music')
                $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            else
                if($uid != $user_info['user_id'])
                    $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
                else
                    $title = 'N/A';

            if(isset($request['doload']) AND $request['doload']){

                return _e( json_encode(array(
                    'result' => $audios_res,
                    'playList' => $audios, '
                    pname' => $pname,
                    'title' => $title,
                    'plname' => $plname,
                    'but' => ($sql_count_['cnt'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : '')) );
            }else{
                $params['is_user'] = true;
                $params['friends_block'] = true;
                $params[$type.'_active'] = 'active';
                $params['plname'] = $plname;
                $params['public_audios'] = 'style="display:none"';
                $params['uid'] = $uid;
                $params['audio_title'] = $title;
                $params['audios_res'] = $audios_res;
                $params['public'] = false;
                $params['user_name'] = $user['user_search_pref'];
                $params['init'] = json_encode($audio_json);
                return view('audio.audio', $params);
            }


        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Вывод всех аудио
     *
     * @return int
     * @throws \Throwable
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){

            $count = 40;
            if (isset($request['page']))
                $page = (int)$request['page'];
            else
                $page = 0;

            $offset = $count * $page;

            //$act = $_REQUEST['act'];
            $params['title'] = $lang['audio'].' | Sura';

            $path = explode('/', $_SERVER['REQUEST_URI']);
                $uid = ($path['2']);

            if(isset($path['2']) ){
                $type = $path['2'];
            }else{
                $type = null;
            }

            if (isset($request['uid']))
                $uid = (int)$_REQUEST['uid'];
            else
                $uid = $user_info['user_id'];


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
            $user = $db->super_query("SELECT user_audio, user_search_pref, user_sex FROM `users` WHERE user_id = '{$uid}'");

//            if($user)
//                $sql_count_['cnt'] = $user['user_audio'];
//            else
                $sql_count_ = $db->super_query("SELECT COUNT(*) as cnt FROM `audio` WHERE oid = '{$uid}'");
//            $jid = 0;

            if ($sql_count_['cnt'] !== $user['user_audio']){
                $db->query("UPDATE `users` SET user_audio = '{$sql_count_['cnt']}' WHERE user_id = '{$uid}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$uid}/profile_{$uid}");
            }

            $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` {$sql_dop} DESC LIMIT {$offset}, {$count}", true);
            $audios_res = '';
            foreach($sql_ as $key => $row){
                $stime = gmdate("i:s", $row['duration']);
                if(!$row['artist']) {
                    $sql_[$key]['artist'] = 'Неизвестный исполнитель';
                }
                if(!$row['title']){
                    $sql_[$key]['title'] = 'Без названия';
                }

                if($row['text'])
                    $is_text = 'text_avilable';
                else
                    $is_text = '';

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

                $sql_[$key]['res'] = $res;

                if($row['oid'] == $user_info['user_id']){
                    $res = str_replace(array('[tools]','[/tools]'), '', $res);
                    $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                } else {
                    $res = str_replace(array('[add]','[/add]'), '', $res);
                    $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                }
                $audios_res .= $res;

            }

            $params['sql'] = $sql_;

            $pname = 'Сейчас играют аудиозаписи '.$user['user_search_pref'].' | '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей'));

            $audio_json = array('id' => 'user_audios', 'uname' => $user['user_search_pref'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);

            if($uid == $user_info['user_id'] && $type == 'my_music')
                $title = '<div class="audio_page_title">У Вас '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';
            else
                if($uid != $user_info['user_id'])
                    $title = '<div class="audio_page_title">У '.$user['user_search_pref'].' '.$sql_count_['cnt'].' '.Gramatic::declOfNum($sql_count_['cnt'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';

            if(isset($request['doload']) AND $request['doload']){

                return _e( json_encode(array(
                    'result' => $audios_res,
                    'playList' => $audios, '
                    pname' => $pname,
                    'title' => $title,
                    'plname' => $plname,
                    'but' => ($sql_count_['cnt'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : '')) );
            }



//            $tpl->load_template('audio/main.html');

//            $tpl->set('[is_user]', '');
//            $tpl->set('[/is_user]', '');
            $params['is_user'] = true;
//            $tpl->set('[friends_block]', '');
//            $tpl->set('[/friends_block]', '');
            $params['friends_block'] = true;

//                $tpl->set('{'.$type.'-active}', 'active');
            $params[$type.'_active'] = 'active';
//                $tpl->set('{plname}', );
            $params['plname'] = $plname;
//                $tpl->set('{public_audios}', );
            $params['public_audios'] = 'style="display:none"';
//                $tpl->set('{uid}', );
            $params['uid'] = $uid;
//                $tpl->set('{title}', );
            $params['audio_title'] = $title;
//                $tpl->set('{audios_res}', );
            $params['audios_res'] = $audios_res;
//                $tpl->set_block("'\\[public\\](.*?)\\[/public\\]'si","");
            $params['public'] = false;
//                $tpl->set('{user_name}', );
            $params['user_name'] = $user['user_search_pref'];

//                $tpl->set('{init}', );
            $params['init'] = json_encode($audio_json);
//                $tpl->compile('content');

//            $tpl->clear();
//            $db->free();
            return view('audio.audio', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}

