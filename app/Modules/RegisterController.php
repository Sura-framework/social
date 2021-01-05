<?php

namespace App\Modules;

use App\Models\Register;
use Exception;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class RegisterController extends Module{

    /**
     * Завершение регистрации
     *
     * @param $params
     * @return string
     */
    public function index($params): string
    {
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        //Проверяем была ли нажата кнопка, если нет, то делаем редирект на главную
        $token = $_POST['token'].'|'.$_SERVER['REMOTE_ADDR'];
        if(!$logged AND $token == $_SESSION['_mytoken']) {
            //Код безопасности
            $session_sec_code = $_SESSION['sec_code'];
            $sec_code = $_POST['sec_code'];

            //Если код введные юзером совпадает, то пропускаем, иначе выводим ошибку
            if ($sec_code == $session_sec_code) {
                //Входные POST Данные

                $user_name = Validation::textFilter($_POST['name']);
                $user_last_name = Validation::textFilter($_POST['lastname']);
                $user_email = Validation::textFilter($_POST['email']);

                $user_name = ucfirst($user_name);
                $user_last_name = ucfirst($user_last_name);

                $user_sex = intval($_POST['sex']);
                if ($user_sex < 0 OR $user_sex > 2) $user_sex = 0;

                $user_day = intval($_POST['day']);
                if ($user_day < 0 OR $user_day > 31) $user_day = 0;

                $user_month = intval($_POST['month']);
                if ($user_month < 0 OR $user_month > 12) $user_month = 0;

                $user_year = intval($_POST['year']);
                if ($user_year < 1930 OR $user_year > 2007) $user_year = 0;

                $user_country = intval($_POST['country']);
                if ($user_country < 0 OR $user_country > 10) $user_country = 0;

                $user_city = intval($_POST['city']);
                if ($user_city < 0 OR $user_city > 1587) $user_city = 0;

                $_POST['password_first'] = Validation::textFilter($_POST['password_first']);
                $_POST['password_second'] = Validation::textFilter($_POST['password_second']);

                $password_first = Tools::GetVar($_POST['password_first']);
                $password_second = Tools::GetVar($_POST['password_second']);
                $user_birthday = $user_year . '-' . $user_month . '-' . $user_day;

                $errors = 0;
                $err = '';

                //Проверка E-mail
                if(Validation::check_email($user_email) == false)
                {
                    $errors++;
                    $err .= 'mail|';
                }

                //Проверка имени
                if (Validation::check_name($user_name) == false) {
                    $errors++;
                    $err .= 'name|';
                }

                //Проверка фамилии
                if (Validation::check_name($user_last_name) == false) {
                    $errors++;
                    $err .= 'surname|';
                }

                //Проверка Паролей
                if (Validation::check_password($password_first, $password_second) == false ){
                    $errors++;
                    $err .= 'password|';
                }

                //Если нет ошибок то пропускаем и добавляем в базу
                if ($errors == 0) {

                    //Если email и существует то пропускаем
                    $check_email = Register::check_email($user_email);
                    if (!$check_email['cnt']) {
                        //$md5_pass = md5(md5($password_first));
                        $pass_hash = password_hash($password_first, PASSWORD_DEFAULT);
                        
                        $user_group = '5';

                        if ($user_country > 0 or $user_city > 0) {
                            $country_info = Register::country_info($user_country);
                            $city_info = Register::city_info($user_city);

                            $user_country_city_name = $country_info['name'] . '|' . $city_info['name'];
                        }

                        $user_search_pref = $user_name . ' ' . $user_last_name;

                        //Hash ID
                        $_IP = $_SERVER['REMOTE_ADDR']; //!NB

                        $server_time = \Sura\Libs\Tools::time();
                        $db->query("INSERT INTO `users` (user_email, user_password, user_name, user_lastname, user_sex, user_day, user_month, user_year, user_country, user_city, user_reg_date, user_lastdate, user_group, user_hash, user_country_city_name, user_search_pref, user_birthday, user_privacy) VALUES ('{$user_email}', '{$pass_hash}', '{$user_name}', '{$user_last_name}', '{$user_sex}', '{$user_day}', '{$user_month}', '{$user_year}', '{$user_country}', '{$user_city}', '{$server_time}', '{$server_time}', '{$user_group}', '{$pass_hash}', '{$user_country_city_name}', '{$user_search_pref}', '{$user_birthday}', 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||')");
                        $id = $db->insert_id();

                        //Устанавливаем в сессию ИД юзера
                        $_SESSION['user_id'] = intval($id);

                        //Записываем COOKIE
                        Tools::set_cookie("user_id", intval($id), 365);
//                        Tools::set_cookie("password", md5(md5($password_first)), 365);
                        Tools::set_cookie("hash", $pass_hash, 365);

                        //Создаём папку юзера в кеше
//                        Cache::mozg_create_folder_cache("user_{$id}");

                        //Директория юзеров
                        $uploaddir = __DIR__ . '/../../public/uploads/users/';

                        mkdir($uploaddir . $id, 0777);
                        chmod($uploaddir . $id, 0777);
                        mkdir($uploaddir . $id . '/albums', 0777);
                        chmod($uploaddir . $id . '/albums', 0777);

                        //Если юзер регался по реф ссылки, то начисляем рефералу 10 убм
                        if ($_SESSION['ref_id']) {
                            //Проверям на накрутку убм, что юзер не сам регистрирует анкеты
                            $check_ref = $db->super_query("SELECT COUNT(*) AS cnt FROM `log` WHERE ip = '{$_IP}'");
                            if (!$check_ref['cnt']) {
                                $ref_id = intval($_SESSION['ref_id']);

                                //Даём рефералу +10 убм
                                $db->query("UPDATE `users` SET user_balance = user_balance+10 WHERE user_id = '{$ref_id}'");

                                //Вставялем рефералу ид регистратора
                                $db->query("INSERT INTO `invites` SET uid = '{$ref_id}', ruid = '{$id}'");
                            }
                        }

                        $_BROWSER = null;

                        //Вставляем лог в бд
                        $db->query("INSERT INTO `log` SET uid = '{$id}', browser = '{$_BROWSER}', ip = '{$_IP}'");

                        echo 'ok|' . $id.'|';
                        return true;

                    } else {
                        echo 'err_mail|';
                    }
                }
                else {
                    echo 'error|no_val|'.$err;
                }
            } else {
                echo 'error';
            }
        }echo 'error|no_val|not token';
    }

    /**
     * Signup
     *
     * @return string
     * @throws Exception
     */
    public function Signup(): string
    {
        $title = 'Регистрация | Sura';
        /**
         * Загружаем Страны
         */
        $Cache = cache_init(array('type' => 'file'));

        $key = "system/all_country";
        try {
            $value = $Cache->get($key, $default = null);
        }catch (Exception $e){
            $db = $this->db();
            $value = $db->super_query("SELECT * FROM `country` ORDER by `name` ASC", 1);
            $Cache->set($key, $value);
            $db->free();
        }
        $all_country = '';
        foreach($value as $row_country){
            $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
        }
        return view('sign_up', array("title"=>$title,"country" => $all_country));
    }
}