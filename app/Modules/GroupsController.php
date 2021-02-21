<?php

namespace App\Modules;

use App\Libs\Antispam;
use App\Libs\Wall;
use App\Models\Menu;
use Exception;
use Intervention\Image\ImageManager;
use RuntimeException;
use Sura\Time\Date;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class GroupsController extends Module
{

    /**
     * Отправка сообщества БД
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function send(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $title = Validation::ajax_utf8(Validation::textFilter($request['title'], false, true));

            Antispam::Check(6, $user_id);

            if(isset($request['title']) AND !empty($request['title'])){

                Antispam::LogInsert(6, $user_id);
                $db->query("INSERT INTO `communities` SET title = '{$title}', type = 1, traf = 1, ulist = '|{$user_id}|', date = NOW(), admin = 'u{$user_id}|', real_admin = '{$user_id}', comments = 1");
                $cid = $db->insert_id();
                $db->query("INSERT INTO `friends` SET friend_id = '{$cid}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
                $db->query("UPDATE `users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");

                if (!mkdir($concurrentDirectory = __DIR__ . '/../../public/uploads/groups/' . $cid . '/', 0777) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                chmod(__DIR__.'/../../public/uploads/groups/'.$cid.'/', 0777);

                if (!mkdir($concurrentDirectory = __DIR__ . '/../../public/uploads/groups/' . $cid . '/photos/', 0777) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                chmod(__DIR__.'/../../public/uploads/groups/'.$cid.'/photos/', 0777);

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");

                echo $cid;
                $status = Status::OK;
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
     *  Выход из сообщества
     *
     * @throws \Throwable
     */
    public function logout(): int
    {
        //FIXME rename function name
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $id = (int)$request['id'];
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
            if($check['cnt']){
                $db->query("DELETE FROM `friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
                $db->query("UPDATE `users` SET user_public_num = user_public_num-1 WHERE user_id = '{$user_id}'");
                $db->query("UPDATE `communities` SET traf = traf-1, ulist = REPLACE(ulist, '|{$user_id}|', '') WHERE id = '{$id}'");

                //Записываем в статистику "Вышедшие участники"
                $server_time = Date::time();
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats` WHERE gid = '{$id}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats_log` WHERE gid = '{$id}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '3'");

                if(!$check_user_stat['cnt']){
                    if($check_stat['cnt']){
                        $db->query("UPDATE `communities_stats` SET exit_users = exit_users + 1 WHERE gid = '{$id}' AND date = '{$stat_date}'");
                    } else {
                        $db->query("INSERT INTO `communities_stats` SET gid = '{$id}', date = '{$stat_date}', exit_users = '1', date_x = '{$stat_x_date}'");
                    }
                    $db->query("INSERT INTO `communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', act = '3', gid = '{$id}'");
                }
                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache = new \Sura\Cache\Cache($storage, 'public');
                $cache->remove("{$id}/profile_{$id}");

//                echo 'true';
                $status = Status::OK;
            }else{
                $user_id = $user_info['user_id'];
                $id = (int)$request['id'];
                $db->query("DELETE FROM `friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
//                echo 'false';
                $status = Status::NOT_FOUND;
            }
        }else{
//            echo 'err_logged';
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Страница загрузки главного фото сообщества
     *
     * @param $params
     * @return int
     */
    public function load_photo_page($params): int
    {
        $lang = $this->get_langs();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['communities'].' | Sura';
            $params['id'] = $_POST['id'];
            return view('news.load_photo', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Загрузка и изминение главного фото сообщества
     *
     * @throws Exception
     * @throws \Throwable
     */
    public function loadphoto(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $id = (int)$request['id'];

            //Проверка на то, что фото обновляет адмиH
            $row = $db->super_query("SELECT admin, photo, del, ban FROM `communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND $row['del'] == 0 AND $row['ban'] == 0){

                //Разришенные форматы
                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

                //Получаем данные о фотографии
                $image_tmp = $_FILES['uploadfile']['tmp_name'];
                $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                $server_time = Date::time();
                $image_rename = substr(md5($server_time+random_int(1,100000)), 0, 20); // имя фотографии
                $image_size = $_FILES['uploadfile']['size']; // размер файла
                $array = explode(".", $image_name);
                $type = end($array); // формат файла

                //Проверям если, формат верный то пропускаем
                if(in_array(strtolower($type), $allowed_files)){
                    if($image_size < 5000000){
                        $res_type = strtolower('.'.$type);

                        $upload_dir = __DIR__."/../../public/uploads/groups/{$id}/";

                        if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                            $manager = new ImageManager(array('driver' => 'gd'));
                            //Создание оригинала
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(200, null, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $image->save($upload_dir.$image_rename.'.webp', 85);

                            //Создание уменьшеной копии 50х50
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(50, 50);
                            $image->save($upload_dir.'50_'.$image_rename.'.webp', 85);

                            //Создание уменьшеной копии 100х100
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(100, 100);
                            $image->save($upload_dir.'100_'.$image_rename.'.webp', 90);

                            unlink($upload_dir.$image_rename.$res_type);
                            $res_type = '.webp';

                            if($row['photo']){
                                unlink($upload_dir.$row['photo']);
                                unlink($upload_dir.'50_'.$row['photo']);
                                unlink($upload_dir.'100_'.$row['photo']);
                            }

                            //Вставляем фотографию
                            $db->query("UPDATE `communities` SET photo = '{$image_rename}{$res_type}' WHERE id = '{$id}'");

                            //Результат для ответа
//                            echo $image_rename.$res_type;

                            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                            $cache = new \Sura\Cache\Cache($storage, 'wall');
                            $cache->remove("group{$id}");

                            $status = Status::OK;
                            $err =  'yes';
                        } else {
//                            echo 'big_size';
                            $status = Status::BAD_MOVE;
                            $err =  'hacking';
                        }
                    } else {
//                        echo 'big_size';
                        $status = Status::BIG_SIZE;
                        $err =  'hacking';
                    }
                } else {
//                    echo 'bad_format';
                    $status = Status::BAD_FORMAT;
                    $err =  'hacking';
                }
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;//BAD_LOGGED
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Удаление фото сообщества
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function delphoto(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];

            //Проверка на то, что фото удалет админ
            $row = $db->super_query("SELECT photo, admin FROM `communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false){
                $upload_dir = __DIR__."/../../public/uploads/groups/{$id}/";
                unlink($upload_dir.$row['photo']);
                unlink($upload_dir.'50_'.$row['photo']);
                unlink($upload_dir.'100_'.$row['photo']);
                $db->query("UPDATE `communities` SET photo = '' WHERE id = '{$id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'wall');
                $cache->remove("group{$id}");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Вступление в сообщество
     *
     * @throws Exception|\Throwable
     */
    public function login(): int
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            if (isset($request['id']))
                $id = (int)$request['id'];
            else{
                throw new Exception('item not found');
            }
            //Проверка на существования юзера в сообществе
            $row = $db->super_query("SELECT ulist, del, ban FROM `communities` WHERE id = '{$id}'");

            if(stripos($row['ulist'], "|{$user_id}|") === false AND $row['del'] == 0 AND $row['ban'] == 0){

                $ulist = $row['ulist']."|{$user_id}|";

                //Обновляем кол-во людей в сообществе
                $db->query("UPDATE `communities` SET traf = traf+1, ulist = '{$ulist}' WHERE id = '{$id}'");

                //Подписываемся
                $db->query("INSERT INTO `friends` SET friend_id = '{$id}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");

                //Записываем в статистику "Новые участники"
                $server_time = Date::time();
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats` WHERE gid = '{$id}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_stats_log` WHERE gid = '{$id}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '2'");

                if(!$check_user_stat['cnt']){
                    if($check_stat['cnt']){
                        $db->query("UPDATE `communities_stats` SET new_users = new_users + 1 WHERE gid = '{$id}' AND date = '{$stat_date}'");
                    } else {
                        $db->query("INSERT INTO `communities_stats` SET gid = '{$id}', date = '{$stat_date}', new_users = '1', date_x = '{$stat_x_date}'");
                    }
                    $db->query("INSERT INTO `communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', act = '2', gid = '{$id}'");
                }

                //Проверка на приглашению юзеру
                $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

                //Если есть приглашение, то удаляем его
                if($check['cnt']){
                    $db->query("DELETE FROM `communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");
                    $appSQLDel = ", invties_pub_num = invties_pub_num - 1";
                }else{
                    $appSQLDel = '';
                }

                //Обновляем кол-во сообществ у юзера
                $db->query("UPDATE `users` SET user_public_num = user_public_num + 1 {$appSQLDel} WHERE user_id = '{$user_id}'");

                //Чистим кеш
                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache = new \Sura\Cache\Cache($storage, 'public');
                $cache->remove("{$id}/profile_{$id}");

                echo 'true';
                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Страница добавления контактов
     *
     * @return int
     */
    public function addfeedback_pg(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $logged = Registry::get('logged');
        $request = (Request::getRequest()->getGlobal());
        if($logged){
            $params['title'] = $lang['communities'].' | Sura';

//            $tpl->load_template('groups/addfeedback_pg.tpl');
            $params['id'] = $request['id'];
            return view('groups.all_feedback', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Добавления контакт в БД
     *
     * @return int
     * @throws \JsonException
     */
    public function addfeedback_db(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) {
//                $page = (int)$request['page'];
//            } else {
//                $page = 1;
//            }
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $upage = (int)$request['upage'];
            $office = Validation::ajax_utf8(Validation::textFilter($request['office'], false, true));
            $phone = Validation::ajax_utf8(Validation::textFilter($request['phone'], false, true));
            $email = Validation::ajax_utf8(Validation::textFilter($request['email'], false, true));

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$id}'");

            //Проверяем что такой юзер есть на сайте
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$upage}'");

            //Проверяем на то что юзера нет в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

            if($row['cnt'] AND stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND !$checkSec['cnt']){
                $db->query("UPDATE `communities` SET feedback = feedback+1 WHERE id = '{$id}'");
                $server_time = Date::time();
                $db->query("INSERT INTO `communities_feedback` SET cid = '{$id}', fuser_id = '{$upage}', office = '{$office}', fphone = '{$phone}', femail = '{$email}', fdate = '{$server_time}'");

                $status = Status::OK;
                $err =  'yes';
            } else {
//                echo 1;
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Удаление контакта из БД
     *
     * @return int
     * @throws \JsonException
     */
    public function delfeedback(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) {
//                $page = (int)$request['page'];
//            } else {
//                $page = 1;
//            }
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $uid = (int)$request['uid'];

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$id}'");

            //Проверяем на то что юзера есть в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
                $db->query("UPDATE `communities` SET feedback = feedback-1 WHERE id = '{$id}'");
                $db->query("DELETE FROM `communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Выводим фотографию юзера при указании ИД страницы
     *
     * @throws \JsonException
     */
    public function checkFeedUser(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
//            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $row = $db->super_query("SELECT user_photo, user_search_pref FROM `users` WHERE user_id = '{$id}'");
            if($row) {
                echo $row['user_search_pref'] . "|" . $row['user_photo'];

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Сохранение отредактированых данных контакт в БД
     *
     * @throws \Throwable
     */
    public function editfeeddave(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

//        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) {
//                $page = (int)$request['page'];
//            } else {
//                $page = 1;
//            }
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $upage = (int)$request['uid'];
            $office = Validation::ajax_utf8(Validation::textFilter($request['office'], false, true));
            $phone = Validation::ajax_utf8(Validation::textFilter($request['phone'], false, true));
            $email = Validation::ajax_utf8(Validation::textFilter($request['email'], false, true));

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$id}'");

            //Проверяем на то что юзера есть в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
                $db->query("UPDATE `communities_feedback` SET office = '{$office}', fphone = '{$phone}', femail = '{$email}' WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'wall');
                $cache->remove("group{$id}");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Все контакты (БОКС)
     *
     * @return int
     */
    public function allfeedbacklist(): int
    {
        $params = array();

//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

//        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];

            //Выводим ИД админа
            $owner = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$id}'");

            $sql_ = $db->super_query("SELECT tb1.fuser_id, office, fphone, femail, tb2.user_search_pref, user_photo FROM `communities_feedback` tb1, `users` tb2 WHERE tb1.cid = '{$id}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate`", true);
//            $tpl->load_template('groups/allfeedbacklist.tpl');
            if($sql_){
                foreach($sql_ as $key => $row){
//                    $tpl->set('{id}', $id);
                    $sql_[$key]['id'] = $id;
//                    $tpl->set('{name}', $row['user_search_pref']);
                    $sql_[$key]['name'] = $row['user_search_pref'];
//                    $tpl->set('{office}', );
                    $sql_[$key]['office'] = stripslashes($row['office']);
//                    $tpl->set('{phone}', );
                    $sql_[$key]['phone'] = stripslashes($row['fphone']);
//                    $tpl->set('{user-id}', );
                    $sql_[$key]['user_id'] = $row['fuser_id'];
                    if($row['fphone'] AND $row['femail']) {
//                        $tpl->set('{email}', );
                        $sql_[$key]['email'] = ', '.stripslashes($row['femail']);
                    }
                    else{
//                        $tpl->set('{email}', );
                        $sql_[$key]['email'] = stripslashes($row['femail']);
                    }
                    if($row['user_photo']) {
//                        $tpl->set('{ava}', );
                        $sql_[$key]['ava'] = '/uploads/users/'.$row['fuser_id'].'/50_'.$row['user_photo'];
                    }
                    else {
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
//                        $tpl->set('{ava}', );
                    }
                    if(stripos($owner['admin'], "u{$user_id}|") !== false){
                        $sql_[$key]['admin'] = true;
//                        $tpl->set('[admin]', '');
//                        $tpl->set('[/admin]', '');
                    } else
                    {
                        $sql_[$key]['admin'] = false;
//                        $tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
                    }
//                    $tpl->compile('content');
                    $params['sql_'] = $sql_;
                }
//                Tools::AjaxTpl();
            } else
                echo '<div class="text-center" style="padding-top:10px;color:#777;font-size:13px;">Список контактов пуст.</div>';

            if(stripos($owner['admin'], "u{$user_id}|") !== false)
                echo "<style>#box_bottom_left_text{padding-top:6px;float:left}</style><script>$('#box_bottom_left_text').html('<a href=\"/\" onClick=\"groups.addcontact({$id}); return false\">Добавить контакт</a>');</script>";
            return view('groups.all_feedback_box', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Сохранение отредактированных данных группы
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function saveinfo(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $comments = (int)$request['comments'];
            $discussion = (int)$request['discussion'];
            $title = Validation::ajax_utf8(Validation::textFilter($request['title'], false, true));
            $adres_page = Validation::ajax_utf8(strtolower(Validation::textFilter($request['adres_page'], false, true)));
            $descr = Validation::ajax_utf8(Validation::textFilter($request['descr'], 5000));

            $request['web'] = str_replace(array('"', "'"), '', $request['web']);
            $web = Validation::ajax_utf8(Validation::textFilter($request['web'], false, true));

            if(!preg_match("/^[a-zA-Z0-9_-]+$/", $adres_page)) $adress_ok = false;
            else $adress_ok = true;

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `communities` WHERE id = '".$id."'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND isset($title) AND !empty($title) AND $adress_ok){
                if(preg_match('/public[0-9]/i', $adres_page))
                    $adres_page = '';

                $adres_page = preg_replace('/\b(u([0-9]+)|friends|editmypage|albums|photo([0-9]+)_([0-9]+)|photo([0-9]+)_([0-9]+)_([0-9]+)|fave|notes|videos|video([0-9]+)_([0-9]+)|news|messages|wall([0-9]+)|settings|support|restore|blog|balance|nonsense|reg([0-9]+)|gifts([0-9]+)|groups|wallgroups([0-9]+)_([0-9]+)|audio|audio([0-9]+)|docs|apps|app([0-9]+)|public|forum([0-9]+)|public([0-9]+))\b/i', '', $adres_page);

                //Проверка на то, что адрес страницы свободен
                if($adres_page)
                    $checkAdres = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities` WHERE adres = '".$adres_page."' AND id != '".$id."'");

                if(!$checkAdres['cnt'] OR $adres_page == ''){
                    $db->query("UPDATE `communities` SET title = '".$title."', descr = '".$descr."', comments = '".$comments."', discussion = '{$discussion}', adres = '".$adres_page."', web = '{$web}' WHERE id = '".$id."'");
                    if(!$adres_page)
                        echo 'no_new';
                } else
                    echo 'err_adres';

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'wall');
                $cache->remove("group{$id}");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Выводим информацию о пользователе которого
     * будем делать админом
     *
     * @throws \JsonException
     */
    public function new_admin(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $new_admin_id = (int)$request['new_admin_id'];
            if ($user_id != $new_admin_id){
                $row = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref, user_sex FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$new_admin_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2");
                if($row){
//                $config = Settings::load();

                    if($row['user_photo']) $ava = "/uploads/users/{$new_admin_id}/100_{$row['user_photo']}";
                    else $ava = "/images/100_no_ava.png";
                    if($row['user_sex'] == 1) $gram = 'был';
                    else $gram = 'была';
                    echo "<div style=\"padding:15px\"><img src=\"{$ava}\" style=\"margin-right:10px\" id=\"adm_ava\" />Вы хотите чтоб <b id=\"adm_name\">{$row['user_search_pref']}</b> {$gram} одним из руководителей страницы?</div>";
                    //FIXME
                    $status = Status::OK;
                } else
                {
                    echo "<div style=\"padding:15px\"><div class=\"err_red\">Пользователь с таким адресом страницы не подписан на эту страницу.</div></div><script>$('#box_but').hide()</script>";
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
     * Запись нового админа в БД
     *
     * @return int
     * @throws \JsonException
     */
    public function send_new_admin(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0)
//                $page = (int)$request['page'];
//            else
//                $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $new_admin_id = (int)$request['new_admin_id'];
            $row = $db->super_query("SELECT admin, ulist FROM `communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$new_admin_id}|") === false AND stripos($row['ulist'], "|{$user_id}|") !== false){
                $admin = $row['admin']."u{$new_admin_id}|";
                $db->query("UPDATE `communities` SET admin = '{$admin}' WHERE id = '{$id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Удаление админа из БД
     * @return int
     * @throws \JsonException
     */
    public function deladmin(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $uid = (int)$request['uid'];
            $row = $db->super_query("SELECT admin, ulist, real_admin FROM `communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$uid}|") !== false AND $uid != $row['real_admin']){
                $admin = str_replace("u{$uid}|", '', $row['admin']);
                $db->query("UPDATE `communities` SET admin = '{$admin}' WHERE id = '{$id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Добавление записи на стену
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function wall_send(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

            //Если страница вывзана через "к предыдущим записям"
            $limit_select = 10;
            if($request['page_cnt'] > 0) {
                $page_cnt = (int)$request['page_cnt'] * $limit_select;
            }
            else {
                $page_cnt = 0;
            }

            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $id = (int)$request['id'];
            $wall_text = Validation::ajax_utf8(Validation::textFilter($request['wall_text']));
            $attach_files = Validation::ajax_utf8(Validation::textFilter($request['attach_files'], false, true));

            //Проверка на админа
            $row = $db->super_query("SELECT admin, del, ban FROM `communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") == false) {
                //BAD_RIGHTS
                return _e('err');
            }

            if(isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files) AND $row['del'] == 0 AND $row['ban'] == 0){

                //Оприделение изображения к ссылке
                if(stripos($attach_files, 'link|') !== false){
                    $attach_arr = explode('||', $attach_files);
                    $cnt_attach_link = 1;
                    foreach($attach_arr as $attach_file){
                        $attach_type = explode('|', $attach_file);
                        if($attach_type[0] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
//                            $domain_url_name = explode('/', $attach_type[1]);
                            //$rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);
                            $rImgUrl = $attach_type[4];
                            $rImgUrl = str_replace("\\", "/", $rImgUrl);
                            $img_name_arr = explode(".", $rImgUrl);
                            $img_format = Gramatic::totranslit(end($img_name_arr));
                            $server_time = Date::time();
                            $image_rename = substr(md5($server_time.md5($rImgUrl)), 0, 15);
                            $res_type = '.'.$img_format;
                            //Разришенные форматы
                            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

                            //Загружаем картинку на сайт
                            if(in_array(strtolower($img_format), $allowed_files) AND preg_match("/https:\/\/(.*?)(.jpg|.png|.gif|.jpeg|.jpe)/i", $rImgUrl)){

                                //Директория загрузки фото
                                $upload_dir = __DIR__.'/../../public/uploads/attach/'.$user_id.'/';

                                //Если нет папки юзера, то создаём её
                                if(!is_dir($upload_dir)){
                                    if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                                        throw new RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                                    }
                                    chmod($upload_dir, 0777);
                                }

                                if(copy($rImgUrl, $upload_dir.'/'.$image_rename.$res_type)){
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
                $vote_title = Validation::ajax_utf8(Validation::textFilter($request['vote_title'], false, true));
                $vote_answer_1 = Validation::ajax_utf8(Validation::textFilter($request['vote_answer_1'], false, true));

                $ansers_list = array();

                if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

                    for($vote_i = 1; $vote_i <= 10; $vote_i++){

                        $vote_answer = Validation::ajax_utf8(Validation::textFilter($request['vote_answer_'.$vote_i], false, true));
                        $vote_answer = str_replace('|', '&#124;', $vote_answer);

                        if($vote_answer) {
                            $ansers_list[] = $vote_answer;
                        }

                    }

                    $sql_answers_list = implode('|', $ansers_list);

                    //Вставляем голосование в БД
                    $db->query("INSERT INTO `votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");

                    $attach_files .= "vote|{$db->insert_id()}||";

                }

                //Вставляем саму запись в БД
                $server_time = Date::time();
                $db->query("INSERT INTO `communities_wall` SET public_id = '{$id}', text = '{$wall_text}', attach = '{$attach_files}', add_date = '{$server_time}'");
                $dbid = $db->insert_id();
                $db->query("UPDATE `communities` SET rec_num = rec_num+1 WHERE id = '{$id}'");

                //Вставляем в ленту новотсей
                $db->query("INSERT INTO `news` SET ac_user_id = '{$id}', action_type = 11, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'public');
                $cache->remove("{$id}/profile_{$id}");

                $query = $db->super_query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, fixed, tb2.title, photo, comments, adres FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT {$page_cnt}, {$limit_select}", true);

                $params['wall_records'] = Wall::build($query);

                return _e('true');
            }
            else{
                //NOT_DATA
                return _e('err');
            }
        }else{
            $status = Status::BAD_LOGGED;//BAD_LOGGED
            $err =  'hacking';
//            return _e('err');
        }


        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Добавление комментария к записи
     *
     * @throws \Throwable
     * @throws \Throwable
     */
    public function wall_send_comm(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();

            Antispam::Check(5, $user_id);

            $rec_id = (int)$request['rec_id'];
            $public_id = (int)$request['public_id'];
            $wall_text = Validation::ajax_utf8(Validation::textFilter($request['wall_text']));
            $answer_comm_id = (int)$request['answer_comm_id'];

            //Проверка на админа и проверяем включены ли комменты
            $row = $db->super_query("SELECT tb1.fasts_num, public_id, tb2.admin, comments FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

            if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text)){

                Antispam::LogInsert(5, $user_id);

                //Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
                if($answer_comm_id){

                    //Выводим ид владельца комменатрия
                    $row_owner2 = $db->super_query("SELECT public_id, text FROM `communities_wall` WHERE id = '{$answer_comm_id}' AND fast_comm_id != '0'");

                    //Проверка на то, что юзер не отвечает сам себе
                    if($user_id != $row_owner2['public_id'] AND $row_owner2){

                        $answer_text = $db->safesql($row_owner2['text']);

                        $check2 = $db->super_query("SELECT user_last_visit, user_name FROM `users` WHERE user_id = '{$row_owner2['public_id']}'");

                        $wall_text = str_replace($check2['user_name'], "<a href=\"/u{$row_owner2['public_id']}\" onClick=\"Page.Go(this.href); return false\" class=\"newcolor000\">{$check2['user_name']}</a>", $wall_text);

                        //Вставляем в ленту новостей
                        $server_time = Date::time();
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$answer_comm_id}', for_user_id = '{$row_owner2['public_id']}', action_time = '{$server_time}', answer_text = '{$answer_text}', link = '/wallgroups{$row['public_id']}_{$rec_id}'");

                        //Вставляем событие в моментальные оповещания
                        $update_time = $server_time - 70;

                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'public');
                        if($check2['user_last_visit'] >= $update_time){

                            $db->query("INSERT INTO `updates` SET for_user_id = '{$row_owner2['public_id']}', from_user_id = '{$user_id}', type = '5', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/news/notifications'");

                            $cache->save("{$row_owner2['public_id']}/updates", 1);
                            //ИНАЧЕ Добавляем +1 юзеру для оповещания
                        } else {
                            $value = $cache->load("{$row_owner2['public_id']}/new_news");
                            $cache->save("{$row_owner2['public_id']}/new_news", $value+1);
                        }
                    }
                }

                //Вставляем саму запись в БД
                $server_time = Date::time();
                $db->query("INSERT INTO `communities_wall` SET public_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', fast_comm_id = '{$rec_id}'");
                $db->query("UPDATE `communities_wall` SET fasts_num = fasts_num+1 WHERE id = '{$rec_id}'");

                $row['fasts_num'] = $row['fasts_num']+1;

                if($row['fasts_num'] > 3)
                    $comments_limit = $row['fasts_num']-3;
                else
                    $comments_limit = 0;

                $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `communities_wall` tb1, `users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date`  LIMIT {$comments_limit}, 3", true);

                //Загружаем кнопку "Показать N запсии"
//                $tpl->load_template('groups/record.tpl');

                $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
//                $tpl->set('{gram-record-all-comm}', );
                $params['gram_record_all_comm'] = Gramatic::declOfNum(($row['fasts_num']-3), $titles1).' '.($row['fasts_num']-3).' '.Gramatic::declOfNum(($row['fasts_num']-3), $titles2);
                if($row['fasts_num'] < 4)
                {
//                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                    $params['all_comm'] = false;
                }
                else {
//                    $tpl->set('{rec-id}', $rec_id);
                    $params['rec_id'] = $rec_id;
//                    $tpl->set('[all-comm]', '');
                    $params['all_comm'] = true;
//                    $tpl->set('[/all-comm]', '');
                }
//                $tpl->set('{public-id}', $public_id);
                $params['public_id'] = $public_id;
//                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                $params['record'] = false;
//                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                $params['comment_form'] = false;
//                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $params['comment'] = false;
//                $tpl->compile('content');
//
//                $tpl->load_template('groups/record.tpl');
                //Сообственно выводим комменты
                $config = Settings::load();
                foreach($sql_comments as $key => $row_comments){
//                    $tpl->set('{public-id}', $public_id);
                    $sql_comments[$key]['public_id'] = $public_id;
//                    $tpl->set('{name}', $row_comments['user_search_pref']);
                    $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                    if($row_comments['user_photo'])
                    {
//                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                        $sql_comments[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                    }
                    else
                    {
//                        $tpl->set('{ava}', '/images/no_ava_50.png');
                        $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                    }
//                    $tpl->set('{comm-id}', $row_comments['id']);
                    $sql_comments[$key]['comm_id'] = $row_comments['id'];
//                    $tpl->set('{user-id}', );
                    $sql_comments[$key]['user_id'] = $row_comments['public_id'];
//                    $tpl->set('{rec-id}', $rec_id);
                    $sql_comments[$key]['rec_id'] = $rec_id;

                    $expBR2 = explode('<br />', $row_comments['text']);
                    $textLength2 = count($expBR2);
                    $strTXT2 = strlen($row_comments['text']);
                    if($textLength2 > 6 OR $strTXT2 > 470)
                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                    //Обрабатываем ссылки
                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                    $tpl->set('{text}', );
                    $sql_comments[$key]['text'] = stripslashes($row_comments['text']);
                    $date = \Sura\Time\Date::megaDate($row['add_date']);
//                    $tpl->set('{date}', $date);
                    $sql_comments[$key]['date'] = $date;
                    if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
                        $sql_comments[$key]['owner'] = true;
                    } else
                    {
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                        $sql_comments[$key]['owner'] = false;
                    }

                    if($user_id == $row_comments['public_id'])

                    {
//                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                        $sql_comments[$key]['not_owner'] = false;
                    }

                    else {

//                        $tpl->set('[not-owner]', '');
//                        $tpl->set('[/not-owner]', '');
                        $sql_comments[$key]['not_owner'] = true;
                    }

//                    $tpl->set('[comment]', '');
//                    $tpl->set('[/comment]', '');
                    $sql_comments[$key]['comment'] = true;
//                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                    $sql_comments[$key]['record'] = false;
//                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                    $sql_comments[$key]['comment-form'] = false;
//                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                    $sql_comments[$key]['all-comm'] = false;
//                    $tpl->compile('content');
                }

                //Загружаем форму ответа
//                $tpl->load_template('groups/record.tpl');
//                $tpl->set('{rec-id}', $rec_id);
                $params['rec_id'] = $rec_id;
//                $tpl->set('{user-id}', $public_id);
                $params['user_id'] = $public_id;
//                $tpl->set('[comment-form]', '');
                $params['comment_form'] = true;
//                $tpl->set('[/comment-form]', '');
//                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                $params['record'] = false;
//                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $params['comment'] = false;
//                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                $params['all-comm'] = false;

                $status = Status::OK;
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
     * Удаление записи
     *
     * @return int
     * @throws \JsonException
     */
    public function wall_del(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $rec_id = (int)$request['rec_id'];
            $public_id = (int)$request['public_id'];

            //Проверка на админа и проверяем включены ли комменты
            if($public_id){
                $row = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");
                $row_rec = $db->super_query("SELECT fast_comm_id, public_id, add_date FROM `communities_wall` WHERE id = '{$rec_id}'");
            } else
            {
                $row = $db->super_query("SELECT tb1.public_id, attach, fast_comm_id, tb2.admin FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");
            }

            if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_rec['public_id']){
                if($public_id){

                    $db->query("UPDATE `communities_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row_rec['fast_comm_id']}'");
                    $db->query("DELETE FROM `news` WHERE ac_user_id = '{$row_rec['public_id']}' AND action_type = '6' AND action_time = '{$row_rec['add_date']}'");

                    $db->query("DELETE FROM `communities_wall` WHERE id = '{$rec_id}'");

                } else if($row['fast_comm_id'] == 0){

                    $db->query("DELETE FROM `communities_wall` WHERE fast_comm_id = '{$rec_id}'");
                    $db->query("DELETE FROM `news` WHERE obj_id = '{$rec_id}' AND action_type = '11'");
                    $db->query("UPDATE `communities` SET rec_num = rec_num-1 WHERE id = '{$row['public_id']}'");

                    //Удаляем фотку из прикрипленой ссылке, если она есть
                    if(stripos($row['attach'], 'link|') !== false){
                        $attach_arr = explode('link|', $row['attach']);
                        $attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
                        $attach_arr3 = explode('||', $attach_arr2[1]);
                        if($attach_arr3[0])
                            @unlink(__DIR__.'/../../uploads/attach/'.$user_id.'/'.$attach_arr3[0]);
                    }

                    $db->query("DELETE FROM `communities_wall` WHERE id = '{$rec_id}'");
                }

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Показ всех комментариев к записи
     *
     */
    public function all_comm(): void
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $rec_id = (int)$request['rec_id'];
            $public_id = (int)$request['public_id'];

            //Проверка на админа и проверяем включены ли комменты
            $row = $db->super_query("SELECT tb2.admin, comments FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

            if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false){
                $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `communities_wall` tb1, `users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC", 1);
//                $tpl->load_template('groups/record.tpl');
                //Сообственно выводим комменты
                $config = Settings::load();

                foreach($sql_comments as $key => $row_comments){
//                    $tpl->set('{public-id}', );
                    $sql_comments[$key]['public_id'] = $public_id;
//                    $tpl->set('{name}', );
                    $sql_comments[$key]['name'] = $row_comments['user_search_pref'];
                    if($row_comments['user_photo'])
                    {
//                        $tpl->set('{ava}', );
                        $sql_comments[$key]['ava'] = $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo'];
                    }
                    else
                    {
//                        $tpl->set('{ava}', );
                        $sql_comments[$key]['ava'] = '/images/no_ava_50.png';
                    }

//                    $tpl->set('{rec-id}', );
                    $sql_comments[$key]['rec_id'] = $rec_id;
//                    $tpl->set('{comm-id}', );
                    $sql_comments[$key]['comm_id'] = $row_comments['id'];
//                    $tpl->set('{user-id}', );
                    $sql_comments[$key]['user_id'] = $row_comments['public_id'];

                    $expBR2 = explode('<br />', $row_comments['text']);
                    $textLength2 = count($expBR2);
                    $strTXT2 = strlen($row_comments['text']);
                    if($textLength2 > 6 OR $strTXT2 > 470)
                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                    //Обрабатываем ссылки
                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

//                    $tpl->set('{text}', );
                    $sql_comments[$key]['text'] = stripslashes($row_comments['text']);
                    $date = \Sura\Time\Date::megaDate(strtotime($row_comments['add_date']));
//                    $tpl->set('{date}', );
                    $sql_comments[$key]['date'] = $date;
                    if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
//                        $tpl->set('[owner]', '');
//                        $tpl->set('[/owner]', '');
                        $sql_comments[$key]['owner'] = true;
                    } else
                    {
//                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                        $sql_comments[$key]['owner'] = false;
                    }

                    if($user_id == $row_comments['public_id'])

                    {
//                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                        $sql_comments[$key]['not_owner'] = false;
                    }

                    else {

//                        $tpl->set('[not-owner]', '');
//                        $tpl->set('[/not-owner]', '');
                        $sql_comments[$key]['not_owner'] = true;
                    }

//                    $tpl->set('[comment]', '');
//                    $tpl->set('[/comment]', '');
                    $sql_comments[$key]['comment'] = true;
//                    $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                    $sql_comments[$key]['record'] = false;
//                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                    $sql_comments[$key]['comment_form'] = false;
//                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                    $sql_comments[$key]['all_comm'] = false;
//                    $tpl->compile('content');
                }
                $query = $sql_comments;
                //Загружаем форму ответа
//                $tpl->load_template('groups/record.tpl');
//                $tpl->set('{rec-id}', );
//                $params['rec_id'] = $rec_id;
//                $tpl->set('{user-id}', $public_id);
//                $params['user_id'] = $public_id;
//                $tpl->set('[comment-form]', '');
//                $tpl->set('[/comment-form]', '');
//                $params['comment_form'] = true;
//                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
//                $params['record'] = false;
//                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
//                $params['comment'] = false;
//                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
//                $params['all_comm'] = false;

                $params['wall_records'] = Wall::build($query);
            }
        }
    }

    /**
     * Страница загрузки фото в сообщество
     *
     * @return int
     */
    public function photos(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) {
//                $page = (int)$request['page'];
//            } else {
//                $page = 1;
//            }
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $public_id = (int)$request['public_id'];
            $rowPublic = $db->super_query("SELECT admin, photos_num FROM `communities` WHERE id = '{$public_id}'");
            if(stripos($rowPublic['admin'], "u{$user_id}|") !== false){

                if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
                $gcount = 36;
                $limit_page = ($page-1)*$gcount;

                //HEAD
//                $tpl->load_template('public/photos/head.tpl');
                $titles = array('фотография', 'фотографии', 'фотографий');//photos
                $params['photo_num'] = $rowPublic['photos_num'].' '.Gramatic::declOfNum($rowPublic['photos_num'], $titles);
                $params['public_id'] = $public_id;
                $params['top'] = true;
                $params['bottom'] = false;

                //Выводим фотографии
                if($rowPublic['photos_num']){
                    $sql_ = $db->super_query("SELECT photo FROM `attach` WHERE public_id = '{$public_id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
//                    $tpl->load_template('public/photos/photo.tpl');
                    foreach($sql_ as $key => $row){
                        $sql_[$key]['photo'] = $row['photo'];
                        $sql_[$key]['public_id'] = $public_id;
                    }

//                    box_navigation($gcount, $rowPublic['photos_num'], $page, 'groups.wall_attach_addphoto', $public_id);
                } else
                {
//                    msgbox('', '<div class="clear" style="margin-top:150px;margin-left:27px"></div>В альбоме сообщества нет загруженных фотографий.', 'info_2');
                }

                //BOTTOM
//                $tpl->load_template('public/photos/head.tpl');
                $params['bottom'] = true;
                $params['top'] = false;
            }
        }
        return view('info.info', $params);
    }

    /**
     * Выводим инфу о видео при прикреплении видео на стену
     *
     * @return int
     * @throws \JsonException
     */
    public function select_video_info(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

//            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $video_id = (int)$request['video_id'];
            $row = $db->super_query("SELECT photo FROM `videos` WHERE id = '".$video_id."'");
            if($row){
                $array = explode('/', $row['photo']);
                echo end($array);

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Ставим мне нравится
     *
     * @return int
     * @throws \JsonException
     */
    public function wall_like_yes(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $user_id = $user_info['user_id'];
            $rec_id = (int)$request['rec_id'];
            $row = $db->super_query("SELECT likes_users FROM `communities_wall` WHERE id = '".$rec_id."'");
            if($row AND stripos($row['likes_users'], "u{$user_id}|") === false){
                $likes_users = "u{$user_id}|".$row['likes_users'];
                $db->query("UPDATE `communities_wall` SET likes_num = likes_num+1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
                $server_time = Date::time();
                $db->query("INSERT INTO `communities_wall_like` SET rec_id = '".$rec_id."', user_id = '".$user_id."', date = '".$server_time."'");
//                return _e('true');

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Убераем мне нравится
     *
     * @return int
     * @throws \JsonException
     */
    public function wall_like_remove(): int
    {
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $user_id = $user_info['user_id'];
            $rec_id = (int)$request['rec_id'];
            $row = $db->super_query("SELECT likes_users FROM `communities_wall` WHERE id = '".$rec_id."'");
            if(stripos($row['likes_users'], "u{$user_id}|") !== false){
                $likes_users = str_replace("u{$user_id}|", '', $row['likes_users']);
                $db->query("UPDATE `communities_wall` SET likes_num = likes_num-1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
                $db->query("DELETE FROM `communities_wall_like` WHERE rec_id = '".$rec_id."' AND user_id = '".$user_id."'");
//                return _e('true');
                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Выводим последних 7 юзеров кто
     * поставил "Мне нравится"
     *
     * @throws \JsonException
     */
    public function wall_like_users_five(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');
        Tools::NoAjaxRedirect();
        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $rec_id = (int)$request['rec_id'];
            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo FROM `communities_wall_like` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rec_id}' ORDER by `date` DESC LIMIT 0, 7", true);
            if($sql_){
//                $config = Settings::load();
                $response = '';
                foreach($sql_ as $row){
                    if($row['user_photo']) {
                        $ava = '/uploads/users/' . $row['user_id'] . '/50_' . $row['user_photo'];
                    }
                    else {
                        $ava = '/images/no_ava_50.png';
                    }
                    $response .= '<a href="/u'.$row['user_id'].'" id="Xlike_user'.$row['user_id'].'_'.$rec_id.'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" alt="name" width="32" /></a>';
                }
                $status = Status::OK;
                $res = $response;
            }else{
                $status = Status::NOT_FOUND;
                $res = null;
            }
        }else{
            $status = Status::BAD_LOGGED;
            $res = null;
        }
        return _e_json(array(
            'status' => $status,
            'res' => $res,
        ) );
    }

    /**
     * Выводим всех юзеров которые
     * поставили "мне нравится"
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
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref FROM `communities_wall_like` tb1, `users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);

                if($sql_){
                    $params['top'] = true;
//                    $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                    $titles = array('человеку', 'людям', 'людям');//like
                    $params['subcr_num'] = 'Понравилось '.$liked_num.' '.Gramatic::declOfNum($liked_num, $titles);
                    $params['bottom'] = false;
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
                            $sql_[$key]['ava'] =  '/images/no_ava_50.png';
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
     * Рассказать друзьям "Мне нравится"
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function wall_tell(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $rid = (int)$request['rec_id'];

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

            if($row){
                if($row['tell_uid']){
                    $row['add_date'] = $row['tell_date'];
                    $row['author_user_id'] = $row['tell_uid'];
                    $row['public_id'] = $row['tell_uid'];
                } else
                    $row['public'] = 1;

                //Проверяем на существование этой записи у себя на стене
                $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE tell_uid = '{$row['public_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}' AND public = '{$row['public']}'");
                if($row['tell_uid'] != $user_id AND $myRow['cnt'] == false){
                    $row['text'] = $db->safesql($row['text']);
                    $row['attach'] = $db->safesql($row['attach']);

                    //Всталвяем себе на стену
                    $server_time = Date::time();
                    $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['public_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '".$row['attach']."'");
                    $dbid = $db->insert_id();
                    $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                    //Вставляем в ленту новостей
                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                    //Чистим кеш
                    $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                    $cache = new \Sura\Cache\Cache($storage, 'users');
                    $cache->remove("{$user_id}/profile_{$user_id}");

                    $status = Status::OK;
                    $err =  'hacking';
                } else {
//                    echo 1;
                    $status = Status::NOT_FOUND;
                    $err =  'hacking';
                }

            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Показ всех подписок
     *
     * @return int
     */
    public function all_people(): int
    {
        $params = array();

//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

//            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();

            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            $public_id = (int)$request['public_id'];
            $subscr_num = (int)$request['num'];

            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.friend_id = '{$public_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
//                $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                $params['top'] = true;
                $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                $params['subcr_num'] = $subscr_num.' '.Gramatic::declOfNum($subscr_num, $titles);
                $params['bottom'] = false;
//                $tpl->load_template('profile_friends.tpl');
                foreach($sql_ as $key => $row){
                    if($row['user_photo'])
                    {
                        $sql_[$key]['ava'] = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                    }
                    else
                    {
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }
                    $sql_[$key]['user_id'] = $row['user_id'];
                    $sql_[$key]['name'] = $row['user_name'];
                    $sql_[$key]['last_name'] = $row['user_lastname'];
                }
//                box_navigation($gcount, $subscr_num, $public_id, 'groups.all_people', $subscr_num);
            }
            return view('groups.subscription_box', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Показ всех сообщества юзера
     * на которые он подписан (BOX)
     *
     * @return int
     */
    public function all_groups_user(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $for_user_id = (int)$request['for_user_id'];
            $subscr_num = (int)$request['num'];

            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
//                $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                $params['top'] = true;
                $titles = array('подписка', 'подписки', 'подписок');//subscr
                $params['subcr_num'] = $subscr_num.' '.Gramatic::declOfNum($subscr_num, $titles);
                $params['bottom'] = false;
//                $tpl->load_template('/profile/profile_group.tpl');
                foreach($sql_ as $key => $row){
                    if($row['photo']) {
                        $sql_[$key]['ava'] = '/uploads/groups/'.$row['id'].'/50_'.$row['photo'];
                    }
                    else {
                        $sql_[$key]['ava'] = '/images/no_ava_50.png';
                    }
                    $sql_[$key]['name'] = stripslashes($row['title']);
                    $sql_[$key]['public_id'] =
                    $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                    $sql_[$key]['num'] = '<span id="traf">'.$row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles);
                    if($row['adres']) {
                        $sql_[$key]['adres'] = $row['adres'];
                    }
                    else {
                        $sql_[$key]['adres'] = 'public'.$row['id'];
                    }
                }
//                Tools::box_navigation($gcount, $subscr_num, $for_user_id, 'groups.all_groups_user', $subscr_num, $tpl);
            }
            return view('groups.subscription_box', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Одна запись со стены
     *
     * @return int
     */
    public function wallgroups(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        if($logged){
            $request = (Request::getRequest()->getGlobal());

//            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
            $params['title'] = $lang['communities'].' | Sura';

            $id = (int)$request['id'];
            $pid = (int)$request['pid'];

            $row = $db->super_query("SELECT id, adres, del, ban FROM `communities` WHERE id = '{$pid}'");

            if($row AND !$row['del'] AND !$row['ban']){

//                $tpl->load_template('groups/wall_head.tpl');
//                $tpl->set('{id}', $id);
                $params['id'] = $id;
//                $tpl->set('{pid}', $pid);
                $params['pid'] = $pid;
                if($row['adres'])
                {
//                    $tpl->set('{adres}', $row['adres']);
                    $params['adres'] = $row['adres'];
                }
                else
                {
//                    $tpl->set('{adres}', 'public'.$pid);
                    $params['adres'] = 'public'.$pid;
                }
//                $tpl->compile('info');

//                include __DIR__.'/../Classes/wall.public.php';
//                $wall = new \wall();
//                $wall->query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, adres FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0");
//                $wall->template('groups/record.tpl');
//                $wall->compile('content');
//                $server_time = Date::time();

                $query = $db->super_query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, adres FROM `communities_wall` tb1, `communities` tb2 WHERE tb1.id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0", true);
                $params['wall_records'] = Wall::build($query);
//                $wall->select($public_admin, $server_time);

//                $tpl->result['content'] = str_replace('width:500px;', 'width:710px;', $tpl->result['content']);

//                if(!$tpl->result['content'])
//                {
//                    msgbox('', '<br /><br /><br />Запись не найдена.<br /><br /><br />', 'info_2');
//                }

            } else
            {
//                msgbox('', '<br /><br />Запись не найдена.<br /><br /><br />', 'info_2');
            }

            //FIXME rename tpl
            return view('groups.one_wall', $params);
        }
        //FIXME rename tpl
        return view('info.info', $params);
    }

    /**
     * Закривление записи
     *
     * @return int
     * @throws \JsonException
     */
    public function fasten(): int
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;

//            Tools::NoAjaxQuery();

            $rec_id = (int)$request['rec_id'];

            //Выводим ИД группы
            $row = $db->super_query("SELECT public_id FROM `communities_wall` WHERE id = '{$rec_id}'");

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Убераем фиксацию у пред записи
                $db->query("UPDATE `communities_wall` SET fixed = '0' WHERE fixed = '1' AND public_id = '{$row['public_id']}'");

                //Ставим фиксацию записи
                $db->query("UPDATE `communities_wall` SET fixed = '1' WHERE id = '{$rec_id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Убераем фиксацию
     *
     * @return int
     * @throws \JsonException
     */
    public function unfasten(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $user_id = $user_info['user_id'];
//            if($request['page'] > 0) $page = (int)$request['page']; else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();

            $rec_id = (int)$request['rec_id'];

            //Выводим ИД группы
            $row = $db->super_query("SELECT public_id FROM `communities_wall` WHERE id = '{$rec_id}'");

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Убераем фиксацию записи
                $db->query("UPDATE `communities_wall` SET fixed = '0' WHERE id = '{$rec_id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Загрузка обложки
     * @throws Exception
     * @deprecated
     */
    public function upload_cover(){
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();

            $public_id = (int)$request['id'];

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Получаем данные о файле
                $image_tmp = $_FILES['uploadfile']['tmp_name'];
                $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                $server_time = Date::time();
                $image_rename = substr(md5($server_time+random_int(1,100000)), 0, 20); // имя файла
                $image_size = $_FILES['uploadfile']['size']; // размер файла
                $array = explode(".", $image_name);
                $type = end($array); // формат файла

                $max_size = 1024 * 7000;

                //Проверка размера
                if($image_size <= $max_size){

                    //Разришенные форматы
                    $allowed_files = explode(', ', 'jpg, jpeg, jpe, png, gif');

                    //Проверям если, формат верный то пропускаем
                    if(in_array(strtolower($type), $allowed_files)){

                        $res_type = strtolower('.'.$type);

                        $upload_dir = __DIR__."/../../uploads/groups/{$public_id}/";

                        if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                            $manager = new ImageManager(array('driver' => 'gd'));

                            //Создание оригинала
                            $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(800, null);
                            $image->save($upload_dir.$image_rename.'.webp', 90);

                            unlink($upload_dir.$image_rename.$res_type);
                            $res_type = '.webp';

                            //Выводим и удаляем пред. обложку
                            $row = $db->super_query("SELECT cover FROM `communities` WHERE id = '{$public_id}'");
                            if($row){
                                @unlink($upload_dir.$row['cover']);
                            }

                            $imgData = getimagesize($upload_dir.$image_rename.$res_type);
                            $rImgsData = round($imgData[1] / ($imgData[0] / 800));

                            //Обновдяем обложку в базе
                            $pos = round(($rImgsData / 2) - 100);

                            if($rImgsData <= 230){
                                $rImgsData = 230;
                                $pos = 0;
                            }

                            $db->query("UPDATE `communities` SET cover = '{$image_rename}{$res_type}', cover_pos = '{$pos}' WHERE id = '{$public_id}'");

                            echo $public_id.'/'.$image_rename.$res_type.'|'.$rImgsData;

                        }

                    } else
                        echo 2;

                } else
                    echo 1;

            }

        }
    }

    /**
     * Сохранение новой позиции обложки
     *
     * @return false|string
     * @throws \JsonException
     * @deprecated
     */
    public function savecoverpos(): bool|string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $request = (Request::getRequest()->getGlobal());
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();

            $public_id = (int)$request['id'];

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                $pos = (int)$request['pos'];
                if($pos < 0) $pos = 0;

                $db->query("UPDATE `communities` SET cover_pos = '{$pos}' WHERE id = '{$public_id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::BAD_RIGHTS;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     *
     * @deprecated
     */
    public function delcover(){
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $request = (Request::getRequest()->getGlobal());
            $public_id = (int)$request['id'];

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Выводим и удаляем пред. обложку
                $row = $db->super_query("SELECT cover FROM `communities` WHERE id = '{$public_id}'");
                if($row){

                    $upDir = __DIR__."/../../uploads/groups/{$public_id}/";
                    @unlink($upDir.$row['cover']);

                }

                $db->query("UPDATE `communities` SET cover_pos = '', cover = '' WHERE id = '{$public_id}'");

            }

        }
    }

    /**
     * invite box
     *
     * @return int
     */
    public function invitebox(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $request = (Request::getRequest()->getGlobal());
            $pub_id = (int)$request['id'];

            $limit_friends = 20;
            if($_POST['page_cnt'] > 0) $page_cnt = (int)$request['page_cnt'] * $limit_friends;
            else $page_cnt = 0;

            //Выводим список участников группы
            $rowPub = $db->super_query("SELECT ulist FROM `communities` WHERE id = '{$pub_id}'");

            //Выводим список друзей
            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_photo, user_search_pref, user_sex FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 ORDER by `friends_date` DESC LIMIT {$page_cnt}, {$limit_friends}", 1);

            if($sql_){

//                $tpl->load_template('groups/inviteuser.tpl');
                $config = Settings::load();
                foreach($sql_ as $key => $row){

                    if($row['user_photo'])
                    {
                        $sql_[$key]['ava'] = $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo'];
                    }
                    else
                    {
                        $sql_[$key]['ava'] = "/images/100_no_ava.png";
                    }

                    $sql_[$key]['user_id'] = $row['friend_id'];
                    $sql_[$key]['name'] = $row['user_search_pref'];

                    //Проверка, юзер есть в сообществе или нет
                    if(stripos($rowPub['ulist'], '|'.$row['friend_id'].'|') !== false){
                        $sql_[$key]['yes_group'] = 'grInviteYesed';
                        $sql_[$key]['yes_text']  =  '<div class="fl_r online grInviteOk">в сообществе</div>';
                        $sql_[$key]['function'] = true;
                    } else {

                        //Проверка, юзеру отправлялось приглашение или нет
                        $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_join` WHERE for_user_id = '{$row['friend_id']}' AND public_id = '{$pub_id}'");

                        if($check['cnt']){
                            $sql_[$key]['yes_group'] = 'grInviteYesed';
                            if($row['user_sex'] == 2)
                            {
                                $sql_[$key]['yes_text'] = '<div class="fl_r online grInviteOk">приглашена</div>';
                            }
                            else
                            {
                                $sql_[$key]['yes_text'] = '<div class="fl_r online grInviteOk">приглашен</div>';
                            }
                            $sql_[$key]['function'] = true;

                        } else {
                            $sql_[$key]['yes_group'] = 'grIntiveUser';
                            $sql_[$key]['yes_text'] = true;
                            $sql_[$key]['function'] = 'groups.inviteSet';
                        }
                    }
//                    $tpl->compile('friends');
                }
                $numFr = count($sql_);
            }
            if(!$page_cnt){
//                $tpl->load_template('groups/invitebox.tpl');
//                $tpl->set('{friends}', $tpl->result['friends']);
                $params['friends'] = 'friends';
                $params['id'] = $pub_id;

                if($numFr == $limit_friends){
                    $params['but'] = true;
                } else
                {
                    $params['but'] = false;
                }
            } else {
//                    $tpl->result['content'] = $tpl->result['friends'];
            }
            return view('groups.invite_box', $params);
        }
        return view('info.info', $params);
    }

    /**
     * invite send
     *
     * @return int
     * @throws \JsonException
     */
    public function invitesend(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            Tools::NoAjaxQuery();
            $request = (Request::getRequest()->getGlobal());
            $pub_id = (int)$request['id'];
            $limit = 50; #лимит в день

            //Выводим список участников группы
            $rowPub = $db->super_query("SELECT id, ulist FROM `communities` WHERE id = '{$pub_id}'");

            //Дата заявки
            $server_time = Date::time();
            $newData = date('Y-m-d', $server_time);

            //Считаем сколько заявок было отправлено за последние сутки
            $rowCnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_join` WHERE user_id = '{$user_id}' AND public_id = '{$pub_id}' AND date = '{$newData}'");

            //Создаем точку отчета для цикла foreach, чтоб если было уже 49 отправок, и юзер еще выбрал 49 то скрипт в масиве заметил это и прекратил действия
            $i = $rowCnt['cnt'];

            //Если заявок меньше указаного лимита, то пропускаем
            if($rowCnt['cnt'] < $limit){

                //Если такая гурппа есть
                if($rowPub['id']){

                    //Получаем список, которых надо пригласить и формируем его
                    $arr_list = explode('|', $request['ulist']);

                    foreach($arr_list as $ruser_id){

                        $ruser_id = (int)$ruser_id;

                        if($ruser_id AND $user_id != $ruser_id AND $i < $limit){

                            //Проверка, такой юзер в базе есть или нет
                            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$ruser_id}'");

                            if($row['cnt']){

                                //Проверка, юзер есть в сообществе или нет
                                if(stripos($rowPub['ulist'], '|'.$ruser_id.'|') === false){

                                    //Проверка, юзеру отправлялось приглашение или нет
                                    $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_join` WHERE for_user_id = '{$ruser_id}' AND public_id = '{$pub_id}'");

                                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                                    $check_friend = (new \App\Libs\Friends)->CheckFriends($ruser_id);

                                    //Если нет приглашения, то отправляем приглашение
                                    if(!$check['cnt'] AND $check_friend){

                                        $i++;

                                        //Вставляем в таблицу приглашений заявку
                                        $db->query("INSERT INTO `communities_join` SET user_id = '{$user_id}', for_user_id = '{$ruser_id}', public_id = '{$pub_id}', date = '{$newData}'");

                                        //Добавляем юзеру +1 в приглашениях
                                        $db->query("UPDATE `users` SET invties_pub_num = invties_pub_num + 1 WHERE user_id = '{$ruser_id}'");

                                    }

                                }

                            }

                        }

                    }

                }

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::LIMIT;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * invites
     *
     * @return int
     */
    public function invites(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

            //Если подгружаем
//            if($page_cnt){

//                Tools::NoAjaxQuery();

//            }
            $request = (Request::getRequest()->getGlobal());
            $limit_num = 20;
            if($request['page_cnt'] > 0) $page_cnt = (int)$request['page_cnt'] * $limit_num;
            else $page_cnt = 0;

            //Загружаем верхушку
            if(!$page_cnt){
//                $tpl->load_template('groups/invites_head.tpl');
                if($user_info['invties_pub_num']){
                    $params['num'] = $user_info['invties_pub_num'].' '.declOfNum($user_info['invties_pub_num'], array('приглашение', 'приглашения', 'приглашений'));
                    $params['yes'] = true;
                    $params['no'] = false;
                } else {
                    $params['no'] = true;
                    $params['yes'] = false;
                }
//                $tpl->compile('info');
            }
            //Выводим сообщества
            if($user_info['invties_pub_num']){
                //SQL Запрос на вывод
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.id, title, photo, traf, adres, tb3.user_search_pref, user_photo FROM `communities_join` tb1, `communities` tb2, `users` tb3 WHERE tb1.for_user_id = '{$user_id}' AND tb1.public_id = tb2.id AND tb1.user_id = tb3.user_id ORDER by `id` DESC LIMIT {$page_cnt}, {$limit_num}", 1);
                if($sql_){
//                    $tpl->load_template('groups/invite.tpl');
                    foreach($sql_ as $key => $row){
                        if($row['photo'])
                        {
                            $sql_[$key]['photo'] = "/uploads/groups/{$row['id']}/100_{$row['photo']}";
                        }
                        else
                        {
                            $sql_[$key]['photo'] = "/images/no_ava_groups_100.gif";
                        }
                        $sql_[$key]['name'] = stripslashes($row['title']);
                        $sql_[$key]['traf'] = $row['traf'].' '.declOfNum($row['traf'], array('участник', 'участника', 'участников'));
                        $sql_[$key]['id'] =  $row['id'];

                        if($row['adres'])
                        {
                            $sql_[$key]['adres'] = $row['adres'];
                        }
                        else
                        {
                            $sql_[$key]['adres'] = 'public'.$row['id'];
                        }
                        $sql_[$key]['inviter_name'] = $row['user_search_pref'];
                        $sql_[$key]['inviter_id'] = $row['user_id'];

                        if($row['user_photo'])
                        {
                            $sql_[$key]['inviter_ava'] = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                        }
                        else
                        {
                            $sql_[$key]['inviter_ava'] = '/images/100_no_ava.png';
                        }
                    }
                }
            }
            //Загружаем низ
            if(!$page_cnt AND $user_info['invties_pub_num'] > $limit_num){

//                $tpl->load_template('groups/invite_bottom.tpl');
//                $tpl->compile('content');

            }

            //Если подгружаем
            if($page_cnt){
//                Tools::AjaxTpl($tpl);
            }
            return view('groups.invite', $params);
        }
        return view('info.info', $params);
    }

    /**
     * invite_no
     *
     * @return int
     * @throws \JsonException
     */
    public function invite_no(): int
    {
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//            $params['title'] = $lang['communities'].' | Sura';

//            Tools::NoAjaxQuery();
            $request = (Request::getRequest()->getGlobal());
            $id = (int)$request['id'];

            //Проверка на приглашению юзеру
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

            //Если есть приглашение, то удаляем его
            if($check['cnt']){

                $db->query("DELETE FROM `communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

                //Обновляем кол-во приглашений
                $db->query("UPDATE `users` SET invties_pub_num = invties_pub_num - 1 WHERE user_id = '{$user_id}'");

                $status = Status::OK;
                $err =  'yes';
            }else{
                $status = Status::NOT_FOUND;
                $err =  'hacking';
            }
        }else{
            $status = Status::BAD_LOGGED;
            $err =  'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ) );
    }

    /**
     * Вывод всех сообществ
     *
     * @return int
     */
    public function index(): int
    {
        $params = array();

        $lang = $this->get_langs();
        $db = $this->db();
        $logged = $this->logged();
        $user_info = $params['user']['user_info'];

        if($logged){
            //$act = $_GET['act'];
            $act = '';
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            if(isset($request['page']) AND $request['page'] > 0) {
                $page = (int)$request['page'];
            }else {
                $page = 1;
            }
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $params['title'] = $lang['communities'].' | Sura';

            $owner = $db->super_query("SELECT user_public_num FROM `users` WHERE user_id = '{$user_id}'");

            if($act == 'admin'){
//                $tpl->load_template('groups/head_admin.tpl');
                $sql_sort = "SELECT id, title, photo, traf, adres FROM `communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]'");
                $owner['user_public_num'] = $sql_count['cnt'];
            } else {
                $sql_sort = "SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
//                $tpl->load_template('groups/head.tpl');
            }

            if($owner['user_public_num']){
                $titles = array('сообществе', 'сообществах', 'сообществах');//groups
                $params['num'] = $owner['user_public_num'].' '.Gramatic::declOfNum($owner['user_public_num'], $titles);
                $params['groups_yes'] = true;
            } else{
                $params['groups_yes'] = false;
            }

            if($owner['user_public_num']){

                $sql_ = $db->super_query($sql_sort, true);

                foreach($sql_ as $key => $row){
                    $sql_[$key]['id'] = $row['id'];
                    if($row['adres']) {
                        $sql_[$key]['adres'] =  $row['adres'];
                    }
                    else{
                        $sql_[$key]['adres'] = 'public'.$row['id'];
                    }

                    $sql_[$key]['name'] = stripslashes($row['title']);
                    $titles = array('участник', 'участника', 'участников');//groups_users
                    $sql_[$key]['traf'] = $row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles);

                    if($act != 'admin'){
                        $sql_[$key]['admin'] = true;
                    } else{
                        $sql_[$key]['admin'] = false;
                    }

                    if($row['photo']){
                        $sql_[$key]['photo'] = "/uploads/groups/{$row['id']}/100_{$row['photo']}";
                    }
                    else{
                        $sql_[$key]['photo'] = "/images/no_ava_groups_100.gif";
                    }
                }
                $params['groups'] = $sql_;

                if($act == 'admin') {
                    $admn_act = 'act=admin&';
                }

//                $params['navigation'] = Tools::navigation($gcount, $owner['user_public_num'], 'groups?'.$admn_act.'page=', $tpl);

            }
            return view('groups.groups', $params);
        }
        else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Вывод всех сообществ
     *
     * @return int
     */
    public function admin(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        if($logged){
            //$act = $_GET['act'];
            $act = '';
            $user_id = $user_info['user_id'];
            $request = (Request::getRequest()->getGlobal());
            if(isset($request['page']) AND $request['page'] > 0)
                $page = (int)$request['page'];
            else
                $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $params['title'] = $lang['communities'].' | Sura';

            $owner = $db->super_query("SELECT user_public_num FROM `users` WHERE user_id = '{$user_id}'");

//            $tpl->load_template('groups/head_admin.tpl');
            $sql_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]'");
            $owner['user_public_num'] = $sql_count['cnt'];

            if($owner['user_public_num']){
                $titles = array('сообществе', 'сообществах', 'сообществах');//groups
                $params['num'] = $owner['user_public_num'].' '.Gramatic::declOfNum($owner['user_public_num'], $titles);
                $params['groups_yes'] = true;
            } else{
                $params['groups_yes'] = false;
            }
//            $tpl->compile('info');

            if($owner['user_public_num']){

                $sql_ = $db->super_query("SELECT id, title, photo, traf, adres FROM `communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}", true);

//                $tpl->load_template('groups/group.tpl');
                foreach($sql_ as $key => $row){
                    $sql_[$key]['id'] = $row['id'];
                    if($row['adres']) {
                        $sql_[$key]['adres'] = $row['adres'];
                    }
                    else {
                        $sql_[$key]['adres'] = 'public'.$row['id'];
                    }

                    $sql_[$key]['name'] = stripslashes($row['title']);
                    $titles = array('участник', 'участника', 'участников');//groups_users
                    $sql_[$key]['traf'] = $row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles);

                    if($act != 'admin'){
                        $sql_[$key]['admin'] = true;
                    } else
                    {
                        $sql_[$key]['admin'] = false;
                    }

                    if($row['photo'])
                    {
                        $sql_[$key]['photo'] = "/uploads/groups/{$row['id']}/100_{$row['photo']}";
                    }
                    else
                    {
                        $sql_[$key]['photo'] = "/images/no_ava_groups_100.gif";
                    }
//                    $tpl->compile('content');
                }
                $params['groups'] = $sql_;

                if($act == 'admin')
                    $admn_act = 'act=admin&';

//                $tpl = Tools::navigation($gcount, $owner['user_public_num'], 'groups?'.$admn_act.'page=', $tpl);

            }
            return view('groups.groups', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * edit main
     *
     * @return int
     */
    public function edit_main(): int
    {
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path['4'])) $id = $path['4']; else $id = $path['3'];
        $params['id'] = $id;
        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];
        $db = $this->db();
        $row = $db->super_query("SELECT id, admin, title, descr, ban_reason, traf, ulist, photo, date, data_del, feedback, comments, discussion, links_num, videos_num, real_admin, rec_num, del, ban, adres, audio_num, type_public, web, date_created, privacy FROM `communities` WHERE id = '{$id}'", false);

        if(stripos($row['admin'], "u{$user_id}|") !== false){
//            $explode_type = explode('-',$row['type_public']);
//            $explode_created = explode('-',$row['date_created']);

            $params['pid'] = $id;
            $params['title'] = stripslashes($row['title']);
            $params['descr'] = stripslashes($row['descr']);
            $params['website'] = stripslashes($row['web']);
            $params['edit_descr'] = Validation::myBrRn(stripslashes($row['descr']));
            if(!$row['adres']) $row['adres'] = 'public'.$row['id'];
            $params['adres'] = $row['adres'];
            $privaces = xfieldsdataload($row['privacy']);

            if($row['comments']) {
                $params['settings_comments'] = 'comments';
            }
            else {
                $params['settings_comments'] = 'none';
            }
            if($privaces['p_audio']) {
                $params['settings_audio'] = 'audio';
            }
            else {
                $params['settings_audio'] = 'none';
            }
            if($privaces['p_videos']) {
                $params['settings_videos'] = 'videos';
            }
            else {
                $params['settings_videos'] = 'none';
            }
            if($privaces['p_contact']) {
                $params['settings_contact'] = 'contact';
            }
            else {
                $params['settings_contact'] = 'none';
            }

            if($row['real_admin'] == $user_id){
                $params['admin_del'] = true;
            } else {
                $params['admin_del'] = false;
            }
        } else {
//            msgbox('', '<div style="margin:0 auto; width:370px;text-align:center;height:65px;font-weight:bold">Вы не имеете прав для редактирования данного сообщества.<br><br><div class="button_blue fl_l" style="margin-left:115px;"><a href="/public'.$pid.'" onClick="Page.Go(this.href); return false"><button>На страницу сообщества</button></a></div></div>', 'info_red');
        }

        $params['menu'] = Menu::public_edit();
        $params['title'] = 'Редактирование информации';
        return view('groups.edit', $params);
    }

    /**
     * edit users
     * @return int
     */
    public function edit_users(): int
    {
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path['4'])) $id = $path['4']; else $id = $path['3'];
        $params['id'] = $id;

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];
        $db = $this->db();
        $row = $db->super_query("SELECT id, admin, title, descr, ban_reason, traf, ulist, photo, date, data_del, feedback, comments, discussion, links_num, videos_num, real_admin, rec_num, del, ban, adres, audio_num, type_public, web, date_created, privacy FROM `communities` WHERE id = '{$id}'", false);
        $request = (Request::getRequest()->getGlobal());
        if(isset($request['tab'])) {
            $tab = $request['tab'];
        }
        else{
            $tab = false;
        }
        if(stripos($row['admin'], "u{$user_id}|") !== false) {

            if(!isset($request['page_cnt']))
                $page_cnt = 10;
            else
                $page_cnt = $request['page_cnt']*10;
            $explode_admins = array_slice(str_replace('|', '', explode('u', $row['admin'])), 0, $page_cnt);
            unset($explode_admins[0]);
            $explode_users = array_slice(str_replace('|','',explode('||', $row['ulist'])), 0, $page_cnt);

            $metatags['title'] = 'Участники';
            if(!isset($request['page_cnt'])) {
//                $tpl->load_template('epage/users.tpl');
                $params['pid'] = $id;
                if($tab == 'admin') {
                    $params['button_tab_b'] = 'buttonsprofileSec';
                    $params['button_tab_a'] = '';
                    $params['type'] = 'admin';
                    $params['admin_page'] = true;
                    $params['noadmin_page'] = false;
                }
                else {
                    $params['button_tab_b'] = '';
                    $params['button_tab_a'] = 'buttonsprofileSec';
                    $params['type'] = 'all';
                    $params['noadmin_page'] = true;
                    $params['admin_page'] = false;
                }
                if(!$row['adres']) {
                    $params['adres'] = 'public'.$row['id'];
                }
                else {
                    $params['adres'] = $row['adres'];
                }
                if($tab == 'admin') {
                    $titles = array('администратор', 'администратора', 'администраторов');//admins
                    $params['titles'] = 'В сообществе '.count($explode_admins).' '.Gramatic::declOfNum($row['traf'], $titles);
                }
                else {
                    $titles = array('участник', 'участника', 'участников');//apps
                    $params['titles'] = 'В сообществе '.$row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles).'';
                }
                $params['count'] = $row['traf'];
            }

//            if($_POST['page_cnt'])
//                NoAjaxQuery();

//            if(!$_POST['page_cnt']) {
//                $tpl->result['content'] .= '<table><tbody><tr><td id="all_users">';
            if($tab == 'admin') {
                $foreach = $explode_admins;

            }
            else {
                $foreach = $explode_users;
            }
            $users = array();
            foreach($foreach as $key => $user) {
                $user = (string)($user);
                $p_user = $db->super_query("SELECT user_search_pref, user_photo, alias, user_last_visit FROM `users` WHERE user_id = '{$user}'");
                $a_user = $db->super_query("SELECT level FROM `communities_admins` WHERE user_id = '{$user}'");
//                $tpl->load_template('epage/user.tpl');
                $users[$key]['uid'] = $user;
                $users[$key]['name'] = $p_user['user_search_pref'];
                $users[$key]['ava'] = $p_user['user_photo'];
                if($a_user) {
                    if($row['real_admin'] == $user) {
                        $users[$key]['tags'] = '<b>Создатель</b>';
                    }
                    else {
                        $users[$key]['tags'] = '';
                    }
                    $users[$key]['view_tags'] = '';
                } else {
                    $users[$key]['view_tags'] = 'no_display';
                    $users[$key]['tags'] = '';
                }

                $server_time = Date::time();
                $online_time = $server_time - 60;
                if($p_user['user_last_visit'] >= $online_time) {
                    $users[$key]['online'] = true;
                } else {
                    $users[$key]['online'] = false;
                }
                if($p_user['alias']) $alias = $p_user['alias'];
                else $alias = 'u'.$user;
                $users[$key]['adres'] = $alias;
                if($p_user['user_photo']) $avatar = '/uploads/users/'.$user.'/100_'.$p_user['user_photo'].'';
                else $avatar = '/images/100_no_ava.png';
                $users[$key]['ava_photo'] = $avatar;
                if(in_array($user, $explode_admins)) {
                    if($user != $row['real_admin']) {
                        $users[$key]['yes_admin'] = true;
                    } else {
                        $users[$key]['yes_admin'] = false;
                    }
                    $users[$key]['no_admin'] = false;
                }
                else {
                    if($user != $row['real_admin']) {
                        $users[$key]['no_admin'] = true;
                    } else {
                        $users[$key]['no_admin'] = false;
                    }
                    $users[$key]['yes_admin'] = false;
                }
            }

            $params['users'] = $users;

//            if(!$_POST['page_cnt'])
//                $tpl->result['content'] .= '</td></tr></tbody></table>';

//            if($_POST['page_cnt']){
//                AjaxTpl();
//                exit;
//            }

        } else {
//            msgbox('', '<div style="margin:0 auto; width:370px;text-align:center;height:65px;font-weight:bold">Вы не имеете прав для редактирования данного сообщества.<br><br><div class="button_blue fl_l" style="margin-left:115px;"><a href="/public'.$pid.'" onClick="Page.Go(this.href); return false"><button>На страницу сообщества</button></a></div></div>', 'info_red');
        }


        $params['menu'] = Menu::public_edit();
        $params['title'] = 'ttt';
        return view('groups.edit_users', $params);
    }

    /**
     * edit
     *
     * @return int
     */
    public function edit(): int
    {
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path['4'])) $id = $path['4']; else $id = $path['3'];
        $params['id'] = $id;


        $params['menu'] = Menu::public_edit();
        $params['title'] = 'ttt';
        $params['info'] = 'err';
        return view('groups.edit_old', $params);
    }
}