<?php
/* 
	Appointment: Загрузка картинок при прикриплении файлов со стены, заметок, или сообщений
	File: attach.php 
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
use System\Libs\Langs;
use System\Libs\Registry;
use System\Libs\Tools;
use System\Libs\Gramatic;

class AttachController extends Module{

    public function index($params)
    {
//        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxQuery();

        if($logged){
            $user_id = $user_info['user_id'];

            //Если нет папки альбома, то создаём её
            $upload_dir = __DIR__."/../../public/uploads/attach/{$user_id}/";
            if(!is_dir($upload_dir)){
                @mkdir($upload_dir, 0777);
                @chmod($upload_dir, 0777);
            }

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
                        $db->query("INSERT INTO `".PREFIX."_attach` SET photo = '{$image_rename}{$res_type}', ouser_id = '{$user_id}', add_date = '{$server_time}'");
                        $ins_id = $db->insert_id();

                        $config = include __DIR__.'/../data/config.php';

                        $img_url = $config['home_url'].'uploads/attach/'.$user_id.'/c_'.$image_rename.$res_type;

                        //Результат для ответа
                        echo $image_rename.$res_type.'|||'.$img_url.'|||'.$user_id;
                    } else
                        echo 'big_size';
                } else
                    echo 'big_size';
            } else
                echo 'bad_format';
        } else
            echo 'no_log';

        die();

    }
}
