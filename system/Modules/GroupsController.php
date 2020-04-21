<?php
/* 
	Appointment: Группы
	File: groups.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use Intervention\Image\ImageManager;
use System\Classes\Public_wall;
use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Gramatic;
use System\Libs\Validation;

class GroupsController extends Module{

    /**
     * Отправка сообщества БД
     */
    public function send($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $title = Validation::ajax_utf8(Validation::textFilter($_POST['title'], false, true));
            AntiSpam('groups');

            if(isset($title) AND !empty($title)){

                AntiSpamLogInsert('groups');
                $db->query("INSERT INTO `".PREFIX."_communities` SET title = '{$title}', type = 1, traf = 1, ulist = '|{$user_id}|', date = NOW(), admin = 'u{$user_id}|', real_admin = '{$user_id}', comments = 1");
                $cid = $db->insert_id();
                $db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$cid}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");
                $db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num+1 WHERE user_id = '{$user_id}'");

                @mkdir(__DIR__.'/../../public/uploads/groups/'.$cid.'/', 0777);
                @chmod(__DIR__.'/../../public/uploads/groups/'.$cid.'/', 0777);

                @mkdir(__DIR__.'/../../public/uploads/groups/'.$cid.'/photos/', 0777);
                @chmod(__DIR__.'/../../public/uploads/groups/'.$cid.'/photos/', 0777);

                Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");

                echo $cid;
            } else
                echo 'no_title';

            die();
        }
    }

    /**
     *  Выход из сообщества
     */
    public function exit($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
            if($check['cnt']){
                $db->query("DELETE FROM `".PREFIX."_friends` WHERE friend_id = '{$id}' AND user_id = '{$user_id}' AND subscriptions = 2");
                $db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num-1 WHERE user_id = '{$user_id}'");
                $db->query("UPDATE `".PREFIX."_communities` SET traf = traf-1, ulist = REPLACE(ulist, '|{$user_id}|', '') WHERE id = '{$id}'");

                //Записываем в статистику "Вышедшие участники"
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats` WHERE gid = '{$id}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats_log` WHERE gid = '{$id}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '3'");

                if(!$check_user_stat['cnt']){

                    if($check_stat['cnt']){

                        $db->query("UPDATE `".PREFIX."_communities_stats` SET exit_users = exit_users + 1 WHERE gid = '{$id}' AND date = '{$stat_date}'");

                    } else {

                        $db->query("INSERT INTO `".PREFIX."_communities_stats` SET gid = '{$id}', date = '{$stat_date}', exit_users = '1', date_x = '{$stat_x_date}'");

                    }

                    $db->query("INSERT INTO `".PREFIX."_communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', act = '3', gid = '{$id}'");

                }

                Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
            }
            die();
        }
    }

    /**
     * Страница загрузки главного фото сообщества
     */
    public function loadphoto_page($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $tpl->load_template('groups/load_photo.tpl');
            $tpl->set('{id}', $_POST['id']);
            $tpl->compile('content');
            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Загрузка и изминение главного фото сообщества
     */
    public function loadphoto($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $id = intval($_GET['id']);

            //Проверка на то, что фото обновляет адмиH
            $row = $db->super_query("SELECT admin, photo, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND $row['del'] == 0 AND $row['ban'] == 0){

                //Разришенные форматы
                $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

                //Получаем данные о фотографии
                $image_tmp = $_FILES['uploadfile']['tmp_name'];
                $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
                $image_size = $_FILES['uploadfile']['size']; // размер файла
                $type = end(explode(".", $image_name)); // формат файла

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
                            $db->query("UPDATE `".PREFIX."_communities` SET photo = '{$image_rename}{$res_type}' WHERE id = '{$id}'");

                            //Результат для ответа
                            echo $image_rename.$res_type;

                            Cache::mozg_clear_cache_folder('groups');
                            Cache::mozg_clear_cache_file("wall/group{$id}");

                        } else
                            echo 'big_size';
                    } else
                        echo 'big_size';
                } else
                    echo 'bad_format';
            }
            die();
        }
    }

    /**
     * Удаление фото сообщества
     */
    public function delphoto($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);

            //Проверка на то, что фото удалет админ
            $row = $db->super_query("SELECT photo, admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false){
                $upload_dir = __DIR__."/../../public/uploads/groups/{$id}/";
                @unlink($upload_dir.$row['photo']);
                @unlink($upload_dir.'50_'.$row['photo']);
                @unlink($upload_dir.'100_'.$row['photo']);
                $db->query("UPDATE `".PREFIX."_communities` SET photo = '' WHERE id = '{$id}'");

                Cache::mozg_clear_cache_folder('groups');
                Cache::mozg_clear_cache_file("wall/group{$id}");

            }
            die();
        }
    }

    /**
     * Вступление в сообщество
     */
    public function login($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);

            //Проверка на существования юзера в сообществе
            $row = $db->super_query("SELECT ulist, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");

            if(stripos($row['ulist'], "|{$user_id}|") === false AND $row['del'] == 0 AND $row['ban'] == 0){

                $ulist = $row['ulist']."|{$user_id}|";

                //Обновляем кол-во людей в сообществе
                $db->query("UPDATE `".PREFIX."_communities` SET traf = traf+1, ulist = '{$ulist}' WHERE id = '{$id}'");

                //Подписываемся
                $db->query("INSERT INTO `".PREFIX."_friends` SET friend_id = '{$id}', user_id = '{$user_id}', friends_date = NOW(), subscriptions = 2");

                //Записываем в статистику "Новые участники"
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $stat_date = date('Y-m-d', $server_time);
                $stat_x_date = date('Y-m', $server_time);
                $stat_date = strtotime($stat_date);
                $stat_x_date = strtotime($stat_x_date);

                $check_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats` WHERE gid = '{$id}' AND date = '{$stat_date}'");
                $check_user_stat = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_stats_log` WHERE gid = '{$id}' AND user_id = '{$user_info['user_id']}' AND date = '{$stat_date}' AND act = '2'");

                if(!$check_user_stat['cnt']){

                    if($check_stat['cnt']){

                        $db->query("UPDATE `".PREFIX."_communities_stats` SET new_users = new_users + 1 WHERE gid = '{$id}' AND date = '{$stat_date}'");

                    } else {

                        $db->query("INSERT INTO `".PREFIX."_communities_stats` SET gid = '{$id}', date = '{$stat_date}', new_users = '1', date_x = '{$stat_x_date}'");

                    }

                    $db->query("INSERT INTO `".PREFIX."_communities_stats_log` SET user_id = '{$user_info['user_id']}', date = '{$stat_date}', act = '2', gid = '{$id}'");

                }

                //Проверка на приглашению юзеру
                $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

                //Если есть приглашение, то удаляем его
                if($check['cnt']){

                    $db->query("DELETE FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");
                    $appSQLDel = ", invties_pub_num = invties_pub_num - 1";

                }

                //Обновляем кол-во сообществ у юзера
                $db->query("UPDATE `".PREFIX."_users` SET user_public_num = user_public_num + 1 {$appSQLDel} WHERE user_id = '{$user_id}'");

                //Чистим кеш
                Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|groups/{$user_id}");
            }
            die();
        }
    }

    /**
     * Страница добавления контактов
     */
    public function addfeedback_pg($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $tpl->load_template('groups/addfeedback_pg.tpl');
            $tpl->set('{id}', $_POST['id']);
            $tpl->compile('content');
            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Добавления контакт в БД
     */
    public function addfeedback_db($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $upage = intval($_POST['upage']);
            $office = Validation::ajax_utf8(Validation::textFilter($_POST['office'], false, true));
            $phone = Validation::ajax_utf8(Validation::textFilter($_POST['phone'], false, true));
            $email = Validation::ajax_utf8(Validation::textFilter($_POST['email'], false, true));

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");

            //Проверяем что такой юзер есть на сайте
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$upage}'");

            //Проверяем на то что юзера нет в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

            if($row['cnt'] AND stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND !$checkSec['cnt']){
                $db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback+1 WHERE id = '{$id}'");
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_communities_feedback` SET cid = '{$id}', fuser_id = '{$upage}', office = '{$office}', fphone = '{$phone}', femail = '{$email}', fdate = '{$server_time}'");
            } else
                echo 1;

            die();
        }
    }

    /**
     * Удаление контакта из БД
     */
    public function delfeedback($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $uid = intval($_POST['uid']);

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");

            //Проверяем на то что юзера есть в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
                $db->query("UPDATE `".PREFIX."_communities` SET feedback = feedback-1 WHERE id = '{$id}'");
                $db->query("DELETE FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$uid}' AND cid = '{$id}'");
            }

            die();
        }
    }

    /**
     * Выводим фотографию юзера при указании ИД страницы
     */
    public function checkFeedUser($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $row = $db->super_query("SELECT user_photo, user_search_pref FROM `".PREFIX."_users` WHERE user_id = '{$id}'");
            if($row) echo $row['user_search_pref']."|".$row['user_photo'];
            die();
        }
    }

    /**
     * Сохранение отредактированых данных контакт в БД
     */
    public function editfeeddave($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $upage = intval($_POST['uid']);
            $office = Validation::ajax_utf8(Validation::textFilter($_POST['office'], false, true));
            $phone = Validation::ajax_utf8(Validation::textFilter($_POST['phone'], false, true));
            $email = Validation::ajax_utf8(Validation::textFilter($_POST['email'], false, true));

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");

            //Проверяем на то что юзера есть в списке контактов
            $checkSec = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_feedback` WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND $checkSec['cnt']){
                $db->query("UPDATE `".PREFIX."_communities_feedback` SET office = '{$office}', fphone = '{$phone}', femail = '{$email}' WHERE fuser_id = '{$upage}' AND cid = '{$id}'");

                Cache::mozg_clear_cache_file("wall/group{$id}");

            } else
                echo 1;

            die();
        }
    }

    /**
     * Все контакты (БОКС)
     */
    public function allfeedbacklist($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);

            //Выводим ИД админа
            $owner = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");

            $sql_ = $db->super_query("SELECT tb1.fuser_id, office, fphone, femail, tb2.user_search_pref, user_photo FROM `".PREFIX."_communities_feedback` tb1, `".PREFIX."_users` tb2 WHERE tb1.cid = '{$id}' AND tb1.fuser_id = tb2.user_id ORDER by `fdate` ASC", 1);
            $tpl->load_template('groups/allfeedbacklist.tpl');
            if($sql_){
                foreach($sql_ as $row){
                    $tpl->set('{id}', $id);
                    $tpl->set('{name}', $row['user_search_pref']);
                    $tpl->set('{office}', stripslashes($row['office']));
                    $tpl->set('{phone}', stripslashes($row['fphone']));
                    $tpl->set('{user-id}', $row['fuser_id']);
                    if($row['fphone'] AND $row['femail']) $tpl->set('{email}', ', '.stripslashes($row['femail']));
                    else $tpl->set('{email}', stripslashes($row['femail']));
                    if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['fuser_id'].'/50_'.$row['user_photo']);
                    else $tpl->set('{ava}', '/images/no_ava_50.png');
                    if(stripos($owner['admin'], "u{$user_id}|") !== false){
                        $tpl->set('[admin]', '');
                        $tpl->set('[/admin]', '');
                    } else
                        $tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");
                    $tpl->compile('content');
                }
                Tools::AjaxTpl();
            } else
                echo '<div align="center" style="padding-top:10px;color:#777;font-size:13px;">Список контактов пуст.</div>';

            if(stripos($owner['admin'], "u{$user_id}|") !== false)
                echo "<style>#box_bottom_left_text{padding-top:6px;float:left}</style><script>$('#box_bottom_left_text').html('<a href=\"/\" onClick=\"groups.addcontact({$id}); return false\">Добавить контакт</a>');</script>";

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Сохранение отредактированых данных группы
     */
    public function saveinfo($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $comments = intval($_POST['comments']);
            $discussion = intval($_POST['discussion']);
            $title = Validation::ajax_utf8(Validation::textFilter($_POST['title'], false, true));
            $adres_page = Validation::ajax_utf8(strtolower(Validation::textFilter($_POST['adres_page'], false, true)));
            $descr = Validation::ajax_utf8(Validation::textFilter($_POST['descr'], 5000));

            $_POST['web'] = str_replace(array('"', "'"), '', $_POST['web']);
            $web = Validation::ajax_utf8(Validation::textFilter($_POST['web'], false, true));

            if(!preg_match("/^[a-zA-Z0-9_-]+$/", $adres_page)) $adress_ok = false;
            else $adress_ok = true;

            //Проверка на то, что действиие делает админ
            $checkAdmin = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '".$id."'");

            if(stripos($checkAdmin['admin'], "u{$user_id}|") !== false AND isset($title) AND !empty($title) AND $adress_ok){
                if(preg_match('/public[0-9]/i', $adres_page))
                    $adres_page = '';

                $adres_page = preg_replace('/\b(u([0-9]+)|friends|editmypage|albums|photo([0-9]+)_([0-9]+)|photo([0-9]+)_([0-9]+)_([0-9]+)|fave|notes|videos|video([0-9]+)_([0-9]+)|news|messages|wall([0-9]+)|settings|support|restore|blog|balance|nonsense|reg([0-9]+)|gifts([0-9]+)|groups|wallgroups([0-9]+)_([0-9]+)|audio|audio([0-9]+)|docs|apps|app([0-9]+)|public|forum([0-9]+)|public([0-9]+))\b/i', '', $adres_page);

                //Проверка на то, что адрес страницы свободен
                if($adres_page)
                    $checkAdres = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE adres = '".$adres_page."' AND id != '".$id."'");

                if(!$checkAdres['cnt'] OR $adres_page == ''){
                    $db->query("UPDATE `".PREFIX."_communities` SET title = '".$title."', descr = '".$descr."', comments = '".$comments."', discussion = '{$discussion}', adres = '".$adres_page."', web = '{$web}' WHERE id = '".$id."'");
                    if(!$adres_page)
                        echo 'no_new';
                } else
                    echo 'err_adres';

                Cache::mozg_clear_cache_folder('groups');
                Cache::mozg_clear_cache_file("wall/group{$id}");
            }

            die();
        }
    }

    /**
     * Выводим информацию о пользователе которого будем делать админом
     */
    public function new_admin($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $new_admin_id = intval($_POST['new_admin_id']);
            $row = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref, user_sex FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$new_admin_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2");
            if($row AND $user_id != $new_admin_id){
                $config = include __DIR__.'/../data/config.php';

                if($row['user_photo']) $ava = "/uploads/users/{$new_admin_id}/100_{$row['user_photo']}";
                else $ava = "/templates/{$config['temp']}/images/100_no_ava.png";
                if($row['user_sex'] == 1) $gram = 'был';
                else $gram = 'была';
                echo "<div style=\"padding:15px\"><img src=\"{$ava}\" align=\"left\" style=\"margin-right:10px\" id=\"adm_ava\" />Вы хотите чтоб <b id=\"adm_name\">{$row['user_search_pref']}</b> {$gram} одним из руководителей страницы?</div>";
            } else
                echo "<div style=\"padding:15px\"><div class=\"err_red\">Пользователь с таким адресом страницы не подписан на эту страницу.</div></div><script>$('#box_but').hide()</script>";

            die();
        }
    }

    /**
     * Запись нового админа в БД
     */
    public function send_new_admin($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $new_admin_id = intval($_POST['new_admin_id']);
            $row = $db->super_query("SELECT admin, ulist FROM `".PREFIX."_communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$new_admin_id}|") === false AND stripos($row['ulist'], "|{$user_id}|") !== false){
                $admin = $row['admin']."u{$new_admin_id}|";
                $db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
            }
            die();
        }
    }

    /**
     * Удаление админа из БД
     */
    public function deladmin($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $uid = intval($_POST['uid']);
            $row = $db->super_query("SELECT admin, ulist, real_admin FROM `".PREFIX."_communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") !== false AND stripos($row['admin'], "u{$uid}|") !== false AND $uid != $row['real_admin']){
                $admin = str_replace("u{$uid}|", '', $row['admin']);
                $db->query("UPDATE `".PREFIX."_communities` SET admin = '{$admin}' WHERE id = '{$id}'");
            }
            die();
        }
    }

    /**
     * Добавление записи на стену
     */
    public function wall_send($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            //Если страница вывзана через "к предыдущим записям"
            $limit_select = 10;
            if($_POST['page_cnt'] > 0)
                $page_cnt = intval($_POST['page_cnt'])*$limit_select;
            else
                $page_cnt = 0;

            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $wall_text = Validation::ajax_utf8(Validation::textFilter($_POST['wall_text']));
            $attach_files = Validation::ajax_utf8(Validation::textFilter($_POST['attach_files'], false, true));

            //Проверка на админа
            $row = $db->super_query("SELECT admin, del, ban FROM `".PREFIX."_communities` WHERE id = '{$id}'");
            if(stripos($row['admin'], "u{$user_id}|") === false)
                die();

            if(isset($wall_text) AND !empty($wall_text) OR isset($attach_files) AND !empty($attach_files) AND $row['del'] == 0 AND $row['ban'] == 0){

                //Оприделение изображения к ссылке
                if(stripos($attach_files, 'link|') !== false){
                    $attach_arr = explode('||', $attach_files);
                    $cnt_attach_link = 1;
                    foreach($attach_arr as $attach_file){
                        $attach_type = explode('|', $attach_file);
                        if($attach_type[0] == 'link' AND preg_match('/https:\/\/(.*?)+$/i', $attach_type[1]) AND $cnt_attach_link == 1){
                            $domain_url_name = explode('/', $attach_type[1]);
                            //$rdomain_url_name = str_replace('https://', '', $domain_url_name[2]);
                            $rImgUrl = $attach_type[4];
                            $rImgUrl = str_replace("\\", "/", $rImgUrl);
                            $img_name_arr = explode(".", $rImgUrl);
                            $img_format = Gramatic::totranslit(end($img_name_arr));
                            $server_time = intval($_SERVER['REQUEST_TIME']);
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
                                    mkdir($upload_dir, 0777);
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

                $attach_files = str_replace('vote|', 'hack|', $attach_files);
                $attach_files = str_replace(array('&amp;#124;', '&amp;raquo;', '&amp;quot;'), array('&#124;', '&raquo;', '&quot;'), $attach_files);

                //Голосование
                $vote_title = Validation::ajax_utf8(Validation::textFilter($_POST['vote_title'], false, true));
                $vote_answer_1 = Validation::ajax_utf8(Validation::textFilter($_POST['vote_answer_1'], false, true));

                $ansers_list = array();

                if(isset($vote_title) AND !empty($vote_title) AND isset($vote_answer_1) AND !empty($vote_answer_1)){

                    for($vote_i = 1; $vote_i <= 10; $vote_i++){

                        $vote_answer = Validation::ajax_utf8(Validation::textFilter($_POST['vote_answer_'.$vote_i], false, true));
                        $vote_answer = str_replace('|', '&#124;', $vote_answer);

                        if($vote_answer)
                            $ansers_list[] = $vote_answer;

                    }

                    $sql_answers_list = implode('|', $ansers_list);

                    //Вставляем голосование в БД
                    $db->query("INSERT INTO `".PREFIX."_votes` SET title = '{$vote_title}', answers = '{$sql_answers_list}'");

                    $attach_files = $attach_files."vote|{$db->insert_id()}||";

                }

                //Вставляем саму запись в БД
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$id}', text = '{$wall_text}', attach = '{$attach_files}', add_date = '{$server_time}'");
                $dbid = $db->insert_id();
                $db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num+1 WHERE id = '{$id}'");

                //Вставляем в ленту новотсей
                $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$id}', action_type = 11, action_text = '{$wall_text}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                //Загружаем все записи
                if(stripos($row['admin'], "u{$user_id}|") !== false)
                    $public_admin = true;
                else
                    $public_admin = false;

                $limit_select = 10;
                //$pid = $id;

                $query = $db->super_query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, fixed, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = '{$row['id']}' AND tb1.public_id = tb2.id AND fast_comm_id = 0 ORDER by `fixed` DESC, `add_date` DESC LIMIT {$page_cnt}, {$limit_select}", 1);
                $tpl->load_template('groups/record.tpl');
                $compile = 'content';

                foreach($query as $row_wall){
                    $tpl->set('{rec-id}', $row_wall['id']);

                    //Закрепить запись
                    if($row_wall['fixed']){

                        $tpl->set('{styles-fasten}', 'style="opacity:1"');
                        $tpl->set('{fasten-text}', 'Закрепленная запись');
                        $tpl->set('{function-fasten}', 'wall_unfasten');

                    } else {

                        $tpl->set('{styles-fasten}', '');
                        $tpl->set('{fasten-text}', 'Закрепить запись');
                        $tpl->set('{function-fasten}', 'wall_fasten');

                    }

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
                        //$jid = 0;
                        $attach_result = '';
                        $attach_result .= '<div class="clear"></div>';
                        foreach($attach_arr as $attach_file){
                            $attach_type = explode('|', $attach_file);

                            //Фото со стены сообщества
                            if($row_wall['tell_uid'])
                                $globParId = $row_wall['tell_uid'];
                            else
                                $globParId = $row_wall['public_id'];

                            if($attach_type[0] == 'photo' AND file_exists(__DIR__."/../../public/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}")){
                                if($cnt_attach < 2)
                                    $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/{$attach_type[1]}\" align=\"left\" /></div>";
                                else
                                    $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/groups/{$globParId}/photos/c_{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$globParId}', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

                                $cnt_attach++;

                                $resLinkTitle = '';

                                //Фото со стены юзера
                            } elseif($attach_type[0] == 'photo_u'){
                                $attauthor_user_id = $row_wall['tell_uid'];

                                if($attach_type[1] == 'attach' AND file_exists(__DIR__."/../../public/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}")){
                                    if($cnt_attach < 2)
                                        $attach_result .= "<div class=\"profile_wall_attach_photo cursor_pointer page_num{$row_wall['id']}\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '{$attauthor_user_id}', '{$attach_type[1]}', '{$cnt_attach}', 'photo_u')\"><img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/{$attach_type[2]}\" align=\"left\" /></div>";
                                    else
                                        $attach_result .= "<img id=\"photo_wall_{$row_wall['id']}_{$cnt_attach}\" src=\"/uploads/attach/{$attauthor_user_id}/c_{$attach_type[2]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" onClick=\"groups.wall_photo_view('{$row_wall['id']}', '', '{$attach_type[1]}', '{$cnt_attach}')\" class=\"cursor_pointer page_num{$row_wall['id']}\" />";

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

                                if($cnt_attach_video == 1 AND preg_match('/(photo|photo_u)/i', $row_wall['attach']) == false){

                                    $video_id = intval($attach_type[2]);

                                    $row_video = $db->super_query("SELECT video, title FROM `".PREFIX."_videos` WHERE id = '{$video_id}'", false, "wall/video{$video_id}");
                                    $row_video['title'] = stripslashes($row_video['title']);
                                    $row_video['video'] = stripslashes($row_video['video']);
                                    $row_video['video'] = strtr($row_video['video'], array('width="770"' => 'width="390"', 'height="420"' => 'height="310"'));

                                    $attach_result .= "<div class=\"cursor_pointer clear\" id=\"no_video_frame{$video_id}\" onClick=\"$('#'+this.id).hide();$('#video_frame{$video_id}').show();\">
							        <div class=\"video_inline_icon\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px\" width=\"390\" height=\"310\" /></div><div id=\"video_frame{$video_id}\" class=\"no_display\" style=\"padding-top:3px\">{$row_video['video']}</div><div class=\"video_inline_vititle\"></div><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><b>{$row_video['title']}</b></a>";

                                } else {

                                    $attach_result .= "<div class=\"fl_l\"><a href=\"/video{$attach_type[3]}_{$attach_type[2]}\" onClick=\"videos.show({$attach_type[2]}, this.href, location.href); return false\"><div class=\"video_inline_icon video_inline_icon2\"></div><img src=\"/uploads/videos/{$attach_type[3]}/{$attach_type[1]}\" style=\"margin-top:3px;margin-right:3px\" align=\"left\" /></a></div>";

                                }

                                $resLinkTitle = '';

                                //Музыка
                            } elseif($attach_type[0] == 'audio'){
                                $data = explode('_', $attach_type[1]);
                                $audioId = intval($data[0]);
                                $row_audio = $db->super_query("SELECT id, oid, artist, title, url, duration FROM`".PREFIX."_audio` WHERE id = '{$audioId}'");
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

                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div><div class="clear"></div><div class="wall_show_block_link" style="'.$no_border_link.'"><a href="/away.php?url='.$attach_type[1].'" target="_blank"><div style="width:108px;height:80px;float:left;text-align:center"><img src="'.$attach_type[4].'" /></div></a><div class="attatch_link_title"><a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$str_title.'</a></div><div style="max-height:50px;overflow:hidden">'.$attach_type[3].'</div></div></div>';

                                    $resLinkTitle = $attach_type[2];
                                    $resLinkUrl = $attach_type[1];
                                } else if($attach_type[1] AND $attach_type[2]){
                                    $attach_result .= '<div style="margin-top:2px" class="clear"><div class="attach_link_block_ic fl_l" style="margin-top:4px;margin-left:0px"></div><div class="attach_link_block_te"><div class="fl_l">Ссылка: <a href="/away.php?url='.$attach_type[1].'" target="_blank">'.$rdomain_url_name.'</a></div></div></div><div class="clear"></div>';

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
                    $tpl->set('{name}', $row_wall['title']);

                    $tpl->set('{user-id}', $row_wall['public_id']);
                    if($row_wall['adres']) $tpl->set('{adres-id}', $row_wall['adres']);
                    else $tpl->set('{adres-id}', 'public'.$row_wall['public_id']);

                    $date = megaDate(strtotime($row_wall['add_date']));
                    $tpl->set('{date}', $date);

                    if($row_wall['photo'])
                        $tpl->set('{ava}', '/uploads/groups/'.$row_wall['public_id'].'/50_'.$row_wall['photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    //Мне нравится
                    if(stripos($row_wall['likes_users'], "u{$user_id}|") !== false){
                        $tpl->set('{yes-like}', 'public_wall_like_yes');
                        $tpl->set('{yes-like-color}', 'public_wall_like_yes_color');
                        $tpl->set('{like-js-function}', 'groups.wall_remove_like('.$row_wall['id'].', '.$user_id.')');
                    } else {
                        $tpl->set('{yes-like}', '');
                        $tpl->set('{yes-like-color}', '');
                        $tpl->set('{like-js-function}', 'groups.wall_add_like('.$row_wall['id'].', '.$user_id.')');
                    }

                    if($row_wall['likes_num']){
                        $tpl->set('{likes}', $row_wall['likes_num']);
                        $tpl->set('{likes-text}', '<span id="like_text_num'.$row_wall['id'].'">'.$row_wall['likes_num'].'</span> '.Gramatic::declOfNum($row_wall['likes_num'], 'like'));
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

                    //Админ
                    if($public_admin){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    //Если есть комменты к записи, то выполняем след. действия / Приватность
                    if($row_wall['fasts_num'])
                        $tpl->set_block("'\\[comments-link\\](.*?)\\[/comments-link\\]'si","");
                    else {
                        $tpl->set('[comments-link]', '');
                        $tpl->set('[/comments-link]', '');
                    }

                    $tpl->set('{public-id}', $row['id']);

                    //Приватность комментирования записей
                    if($row_wall['comments'] OR $public_admin){
                        $tpl->set('[privacy-comment]', '');
                        $tpl->set('[/privacy-comment]', '');
                    } else
                        $tpl->set_block("'\\[privacy-comment\\](.*?)\\[/privacy-comment\\]'si","");

                    $tpl->set('[record]', '');
                    $tpl->set('[/record]', '');
                    $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                    $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");

                    $tpl->compile($compile);

                    //Если есть комменты к записи, то открываем форму ответа уже в развернутом виде и выводим комменты к записи
                    if($row_wall['comments'] OR $public_admin){
                        if($row_wall['fasts_num']){

                            //Помещаем все комменты в id wall_fast_block_{id} это для JS
                            $tpl->result[$compile] .= '<div id="wall_fast_block_'.$row_wall['id'].'" class="public_wall_rec_comments">';

                            if($row_wall['fasts_num'] > 3)
                                $comments_limit = $row_wall['fasts_num']-3;
                            else
                                $comments_limit = 0;

                            $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$row_wall['id']}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

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
                            $tpl->set('{public-id}', $row['id']);
                            $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                            $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $tpl->compile($compile);

                            //Сообственно выводим комменты
                            foreach($sql_comments as $row_comments){
                                $tpl->set('{public-id}', $row['id']);
                                $tpl->set('{name}', $row_comments['user_search_pref']);
                                if($row_comments['user_photo'])
                                    $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                                else
                                    $tpl->set('{ava}', '/images/no_ava_50.png');

                                $tpl->set('{rec-id}', $row_wall['id']);
                                $tpl->set('{comm-id}', $row_comments['id']);
                                $tpl->set('{user-id}', $row_comments['public_id']);

                                $expBR2 = explode('<br />', $row_comments['text']);
                                $textLength2 = count($expBR2);
                                $strTXT2 = strlen($row_comments['text']);
                                if($textLength2 > 6 OR $strTXT2 > 470)
                                    $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                                //Обрабатываем ссылки
                                $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                                $tpl->set('{text}', stripslashes($row_comments['text']));

                                $date = megaDate(strtotime($row_comments['add_date']));
                                $tpl->set('{date}', $date);
                                if($public_admin OR $user_id == $row_comments['public_id']){
                                    $tpl->set('[owner]', '');
                                    $tpl->set('[/owner]', '');
                                } else
                                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                                if($user_id == $row_comments['public_id'])

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
                            $tpl->set('{user-id}', $row_wall['public_id']);
                            $tpl->set('[comment-form]', '');
                            $tpl->set('[/comment-form]', '');
                            $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                            $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                            $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                            $tpl->compile($compile);

                            //Закрываем блок для JS
                            $tpl->result[$compile] .= '</div>';
                        }
                    }
                }

                Tools::AjaxTpl($tpl);
            }
            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Добавление комментария к записи
     */
    public function wall_send_comm($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            AntiSpam('comments');

            $rec_id = intval($_POST['rec_id']);
            $public_id = intval($_POST['public_id']);
            $wall_text = Validation::ajax_utf8(Validation::textFilter($_POST['wall_text']));
            $answer_comm_id = intval($_POST['answer_comm_id']);

            //Проверка на админа и проверяем включены ли комменты
            $row = $db->super_query("SELECT tb1.fasts_num, public_id, tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

            if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false AND isset($wall_text) AND !empty($wall_text)){

                AntiSpamLogInsert('comments');

                //Если добавляется ответ на комментарий то вносим в ленту новостей "ответы"
                if($answer_comm_id){

                    //Выводим ид владельца комменатрия
                    $row_owner2 = $db->super_query("SELECT public_id, text FROM `".PREFIX."_communities_wall` WHERE id = '{$answer_comm_id}' AND fast_comm_id != '0'");

                    //Проверка на то, что юзер не отвечает сам себе
                    if($user_id != $row_owner2['public_id'] AND $row_owner2){

                        $answer_text = $db->safesql($row_owner2['text']);

                        $check2 = $db->super_query("SELECT user_last_visit, user_name FROM `".PREFIX."_users` WHERE user_id = '{$row_owner2['public_id']}'");

                        $wall_text = str_replace($check2['user_name'], "<a href=\"/u{$row_owner2['public_id']}\" onClick=\"Page.Go(this.href); return false\" class=\"newcolor000\">{$check2['user_name']}</a>", $wall_text);

                        //Вставляем в ленту новостей
                        $server_time = intval($_SERVER['REQUEST_TIME']);
                        $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 6, action_text = '{$wall_text}', obj_id = '{$answer_comm_id}', for_user_id = '{$row_owner2['public_id']}', action_time = '{$server_time}', answer_text = '{$answer_text}', link = '/wallgroups{$row['public_id']}_{$rec_id}'");

                        //Вставляем событие в моментальные оповещания
                        $update_time = $server_time - 70;

                        if($check2['user_last_visit'] >= $update_time){

                            $db->query("INSERT INTO `".PREFIX."_updates` SET for_user_id = '{$row_owner2['public_id']}', from_user_id = '{$user_id}', type = '5', date = '{$server_time}', text = '{$wall_text}', user_photo = '{$user_info['user_photo']}', user_search_pref = '{$user_info['user_search_pref']}', lnk = '/news/notifications'");

                            Cache::mozg_create_cache("user_{$row_owner2['public_id']}/updates", 1);

                            //ИНАЧЕ Добавляем +1 юзеру для оповещания
                        } else {

                            $cntCacheNews = Cache::mozg_cache("user_{$row_owner2['public_id']}/new_news");
                            Cache::mozg_create_cache("user_{$row_owner2['public_id']}/new_news", ($cntCacheNews+1));

                        }

                    }

                }

                //Вставляем саму запись в БД
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_communities_wall` SET public_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', fast_comm_id = '{$rec_id}'");
                $db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num+1 WHERE id = '{$rec_id}'");

                $row['fasts_num'] = $row['fasts_num']+1;

                if($row['fasts_num'] > 3)
                    $comments_limit = $row['fasts_num']-3;
                else
                    $comments_limit = 0;

                $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC LIMIT {$comments_limit}, 3", 1);

                //Загружаем кнопку "Показать N запсии"
                $tpl->load_template('groups/record.tpl');

                $titles1 = array('предыдущий', 'предыдущие', 'предыдущие');//prev
                $titles2 = array('комментарий', 'комментария', 'комментариев');//comments
                $tpl->set('{gram-record-all-comm}', Gramatic::declOfNum(($row['fasts_num']-3), $titles1).' '.($row['fasts_num']-3).' '.Gramatic::declOfNum(($row['fasts_num']-3), $titles2));
                if($row['fasts_num'] < 4)
                    $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                else {
                    $tpl->set('{rec-id}', $rec_id);
                    $tpl->set('[all-comm]', '');
                    $tpl->set('[/all-comm]', '');
                }
                $tpl->set('{public-id}', $public_id);
                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                $tpl->set_block("'\\[comment-form\\](.*?)\\[/comment-form\\]'si","");
                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $tpl->compile('content');

                $tpl->load_template('groups/record.tpl');
                //Сообственно выводим комменты
                $config = include __DIR__.'/../data/config.php';
                foreach($sql_comments as $row_comments){
                    $tpl->set('{public-id}', $public_id);
                    $tpl->set('{name}', $row_comments['user_search_pref']);
                    if($row_comments['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    $tpl->set('{comm-id}', $row_comments['id']);
                    $tpl->set('{user-id}', $row_comments['public_id']);
                    $tpl->set('{rec-id}', $rec_id);

                    $expBR2 = explode('<br />', $row_comments['text']);
                    $textLength2 = count($expBR2);
                    $strTXT2 = strlen($row_comments['text']);
                    if($textLength2 > 6 OR $strTXT2 > 470)
                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                    //Обрабатываем ссылки
                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                    $tpl->set('{text}', stripslashes($row_comments['text']));
                    $date = megaDate(strtotime($row['add_date']));
                    $tpl->set('{date}', $date);
                    if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    if($user_id == $row_comments['public_id'])

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
                    $tpl->compile('content');
                }

                //Загружаем форму ответа
                $tpl->load_template('groups/record.tpl');
                $tpl->set('{rec-id}', $rec_id);
                $tpl->set('{user-id}', $public_id);
                $tpl->set('[comment-form]', '');
                $tpl->set('[/comment-form]', '');
                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                $tpl->compile('content');

                Tools::AjaxTpl($tpl);
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Удаление записи
     */
    public function wall_del($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rec_id = intval($_POST['rec_id']);
            $public_id = intval($_POST['public_id']);

            //Проверка на админа и проверяем включены ли комменты
            if($public_id){
                $row = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
                $row_rec = $db->super_query("SELECT fast_comm_id, public_id, add_date FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
            } else
                $row = $db->super_query("SELECT tb1.public_id, attach, fast_comm_id, tb2.admin FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

            if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_rec['public_id']){
                if($public_id){

                    $db->query("UPDATE `".PREFIX."_communities_wall` SET fasts_num = fasts_num-1 WHERE id = '{$row_rec['fast_comm_id']}'");
                    $db->query("DELETE FROM `".PREFIX."_news` WHERE ac_user_id = '{$row_rec['public_id']}' AND action_type = '6' AND action_time = '{$row_rec['add_date']}'");

                    $db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");

                } else if($row['fast_comm_id'] == 0){

                    $db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = '{$rec_id}'");
                    $db->query("DELETE FROM `".PREFIX."_news` WHERE obj_id = '{$rec_id}' AND action_type = '11'");
                    $db->query("UPDATE `".PREFIX."_communities` SET rec_num = rec_num-1 WHERE id = '{$row['public_id']}'");

                    //Удаляем фотку из прикрипленой ссылке, если она есть
                    if(stripos($row['attach'], 'link|') !== false){
                        $attach_arr = explode('link|', $row['attach']);
                        $attach_arr2 = explode('|/uploads/attach/'.$user_id.'/', $attach_arr[1]);
                        $attach_arr3 = explode('||', $attach_arr2[1]);
                        if($attach_arr3[0])
                            @unlink(__DIR__.'/../../uploads/attach/'.$user_id.'/'.$attach_arr3[0]);
                    }

                    $db->query("DELETE FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");
                }

            }
            die();
        }
    }

    /**
     * Показ всех комментариев к записи
     */
    public function all_comm($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rec_id = intval($_POST['rec_id']);
            $public_id = intval($_POST['public_id']);

            //Проверка на админа и проверяем включены ли комменты
            $row = $db->super_query("SELECT tb2.admin, comments FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.public_id = tb2.id AND tb1.id = '{$rec_id}'");

            if($row['comments'] OR stripos($row['admin'], "u{$user_id}|") !== false){
                $sql_comments = $db->super_query("SELECT tb1.id, public_id, text, add_date, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_users` tb2 WHERE tb1.public_id = tb2.user_id AND tb1.fast_comm_id = '{$rec_id}' ORDER by `add_date` ASC", 1);
                $tpl->load_template('groups/record.tpl');
                //Сообственно выводим комменты
                $config = include __DIR__.'/../data/config.php';
                foreach($sql_comments as $row_comments){
                    $tpl->set('{public-id}', $public_id);
                    $tpl->set('{name}', $row_comments['user_search_pref']);
                    if($row_comments['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row_comments['public_id'].'/50_'.$row_comments['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    $tpl->set('{rec-id}', $rec_id);
                    $tpl->set('{comm-id}', $row_comments['id']);
                    $tpl->set('{user-id}', $row_comments['public_id']);

                    $expBR2 = explode('<br />', $row_comments['text']);
                    $textLength2 = count($expBR2);
                    $strTXT2 = strlen($row_comments['text']);
                    if($textLength2 > 6 OR $strTXT2 > 470)
                        $row_comments['text'] = '<div class="wall_strlen" id="hide_wall_rec'.$row_comments['id'].'" style="max-height:102px"">'.$row_comments['text'].'</div><div class="wall_strlen_full" onMouseDown="wall.FullText('.$row_comments['id'].', this.id)" id="hide_wall_rec_lnk'.$row_comments['id'].'">Показать полностью..</div>';

                    //Обрабатываем ссылки
                    $row_comments['text'] = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<a href="/away.php?url=$1" target="_blank">$1</a>', $row_comments['text']);

                    $tpl->set('{text}', stripslashes($row_comments['text']));
                    $date = megaDate(strtotime($row_comments['add_date']));
                    $tpl->set('{date}', $date);
                    if(stripos($row['admin'], "u{$user_id}|") !== false OR $user_id == $row_comments['public_id']){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    if($user_id == $row_comments['public_id'])

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
                    $tpl->compile('content');
                }

                //Загружаем форму ответа
                $tpl->load_template('groups/record.tpl');
                $tpl->set('{rec-id}', $rec_id);
                $tpl->set('{user-id}', $public_id);
                $tpl->set('[comment-form]', '');
                $tpl->set('[/comment-form]', '');
                $tpl->set_block("'\\[record\\](.*?)\\[/record\\]'si","");
                $tpl->set_block("'\\[comment\\](.*?)\\[/comment\\]'si","");
                $tpl->set_block("'\\[all-comm\\](.*?)\\[/all-comm\\]'si","");
                $tpl->compile('content');

                Tools::AjaxTpl($tpl);
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Страница загрузки фото в сообщество
     */
    public function photos($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $public_id = intval($_POST['public_id']);
            $rowPublic = $db->super_query("SELECT admin, photos_num FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
            if(stripos($rowPublic['admin'], "u{$user_id}|") !== false){

                if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
                $gcount = 36;
                $limit_page = ($page-1)*$gcount;

                //HEAD
                $tpl->load_template('public/photos/head.tpl');
                $titles = array('фотография', 'фотографии', 'фотографий');//photos
                $tpl->set('{photo-num}', $rowPublic['photos_num'].' '.Gramatic::declOfNum($rowPublic['photos_num'], $titles));
                $tpl->set('{public_id}', $public_id);
                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('info');

                //Выводим фотографии
                if($rowPublic['photos_num']){
                    $sql_ = $db->super_query("SELECT photo FROM `".PREFIX."_attach` WHERE public_id = '{$public_id}' ORDER by `add_date` DESC LIMIT {$limit_page}, {$gcount}", 1);
                    $tpl->load_template('public/photos/photo.tpl');
                    foreach($sql_ as $row){
                        $tpl->set('{photo}', $row['photo']);
                        $tpl->set('{public-id}', $public_id);
                        $tpl->compile('content');
                    }
                    box_navigation($gcount, $rowPublic['photos_num'], $page, 'groups.wall_attach_addphoto', $public_id);
                } else
                    msgbox('', '<div class="clear" style="margin-top:150px;margin-left:27px"></div>В альбоме сообщества нет загруженных фотографий.', 'info_2');

                //BOTTOM
                $tpl->load_template('public/photos/head.tpl');
                $tpl->set('[bottom]', '');
                $tpl->set('[/bottom]', '');
                $tpl->set_block("'\\[top\\](.*?)\\[/top\\]'si","");
                $tpl->compile('content');

                Tools::AjaxTpl($tpl);
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Выводим инфу о видео при прикриплении видео на стену
     */
    public function select_video_info($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $video_id = intval($_POST['video_id']);
            $row = $db->super_query("SELECT photo FROM `".PREFIX."_videos` WHERE id = '".$video_id."'");
            if($row){
                $photo = end(explode('/', $row['photo']));
                echo $photo;
            } else
                echo '1';

            die();
        }
    }

    /**
     * Ставим мне нравится
     */
    public function wall_like_yes($params){
        //$tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            //$act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            //$limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rec_id = intval($_POST['rec_id']);
            $row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
            if($row AND stripos($row['likes_users'], "u{$user_id}|") === false){
                $likes_users = "u{$user_id}|".$row['likes_users'];
                $db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num+1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `".PREFIX."_communities_wall_like` SET rec_id = '".$rec_id."', user_id = '".$user_id."', date = '".$server_time."'");
            }
        }
    }

    /**
     * Убераем мне нравится
     */
    public function wall_like_remove($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rec_id = intval($_POST['rec_id']);
            $row = $db->super_query("SELECT likes_users FROM `".PREFIX."_communities_wall` WHERE id = '".$rec_id."'");
            if(stripos($row['likes_users'], "u{$user_id}|") !== false){
                $likes_users = str_replace("u{$user_id}|", '', $row['likes_users']);
                $db->query("UPDATE `".PREFIX."_communities_wall` SET likes_num = likes_num-1, likes_users = '{$likes_users}' WHERE id = '".$rec_id."'");
                $db->query("DELETE FROM `".PREFIX."_communities_wall_like` WHERE rec_id = '".$rec_id."' AND user_id = '".$user_id."'");
            }
            die();
        }
    }

    /**
     * Выводим последних 7 юзеров кто поставил "Мне нравится"
     */
    public function wall_like_users_five($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rec_id = intval($_POST['rec_id']);
            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rec_id}' ORDER by `date` DESC LIMIT 0, 7", 1);
            if($sql_){
                $config = include __DIR__.'/../data/config.php';
                foreach($sql_ as $row){
                    if($row['user_photo']) $ava = '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo'];
                    else $ava = '/templates/'.$config['temp'].'/images/no_ava_50.png';
                    echo '<a href="/u'.$row['user_id'].'" id="Xlike_user'.$row['user_id'].'_'.$rec_id.'" onClick="Page.Go(this.href); return false"><img src="'.$ava.'" width="32" /></a>';
                }
            }
            die();
        }
    }

    /**
     * Выводим всех юзеров которые поставили "мне нравится"
     */
    public function all_liked_users($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rid']);
            $liked_num = intval($_POST['liked_num']);

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            if(!$liked_num)
                $liked_num = 24;

            if($rid AND $liked_num){
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_photo, user_search_pref FROM `".PREFIX."_communities_wall_like` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = tb2.user_id AND tb1.rec_id = '{$rid}' ORDER by `date` DESC LIMIT {$limit_page}, {$gcount}", 1);

                if($sql_){
                    $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                    $tpl->set('[top]', '');
                    $tpl->set('[/top]', '');
                    $titles = array('человеку', 'людям', 'людям');//like
                    $tpl->set('{subcr-num}', 'Понравилось '.$liked_num.' '.Gramatic::declOfNum($liked_num, $titles));
                    $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                    $tpl->compile('content');

                    $tpl->result['content'] = str_replace('Всего', '', $tpl->result['content']);

                    $tpl->load_template('profile_friends.tpl');
                    $config = include __DIR__.'/../data/config.php';
                    foreach($sql_ as $row){
                        if($row['user_photo'])
                            $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');
                        $friend_info_online = explode(' ', $row['user_search_pref']);
                        $tpl->set('{user-id}', $row['user_id']);
                        $tpl->set('{name}', $friend_info_online[0]);
                        $tpl->set('{last-name}', $friend_info_online[1]);
                        $tpl->compile('content');
                    }
                    box_navigation($gcount, $liked_num, $rid, 'groups.wall_all_liked_users', $liked_num);

                    Tools::AjaxTpl($tpl);
                }
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Рассказать друзьям "Мне нравится"
     */
    public function wall_tell($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();
            $rid = intval($_POST['rec_id']);

            //Проверка на существование записи
            $row = $db->super_query("SELECT add_date, text, public_id, attach, tell_uid, tell_date, public FROM `".PREFIX."_communities_wall` WHERE fast_comm_id = 0 AND id = '{$rid}'");

            if($row){
                if($row['tell_uid']){
                    $row['add_date'] = $row['tell_date'];
                    $row['author_user_id'] = $row['tell_uid'];
                    $row['public_id'] = $row['tell_uid'];
                } else
                    $row['public'] = 1;

                //Проверяем на существование этой записи у себя на стене
                $myRow = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_wall` WHERE tell_uid = '{$row['public_id']}' AND tell_date = '{$row['add_date']}' AND author_user_id = '{$user_id}' AND public = '{$row['public']}'");
                if($row['tell_uid'] != $user_id AND $myRow['cnt'] == false){
                    $row['text'] = $db->safesql($row['text']);
                    $row['attach'] = $db->safesql($row['attach']);

                    //Всталвяем себе на стену
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $db->query("INSERT INTO `".PREFIX."_wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$row['text']}', add_date = '{$server_time}', fast_comm_id = 0, tell_uid = '{$row['public_id']}', tell_date = '{$row['add_date']}', public = '{$row['public']}', attach = '".$row['attach']."'");
                    $dbid = $db->insert_id();
                    $db->query("UPDATE `".PREFIX."_users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                    //Вставляем в ленту новостей
                    $db->query("INSERT INTO `".PREFIX."_news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$row['text']}', obj_id = '{$dbid}', action_time = '{$server_time}'");

                    //Чистим кеш
                    Cache::mozg_clear_cache_file("user_{$user_id}/profile_{$user_id}");
                } else
                    echo 1;
            } else
                echo 1;

            die();
        }
    }

    /**
     * Показ всех подпискок
     */
    public function all_people($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 24;
            $limit_page = ($page-1)*$gcount;

            $public_id = intval($_POST['public_id']);
            $subscr_num = intval($_POST['num']);

            $sql_ = $db->super_query("SELECT tb1.user_id, tb2.user_name, user_lastname, user_photo FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.friend_id = '{$public_id}' AND tb1.user_id = tb2.user_id AND tb1.subscriptions = 2 ORDER by `friends_date` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
                $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                $tpl->set('{subcr-num}', $subscr_num.' '.Gramatic::declOfNum($subscr_num, $titles));
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('content');

                $tpl->load_template('profile_friends.tpl');
                foreach($sql_ as $row){
                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');
                    $tpl->set('{user-id}', $row['user_id']);
                    $tpl->set('{name}', $row['user_name']);
                    $tpl->set('{last-name}', $row['user_lastname']);
                    $tpl->compile('content');
                }

                box_navigation($gcount, $subscr_num, $public_id, 'groups.all_people', $subscr_num);

            }

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Показ всех сообщества юзера на которые он подписан (BOX)
     */
    public function all_groups_user($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            if($_POST['page'] > 0) $page = intval($_POST['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $for_user_id = intval($_POST['for_user_id']);
            $subscr_num = intval($_POST['num']);

            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$for_user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_){
                $tpl->load_template('/profile/profile_subscription_box_top.tpl');
                $tpl->set('[top]', '');
                $tpl->set('[/top]', '');
                $titles = array('подписка', 'подписки', 'подписок');//subscr
                $tpl->set('{subcr-num}', $subscr_num.' '.Gramatic::declOfNum($subscr_num, $titles));
                $tpl->set_block("'\\[bottom\\](.*?)\\[/bottom\\]'si","");
                $tpl->compile('content');

                $tpl->load_template('/profile/profile_group.tpl');
                foreach($sql_ as $row){
                    if($row['photo']) $tpl->set('{ava}', '/uploads/groups/'.$row['id'].'/50_'.$row['photo']);
                    else $tpl->set('{ava}', '/images/no_ava_50.png');
                    $tpl->set('{name}', stripslashes($row['title']));
                    $tpl->set('{public-id}', $row['id']);
                    $titles = array('подписчик', 'подписчика', 'подписчиков');//subscribers
                    $tpl->set('{num}', '<span id="traf">'.$row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles));
                    if($row['adres']) $tpl->set('{adres}', $row['adres']);
                    else $tpl->set('{adres}', 'public'.$row['id']);
                    $tpl->compile('content');
                }

                Tools::box_navigation($gcount, $subscr_num, $for_user_id, 'groups.all_groups_user', $subscr_num, $tpl);

            }

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Одна запись со стены
     */
    public function wallgroups($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            $id = intval($_GET['id']);
            $pid = intval($_GET['pid']);

            $row = $db->super_query("SELECT id, adres, del, ban FROM `".PREFIX."_communities` WHERE id = '{$pid}'");

            if($row AND !$row['del'] AND !$row['ban']){

                $tpl->load_template('groups/wall_head.tpl');
                $tpl->set('{id}', $id);
                $tpl->set('{pid}', $pid);
                if($row['adres'])
                    $tpl->set('{adres}', $row['adres']);
                else
                    $tpl->set('{adres}', 'public'.$pid);
                $tpl->compile('info');

                include __DIR__.'/../Classes/wall.public.php';
                $wall = new \wall();
                $wall->query("SELECT tb1.id, text, public_id, add_date, fasts_num, attach, likes_num, likes_users, tell_uid, public, tell_date, tell_comm, tb2.title, photo, comments, adres FROM `".PREFIX."_communities_wall` tb1, `".PREFIX."_communities` tb2 WHERE tb1.id = '{$id}' AND tb1.public_id = tb2.id AND fast_comm_id = 0");
                $wall->template('groups/record.tpl');
                $wall->compile('content');
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $wall->select($public_admin, $server_time);

                $tpl->result['content'] = str_replace('width:500px;', 'width:710px;', $tpl->result['content']);

                if(!$tpl->result['content'])
                    msgbox('', '<br /><br /><br />Запись не найдена.<br /><br /><br />', 'info_2');

            } else
                msgbox('', '<br /><br />Запись не найдена.<br /><br /><br />', 'info_2');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Закрипление записи
     */
    public function fasten($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $rec_id = intval($_POST['rec_id']);

            //Выводим ИД группы
            $row = $db->super_query("SELECT public_id FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Убераем фиксацию у пред записи
                $db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '0' WHERE fixed = '1' AND public_id = '{$row['public_id']}'");

                //Ставим фиксацию записи
                $db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '1' WHERE id = '{$rec_id}'");

            }

            exit();
        }
    }

    /**
     * Убераем фиксацию
     */
    public function unfasten($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $rec_id = intval($_POST['rec_id']);

            //Выводим ИД группы
            $row = $db->super_query("SELECT public_id FROM `".PREFIX."_communities_wall` WHERE id = '{$rec_id}'");

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$row['public_id']}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Убераем фиксацию записи
                $db->query("UPDATE `".PREFIX."_communities_wall` SET fixed = '0' WHERE id = '{$rec_id}'");

            }

            exit();
        }
    }

    /**
     * Загрузка обложки
     */
    public function upload_cover($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $public_id = intval($_GET['id']);

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Получаем данные о файле
                $image_tmp = $_FILES['uploadfile']['tmp_name'];
                $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя файла
                $image_size = $_FILES['uploadfile']['size']; // размер файла
                $type = end(explode(".", $image_name)); // формат файла

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
                            $row = $db->super_query("SELECT cover FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
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

                            $db->query("UPDATE `".PREFIX."_communities` SET cover = '{$image_rename}{$res_type}', cover_pos = '{$pos}' WHERE id = '{$public_id}'");

                            echo $public_id.'/'.$image_rename.$res_type.'|'.$rImgsData;

                        }

                    } else
                        echo 2;

                } else
                    echo 1;

            }

            exit();
        }
    }

    /**
     * Сохранение новой позиции обложки
     */
    public function savecoverpos($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $public_id = intval($_GET['id']);

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                $pos = intval($_POST['pos']);
                if($pos < 0) $pos = 0;

                $db->query("UPDATE `".PREFIX."_communities` SET cover_pos = '{$pos}' WHERE id = '{$public_id}'");

            }

            exit();
        }
    }

    public function delcover($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $public_id = intval($_GET['id']);

            //Проверка на админа
            $row_pub = $db->super_query("SELECT admin FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");

            if(stripos($row_pub['admin'], "u{$user_id}|") !== false){

                //Выводим и удаляем пред. обложку
                $row = $db->super_query("SELECT cover FROM `".PREFIX."_communities` WHERE id = '{$public_id}'");
                if($row){

                    $upDir = __DIR__."/../../uploads/groups/{$public_id}/";
                    @unlink($upDir.$row['cover']);

                }

                $db->query("UPDATE `".PREFIX."_communities` SET cover_pos = '', cover = '' WHERE id = '{$public_id}'");

            }

            exit();
        }
    }

    public function invitebox($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $pub_id = intval($_POST['id']);

            $limit_friends = 20;
            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt']) * $limit_friends;
            else $page_cnt = 0;

            //Выводим список участников группы
            $rowPub = $db->super_query("SELECT ulist FROM `".PREFIX."_communities` WHERE id = '{$pub_id}'");

            //Выводим список друзей
            $sql_ = $db->super_query("SELECT tb1.friend_id, tb2.user_photo, user_search_pref, user_sex FROM `".PREFIX."_friends` tb1, `".PREFIX."_users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 0 ORDER by `friends_date` DESC LIMIT {$page_cnt}, {$limit_friends}", 1);

            if($sql_){

                $tpl->load_template('groups/inviteuser.tpl');
                $config = include __DIR__.'/../data/config.php';
                foreach($sql_ as $row){

                    if($row['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['friend_id'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', "/images/100_no_ava.png");

                    $tpl->set('{user-id}', $row['friend_id']);
                    $tpl->set('{name}', $row['user_search_pref']);


                    //Проверка, юзер есть в сообществе или нет
                    if(stripos($rowPub['ulist'], '|'.$row['friend_id'].'|') !== false){

                        $tpl->set('{yes-group}', 'grInviteYesed');
                        $tpl->set('{yes-text}', '<div class="fl_r online grInviteOk">в сообществе</div>');
                        $tpl->set('{function}', '');

                    } else {

                        //Проверка, юзеру отправлялось приглашение или нет
                        $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$row['friend_id']}' AND public_id = '{$pub_id}'");

                        if($check['cnt']){

                            $tpl->set('{yes-group}', 'grInviteYesed');

                            if($row['user_sex'] == 2)
                                $tpl->set('{yes-text}', '<div class="fl_r online grInviteOk">приглашена</div>');
                            else
                                $tpl->set('{yes-text}', '<div class="fl_r online grInviteOk">приглашен</div>');

                            $tpl->set('{function}', '');

                        } else {

                            $tpl->set('{yes-group}', 'grIntiveUser');
                            $tpl->set('{yes-text}', '');
                            $tpl->set('{function}', 'groups.inviteSet');

                        }

                    }

                    $tpl->compile('friends');

                }

                $numFr = count($sql_);

            }

            if(!$page_cnt){

                $tpl->load_template('groups/invitebox.tpl');
                $tpl->set('{friends}', $tpl->result['friends']);
                $tpl->set('{id}', $pub_id);

                if($numFr == $limit_friends){

                    $tpl->set('[but]', '');
                    $tpl->set('[/but]', '');

                } else

                    $tpl->set_block("'\\[but\\](.*?)\\[/but\\]'si","");

                $tpl->compile('content');

            } else {

                $tpl->result['content'] = $tpl->result['friends'];

            }

            Tools::AjaxTpl($tpl);

            exit();
        }
    }

    public function invitesend($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $pub_id = intval($_POST['id']);
            $limit = 50; #лимит в день

            //Выводим список участников группы
            $rowPub = $db->super_query("SELECT id, ulist FROM `".PREFIX."_communities` WHERE id = '{$pub_id}'");

            //Дата заявки
            $server_time = intval($_SERVER['REQUEST_TIME']);
            $newData = date('Y-m-d', $server_time);

            //Считаем сколько заявок было отправлено за последние сутки
            $rowCnt = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_join` WHERE user_id = '{$user_id}' AND public_id = '{$pub_id}' AND date = '{$newData}'");

            //Создаем точку отчета для цикла foreach, чтоб если было уже 49 отправок, и юзер еще выбрал 49 то скрипт в масиве заметил это и прекратил действия
            $i = $rowCnt['cnt'];

            //Если заявок меньше указаного лимита, то пропускаем
            if($rowCnt['cnt'] < $limit){

                //Если такая гурппа есть
                if($rowPub['id']){

                    //Получаем список, которых надо пригласить и формируем его
                    $arr_list = explode('|', $_POST['ulist']);

                    foreach($arr_list as $ruser_id){

                        $ruser_id = intval($ruser_id);

                        if($ruser_id AND $user_id != $ruser_id AND $i < $limit){

                            //Проверка, такой юзер в базе есть или нет
                            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_users` WHERE user_id = '{$ruser_id}'");

                            if($row['cnt']){

                                //Проверка, юзер есть в сообществе или нет
                                if(stripos($rowPub['ulist'], '|'.$ruser_id.'|') === false){

                                    //Проверка, юзеру отправлялось приглашение или нет
                                    $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$ruser_id}' AND public_id = '{$pub_id}'");

                                    //Проверка естьли запрашиваемый юзер в друзьях у юзера который смотрит стр
                                    $check_friend = CheckFriends($ruser_id);

                                    //Если нет приглашения, то отправляем приглашение
                                    if(!$check['cnt'] AND $check_friend){

                                        $i++;

                                        //Вставляем в таблицу приглашений заявку
                                        $db->query("INSERT INTO `".PREFIX."_communities_join` SET user_id = '{$user_id}', for_user_id = '{$ruser_id}', public_id = '{$pub_id}', date = '{$newData}'");

                                        //Добавляем юзеру +1 в приглашениях
                                        $db->query("UPDATE `".PREFIX."_users` SET invties_pub_num = invties_pub_num + 1 WHERE user_id = '{$ruser_id}'");

                                    }

                                }

                            }

                        }

                    }

                }

            } else
                echo 1;

            exit();
        }
    }

    public function invites($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            //Если подгружаем
            if($page_cnt){

                Tools::NoAjaxQuery();

            }

            $limit_num = 20;
            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt']) * $limit_num;
            else $page_cnt = 0;

            //Загружаем верхушку
            if(!$page_cnt){

                $tpl->load_template('groups/invites_head.tpl');

                if($user_info['invties_pub_num']){

                    $tpl->set('{num}', $user_info['invties_pub_num'].' '.declOfNum($user_info['invties_pub_num'], array('приглашение', 'приглашения', 'приглашений')));

                    $tpl->set('[yes]', '');
                    $tpl->set('[/yes]', '');
                    $tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");

                } else {

                    $tpl->set('[no]', '');
                    $tpl->set('[/no]', '');
                    $tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");

                }

                $tpl->compile('info');

            }

            //Выводим сообщества
            if($user_info['invties_pub_num']){

                //SQL Запрос на вывод
                $sql_ = $db->super_query("SELECT tb1.user_id, tb2.id, title, photo, traf, adres, tb3.user_search_pref, user_photo FROM `".PREFIX."_communities_join` tb1, `".PREFIX."_communities` tb2, `".PREFIX."_users` tb3 WHERE tb1.for_user_id = '{$user_id}' AND tb1.public_id = tb2.id AND tb1.user_id = tb3.user_id ORDER by `id` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

                if($sql_){

                    $tpl->load_template('groups/invite.tpl');

                    foreach($sql_ as $row){

                        if($row['photo'])
                            $tpl->set('{photo}', "/uploads/groups/{$row['id']}/100_{$row['photo']}");
                        else
                            $tpl->set('{photo}', "/images/no_ava_groups_100.gif");

                        $tpl->set('{name}', stripslashes($row['title']));
                        $tpl->set('{traf}', $row['traf'].' '.declOfNum($row['traf'], array('участник', 'участника', 'участников')));
                        $tpl->set('{id}', $row['id']);

                        if($row['adres'])
                            $tpl->set('{adres}', $row['adres']);
                        else
                            $tpl->set('{adres}', 'public'.$row['id']);

                        $tpl->set('{inviter-name}', $row['user_search_pref']);
                        $tpl->set('{inviter-id}', $row['user_id']);

                        if($row['user_photo'])
                            $tpl->set('{inviter-ava}', '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{inviter-ava}', '/images/100_no_ava.png');

                        $tpl->compile('content');

                    }

                }

            }

            //Загружаем низ
            if(!$page_cnt AND $user_info['invties_pub_num'] > $limit_num){

                $tpl->load_template('groups/invite_bottom.tpl');
                $tpl->compile('content');

            }

            //Если подгружаем
            if($page_cnt){

                Tools::AjaxTpl($tpl);

                exit();

            }
        }
    }

    public function invite_no($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['communities'];

            Tools::NoAjaxQuery();

            $id = intval($_POST['id']);

            //Проверка на приглашению юзеру
            $check = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

            //Если есть приглашение, то удаляем его
            if($check['cnt']){

                $db->query("DELETE FROM `".PREFIX."_communities_join` WHERE for_user_id = '{$user_id}' AND public_id = '{$id}'");

                //Обновляем кол-во приглашений
                $db->query("UPDATE `".PREFIX."_users` SET invties_pub_num = invties_pub_num - 1 WHERE user_id = '{$user_id}'");

            }

            exit();
        }
    }

    /**
     * Вывод всех сообществ
     */
    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $metatags['title'] = $lang['communities'];

            $owner = $db->super_query("SELECT user_public_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            if($act == 'admin'){
                $mobile_speedbar = 'Ваши сообщества';
                $tpl->load_template('groups/head_admin.tpl');
                $sql_sort = "SELECT id, title, photo, traf, adres FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]' ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
                $sql_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `".PREFIX."_communities` WHERE admin regexp '[[:<:]](u{$user_id})[[:>:]]'");
                $owner['user_public_num'] = $sql_count['cnt'];
            } else {
                $mobile_speedbar = 'Сообщества';
                $sql_sort = "SELECT tb1.friend_id, tb2.id, title, photo, traf, adres FROM `".PREFIX."_friends` tb1, `".PREFIX."_communities` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT {$limit_page}, {$gcount}";
                $tpl->load_template('groups/head.tpl');
            }

            if($owner['user_public_num']){
                $titles = array('сообществе', 'сообществах', 'сообществах');//groups
                $tpl->set('{num}', $owner['user_public_num'].' '.Gramatic::declOfNum($owner['user_public_num'], $titles));
                $tpl->set('[yes]', '');
                $tpl->set('[/yes]', '');
                $tpl->set_block("'\\[no\\](.*?)\\[/no\\]'si","");
            } else {
                $tpl->set('[no]', '');
                $tpl->set('[/no]', '');
                $tpl->set_block("'\\[yes\\](.*?)\\[/yes\\]'si","");
            }
            $tpl->compile('info');

            if($owner['user_public_num']){

                $sql_ = $db->super_query($sql_sort, 1);

                $tpl->load_template('groups/group.tpl');
                foreach($sql_ as $row){
                    $tpl->set('{id}', $row['id']);
                    if($row['adres']) $tpl->set('{adres}', $row['adres']);
                    else $tpl->set('{adres}', 'public'.$row['id']);

                    $tpl->set('{name}', stripslashes($row['title']));
                    $titles = array('участник', 'участника', 'участников');//groups_users
                    $tpl->set('{traf}', $row['traf'].' '.Gramatic::declOfNum($row['traf'], $titles));

                    if($act != 'admin'){
                        $tpl->set('[admin]', '');
                        $tpl->set('[/admin]', '');
                    } else
                        $tpl->set_block("'\\[admin\\](.*?)\\[/admin\\]'si","");

                    if($row['photo'])
                        $tpl->set('{photo}', "/uploads/groups/{$row['id']}/100_{$row['photo']}");
                    else
                        $tpl->set('{photo}', "/images/no_ava_groups_100.gif");

                    $tpl->compile('content');
                }

                if($act == 'admin') $admn_act = 'act=admin&';

                $tpl = Tools::navigation($gcount, $owner['user_public_num'], 'groups?'.$admn_act.'page=', $tpl);

            }
            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        Registry::set('tpl', $tpl);
    }
}