<?php


namespace System\Modules;


use Intervention\Image\ImageManager;
use System\Classes\Db;
use System\Classes\Thumb;
use System\Libs\Gramatic;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Validation;

class BugsController extends Module
{

    function add_box($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        Tools::NoAjaxQuery();
        $tpl->load_template('bugs/add.tpl');
        $row = $db->super_query("SELECT user_id, user_photo FROM `vii_users` WHERE user_id = '{$user_id}'");
        if($row['user_photo']) $tpl->set('{photo}', '/uploads/users/'.$row['user_id'].'/'.$row['user_photo']);
        else $tpl->set('{photo}', '/images/no_ava.gif');
        $tpl->compile('content');
        Tools::AjaxTpl($tpl);
        return true;
    }
    function create($params){
        $db = Db::getDB();

        Tools::NoAjaxQuery();
//        Tools::AntiSpam('bugs');
        $title = Validation::textFilter($_POST['title']);
        $text = Validation::textFilter($_POST['text']);
        $file = Validation::textFilter($_POST['file']);

        if(!$file){
//            die();//////}
            $file = '';
        }

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        $server_time = intval($_SERVER['REQUEST_TIME']);

        $db->query("INSERT INTO `vii_bugs` (uids, title, text, date, images) VALUES ('{$user_id}', '{$title}', '{$text}', '{$server_time}', '{$file}')");
//        Tools::AntiSpamLogInsert('bugs');
//        $iid = $db->insert_id();

        die();
    }
    function load_img($params){
        Tools::NoAjaxQuery();

        $image_tmp = $_FILES['uploadfile']['tmp_name'];
        $image_name = totranslit($_FILES['uploadfile']['name']);
        $server_time = intval($_SERVER['REQUEST_TIME']);
        $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20);
        $image_size = $_FILES['uploadfile']['size'];
        $exp = explode(".", $image_name);
        $type = end($exp); // формат файла

        $max_size = 1024 * 5000;

        if($image_size <= $max_size){
            $allowed_files = explode(', ', 'jpg, jpeg, jpe, png, gif');
            if(in_array(strtolower($type), $allowed_files)){
                $res_type = strtolower('.'.$type);
                $user_info = $this->user_info();
                $user_id = $user_info['user_id'];
                $upload_dir = __DIR__.'/../../public/uploads/bugs/'.$user_id.'/';

                if(!is_dir($upload_dir)){
                    @mkdir($upload_dir, 0777);
                    @chmod($upload_dir, 0777);
                }

//                $rImg = $upload_dir.$image_rename.$res_type;

                if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){

                    //Создание оригинала
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(600, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($upload_dir.$image_rename.'.webp', 85);

                    //Создание маленькой копии
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($upload_dir.'c_'.$image_rename.'.webp', 90);

                    unlink($upload_dir.$image_rename.$res_type);
                    $res_type = '.webp';

                    die($user_id.'|'.$image_rename.$res_type);
                }
            }
        }else
            die('size');

        die();
    }
    function delete($params){
        $db = Db::getDB();

        Tools::NoAjaxQuery();
        $id = intval($_POST['id']);

        $row = $db->super_query("SELECT uids, images FROM `vii_bugs` WHERE id = '{$id}'");

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        if($row['uids'] != $user_id || !$row['uids']) die('err');

        $url_1 = __DIR__ . '/../../public/uploads/bugs/'.$row['uids'].'/o_'.$row['images'];
        $url_2 = __DIR__ . '/../../public/uploads/bugs/'.$row['uids'].'/'.$row['images'];

        @unlink($url_1);
        @unlink($url_2);

        $db->query("DELETE FROM `vii_bugs` WHERE id = '{$id}'");

        echo 'ok';

        die();
    }
    function open($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        $limit_num = 10;
        if($_GET['page_cnt'] > 0) $page_cnt = intval($_GET['page_cnt']) * $limit_num;
        else $page_cnt = 0;

        $where = "AND status = '1'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if($sql_){

            $tpl->load_template('bugs/all.tpl');

            foreach($sql_ as $row){

                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Выводим даты сообщения//
                if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
                    $date = langdate('сегодня в H:i', $row['date']);
                elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
                    $date = langdate('вчера в H:i', $row['date']);
                else
                    $date = langdate('j F Y в H:i', $row['date']);
                $tpl->set('{date}',  $date);
                //Конец//

                if($row['status'] == 1)
                    $status = '<span class="orange">открытый</span>';
                else if($row['status'] == 2)
                    $status = '<span class="green">исправлен</span>';
                else if($row['status'] == 3)
                    $status = '<span class="red">отклонен</span>';

                if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
                else $tpl->set('{sex}', 'добавила');

                if($row['uids'] == $row['user_id']){
                    if($row['uids'])
                        $tpl->set('{status}', $status);
                    else
                        $tpl->set('{status}', $status);

                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['uids'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/no_ava_50.png');

                }

                $tpl->set('{id}', $row['id']);

                $tpl->set('{uid}', $row['user_id']);

                $tpl->compile('bugs');
            }

        }else{
            $tpl->result['bugs'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';
        }
        $tpl->load_template('bugs/head.tpl');
        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $tpl->compile('content');

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
    function complete($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        $limit_num = 10;
        if($_GET['page_cnt'] > 0) $page_cnt = intval($_GET['page_cnt']) * $limit_num;
        else $page_cnt = 0;

        $where = "AND status = '2'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if($sql_){

            $tpl->load_template('bugs/all.tpl');

            foreach($sql_ as $row){

                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Выводим даты сообщения//
                if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
                    $date = langdate('сегодня в H:i', $row['date']);
                elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
                    $date = langdate('вчера в H:i', $row['date']);
                else
                    $date = langdate('j F Y в H:i', $row['date']);
                $tpl->set('{date}',  $date);
                //Конец//

                if($row['status'] == 1)
                    $status = '<span class="orange">открытый</span>';
                else if($row['status'] == 2)
                    $status = '<span class="green">исправлен</span>';
                else if($row['status'] == 3)
                    $status = '<span class="red">отклонен</span>';

                if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
                else $tpl->set('{sex}', 'добавила');

                if($row['uids'] == $row['user_id']){
                    if($row['uids'])
                        $tpl->set('{status}', $status);
                    else
                        $tpl->set('{status}', $status);

                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['uids'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/no_ava_50.png');

                }

                $tpl->set('{id}', $row['id']);

                $tpl->set('{uid}', $row['user_id']);

                $tpl->compile('bugs');
            }

        }else{
            $tpl->result['bugs'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';
        }
        $tpl->load_template('bugs/head.tpl');
        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $tpl->compile('content');

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
    function close($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        $limit_num = 10;
        if($_GET['page_cnt'] > 0) $page_cnt = intval($_GET['page_cnt']) * $limit_num;
        else $page_cnt = 0;

        $where = "AND status = '3'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if($sql_){

            $tpl->load_template('bugs/all.tpl');

            foreach($sql_ as $row){

                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Выводим даты сообщения//
                if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
                    $date = langdate('сегодня в H:i', $row['date']);
                elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
                    $date = langdate('вчера в H:i', $row['date']);
                else
                    $date = langdate('j F Y в H:i', $row['date']);
                $tpl->set('{date}',  $date);
                //Конец//

                if($row['status'] == 1)
                    $status = '<span class="orange">открытый</span>';
                else if($row['status'] == 2)
                    $status = '<span class="green">исправлен</span>';
                else if($row['status'] == 3)
                    $status = '<span class="red">отклонен</span>';

                if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
                else $tpl->set('{sex}', 'добавила');

                if($row['uids'] == $row['user_id']){
                    if($row['uids'])
                        $tpl->set('{status}', $status);
                    else
                        $tpl->set('{status}', $status);

                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['uids'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/no_ava_50.png');

                }

                $tpl->set('{id}', $row['id']);

                $tpl->set('{uid}', $row['user_id']);

                $tpl->compile('bugs');
            }

        }else{
            $tpl->result['bugs'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';
        }
        $tpl->load_template('bugs/head.tpl');
        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $tpl->compile('content');

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
    function my($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        $limit_num = 10;
        if($_GET['page_cnt'] > 0) $page_cnt = intval($_GET['page_cnt']) * $limit_num;
        else $page_cnt = 0;

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        $where = "AND uids = '{$user_id}'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if($sql_){

            $tpl->load_template('bugs/all.tpl');

            foreach($sql_ as $row){

                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Выводим даты сообщения//
                if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
                    $date = langdate('сегодня в H:i', $row['date']);
                elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
                    $date = langdate('вчера в H:i', $row['date']);
                else
                    $date = langdate('j F Y в H:i', $row['date']);
                $tpl->set('{date}',  $date);
                //Конец//

                if($row['status'] == 1)
                    $status = '<span class="orange">открытый</span>';
                else if($row['status'] == 2)
                    $status = '<span class="green">исправлен</span>';
                else if($row['status'] == 3)
                    $status = '<span class="red">отклонен</span>';

                if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
                else $tpl->set('{sex}', 'добавила');

                if($row['uids'] == $row['user_id']){
                    if($row['uids'])
                        $tpl->set('{status}', $status);
                    else
                        $tpl->set('{status}', $status);

                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['uids'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/no_ava_50.png');

                }

                $tpl->set('{id}', $row['id']);

                $tpl->set('{uid}', $row['user_id']);

                $tpl->compile('bugs');
            }

        }else{
            $tpl->result['bugs'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';
        }
        $tpl->load_template('bugs/head.tpl');
        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $tpl->compile('content');

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }

    function view($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        Tools::NoAjaxQuery();

        $id = intval($_POST['id']);

        $row = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.id = '{$id}' AND tb1.uids = tb2.user_id");
        $bugs = $db->super_query("SELECT admin_id, admin_text FROM `vii_bugs` WHERE admin_id = '{$row['user_id']}'");

        if(!$row) die('err');

        $tpl->load_template('bugs/view.tpl');
        //Bugs
        if($row['images'] == 'null'){
            $tpl->set('{photo}', '');
        }else{
            $tpl->set('{photo}', '<img src="/uploads/bugs/'.$row['uids'].'/o_'.$row['images'].'" style="width: 70%; opacity: 1;" class="">');
        }
        $tpl->set('{title}', stripslashes($row['title']));
        $tpl->set('{text}', stripslashes($row['text']));
        $tpl->set('{id}', $row['id']);

        //Admin
        $tpl->set('{admin_text}', stripslashes($row['admin_text']));
        $tpl->set('{admin_id}', stripslashes($row['admin_id']));

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        if($user_id == $bugs['admin_id'])
            $tpl->set('{admin}', $row['user_search_pref']);
        else
            $tpl->set('{admin}', $row['user_search_pref']);

        $server_time = intval($_SERVER['REQUEST_TIME']);

        //Выводим даты сообщения//
        if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
            $date = langdate('сегодня в H:i', $row['date']);
        elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
            $date = langdate('вчера в H:i', $row['date']);
        else
            $date = langdate('j F Y в H:i', $row['date']);
        $tpl->set('{date}',  $date);
        //Конец//

        //Пол
        if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
        else $tpl->set('{sex}', 'добавила');

        //status
        if($row['status'] == 1)
            $status = '<span class="orange">открытый</span>';
        else if($row['status'] == 2)
            $status = '<span class="green">исправлен</span>';
        else if($row['status'] == 3)
            $status = '<span class="red">отклонен</span>';
        $tpl->set('{status}', $status);

        //user
        if($user_id == $row['uids'])
            $tpl->set('{delete}', '<a href="/" onClick="bugs.Delete('.$row['id'].'); return false;" style="color: #000000">Удалить</a>');
        else
            $tpl->set('{delete}', '');

        $tpl->set('{uid}', $row['user_id']);
        if($row['user_photo']) $tpl->set('{ava}', '/uploads/users/'.$row['user_id'].'/50_'.$row['user_photo']);
        else $tpl->set('{ava}', '/templates/Default/images/no_ava_50.png');
        $tpl->set('{name}', $row['user_search_pref']);
        $tpl->compile('content');

        Tools::AjaxTpl($tpl);

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }

    function index($params){
        $tpl = Registry::get('tpl');
        $db = Db::getDB();

        $limit_num = 10;
        if($_GET['page_cnt'] > 0) $page_cnt = intval($_GET['page_cnt']) * $limit_num;
        else $page_cnt = 0;

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `vii_bugs` tb1, `vii_users` tb2 WHERE tb1.uids = tb2.user_id {$where_sql} {$where_cat} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if($sql_){

            $tpl->load_template('bugs/all.tpl');

            foreach($sql_ as $row){

                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{text}', stripslashes($row['text']));

                $server_time = intval($_SERVER['REQUEST_TIME']);

                //Выводим даты сообщения//
                if(date('Y-m-d', $row['date']) == date('Y-m-d', $server_time))
                    $date = langdate('сегодня в H:i', $row['date']);
                elseif(date('Y-m-d', $row['date']) == date('Y-m-d', ($server_time-84600)))
                    $date = langdate('вчера в H:i', $row['date']);
                else
                    $date = langdate('j F Y в H:i', $row['date']);
                $tpl->set('{date}',  $date);
                //Конец//

                if($row['status'] == 1)
                    $status = '<span class="orange">открытый</span>';
                else if($row['status'] == 2)
                    $status = '<span class="green">исправлен</span>';
                else if($row['status'] == 3)
                    $status = '<span class="red">отклонен</span>';

                if($row['user_sex'] == 1) $tpl->set('{sex}', 'добавил');
                else $tpl->set('{sex}', 'добавила');

                if($row['uids'] == $row['user_id']){
                    if($row['uids'])
                        $tpl->set('{status}', $status);
                    else
                        $tpl->set('{status}', $status);

                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['uids'].'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '{theme}/images/no_ava_50.png');

                }

                $tpl->set('{id}', $row['id']);

                $tpl->set('{uid}', $row['user_id']);

                $tpl->compile('bugs');
            }

        }else{
            $tpl->result['bugs'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';
        }
        $tpl->load_template('bugs/head.tpl');
        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $tpl->compile('content');

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}