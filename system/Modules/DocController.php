<?php
/* 
	Appointment: Документы
	File: doc.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Validation;

class DocController extends Module{

    /**
     * Загрузка файла
     */
    public function upload($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            //Получаем данные о фотографии
            $file_tmp = $_FILES['uploadfile']['tmp_name'];
            $file_name = $_FILES['uploadfile']['name']; // оригинальное название для оприделения формата
            $file_size = $_FILES['uploadfile']['size']; // размер файла
            $type = end(explode(".", $file_name)); // формат файла

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
                        @mkdir($upload_dir, 0777);
                        @chmod($upload_dir, 0777);
                    }

                    $server_time = intval($_SERVER['REQUEST_TIME']);
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
                        $db->query("UPDATE `".PREFIX."_users` SET user_doc_num = user_doc_num+1 WHERE user_id = '{$user_id}'");

                        if(!$file_name) $file_name = 'Без названия.'.$res_type;

                        $strLn = strlen($file_name);
                        if($strLn > 50){
                            $file_name = str_replace('.'.$res_type, '', $file_name);
                            $file_name = substr($file_name, 0, 50).'...'.$res_type;
                        }

                        $server_time = intval($_SERVER['REQUEST_TIME']);
                        //Вставляем файл в БД
                        $db->query("INSERT INTO `".PREFIX."_doc` SET duser_id = '{$user_id}', dname = '{$file_name}', dsize = '{$dsize}', ddate = '{$server_time}', ddownload_name = '{$downl_file_name}{$res_type}'");

                        echo $file_name.'"'.$db->insert_id().'"'.$dsize.'"'.strtolower($type).'"'.langdate('сегодня в H:i', $server_time);

                        Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|user_{$user_id}/docs");

                    }

                } else
                    echo 1;

            }

            exit;
        }
    }

    /**
     * Удаление документа
     */
    public function del($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];


            Tools::NoAjaxQuery();

            $did = intval($_POST['did']);

            $row = $db->super_query("SELECT duser_id, ddownload_name FROM `".PREFIX."_doc` WHERE did = '{$did}'");

            if($row['duser_id'] == $user_id){

                @unlink(__DIR__."/../../public/uploads/doc/{$user_id}/".$row['ddownload_name']);

                $db->query("DELETE FROM `".PREFIX."_doc` WHERE did = '{$did}'");

                //Обновляем кол-во док. у юзера
                $db->query("UPDATE `".PREFIX."_users` SET user_doc_num = user_doc_num-1 WHERE user_id = '{$user_id}'");

                Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|user_{$user_id}/docs");
                Cache::mozg_clear_cache_file("wall/doc{$did}");

            }

            exit;
        }
    }

    /**
     * Сохранение отред.данных
     */
    public function editsave($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $did = intval($_POST['did']);
            $name = Validation::ajax_utf8(Validation::textFilter($_POST['name'], false, true));
            $strLn = strlen($name);
            if($strLn > 50)
                $name = substr($name, 0, 50);

            $row = $db->super_query("SELECT duser_id FROM `".PREFIX."_doc` WHERE did = '{$did}'");

            if($row['duser_id'] == $user_id AND isset($name) AND !empty($name)){

                $db->query("UPDATE `".PREFIX."_doc`SET dname = '{$name}' WHERE did = '{$did}'");

                Cache::mozg_mass_clear_cache_file("user_{$user_id}/profile_{$user_id}|user_{$user_id}/docs");
                Cache::mozg_clear_cache_file("wall/doc{$did}");

            }

            exit;
        }
    }

    /**
     * Скачивание документа с сервера
     */
    public function download($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $did = intval($_GET['did']);

            $row = $db->super_query("SELECT duser_id, ddownload_name, dname FROM `".PREFIX."_doc` WHERE did = '{$did}'");

            if($row){

                $filename = str_replace(array('/', '\\', 'php', 'tpl'), '', $row['ddownload_name']);
                define('FILE_DIR', "uploads/doc/{$row['duser_id']}/");

                include __DIR__ . '/../Classes/download.php';

                $config['files_max_speed'] = 0;

                $format = end(explode('.', $filename));

                $row['dname'] = str_replace('.'.$format, '', $row['dname']).'.'.$format;

                if(file_exists(FILE_DIR.$filename) AND $filename){

                    $file = new download(FILE_DIR.$filename, $row['dname'], 1, $config['files_max_speed']);
                    $file->download_file();

                }

            } else
                header("Location: /index.php");

            exit;
        }
    }

    /**
     * Страница всех загруженных документов
     */
    public function list($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $metatags['title'] = 'Документы';

            $sql_limit = 20;

            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt'])*$sql_limit;
            else $page_cnt = 0;

            if($page_cnt)
                Tools::NoAjaxQuery();

            $sql_ = $db->super_query("SELECT did, dname, ddate, ddownload_name, dsize FROM `".PREFIX."_doc` WHERE duser_id = '{$user_id}' ORDER by `ddate` DESC LIMIT {$page_cnt}, {$sql_limit}", 1);

            $rowUser = $db->super_query("SELECT user_doc_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            if(!$page_cnt){

                $tpl->load_template('doc/top_list.tpl');
                $tpl->set('{doc-num}', $rowUser['user_doc_num']);
                $tpl->compile('content');

            }

            $tpl->load_template('doc/doc_list.tpl');
            foreach($sql_ as $row){

                $tpl->set('{name}', stripslashes($row['dname']));
                $tpl->set('{format}', end(explode('.', $row['ddownload_name'])));
                $tpl->set('{did}', $row['did']);
                $tpl->set('{size}', $row['dsize']);
                $date = megaDate(strtotime($row['ddate']));
                $tpl->set('{date}', $date);

                $tpl->compile('content');
            }

            if($page_cnt){

                Tools::AjaxTpl($tpl);
                exit;

            }

            if($rowUser['user_doc_num'] > 20){

                $tpl->load_template('doc/bottom_list.tpl');
                $tpl->compile('content');

            }

        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }

    /**
     * Страница всех загруженных документов для прикрипления BOX
     */
    public function index()
    {
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            Tools::NoAjaxQuery();

            $sql_limit = 20;

            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt'])*$sql_limit;
            else $page_cnt = 0;

            $sql_ = $db->super_query("SELECT did, dname, ddate, ddownload_name FROM `".PREFIX."_doc` WHERE duser_id = '{$user_id}' ORDER by `ddate` DESC LIMIT {$page_cnt}, {$sql_limit}", 1);

            if(!$page_cnt){
                $rowUser = $db->super_query("SELECT user_doc_num FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

                $tpl->load_template('doc/top.tpl');
                $tpl->set('{doc-num}', $rowUser['user_doc_num']);
                $tpl->compile('content');
            }

            $tpl->load_template('doc/doc.tpl');
            foreach($sql_ as $row){

                $tpl->set('{name}', stripslashes($row['dname']));
                $tpl->set('{format}', end(explode('.', $row['ddownload_name'])));
                $tpl->set('{did}', $row['did']);

                $date = megaDate(strtotime($row['ddate']));
                $tpl->set('{date}', $date);

                $tpl->compile('content');
            }

            if(!$page_cnt AND $rowUser['user_doc_num'] > 20){
                $tpl->load_template('doc/bottom.tpl');
                $tpl->compile('content');
            }

            Tools::AjaxTpl($tpl);

            exit;

            $tpl->clear();
            $db->free();

        } else
            echo 'no_log';

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}