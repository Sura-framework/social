<?php
/* 
	Appointment: Завершение регистрации
	File: register.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Auth;
use System\Libs\Cache;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Password;
use System\Libs\Registry;
use System\Libs\Request;
use System\Libs\Tools;
use System\Libs\Validation;
use thiagoalessio\TesseractOCR\TesseractOCR;

class HomeController extends Module{

    /**
     * @param array $params
     * @return bool
     */
    public function index($params)
    {
//        var_dump($params);
        //$logged = $this->logged();
        $logged = $params['user']['logged'];
        if ($logged == true){
            (new NewsController)->index($params);
        }else{
            //################## Загружаем Страны ##################//
            try {
                $sql_country_serialize = Cache::system_cache('all_country');
                $sql_country = unserialize($sql_country_serialize);
            }catch (\Exception $e){
                $db = $this->db();
                $sql_country = $db->super_query("SELECT * FROM `".PREFIX."_country` ORDER by `name` ASC", true, "country", true);
                $sql_country_serialize = serialize($sql_country);
                Cache::creat_system_cache('all_country', $sql_country_serialize);
                $db->free();
            }
            $all_country = '';
            foreach($sql_country as $row_country)
                $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';

            $tpl = $params['tpl'];
            $tpl->load_template('reg.tpl');
            $tpl->set('{country}', $all_country);
            $tpl->compile('content');
            //            return $tpl;
            $tpl->clear();

//        $params = array('tpl' => $tpl);
            //die();
            //Registry::set('tpl', $tpl);
            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function login($params)
    {

        $logged = $params['user']['logged'];
        $db = $this->db();

        //Если данные поступили через пост и пользователь не авторизован
        if(isset($_POST['login']) AND $logged == false){

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
                $errors++;
                $err .= 'password|n\a';
            }

            // if( _strlen( $name, $config['charset'] ) > 40 OR _strlen(trim($name), $config['charset']) < 3) $stop = 'error';
            $lang = langs::get_langs();

            if($errors == 0) {
                $check_user = $db->super_query("SELECT user_id, user_password FROM `".PREFIX."_users` WHERE user_email = '".$email."'");

                //Если есть юзер то пропускаем
                if($check_user AND password_verify($password, $check_user['user_password']) == true){
                    //Hash ID
                    $_IP = null;
                    $hid = $password.md5(md5($_IP));

                    //Обновляем хэш входа
                    $db->query("UPDATE `".PREFIX."_users` SET user_hid = '".$hid."' WHERE user_id = '".$check_user['user_id']."'");

                    //Удаляем все рание события
                    $db->query("DELETE FROM `".PREFIX."_updates` WHERE for_user_id = '{$check_user['user_id']}'");

                    //Устанавливаем в сессию ИД юзера
                    $_SESSION['user_id'] = intval($check_user['user_id']);

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", intval($check_user['user_id']), 365);
                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hid", $hid, 365);

                    //Вставляем лог в бд
                    $_BROWSER = null;
                    $db->query("UPDATE `".PREFIX."_log` SET browser = '".$_BROWSER."', ip = '".$_IP."' WHERE uid = '".$check_user['user_id']."'");

                    $config = include __DIR__.'/../data/config.php';

                       // header('Location: /');
                    echo 'ok|'.$check_user['user_id'];
                } else{
                    echo 'error|no_val|no_user|'.$password;
                    var_dump(password_verify($password, $check_user['user_password']));
                    //msgbox('', $lang['not_loggin'].'<br /><br /><a href="/restore/" onClick="Page.Go(this.href); return false">Забыли пароль?</a>', 'info_red');
                }
            }else{
                echo 'error|no_val|'.$err;
            }
        }
    }

    public function test()
    {

        $img = __DIR__.'/antibot.gif';

        echo (new TesseractOCR($img))
            ->whitelist(range(0, 9), range('A', 'Z'))
            ->run();

        die();
    }

    public function test2()
    {
//        try {
//            $env = parse_ini_file(__DIR__ . '/../../.env');
//            if (isset($env["DB_DATABASE"])) {
//                return $env;
//            }
//        } catch (\Exception $e){
//            return [];
//        }
//        die();
        $titles = array('Сидит %d котик', 'Сидят %d котика', 'Сидит %d котиков', 'Сидит %d котикова');
        function declOfNum($number, $titles)
        {
            $cases = array (2, 0, 1, 1, 1, 2);
            $format = $titles[ ($number%100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)] ];
            return sprintf($format, $number);
        }

        echo declOfNum(0, $titles).'<br>';
        echo declOfNum(1, $titles).'<br>';
        echo declOfNum(2, $titles).'<br>';
        echo declOfNum(3, $titles).'<br>';
        echo declOfNum(4, $titles).'<br>';
        echo declOfNum(5, $titles).'<br>';
        echo declOfNum(6, $titles).'<br>';
        echo declOfNum(7, $titles).'<br>';
        echo declOfNum(8, $titles).'<br>';
        echo declOfNum(9, $titles).'<br>';
        echo declOfNum(10, $titles).'<br>';
        echo declOfNum(11, $titles).'<br>';
        echo declOfNum(12, $titles).'<br>';
        echo declOfNum(13, $titles).'<br>';
        echo declOfNum(14, $titles).'<br>';
        echo declOfNum(15, $titles).'<br>';
        echo declOfNum(16, $titles).'<br>';
        echo declOfNum(17, $titles).'<br>';
        echo declOfNum(18, $titles).'<br>';
        echo declOfNum(19, $titles).'<br>';
        echo declOfNum(20, $titles).'<br>';
        echo declOfNum(21, $titles).'<br>';
        echo declOfNum(22, $titles).'<br>';
        echo declOfNum(23, $titles).'<br>';
        echo declOfNum(24, $titles).'<br>';
        echo declOfNum(25, $titles).'<br>';
        echo declOfNum(26, $titles).'<br>';
        echo declOfNum(27, $titles).'<br>';
die();
    }

    function test3()
    {
        function langdate($format, $stamp){
            $langdate = Langs::get_langdate();
            return strtr(date($format, $stamp), $langdate);
        }

        function megaDate($date, $func = false, $full = false){
            $server_time = intval($_SERVER['REQUEST_TIME']);

            if(date('Y-m-d', $date) == date('Y-m-d', $server_time))
                return $date = langdate('сегодня в H:i', $date);
            elseif(date('Y-m-d', $date) == date('Y-m-d', ($server_time-84600)))
                return $date = langdate('вчера в H:i', $date);
            else
                if($func == 'no_year')
                    return $date = langdate('j M в H:i', $date);
                else
                    if($full)
                        return $date = langdate('j F Y в H:i', $date);
                    else
                        return $date = langdate('j M Y в H:i', $date);
        }

        $str_date = time();

        $date = megaDate($str_date);

        echo $date;

        var_dump($str_date);



    }
}