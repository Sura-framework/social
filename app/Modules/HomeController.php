<?php

namespace App\Modules;

use App\Services\Cache;
use Exception;
use Sura\Libs\Request;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class HomeController extends Module{

    /**
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function index(array $params)
    {
        $logged = $this->logged();

        if ($logged == true || isset($logged)){
            $params['title'] = 'Новости'.' | Sura';
//            $params['title'] = 'Sura';

//            var_dump($params);
//            exit();
            return (new FeedController)->feed($params);
        }else{

            $params['title'] = 'Sura';
            try {
                return view('reg', $params);
            } catch (Exception $e) {
                echo 'error';
            }
        }
    }

    /**
     *
     */
    public function login()
    {
        $logged = $this->logged();
        $db = $this->db();
        $token = $_POST['token'].'|'.$_SERVER['REMOTE_ADDR'];
        $check_token = false;
        if ($token == $_SESSION['_mytoken'] AND $check_token == true){
            $user_token = true;
        }elseif ($check_token == false){
            $user_token = true;
        }else{
            $user_token = false;
        }

        //Если данные поступили через пост запрос и пользователь не авторизован
        if(isset($_POST['login']) AND $logged == false AND $user_token == true ){

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

            if($errors == 0) {
                $check_user = $db->super_query("SELECT user_id, user_password FROM `users` WHERE user_email = '".$email."'");

                //Если есть юзер то пропускаем
                if($check_user['user_password'] == true AND is_array($check_user) AND password_verify($password, $check_user['user_password']) == true){
                    //Hash ID
                    $_IP = $_SERVER['REMOTE_ADDR'];
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    //Обновляем хэш входа
                    $db->query("UPDATE `users` SET user_hash = '".$hash."' WHERE user_id = '".$check_user['user_id']."'");

                    //Удаляем все рание события
                    $db->query("DELETE FROM `updates` WHERE for_user_id = '{$check_user['user_id']}'");

                    //Устанавливаем в сессию ИД юзера
                    $_SESSION['user_id'] = intval($check_user['user_id']);

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", intval($check_user['user_id']), 365);
//                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hash", $hash, 365);

                    //Вставляем лог в бд
                    $_BROWSER = null;
                    $db->query("UPDATE `log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");

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

    /**
     *
     */
    public function Test()
    {
        $key = "system/all_country";
        $Cache = Cache::initialize();

        try {
            $item = $Cache->get($key, $default = null);
            print_r($item);

            $Cache->delete($key);

            $item2 = $Cache->get($key, $default = null);
            echo '<br>';
            echo '<br>';
            print_r($item2);
            echo '<br>end| ';

            $s = 1;
        }catch (Exception $e){
            $db = $this->db();
            $item = $db->super_query("SELECT * FROM `country` ORDER by `name` ", true);
            $Cache->set($key, $item);
            $s = 2;
        }

        echo '<br>';

        print_r($item);

        echo $s;
    }

}