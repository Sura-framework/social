<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Libs\Langs;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Gramatic;
use Sura\Libs\Status;
use Sura\Libs\Validation;

class AttachController extends Module{

    /**
     * Загрузка картинок при прикреплении файлов со стены,
     * заметок, или сообщений
     * @throws \JsonException
     * @throws \Exception
     */
    public function index(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();



        if($logged){
            $user_id = $user_info['user_id'];

            //Если нет папки альбома, то создаём её
            $upload_dir = __DIR__."/../../public/uploads/attach/{$user_id}/";
            if(!is_dir($upload_dir)){
                if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
//                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                }
//                @chmod($upload_dir, 0777);
            }

            //Разрешенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Получаем данные о фотографии
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = \Sura\Time\Date::time();
            $image_rename = substr(md5($server_time+random_int(1,100000)), 0, 20); // имя фотографии
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $image_name);
            $type = end($array); // формат файла

            //Проверяем если, формат верный то пропускаем
            if(in_array(strtolower($type), $allowed_files)){
                if($image_size < 5000000){
                    $res_type = strtolower('.'.$type);

                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                        $manager = new ImageManager(array('driver' => 'gd'));

                        //Создание оригинала
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.$image_rename.'.webp', 75);

                        //Создание маленькой копии
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(140, 100);
                        $image->save($upload_dir.'c_'.$image_rename.'.webp', 90);

                        unlink($upload_dir.$image_rename.$res_type);
                        $res_type = '.webp';

                        //Вставляем фотографию
                        $db->query("INSERT INTO `attach` SET photo = '{$image_rename}{$res_type}', ouser_id = '{$user_id}', add_date = '{$server_time}'");
//                        $ins_id = $db->insert_id();

                        $config = Settings::load();

                        $img_url = $config['home_url'].'uploads/attach/'.$user_id.'/c_'.$image_rename.$res_type;

                        //Результат для ответа
//                        $res = $image_rename.$res_type.'|||'.$img_url.'|||'.$user_id;
                        $res = array(
                            'img' => $image_rename.$res_type,
                            'url' => $img_url,
                            'user' => $user_id,
                        );
                        $status = Status::TTT;
                    } else{
                        $res = '';
                        $status = Status::TTT;
                    }
                } else{
                    $res = '';
                    $status = Status::TTT;
                }
            } else{
                $res = '';
                $status = Status::TTT;
            }
        }else{
            $res = '';
            $status = Status::BAD_LOGGED;
        }

        return _e_json(array(
            'res' => $res,
            'status' => $status,
        ) );
    }

    /**
     * Загрузка картинок при прикреплении файлов со стены,
     * заметок, или сообщений -> Сообщества
     *
     * @return int
     * @throws \JsonException
     */
    public function Attach_groups(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $public_id = (int)$request['public_id'];

            $rowPublic = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

            if(stripos($rowPublic['admin'], "u{$user_info['user_id']}|") !== false){
                //Если нет папки альбома, то создаём её
                $upload_dir = __DIR__."/../../public/uploads/groups/{$public_id}/photos/";

                //Разришенные форматы
                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

                //Получаем данные о фотографии
                $image_tmp = $_FILES['uploadfile']['tmp_name'];
                $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                $server_time = \Sura\Time\Date::time();
                $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
                $image_size = $_FILES['uploadfile']['size']; // размер файла
                $array = explode(".", $image_name);
                $type = end($array); // формат файла

                //Проверям если, формат верный то пропускаем
                if(in_array(strtolower($type), $allowed_files)){
                    if($image_size < 5000000){
                        $res_type = strtolower('.'.$type);

                        if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){

                            //Создание оригинала
                            $manager = new ImageManager(array('driver' => 'gd'));
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $image->save($upload_dir.$image_rename.'.webp', 85);

                            //Создание маленькой копии
                            $manager = new ImageManager(array('driver' => 'gd'));
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(140, 100);
                            $image->save($upload_dir.'c_'.$image_rename.'.webp', 90);

                            unlink($upload_dir.$image_rename.$res_type);
                            $res_type = '.webp';


                            //Вставляем фотографию
                            $db->query("INSERT INTO `attach` SET photo = '{$image_rename}{$res_type}', public_id = '{$public_id}', add_date = '{$server_time}', ouser_id = '{$user_info['user_id']}'");
                            $db->query("UPDATE `communities` SET photos_num = photos_num+1 WHERE id = '{$public_id}'");

                            //Результат для ответа
//                            echo $image_rename.$res_type;
                            $status = Status::TTT;
                            $img = $image_rename.$res_type;
                        } else{
                            $img = '';
                            $status = Status::TTT;
                        }
                    } else{
                        $img = '';
                        $status = Status::TTT;
                    }
                } else{
                    $img = '';
                    $status = Status::TTT;
                }
            }else{
                $img = '';
                $status = Status::TTT;
            }
        } else{
            $img = '';
            $status = Status::BAD_LOGGED;
        }

        return  _e_json(array(
            'img' => $img,
            'status' => $status,
        ) );
    }

    /**
     * Удаление комментария
     * @throws \JsonException
     */
    public function delcomm(): int
    {
//        $tpl = $params['tpl'];
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if ($logged) {
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];
            $purl = $db->safesql(Gramatic::totranslit($request['purl']));

            //Выводим данные о комментариии
            $row = $db->super_query("SELECT tb1.forphoto, auser_id, tb2.ouser_id FROM `attach_comm` tb1, `attach` tb2 WHERE tb1.id = '{$id}' AND tb1.forphoto = '{$purl}'");
            $tab_photos = false;

            //Если нет фотки в таблице PREFIX_attach то проверяем в таблице PREFIX_photos
            if(!$row){

                //Проверка в таблице PREFIX_photos
                $row_photos = $db->super_query("SELECT tb1.pid, owner_id, tb2.user_id FROM `photos_comments` tb1, `photos` tb2 WHERE tb1.id = '{$id}' AND tb1.photo_name = '{$purl}'");
                $tab_photos = true;

                $row['auser_id'] = $row_photos['owner_id'];
                $row['ouser_id'] = $row_photos['user_id'];
                $row['pid'] = $row_photos['pid'];

            }

            if($row['auser_id'] == $user_id OR $row['ouser_id'] == $user_id){

                //Если нет фотки в таблице PREFIX_attach то проверяем в таблице PREFIX_photos
                if($tab_photos){

                    $db->query("DELETE FROM `photos_comments` WHERE id = '{$id}'");
                    $db->query("UPDATE `photos` SET comm_num = comm_num-1 WHERE id = '{$row['pid']}'");

                    $row2 = $db->super_query("SELECT album_id FROM `photos` WHERE id = '{$row['pid']}'");

                    $db->query("UPDATE `albums` SET comm_num = comm_num-1 WHERE aid = '{$row2['album_id']}'");

                } else {

                    //Обновляем кол-во комментов
                    $db->query("UPDATE `attach` SET acomm_num = acomm_num-1 WHERE photo = '{$row['forphoto']}'");

                    //Удаляем комментарий
                    $db->query("DELETE FROM `attach_comm` WHERE forphoto = '{$row['forphoto']}' AND id = '{$id}'");

                }
                $status = Status::TTT;
            }else{
                $status = Status::TTT;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Добавления комментария
     * @return int
     * @throws \Exception
     */
    public function addcomm(): int
    {
//        $tpl = $params['tpl'];
//        include __DIR__ . '/../lang/' . $checkLang . '/site.lng';
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if ($logged) {
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $text = Validation::ajax_utf8(Validation::textFilter($request['text']));
            $purl = $db->safesql(Gramatic::totranslit($request['purl']));

            //Проверка на существования фотки в таблице PREFIX_attach
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `attach` WHERE photo = '{$purl}'");
            $tab_photos = false;

            //Если нет фотки в таблице PREFIX_attach то проверяем в таблице PREFIX_photos
            if(!$row['cnt']){

                $row = $db->super_query("SELECT album_id, user_id, photo_name, id FROM `photos` WHERE photo_name = '{$purl}'");
                $tab_photos = true;

                if($row['album_id'])
                    $row['cnt'] = 1;

            }

            //Если фотка есть
            if(isset($text) AND !empty($text) AND $row['cnt']){
                $server_time = \Sura\Time\Date::time();
                if($tab_photos){
//                    $server_time = Date::time();
                    $hash = md5($user_id.$server_time.$user_info['user_email'].random_int(0, 1000000000)).$text.$purl;

                    $db->query("INSERT INTO `photos_comments` (pid, user_id, text, date, hash, album_id, owner_id, photo_name) VALUES ('{$row['id']}', '{$user_id}', '{$text}', NOW(), '{$hash}', '{$row['album_id']}', '{$row['user_id']}', '{$row['photo_name']}')");
                    $id = $db->insert_id();

                    $db->query("UPDATE `photos` SET comm_num = comm_num+1 WHERE id = '{$row['id']}'");

                    $db->query("UPDATE `albums` SET comm_num = comm_num+1 WHERE aid = '{$row['album_id']}'");

                } else {
                    //Вставляем сам комментарий
                    $db->query("INSERT INTO `attach_comm` SET forphoto = '{$purl}', auser_id = '{$user_id}', text = '{$text}', adate = '{$server_time}'");
                    $id = $db->insert_id();

                    //Обновляем кол-во комментов
                    $db->query("UPDATE `attach` SET acomm_num = acomm_num+1 WHERE photo = '{$purl}'");

                }

                $tpl->load_template('attach/comment.tpl');
                $tpl->set('{id}', $id);
                $tpl->set('{uid}', $user_id);
                $tpl->set('{comment}', stripslashes($text));
                $tpl->set('{purl}', $purl);
                $tpl->set('{author}', $user_info['user_search_pref']);
                $tpl->set('{online}', $lang['online']);
                $tpl->set('{date}', Langs::lang_date('сегодня в H:i', $server_time));
                if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
                else $tpl->set('{ava}', '/images/no_ava_50.png');
                $tpl->set('[owner]', '');
                $tpl->set('[/owner]', '');
                $tpl->compile('content');


                return view('info.info', $params);
            }
        }
        return view('info.info', $params);
    }

    /**
     * Показ пред.комментариев
     * @return int
     */
    public function prevcomm(): int
    {
//        $tpl = $params['tpl'];
        include __DIR__ . '/../lang/' . $checkLang . '/site.lng';
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if ($logged) {
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $foSQLurl = $db->safesql(Gramatic::totranslit($request['purl']));

            //Выводим данные о владельце фото
            $row = $db->super_query("SELECT ouser_id, acomm_num FROM `attach` WHERE photo = '{$foSQLurl}'");
            $tab_photos = false;

            //Если нету то проверяем в таблице PREFIX_photos
            if(!$row){

                $row = $db->super_query("SELECT user_id, comm_num FROM `photos` WHERE photo_name = '{$foSQLurl}'");
                $row['acomm_num'] = $row['comm_num'];
                $row['ouser_id'] = $row['user_id'];
                $tab_photos = true;

            }

            $limit = 10;
            $first_id = (int)$request['first_id'];
            $page_post = (int)$request['page'];
            if($page_post <= 0) {
                $page_post = 1;
            }

            $start_limit = $row['acomm_num']-($page_post*$limit)-3;
            if($start_limit < 0) {
                $start_limit = 0;
            }

            if($tab_photos) {
                $sql_comm = $db->super_query("SELECT tb1.user_id, text, date, id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `photos_comments` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.photo_name = '{$foSQLurl}' AND id < '{$first_id}' ORDER by `date` ASC LIMIT {$start_limit}, {$limit}", 1);
            }

            else {
                $sql_comm = $db->super_query("SELECT tb1.auser_id, text, adate, id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `attach_comm` tb1, `users` tb2 WHERE tb1.auser_id = tb2.user_id AND tb1.forphoto = '{$foSQLurl}' AND id < '{$first_id}' ORDER by `adate` ASC LIMIT {$start_limit}, {$limit}", 1);
            }

            $tpl->load_template('attach/comment.tpl');

            foreach($sql_comm as $row_comm){

                if($tab_photos){

                    $row_comm['adate'] = strtotime($row_comm['date']);
                    $row_comm['auser_id'] = $row_comm['user_id'];

                }

                $tpl->set('{comment}', stripslashes($row_comm['text']));
                $tpl->set('{uid}', $row_comm['auser_id']);
                $tpl->set('{id}', $row_comm['id']);
                $tpl->set('{purl}', $foSQLurl);
                $tpl->set('{author}', $row_comm['user_search_pref']);

                if($row_comm['user_photo']) {
                    $tpl->set('{ava}', '/uploads/users/' . $row_comm['auser_id'] . '/50_' . $row_comm['user_photo']);
                }
                else {
                    $tpl->set('{ava}', '/images/no_ava_50.png');
                }

                $online = \App\Libs\Profile::Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                $tpl->set('{online}', $online);

                $date = \Sura\Time\Date::megaDate(strtotime($row_comm['adate']));
                $tpl->set('{date}', $date);

                if($row_comm['auser_id'] == $user_id OR $row['ouser_id'] == $user_id){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                } else
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                $tpl->compile('content');

            }
            return view('info.info', $params);

        }
        return view('info.info', $params);
    }

    /**
     * @return int
     */
    public function Attach_comm(): int
    {
//        $tpl = $params['tpl'];
        //$lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $photo_url = $request['photo'];
            $resIMGurl = explode('/', $photo_url);
            $foSQLurl = end($resIMGurl);
            $foSQLurl = $db->safesql(Gramatic::totranslit($foSQLurl));

            //Выводим данные о владельце фото
            $row = $db->super_query("SELECT tb1.ouser_id, acomm_num, add_date, tb2.user_search_pref, user_country_city_name FROM `attach` tb1, `users` tb2 WHERE tb1.ouser_id = tb2.user_id AND tb1.photo = '{$foSQLurl}'");
            $tab_photos = false;

            //Если нету то проверяем в таблице PREFIX_photos
            if(!$row){

                $row = $db->super_query("SELECT tb1.user_id, comm_num, date, tb2.user_search_pref, user_country_city_name FROM `photos` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.photo_name = '{$foSQLurl}'");
                $row['acomm_num'] = $row['comm_num'];
                $row['ouser_id'] = $row['user_id'];
                $row['add_date'] = strtotime($row['date']);
                $tab_photos = true;

            }

            if($row){

                //Выводим комментарии если они есть
                if($row['acomm_num']){

                    if($row['acomm_num'] > 7) {
                        $limit_comm = $row['acomm_num'] - 3;
                    }
                    else {
                        $limit_comm = 0;
                    }

                    if($tab_photos) {
                        $sql_comm = $db->super_query("SELECT tb1.user_id, text, date, id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `photos_comments` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.photo_name = '{$foSQLurl}' ORDER by `date` ASC LIMIT {$limit_comm}, {$row['acomm_num']}", 1);
                    }

                    else {
                        $sql_comm = $db->super_query("SELECT tb1.auser_id, text, adate, id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `attach_comm` tb1, `users` tb2 WHERE tb1.auser_id = tb2.user_id AND tb1.forphoto = '{$foSQLurl}' ORDER by `adate` ASC LIMIT {$limit_comm}, {$row['acomm_num']}", 1);
                    }

                    $tpl->load_template('attach/comment.tpl');

                    foreach($sql_comm as $row_comm){

                        if($tab_photos){

                            $row_comm['adate'] = strtotime($row_comm['date']);
                            $row_comm['auser_id'] = $row_comm['user_id'];

                        }

                        $tpl->set('{comment}', stripslashes($row_comm['text']));
                        $tpl->set('{uid}', $row_comm['auser_id']);
                        $tpl->set('{id}', $row_comm['id']);
                        $tpl->set('{purl}', $foSQLurl);
                        $tpl->set('{author}', $row_comm['user_search_pref']);

                        if($row_comm['user_photo']) {
                            $tpl->set('{ava}', '/uploads/users/' . $row_comm['auser_id'] . '/50_' . $row_comm['user_photo']);
                        }
                        else {
                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        }

                        $online = \App\Libs\Profile::Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                        $tpl->set('{online}', $online);

                        $date = \Sura\Time\Date::megaDate(strtotime($row_comm['adate']));
                        $tpl->set('{date}', $date);

                        if($row_comm['auser_id'] == $user_id OR $row['ouser_id'] == $user_id){
                            $tpl->set('[owner]', '');
                            $tpl->set('[/owner]', '');
                        } else {
                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si", "");
                        }

                        $tpl->compile('comments');
                    }

                }

                $tpl->load_template('attach/addcomm.tpl');

                //Кнопка показ пред сообщений
                if($row['acomm_num'] > 7){

                    $tpl->set('[comm]', '');
                    $tpl->set('[/comm]', '');

                } else {
                    $tpl->set_block("'\\[comm\\](.*?)\\[/comm\\]'si", "");
                }

                $tpl->set('{author}', $row['user_search_pref']);
                $tpl->set('{uid}', $row['ouser_id']);
                $tpl->set('{purl}', $foSQLurl);
                $tpl->set('{purl-js}', substr($foSQLurl, 0, 20));

                if($row['add_date']){
                    $date = \Sura\Time\Date::megaDate(strtotime($row['add_date']));
                    $tpl->set('{date}', $date);
                }else {
                    $tpl->set('{date}', '');
                }

                $author_info = explode('|', $row['user_country_city_name']);
                if($author_info[0]) $tpl->set('{author-info}', $author_info[0]);
                else $tpl->set('{author-info}', '');
                if($author_info[1]) $tpl->set('{author-info}', $author_info[0].', '.$author_info[1].'<br />');

                $tpl->set('{comments}', $tpl->result['comments']);
                $tpl->compile('content');


            }

            $tpl->clear();
            $db->free();
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }
}
