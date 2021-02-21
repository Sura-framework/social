<?php

namespace App\Modules;

use Exception;
use Intervention\Image\ImageManager;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;

class VideosController extends Module{

    /**
     * upload
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function upload(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $config = Settings::load();

            $file_tmp = $_FILES['uploadfile']['tmp_name'];
            if (!file_exists($file_tmp)) {
                echo 'not_uploaded';
            }

            $modules_dir = dirname (__FILE__);
            $root_dir = str_replace('/app/Modules', '', $modules_dir);

            $file_name = Gramatic::totranslit($_FILES['uploadfile']['name']);
            $server_time = \Sura\Time\Date::time();
            $file_rename = substr(md5($server_time+rand(1,100000)), 0, 15);
            $file_size = $_FILES['uploadfile']['size'];
            $array = explode(".", $file_name);
            $type = strtolower(end($array));
            if($type == 'mp4' AND $config['video_mod_add'] == 'yes' AND $file_size < 500000000){
                $video_dir = $root_dir.'/public/uploads/videos/'.$user_id.'/';
                if(!is_dir($video_dir)){
                    mkdir($video_dir, 0777);
                    chmod($video_dir, 0777);
                }
                $res_type = '.'.$type;

                if(move_uploaded_file($file_tmp, $video_dir.$file_rename.$res_type)){
                    $result_video = $config['home_url'].'uploads/videos/'.$user_id.'/'.$file_rename.$res_type;

                    if ($type == 'mp4') {

                        // Создаем Изображение видео
                        // Создаем обложку
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($root_dir.'/public/uploads/videos/'.$user_id.'/'.$file_rename.$res_type);
                        $video
                            ->filters()
                            ->resize(new Dimension(320, 240))
                            ->synchronize();
                        $video
                            ->frame(TimeCode::fromSeconds(10))
                            ->save($root_dir.'/public/uploads/videos/'.$user_id.'/'.$file_rename.'.jpg');

                        // Добавить задачу декодирования в другие битрейды
                        $video_file = $root_dir.'/public/uploads/videos/'.$user_id.'/'.$file_rename.$res_type;
                        $db->query("INSERT INTO `videos_decode` SET video = '{$video_file}', type = 'mp4'");

                    }else{
                        echo 'bad_format';
                    }

                    $photo = $config['home_url'].'uploads/videos/'.$user_id.'/'.$file_rename.'.jpg';
                    $title = 'Без названия';
                    $descr = '';

                    $db->query("INSERT INTO `videos` SET download = '1', owner_user_id = '{$user_id}', video = '{$result_video}', photo = '{$photo}', title = '{$title}', descr = '{$descr}', add_date = NOW(), privacy = '1'");
                    $dbid = $db->insert_id();
                    $db->query("UPDATE `users` SET user_videos_num = user_videos_num+1 WHERE user_id = '{$user_id}'");
                    $generateLastTime = $server_time-10800;
                    $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 2 AND ac_user_id = '{$user_id}'");
                    if($row) {
                        $db->query("UPDATE `news` SET action_text = '{$dbid}|{$photo}||{$row['action_text']}', action_time = '{$server_time}' WHERE ac_id = '{$row['ac_id']}'");
                    }
                    else {
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 2, action_text = '{$dbid}|{$photo}', action_time = '{$server_time}'");
                    }
//
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/profile_{$user_id}");
                    $cache->remove("{$user_id}/page_videos_user");
                    $cache->remove("{$user_id}/page_videos_user_friends");
                    $cache->remove("{$user_id}/page_videos_user_all");
                    $cache->remove("{$user_id}/videos_num_all");
                    $cache->remove("{$user_id}/videos_num_friends");

                    $status = Status::OK;
//                    echo $Video_id; // 'complited'
                } else {
                    $status = Status::BAD_MOVE;
                }
            } else {
                $status = Status::BIG_SIZE;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Страница добавления видео
     *
     * @return int
     */
    public function add(): int
    {
        $logged = $this->logged();
        Tools::NoAjaxRedirect();
        $params = array();

        if($logged){
            return view('videos.add', $params);

        }
        return view('info.info', $params);
    }

    /**
     * upload add
     *
     * @return int
     */
    public function upload_add(): int
    {
        $logged = $this->logged();
        Tools::NoAjaxRedirect();
        $params = array();

        if($logged){
            return view('videos.upload', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Добавление видео в БД
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
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $config = Settings::load();

            if($config['video_mod_add'] == 'yes'){
                $good_video_lnk = Validation::ajax_utf8(Validation::textFilter($request['good_video_lnk']));
                $title = Validation::ajax_utf8(Validation::textFilter($request['title']));
                $descr = Validation::ajax_utf8(Validation::textFilter($_POST['descr'], 3000));
                $privacy = intval($request['privacy']);
                if($privacy <= 0 OR $privacy > 3) $privacy = 1;

                //Если youtube то добавляем префикс src=" и составляем ответ для скрипта, для вставки в БД
                if(preg_match("/src=\"https:\/\/www.youtube.com|src=\"https:\/\/youtube.com/i", 'src="'.$good_video_lnk)){
                    $good_video_lnk = str_replace(array('#', '!'), '', $good_video_lnk);
                    $exp_y = explode('v=', $good_video_lnk);
                    $exp_x = explode('&', $exp_y[1]);
                    $result_video_lnk = '<iframe width="770" height="420" src="https://www.youtube.com/embed/'.$exp_x[0].'" frameborder="0" allowfullscreen></iframe>';
                }

                //Если rutube, То добавляем префикс value="
                if(preg_match("/value=\"https:\/\/www.rutube.ru|value=\"https:\/\/rutube.ru/i", 'value="'.$good_video_lnk)){
                    $exp_frutube = explode('?v=', $good_video_lnk);
                    $result_video_lnk = '<OBJECT width="770" height="420"><PARAM name="movie" value="https://video.rutube.ru/'.$exp_frutube[1].'"></PARAM><PARAM name="wmode" value="window"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><EMBED src="https://video.rutube.ru/'.$exp_frutube[1].'" type="application/x-shockwave-flash" wmode="window" width="770" height="420" allowFullScreen="true" ></EMBED></OBJECT>';
                }

                //Если vimeo, То добавляем префикс src="
                if(preg_match("/src=\"https:\/\/www.vimeo.com|src=\"https:\/\/vimeo.com/i", 'src="'.$good_video_lnk)){
                    $exp_frutube = explode('com/', $good_video_lnk);
                    $result_video_lnk = '<iframe src="https://player.vimeo.com/video/'.$exp_frutube[1].'" width="770" height="420" frameborder="0"></iframe>';
                }

                //Если smotri, То добавляем префикс src="
                if(preg_match("/src=\"https:\/\/www.smotri.com|src=\"https:\/\/smotri.com/i", 'src="'.$good_video_lnk)){
                    $exp = explode('id=', str_replace('#', '', $good_video_lnk));
                    $result_video_lnk = '<object id="smotriComVideoPlayer'.$exp[1].'_1314557535.5897_7726" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="770" height="420"><param name="movie" value="https://pics.smotri.com/player.swf?file='.$exp[1].'&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#ffffff" /><embed src="https://pics.smotri.com/player.swf?file='.$exp[1].'&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml" quality="high" allowscriptaccess="always" allowfullscreen="true" wmode="opaque"  width="770" height="420" type="application/x-shockwave-flash"></embed></object>';
                }

                //Формируем данные о фото
                $photo = $db->safesql(Validation::ajax_utf8(htmlspecialchars(trim($request['photo']))));
                $photo = str_replace("\\", "/", $photo);
                $img_name_arr = explode(".", $photo);
                $img_format = Gramatic::totranslit(end($img_name_arr));
                $image_rename = substr(md5(time().md5($good_video_lnk)), 0, 15);

                //Разришенные форматы
                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

                //Загружаем картинку на сайт
                if(in_array(strtolower($img_format), $allowed_files) && preg_match("/https:\/\//i", $photo) && $result_video_lnk){

                    //Директория загрузки фото
                    $upload_dir = __DIR__.'/../../public/uploads/videos/'.$user_id.'/';
                    $res_type = '.'.$img_format;
                    //Если нет папки юзера, то создаём её
                    if(!is_dir($upload_dir)){
                        mkdir($upload_dir, 0777);
                        chmod($upload_dir, 0777);
                    }

                    copy($photo, $upload_dir.$image_rename.$res_type);

                    $manager = new ImageManager(array('driver' => 'gd'));

                    //Создание оригинала
                    $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(175, 131);
                    $image->save($upload_dir.$image_rename.'.webp', 90);

                    unlink($upload_dir.$image_rename.$res_type);
                    $res_type = '.webp';
                }

                if($result_video_lnk AND $title){
                    $photo = $config['home_url'].'uploads/videos/'.$user_id.'/'.$image_rename.'.'.$img_format;
                    $db->query("INSERT INTO `videos` SET owner_user_id = '{$user_id}', video = '{$result_video_lnk}', photo = '{$photo}', title = '{$title}', descr = '{$descr}', add_date = NOW(), privacy = '{$privacy}'");
                    $dbid = $db->insert_id();

                    $db->query("UPDATE `users` SET user_videos_num = user_videos_num+1 WHERE user_id = '{$user_id}'");

                    $photo = str_replace($config['home_url'], '/', $photo);

                    //Добавляем действия в ленту новостей
                    $server_time = \Sura\Time\Date::time();
                    $generateLastTime = $server_time-10800;
                    $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 2 AND ac_user_id = '{$user_id}'");
                    if($row)
                        $db->query("UPDATE `news` SET action_text = '{$dbid}|{$photo}||{$row['action_text']}', action_time = '{$server_time}' WHERE ac_id = '{$row['ac_id']}'");
                    else
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 2, action_text = '{$dbid}|{$photo}', action_time = '{$server_time}'");

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/profile_{$user_id}");
                    $cache->remove("{$user_id}/page_videos_user");
                    $cache->remove("{$user_id}/page_videos_user_friends");
                    $cache->remove("{$user_id}/page_videos_user_all");
                    $cache->remove("{$user_id}/videos_num_all");
                    $cache->remove("{$user_id}/videos_num_friends");

                    if($request['notes'] == 1)
                    {
//                        echo "{$photo}|{$user_id}|{$dbid}";

                        $status = Status::OK;
                    }else{
                        $status = Status::NOT_DATA;
                    }
                }else{
                    $status = Status::NOT_DATA;//NOT_DATA
                }
            } else {
                $status = Status::BAD_RIGHTS;//BAD_RIGHTS
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Парсер . Загрузка данных о видео
     *
     * @return int
     * @throws \JsonException
     */
    public function load(): int
    {
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $video_lnk = $request['video_lnk'];

            if(preg_match("/https:\/\/www.youtube.com|https:\/\/youtube.com|https:\/\/rutube.ru|https:\/\/www.rutube.ru|https:\/\/www.vimeo.com|https:\/\/vimeo.com|https:\/\/smotri.com|https:\/\/www.smotri.com/i", $video_lnk)){
                //Открываем ссылку
                //Если ссылка youtube, то формируем xml ссылку для получения данных
                if(preg_match("/https:\/\/www.youtube.com|https:\/\/youtube.com/i", $video_lnk)){
                    $exp_y = explode('v=', $video_lnk);
                    $exp_x = explode('&', $exp_y[1]);
                    $sock = fopen('https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v='.$exp_x[0].'&format=xml', 'r');
                } elseif(preg_match("/https:\/\/www.vimeo.com|https:\/\/vimeo.com/i", $video_lnk)){
                    $sock = fopen('https://vimeo.com/api/oembed.xml?url='.$video_lnk, 'r');
                } else {
                    $sock = fopen($video_lnk, 'r');
                }

                if(!$sock){
                    $status = Status::NOT_DATA;
                }
                else {
                    $html = '';

                    //Если сервис youtube, rutube, smotri то просто выводи
                    if(preg_match("/https:\/\/www.youtube.com|https:\/\/youtube.com|https:\/\/rutube.ru|https:\/\/www.rutube.ru|https:\/\/smotri.com|https:\/\/www.smotri.com/i", $video_lnk)){
                        while(!feof($sock)){
                            $html .= fgets($sock);
                        }
                    }

                    //Если сервис Vimeo, то сразу применяем кодировку utf-8, win-1251
                    if(preg_match("/https:\/\/www.vimeo.com|https:\/\/vimeo.com/i", $video_lnk)){
                        while(!feof($sock)){
                            $html .= Validation::ajax_utf8(fgets($sock));
                        }
                    }

                    fclose($sock);

                    //Если сервис Vimeo, то выводим без кодировки
                    $data = str_replace(array('[', ']'), array('&iqu;', '&iqu2;'), $html);

                    //Если сервис youtube применяем кодировку utf-8, win-1251
                    $data_all = Validation::ajax_utf8(str_replace(array('[', ']'), array('&iqu;', '&iqu2;'), $html));

                    //Если видеосервис youtube
                    if(preg_match("/https:\/\/www.youtube.com|https:\/\/youtube.com/i", $video_lnk)){
                        preg_match_all('`(<title>[^\[]+\</title>)`si', $data_all, $parse);
                        $res_title = rn_replace(str_replace(array('<title>', '</title>'), '', $parse[1][0]));

                        //Делаем фотку для youtube
                        $parse_start = explode('v=', $video_lnk);
                        $parse_end = explode('&', $parse_start[1]);
                        $res_img = "https://img.youtube.com/vi/{$parse_end[0]}/0.jpg";
                    }

                    //Если видеосервис rutube
                    if(preg_match("/https:\/\/rutube.ru|https:\/\/www.rutube.ru/i", $video_lnk)){
                        $data_rutube = iconv('koi8-u', 'windows-1251', str_replace(array('[', ']'), array('&iqu;', '&iqu2;'), $html));

                        preg_match_all('`(<meta property="og:title" content="[^\[]+\<meta property="og:description")`si', $data_rutube, $parse_rutube);
                        $res_title = rn_replace(str_replace(array('<meta property="og:title" content="', '<meta property="og:description"', '" />'), '', $parse_rutube[1][0]));

                        preg_match_all('`(<meta property="og:description" content="[^\[]+\<meta property="og:image")`si', $data_rutube, $parse_rutube_descr);
                        $res_descr = rn_replace(str_replace(array('<meta property="og:description" content="', '<meta property="og:image"', '" />'), '', $parse_rutube_descr[1][0]));

                        $exp_rutube_img = explode('v=', $video_lnk);
                        $exp_img_dir = substr($exp_rutube_img[1], 0, 2);
                        $exp_img_dir_2 = substr($exp_rutube_img[1], 2, 2);
                        $res_img = "https://tub.rutube.ru/thumbs/{$exp_img_dir}/{$exp_img_dir_2}/{$exp_rutube_img[1]}-1.jpg";
                    }

                    //Если видеосервис vimeo
                    if(preg_match("/https:\/\/www.vimeo.com|https:\/\/vimeo.com/i", $video_lnk)){
                        preg_match_all('`(<title>[^\[]+\</title>)`si', $data, $parse);
                        $res_title = str_replace(array('<title>', '</title>'), '', $parse[1][0]);

                        preg_match_all('`(<thumbnail_url>[^\[]+\</thumbnail_url>)`si', $data, $parse_img);
                        $res_img = str_replace(array('<thumbnail_url>', '</thumbnail_url>'), '', $parse_img[1][0]);

                        preg_match_all('`(<description>[^\[]+\</description>)`si', $data, $parse_descr);
                        $res_descr = myBrRn(rn_replace($parse_descr[1][0]));
                    }

                    //Если видеосервис smotri
                    if(preg_match("/https:\/\/smotri.com|https:\/\/www.smotri.com/i", $video_lnk)){
                        $html = iconv('utf-8', 'windows-1251', $html);

                        preg_match_all('`(<meta property="og:title" content="[^\[]+\<meta property="og:image")`si', $html, $parse_title);
                        $res_title = rn_replace(str_replace(array('<meta property="og:title" content="', '<meta property="og:image"', '" />'), '', $parse_title[1][0]));

                        preg_match_all('`(<link rel="image_src" href="[^\[]+\<!-- Open Graf Protocol. Facebook/Yandex -->)`si', $html, $parse_img);
                        $res_img = rn_replace(str_replace(array('<link rel="image_src" href="', '<!-- Open Graf Protocol. Facebook/Yandex -->', '" />'), '', $parse_img[1][0]));
                    }

                    $result_img = $res_img;
                    $result_title = trim(strip_tags(strtr($res_title, array('&#39;' => "'", '&quot;' => '"', '&iqu;' => '[', '&iqu2;' => ']'))));
                    $result_descr = trim(strip_tags($res_descr));

                    if($result_img && $result_title) {
//                        echo "{$result_img}:|:{$result_title}:|:{$result_descr}";
                        $status = Status::OK;
                    }else {
                        $status = Status::NOT_DATA;
                    }
                }
            } else {
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
     * Удаление видео
     *
     * @throws \Throwable
     */
    public function delet(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $vid = intval($request['vid']);

            if($vid){
                $row = $db->super_query("SELECT owner_user_id, photo, public_id FROM `videos` WHERE id = '{$vid}'");
                if($row['owner_user_id'] == $user_id AND !$row['public_id']){
                    $db->query("DELETE FROM `videos` WHERE id = '{$vid}'");
                    $db->query("DELETE FROM `videos_comments` WHERE video_id = '{$vid}'");
                    $db->query("UPDATE `users` SET user_videos_num = user_videos_num-1 WHERE user_id = '{$row['owner_user_id']}'");

                    //Удаляем фотку
                    $exp_photo = explode('/', $row['photo']);
                    $photo_name = end($exp_photo);
                    if (file_exists(__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$photo_name)) {
                        unlink(__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$photo_name);
                    }

                    // original && 240 && 720
                    $type = 'jpg';
                    $file_name = str_replace('.'.$type, '', $photo_name);
                    if (__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'.mp4') {
                        unlink(__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'.mp4');
                    }
                    if (__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'_240.mp4') {
                        unlink(__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'_240.mp4');
                    }
                    if (__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'_720.mp4') {
                        unlink(__DIR__.'/../../public/uploads/videos/'.$row['owner_user_id'].'/'.$file_name.'_720.mp4');
                    }

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/profile_{$user_id}");
                    $cache->remove("{$user_id}/page_videos_user");
                    $cache->remove("{$user_id}/page_videos_user_friends");
                    $cache->remove("{$user_id}/page_videos_user_all");
                    $cache->remove("{$user_id}/videos_num_all");
                    $cache->remove("{$user_id}/videos_num_friends");
                    $cache = new \Sura\Cache\Cache($storage, 'wall');
                    $cache->remove("video{$vid}");

                    $status = Status::OK;
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
     *  Страница редактирования видео
     *
     * @return int
     */
    public function edit(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            //$act = $request['act'];
            $user_id = $user_info['user_id'];
            //$limit_vieos = 20;

            $vid = intval($request['vid']);
            if($vid){
                $row = $db->super_query("SELECT title, descr, privacy FROM `videos` WHERE id = '{$vid}' AND owner_user_id = '{$user_id}'");
                if($row){
//                    $tpl->load_template('videos/editpage.tpl');
//                    $tpl->set('{title}', stripslashes($row['title']));
//                    $tpl->set('{descr}', stripslashes(myBrRn($row['descr'])));
//                    $tpl->set('{privacy}', $row['privacy']);
//                    $tpl->set('{privacy-text}', strtr($row['privacy'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
//                    $tpl->compile('content');
//                    Tools::AjaxTpl($tpl);

                    return view('info.info', $params);
                }
            }
        }
        return view('info.info', $params);
    }

    /**
     * Сохранение отредактированых данных
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function editsave(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $vid = intval($request['vid']);

            if($vid){
                $title = Validation::ajax_utf8(Validation::textFilter($request['title'], false, true));
                $descr = Validation::ajax_utf8(Validation::textFilter($request['descr'], 3000));
                $privacy = intval($request['privacy']);
                if($privacy <= 0 OR $privacy > 3) $privacy = 1;

                //Проверка на существования записи
                $row = $db->super_query("SELECT owner_user_id, public_id FROM `videos` WHERE id = '{$vid}'");
                if($row['owner_user_id'] == $user_id AND !$row['public_id']){
                    $db->query("UPDATE `videos` SET title = '{$title}', descr = '{$descr}', privacy = '{$privacy}' WHERe id = '{$vid}'");
                    echo stripslashes($descr);

                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/profile_{$user_id}");
                    $cache->remove("{$user_id}/page_videos_user");
                    $cache->remove("{$user_id}/page_videos_user_friends");
                    $cache->remove("{$user_id}/page_videos_user_all");
                    $cache->remove("{$user_id}/videos_num_all");
                    $cache->remove("{$user_id}/videos_num_friends");
                    $cache = new \Sura\Cache\Cache($storage, 'wall');
                    $cache->remove("video{$vid}");

                    $status = Status::OK;
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
     *  Просмотр видео
     *
     * @return int
     */
    public function view(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            $path = explode('/', $_SERVER['REQUEST_URI']);

            if(!empty($request['vid'])){
                $vid = intval($request['vid']);
            }else{
                $vid = intval($path['3']);
            }

            $close_link = $request['close_link'];

            //Выводи данные о видео если оно есть
            $row = $db->super_query("SELECT tb1.video, title, download, add_date, descr, owner_user_id, views, comm_num, privacy, public_id, tb2.user_search_pref FROM `videos` tb1, `users` tb2 WHERE tb1.id = '{$vid}' AND tb1.owner_user_id = tb2.user_id");

            if($row){
                //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                $get_user_id = null; //bug


                if($user_id != $get_user_id)
                    $check_friend = (new \App\Libs\Friends)->CheckFriends($row['owner_user_id']);
                else
                    $check_friend = null;//bug

                //Blacklist
                $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($row['owner_user_id']);


                //Приватность
                if(!$CheckBlackList AND $row['privacy'] == 1 OR $row['privacy'] == 2 AND $check_friend OR $user_info['user_id'] == $row['owner_user_id'])
                    $privacy = true;
                else
                    $privacy = false;

                if($privacy){
                    $config = Settings::load();

                    //Выводим комментарии если они есть
                    if($row['comm_num'] AND $config['video_mod_comm'] == 'yes'){

                        if($row['public_id']){

                            $infoGroup = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");

                            $public_admin = null;

                            if(strpos($infoGroup['admin'], "u{$user_id}|") !== false)
                                $public_admin = true;
                            else
                                $public_admin = false;

                        }

                        if($row['comm_num'] > 3)
                            $limit_comm = $row['comm_num']-3;
                        else
                            $limit_comm = 0;

                        $sql_comm = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `videos_comments` tb1, `users` tb2 WHERE tb1.video_id = '{$vid}' AND tb1.author_user_id = tb2.user_id ORDER by `add_date` ASC LIMIT {$limit_comm}, {$row['comm_num']}", 1);
//                        $tpl->load_template('videos/comment.tpl');
                        foreach($sql_comm as $row_comm){
                            $online = \App\Libs\Profile::Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
//                            $tpl->set('{online}', $online);

//                            $tpl->set('{uid}', $row_comm['author_user_id']);
//                            $tpl->set('{author}', $row_comm['user_search_pref']);
//                            $tpl->set('{comment}', stripslashes($row_comm['text']));
//                            $tpl->set('{id}', $row_comm['id']);
                            //Registry::set('tpl', $tpl);
                            $date = \Sura\Time\Date::megaDate(strtotime($row_comm['add_date']));
//                            $tpl->set('{date}', $date);

                            if($row_comm['author_user_id'] == $user_id || $row['owner_user_id'] == $user_id || $public_admin){
//                                $tpl->set('[owner]', '');
//                                $tpl->set('[/owner]', '');
                            } else{
//                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                            }

                            if($row_comm['user_photo']){
//                                $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['author_user_id'].'/50_'.$row_comm['user_photo']);

                            }
                            else{
//                                $tpl->set('{ava}', '/images/no_ava_50.png');

                            }
//                            $tpl->compile('comments');
                        }
                    }

                    $sql_playlist = $db->super_query("SELECT id, photo, title, views FROM `videos` WHERE owner_user_id = '{$row['owner_user_id']}' ORDER by id DESC LIMIT 0, 5", 1);

                    $vplaylist = '';
                    foreach($sql_playlist as $row_playlist){

                        if($vid == $row_playlist['id'])
                            $active = ' active';
                        else
                            $active = '';

                        $vplaylist .= '<a class="plvideo'.$row_playlist['id'].'" href="/video/'.$row['owner_user_id'].'/'.$row_playlist['id'].'/" onclick="$(`.video_view`).remove(); videos.show('.$row_playlist['id'].', this.href); return false;"><div class="plvideo'.$row_playlist['id'].' videopl'.$active.'" style="display: flex;font-size: 12px;padding: 6px 5px 6px 10px;">
                                <div style="margin-right: 7px;"><img style="width:100px;height:55px;" src="'.$row_playlist['photo'].'"/></div>
                                <div>
                                <div style="max-height: 34px;margin-bottom: 2px;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;line-height: 17px;color: #fff;opacity: 0.7;">'.$row_playlist['title'].'</div>
                                <div style="color: #fff;opacity: 0.35;">'.$row_playlist['views'].' '.declofnum($row_playlist['views'], array('просмотр','просмотра','просмотров')).'</div>
                                </div>
                                </div></a>';
                    }
                    if($row['download'] == '1') {

//                        $tpl->load_template('videos/show.tpl');
//                        $tpl->set('{photo}', $row['photo']);

                        $video_strlen = mb_strlen($row['video']);

                        $video_str_count = $video_strlen - 4;

                        $video_w_patch = substr($row['video'], 0, $video_str_count);

                        $video_patch = str_replace($config['home_url'], '', $video_w_patch);


                        $modules_dir = dirname (__FILE__);
                        $root_dir = str_replace('/app/Modules', '', $modules_dir);
                        $check_converted = $db->super_query("SELECT id FROM `videos_decode` WHERE video = '".$root_dir."/public/".$video_patch.".mp4'");

                        if (file_exists(__DIR__.'/../../public/'.$video_patch.'_240.mp4')) {
//                            $tpl->set('{video_240}', '<source src="'.$video_w_patch.'_240.mp4" type="video/mp4" size="240" />');
                        }else{
//                            $tpl->set('{video_240}', '');
                        }

                        if (file_exists(__DIR__.'/../../public/'.$video_patch.'_720.mp4')) {
//                            $tpl->set('{video_720}', '<source src="'.$video_w_patch.'_720.mp4" type="video/mp4" size="720" />');
                        }else{
//                            $tpl->set('{video_720}', '');
                        }
                        // $tpl->set('{video_1080}', '<source src="'.$video_w_patch.'_240.mp4" type="video/mp4" size="320" />');
//                        $tpl->set('{video}', '<source src="'.$row['video'].'" type="video/mp4" size="1080" />');
                    }else {
//                        $tpl->load_template('videos/full.tpl');
                        $row['video'] = str_replace('960','800',$row['video']);
//                        $tpl->set('{video}', $row['video']);
                    }
//                    $tpl->set('{vid}', $vid);

//                    $tpl->set('{vplaylist}', $vplaylist);
                    $type = explode('.', $row['video']);
//                    $tpl->set('{type}', $type[count($type)-1]);
//                    $tpl->set('{vid}', $vid);
                    $titles = array('просмотр', 'просмотра', 'просмотров');//video_views
                    if($row['views']) {
//                        $tpl->set('{views}', $row['views'].' '.Gramatic::declOfNum($row['views'], $titles).'<br /><br />');

                    }
                    else{
//                        $tpl->set('{views}', '');

                    }
//                    $tpl->set('{tit/le}', stripslashes($row['title']));
//                    $tpl->set('{descr}', stripslashes($row['descr']));
//                    $tpl->set('{author}', $row['user_search_pref']);
//                    $tpl->set('{uid}', $row['owner_user_id']);
//                    $tpl->set('{comments}', $tpl->result['comments']);
//                    $tpl->set('{comm-num}', $row['comm_num']);
//                    $tpl->set('{owner-id}', $row['owner_user_id']);
//                    $tpl->set('{close-link}', $close_link);

                    $date = \Sura\Time\Date::megaDate(strtotime($row['add_date']));
//                    $tpl->set('{date}', $date);

                    if($row['owner_user_id'] == $user_id){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
//                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                    } else {
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
//                        $tpl->set('[not-owner]', '');
//                        $tpl->set('[/not-owner]', '');
                    }

                    if($row['public_id']){
//                        $tpl->set_block("'\\[public\\](.*?)\\[/public\\]'si","");

                    } else {
//                        $tpl->set('[public]', '');
//                        $tpl->set('[/public]', '');

                    }

                    if($config['video_mod_add_my'] == 'no'){
//                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                    }

                    $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                    $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
//                    $tpl->set('{prev-text-comm}', Gramatic::declOfNum(($row['comm_num']-3), $titles1).' '.($row['comm_num']-3).' '.Gramatic::declOfNum(($row['comm_num']-3), $titles2));
                    if($row['comm_num'] < 4){
//                        $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");

                    }
                    else {
//                        $tpl->set('[all-comm]', '');
//                        $tpl->set('[/all-comm]', '');
                    }

                    if($config['video_mod_comm'] == 'yes'){
//                        $tpl->set('[admin-comments]', '');
//                        $tpl->set('[/admin-comments]', '');
                    } else{
//                        $tpl->set_block("'\\[admin-comments\\](.*?)\\[/admin-comments\\]'si","");

                    }

//                    $tpl->compile('content');
//                    Tools::AjaxTpl($tpl);

                    $db->query("UPDATE LOW_PRIORITY `videos` SET views = views+1 WHERE id = '".$vid."'");

                    return view('info.info', $params);
                } else
                    return _e('err_privacy');
            } else
                return _e(  'no_video');
        }
        return view('info.info', $params);
    }

    /**
     * Добавления комментария в базу
     *
     * @return int
     * @throws \Throwable
     */
    public function addcomment(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $limit_vieos = 20;

            $config = Settings::load();

            if($config['video_mod_comm'] == 'yes'){
                $vid = intval($request['vid']);
                $comment = Validation::ajax_utf8(Validation::textFilter($request['comment']));

                //Провекра на существования видео
                $check_video = $db->super_query("SELECT owner_user_id, photo, public_id FROM `videos` WHERE id = '{$vid}'");

                //ЧС
                $CheckBlackList = CheckBlackList($check_video['owner_user_id']);
                if(!$CheckBlackList){
                    if($check_video AND isset($comment) AND !empty($comment)){
                        $db->query("INSERT INTO `videos_comments` SET author_user_id = '{$user_id}', video_id = '{$vid}', text = '{$comment}', add_date = NOW()");
                        $id = $db->insert_id();
                        $db->query("UPDATE `videos` SET comm_num = comm_num+1 WHERE id = '{$vid}'");

//                        $tpl->load_template('videos/comment.tpl');
//                        $tpl->set('{online}', $lang['online']);
//                        $tpl->set('{uid}', $user_id);
//                        $tpl->set('{author}', $user_info['user_search_pref']);
//                        $tpl->set('{comment}', stripslashes($comment));
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
//                        $tpl->set('{id}', $id);
//                        $tpl->set('{date}', langdate('сегодня в H:i', time()));
                        if($user_info['user_photo']){
//                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$user_id.'/50_'.$user_info['user_photo']);

                        }
                        else{
//                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        }
//                        $tpl->compile('content');

                        if(!$check_video['public_id']){

                            //Добавляем действие в ленту новостей "ответы" владельцу фотографии
                            if($user_id != $check_video['owner_user_id']){
                                $check_video['photo'] = str_replace($config['home_url'], '/', $check_video['photo']);
                                $comment = str_replace("|", "&#124;", $comment);
                                $server_time = \Sura\Time\Date::time();
                                $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 9, action_text = '{$comment}|{$check_video['photo']}|{$vid}', obj_id = '{$id}', for_user_id = '{$check_video['owner_user_id']}', action_time = '{$server_time}'");

                                //Вставляем событие в моментальные оповещания
                                $row_userOW = $db->super_query("SELECT user_last_visit FROM `users` WHERE user_id = '{$check_video['owner_user_id']}'");
                                $update_time = $server_time - 70;

                                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                                $cache = new \Sura\Cache\Cache($storage, 'users');

                                if($row_userOW['user_last_visit'] >= $update_time){
                                    $db->query("INSERT INTO `updates` SET for_user_id = '{$check_video['owner_user_id']}', from_user_id = '{$user_id}', type = '3', date = '{$server_time}', text = '{$comment}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/video{$check_video['owner_user_id']}_{$vid}'");
                                    $cache->save("{$check_video['owner_user_id']}/updates", 1);
                                } else {
                                    //ИНАЧЕ Добавляем +1 юзеру для оповещания
                                    $value = $cache->load("users/{$check_video['owner_user_id']}/new_news");
                                    $cache->save("users/{$check_video['owner_user_id']}/new_news", $value+1);
                                }

                                //Отправка уведомления на E-mail
//                                if($config['news_mail_3'] == 'yes'){
//                                    $rowUserEmail = $db->super_query("SELECT user_name, user_email FROM `users` WHERE user_id = '".$check_video['owner_user_id']."'");
//                                    if($rowUserEmail['user_email']){
//                                        include_once __DIR__.'/../Classes/mail.php';
//                                        $mail = new \dle_mail($config);
//                                        $rowMyInfo = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '".$user_id."'");
//                                        $rowEmailTpl = $db->super_query("SELECT text FROM `mail_tpl` WHERE id = '3'");
//                                        $rowEmailTpl['text'] = str_replace('{%user%}', $rowUserEmail['user_name'], $rowEmailTpl['text']);
//                                        $rowEmailTpl['text'] = str_replace('{%user-friend%}', $rowMyInfo['user_search_pref'], $rowEmailTpl['text']);
//                                        $rowEmailTpl['text'] = str_replace('{%rec-link%}', $config['home_url'].'video'.$check_video['owner_user_id'].'_'.$vid, $rowEmailTpl['text']);
//                                        $mail->send($rowUserEmail['user_email'], 'Новый комментарий к Вашей видеозаписи', $rowEmailTpl['text']);
//                                    }
//                                }
                            }

                            //Чистим кеш
                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'users');

                            $cache->remove("{$check_video['owner_user_id']}/profile");
                            $cache->remove("{$check_video['owner_user_id']}/page_videos_user_friends");
                            $cache->remove("{$check_video['owner_user_id']}/page_videos_user_all");

                        } else{
                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'groups');
                            $cache->remove("{$check_video['public_id']}/video{$check_video['public_id']}");
                        }
                        return view('info.info', $params);

                    }
                }
            } else
                echo 'error';
        }
        return view('info.info', $params);
    }

    /**
     * Удаления комментария
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function delcomment(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $comm_id = intval($request['comm_id']);

            //Проверка на существования комментария, и выводим ИД владельца видео
            $row = $db->super_query("SELECT tb1.video_id, author_user_id, tb2.owner_user_id, public_id FROM `videos_comments` tb1, `videos` tb2 WHERE tb1.id = '{$comm_id}' AND tb1.video_id = tb2.id");

            if($row['public_id']){
                $infoGroup = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");
                if(strpos($infoGroup['admin'], "u{$user_id}|") !== false)
                    $public_admin = true;
                else
                    $public_admin = false;

                if($public_admin AND $row){

                    $db->query("DELETE FROM `videos_comments` WHERE id = '{$comm_id}'");
                    $db->query("DELETE FROM `news` WHERE obj_id = '{$comm_id}' AND action_type = 9");
                    $db->query("UPDATE `videos` SET comm_num = comm_num-1 WHERE id = '{$row['video_id']}'");

                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'groups');

                    $cache->remove("{$row['public_id']}/video{$row['public_id']}");

                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
                }
            }
            else {
                if($row['author_user_id'] == $user_id OR $row['owner_user_id'] == $user_id){

                    $db->query("DELETE FROM `videos_comments` WHERE id = '{$comm_id}'");
                    $db->query("DELETE FROM `news` WHERE obj_id = '{$comm_id}' AND action_type = 9");
                    $db->query("UPDATE `videos` SET comm_num = comm_num-1 WHERE id = '{$row['video_id']}'");

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');

                    $cache->remove("{$row['owner_user_id']}/profile");
                    $cache->remove("{$row['owner_user_id']}/page_videos_user_friends");
                    $cache->remove("row['owner_user_id']}/page_videos_user_all");

                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
                }

            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Показ всех комментариев
     *
     * @return int
     */
    public function all_comm(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
            $vid = intval($request['vid']);
            $comm_num = intval($request['num']);
            $owner_id = intval($request['owner_id']);

            $row = $db->super_query("SELECT public_id FROM `videos` WHERE id = '{$vid}'");
            $public_admin = null;
            if($row['public_id']){
                $infoGroup = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");
                if(strpos($infoGroup['admin'], "u{$user_id}|") !== false)
                    $public_admin = true;
                else
                    $public_admin = false;

            }

            if($comm_num > 3 AND $vid AND $owner_id){

                $limit_comm = $comm_num-3;

                $sql_comm = $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `videos_comments` tb1, `users` tb2 WHERE tb1.video_id = '{$vid}' AND tb1.author_user_id = tb2.user_id ORDER by `add_date` ASC LIMIT 0, {$limit_comm}", 1);

//                $tpl->load_template('videos/comment.tpl');

                foreach($sql_comm as $row_comm){

//                    $tpl->set('{uid}', $row_comm['author_user_id']);
//                    $tpl->set('{author}', $row_comm['user_search_pref']);
//                    $tpl->set('{comment}', stripslashes($row_comm['text']));
//                    $tpl->set('{id}', $row_comm['id']);
                    $online = \App\Libs\Profile::Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
//                    $tpl->set('{online}', $online);

                    $date = \Sura\Time\Date::megaDate(strtotime($row_comm['add_date']));
//                    $tpl->set('{date}', $date);

                    if($row_comm['author_user_id'] == $user_id OR $owner_id == $user_id OR $public_admin){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');

                    } else{
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    }

                    $config = Settings::load();

                    if($row_comm['user_photo']){
//                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['author_user_id'].'/50_'.$row_comm['user_photo']);

                    }
                    else{
//                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    }
//                    $tpl->compile('content');

                }

            }
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Страница всех видео юзера,
     * для прикрепления видео кому-то на стену
     *
     * @return int
     */
    public function all_videos(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            //$limit_vieos = 20;

            $notes = intval($request['notes']);

            //Для навигатор
            if($request['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            //Делаем SQL запрос на вывод
            $sql_ = $db->super_query("SELECT id, photo, title FROM `videos` WHERE owner_user_id = '{$user_id}' AND public_id = '0' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            //Выводим кол-во видео
            $count = $db->super_query("SELECT user_videos_num FROM `users` WHERE user_id = '{$user_id}'");

            if($count['user_videos_num']){
                if($notes){
//                    $tpl->load_template('videos/box_all_video_notes_top.tpl');

                }
                else{
//                    $tpl->load_template('videos/box_all_video_top.tpl');

                }

//                $tpl->set('[top]', '');
//                $tpl->set('[/top]', '');
                $titles = array('видеозапись', 'видеозаписи', 'видеозаписей');//videos
//                $tpl->set('{photo-num}', $count['user_videos_num'].' '.Gramatic::declOfNum($count['user_videos_num'], $titles));
//                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
//                $tpl->compile('content');

                //Выводим циклом видео
                if(!$notes){
//                    $tpl->load_template('videos/box_all_video.tpl');

                }
                else{
//                    $tpl->load_template('videos/box_all_video_notes.tpl');

                }

                foreach($sql_ as $row){
//                    $tpl->set('{photo}', $row['photo']);
//                    $tpl->set('{title}', stripslashes($row['title']));
//                    $tpl->set('{video-id}', $row['id']);
//                    $tpl->set('{user-id}', $user_id);
//                    $tpl->compile('content');
                }
//                box_navigation($gcount, $count['user_videos_num'], $page, 'wall.attach_addvideo', $notes);

//                $tpl->load_template('/albums/albums_editcover.tpl');
//                $tpl->set('[bottom]', '');
//                $tpl->set('[/bottom]', '');
//                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
//                $tpl->compile('content');
            } else
                if($notes)
                    echo $lang['videos_box_none'].'<div class="button_div_gray fl_l" style="margin-left:210px;margin-top:20px"><button onClick="videos.add(1)">Добавить новый видеоролик</button></div>';
                else
                    echo $lang['videos_box_none'];

            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Страница всех видео юзера,
     * для прикрепления видео в сообщество
     *
     * @return int
     */
    public function all_videos_public(): int
    {
//        $tpl = $params['tpl'];
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            //$limit_vieos = 20;

            $pid = intval($request['pid']);

            //Для навигатор
            if($request['page'] > 0) $page = intval($request['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            //Делаем SQL запрос на вывод
            $sql_ = $db->super_query("SELECT id, photo, title FROM `videos` WHERE public_id = '{$pid}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            //Выводим кол-во видео
            $count = $db->super_query("SELECT videos_num FROM `communities` WHERE id = '{$pid}'");

            if($count['videos_num']){

//                $tpl->load_template('videos/box_all_video_top.tpl');

//                $tpl->set('[top]', '');
//                $tpl->set('[/top]', '');
                $titles = array('видеозапись', 'видеозаписи', 'видеозаписей');//videos
//                $tpl->set('{photo-num}', $count['videos_num'].' '.Gramatic::declOfNum($count['videos_num'], $titles));
//                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
//                $tpl->compile('content');

                //Выводим циклом видео
//                $tpl->load_template('videos/box_all_video.tpl');

                foreach($sql_ as $row){

//                    $tpl->set('{photo}', $row['photo']);
//                    $tpl->set('{title}', stripslashes($row['title']));
//                    $tpl->set('{video-id}', $row['id']);
//                    $tpl->set('{user-id}', $user_id);
//                    $tpl->compile('content');

                }

//                box_navigation($gcount, $count['videos_num'], $page, 'wall.attach_addvideo_public', $pid);

//                $tpl->load_template('/albums/albums_editcover.tpl');
//                $tpl->set('[bottom]', '');
//                $tpl->set('[/bottom]', '');
//                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
//                $tpl->compile('content');
                return view('info.info', $params);
            } else{
                echo '<div class="info_center" style="padding-top:170px">Нет ни одной видеозаписи.</div>';
                return view('info.info', $params);
            }


        }
        return view('info.info', $params);
    }

    /**
     * Бесконечная подгрузка видео из БД
     *
     * @return int
     */
    public function page(): int
    {
//        $tpl = $params['tpl'];
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            //$act = $request['act'];
            $user_id = $user_info['user_id'];
            $limit_vieos = 20;

            $get_user_id = intval($request['get_user_id']);
            $last_id = intval($request['last_id']);
            if(!$get_user_id)
                $get_user_id = $user_id;

            //ЧС
            $CheckBlackList = CheckBlackList($get_user_id);
            if(!$CheckBlackList){
                if($last_id){
                    if($user_id != $get_user_id)
                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        $check_friend = CheckFriends($get_user_id);

                    //Настройки приватности
                    if($user_id == $get_user_id)
                        $sql_privacy = "";
                    elseif($check_friend)
                        $sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
                    else
                        $sql_privacy = "AND privacy = 1";

                    //SQL Запрос
                    $sql_ = $db->super_query("SELECT id, title, photo, comm_num, add_date, SUBSTRING(descr, 1, 180) AS descr FROM `videos` WHERE owner_user_id = '{$get_user_id}' AND id < '{$last_id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_vieos}", 1);

                    //Если есть ответ из БД
                    if($sql_){
//                        $tpl->load_template('videos/short.tpl');
                        foreach($sql_ as $row){
//                            $tpl->set('{photo}', stripslashes($row['photo']));
//                            $tpl->set('{title}', stripslashes($row['title']));
//                            $tpl->set('{id}', $row['id']);
//                            $tpl->set('{user-id}', $get_user_id);
                            if($row['descr']){
//                                $tpl->set('{descr}', stripslashes($row['descr']).'...');

                            }
                            else{
//                                $tpl->set('{descr}', '');

                            }


                            $titles = array('комментарий', 'комментария', 'комментариев');//comments
//                            $tpl->set('{comm}', $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles));

                            $date = \Sura\Time\Date::megaDate(strtotime($row['add_date']));
//                            $tpl->set('{date}', $date);
                            if($get_user_id == $user_id){
//                                $tpl->set('[owner]', '');
//                                $tpl->set('[/owner]', '');
                            } else{
//                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                            }
//                            $tpl->compile('content');
                        }
                    }
                }
            }
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Добавление видео к себе в список
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function addmylist(): int
    {
        //$tpl = $params['tpl'];
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());
            //$act = $request['act'];
            $user_id = $user_info['user_id'];
            //$limit_vieos = 20;

            $vid = intval($request['vid']);

            $config = Settings::load();

            if ($config['video_mod_add_my'] == 'yes'){
                $row = $db->super_query("SELECT video, photo, title, descr FROM `videos` WHERE id = '{$vid}'");
                if($row){
                    //Директория загрузки фото
                    $upload_dir = __DIR__.'/../../public/uploads/videos/'.$user_id;

                    //Если нет папки юзера, то создаём её
                    if(!is_dir($upload_dir)){
                        @mkdir($upload_dir, 0777);
                        @chmod($upload_dir, 0777);
                    }

                    $array = explode('/', $row['photo']);
                    $expPhoto = end($array);
                    copy($row['photo'], __DIR__."/../../public/uploads/videos/{$user_id}/{$expPhoto}");
                    $newPhoto = "{$config['home_url']}uploads/videos/{$user_id}/{$expPhoto}";
                    $row['video'] = $db->safesql($row['video']);
                    $row['descr'] = $db->safesql($row['descr']);
                    $row['title'] = $db->safesql($row['title']);
                    $db->query("INSERT INTO `videos` SET owner_user_id = '{$user_id}', video = '{$row['video']}', photo = '{$newPhoto}', title = '{$row['title']}', descr = '{$row['descr']}', add_date = NOW(), privacy = 1");
                    //$dbid = $db->insert_id();
                    $db->query("UPDATE `users` SET user_videos_num = user_videos_num+1 WHERE user_id = '{$user_id}'");

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/page_videos_user");
                    $cache->remove("{$user_id}/page_videos_user_friends");
                    $cache->remove("{$user_id}/page_videos_user_all");
                    $cache->remove("{$user_id}/profile_{$user_id}");
                    $cache->remove("{$user_id}/videos_num_all");
                    $cache->remove("{$user_id}/videos_num_friends");

                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
                }
            }else{
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
     * Просмотр видео и комментари к видео
     *
     * @return int
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];
            $limit_vieos = 20;

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $get_user_id = intval($path['2']);

            $params['user_id'] = $get_user_id;
            if($get_user_id == $user_id){
                $params['owner'] = true;
                $params['not_owner'] = false;
            } else {
                $params['not_owner'] = true;
                $params['owner'] = false;
            }

            $config = Settings::load();

            if($config['video_mod_add'] == 'yes'){
                $params['admin_video_add'] = true;
            } else{
                $params['admin_video_add'] = false;
            }


            //################### Вывод всех видео ###################//
            //$get_user_id = intval($_GET['get_user_id']);
            if(!$get_user_id)
                $get_user_id = $user_id;

            //ЧС
            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($get_user_id);
            if(!$CheckBlackList){

                //Выводи кол-во видео записей
                $owner = $db->super_query("SELECT user_videos_num, user_search_pref FROM `users` WHERE user_id = '{$get_user_id}'");
                if($owner){
                    $name_info = explode(' ', $owner['user_search_pref']);
                    $params['title'] = $lang['videos'].' '.Gramatic::gramatikName($name_info['0']).' '.Gramatic::gramatikName($name_info['1']).' | Sura';

                    $check_friend = null;

                    if($user_id != $get_user_id)
                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        $check_friend = (new \App\Libs\Friends)->CheckFriends($get_user_id);

                    //Настройки приватности
                    if($user_id == $get_user_id)
                        $sql_privacy = "";
                    elseif($check_friend){
                        $sql_privacy = "AND privacy regexp '[[:<:]](1|2)[[:>:]]'";
                        $cache_pref = '_friends';
                    } else {
                        $sql_privacy = "AND privacy = 1";
                        $cache_pref = '_all';
                    }

                    //Если страницу смотрит другой юзер, то считаем кол-во видео
                    if($user_id != $get_user_id){
                        $video_cnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = '{$get_user_id}' {$sql_privacy} AND public_id = '0'", false, "user_{$get_user_id}/videos_num{$cache_pref}");
                        $owner['user_videos_num'] = $video_cnt['cnt'];
                    }

//                    $titles = array('видеозапись', 'видеозаписи', 'видеозаписей');//videos
//                    if($get_user_id == $user_id)
//                        $user_speedbar = 'У Вас <span id="nums">'.($owner['user_videos_num'] ? $owner['user_videos_num'] : false).'</span> '.Gramatic::declOfNum($owner['user_videos_num'], $titles);
//                    else
//                        $user_speedbar = 'У '.Gramatic::gramatikName($name_info[0]).' '.($owner['user_videos_num'] ? $owner['user_videos_num'] : false).' '.Gramatic::declOfNum($owner['user_videos_num'], $titles);

                    if($owner['user_videos_num']){

                        //SQL Запрос
                        $sql_ = $db->super_query("SELECT id, title, photo, comm_num, add_date, SUBSTRING(descr, 1, 180) AS descr FROM `videos` WHERE owner_user_id = '{$get_user_id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0, {$limit_vieos}", 1);

                        //Загружаем меню по видео
//                        $tpl->load_template('videos/head.tpl');
                        $params['videos_num'] = $owner['user_videos_num'];
                        $params['name'] = Gramatic::gramatikName($name_info['0']);

                        if($sql_){
//                            $tpl->load_template('videos/short.tpl');
                            foreach($sql_ as $key => $row){
                                $params[$sql_]['photo'] = stripslashes($row['photo']);
                                $params[$sql_]['title'] = stripslashes($row['title']);
                                $params[$sql_]['user_id'] = $get_user_id;
                                $params[$sql_]['id'] = $row['id'];
                                if($row['descr']){
                                    $params[$sql_]['descr'] = stripslashes($row['descr']).'...';
                                }
                                else{
                                    $params[$sql_]['descr'] = '';
                                }

                                $titles = array('комментарий', 'комментария', 'комментариев');//comments
                                $params[$sql_]['comm'] = $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles);

                                $date = \Sura\Time\Date::megaDate(strtotime($row['add_date']));
                                $params[$sql_]['date'] = $date;
                                if($get_user_id == $user_id){
                                    $params[$sql_]['owner'] = true;
                                } else{
                                    $params[$sql_]['owner'] = false;
                                }
                            }
                            $params['videos'] = $sql_;

                        } else{
//                            msgbox('', $lang['videos_nones_videos_user'], 'info_2');

                        }
                    } else {
                        if($get_user_id == $user_id){
//                            msgbox('', $lang['videos_nones_videos_user'], 'info_2');

                        }
                        else{
//                            msgbox('', $owner['user_search_pref'].' '.$lang['videos_none'], 'info_2');

                        }
                    }
                } else{
//                    Hacking();

                }
            } else {
//                $user_speedbar = $lang['error'];
//                msgbox('', $lang['no_notes'], 'info');
            }
            return view('videos.videos', $params);
        }
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);

    }
}