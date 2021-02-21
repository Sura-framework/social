<?php

declare(strict_types=1);

namespace App\Modules;

use App\Libs\Antispam;
use App\Models\Bugs;
use App\Models\Menu;
use Intervention\Image\ImageManager;
use Sura\Libs\Request;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;
use Sura\Time\Date;

class BugsController extends Module
{

    /**
     * @return int
     * @throws \JsonException
     */
    public function add_box(): int
    {
//        $tpl = $params['tpl'];
        $db = $this->db();
        $user_info = $row = $this->user_info();
        $user_id = $user_info['user_id'];

//        Tools::NoAjaxQuery();
//        $tpl->load_template('bugs/add.tpl');
        $row = $db->super_query("SELECT user_id, user_photo FROM `users` WHERE user_id = '{$user_id}'");
        if ($row['user_photo']) {
//            $tpl->set('{photo}', '/uploads/users/' . $row['user_id'] . '/' . $row['user_photo']);
            $params['photo'] = '/uploads/users/' . $row['user_id'] . '/' . $row['user_photo'];
        } else {
//            $tpl->set('{photo}', '/images/no_ava.gif');
            $params['photo'] = '/images/no_ava.gif';
        }
//        $tpl->compile('content');
//        Tools::AjaxTpl($tpl);
//        return view('bugs.add', $params);
        return _e_json(array(
            'status' => 1,
            'row' => view_data('bugs.add', $params)
        ));
    }

    /**
     *
     * @throws \Exception
     */
    public function create(): int
    {
        $db = $this->db();
        $logged = $this->logged();
        $user_info = $row = $this->user_info();
        $user_id = $user_info['user_id'];

        $request = (Request::getRequest()->getGlobal());
        if ($logged) {
            Antispam::Check(9, $user_id);
            $title = Validation::textFilter($request['title']);
            $text = Validation::textFilter($request['text']);
            $file = Validation::textFilter($request['file']);

            if (!$file) {
//            die();//////}
                $file = '';
            }

            $user_info = $this->user_info();
            $user_id = $user_info['user_id'];

            $server_time = \Sura\Time\Date::time();
            $date = Date::date_convert($server_time, 'Y-m-d H:i:s');

            $row = $db->query("INSERT INTO `bugs` (uids, title, text, date, add_date, images) VALUES ('{$user_id}', '{$title}', '{$text}', '{$date}','{$date}', '{$file}')");
            Antispam::LogInsert(9, $user_id);
            $id = $db->insert_id();
            $status = Status::OK;
        } else {
            $status = Status::BAD_LOGGED;

        }
        return _e_json(array(
            'status' => $status,
        ));
    }

    /**
     *
     * @throws \Exception
     */
    public function create_comment(): int
    {
        $db = $this->db();
        $logged = $this->logged();
        $user_info = $row = $this->user_info();
        $user_id = $user_info['user_id'];

        $request = (Request::getRequest()->getGlobal());

        if ($logged) {
            if ($user_id == $request['user_id']){
//            Antispam::Check(9, $user_id);
//                $title = $request['title'];
                $text = $request['text'];
                $status = $request['status'];
                $bug_id = $request['id'];
//            $file = Validation::textFilter($request['file']);

//            if (!$file) {
////            die();//////}
//                $file = '';
//            }

//                $user_info = $this->user_info();
//                $user_id = $user_info['user_id'];

                $server_time = \Sura\Time\Date::time();
                $date = Date::date_convert($server_time, 'Y-m-d H:i:s');

                $row = $db->query("INSERT INTO `bugs_comments` (author_user_id, text, add_date, status, bug_id) VALUES ('{$user_id}', '{$text}', '{$date}', '{$status}', '{$bug_id}')");
                $db->query("UPDATE `bugs` SET status = '{$status}', date = '{$date}' WHERE id = '{$bug_id}'");
//            Antispam::LogInsert(9, $user_id);
                $id = $db->insert_id();
                $status = Status::OK;
            } else {
                $status = Status::NOT_DATA;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ));
    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function load_img(): int
    {


        $image_tmp = $_FILES['uploadfile']['tmp_name'];
        $image_name = totranslit($_FILES['uploadfile']['name']);
        $server_time = \Sura\Time\Date::time();
        $image_rename = substr(md5($server_time + rand(1, 100000)), 0, 20);
        $image_size = $_FILES['uploadfile']['size'];
        $exp = explode(".", $image_name);
        $type = end($exp); // формат файла

        $max_size = 1024 * 5000;

        if ($image_size <= $max_size) {
            $allowed_files = explode(', ', 'jpg, jpeg, jpe, png, gif');
            if (in_array(strtolower($type), $allowed_files)) {
                $res_type = strtolower('.' . $type);
                $user_info = $this->user_info();
                $user_id = $user_info['user_id'];
                $upload_dir = __DIR__ . '/../../public/uploads/bugs/' . $user_id . '/';

                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                    }
                    @chmod($upload_dir, 0777);
                }

//                $rImg = $upload_dir.$image_rename.$res_type;

                if (move_uploaded_file($image_tmp, $upload_dir . $image_rename . $res_type)) {

                    //Создание оригинала
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $image = $manager->make($upload_dir . $image_rename . $res_type)->resize(600, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($upload_dir . $image_rename . '.webp', 85);

                    //Создание маленькой копии
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $image = $manager->make($upload_dir . $image_rename . $res_type)->resize(200, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($upload_dir . 'c_' . $image_rename . '.webp', 90);

                    unlink($upload_dir . $image_rename . $res_type);
                    $res_type = '.webp';

                    $img = ($user_id . '|' . $image_rename . $res_type);
                    $status = Status::OK;
                } else {
                    $img = '';
                    $status = Status::BAD_MOVE;
                }
            } else {
                $img = '';
                $status = Status::BAD_FORMAT;
            }
        } else {
            $img = '';
            $status = Status::BIG_SIZE;
        }
        return _e_json(array(
            'img' => $img,
            'status' => $status,
        ));
    }

    /**
     * @throws \JsonException
     */
    public function delete(): int
    {
        $logged = $this->logged();

        if ($logged) {
            $db = $this->db();

            $request = (Request::getRequest()->getGlobal());

            $id = (int)$request['id'];

            $row = $db->super_query("SELECT uids, images FROM `bugs` WHERE id = '{$id}'");
            if ($row['uids']){
                $user_info = $this->user_info();
                $user_id = $user_info['user_id'];

                $url_1 = __DIR__ . '/../../public/uploads/bugs/' . $row['uids'] . '/o_' . $row['images'];
                $url_2 = __DIR__ . '/../../public/uploads/bugs/' . $row['uids'] . '/' . $row['images'];

                unlink($url_1);
                unlink($url_2);

                $db->query("DELETE FROM `bugs` WHERE id = '{$id}'");
                $status = Status::OK;
            } else {
                $status = Status::NOT_FOUND;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ));
    }

    /**
     * @return int
     */
    public function open(): int
    {
        $params = array();
//        $tpl = $params['tpl'];
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $limit_num = 10;
        if ($request['page_cnt'] > 0) {
            $page_cnt = (int)$request['page_cnt'] * $limit_num;
        } else {
            $page_cnt = 0;
        }

        $where = "AND status = '1'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
        $params['menu'] = Menu::bugs();
//        $tpl->load_template('bugs/head.tpl');
//        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
//        $tpl->compile('content');

        return view('bugs.main', $params);
    }

    /**
     * @return int
     */
    public function complete(): int
    {
//        $tpl = $params['tpl'];
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $limit_num = 10;
        if ($request['page_cnt'] > 0) {
            $page_cnt = (int)$request['page_cnt'] * $limit_num;
        } else {
            $page_cnt = 0;
        }

        $where = "AND status = '2'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
        $params['menu'] = Menu::bugs();
//        $tpl->load_template('bugs/head.tpl');
//        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
//        $tpl->compile('content');

        return view('bugs.main', $params);
    }

    /**
     * @return int
     */
    public function close(): int
    {
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $limit_num = 10;
        if ($request['page_cnt'] > 0) {
            $page_cnt = (int)$request['page_cnt'] * $limit_num;
        } else {
            $page_cnt = 0;
        }

        $where = "AND status = '3'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
        $params['menu'] = Menu::bugs();
//        $tpl->load_template('bugs/head.tpl');
//        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
//        $tpl->compile('content');

        return view('bugs.main', $params);
    }

    /**
     * @return int
     */
    public function my(): int
    {
//        $tpl = $params['tpl'];
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $limit_num = 10;
        if ($request['page_cnt'] > 0) {
            $page_cnt = (int)$request['page_cnt'] * $limit_num;
        } else {
            $page_cnt = 0;
        }

        $user_info = $this->user_info();
        $user_id = $user_info['user_id'];

        $where = "AND uids = '{$user_id}'";

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.uids = tb2.user_id  {$where} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", 1);

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
//        $tpl->load_template('bugs/head.tpl');
//        $tpl->set('{load}', $tpl->result['bugs']);
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
//        $tpl->compile('content');

        $params['menu'] = Menu::bugs();
        return view('bugs.main', $params);
    }

    /**
     * @return int
     * @throws \JsonException
     */
    public function view(): int
    {
        $params = array();
//        $tpl = $params['tpl'];
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $id = (int)$request['id'];

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.id = '{$id}' AND tb1.uids = tb2.user_id", true);
//        $bugs = $db->super_query("SELECT admin_id, admin_text FROM `bugs` WHERE admin_id = '{$sql_['user_id']}'");

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
            $status = Status::OK;
        }else{
            $status = Status::NOT_FOUND;
        }

        //Admin
//        $tpl->set('{admin_text}', stripslashes($row['admin_text']));
//        $tpl->set('{admin_id}', stripslashes($row['admin_id']));

//        $user_info = $this->user_info();
//        $user_id = $user_info['user_id'];

        //user
//        if ($user_id == $row['uids']) {
////            $tpl->set('{delete}', '<a href="/" onClick="bugs.Delete(' . $row['id'] . '); return false;" style="color: #000000">Удалить</a>');
//        }
//        else {
////            $tpl->set('{delete}', '');
//        }

//        $tpl->set('{uid}', $row['user_id']);
//        if ($row['user_photo']) {
////            $tpl->set('{ava}', '/uploads/users/' . $row['user_id'] . '/50_' . $row['user_photo']);
//        }
//        else {
////            $tpl->set('{ava}', '/templates/Default/images/no_ava_50.png');
//        }
//        $tpl->set('{name}', $row['user_search_pref']);
//        $tpl->compile('content');

        $row = view_data('bugs.view', $params);

        return _e_json(array(
            'status' => $status,
            'row' => $row
        ));
    }

    /**
     * @return int
     */
    public function view_page(): int
    {
        $params = array();
        $db = $this->db();
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $id = (int)$path['2'];

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.id = '{$id}' AND tb1.uids = tb2.user_id", true);
//        $bugs = $db->super_query("SELECT admin_id, admin_text FROM `bugs` WHERE admin_id = '{$sql_['user_id']}'");
        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
        $params['menu'] = Menu::bugs();
        return view('bugs.view_page', $params);
    }

    /**
     * @return int
     * @ajax
     */
    public function index(): int
    {
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $limit_num = 10;
        if (isset($path['2']) AND $path['2'] > 0) {
            $page_cnt = (int)$path['2'] * $limit_num;
        } else {
            $page_cnt = 0;
        }

        $where_sql = '';
        $where_cat = '';

        $sql_ = $db->super_query("SELECT tb1.*, tb2.user_id, user_search_pref, user_photo, user_sex FROM `bugs` tb1, `users` tb2 WHERE tb1.uids = tb2.user_id {$where_sql} {$where_cat} ORDER by `date` DESC LIMIT {$page_cnt}, {$limit_num}", true);

        if ($sql_) {
            $params['bugs'] = (new \App\Models\Bugs)->getData($sql_);
        }
//        $query = Validation::strip_data(urldecode($request['query']));
//        Tools::navigation($page_cnt, $limit_num, '/index.php'.$query.'&page_cnt=');
        $params['menu'] = Menu::bugs();
//        $params['navigation'] = \Sura\Libs\Tools::navigation($page_cnt, $limit_num, '/bugs/');
//        $tpl->compile('content');

        return view('bugs.main', $params);
    }
}