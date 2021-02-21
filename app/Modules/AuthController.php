<?php

declare(strict_types=1);

namespace App\Modules;


use App\Models\Register;
use JetBrains\PhpStorm\NoReturn;
use JsonException;
use Sura\Libs\Auth;
use Sura\Libs\Mail;
use Sura\Libs\Model;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Validation;
use Sura\Time\Date;

class AuthController extends Module
{
    /**
     * Если делаем выход
     */
    #[NoReturn] public static function logout(): void
    {
        Auth::logout();
    }

    /**
     * Вход на сайт
     *
     * @return int
     * @throws JsonException
     */
    public function login(): int
    {
//        $logged = $this->logged();
        $db = $this->db();

        $request = ($requests = Request::getRequest()->getGlobal());
        $token = $request['token'] . '|' . $_SERVER['REMOTE_ADDR'];
        $check_token = false;
        if ($token == $_SESSION['_mytoken'] and $check_token == true) {
            $user_token = true;
        } elseif ($check_token == false) {
            $user_token = true;
        } else {
            $user_token = false;
        }

        $errors = 0;
        $err = array();
        $res = '';
        $logged = false;
        //Если данные поступили через пост запрос и пользователь не авторизован
        if (isset($request['login']) and $logged == false and $user_token == true) {
            //Приготавливаем данные

            /** Проверка E-mail */
            $email = strip_tags($request['email']);
            if (Validation::check_email($email) == false) {
                $errors++;
//                $err .= 'mail|'.$email;
                $err['mail'] = $email;
            }

            /** Проверка Пароля */
            if (!empty($request['password'])) {
                $password = GetVar($request['password']);
            } else {
                $password = NUlL;
                $errors++;
//                $err .= 'password|n\a';
                $err['password'] = 'password';
            }

            if ($errors == 0) {
                $check_user = $db->super_query("SELECT user_id, user_password FROM `users` WHERE user_email = '" . $email . "'");

                //Если есть юзер то пропускаем
                if ($check_user['user_password'] == true and is_array($check_user) and password_verify($password, $check_user['user_password']) == true) {
                    //Hash ID
                    $_IP = Request::getRequest()->getClientIP();
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    //Обновляем хэш входа
                    $db->query("UPDATE `users` SET user_hash = '" . $hash . "' WHERE user_id = '" . $check_user['user_id'] . "'");

                    //Удаляем все рание события
                    $db->query("DELETE FROM `updates` WHERE for_user_id = '{$check_user['user_id']}'");

                    //Устанавливаем в сессию ИД юзера
                    $_SESSION['user_id'] = (int)$check_user['user_id'];

                    //Записываем COOKIE
                    Tools::set_cookie("user_id", (string)$check_user['user_id'], 365);
//                    Tools::set_cookie("password", $password, 365);
                    Tools::set_cookie("hash", $hash, 365);

                    //Вставляем лог в бд
                    $_BROWSER = null;
                    $db->query("UPDATE `log` SET browser = '" . $_BROWSER . "', ip = '" . $_IP . "' WHERE uid = '" . $check_user['user_id'] . "'");

                    header('Location: /');
                    $status = Status::OK;
                    $res = $check_user['user_id'];
//                    return _e('ok|'.$check_user['user_id']);
                } else {
                    header('Location: /');
                    $status = '2 ' . $password . ' ' . $check_user['user_password'];
                    $status = Status::NOT_USER;
//                    return _e( 'error|no_val|no_user|'.$password);
//                    var_dump(password_verify($password, $check_user['user_password']));
                    //msgbox('', $lang['not_loggin'].'<br /><br /><a href="/restore/" onClick="Page.Go(this.href); return false">Забыли пароль?</a>', 'info_red');
                }
            } else {
                $status = 3;//BAD
//                return _e( 'error|no_val|'.$err);
            }
        } else {
            $status = 4;//NOT_DATA
//            return _e( 'error|no_val|');
        }
        return _e(json_encode(array(
            'status' => $status,
            'res' => $res,
        ), JSON_THROW_ON_ERROR));
    }

    /**
     * Signup
     *
     * @return int
     * @throws \Throwable
     */
    public function signup(): int
    {
        $title = 'Регистрация | Sura';
        /**
         * Загружаем Страны
         */
        $all_country = (new \App\Libs\Support)->allCountry();
        return view('sign_up', array("title" => $title, "country" => $all_country));
    }

    /**
     * Завершение регистрации
     *
     * @return int
     * @throws JsonException
     */
    public function end_register(): int
    {
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        $request = ($requests = Request::getRequest()->getGlobal());

        $res = '';
        $err = '';

        //Проверяем была ли нажата кнопка, если нет, то делаем редирект на главную
        $token = $_POST['token'] . '|' . $_SERVER['REMOTE_ADDR'];
        if (!$logged and $token == $_SESSION['_mytoken']) {
            //Код безопасности
            $session_sec_code = $_SESSION['s_code'];
            $sec_code = $_POST['sec_code'];

            //Если код введные юзером совпадает, то пропускаем, иначе выводим ошибку
            if ($sec_code == $session_sec_code) {
                //Входные POST Данные

                $user_name = Validation::textFilter($_POST['name']);
                $user_last_name = Validation::textFilter($_POST['lastname']);
                $user_email = Validation::textFilter($_POST['email']);

                $user_name = ucfirst($user_name);
                $user_last_name = ucfirst($user_last_name);

                $user_sex = (int)$_POST['sex'];
                if ($user_sex < 0 or $user_sex > 2) {
                    $user_sex = 0;
                }

                $user_day = (int)$_POST['day'];
                if ($user_day < 0 or $user_day > 31) {
                    $user_day = 0;
                }

                $user_month = (int)$_POST['month'];
                if ($user_month < 0 or $user_month > 12) {
                    $user_month = 0;
                }

                $user_year = (int)$_POST['year'];
                if ($user_year < 1930 or $user_year > 2007) {
                    $user_year = 0;
                }

                $user_country = (int)$_POST['country'];
                if ($user_country < 0 or $user_country > 10) {
                    $user_country = 0;
                }

                $user_city = (int)$_POST['city'];
                if ($user_city < 0 or $user_city > 1587) {
                    $user_city = 0;
                }

                $password_first = Validation::textFilter($_POST['password_first']);
                $password_second = Validation::textFilter($_POST['password_second']);
                $user_birthday = $user_year . '-' . $user_month . '-' . $user_day;

                $errors = 0;
                $err = array();
                $res = '';

                //Проверка E-mail
                if (Validation::check_email($user_email) == false) {
                    $errors++;
                    $err['mail'] = $user_email;
                }

                //Проверка имени
                if (Validation::check_name($user_name) == false) {
                    $errors++;
                    $err['user_name'] = $user_name;
                }

                //Проверка фамилии
                if (Validation::check_name($user_last_name) == false) {
                    $errors++;
                    $err['user_surname'] = $user_last_name;
                }

                //Проверка Паролей
                if (Validation::check_password($password_first, $password_second) == false) {
                    $errors++;
                    $err['password'] = $password_first;
                }

                //Если нет ошибок то пропускаем и добавляем в базу
                if ($errors == 0) {

                    //Если email и существует то пропускаем
                    $check_email = (new \App\Models\Register)->check_email($user_email);
                    if (!$check_email['cnt']) {
                        //$md5_pass = md5(md5($password_first));
                        $pass_hash = password_hash($password_first, PASSWORD_DEFAULT);

                        $hash = password_hash($password_first, PASSWORD_DEFAULT);

                        $user_group = '5';

                        if ($user_country > 0 or $user_city > 0) {
                            $country_info = (new \App\Models\Register)->country_info((int)$user_country);
                            $city_info = (new \App\Models\Register)->city_info((int)$user_city);

                            $user_country_city_name = $country_info['name'] . '|' . $city_info['name'];
                        }else{
                            $user_country_city_name = "N\A";
                        }

                        $user_search_pref = $user_name . ' ' . $user_last_name;

                        //Hash ID
                        $_IP = Request::getRequest()->getClientIP();

                        $server_time = Date::time();

                        //FIXME update db query
//                        $db->query("INSERT INTO `users` (user_email, user_password, user_name, user_lastname, user_sex,
//                            user_day, user_month, user_year, user_country, user_city, user_reg_date, user_lastdate, user_group,
//                            user_hash, user_country_city_name, user_search_pref, user_birthday, user_privacy)
//                            VALUES ('{$user_email}', '{$pass_hash}', '{$user_name}', '{$user_last_name}', '{$user_sex}', '{$user_day}',
//                                    '{$user_month}', '{$user_year}', '{$user_country}', '{$user_city}', '{$server_time}', '{$server_time}', '{$user_group}', '{$pass_hash}',
//                                    '{$user_country_city_name}', '{$user_search_pref}', '{$user_birthday}', 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||')");

                        $database = Model::getDB();
                        $database->query('INSERT INTO users ?', [ // an array can be a parameter
                            'user_email' => $user_email,
                            'user_password' => $pass_hash,
                            'user_name' => $user_name,
                            'user_lastname' => $user_last_name,
                            'user_sex' => $user_sex,
                            'user_day' => $user_day,
                            'user_month' => $user_month,
                            'user_year' => $user_year,
                            'user_country' => $user_country,
                            'user_city' => $user_city,
                            'user_lastdate' => $server_time,
                            'user_reg_date' => $server_time,
                            'user_group' => $user_group,
                            'user_hash' => $pass_hash,
                            'user_country_city_name' => $user_country_city_name,
                            'user_search_pref' => $user_search_pref,
                            'user_birthday' => $user_birthday,
                            'user_last_visit' => $server_time,
                            'user_privacy' => 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||',
                        ]); // it is even possible to use multiple inserts

                        //user_id
                        //user_email
                        //user_password
                        //user_name
                        //user_lastname
                        //user_photo
                        //user_wall_id
                        //user_birthday
                        //user_sex
                        //user_day
                        //user_month
                        //user_year
                        //user_country
                        //user_city
                        //user_reg_date
                        //user_lastdate
                        //user_group
                        //user_hash
                        //user_country_city_name
                        //user_search_pref
                        //user_xfields
                        //xfields
                        //user_xfields_all
                        //user_albums_num
                        //user_friends_demands
                        //user_friends_num
                        //user_last_visit
                        //user_fave_num
                        //user_pm_num
                        //user_notes_num
                        //user_subscriptions_num
                        //user_videos_num
                        //user_wall_num
                        //user_status
                        //user_privacy
                        //user_blacklist_num
                        //user_blacklist
                        //user_sp
                        //user_support
                        //user_balance
                        //user_lastupdate
                        //user_gifts
                        //user_public_num
                        //user_audio
                        //user_msg_type
                        //user_delet
                        //user_ban
                        //user_ban_date
                        //user_new_mark_photos
                        //user_doc_num
                        //user_logged_mobile
                        //guests
                        //user_cover
                        //user_cover_pos
                        //balance_rub
                        //user_rating
                        //invties_pub_num
                        //notifications_list
                        //user_text
                        //time_zone
                        //alias

//                        $id = $db->insert_id();
                        $id = $database->getInsertId();

                        //Устанавливаем в сессию ИД юзера
                        $_SESSION['user_id'] = (int)$id;

                        //Записываем COOKIE
                        Tools::set_cookie("user_id", (string)$id, 365);
//                        Tools::set_cookie("password", md5(md5($password_first)), 365);
                        Tools::set_cookie("hash", $pass_hash, 365);

                        //Создаём папку юзера в кеше
//                        Cache::mozg_create_folder_cache("user_{$id}");

                        //Директория юзеров
                        $uploaddir = __DIR__ . '/../../public/uploads/users/';

                        if (!mkdir($concurrentDirectory = $uploaddir . $id, 0777) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                        }
//                        chmod($uploaddir . $id, 0777);
                        if (!mkdir($concurrentDirectory = $uploaddir . $id . '/albums', 0777) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                        }
//                        chmod($uploaddir . $id . '/albums', 0777);

                        //Если юзер регался по реф ссылки, то начисляем рефералу 10 убм
                        if ($_SESSION['ref_id']) {
                            //Проверям на накрутку убм, что юзер не сам регистрирует анкеты
                            $check_ref = $db->super_query("SELECT COUNT(*) AS cnt FROM `log` WHERE ip = '{$_IP}'");
                            if (!$check_ref['cnt']) {
                                $ref_id = (int)$_SESSION['ref_id'];

                                //Даём рефералу +10 убм
                                $db->query("UPDATE `users` SET user_balance = user_balance+10 WHERE user_id = '{$ref_id}'");

                                //Вставялем рефералу ид регистратора
                                $db->query("INSERT INTO `invites` SET uid = '{$ref_id}', ruid = '{$id}'");
                            }
                        }

                        $_BROWSER = null;

                        //Вставляем лог в бд
                        $db->query("INSERT INTO `log` SET uid = '{$id}', browser = '{$_BROWSER}', ip = '{$_IP}'");

                        $status = Status::OK;
                        $res = $id;
                    } else {
                        $status = Status::FOUND;
                    }
                } else {
                    $status = Status::NOT_VALID;
                }
            } else {
                $status = Status::BAD_CODE;
            }
        } else {
            $status = Status::LOGGED;
        }
        return _e(json_encode(array(
            'status' => $status,
            'res' => $res,
        ), JSON_THROW_ON_ERROR));
    }

    /**
     * Отправка данных на почту на воостановления
     *
     * @return int
     * @throws JsonException
     */
    public function restore_send(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if (!$logged) {
            $params['title'] = $lang['restore_title'] . ' | Sura';

            $request = (Request::getRequest()->getGlobal());
//
            $email = Validation::check_email($request['email']);
            $check = $db->super_query("SELECT user_name FROM `users` WHERE user_email = '{$email}'");

            $server_time = Date::time();

            if ($check) {
                //Удаляем все предыдущие запросы на воостановление
                $db->query("DELETE FROM `restore` WHERE email = '{$email}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                $rand_lost = '';
                for ($i = 0; $i < 15; $i++) {
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $hash = md5($server_time . $email . rand(0, 100000) . $rand_lost . $check['user_name']);

                $_IP = Request::getRequest()->getClientIP();

                //Вставляем в базу
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");

                //Отправляем письмо на почту для воостановления
//                include_once __DIR__.'/../Classes/mail.php';

                $config = Settings::load();

                $mail = new Mail($config);
                $message = <<<HTML
                        Здравствуйте, {$check['user_name']}.
                        
                        Чтобы сменить ваш пароль, пройдите по этой ссылке:
                        {$config['home_url']}restore?act=prefinish&h={$hash}
                        
                        Мы благодарим Вас за участие в жизни нашего сайта.
                        
                        {$config['home_url']}
                        HTML;

                $mail->send($email, $lang['lost_subj'], $message);

                $status = Status::OK;
                $err = 'yes';
            } else {
                $status = Status::NOT_FOUND;
                $err = 'hacking';
            }
        } else {
            $status = Status::BAD_LOGGED;
            $err = 'hacking';
        }
        return _e_json(array(
            'status' => $status,
            'err' => $err
        ));
    }

    /**
     *  Страница смены пароля
     *
     * @param $params
     * @return int
     */
    public function restore_pre_finish($params): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if (!$logged) {
            $params['title'] = $lang['restore_title'] . ' | Sura';
            $requests = Request::getRequest();
            $request = ($requests->getGlobal());

            $hash = Validation::strip_data($request['h']);
            $_IP = $requests->getClientIP();
            $row = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$hash}' AND ip = '{$_IP}'");
            if ($row) {
//                $info = $db->super_query("SELECT user_name FROM `users` WHERE user_email = '{$row['email']}'");
//                $tpl->load_template('restore/prefinish.tpl');
//                $tpl->set('{name}', $info['user_name']);

//                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
//                $rand_lost = 0;
//                for ($i = 0; $i < 15; $i++) {
//                    $rand_lost .= $salt[rand(0, 33)];
//                }
//                $server_time = Date::time();

//                $newhash = md5($server_time . $row['email'] . rand(0, 100000) . $rand_lost);
                $new_hash = password_hash($row['email'], PASSWORD_DEFAULT);
//                $tpl->set('{hash}', $newhash);
                $db->query("UPDATE `restore` SET hash = '{$new_hash}' WHERE email = '{$row['email']}'");

//                $tpl->compile('content');
                return view('info.info', $params);
            }
//            $speedbar = $lang['no_infooo'];
//            msg_box($lang['restore_badlink'], 'info');
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Смена пароля
     *
     * @return int
     * @throws JsonException
     */
    public function restore_finish(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if (!$logged) {
            $params['title'] = $lang['restore_title'] . ' | Sura';

            $request = (Request::getRequest()->getGlobal());
//
            $hash = $db->safesql(Validation::strip_data($request['hash']));
            $_IP = Request::getRequest()->getClientIP();
            $row = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$hash}' AND ip = '{$_IP}'");
            if ($row) {

                $request['new_pass'] = Validation::textFilter($request['new_pass']);
                $request['new_pass2'] = Validation::textFilter($request['new_pass2']);

                if (strlen($request['new_pass']) >= 6 and $request['new_pass'] == $request['new_pass2']) {
                    $pass_hash = password_hash($request['new_pass'], PASSWORD_DEFAULT);
                    $db->query("UPDATE `users` SET user_password = '{$pass_hash}' WHERE user_email = '{$row['email']}'");
                    $db->query("DELETE FROM `restore` WHERE email = '{$row['email']}'");
                }
                $status = Status::OK;
            } else {
                $status = Status::NOT_FOUND;
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ));
    }

    /**
     * Восстановление доступа к странице
     *
     * @return int
     */
    public function restore(): int
    {
        $lang = $this->get_langs();
//        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        if (!$logged) {
            $params['title'] = $lang['restore_title'] . ' | Sura';

            $data = array();
            $data['title'] = $lang['restore_title'] . ' | Sura';
            return view('restore.main', array("title" => $data['title']));
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }

    }

    /**
     * Проверка данных на воостановления
     * @throws JsonException
     */
    public function restore_next(): int
    {
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if (!$logged) {
            $meta_tags['title'] = $lang['restore_title'];

            $request = (Request::getRequest()->getGlobal());

            $email = Validation::ajax_utf8($request['email']);
            $check = $db->super_query("SELECT user_id, user_search_pref, user_photo FROM `users` WHERE user_email = '{$email}'");
            if ($check) {
                if ($check['user_photo'])
                    $check['user_photo'] = "/uploads/users/{$check['user_id']}/50_{$check['user_photo']}";
                else
                    $check['user_photo'] = "/images/no_ava_50.png";
           
//                echo $check['user_search_pref'] . "|" . $check['user_photo'];
	            $status = Status::OK;
                return _e_json(array(
                    'status' => $status,
                    'name' => $check['user_search_pref'],
                    'photo' => $check['user_photo'],
                ) );
            } else{
                $status = Status::NOT_FOUND;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
	    return _e_json(array(
		    'status' => $status,
	    ) );
    }
}