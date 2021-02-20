<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Libs\Request;
use Sura\Libs\Settings;

class Photo_editorController extends Module{

    /**
     * Отмена редактирования
     *
     * @return int
     */
    public function close(): int
    {
//        $tpl = $params['tpl'];
//        $lang = $this->get_langs();
//        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $request = (Request::getRequest()->getGlobal());

//            $user_id = $user_info['user_id'];

//            $tpl->load_template('photos/editor_close.tpl');
//            $tpl->set('{photo}', $request['image']);
//            $tpl->compile('content');

//            Tools::AjaxTpl($tpl);

            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Сохранение отредактированной фотки
     *
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

            $request = (Request::getRequest()->getGlobal());

            $user_id = $user_info['user_id'];

            $config = Settings::load();

            //Разришенные форматы
            $allowed_files = explode(', ', $config['photo_format']);

            $res_image = $request['image'];
            $array = explode('.', $res_image);
            $format = end($array);
            $pid = $request['pid'];

            if(stripos($request['HTTP_REFERER'], 'pixlr.com') !== false AND $pid AND $format){

                //Выодим информацию о фото
                $row = $db->super_query("SELECT photo_name, album_id FROM `photos` WHERE user_id = '{$user_id}' AND id = '{$pid}'");

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

//                    $tpl->load_template('photos/editor.tpl');
                    $server_time = \Sura\Time\Date::time();
//                    $tpl->set('{photo}', "/uploads/users/{$user_id}/albums/{$row['album_id']}/{$row['photo_name']}?{$server_time}");
//                    $tpl->compile('content');

//                    Tools::AjaxTpl($tpl);

                    return view('info.info', $params);
                }
            } else
                return view('info.info', $params);
        }
        return view('info.info', $params);
    }
}