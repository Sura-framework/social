<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Libs\Thumb;
use Sura\Libs\Cache;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class AlbumsController extends Module{

    /**
     * Создание альбома
     */
    public function create($params){
        //$tpl = $params['tpl'];
        //$db = $this->db();
        //$user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

        }
    }

    /**
     * Страница создания альбома
     */
    public function create_page($params){
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();

            $name = Validation::ajax_utf8(Validation::textFilter($_POST['name'], false, true));
            $descr = Validation::ajax_utf8(Validation::textFilter($_POST['descr']));
            $privacy = intval($_POST['privacy']);
            $privacy_comm = intval($_POST['privacy_comm']);
            if($privacy <= 0 OR $privacy > 3) $privacy = 1;
            if($privacy_comm <= 0 OR $privacy_comm > 3) $privacy_comm = 1;
            $sql_privacy = $privacy.'|'.$privacy_comm;

            if(isset($name) AND !empty($name)){

                //Выводи кол-во альбомов у юзера
                $row = $db->super_query("SELECT user_albums_num FROM `users` WHERE user_id = '{$user_info['user_id']}'");
                $config = Settings::loadsettings();
                if($row['user_albums_num'] < $config['max_albums']){
                    //hash
                    $_IP = '';
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $hash = md5(md5($server_time).$name.$descr.md5($user_info['user_id']).md5($user_info['user_email']).$_IP);
                    $date_create = date('Y-m-d H:i:s', $server_time);

                    $sql_ = $db->query("INSERT INTO `albums` (user_id, name, descr, ahash, adate, position, privacy) VALUES ('{$user_info['user_id']}', '{$name}', '{$descr}', '{$hash}', '{$date_create}', '0', '{$sql_privacy}')");
                    $id = $db->insert_id();
                    $db->query("UPDATE `users` SET user_albums_num = user_albums_num+1 WHERE user_id = '{$user_info['user_id']}'");

                    Cache::mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends|user_{$user_info['user_id']}/albums_cnt_friends|user_{$user_info['user_id']}/albums_cnt_all|user_{$user_info['user_id']}/profile_{$user_info['user_id']}");
                    if($sql_)
                        echo '/albums/add/'.$id;
                    else
                        echo 'no';
                } else
                    echo 'max';
            } else
                echo 'no_name';

            die();
        }
    }

    /**
     * Страница добавление фотографий в альбом
     */
    public function add($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = intval($path['3']);

            //$aid = intval($_GET['aid']);
            $user_id = $user_info['user_id'];

            //Проверка на существование альбома
            $row = $db->super_query("SELECT name, aid FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");
            if($row){
                //$params['title'] = $lang['add_photo'];
                $params['title'] = $lang['add_photo'];
                $user_speedbar = $lang['add_photo_2'];
                $tpl->load_template('/albums/albums_addphotos.tpl');
                $tpl->set('{aid}', $aid);
                $tpl->set('{album-name}', stripslashes($row['name']));
                $tpl->set('{user-id}', $user_id);
                $tpl->set('{PHPSESSID}', $_COOKIE['PHPSESSID']);
                $tpl->compile('content');

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            } else
                Hacking();
        }
    }

    /**
     * Загрузка фотографии в альбом
     */
    public function upload($params){
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){

            Tools::NoAjaxQuery();

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = intval($path['3']);

            //$aid = intval($_GET['aid']);
            $user_id = $user_info['user_id'];

            //Проверка на существование альбома и то что загружает владелец альбома
            $row = $db->super_query("SELECT aid, photo_num, cover FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");
            if($row){
                $config = Settings::loadsettings();
                //Проверка на кол-во фоток в альбоме
                if($row['photo_num'] < $config['max_album_photos']){

                    //Директория юзеров
                    $uploaddir = __DIR__.'/../../public/uploads/users/';

                    //Если нет папок юзера, то создаём их
                    if(!is_dir($uploaddir.$user_id)){
                        @mkdir($uploaddir.$user_id, 0777 );
                        @chmod($uploaddir.$user_id, 0777 );
                        @mkdir($uploaddir.$user_id.'/albums', 0777 );
                        @chmod($uploaddir.$user_id.'/albums', 0777 );
                    }

                    //Если нет папки альбома, то создаём её
                    $upload_dir = __DIR__.'/../../public/uploads/users/'.$user_id.'/albums/'.$aid.'/';
                    if(!is_dir($upload_dir)){
                        @mkdir($upload_dir, 0777);
                        @chmod($upload_dir, 0777);
                    }
                    $config = Settings::loadsettings();
                    //Разришенные форматы
                    $allowed_files = explode(', ', $config['photo_format']);

                    //Получаем данные о фотографии
                    $image_tmp = $_FILES['uploadfile']['tmp_name'];
                    $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
                    $image_size = $_FILES['uploadfile']['size']; // размер файла
                    $image_name_arr = explode(".", $image_name);
                    $type = end($image_name_arr); // формат файла

                    //Проверям если, формат верный то пропускаем
                    if(in_array(strtolower($type), $allowed_files)){
                        $config = Settings::loadsettings();
                        $config['max_photo_size'] = $config['max_photo_size'] * 1000;
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
                                    $position_all = $position_all+1;
                                    $_SESSION['position_all'] = $position_all;
                                } else {
                                    $position_all = 100000;
                                    $_SESSION['position_all'] = $position_all;
                                }

                                //Вставляем фотографию
                                $db->query("INSERT INTO `photos` (album_id, photo_name, user_id, date, position) VALUES ('{$aid}', '{$image_rename}{$res_type}', '{$user_id}', '{$date}', '{$position_all}')");
                                $ins_id = $db->insert_id();

                                //Проверяем на наличии обложки у альбома, если нету то ставим обложку загруженную фотку
                                if(!$row['cover'])
                                    $db->query("UPDATE `albums` SET cover = '{$image_rename}{$res_type}' WHERE aid = '{$aid}'");

                                $db->query("UPDATE `albums` SET photo_num = photo_num+1, adate = '{$date}' WHERE aid = '{$aid}'");

                                $config = Settings::loadsettings();
                                $img_url = $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$aid.'/c_'.$image_rename.$res_type;

                                //Результат для ответа
                                echo $ins_id.'|||'.$img_url.'|||'.$user_id;

                                $photos_num = null; // bug !!!

                                //Удаляем кеш позиций фотографий
                                if(!$photos_num)
                                    Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);

                                //Чистим кеш
                                Cache::mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends|user_{$user_info['user_id']}/position_photos_album_{$aid}");

                                $img_url = str_replace($config['home_url'], '/', $img_url);

                                //Добавляем действия в ленту новостей
                                $generateLastTime = $server_time-10800;
                                $row = $db->super_query("SELECT ac_id, action_text FROM `news` WHERE action_time > '{$generateLastTime}' AND action_type = 3 AND ac_user_id = '{$user_id}'");
                                if($row)
                                    $db->query("UPDATE `news` SET action_text = '{$ins_id}|{$img_url}||{$row['action_text']}', action_time = '{$server_time}' WHERE ac_id = '{$row['ac_id']}'");
                                else
                                    $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 3, action_text = '{$ins_id}|{$img_url}', action_time = '{$server_time}'");
                            } else
                                echo 'big_size';
                        } else
                            echo 'big_size';
                    } else
                        echo 'bad_format';
                } else
                    echo 'max_img';
            } else
                echo 'hacking';

            die();
        }
    }

    /**
     * Удаление фотографии из альбома
     */
    public function del_photo($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $id = intval($_GET['id']);
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
                Cache::mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends|user_{$row['user_id']}/position_photos_album_{$row['album_id']}");

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
            }

            die();
        }
    }

    /**
     * Установка новой обложки для альбома
     */
    public function set_cover($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $id = intval($_GET['id']);
            $user_id = $user_info['user_id'];

            //Выводи фотку из БД, если она есть
            $row = $db->super_query("SELECT album_id, photo_name FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $db->query("UPDATE `albums` SET cover = '{$row['photo_name']}' WHERE aid = '{$row['album_id']}'");

                //Чистим кеш
                Cache::mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends");
            }

            die();
        }
    }

    /**
     * Сохранение описания к фотографии
     */
    public function save_descr($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $user_id = $user_info['user_id'];
            $descr = Validation::ajax_utf8(Validation::textFilter($_POST['descr']));

            //Выводим фотку из БД, если она есть
            $row = $db->super_query("SELECT id FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $db->query("UPDATE `photos` SET descr = '{$descr}' WHERE id = '{$id}' AND user_id = '{$user_id}'");

                //Ответ скрипта
                echo stripslashes(myBr(htmlspecialchars(Validation::ajax_utf8(trim($_POST['descr'])))));
            }
            die();
        }
    }

    /**
     * Страница редактирование фотографии
     */
    public function editphoto($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $id = intval($_GET['id']);
            $user_id = $user_info['user_id'];
            $row = $db->super_query("SELECT descr FROM `photos` WHERE id = '{$id}' AND user_id = '{$user_id}'");
            if($row)
                echo stripslashes(myBrRn($row['descr']));
            die();
        }
    }

    /**
     * Сохранение сортировки альбомов
     */
    public function save_pos_albums($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $array = $_POST['album'];
            $count = 1;

            $config = Settings::loadsettings();

            //Если есть данные о масиве
            if($array AND $config['albums_drag'] == 'yes'){
                //Выводим масивом и обновляем порядок
                foreach($array as $idval){
                    $idval = intval($idval);
                    $db->query("UPDATE `albums` SET position = ".$count." WHERE aid = '{$idval}' AND user_id = '{$user_info['user_id']}'");
                    $count++;
                }

                //Чистим кеш
                Cache::mozg_mass_clear_cache_file("user_{$user_info['user_id']}/albums|user_{$user_info['user_id']}/albums_all|user_{$user_info['user_id']}/albums_friends");
            }
            die();
        }
    }

    /**
     * Сохранение сортировки фотографий
     */
    public function save_pos_photos($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $array	= $_POST['photo'];
            $count = 1;

            $config = Settings::loadsettings();

            //Если есть данные о масиве
            if($array AND $config['photos_drag'] == 'yes'){
                //Выводим масивом и обновляем порядок
                $row = $db->super_query("SELECT album_id FROM `photos` WHERE id = '{$array[1]}'");
                if($row){
                    $photo_info = '';
                    foreach($array as $idval){
                        $idval = intval($idval);
                        $db->query("UPDATE `photos` SET position = '{$count}' WHERE id = '{$idval}' AND user_id = '{$user_info['user_id']}'");
                        $photo_info .= $count.'|'.$idval.'||';
                        $count ++;
                    }
                    Cache::mozg_create_cache('user_'.$user_info['user_id'].'/position_photos_album_'.$row['album_id'], $photo_info);
                }
            }
            die();
        }
    }

    /**
     * Страница редактирование альбома
     */
    public function edit_page($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $user_id = $user_info['user_id'];
            $id = $db->safesql(intval($_POST['id']));
            $row = $db->super_query("SELECT aid, name, descr, privacy FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");
            if($row){
                $album_privacy = explode('|', $row['privacy']);
                $tpl->load_template('/albums/albums_edit.tpl');
                $tpl->set('{id}', $row['aid']);
                $tpl->set('{name}', stripslashes($row['name']));
                $tpl->set('{descr}', stripslashes(myBrRn($row['descr'])));
                $tpl->set('{privacy}', $album_privacy[0]);
                $tpl->set('{privacy-text}', strtr($album_privacy[0], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
                $tpl->set('{privacy-comment}', $album_privacy[1]);
                $tpl->set('{privacy-comment-text}', strtr($album_privacy[1], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я')));
                $tpl->compile('content');
                Tools::AjaxTpl($tpl);

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            }
            die();
        }
    }

    /**
     * Сохранение настроек альбома
     */
    public function save_album($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $user_id = $user_info['user_id'];
            $name = Validation::ajax_utf8(Validation::textFilter($_POST['name'], false, true));
            $descr = Validation::ajax_utf8(Validation::textFilter($_POST['descr']));

            $privacy = intval($_POST['privacy']);
            $privacy_comm = intval($_POST['privacy_comm']);
            if($privacy <= 0 OR $privacy > 3) $privacy = 1;
            if($privacy_comm <= 0 OR $privacy_comm > 3) $privacy_comm = 1;
            $sql_privacy = $privacy.'|'.$privacy_comm;

            //Проверка на существование юзера
            $chekc_user = $db->super_query("SELECT privacy FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");
            if($chekc_user){
                if(isset($name) AND !empty($name)){
                    $db->query("UPDATE `albums` SET name = '{$name}', descr = '{$descr}', privacy = '{$sql_privacy}' WHERE aid = '{$id}'");
                    echo stripslashes($name).'|#|||#row#|||#|'.stripslashes($descr);

                    Cache::mozg_mass_clear_cache_file("user_{$user_id}/albums|user_{$user_id}/albums_all|user_{$user_id}/albums_friends|user_{$user_id}/albums_cnt_friends|user_{$user_id}/albums_cnt_all");
                } else
                    echo 'no_name';
            }
            die();
        }
    }

    /**
     * Страница изминения обложки
     */
    public function edit_cover($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            $checkLang = '';
            include __DIR__.'/../lang/'.$checkLang.'/site.lng';
            Tools::NoAjaxQuery();

            $user_id = $user_info['user_id'];
            $id = intval($_POST['id']);

            if($user_id AND $id){

                //Для навигатор
                if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
                $gcount = 36;
                $limit_page = ($page-1)*$gcount;

                //Делаем SQL запрос на вывод
                $sql_ = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$id}' AND user_id = '{$user_id}' ORDER by `position` ASC LIMIT {$limit_page}, {$gcount}", 1);

                //Если есть SQL запрос то пропускаем
                if($sql_){

                    //Выводим данные о альбоме (кол-во фотографй)
                    $row_album = $db->super_query("SELECT photo_num FROM `albums` WHERE aid = '{$id}' AND user_id = '{$user_id}'");

                    $tpl->load_template('/albums/albums_editcover.tpl');
                    $tpl->set('[top]', '');
                    $tpl->set('[/top]', '');
                    $titles = array('фотография', 'фотографии', 'фотографий');//photos
                    $tpl->set('{photo-num}', $row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles));
                    $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                    $tpl->compile('content');

                    $config = Settings::loadsettings();

                    //Выводим масивом фотографии
                    $tpl->load_template('/albums/albums_editcover_photo.tpl');
                    foreach($sql_ as $row){
                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$id.'/c_'.$row['photo_name']);
                        $tpl->set('{id}', $row['id']);
                        $tpl->set('{aid}', $id);
                        $tpl->compile('content');
                    }
                    box_navigation($gcount, $row_album['photo_num'], $id, 'Albums.EditCover', '');

                    $tpl->load_template('/albums/albums_editcover.tpl');
                    $tpl->set('[bottom]', '');
                    $tpl->set('[/bottom]', '');
                    $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
                    $tpl->compile('content');

                    Tools::AjaxTpl($tpl);

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                } else
                    echo $lang['no_photo_alnumx'];
            } else
                Hacking();

            die();
        }
    }

    /**
     * Страница всех фотографий юзера, для прикрепления своей фотки кому-то на стену
     */
    public function all_photos_box($params){
        $tpl = Registry::get('tpl');
//        $db = $this->db();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            //Tools::NoAjaxQuery();
            $user_id = $user_info['user_id'];
            $notes = intval($_POST['notes']);

            //Для навигатор
            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 36;
            $limit_page = ($page-1)*$gcount;

            //Делаем SQL запрос на вывод
            $sql_ = $db->query("SELECT id, photo_name, album_id FROM `photos` WHERE user_id = '{$user_id}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}");
            $row_album = $db->super_query("SELECT SUM(photo_num) AS photo_num FROM `albums` WHERE user_id = '{$user_id}'");

            //Если есть Фотографии то пропускаем
            if($row_album['photo_num']){
                if($notes)
                    $tpl->load_template('notes/attatch_addphoto_top.tpl');
                else
                    $tpl->load_template('wall/attatch_addphoto_top.tpl');

                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $titles = array('фотография', 'фотографии', 'фотографий');//photos
                $tpl->set('{photo-num}', $row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles));
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('content');

                //Выводим циклом фотографии
                if(!$notes)
                    $tpl->load_template('/albums/albums_all_photos.tpl');
                else
                    $tpl->load_template('/albums/albums_box_all_photos_notes.tpl');

                while($row = $db->get_row($sql_)){
                    $tpl->set('{photo}', '/uploads/users/'.$user_id.'/albums/'.$row['album_id'].'/c_'.$row['photo_name']);
                    $tpl->set('{photo-name}',$row['photo_name']);
                    $tpl->set('{user-id}', $user_id);
                    $tpl->set('{photo-id}', $row['id']);
                    $tpl->set('{aid}', $row['album_id']);
                    $tpl->compile('content');
                }
                $tpl =  Tools::box_navigation($gcount, $row_album['photo_num'], $page, 'wall.attach_addphoto', $notes, $tpl);

                $tpl->load_template('/albums/albums_editcover.tpl');
                $tpl->set('[bottom]', '');
                $tpl->set('[/bottom]', '');
                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
                $tpl->compile('content');

                Tools::AjaxTpl($tpl);

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            } else {
                if($notes)
                    $scrpt_insert = "response[1] = response[1].replace('/c_', '/');wysiwyg.boxPhoto(response[1], 0, 0);";
                else
                    $scrpt_insert = "var imgname = response[1].split('/');wall.attach_insert('photo', response[1], 'attach|'+imgname[6].replace('c_', ''), response[2]);";

                echo <<<HTML
                        <script type="text/javascript">
                        $(document).ready(function(){
                            Xajax = new AjaxUpload('upload', {
                                action: '/attach/',
                                name: 'uploadfile',
                                onSubmit: function (file, ext) {
                                    if (!(ext && /^(jpg|png|jpeg|gif|jpe)$/.test(ext))) {
                                        addAllErr(lang_bad_format, 3300);
                                        return false;
                                    }
                                    Page.Loading('start');
                                },
                                onComplete: function (file, response){
                                    if(response == 'big_size'){
                                        addAllErr(lang_max_size, 3300);
                                        Page.Loading('stop');
                                    } else {
                                        var response = response.split('|||');
                                        {$scrpt_insert}
                                        Page.Loading('stop');
                                    }
                                }
                            });
                        });
                        </script>
                        HTML;
                echo $lang['no_photo_alnumx'].'<br /><br /><div class="button_div_gray fl_l" style="margin-left:205px"><button id="upload">Загрузить новую фотографию</button></div>';
            }

            die();
        }
    }

    /**
     * Удаление альбома
     */
    public function del_album($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            Tools::NoAjaxQuery();
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

                //Удаляем кеш позиций фотографий и кеш профиля
                Cache::mozg_clear_cache_file('user_'.$row['user_id'].'/position_photos_album_'.$row['aid']);
                Cache::mozg_clear_cache_file("user_{$user_info['user_id']}/profile_{$user_info['user_id']}");

                Cache::mozg_mass_clear_cache_file("user_{$user_id}/albums|user_{$user_id}/albums_all|user_{$user_id}/albums_friends|user_{$user_id}/albums_cnt_friends|user_{$user_id}/albums_cnt_all");
            }

            die();
        }
    }

    /**
     * Просмотр всех комментариев к альбому
     */
    public function all_comments($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];

            $mobile_speedbar = 'Комментарии';

            $user_id = $user_info['user_id'];
            $uid = intval($_GET['uid']);
            //$aid = intval($_GET['aid']);


            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = intval($path['3']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $page = intval($path['5']);


            if($aid) $uid = false;
            if($uid) $aid = false;

            if($page > 0) $page = intval($page); else $page = 1;
            $gcount = 25;
            $limit_page = ($page-1) * $gcount;

            $privacy = true;

            //Если вызваны комменты к альбому
            if($aid AND !$uid){
                $row_album = $db->super_query("SELECT user_id, name, privacy FROM `albums` WHERE aid = '{$aid}'");
                $album_privacy = explode('|', $row_album['privacy']);
                $uid = $row_album['user_id'];
                if(!$uid)
                    Hacking();
            }

            $CheckBlackList = Tools::CheckBlackList($uid);

            if($user_id != $uid)
                //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                $check_friend =Tools::CheckFriends($uid);

            if($aid AND $album_privacy){
                if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_id == $uid)
                    $privacy = true;
                else
                    $privacy = false;
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
                $tpl->load_template('/albums/albums_top.tpl');
                $tpl->set('{user-id}', $uid);
                $tpl->set('{aid}', $aid);
                $tpl->set('{name}', Gramatic::gramatikName($row_owner['user_name']));
                $tpl->set('{album-name}', stripslashes($row_album['name']));
                $tpl->set('[comments]', '');
                $tpl->set('[/comments]', '');
                $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
                $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
                $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
                $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                if($uid AND !$aid){
                    $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
                } else {
                    $tpl->set('[albums-comments]', '');
                    $tpl->set('[/albums-comments]', '');
                    $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                }
                if($uid == $user_id){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                } else {
                    $tpl->set('[not-owner]', '');
                    $tpl->set('[/not-owner]', '');
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                }
                $tpl->compile('info');

                //Если есть ответ о запросе то выводим
                if($sql_){

                    $tpl->load_template('/albums/albums_comment.tpl');
                    foreach($sql_ as $row_comm){
                        $tpl->set('{comment}', stripslashes($row_comm['text']));
                        $tpl->set('{uid}', $row_comm['user_id']);
                        $tpl->set('{id}', $row_comm['id']);
                        $tpl->set('{hash}', $row_comm['hash']);
                        $tpl->set('{author}', $row_comm['user_search_pref']);

                        $config = Settings::loadsettings();

                        //Выводим данные о фотографии
                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$uid.'/albums/'.$row_comm['album_id'].'/c_'.$row_comm['photo_name']);
                        $tpl->set('{pid}', $row_comm['pid']);
                        $tpl->set('{user-id}', $row_comm['owner_id']);

                        if($aid){
                            $tpl->set('{aid}', '_'.$aid);
                            $tpl->set('{section}', 'album_comments');
                        } else {
                            $tpl->set('{aid}', '');
                            $tpl->set('{section}', 'all_comments');
                        }

                        if($row_comm['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comm['user_id'].'/50_'.$row_comm['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        $online = Online($row_comm['user_last_visit'], $row_comm['user_logged_mobile']);
                        $tpl->set('{online}', $online);

                        $date = megaDate(strtotime($row_comm['date']));
                        $tpl->set('{date}', $date);

                        if($row_comm['user_id'] == $user_info['user_id'] OR $user_info['user_id'] == $uid){
                            $tpl->set('[owner]', '');
                            $tpl->set('[/owner]', '');
                        } else
                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                        $tpl->compile('content');
                    }

                    if($uid AND !$aid)
                        if($user_id == $uid)
                            $row_album = $db->super_query("SELECT SUM(comm_num) AS all_comm_num FROM `albums` WHERE user_id = '{$uid}'", false, "user_{$uid}/albums_{$uid}_comm{$cache_cnt_num}");
                        else
                            $row_album = $db->super_query("SELECT COUNT(*) AS all_comm_num FROM `photos_comments` tb1, `albums` tb3 WHERE tb1.owner_id = '{$uid}' {$privacy_sql}", false, "user_{$uid}/albums_{$uid}_comm{$cache_cnt_num}");
                    else
                        $row_album = $db->super_query("SELECT comm_num AS all_comm_num FROM `albums` WHERE aid = '{$aid}'");

                    if($uid AND !$aid)
                        $tpl = Tools::navigation($gcount, $row_album['all_comm_num'], $config['home_url'].'albums/comments/'.$uid.'/page/', tpl);
                    else
                        $tpl = navigation($gcount, $row_album['all_comm_num'], $config['home_url'].'albums/view/'.$aid.'/comments/page/', $tpl);

                    $titles = array('комментарий', 'комментария', 'комментариев');
                    $user_speedbar = $row_album['all_comm_num'].' '.Gramatic::declOfNum($row_album['all_comm_num'], $titles);

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                } else
                    msgbox('', $lang['no_comments'], 'info_2');

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
            } else {
                $user_speedbar = $lang['title_albums'];
                msgbox('', $lang['no_notes'], 'info');

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            }
        }
    }

    /**
     * Страница изминения порядка фотографий
     */
    public function edit_pos_photos($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

            //include __DIR__.'/../lang/'.$checkLang.'/site.lng';
            $user_id = $user_info['user_id'];
            //$aid = intval($_GET['aid']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = intval($path['3']);

            $check_album = $db->super_query("SELECT name FROM `albums` WHERE aid = '{$aid}' AND user_id = '{$user_id}'");

            if($check_album){
                $sql_ = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$aid}' AND user_id = '{$user_id}' ORDER by `position` ASC", 1);

                //$params['title'] = $lang['editphotos'];
                $params['title'] = $lang['editphotos'].' | Sura';
                $user_speedbar = $lang['editphotos'];

                $tpl->load_template('/albums/albums_top.tpl');
                $tpl->set('{user-id}', $user_id);
                $tpl->set('{aid}', $aid);
                $tpl->set('{album-name}', stripslashes($check_album['name']));
                $tpl->set('[editphotos]', '');
                $tpl->set('[/editphotos]', '');
                $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
                $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
                $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");

                $config = Settings::loadsettings();

                if($config['photos_drag'] == 'no')
                    $tpl->set_block("'\\[admin-drag\\](.*?)\\[/admin-drag\\]'si","");
                else {
                    $tpl->set('[admin-drag]', '');
                    $tpl->set('[/admin-drag]', '');
                }

                $tpl->compile('info');

                if($sql_){
                    //Добавляем ID для Drag-N-Drop jQuery
                    $tpl->result['content'] .= '<div id="dragndrop"><ul>';
                    $tpl->load_template('/albums/albums_editphotos.tpl');
                    foreach($sql_ as $row){
                        $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$user_id.'/albums/'.$aid.'/c_'.$row['photo_name']);
                        $tpl->set('{id}', $row['id']);
                        $tpl->compile('content');
                    }
                    //Конец ID для Drag-N-Drop jQuery
                    $tpl->result['content'] .= '</div></ul>';

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                } else{
                    msgbox('', $lang['no_photos'], 'info_2');

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                }

            } else {
                //$params['title'] = $lang['hacking'];
                $params['title'] = $lang['hacking'].' | Sura';
                $user_speedbar = $lang['no_infooo'];
                msgbox('', $lang['hacking'], 'info_2');

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            }
        }
    }

    /**
     * Просмотр альбома
     */
    public function view($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            //$act = $_GET['act'];

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $aid = intval($path['3']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
            $page = intval($path['5']);
            //var_dump($aid);
            $mobile_speedbar = 'Просмотр альбома';

            $user_id = $user_info['user_id'];
            //            $aid = intval($_GET['aid']);



            if($page > 0) $page = intval($page); else $page = 1;
            $gcount = 25;
            $limit_page = ($page-1) * $gcount;

            //Выводим данные о фотках
            $sql_photos = $db->super_query("SELECT id, photo_name FROM `photos` WHERE album_id = '{$aid}' ORDER by `position` ASC LIMIT {$limit_page}, {$gcount}", 1);

            //Выводим данные о альбоме
            $row_album = $db->super_query("SELECT user_id, name, photo_num, privacy FROM `albums` WHERE aid = '{$aid}'");

            //ЧС
            $CheckBlackList = Tools::CheckBlackList($row_album['user_id']);
            if(!$CheckBlackList){
                $album_privacy = explode('|', $row_album['privacy']);
                if(!$row_album)
                    Hacking();

                //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                if($user_id != $row_album['user_id'])
                    $check_friend = Tools::CheckFriends($row_album['user_id']);

                //Приватность
                if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $row_album['user_id']){
                    //Выводим данные о владельце альбома(ов)
                    $row_owner = $db->super_query("SELECT user_name FROM `users` WHERE user_id = '{$row_album['user_id']}'");

                    $tpl->load_template('albums/albums_top.tpl');
                    $tpl->set('{user-id}', $row_album['user_id']);
                    $tpl->set('{name}', Gramatic::gramatikName($row_owner['user_name']));
                    $tpl->set('{aid}', $aid);
                    $tpl->set('[view]', '');
                    $tpl->set('[/view]', '');
                    $tpl->set_block("'\\[all-albums\\](.*?)\\[/all-albums\\]'si","");
                    $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                    $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
                    $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
                    $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");
                    if($row_album['user_id'] == $user_id){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                    } else {
                        $tpl->set('[not-owner]', '');
                        $tpl->set('[/not-owner]', '');
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }
                    $tpl->set('{album-name}', stripslashes($row_album['name']));
                    $tpl->set('{all_p_num}', $row_album['photo_num']);
                    $tpl->set('{aid}', $aid);
                    $tpl->set('{count}', $limit_page);
                    $tpl->compile('info');

                    //Мета теги и формирование спидбара
                    $titles = array('фотография', 'фотографии', 'фотографий');
                    $params['title'] = stripslashes($row_album['name']).' | '.$row_album['photo_num'].' '.Gramatic::declOfNum($row_album['photo_num'], $titles).' | Sura';
                    $user_speedbar = '<span id="photo_num">'.$row_album['photo_num'].'</span> '.Gramatic::declOfNum($row_album['photo_num'], $titles);

                    if($sql_photos){
                        $tpl->load_template('albums/album_photo.tpl');

                        $config = Settings::loadsettings();
                        foreach($sql_photos as $row){
                            $tpl->set('{photo}', $config['home_url'].'uploads/users/'.$row_album['user_id'].'/albums/'.$aid.'/c_'.$row['photo_name']);
                            $tpl->set('{id}', $row['id']);
                            $tpl->set('{all}', '');
                            $tpl->set('{uid}', $row_album['user_id']);
                            $tpl->set('{aid}', '_'.$aid);
                            $tpl->set('{section}', '');
                            if($row_album['user_id'] == $user_id){
                                $tpl->set('[owner]', '');
                                $tpl->set('[/owner]', '');
                                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                            } else {
                                $tpl->set('[not-owner]', '');
                                $tpl->set('[/not-owner]', '');
                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                            }
                            $tpl->compile('content');
                        }
                        $tpl = Tools::navigation($gcount, $row_album['photo_num'], $config['home_url'].'albums/view/'.$aid.'/page/', $tpl);
                    } else
                        msgbox('', '<br /><br />В альбоме нет фотографий<br /><br /><br />', 'info_2');

                    //Проверяем на наличии файла с позициям фоток
                    $check_pos = Cache::mozg_cache('user_'.$row_album['user_id'].'/position_photos_album_'.$aid);

                    //Если нету, то вызываем функцию генерации
                    if(!$check_pos)
                        GenerateAlbumPhotosPosition($row_album['user_id'], $aid);
                } else {
                    $user_speedbar = $lang['error'];
                    msgbox('', $lang['no_notes'], 'info');
                }

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;

            } else {
                $user_speedbar = $lang['title_albums'];
                msgbox('', $lang['no_notes'], 'info');

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            }
        }
    }

    /**
     * Страница с новыми фотографиями
     */
    public function new_photos($params){
        $tpl = Registry::get('tpl');
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $lang = $this->get_langs();
        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];

        }
    }

    /**
     *  Просмотр всех альбомов юзера
     */
    public function index()
    {

        $tpl = Registry::get('tpl');

        $db = $this->db();
        $user_info = $this->user_info();
        $lang = $this->get_langs();
        $logged = $this->logged();

//        $lang = $this->get_langs();

        $ajax = (isset($_POST['ajax'])) ? 'yes' : 'no';
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $act = $_GET['act'];

        $mobile_speedbar = 'Альбомы';
        $uid = intval($_GET['uid']);

            $path = explode('/', $_SERVER['REQUEST_URI']);
//            $id = str_replace('u', '', $path);
            $uid = intval($path['2']);

        //Выводим данные о владельце альбома(ов)
        $row_owner = $db->super_query("SELECT user_search_pref, user_albums_num, user_new_mark_photos FROM `users` WHERE user_id = '{$uid}'");

        if($row_owner){
            //ЧС
            $CheckBlackList = Tools::CheckBlackList($uid);
            if(!$CheckBlackList){
                $author_info = explode(' ', $row_owner['user_search_pref']);

                $params['title'] = $lang['title_albums'].' '.Gramatic::gramatikName($author_info[0]).' '.Gramatic::gramatikName($author_info[1]).' | Sura';
                $user_speedbar = $lang['title_albums'];

                //Выводи данные о альбоме
                $sql_ = $db->super_query("SELECT aid, name, adate, photo_num, descr, comm_num, cover, ahash, privacy FROM `albums` WHERE user_id = '{$uid}' ORDER by `position` ASC", 1);

                //Если есть альбомы то выводи их
                if($sql_){
                    $m_cnt = $row_owner['user_albums_num'];

                    $tpl->load_template('/albums/album.tpl');

                    //Добавляем ID для DragNDrop jQuery
                    $tpl->result['content'] .= '<div id="dragndrop"><ul>';

                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                    if($user_info['user_id'] != $uid)
                        $check_friend = Tools::CheckFriends($uid);

                    foreach($sql_ as $row){

                        //Приватность
                        $album_privacy = explode('|', $row['privacy']);
                        if($album_privacy[0] == 1 OR $album_privacy[0] == 2 AND $check_friend OR $user_info['user_id'] == $uid){
                            if($user_info['user_id'] == $uid){
                                $tpl->set('[owner]', '');
                                $tpl->set('[/owner]', '');
                                $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                            } else {
                                $tpl->set('[not-owner]', '');
                                $tpl->set('[/not-owner]', '');
                                $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                            }

                            $tpl->set('{name}', stripslashes($row['name']));
                            if($row['descr'])
                                $tpl->set('{descr}', '<div style="padding-top:4px;">'.stripslashes($row['descr']).'</div>');
                            else
                                $tpl->set('{descr}', '');

                            $titles = array('фотография', 'фотографии', 'фотографий');
                            $tpl->set('{photo-num}', $row['photo_num'].' '.Gramatic::declOfNum($row['photo_num'], $titles));

                            $titles = array('комментарий', 'комментария', 'комментариев');
                            $tpl->set('{comm-num}', $row['comm_num'].' '.Gramatic::declOfNum($row['comm_num'], $titles));

                            $date = megaDate(strtotime($row['adate']), 1, 1);
                            $tpl->set('{date}', $date);

                            $config = Settings::loadsettings();

                            if($row['cover'])
                                $tpl->set('{cover}', $config['home_url'].'uploads/users/'.$uid.'/albums/'.$row['aid'].'/c_'.$row['cover']);
                            else
                                $tpl->set('{cover}', '/images/no_cover.png');

                            $tpl->set('{aid}', $row['aid']);
                            $tpl->set('{hash}', $row['ahash']);

                            $tpl->compile('content');
                        } else
                            $m_cnt--;
                    }

                    //Конец ID для DragNDrop jQuery
                    $tpl->result['content'] .= '</div></ul>';

                    $row_owner['user_albums_num'] = $m_cnt;

                    if($row_owner['user_albums_num']){
                        $titles = array('альбом', 'альбома', 'альбомов');
                        if($user_info['user_id'] == $uid){
                            $user_speedbar = 'У Вас <span id="albums_num">'.$row_owner['user_albums_num'].'</span> '.Gramatic::declOfNum($row_owner['user_albums_num'], $titles);
                        } else {
                            $user_speedbar = 'У '.Gramatic::gramatikName($author_info[0]).' '.$row_owner['user_albums_num'].' '.Gramatic::declOfNum($row_owner['user_albums_num'], $titles);
                        }

                        $tpl->load_template('/albums/albums_top.tpl');
                        $tpl->set('{user-id}', $uid);
                        $tpl->set('{name}', Gramatic::gramatikName($author_info[0]));
                        $tpl->set('[all-albums]', '');
                        $tpl->set('[/all-albums]', '');
                        $tpl->set_block("'\\[view\\](.*?)\\[/view\\]'si","");
                        $tpl->set_block("'\\[comments\\](.*?)\\[/comments\\]'si","");
                        $tpl->set_block("'\\[editphotos\\](.*?)\\[/editphotos\\]'si","");
                        $tpl->set_block("'\\[albums-comments\\](.*?)\\[/albums-comments\\]'si","");
                        $tpl->set_block("'\\[all-photos\\](.*?)\\[/all-photos\\]'si","");

                        //Показ скрытых тексто только для владельца страницы
                        if($user_info['user_id'] == $uid){
                            $tpl->set('[owner]', '');
                            $tpl->set('[/owner]', '');
                            $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                        } else {
                            $tpl->set('[not-owner]', '');
                            $tpl->set('[/not-owner]', '');
                            $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                        }

                        $config = Settings::loadsettings();

                        if($config['albums_drag'] == 'no')
                            $tpl->set_block("'\\[admin-drag\\](.*?)\\[/admin-drag\\]'si","");
                        else {
                            $tpl->set('[admin-drag]', '');
                            $tpl->set('[/admin-drag]', '');
                        }

                        if($row_owner['user_new_mark_photos'] AND $user_info['user_id'] == $uid){
                            $tpl->set('[new-photos]', '');
                            $tpl->set('[/new-photos]', '');
                            $tpl->set('{num}', $row_owner['user_new_mark_photos']);
                        } else
                            $tpl->set_block("'\\[new-photos\\](.*?)\\[/new-photos\\]'si","");

                        $tpl->compile('info');
                    } else
                        msgbox('', $lang['no_albums'], 'info_2');
                } else {
                    $tpl->load_template('/albums/albums_info.tpl');
                    //Показ скрытых тексто только для владельца страницы
                    if($user_info['user_id'] == $uid){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                    } else {
                        $tpl->set('[not-owner]', '');
                        $tpl->set('[/not-owner]', '');
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                    }
                    $tpl->compile('content');
                }
            } else {
                $user_speedbar = $lang['error'];
                msgbox('', $lang['no_notes'], 'info');
            }
        } else
            Hacking();
//            }
            $tpl->clear();
            $db->free($sql_);
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}
