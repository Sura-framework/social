<?php

namespace App\Modules;

use App\Contracts\Modules\HomeInterface;
use Exception;
use Sura\Libs\Request;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class HomeController extends Module implements HomeInterface {

    /**
     * Главная страница
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function index(array $params): string
    {
        $logged = $this->logged();

        if ($logged === true || isset($logged)){
            $params['title'] = 'Новости'.' | Sura';
            try {
//                throw new Exception('Деление на ноль.');

                // Если метод delete() из модели возвращает true
                return (new FeedController)->feed($params);

            } catch (Exception $e) {
                // Если false - ловим брошенное из модели исключение
                echo $e->getMessage();
                // Или вывести в уведомление через сессию, например
                // Session::set('error', $e->getMessage());
            }
        }

        $params['title'] = 'Sura';

        try {
            return view('reg', $params);
        } catch (Exception $e) {
            return  _e('err');
        }
    }

    /**
     * Вход на сайт
     *
     * @param array $params
     * @return string
     */
    public function login(array $params): string
    {
        $logged = $this->logged();
        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $token = $request['token'].'|'.$_SERVER['REMOTE_ADDR'];
        $check_token = false;
        if ($token == $_SESSION['_mytoken'] AND $check_token == true){
            $user_token = true;
        }elseif ($check_token == false){
            $user_token = true;
        }else{
            $user_token = false;
        }

        //Если данные поступили через пост запрос и пользователь не авторизован
        if(isset($request['login']) AND $logged == false AND $user_token == true ){

            $errors = 0;
            $err = '';

            //Приготавливаем данные

            //Проверка E-mail
            $email = strip_tags($request['email']);
            if(Validation::check_email($email) == false)
            {
                $errors++;
                $err .= 'mail|'.$email;
            }

            //Проверка Пароля
            if (!empty($request['pass'])){
                $password = GetVar($request['pass']);
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
                    $_SESSION['user_id'] = (int)$check_user['user_id'];

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", (int)$check_user['user_id'], 365);
//                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hash", $hash, 365);

                    //Вставляем лог в бд
                    $_BROWSER = null;
                    $db->query("UPDATE `log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");

                       // header('Location: /');
                    return _e('ok|'.$check_user['user_id']);
                } else{
                    return _e( 'error|no_val|no_user|'.$password);
                    //var_dump(password_verify($password, $check_user['user_password']));
                    //msgbox('', $lang['not_loggin'].'<br /><br /><a href="/restore/" onClick="Page.Go(this.href); return false">Забыли пароль?</a>', 'info_red');
                }
            }else{
                return _e( 'error|no_val|'.$err);
            }
        }
        else{
            return _e( 'error|no_val|');
        }
    }

    /**
     * Test page
     *
     * @param array $params
     */
    public function Test(array $params): void
    {

    }

}