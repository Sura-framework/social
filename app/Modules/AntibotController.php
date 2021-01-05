<?php

namespace App\Modules;


class AntibotController extends Module{

    /**
     * создание капчи
     */
    public function index(): bool
    {

        //session_start();

        //error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

//        if(clean_url($_SERVER['HTTP_REFERER']) != clean_url($_SERVER['HTTP_HOST']))
//            die("Hacking attempt!");

        $width = 120;				//Ширина изображения
        $height = 50;				//Высота изображения
        $font_size = 16;   			//Размер шрифта
        $let_amount = 5;			//Количество символов, которые нужно набрать
        $font = __DIR__.'/../../vendor/sura/framework/src/fonts/cour.ttf';	//Путь к шрифту

        //набор символов
        $letters = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

        //Цвета для фона
        $background_color = array(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));

        //Цвета для обводки
        $foreground_color = array(mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

        $src = imagecreatetruecolor($width,$height); //создаем изображение

        $fon = imagecolorallocate($src, $background_color['0'], $background_color['1'], $background_color['2']); //создаем фон

        imagefill($src,0,0,$fon); //заливаем изображение фоном

        //то же самое для основных букв
        $cod = [];
        for($i=0; $i < $let_amount; $i++){
            $color = imagecolorallocatealpha($src, $foreground_color['0'], $foreground_color['1'], $foreground_color['2'], rand(20,40)); //Цвет шрифта
            $letter = $letters[rand(0,count($letters)-1)];
            $size = rand(25,34);
            $x = ($i+1)*$font_size + rand(5,9); //даем каждому символу случайное смещение
            $y = (($height*2)/3) + rand(0,7);
            $cod[] = $letter; //запоминаем код
            imagettftext($src,$size,rand(0,20),$x,$y,$color,$font,$letter);
        }

        $foreground = imagecolorallocate($src, $foreground_color['0'], $foreground_color['1'], $foreground_color['2']);

        imageline($src, 0, 0,  $width, 0, $foreground);
        imageline($src, 0, 0,  0, $height, $foreground);
        imageline($src, 0, $height-1,  $width, $height-1, $foreground);
        imageline($src, $width-1, 0,  $width-1, $height, $foreground);

        $cod = implode("",$cod); //переводим код в строку

        header("Content-type: image/gif"); //выводим готовую картинку

        $_SESSION['sec_code'] = $cod; //Добавляем код в сессию
        imagegif($src);

        return true;
    }

    /**
     *  проверка капчи
     */
    public static function code(): string
    {
        session_start();
//        error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

//        if(clean_url($_SERVER['HTTP_REFERER']) != clean_url($_SERVER['HTTP_HOST']))
//            die("Hacking attempt!");

        if($_GET['user_code'] == $_SESSION['sec_code']){
            return _e('ok');
        } else {
            return _e( 'no');
        }
    }
}