<?php

namespace App\Modules;

use App\Modules\FeedController;
use Sura\Libs\Auth;
use Sura\Libs\Blade;
use Sura\Libs\Cache;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Password;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Validation;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Modules\Module;
use App\Modules\NewsController;


class HomeController extends Module{

    /**
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function index($params)
    {
//        var_dump($params);
        //$logged = $this->logged();
        $logged = $params['user']['logged'];
        $params['title'] = 'Новости'.' | Sura';


        if ($logged == true){
           return (new FeedController)->feed($params);
        }else{
            $data  = array();
            $params['title'] = 'Sura';

            return view('reg', $params);
        }
    }

    public function login($params)
    {
        $logged = $params['user']['logged'];
        $db = $this->db();
        $token = $_POST['token'].'|'.$_SERVER['REMOTE_ADDR'];
        //Если данные поступили через пост запрос и пользователь не авторизован
        if(isset($_POST['login']) AND $logged == false AND $token == $_SESSION['_mytoken'] ){

            $errors = 0;
            $err = '';

            //Приготавливаем данные

            //Проверка E-mail
            $email = strip_tags($_POST['email']);
            if(Validation::check_email($email) == false)
            {
                $errors++;
                $err .= 'mail|'.$email;
            }

            //Проверка Пароля
            if (!empty($_POST['pass'])){
                $password = GetVar($_POST['pass']);
            }else{
                $password = NUlL;
                $errors++;
                $err .= 'password|n\a';
            }

            // if( _strlen( $name, $config['charset'] ) > 40 OR _strlen(trim($name), $config['charset']) < 3) $stop = 'error';
//            $lang = $this->get_langs();

            if($errors == 0) {
                $check_user = $db->super_query("SELECT user_id, user_password FROM `users` WHERE user_email = '".$email."'");

                //Если есть юзер то пропускаем
                if(is_array($check_user) AND password_verify($password, $check_user['user_password']) == true){
                    //Hash ID
                    $_IP = null;
                    $hid = $password.md5(md5($_IP));

                    //Обновляем хэш входа
                    $db->query("UPDATE `users` SET user_hid = '".$hid."' WHERE user_id = '".$check_user['user_id']."'");

                    //Удаляем все рание события
                    $db->query("DELETE FROM `updates` WHERE for_user_id = '{$check_user['user_id']}'");

                    //Устанавливаем в сессию ИД юзера
                    $_SESSION['user_id'] = intval($check_user['user_id']);

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", intval($check_user['user_id']), 365);
                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hid", $hid, 365);

                    //Вставляем лог в бд
                    $_BROWSER = null;
                    $db->query("UPDATE `log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");

                    //$config = Settings::loadsettings();

                       // header('Location: /');
                    echo 'ok|'.$check_user['user_id'];
                } else{
                    echo 'error|no_val|no_user|'.$password;
                    //var_dump(password_verify($password, $check_user['user_password']));
                    //msgbox('', $lang['not_loggin'].'<br /><br /><a href="/restore/" onClick="Page.Go(this.href); return false">Забыли пароль?</a>', 'info_red');
                }
            }else{
                echo 'error|no_val|'.$err;
            }
        }else{
            echo 'error|no_val|';
        }
    }
}