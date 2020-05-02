<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Classes\Thumb;
use Sura\Libs\Langs;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class Attach_groupsController extends Module{

    public function index($params)
    {
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxQuery();

        if($logged){
            $public_id = intval($_GET['public_id']);

            $rowPublic = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$public_id}'");

            if(stripos($rowPublic['admin'], "u{$user_info['user_id']}|") !== false){
                //Если нет папки альбома, то создаём её
                $upload_dir = __DIR__."/../../public/uploads/groups/{$public_id}/photos/";

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
                            echo $image_rename.$res_type;

                        } else
                            echo 'big_size';
                    } else
                        echo 'big_size';
                } else
                    echo 'bad_format';
            }
        } else
            echo 'no_log';

        die();

    }
}
