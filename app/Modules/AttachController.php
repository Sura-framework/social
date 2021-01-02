<?php

namespace App\Modules;

use Intervention\Image\ImageManager;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class AttachController extends Module{

    /**
     * Загрузка картинок при прикреплении файлов со стены,
     * заметок, или сообщений
     */
    public function index(): string
    {
//        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();



        if($logged){
            $user_id = $user_info['user_id'];

            //Если нет папки альбома, то создаём её
            $upload_dir = __DIR__."/../../public/uploads/attach/{$user_id}/";
            if(!is_dir($upload_dir)){
                if (!mkdir($upload_dir, 0777) && !is_dir($upload_dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
                }
                @chmod($upload_dir, 0777);
            }

            //Разришенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Получаем данные о фотографии
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = \Sura\Libs\Tools::time();
            $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя фотографии
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $image_name);
            $type = end($array); // формат файла

            //Проверям если, формат верный то пропускаем
            if(in_array(strtolower($type), $allowed_files)){
                if($image_size < 5000000){
                    $res_type = strtolower('.'.$type);

                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                        $manager = new ImageManager(array('driver' => 'gd'));

                        //Создание оригинала
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.$image_rename.'.webp', 75);

                        //Создание маленькой копии
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(140, 100);
                        $image->save($upload_dir.'c_'.$image_rename.'.webp', 90);

                        unlink($upload_dir.$image_rename.$res_type);
                        $res_type = '.webp';

                        //Вставляем фотографию
                        $db->query("INSERT INTO `attach` SET photo = '{$image_rename}{$res_type}', ouser_id = '{$user_id}', add_date = '{$server_time}'");
//                        $ins_id = $db->insert_id();

                        $config = Settings::loadsettings();

                        $img_url = $config['home_url'].'uploads/attach/'.$user_id.'/c_'.$image_rename.$res_type;

                        //Результат для ответа
                        echo $image_rename.$res_type.'|||'.$img_url.'|||'.$user_id;
                    } else
                        return _e('big_size');
                } else
                    return _e( 'big_size');
            } else
                return _e( 'bad_format');
        } else
            return _e( 'no_log');
    }
}
