<?php

namespace App\Modules;

use Sura\Libs\Download;
use Sura\Libs\Langs;
use Sura\Libs\Request;
use Sura\Libs\Status;
use Sura\Libs\Validation;

class DocController extends Module{

    /**
     * Загрузка файла
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function upload(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];



            //Получаем данные о фотографии
            $file_tmp = $_FILES['uploadfile']['tmp_name'];
            $file_name = $_FILES['uploadfile']['name']; // оригинальное название для оприделения формата
            $file_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $file_name);
            $type = end($array); // формат файла

            //Разришенные форматы
            $allowed_files = array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'rtf', 'pdf', 'png', 'jpg', 'gif', 'psd', 'mp3', 'djvu', 'fb2', 'ps', 'jpeg', 'txt');

            //Проверям если, формат верный то пропускаем
            if(in_array(strtolower($type), $allowed_files)){

                if($file_size < 10000000){

                    $res_type = strtolower('.'.$type);

                    //Директория загрузки
                    $upload_dir = __DIR__."/../../public/uploads/doc/{$user_id}/";

                    //Если нет папки юзера, то создаём её
                    if(!is_dir($upload_dir)){
                        if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                        }
                        @chmod($upload_dir, 0777);
                    }

                    $server_time = \Sura\Time\Date::time();
                    $downl_file_name = substr(md5($file_name.rand(0, 1000).$server_time), 0, 25);

                    //Загружаем сам файл
                    if(move_uploaded_file($file_tmp, $upload_dir.$downl_file_name.$res_type)){

                        function formatsize($file_size){
                            if($file_size >= 1073741824){
                                $file_size = round($file_size / 1073741824 * 100 ) / 100 ." Гб";
                            } elseif($file_size >= 1048576){
                                $file_size = round($file_size / 1048576 * 100 ) / 100 ." Мб";
                            } elseif($file_size >= 1024){
                                $file_size = round($file_size / 1024 * 100 ) / 100 ." Кб";
                            } else {
                                $file_size = $file_size." б";
                            }
                            return $file_size;
                        }

                        $dsize = formatsize($file_size);
                        $file_name = Validation::textFilter($file_name, false, true);

                        //Обновляем кол-во док. у юзера
                        $db->query("UPDATE `users` SET user_doc_num = user_doc_num+1 WHERE user_id = '{$user_id}'");

                        if(!$file_name) {
                            $file_name = 'Без названия.' . $res_type;
                        }

                        $strLn = strlen($file_name);
                        if($strLn > 50){
                            $file_name = str_replace('.'.$res_type, '', $file_name);
                            $file_name = substr($file_name, 0, 50).'...'.$res_type;
                        }

                        $server_time = \Sura\Time\Date::time();
                        //Вставляем файл в БД
                        $db->query("INSERT INTO `doc` SET duser_id = '{$user_id}', dname = '{$file_name}', dsize = '{$dsize}', ddate = '{$server_time}', ddownload_name = '{$downl_file_name}{$res_type}'");

                        echo $file_name.'"'.$db->insert_id().'"'.$dsize.'"'.strtolower($type).'"'.Langs::lang_date('сегодня в H:i', $server_time);

                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $cache->remove("{$user_id}/profile_{$user_id}");
                        $cache->remove("{$user_id}/docs");

                        $status = Status::OK;
                    }else{
                        $status = Status::BAD_MOVE;
                    }
                }else{
                    $status = Status::BIG_SIZE;
                }
            }else{
                $status = Status::BAD_FORMAT;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Удаление документа
     * @throws \JsonException
     * @throws \Throwable
     */
    public function del(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $did = (int)$request['did'];

            $row = $db->super_query("SELECT duser_id, ddownload_name FROM `doc` WHERE did = '{$did}'");

            if($row['duser_id'] == $user_id){

                @unlink(__DIR__."/../../public/uploads/doc/{$user_id}/".$row['ddownload_name']);

                $db->query("DELETE FROM `doc` WHERE did = '{$did}'");

                //Обновляем кол-во док. у юзера
                $db->query("UPDATE `users` SET user_doc_num = user_doc_num-1 WHERE user_id = '{$user_id}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache->remove("{$user_id}/docs");
                $cache = new \Sura\Cache\Cache($storage, 'wall');
                $cache->remove("wall/doc{$did}");

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
     * Сохранение отред.данных
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function editsave(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $did = (int)$request['did'];
            $name = Validation::ajax_utf8(Validation::textFilter($request['name'], false, true));
            $strLn = strlen($name);
            if($strLn > 50)
                $name = substr($name, 0, 50);

            $row = $db->super_query("SELECT duser_id FROM `doc` WHERE did = '{$did}'");

            if($row['duser_id'] == $user_id AND isset($name) AND !empty($name)){

                $db->query("UPDATE `doc`SET dname = '{$name}' WHERE did = '{$did}'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove("{$user_id}/profile_{$user_id}");
                $cache->remove("{$user_id}/docs");
                $cache = new \Sura\Cache\Cache($storage, 'wall');
                $cache->remove("doc{$did}");

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
     * Скачивание документа с сервера
     * @throws \JsonException
     */
    public function download(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
//            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $did = (int)$request['did'];

            $row = $db->super_query("SELECT duser_id, ddownload_name, dname FROM `doc` WHERE did = '{$did}'");

            if($row){

                $filename = str_replace(array('/', '\\', 'php', 'tpl'), '', $row['ddownload_name']);
                define('FILE_DIR', "uploads/doc/{$row['duser_id']}/");

                include __DIR__ . '/../Classes/download.php';

                $config['files_max_speed'] = 0;

                $array = explode('.', $filename);
                $format = end($array);

                $row['dname'] = str_replace('.'.$format, '', $row['dname']).'.'.$format;

                if(file_exists(FILE_DIR.$filename) AND $filename){

                    $file = new Download(FILE_DIR.$filename, $row['dname'], 1, $config['files_max_speed']);
                    $file->download_file();

                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
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
     * Страница всех загруженных документов
     */
    public function list(): int
    {
        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];

            $params['title'] = 'Документы'.' | Sura';

            $sql_limit = 20;

            if($request['page_cnt'] > 0) $page_cnt = (int)$request['page_cnt'] *$sql_limit;
            else $page_cnt = 0;



            $sql_ = $db->super_query("SELECT did, dname, ddate, ddownload_name, dsize FROM `doc` WHERE duser_id = '{$user_id}' ORDER by `ddate` DESC LIMIT {$page_cnt}, {$sql_limit}", 1);

            $rowUser = $db->super_query("SELECT user_doc_num FROM `users` WHERE user_id = '{$user_id}'");

            if(!$page_cnt){

                $tpl->load_template('doc/top_list.tpl');
                $tpl->set('{doc-num}', $rowUser['user_doc_num']);
                $tpl->compile('content');

            }

            $tpl->load_template('doc/doc_list.tpl');
            foreach($sql_ as $row){

                $tpl->set('{name}', stripslashes($row['dname']));
                $array = explode('.', $row['ddownload_name']);
                $tpl->set('{format}', end($array));
                $tpl->set('{did}', $row['did']);
                $tpl->set('{size}', $row['dsize']);
                $date = \Sura\Time\Date::megaDate(strtotime($row['ddate']));
                $tpl->set('{date}', $date);

                $tpl->compile('content');
            }

            if($page_cnt){


                exit;

            }

            if($rowUser['user_doc_num'] > 20){

                $tpl->load_template('doc/bottom_list.tpl');
                $tpl->compile('content');

            }

        }

        return view('info.info', $params);
    }

    /**
     * Страница всех загруженных документов для прикрепления BOX
     * @return int
     */
    public function index(): int
    {
//        $tpl = $params['tpl'];

//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $sql_limit = 20;

            if($request['page_cnt'] > 0) $page_cnt = (int)$request['page_cnt'] *$sql_limit;
            else $page_cnt = 0;

            $sql_ = $db->super_query("SELECT did, dname, ddate, ddownload_name FROM `doc` WHERE duser_id = '{$user_id}' ORDER by `ddate` DESC LIMIT {$page_cnt}, {$sql_limit}", 1);

            if(!$page_cnt){
                $rowUser = $db->super_query("SELECT user_doc_num FROM `users` WHERE user_id = '{$user_id}'");

//                $tpl->load_template('doc/top.tpl');
//                $tpl->set('{doc-num}', $rowUser['user_doc_num']);
//                $tpl->compile('content');
            }

//            $tpl->load_template('doc/doc.tpl');
            foreach($sql_ as $row){

//                $tpl->set('{name}', stripslashes($row['dname']));
                $array = explode('.', $row['ddownload_name']);
//                $tpl->set('{format}', end($array));
//                $tpl->set('{did}', $row['did']);

                $date = \Sura\Time\Date::megaDate(strtotime($row['ddate']));
//                $tpl->set('{date}', $date);

//                $tpl->compile('content');
            }

            if(!$page_cnt AND $rowUser['user_doc_num'] > 20){
//                $tpl->load_template('doc/bottom.tpl');
//                $tpl->compile('content');
            }
            return view('docs.box', $params);

        } else
            echo 'no_log';

        return view('info.info', $params);
    }
}