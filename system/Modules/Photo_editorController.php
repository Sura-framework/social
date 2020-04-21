<?php
/* 
	Appointment: Фоторедактор
	File: photo_editor.php 
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
use System\Classes\Thumb;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;

class Photo_editorController extends Module{

    /**
     * Отмена редактирования
     */
    public function close($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $tpl->load_template('photos/editor_close.tpl');
            $tpl->set('{photo}', $_GET['image']);
            $tpl->compile('content');

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    /**
     * Сохранение отредактированой фотки
     */
    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){

            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            $config = include __DIR__.'/../data/config.php';

            //Разришенные форматы
            $allowed_files = explode(', ', $config['photo_format']);

            $res_image = $_GET['image'];
            $format = end(explode('.', $res_image));
            $pid = $_GET['pid'];

            if(stripos($_SERVER['HTTP_REFERER'], 'pixlr.com') !== false AND $pid AND $format){

                //Выодим информацию о фото
                $row = $db->super_query("SELECT photo_name, album_id FROM `".PREFIX."_photos` WHERE user_id = '{$user_id}' AND id = '{$pid}'");

                //Проверям если, формат верный то пропускаем
                if(in_array(strtolower($format), $allowed_files) AND $row['photo_name']){

                    $upload_dir = __DIR__."/../../public/uploads/users/{$user_id}/albums/{$row['album_id']}/";
                    $image_rename = $row['photo_name'];

                    copy($res_image, $upload_dir."{$row['photo_name']}");

                    $manager = new ImageManager(array('driver' => 'gd'));

                    //Создание оригинала
                    $image = $manager->make($upload_dir.$image_rename)->resize(770, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($upload_dir.$image_rename, 75);

                    //Создание маленькой копии
                    $image = $manager->make($upload_dir.$image_rename)->resize(140, 100);
                    $image->save($upload_dir.'c_'.$image_rename, 90);

                    $tpl->load_template('photos/editor.tpl');
                    $server_time = intval($_SERVER['REQUEST_TIME']);
                    $tpl->set('{photo}', "/uploads/users/{$user_id}/albums/{$row['album_id']}/{$row['photo_name']}?{$server_time}");
                    $tpl->compile('content');

                    Tools::AjaxTpl($tpl);

                    $params['tpl'] = $tpl;
                    Page::generate($params);
                    return true;
                }
            } else
                echo 'Hacking attempt!';
        }
    }
}