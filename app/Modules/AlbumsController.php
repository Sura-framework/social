<?php

namespace App\Modules;

use Exception;
use Intervention\Image\ImageManager;
use JsonException;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;
use Sura\Time\Date;

class AlbumsController extends Module{

    /**
     * Создание альбома
     * @throws JsonException
     */
    public function create(){
        $lang = $this->get_langs();

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if ($logged){
            $name = Validation::ajax_utf8(Validation::textFilter($request['name'], 25000, true));
            $descr = Validation::ajax_utf8(Validation::textFilter($request['descr']));
            $privacy = (int)$request['privacy'];
            $privacy_comm = (int)($request['privacy_comm']);
            if($privacy <= 0 OR $privacy > 3) {
                $privacy = 1;
            }
            if($privacy_comm <= 0 OR $privacy_comm > 3) {
                $privacy_comm = 1;
            }
            $sql_privacy = $privacy.'|'.$privacy_comm;

            if(isset($name) AND !empty($name)){

                //Выводи кол-во альбомов у юзера
                $row = $db->super_query("SELECT user_albums_num FROM `users` WHERE user_id = '{$user_info['user_id']}'");

                $config = Settings::load();

                if($row['user_albums_num'] < $config['max_albums']){
                    $server_time = Date::time();
                    $_IP = Request::getRequest()->getClientIP();
                    //hash
                    $hash = md5(md5($server_time).$name.$descr.md5($user_info['user_id']).md5($user_info['user_email']).$_IP);
                    $date_create = date('Y-m-d H:i:s', $server_time);

                    $sql_ = $db->query("INSERT INTO `albums` (user_id, name, descr, ahash, adate, position, privacy) VALUES ('{$user_info['user_id']}', '{$name}', '{$descr}', '{$hash}', '{$date_create}', '0', '{$sql_privacy}')");
                    $id = $db->insert_id();
                    $db->query("UPDATE `users` SET user_albums_num = user_albums_num+1 WHERE user_id = '{$user_info['user_id']}'");

//                    mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends|user_{$user_info['user_id']}/albums_cnt_friends|user_{$user_info['user_id']}/albums_cnt_all|user_{$user_info['user_id']}/profile_{$user_info['user_id']}");
                    if($sql_){
//                        echo '/albums/add/'.$id;
                        $status = Status::OK;
                        $data = '/albums/add/'.$id;
                    }else{
//                        echo 'no';
                        $status = Status::BAD;
                        $data = null;
                    }
                } else{
//                    echo 'max';
                    $status = Status::BIG_SIZE;
                    $data = null;
                }
            } else{
//                echo 'no_name';
                $status = Status::NOT_DATA;
                $data = null;
            }
        }else{
            $status = Status::BAD_LOGGED;
            $data = null;
        }
        return  _e_json(array(
            'res' => $data,
            'status' => $status,
        ) );
    }

    /**
     * @return int
     * @throws JsonException
     */
    public function create_page(): int
    {
        $params = array();

        $data =  view_data('albums.create_page', $params);
        $status = Status::OK;
        return  _e_json(array(
            'res' => $data,
            'status' => $status,
        ) );
    }

    /**
     * Страница создания альбома
     * @throws JsonException
     * @throws \Throwable
     */
    public function create_page1(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        $request = (Request::getRequest()->getGlobal());

        Tools::NoAjaxRedirect();

        if($logged){
            $name = Validation::ajax_utf8(Validation::textFilter($request['name'], 25000, true));
            $descr = Validation::ajax_utf8(Validation::textFilter($request['descr']));
            $privacy = (int)($request['privacy']);
            $privacy_comm = (int)($request['privacy_comm']);
            if($privacy <= 0 OR $privacy > 3) $privacy = 1;
            if($privacy_comm <= 0 OR $privacy_comm > 3) $privacy_comm = 1;
            $sql_privacy = $privacy.'|'.$privacy_comm;

            if(isset($name) AND !empty($name)){

                //Выводи кол-во альбомов у юзера
                $row = $db->super_query("SELECT user_albums_num FROM `users` WHERE user_id = '{$user_info['user_id']}'");
                $config = Settings::load();
                if($row['user_albums_num'] < $config['max_albums']){
                    //hash
                    $_IP = Request::getRequest()->getClientIP();
                    $server_time = Date::time();
                    $hash = md5(md5($server_time).$name.$descr.md5($user_info['user_id']).md5($user_info['user_email']).$_IP);
                    $date_create = date('Y-m-d H:i:s', $server_time);

                    $sql_ = $db->query("INSERT INTO `albums` (user_id, name, descr, ahash, adate, position, privacy) VALUES ('{$user_info['user_id']}', '{$name}', '{$descr}', '{$hash}', '{$date_create}', '0', '{$sql_privacy}')");
                    $id = $db->insert_id();
                    $db->query("UPDATE `users` SET user_albums_num = user_albums_num+1 WHERE user_id = '{$user_info['user_id']}'");

                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_info['user_id']}/albums");
                    $cache->remove("{$user_info['user_id']}/albums_all");
                    $cache->remove("{$user_info['user_id']}/albums_friends");
                    $cache->remove("{$user_info['user_id']}/albums_cnt_all");
                    $cache->remove("{$user_info['user_id']}/profile__{$user_info['user_id']}");

                    if($sql_){
                        $link = '/albums/add/'.$id;
                        $status = Status::OK;
                    }else{
                        $link = null;
                        $status = Status::NOT_FOUND;
                    }
                } else{
                    $link = null;
                    $status = Status::MAX;
                }
            } else{
                $link = null;
                $status = Status::NOT_DATA;
            }
        } else{
            $link = null;
            $status = Status::BAD_LOGGED;
        }
        return  _e_json(array(
            'link' => $link,
            'status' => $status,
        ) );
    }
	
	/**
	 * Страница добавление фотографий в альбом
	 * @return bool
	 * @throws JsonException
	 */
    public function add(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = (int)$path['3'];

            //$aid = intval($_GET['aid']);
            $user_id = $user_info['user_id'];

            //Проверка на существование альбома
            $row = $db->super_query("SELECT name, aid FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");
            if($row){
                //$params['title'] = $lang['add_photo'];
                $params['title'] = $lang['add_photo'];
                $user_speedbar = $lang['add_photo_2'];
//                $tpl->load_template('/albums/albums_addphotos.tpl');
//                $tpl->set('{aid}', $aid);
//                $tpl->set('{album-name}', stripslashes($row['name']));
//                $tpl->set('{user-id}', $user_id);
//                $tpl->set('{PHPSESSID}', $_COOKIE['PHPSESSID']);
//                $tpl->compile('content');
	
	            $status = Status::OK;
            }else{
	            $status = Status::NOT_FOUND;
            }
        }else{
	        $status = Status::BAD_LOGGED;
        }
	    return  _e_json(array(
		    'status' => $status,
	    ) );
    }

    /**
     * Загрузка фотографии в альбом
     * @throws JsonException
     * @throws Exception
     * @throws \Throwable
     */
    public function upload(): int
    {
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = (int)$path['3'];

            //$aid = intval($_GET['aid']);
            $user_id = $user_info['user_id'];

            //Проверка на существование альбома и то что загружает владелец альбома
            $row = $db->super_query("SELECT aid, photo_num, cover FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");
            if($row){
                $config = Settings::load();
                //Проверка на кол-во фоток в альбоме
                if($row['photo_num'] < $config['max_album_photos']){

                    //Директория юзеров
                    $uploaddir = __DIR__.'/../../public/uploads/users/';

                    //Если нет папок юзера, то создаём их
                    if(!is_dir($uploaddir.$user_id)){
                        if (!mkdir($concurrentDirectory = $uploaddir . $user_id, 0777) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                        }
                        @chmod($uploaddir.$user_id, 0777 );
                        if (!mkdir($concurrentDirectory = $uploaddir . $user_id . '/albums', 0777) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                        }
                        @chmod($uploaddir.$user_id.'/albums', 0777 );
                    }

                    //Если нет папки альбома, то создаём её
                    $upload_dir = __DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$aid.'/';
                    if(!is_dir($upload_dir)){
                        if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                        }
                        @chmod($upload_dir, 0777);
                    }
                    $config = Settings::load();
                    //Разришенные форматы
                    $allowed_files = explode(', ', $config['photo_format']);

                    //Получаем данные о фотографии
                    $image_tmp = $_FILES['uploadfile']['tmp_name'];
                    $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                    $server_time = Date::time();
                    $image_rename = substr(md5($server_time+random_int(1,100000)), 0, 20); // имя фотографии
                    $image_size = $_FILES['uploadfile']['size']; // размер файла
                    $image_name_arr = explode(".", $image_name);
                    $type = end($image_name_arr); // формат файла

                    //Проверям если, формат верный то пропускаем
                    if(in_array(strtolower($type), $allowed_files, true)){
                        $config = Settings::load();
                        $config['max_photo_size'] *= 1000;
                        if($image_size < $config['max_photo_size']){
                            $res_type = strtolower('.'.$type);

                            if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){

                                //Подключаем класс для фотографий
//                                        include __DIR__.'/../Classes/images.php';

                                //Создание оригинала
                                $manager = new ImageManager(array('driver' => 'gd'));
                                $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null);
                                $image->save($upload_dir.$image_rename.'.webp', 85);

                                //Создание маленькой копии
                                $manager = new ImageManager(array('driver' => 'gd'));
                                $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(140, 100);
                                $image->save($upload_dir.'c_'.$image_rename.'.webp', 90);

                                unlink($upload_dir.$image_rename.$res_type);
                                $res_type = '.webp';

                                $date = date('Y-m-d H:i:s', $server_time);

                                //Генерируем position фотки для "обзо фотографий"
                                $position_all = $_SESSION['position_all'];
                                if($position_all){
                                    ++$position_all;
                                    $_SESSION['position_all'] = $position_all;
                                } else {
                                    $position_all = 100000;
                                    $_SESSION['position_all'] = $position_all;
                                }

                                //Вставляем фотографию
                                $db->query("INSERT INTO `photos` (album_id, photo_name, user_id, date, position) VALUES ('{$aid}', '{$image_rename}{$res_type}', '{$user_id}', '{$date}', '{$position_all}')");
                                $ins_id = $db->insert_id();

                                //Проверяем на наличии обложки у альбома, если нету то ставим обложку загруженную фотку
                                if(!$row['cover']) {
                                    $db->query("UPDATE `albums` SET cover = '{$image_rename}{$res_type}' WHERE aid = '{$aid}'");
                                }

                                $db->query("UPDATE `albums` SET photo_num = photo_num+1, adate = '{$date}' WHERE aid = '{$aid}'");

                                $config = Settings::load();
                                $img_url = $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$aid.'/c_'.$image_rename.$res_type;

                                //Результат для ответа
                                echo $ins_id.'|||'.$img_url.'|||'.$user_id;

                                $photos_num = null; // bug !!!


                                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                                $cache = new \Sura\Cache\Cache($storage, 'users');

                                //Удаляем кеш позиций фотографий
                                if(!$photos_num)
                                {
                                    $cache->remove("{$user_id}/profile_".$user_id);
                                }

                                //Чистим кеш
                                $cache->remove("{$user_id}/albums");
                                $cache->remove("{$user_id}/albums_all");
                                $cache->remove("{$user_id}/albums_friends");
                                $cache->remove("{$user_id}/position_photos_album_{$aid}");

                                $img_url = str_replace($config['home_url'], '/', $img_url);

                                //Добавляем действия в ленту новостей
                                $generateLastTime = $server_time-10800;
                                $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 3 AND ac_user_id = '{$user_id}'");
                                if($row) {
                                    $db->query("UPDATE `news` SET action_text = '{$ins_id}|{$img_url}||{$row['action_text']}', action_time = '{$server_time}' WHERE ac_id = '{$row['ac_id']}'");
                                }
                                else {
                                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 3, action_text = '{$ins_id}|{$img_url}', action_time = '{$server_time}'");
                                }

                                $status = Status::OK;
                            } else{
                                $status = Status::BAD_MOVE;
                            }
                        } else{
                            $status = Status::BIG_SIZE;
                        }
                    } else{
                        $status = Status::BAD_FORMAT;
                    }
                } else{
                    $status = Status::MAX;
                }
            } else{
                $status = Status::NOT_FOUND;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return  _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаление фотографии из альбома
     * @throws JsonException
     * @throws \Throwable
     */
    public function del_photo(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        $request = (Request::getRequest()->getGlobal());

        Tools::NoAjaxRedirect();

        if($logged){
            $id = (int)$request['id'];
            $user_id = $user_info['user_id'];

            $row = $db->super_query("SELECT user_id, album_id, photo_name, comm_num, position FROM `photos` WHERE id = '{$id}'");

            //Если есть такая фотография и владельце действителен
            if($row['user_id'] == $user_id){

                //Директория удаления
                $del_dir = __DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/';

                //Удаление фотки с сервера
                @unlink($del_dir.'c_'.$row['photo_name']);
                @unlink($del_dir.$row['photo_name']);

                //Удаление фотки из БД
                $db->query("DELETE FROM `photos` WHERE id = '{$id}'");

                $check_photo_album = $db->super_query("SELECT id FROM `photos` WHERE album_id = '{$row['album_id']}'");
                $album_row = $db->super_query("SELECT cover FROM `albums` WHERE aid = '{$row['album_id']}'");

                //Если удаляемая фотография является обложкой то обновляем обложку на последнюю фотографию, если фотки еще есть из альбома
                if($album_row['cover'] == $row['photo_name'] AND $check_photo_album){
                    $row_last_photo = $db->super_query("SELECT photo_name FROM `photos` WHERE user_id = '{$user_id}' AND album_id = '{$row['album_id']}' ORDER by `id` DESC");
                    $set_cover = ", cover = '{$row_last_photo['photo_name']}'";
                }

                //Если в альбоме уже нет фоток, то удаляем обложку
                if(!$check_photo_album)
                    $set_cover = ", cover = ''";

                //Удаляем комментарии к фотографии
                $db->query("DELETE FROM `photos_comments` WHERE pid = '{$id}'");

                //Обновляем количество комментов у альбома
                $db->query("UPDATE `albums` SET photo_num = photo_num-1, comm_num = comm_num-{$row['comm_num']} {$set_cover} WHERE aid = '{$row['album_id']}'");

                //Чистим кеш
                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');

                $cache->remove("{$user_id}/albums");
                $cache->remove("{$user_id}/albums_all");
                $cache->remove("{$user_id}/albums_friends");
                $cache->remove("{$user_id}/position_photos_album_{$row['album_id']}");

                //Выводим и удаляем отметки если они есть
                $sql_mark = $db->super_query("SELECT muser_id FROM `photos_mark` WHERE mphoto_id = '".$id."' AND mapprove = '0'", 1);
                if($sql_mark){
                    foreach($sql_mark as $row_mark){
                        $db->query("UPDATE `users` SET user_new_mark_photos = user_new_mark_photos-1 WHERE user_id = '".$row_mark['muser_id']."'");
                    }
                }
                $db->query("DELETE FROM `photos_mark` WHERE mphoto_id = '".$id."'");
                //Удаляем оценки
                $db->query("DELETE FROM `photos_rating` WHERE photo_id = '".$id."'");

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
     * Установка новой обложки для альбома
     * @throws JsonException
     * @throws \Throwable
     */
    public function set_cover(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        $request = (Request::getRequest()->getGlobal());

        Tools::NoAjaxRedirect();

        if($logged){
            $id = (int)$request['id'];
            $user_id = $user_info['user_id'];

            //Выводи фотку из БД, если она есть
            $row = $db->super_query("SELECT album_id, photo_name FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $db->query("UPDATE `albums` SET cover = '{$row['photo_name']}' WHERE aid = '{$row['album_id']}'");

                //Чистим кеш
                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/albums");
                $cache->remove("{$user_id}/albums_all");
                $cache->remove("{$user_id}/albums_friends");

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
     * Сохранение описания к фотографии
     * @throws JsonException
     */
    public function save_descr(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];
            $user_id = $user_info['user_id'];
            $descr = Validation::ajax_utf8(Validation::textFilter($request['descr']));

            //Выводим фотку из БД, если она есть
            $row = $db->super_query("SELECT id FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $db->query("UPDATE `photos` SET descr = '{$descr}' WHERE id = '{$id}' AND user_id = '{$user_id}'");

                //Ответ скрипта
//                echo stripslashes(Validation::myBr(htmlspecialchars(Validation::ajax_utf8(trim($request['descr'])))));

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
     * Страница редактирование фотографии
     * @throws JsonException
     */
    public function editphoto(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];
            $user_id = $user_info['user_id'];
            $row = $db->super_query("SELECT descr FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row)
            {
//                echo stripslashes(Validation::myBrRn($row['descr']));
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
     * Сохранение сортировки альбомов
     * @throws JsonException
     * @throws \Throwable
     */
    public function save_pos_albums(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $array = $request['album'];
            $count = 1;

            $config = Settings::load();

            if ($array){
	            //Если есть данные о масиве
	            if($config['albums_drag'] == 'yes'){
		            //Выводим масивом и обновляем порядок
		            foreach($array as $idval){
			            $idval = (int)$idval;
			            $db->query("UPDATE `albums` SET position = ".$count." WHERE aid = '{$idval}' AND user_id = '{$user_info['user_id']}'");
			            $count++;
		            }
		
		            //Чистим кеш
		            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		            $cache = new \Sura\Cache\Cache($storage, 'users');
		            $cache->remove("{$user_id}/albums");
		            $cache->remove("{$user_id}/albums_all");
		            $cache->remove("{$user_id}/albums_friends");
		
		            $status = Status::OK;
	            }else{
		            $status = Status::BAD_RIGHTS;
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
     * Сохранение сортировки фотографий
     * @throws JsonException
     * @throws \Throwable
     */
    public function save_pos_photos(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){

            $array	= $_POST['photo'];
            $count = 1;

            $config = Settings::load();

            //Если есть данные о масиве
            if($config['photos_drag'] == 'yes'){
                if ($array){
                    //Выводим масивом и обновляем порядок
                    $row = $db->super_query("SELECT album_id FROM `photos` WHERE id = '{$array[1]}'");
                    if($row){
                        $photo_info = '';
                        foreach($array as $idval){
                            $idval = (int)$idval;
                            $db->query("UPDATE `photos` SET position = '{$count}' WHERE id = '{$idval}' AND user_id = '{$user_info['user_id']}'");
                            $photo_info .= $count.'|'.$idval.'||';
                            $count ++;
                        }
//                    Cache::mozg_create_cache('
//                    user_'.$user_info['user_id'].'/position_photos_album_'.$row['album_id'], $photo_info);

                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $key = "{$user_id}/position_photos_album_{$row['album_id']}";
                        $value = $photo_info;
                        $cache->save($key, $value);

                        $status = Status::OK;
                    }else{
                        $status = Status::NOT_FOUND;
                    }
                }else{
                    $status = Status::NOT_DATA;
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
     * Страница редактирование альбома
     * @return string
     * @throws Exception
     */
    public function edit_page(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();
        $params = array();

        if($logged){

            $user_id = $user_info['user_id'];
            $id = $db->safesql((int)$request['id']);
            $row = $db->super_query("SELECT aid, name, descr, privacy FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $album_privacy = explode('|', $row['privacy']);
//                $tpl->load_template('/albums/albums_edit.tpl');
//                $tpl->set('{id}', $row['aid']);
//                $tpl->set('{name}', stripslashes($row['name']));
//                $tpl->set('{descr}', stripslashes(myBrRn($row['descr'])));
//                $tpl->set('{privacy}', $album_privacy[0]);
//                $tpl->set('{privacy-text}', strtr($album_privacy[0], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
//                $tpl->set('{privacy-comment}', $album_privacy[1]);
//                $tpl->set('{privacy-comment-text}', strtr($album_privacy[1], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
//                $tpl->compile('content');


            }

            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Сохранение настроек альбома
     * @throws JsonException
     * @throws \Throwable
     */
    public function save_album(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){

            $id = (int)$request['id'];
            $user_id = $user_info['user_id'];
            $name = Validation::ajax_utf8(Validation::textFilter($request['name']));
            $descr = Validation::ajax_utf8(Validation::textFilter($request['descr']));

            $privacy = (int)$request['privacy'];
            $privacy_comm = (int)$request['privacy_comm'];
            if($privacy <= 0 OR $privacy > 3) $privacy = 1;
            if($privacy_comm <= 0 OR $privacy_comm > 3) $privacy_comm = 1;
            $sql_privacy = $privacy.'|'.$privacy_comm;

            //Проверка на существование юзера
            $chekc_user = $db->super_query("SELECT privacy FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");
            if($chekc_user){
                if(isset($name) AND !empty($name)){
                    $db->query("UPDATE `albums` SET name = '{$name}', descr = '{$descr}', privacy = '{$sql_privacy}' WHERE aid = '{$id}'");
                    echo stripslashes($name).'|#|||#row#|||#|'.stripslashes($descr);


                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/albums");
                    $cache->remove("{$user_id}/albums_all");
                    $cache->remove("{$user_id}/albums_friends");
                    $cache->remove("{$user_id}/albums_cnt_friends");
                    $cache->remove("{$user_id}/albums_cnt_all");

                    $status = Status::OK;
                } else{
                    $status = Status::NOT_DATA;
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
     * Страница изминения обложки
     * @return int
     */
    public function edit_cover(): int
    {
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){

            $user_id = $user_info['user_id'];
            $id = (int)$request['id'];

            if($id){

                //Для навигатор
                if($request['page'] > 0) {
                    $page = (int)$_POST['page'];
                } else {
                    $page = 1;
                }
                $gcount = 36;
                $limit_page = ($page-1)*$gcount;

                //Делаем SQL запрос на вывод
                $sql_ = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$id}' AND user_id = '{$user_id}' ORDER by `position` ASC LIMIT {$limit_page}, {$gcount}", 1);

                //Если есть SQL запрос то пропускаем
                if($sql_){

                    //Выводим данные о альбоме (кол-во фотографй)
                    $row_album = $db->super_query("SELECT photo_num FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");

//                    $tpl->load_template('/albums/albums_editcover.tpl');
//                    $tpl->set('[top]', '');
//                    $tpl->set('[/top]', '');
//                    $titles = array('фотография', 'фотографии', 'фотографий');//photos
//                    $tpl->set('{photo-num}', $row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles));
//                    $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
//                    $tpl->compile('content');

                    $config = Settings::load();

                    //Выводим масивом фотографии
                    $tpl->load_template('/albums/albums_editcover_photo.tpl');
                    foreach($sql_ as $row){
//                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$id.'/c_'.$row['photo_name']);
//                        $tpl->set('{id}', $row['id']);
//                        $tpl->set('{aid}', $id);
//                        $tpl->compile('content');
                    }
                    Tools::box_navigation($gcount, $row_album['photo_num'], $id, 'Albums.EditCover', '');

//                    $tpl->load_template('/albums/albums_editcover.tpl');
//                    $tpl->set('[bottom]', '');
//                    $tpl->set('[/bottom]', '');
//                    $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
//                    $tpl->compile('content');


                    return view('info.info', $params);
                } else {
                    echo $lang['no_photo_alnumx'];
                }
            } else {
                return '';
            }
        }
        return view('info.info', $params);
    }

    /**
     * Страница всех фотографий юзера, для прикрепления своей фотки кому-то на стену
     */
    public function all_photos_box(): int
    {
//        $tpl = Registry::get('tpl');
//        $db = $this->db();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){

            $user_id = $user_info['user_id'];

            //Для навигатор
            if($request['page'] > 0) {
                $page = (int)$request['page'];
            } else {
                $page = 1;
            }
            $gcount = 36;
            $limit_page = ($page-1)*$gcount;

            //Делаем SQL запрос на вывод
            $sql_ = $db->query("SELECT id, photo_name, album_id FROM `photos` WHERE user_id = '{$user_id}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}");
            $row_album = $db->super_query("SELECT SUM(photo_num) AS photo_num FROM `albums` WHERE user_id = '{$user_id}'");

            //Если есть Фотографии то пропускаем
            if($row_album['photo_num']){

//               $tpl->load_template('wall/attatch_addphoto_top.tpl');
                $params['top'] = true;
                $titles = array('фотография', 'фотографии', 'фотографий');//photos
                $params['photo_num'] =  $row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles);
                $params['bottom'] = false;

                //Выводим циклом фотографии
//              $tpl->load_template('/albums/albums_all_photos.tpl');
                $photos = $db->get_row($sql_);
                foreach ($photos as $key => $row){
                    $photos[$key]['photo'] = '/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/c_'.$row['photo_name'];
                    $photos[$key]['photo_name'] = $row['photo_name'];
                    $photos[$key]['user_id'] = $user_id;
                    $photos[$key]['photo_id'] = $row['id'];
                    $photos[$key]['aid'] =  $row['album_id'];
                }
                $params['photos'] = $photos;
                $params['navigation'] = Tools::box_navigation($gcount, $row_album['photo_num'], $page, 'wall.attach_addphoto', false);
//                $tpl->load_template('/albums/albums_editcover.tpl');

                return view('wall.attach_photo', $params);
            } else {
                $params = array();

//                echo $lang['no_photo_alnumx'].'<br /><br /><div class="button_div_gray fl_l" style="margin-left:205px"><button id="upload">Загрузить новую фотографию</button></div>';
                return view('wall.attach_photo', $params);
//                return view('info.info', $params);
            }

        }
        return view('info.info', array());
    }

    /**
     * Удаление альбома
     * @throws JsonException
     * @throws \Throwable
     */
    public function del_album(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        if($logged){

            $hash = $db->safesql(substr($_POST['hash'], 0, 32));
            $row = $db->super_query("SELECT aid, user_id, photo_num FROM `albums` WHERE ahash = '{$hash}'");

            if($row){
                $aid = $row['aid'];
                $user_id = $row['user_id'];

                //Удаляем альбом
                $db->query("DELETE FROM `albums` WHERE ahash = '{$hash}'");

                //Проверяем еслить ли фотки в альбоме
                if($row['photo_num']){

                    //Удаляем фотки
                    $db->query("DELETE FROM `photos` WHERE album_id = '{$aid}'");

                    //Удаляем комментарии к альбому
                    $db->query("DELETE FROM `photos_comments` WHERE album_id = '{$aid}'");

                    //Удаляем фотки из папки на сервере
                    $fdir = opendir(__DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$aid);
                    while($file = readdir($fdir))
                        @unlink(__DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$aid.'/'.$file);

                    @rmdir(__DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$aid);
                }

                //Обновлям кол-во альбом в юзера
                $db->query("UPDATE `users` SET user_albums_num = user_albums_num-1 WHERE user_id = '{$user_id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                //Удаляем кеш позиций фотографий и кеш профиля
                $cache->remove("{$row['user_id']}/position_photos_album_{$row['aid']}");
                $cache->remove("{$user_info['user_id']}/profile_{$user_info['user_id']}");
                $cache->remove("{$user_id}/albums");
                $cache->remove("{$user_id}/albums_all");
                $cache->remove("{$user_id}/albums_friends");
                $cache->remove("{$user_id}/albums_cnt_friends");
                $cache->remove("{$user_id}/albums_cnt_all");

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
     * Просмотр всех комментариев к альбому
     * @return int
     */
    public function all_comments(): int
    {
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            //$act = $_GET['act'];

//            $mobile_speedbar = 'Комментарии';

            $user_id = $user_info['user_id'];
            $uid = (int)$request['uid'];
            $aid = (int)$request['aid'];


            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = (int)$path['3'];

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $page = (int)$path['5'];


            if($aid) {
                $uid = false;
            }
            if($uid) {
                $aid = false;
            }

            if($page > 0) {
                $page = (int)$page;
            } else {
                $page = 1;
            }
            $gcount = 25;
            $limit_page = ($page-1) * $gcount;

            $privacy = true;

            //Если вызваны комменты к альбому
            if($aid AND !$uid){
                $row_album = $db->super_query("SELECT user_id, name, privacy FROM `albums` WHERE aid = '{$aid}'");
                $album_privacy = explode('|', $row_album['privacy']);
                $uid = $row_album['user_id'];
                if(!$uid) {
                    Hacking();
                }
            }

            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($uid);

            if($user_id != $uid)
                //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                $check_friend = (new \App\Libs\Friends)->CheckFriends($uid);

            if($aid AND $album_privacy){
                if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_id == $uid) {
                    $privacy = true;
                }
                else {
                    $privacy = false;
                }
            }

            //Приватность
            if($privacy AND !$CheckBlackList){
                if($uid AND !$aid){
                    $sql_tb3 = ", `albums` tb3";

                    if($user_id == $uid){
                        $privacy_sql = "";
                        $sql_tb3 = "";
                    } elseif($check_friend){
                        $privacy_sql = "AND tb1.album_id = tb3.aid AND SUBSTRING(tb3.privacy, 1, 1) regexp '[[:<:]](1|2)[[:>:]]'";
                        $cache_cnt_num = "_friends";
                    } else {
                        $privacy_sql = "AND tb1.album_id = tb3.aid AND SUBSTRING(tb3.privacy, 1, 1) regexp '[[:<:]](1)[[:>:]]'";
                        $cache_cnt_num = "_all";
                    }
                }

                //Если вызвана страница всех комментариев юзера, если нет, то значит вызвана страница оприделенго альбома
                if($uid AND !$aid)
                    $sql_ = $db->super_query("SELECT tb1.user_id, text, date, id, hash, album_id, pid, owner_id, photo_name, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `photos_comments` tb1, `users` tb2 {$sql_tb3} WHERE tb1.owner_id = '{$uid}' AND tb1.user_id = tb2.user_id {$privacy_sql} ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);
                else
                    $sql_ = $db->super_query("SELECT tb1.user_id, text, date, id, hash, album_id, pid, owner_id, photo_name, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `photos_comments` tb1, `users` tb2 WHERE tb1.album_id = '{$aid}' AND tb1.user_id = tb2.user_id ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);

                //Выводи имя владельца альбомов
                $row_owner = $db->super_query("SELECT user_name FROM `users` WHERE user_id = '{$uid}'");

                //Если вызвана страница всех комментов
                if($uid AND !$aid){
                    $user_speedbar = $lang['comm_form_album_all'];
                    //$params['title'] = $lang['comm_form_album_all'];
                    $params['title'] = $lang['comm_form_album_all'].' | Sura';
                } else {
                    $user_speedbar = $lang['comm_form_album'];
                    //$params['title'] = $lang['comm_form_album'];
                    $params['title'] = $lang['comm_form_album'].' | Sura';
                }

                //Загружаем HEADER альбома
//                $tpl->load_template('/albums/albums_top.tpl');
//                $tpl->set('{user-id}', $uid);
//                $tpl->set('{aid}', $aid);
//                $tpl->set('{name}', Gramatic::gramatikName($row_owner['user_name']));
//                $tpl->set('{album-name}', stripslashes($row_album['name']));
//                $tpl->set('[comments]', '');
//                $tpl->set('[/comments]', '');
//                $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
//                $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
//                $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
//                $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                if($uid AND !$aid){
//                    $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
                } else {
//                    $tpl->set('[albums-comments]', '');
//                    $tpl->set('[/albums-comments]', '');
//                    $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                }
                if($uid == $user_id){
//                    $tpl->set('[owner]', '');
//                    $tpl->set('[/owner]', '');
//                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                } else {
//                    $tpl->set('[not-owner]', '');
//                    $tpl->set('[/not-owner]', '');
//                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                }
//                $tpl->compile('info');

                //Если есть ответ о запросе то выводим
                if($sql_){

//                    $tpl->load_template('/albums/albums_comment.tpl');
                    foreach($sql_ as $row_comm){
//                        $tpl->set('{comment}', stripslashes($row_comm['text']));
//                        $tpl->set('{uid}', $row_comm['user_id']);
//                        $tpl->set('{id}', $row_comm['id']);
//                        $tpl->set('{hash}', $row_comm['hash']);
//                        $tpl->set('{author}', $row_comm['user_search_pref']);

                        $config = Settings::load();

                        //Выводим данные о фотографии
//                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$uid.'/albums/'.$row_comm['album_id'].'/c_'.$row_comm['photo_name']);
//                        $tpl->set('{pid}', $row_comm['pid']);
//                        $tpl->set('{user-id}', $row_comm['owner_id']);

                        if($aid){
//                            $tpl->set('{aid}', '_'.$aid);
//                            $tpl->set('{section}', 'album_comments');
                        } else {
//                            $tpl->set('{aid}', '');
//                            $tpl->set('{section}', 'all_comments');
                        }

                        if($row_comm['user_photo'])
                        {
//                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
                        }
                        else
                        {
//                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        }

                        $online = \App\Libs\Profile::Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
//                        $tpl->set('{online}', $online);

                        $date = Date::megaDate(strtotime($row_comm['date']));
//                        $tpl->set('{date}', $date);

                        if($row_comm['user_id'] == $user_info['user_id'] OR $user_info['user_id'] == $uid){
//                            $tpl->set('[owner]', '');
//                            $tpl->set('[/owner]', '');
                        } else
                        {
//                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                        }

//                        $tpl->compile('content');
                    }

                    if($uid AND !$aid) {
                        if ($user_id == $uid) {
                            $row_album = $db->super_query("SELECT SUM(comm_num) AS all_comm_num FROM `albums` WHERE user_id = '{$uid}'", false, "user_{$uid}/albums_{$uid}_comm{$cache_cnt_num}");
                        }
                        else {
                            $row_album = $db->super_query("SELECT COUNT(*) AS all_comm_num FROM `photos_comments` tb1, `albums` tb3 WHERE tb1.owner_id = '{$uid}' {$privacy_sql}", false, "user_{$uid}/albums_{$uid}_comm{$cache_cnt_num}");
                        }
                    }
                    else {
                        $row_album = $db->super_query("SELECT comm_num AS all_comm_num FROM `albums` WHERE aid = '{$aid}'");
                    }

                    if($uid AND !$aid) {
                        $tpl = Tools::navigation($gcount, $row_album['all_comm_num'], $config['home_url'] . 'albums/comments/' . $uid . '/page/');
                    }
                    else {
                        $tpl = Tools::navigation($gcount, $row_album['all_comm_num'], $config['home_url'] . 'albums/view/' . $aid . '/comments/page/');
                    }

//                    $titles = array('комментарий', 'комментария', 'комментариев');
//                    $user_speedbar = $row_album['all_comm_num'].' '.Gramatic::declOfNum($row_album['all_comm_num'], $titles);

                    return view('info.info', $params);
                } else
//                    msg_box( $lang['no_comments'], 'info_2');

                {
                    return view('info.info', $params);
                }
            } else {
//                $user_speedbar = $lang['title_albums'];
//                msg_box( $lang['no_notes'], 'info');

                return view('info.info', $params);
            }
        }
        return view('info.info', $params);
    }

    /**
     * Страница изминения порядка фотографий
     * @return int
     */
    public function edit_pos_photos(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            //include __DIR__.'/../lang/'.$checkLang.'/site.lng';
            $user_id = $user_info['user_id'];
            //$aid = intval($_GET['aid']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = (int)$path['3'];

            $check_album = $db->super_query("SELECT name FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");

            if($check_album){
                $sql_ = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$aid}' AND user_id = '{$user_id}' ORDER by `position` ASC", 1);

                //$params['title'] = $lang['editphotos'];
                $params['title'] = $lang['editphotos'].' | Sura';
                $user_speedbar = $lang['editphotos'];

//                $tpl->load_template('/albums/albums_top.tpl');
//                $tpl->set('{user-id}', $user_id);
//                $tpl->set('{aid}', $aid);
//                $tpl->set('{album-name}', stripslashes($check_album['name']));
//                $tpl->set('[editphotos]', '');
//                $tpl->set('[/editphotos]', '');
//                $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
//                $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
//                $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
//                $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
//                $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");

                $config = Settings::load();

                if($config['photos_drag'] == 'no')
                {
//                    $tpl->set_block("'\\[admin-drag\\](.*?)\\[/admin-drag\\]'si","");
                }
                else {
//                    $tpl->set('[admin-drag]', '');
//                    $tpl->set('[/admin-drag]', '');
                }

//                $tpl->compile('info');

                if($sql_){
                    //Добавляем ID для Drag-N-Drop jQuery
//                    $tpl->result['content'] .= '<div id="dragndrop"><ul>';
//                    $tpl->load_template('/albums/albums_editphotos.tpl');
                    foreach($sql_ as $row){
//                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$aid.'/c_'.$row['photo_name']);
//                        $tpl->set('{id}', $row['id']);
//                        $tpl->compile('content');
                    }
                    //Конец ID для Drag-N-Drop jQuery
//                    $tpl->result['content'] .= '</div></ul>';

                    return view('info.info', $params);
                } else{
//                    msg_box($lang['no_photos'], 'info_2');

                    return view('info.info', $params);
                }

            } else {
                //$params['title'] = $lang['hacking'];
                $params['title'] = $lang['hacking'].' | Sura';
                $user_speedbar = $lang['no_infooo'];
//                msg_box( $lang['hacking'], 'info_2');

                return view('info.info', $params);
            }
        }
        return view('info.info', $params);
    }

    /**
     * Просмотр альбома
     * @return string
     * @throws Exception
     */
    public function view(): int
    {
//        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        $params = array();

        Tools::NoAjaxRedirect();


        if($logged){
            //$act = $_GET['act'];

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = (int)$path['3'];

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $page = (int)$path['5'];
            //var_dump($aid);
            $mobile_speedbar = 'Просмотр альбома';

            $user_id = $user_info['user_id'];
            //            $aid = intval($_GET['aid']);



            if($page > 0) $page = (int)$page; else $page = 1;
            $gcount = 25;
            $limit_page = ($page-1) * $gcount;

            //Выводим данные о фотках
            $sql_photos = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$aid}' ORDER by `position` ASC LIMIT {$limit_page}, {$gcount}", 1);

            //Выводим данные о альбоме
            $row_album = $db->super_query("SELECT user_id, name, photo_num, privacy FROM `albums` WHERE aid = '{$aid}'");

            //ЧС
            $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($row_album['user_id']);
            if(!$CheckBlackList){
                $album_privacy = explode('|', $row_album['privacy']);
                if(!$row_album)
                    Hacking();

                //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                if($user_id != $row_album['user_id'])
                    $check_friend = (new \App\Libs\Friends)->CheckFriends($row_album['user_id']);

                //Приватность
                if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $row_album['user_id']){
                    //Выводим данные о владельце альбома(ов)
                    $row_owner = $db->super_query("SELECT user_name FROM `users` WHERE user_id = '{$row_album['user_id']}'");

//                    $tpl->load_template('albums/albums_top.tpl');
//                    $tpl->set('{user-id}', $row_album['user_id']);
//                    $tpl->set('{name}', Gramatic::gramatikName($row_owner['user_name']));
//                    $tpl->set('{aid}', $aid);
//                    $tpl->set('[view]', '');
//                    $tpl->set('[/view]', '');
//                    $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
//                    $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
//                    $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
//                    $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
//                    $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                    if($row_album['user_id'] == $user_id){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
//                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                    } else {
//                        $tpl->set('[not-owner]', '');
//                        $tpl->set('[/not-owner]', '');
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }
//                    $tpl->set('{album-name}', stripslashes($row_album['name']));
//                    $tpl->set('{all_p_num}', $row_album['photo_num']);
//                    $tpl->set('{aid}', $aid);
//                    $tpl->set('{count}', $limit_page);
//                    $tpl->compile('info');

                    //Мета теги и формирование спидбара
                    $titles = array('фотография', 'фотографии', 'фотографий');
                    $params['title'] = stripslashes($row_album['name']).' | '.$row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles).' | Sura';
                    $user_speedbar = '<span id="photo_num">'.$row_album['photo_num'].'</span> '.Gramatic::declOfNum($row_album['photo_num'], $titles);

                    if($sql_photos){
//                        $tpl->load_template('albums/album_photo.tpl');

                        $config = Settings::load();
                        foreach($sql_photos as $row){
//                            $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$row_album['user_id'].'/albums/'.$aid.'/c_'.$row['photo_name']);
//                            $tpl->set('{id}', $row['id']);
//                            $tpl->set('{all}', '');
//                            $tpl->set('{uid}', $row_album['user_id']);
//                            $tpl->set('{aid}', '_'.$aid);
//                            $tpl->set('{section}', '');
                            if($row_album['user_id'] == $user_id){
//                                $tpl->set('[owner]', '');
//                                $tpl->set('[/owner]', '');
//                                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                            } else {
//                                $tpl->set('[not-owner]', '');
//                                $tpl->set('[/not-owner]', '');
//                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                            }
//                            $tpl->compile('content');
                        }
                        $tpl = Tools::navigation($gcount, $row_album['photo_num'], $config['home_url'].'albums/view/'.$aid.'/page/', $tpl);
                    } else{

//                        msg_box( '<br /><br />В альбоме нет фотографий<br /><br /><br />', 'info_2');
                    }

                    //Проверяем на наличии файла с позициям фоток
//                    $check_pos = Cache::mozg_cache('user_'.$row_album['user_id'].'/position_photos_album_'.$aid);

                    //Если нету, то вызываем функцию генерации
//                    if(!$check_pos)
//                        GenerateAlbumPhotosPosition($row_album['user_id'], $aid);
                } else {
//                    $user_speedbar = $lang['error'];
//                    msg_box($lang['no_notes'], 'info');
                }

                return view('info.info', $params);
            } else {
//                $user_speedbar = $lang['title_albums'];
//                msg_box($lang['no_notes'], 'info');

                return view('info.info', $params);
            }
        }
        return view('info.info', $params);
    }

    /**
     * Страница с новыми фотографиями
     * @return int
     */
    public function new_photos(): int
    {
//        $tpl = Registry::get('tpl');
//        $db = $this->db();
//        $user_info = $this->user_info();
        $logged = $this->logged();
//        $lang = $this->get_langs();

        Tools::NoAjaxRedirect();

        $params = array();

        if($logged){
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     *  Просмотр всех альбомов юзера
     * @return string
     * @throws Exception
     */
    public function index(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $lang = $this->get_langs();
        $logged = $this->logged();

        $params = array();

        if($logged){
            $path = explode('/', $_SERVER['REQUEST_URI']);
            $uid = $user_id = (int)$path['2'];

            //Выводим данные о владельце альбома(ов)
            $row_owner = $db->super_query("SELECT user_search_pref, user_albums_num, user_new_mark_photos FROM `users` WHERE user_id = '{$uid}'");

            if($row_owner){
                //ЧС
                $CheckBlackList = (new \App\Libs\Friends)->CheckBlackList($uid);
                if(!$CheckBlackList){
                    $author_info = explode(' ', $row_owner['user_search_pref']);

                    $params['title'] = $lang['albums'].' '.Gramatic::gramatikName($author_info['0']).' '.Gramatic::gramatikName($author_info['1']).' | Sura';

                    //Выводи данные о альбоме
                    $sql_ = $db->super_query("SELECT aid, name, adate, photo_num, descr, comm_num, cover, ahash, privacy FROM `albums` WHERE user_id = '{$uid}' ORDER by `position` ASC", 1);

                    //Если есть альбомы то выводи их
                    if($sql_){
                        $m_cnt = $row_owner['user_albums_num'];

//                        $tpl->load_template('/albums/album.tpl');

                        //Добавляем ID для DragNDrop jQuery
//                        $tpl->result['content'] .= '<div id="dragndrop"><ul>';

                        //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                        if($user_info['user_id'] != $uid)
                            $check_friend = (new \App\Libs\Friends)->CheckFriends($uid);

                        foreach($sql_ as $key => $row){

                            //Приватность
                            $album_privacy = explode('|', $row['privacy']);
                            if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $uid){
                                if($user_info['user_id'] == $uid){
//                                    $tpl->set('[owner]', '');
//                                    $tpl->set('[/owner]', '');
                                    $sql_[$key]['owner'] = true;
//                                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                    $sql_[$key]['not_owner'] = false;
                                } else {
//                                    $tpl->set('[not-owner]', '');
//                                    $tpl->set('[/not-owner]', '');
                                    $sql_[$key]['not_owner'] = true;
//                                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                    $sql_[$key]['owner'] = false;
                                }

//                                $tpl->set('{name}', stripslashes($row['name']));
                                $sql_[$key]['name'] = stripslashes($row['name']);
                                if($row['descr'])
//                                    $tpl->set('{descr}', );
                                {
                                    $sql_[$key]['descr'] = '<div style="padding-top:4px;">' . stripslashes($row['descr']) . '</div>';
                                }
                                else
//                                    $tpl->set('{descr}', '');
                                {
                                    $sql_[$key]['descr'] = '';
                                }

                                $titles = array('фотография', 'фотографии', 'фотографий');
//                                $tpl->set('{photo-num}', );
                                $sql_[$key]['photo_num'] = $row['photo_num'].' '.Gramatic::declOfNum($row['photo_num'], $titles);

                                $titles = array('комментарий', 'комментария', 'комментариев');
//                                $tpl->set('{comm-num}', );
                                $sql_[$key]['comm_num'] = $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles);

                                $date = Date::megaDate(($row['adate']), 1, 1);
//                                $tpl->set('{date}', );
                                $sql_[$key]['date'] = $date;

                                $config = Settings::load();

                                if($row['cover'])
//                                    $tpl->set('{cover}', );
                                    $sql_[$key]['cover'] = $config['home_url'].'uploads/users/'.$uid.'/albums/'.$row['aid'].'/c_'.$row['cover'];
                                else
//                                    $tpl->set('{cover}', );
                                $sql_[$key]['cover'] = '/images/no_cover.png';

//                                    $tpl->set('{aid}', );
                                $sql_[$key]['aid'] = $row['aid'];
//                                    $tpl->set('{hash}', );
                                $sql_[$key]['hash'] = $row['ahash'];
//                                    $tpl->compile('content');
                            } else
                                $m_cnt--;
                        }

                        $params['albums'] = $sql_;

                        //Конец ID для DragNDrop jQuery
//                        $tpl->result['content'] .= '</div></ul>';

                        $row_owner['user_albums_num'] = $m_cnt;

                        if($row_owner['user_albums_num']){
//                            $titles = array('альбом', 'альбома', 'альбомов');
//                            if($user_info['user_id'] == $uid){
//                                $user_speedbar = 'У Вас <span id="albums_num">'.$row_owner['user_albums_num'].'</span> '.Gramatic::declOfNum($row_owner['user_albums_num'], $titles);
//                            } else {
//                                $user_speedbar = 'У '.Gramatic::gramatikName($author_info[0]).' '.$row_owner['user_albums_num'].' '.Gramatic::declOfNum($row_owner['user_albums_num'], $titles);
//                            }

//                            $tpl->load_template('/albums/albums_top.tpl');
//                            $tpl->set('{user-id}', );
                            $sql_[$key]['user_id'] = $uid;
//                                $tpl->set('{name}', );
                            $sql_[$key]['name'] = Gramatic::gramatikName($author_info['0']);
//                                $tpl->set('[all-albums]', '');
//                            $tpl->set('[/all-albums]', '');
                            $sql_[$key]['all_albums'] = true;
//                                $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
                            $sql_[$key]['view'] = false;
//                                $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                            $sql_[$key]['comments'] = false;
//                                $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
                            $sql_[$key]['editphotos'] = false;
//                                $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
                            $sql_[$key]['albums_comments'] = false;
//                                $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                            $sql_[$key]['all_photos'] = false;

                            //Показ скрытых тексто только для владельца страницы
                            if($user_info['user_id'] == $uid){
//                                $tpl->set('[owner]', '');
//                                $tpl->set('[/owner]', '');
                                $sql_[$key]['owner'] = true;
//                                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                                $sql_[$key]['not_owner'] = false;
                            } else {
//                                $tpl->set('[not-owner]', '');
//                                $tpl->set('[/not-owner]', '');
                                $sql_[$key]['not_owner'] = true;
//                                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                                $sql_[$key]['owner'] = false;
                            }

                            $config = Settings::load();

                            if($config['albums_drag'] == 'no')
//                                $tpl->set_block("'\\[admin-drag\\](.*?)\\[/admin-drag\\]'si","");
                            {
                                $sql_[$key]['albums_drag'] = false;
                            }
                            else {
//                                $tpl->set('[admin-drag]', '');
//                                $tpl->set('[/admin-drag]', '');
                                $sql_[$key]['albums_drag'] = true;
                            }

                            if($row_owner['user_new_mark_photos'] AND $user_info['user_id'] == $uid){
//                                $tpl->set('[new-photos]', '');
//                                $tpl->set('[/new-photos]', '')
                                $sql_[$key]['new_photos'] = true;
//                                $tpl->set('{num}', );
                                $sql_[$key]['num'] = $row_owner['user_new_mark_photos'];
                            } else
//                                $tpl->set_block("'\\[new-photos\\](.*?)\\[/new-photos\\]'si","");
                                $sql_[$key]['new_photos'] = false;

//                                    $tpl->compile('info');
                        } else{
//                            msg_box('', $lang['no_albums'], 'info_2');

                        }
                    } else {

                        $params['all_albums'] = true;
                        $params['user_id'] = $user_id;
    //                    $tpl->load_template('/albums/albums_info.tpl');
                        //Показ скрытых тексто только для владельца страницы
                        if($user_info['user_id'] == $uid){
                            $params['owner'] = true;
                            $params['not_owner'] = false;
                        } else {
                            $params['not_owner'] = true;
                            $params['owner'] = false;
                        }
                    }
                } else {
    //                msg_box('', $lang['no_notes'], 'info');
                }
            } else{
    //            Hacking();

            }

            return view('albums.albums', $params);
    //            }
    //            $tpl->clear();
    //            $db->free($sql_);
            }
        else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}
