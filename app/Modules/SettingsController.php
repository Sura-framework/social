<?php
declare(strict_types=1);
namespace App\Modules;

use App\Libs\Friends;
use Exception;
use Sura\Libs\Profile_check;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;


class SettingsController extends Module{

    /**
     * Изменение пароля
     *
     * @return int
     * @throws \JsonException
     */
    public function newpass(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            Tools::NoAjaxRedirect();

            $request = (Request::getRequest()->getGlobal());

            $request['old_pass'] = Validation::ajax_utf8($request['old_pass']);
            $request['new_pass'] = Validation::ajax_utf8($request['new_pass']);
            $request['new_pass2'] = Validation::ajax_utf8($request['new_pass2']);

            $old_pass = md5(md5(GetVar($request['old_pass'])));
            $new_pass = md5(md5(GetVar($request['new_pass'])));
            $new_pass2 = md5(md5(GetVar($request['new_pass2'])));

            //Выводим текущий пароль
            $row = $db->super_query("SELECT user_password FROM `users` WHERE user_id = '{$user_id}'");
            if($row['user_password'] == $old_pass){
                if($new_pass == $new_pass2){
                    $db->query("UPDATE `users` SET user_password = '{$new_pass2}' WHERE user_id = '{$user_id}'");

                    $status = Status::OK;
                }else{
                    $status = Status::PASSWORD_DOESNT_MATCH;
                }
            }else{
                $status = Status::NOT_DATA;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     *  Изменение имени
     *
     * @param $params
     * @throws \JsonException
     */
    public function newname(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $user_name = Validation::ajax_utf8(Validation::textFilter($request['name']));
            $user_lastname = Validation::ajax_utf8(Validation::textFilter(ucfirst($request['lastname'])));

            //Проверка имени
            if(isset($user_name)){
                if(strlen($user_name) >= 2){
                    if(!preg_match("/^[a-zA-Zа-яА-Я]+$/iu", $user_name))
                        $errors = 3;
                    else
                        $errors = 0;
                } else
                    $errors = 2;
            } else
                $errors = 1;

            //Проверка фамилии
            if(isset($user_lastname)){
                if(strlen($user_lastname) >= 2){
                    if(!preg_match("/^[a-zA-Zа-яА-Я]+$/iu", $user_lastname))
                        $errors_lastname = 3;
                    else
                        $errors_lastname = 0;
                } else
                    $errors_lastname = 2;
            } else
                $errors_lastname = 1;

            if($errors == 0 AND $errors_lastname == 0){
                $user_name = ucfirst($user_name);
                $user_lastname = ucfirst($user_lastname);

                $db->query("UPDATE `users` SET user_name = '{$user_name}', user_lastname = '{$user_lastname}', user_search_pref = '{$user_name} {$user_lastname}' WHERE user_id = '{$user_id}'");
                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove($user_id.'/profile_'.$user_id);

                if ($response == false){
                        $status = Status::OK;
                    }else{
                        $status = Status::BAD;
                    }
            } else {
                $status = Status::BAD;//TODO update
            }
        } else {
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Сохранение настроек приватности
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function saveprivacy(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $params['title'] = $lang['settings'].' | Sura';

            $request = (Request::getRequest()->getGlobal());

            $val_msg = (int)$request['val_msg'];
            $val_wall1 = (int)$request['val_wall1'];
            $val_wall2 = (int)$request['val_wall2'];
            $val_wall3 = (int)$request['val_wall3'];
            $val_info = (int)$request['val_info'];

            if($val_msg <= 0 OR $val_msg > 3) $val_msg = 1;
            if($val_wall1 <= 0 OR $val_wall1 > 3) $val_wall1 = 1;
            if($val_wall2 <= 0 OR $val_wall2 > 3) $val_wall2 = 1;
            if($val_wall3 <= 0 OR $val_wall3 > 3) $val_wall3 = 1;
            if($val_info <= 0 OR $val_info > 3) $val_info = 1;

            $user_privacy = "val_msg|{$val_msg}||val_wall1|{$val_wall1}||val_wall2|{$val_wall2}||val_wall3|{$val_wall3}||val_info|{$val_info}||";

            $db->query("UPDATE `users` SET user_privacy = '{$user_privacy}' WHERE user_id = '{$user_id}'");

            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
            $cache = new \Sura\Cache\Cache($storage, 'users');
            $cache->remove($user_id.'/profile_'.$user_id);

            $status = Status::OK;
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Приватность настройки
     *
     * @param $params
     * @return int
     * @throws Exception
     */
    public function privacy(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['settings'].' | Sura';

            $sql_ = $db->super_query("SELECT user_privacy FROM `users` WHERE user_id = '{$user_id}'");
            $row = xfieldsdataload($sql_['user_privacy']);
//            $tpl->load_template('settings/privacy.tpl');
//            $tpl->set('{val_msg}', $row['val_msg']);
            $params['val_msg'] = $row['val_msg'];
//            $tpl->set('{val_msg_text}', );
            $params['val_msg_text'] = strtr($row['val_msg'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Никто'));
//            $tpl->set('{val_wall1}', );
            $params['val_wall1'] = $row['val_wall1'];
//            $tpl->set('{val_wall1_text}', );
            $params['val_wall1_text'] = strtr($row['val_wall1'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я'));
//            $tpl->set('{val_wall2}', );
            $params['val_wall2'] = $row['val_wall2'];
//            $tpl->set('{val_wall2_text}', );
            $params['val_wall2_text'] = strtr($row['val_wall2'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я'));
//            $tpl->set('{val_wall3}', );
            $params['val_wall3'] = $row['val_wall3'];
//            $tpl->set('{val_wall3_text}', );
            $params['val_wall3_text'] = strtr($row['val_wall3'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я'));
//            $tpl->set('{val_info}', );
            $params['val_info'] = $row['val_info'];
//            $tpl->set('{val_info_text}', );
            $params['val_info_text'] = strtr($row['val_info'], array('1' => 'Все пользователи', '2' => 'Только друзья', '3' => 'Только я'));
            $params['menu'] = \App\Models\Menu::settings();

            return view('settings.privacy', $params);
        }

        return view('info.info', $params);
    }

    /**
     * Блокируем юзера
     * Добавление в черный список
     *
     * @return int
     * @throws \JsonException
     * @throws \Throwable
     */
    public function addblacklist(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $bad_user_id = (int)$request['bad_user_id'];

            //Проверяем на существование юзера
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$bad_user_id}'");

            //Выводим свой блеклист для проверка
            //Проверяем юзера на блеклист
            $row_blacklist = $db->super_query("SELECT id FROM `users_blacklist` WHERE users = '{$user_id}|{$bad_user_id}'");

            if ($row['cnt'] AND $user_id != $bad_user_id){
                if( !$row_blacklist['id']){
                    $db->query("UPDATE `users` SET user_blacklist_num = user_blacklist_num+1 WHERE user_id = '{$user_id}'");

                    $db->query("INSERT INTO `users_blacklist` SET users = '{$user_id}|{$bad_user_id}'");

                    //Если юзер есть в др.
                    if(Friends::CheckFriends($bad_user_id)){
                        //Удаляем друга из таблицы друзей
                        $db->query("DELETE FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$bad_user_id}' AND subscriptions = 0");

                        //Удаляем у друга из таблицы
                        $db->query("DELETE FROM `friends` WHERE user_id = '{$bad_user_id}' AND friend_id = '{$user_id}' AND subscriptions = 0");

                        //Обновляем кол-друзей у юзера
                        $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$user_id}'");

                        //Обновляем у друга которого удаляем кол-во друзей
                        $db->query("UPDATE `users` SET user_friends_num = user_friends_num-1 WHERE user_id = '{$bad_user_id}'");

                        //Чистим кеш владельцу стр и тому кого удаляем из др.
                        $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                        $cache = new \Sura\Cache\Cache($storage, 'users');
                        $cache->remove($user_id.'/profile_'.$user_id);
                        $cache->remove($bad_user_id.'/profile_'.$bad_user_id);


                        //Удаляем пользователя из кеш файл друзей
                        $openMyList = $cache->load("{$user_id}/friends");
                        $cache->save("{$user_id}/friends", str_replace("u{$bad_user_id}|", "", $openMyList));

                        $openTakeList = $cache->load("users/{$bad_user_id}/friends");
                        $cache->save("users/{$bad_user_id}/friends", str_replace("u{$user_id}|", "", $openTakeList));

                        $status = Status::OK;
                    }else{
                        $status = Status::BAD_FRIEND;
                    }
                }else{
                    $status = Status::FOUND;
                }
            }else{
                $status = Status::BAD_USER;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * разблокируем юзера
     * Удаление из черного списка
     *
     * @return int
     * @throws \JsonException
     */
    public function delblacklist(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $request = (Request::getRequest()->getGlobal());

            $bad_user_id = (int)$request['bad_user_id'];

            //Проверяем на существование юзера
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_id = '{$bad_user_id}'");

            //Выводим свой блеклист для проверки
            //Проверяем юзера на блеклист
            $row_blacklist = $db->super_query("SELECT id FROM `users_blacklist` WHERE users = '{$user_id}|{$bad_user_id}'");

            if ($row['cnt'] AND $user_id != $bad_user_id){
                if($row_blacklist['id']){
                    $db->query("UPDATE `users` SET user_blacklist_num = user_blacklist_num-1 WHERE user_id = '{$user_id}'");
                    $db->query("DELETE FROM `users_blacklist` WHERE users = '{$user_id}|{$bad_user_id}'");
                    $status = Status::OK;
                }else{
                    $status = Status::NOT_FOUND;
                }
            }else{
                $status = Status::BAD_USER;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Черный список
     *
     * @return int
     * @throws Exception
     */
    public function blacklist(): int
    {
        $params = array();
        $lang = $this->get_langs();
        $db = $this->db();

        $logged = $this->logged();

        if($logged){
            $params['title'] = $lang['settings'].' | Sura';
            $row = $db->super_query("SELECT user_blacklist, user_blacklist_num FROM `users` WHERE user_id = '{$params['user']['user_id']}'");
            if($row['user_blacklist_num'] > 0 AND $row['user_blacklist_num'] <= 100){
                $array_blacklist = explode('|', $row['user_blacklist']);
                foreach($array_blacklist as $user){
                    if($user){
                        $infoUser = $db->super_query("SELECT user_photo, user_search_pref FROM `users` WHERE user_id = '{$user}'");

                        $params['user_blacklist']['$user'] = array();

                        if($infoUser['user_photo'])
                            $params['user_blacklist']['$user']['ava'] = '/uploads/users/'.$user.'/50_'.$infoUser['user_photo'];
                        else
                            $params['user_blacklist']['$user']['ava'] = '/images/no_ava_50.png';

                        $params['user_blacklist']['$user']['name'] = $infoUser['user_search_pref'];
                        $params['user_blacklist']['$user']['user-id'] = $user;//=)
                    }
                }
            } else{
                $params['user_blacklist_info'] = $lang['settings_nobaduser'];
//                $tpl->compile('info');
//                $tpl->result['alert_info'] = msg_box($lang['settings_nobaduser'], 'info_2');

//                $tpl->result['alert_info'] = '<div class="info_center"><br><br>Ни чего не найдено<br><br></div>';

            }

//            $tpl->load_template('settings/blacklist.tpl');

//            $tpl->set('{cnt}', '<span id="badlistnum">'.$row['user_blacklist_num'].'</span> '.Gramatic::declOfNum($row['user_blacklist_num'], $titles));
            $params['user_blacklist_num'] = $row['user_blacklist_num'];
            if ($params['user_blacklist_num']) {
                $titles = array('человек', 'человека', 'человек');//fave
                $params['cnt'] = '<span id="badlistnum">' . $row['user_blacklist_num'] . '</span> ' . Gramatic::declOfNum($row['user_blacklist_num'], $titles);
            }
//            if($row['user_blacklist_num']){
//                $tpl->set('[yes-users]', '');
//                $tpl->set('[/yes-users]', '');
//            } else
//                $tpl->set_block("'\\[yes-users\\](.*?)\\[/yes-users\\]'si","");

//            $tpl->set('{bad_user}', $tpl->result['alert_info']);
//            $tpl->compile('content');
            $params['menu'] = \App\Models\Menu::settings();

            return view('settings.blacklist', $params);
        }
        return view('info.info', $params);
    }

    /**
     *  Смена e-mail
     * @throws \JsonException
     */
    public function change_mail(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];

            $config = Settings::load();

            //Отправляем письмо на обе почты
//            include_once __DIR__.'/../Classes/mail.php';
//            $mail = new \dle_mail($config);

            $request = (Request::getRequest()->getGlobal());

            $email = Validation::textFilter($request['email']);

            //Проверка E-mail
            if(preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email))
            {
                $ok_email = true;
            }
            else {
                $ok_email = false;
            }

            $row = $db->super_query("SELECT user_email FROM `users` WHERE user_id = '{$user_id}'");

            $check_email = $db->super_query("SELECT COUNT(*) AS cnt FROM `users`  WHERE user_email = '{$email}'");

            if($row['user_email'] AND $ok_email AND !$check_email['cnt']){

                //Удаляем все пред. заявки
                $db->query("DELETE FROM `restore` WHERE email = '{$email}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                $rand_lost = '';
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $server_time = \Sura\Libs\Date::time();
                $hash = md5($server_time.$row['user_email'].rand(0, 100000).$rand_lost);

                $message = <<<HTML
                        Вы получили это письмо, так как зарегистрированы на сайте
                        {$config['home_url']} и хотите изменить основной почтовый адрес.
                        Вы желаете изменить почтовый адрес с текущего ({$row['user_email']}) на {$email}
                        Для того чтобы Ваш основной e-mail на сайте {$config['home_url']} был
                        изменен, Вам необходимо пройти по ссылке:
                        {$config['home_url']}index.php?go=settings&code1={$hash}
                        
                        Внимание: не забудьте, что после изменения почтового адреса при входе
                        на сайт Вам нужно будет указывать новый адрес электронной почты.
                        
                        Если Вы не посылали запрос на изменение почтового адреса,
                        проигнорируйте это письмо.С уважением,
                        Администрация {$config['home_url']}
                        HTML;
//                $mail->send($row['user_email'], 'Изменение почтового адреса', $message);

                if (!isset($_IP))
                    $_IP = NULL;

                //Вставляем в БД код 1
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $hash = md5($server_time.$row['user_email'].rand(0, 300000).$rand_lost);

                $message = <<<HTML
                        Вы получили это письмо, так как зарегистрированы на сайте
                        {$config['home_url']} и хотите изменить основной почтовый адрес.
                        Вы желаете изменить почтовый адрес с текущего ({$row['user_email']}) на {$email}
                        Для того чтобы Ваш основной e-mail на сайте {$config['home_url']} был
                        изменен, Вам необходимо пройти по ссылке:
                        {$config['home_url']}index.php?go=settings&code2={$hash}
                        
                        Внимание: не забудьте, что после изменения почтового адреса при входе
                        на сайт Вам нужно будет указывать новый адрес электронной почты.
                        
                        Если Вы не посылали запрос на изменение почтового адреса,
                        проигнорируйте это письмо.С уважением,
                        Администрация {$config['home_url']}
                        HTML;
//                $mail->send($email, 'Изменение почтового адреса', $message);

                //Вставляем в БД код 2
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");


                $status = Status::OK;
            }else{
                $status = Status::BAD_MAIL;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * time zone
     * @throws \JsonException
     * @throws \Throwable
     */
    public function time_zone(): int
    {
        $logged = $this->logged();
        if ($logged){
            $request = (Request::getRequest()->getGlobal());

            $time_zone = (int)$request['time_zone'];
            $max = 26;
            if($time_zone < $max){
                $user_info = $this->user_info();
                $db = $this->db();
                $db->query("UPDATE `users` SET time_zone = '".$time_zone."'  WHERE user_id = '".$user_info['user_id']."'");

                $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
                $cache = new \Sura\Cache\Cache($storage, 'users');
                $cache->remove($user_info['user_id'].'/profile_'.$user_info['user_id']);

                $status = Status::OK;
            }else{
                $status = Status::MAX;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Общие настройки
     *
     * @return int
     */
    public function general(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $params['title'] = $lang['settings'].' | Sura';

            $request = (Request::getRequest()->getGlobal());

            //Загружаем вверх
//            $tpl->load_template('settings/general.tpl');

            //Завершении смены E-mail
            $params['code_1'] = 'no_display';
            $params['code_2'] = 'no_display';
            $params['code_3'] = 'no_display';

            if(isset($request['code1'])){
                $code1 = Validation::strip_data($request['code1']);
                $code2 = Validation::strip_data($request['code2']);

                if(strlen($code1) == 32){
                    $_IP = null;
                    $code2 = '';
                    $check_code1 = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$code1}' AND ip = '{$_IP}'");
                    if($check_code1['email']){
                        $check_code2 = $db->super_query("SELECT COUNT(*) AS cnt FROM `restore` WHERE hash != '{$code1}' AND email = '{$check_code1['email']}' AND ip = '{$_IP}'");
                        if($check_code2['cnt'])
                            $params['code_1'] = '';
                        else {
                            $params['code_1'] = 'no_display';
                            $params['code_3'] = '';
                            //Меняем
                            $db->query("UPDATE `users` SET user_email = '{$check_code1['email']}' WHERE user_id = '{$params['user']['user_id']}'");
                            $params['user']['user_email'] = $check_code1['email'];
                        }
                        $db->query("DELETE FROM `restore` WHERE hash = '{$code1}' AND ip = '{$_IP}'");
                    }
                }

                if(strlen($code2) == 32){
                    $check_code2 = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$code2}' AND ip = '{$_IP}'");
                    if($check_code2['email']){
                        $check_code1 = $db->super_query("SELECT COUNT(*) AS cnt FROM `restore` WHERE hash != '{$code2}' AND email = '{$check_code2['email']}' AND ip = '{$_IP}'");
                        if($check_code1['cnt'])
                            $params['code_2'] = '';
                        else {
                            $params['code_2'] = 'no_display';
                            $params['code_3'] = '';

                            //Меняем
                            $db->query("UPDATE `users` SET user_email = '{$check_code2['email']}'  WHERE user_id = '{$params['user']['user_id']}'");
                            $params['user']['user_email'] = $check_code2['email'];
                        }
                        $db->query("DELETE FROM `restore` WHERE hash = '{$code2}' AND ip = '{$_IP}'");
                    }
                }
            }

            //Email
            $substre = substr($user_info['user_email'], 0, 1);
            $epx1 = explode('@', $user_info['user_email']);
            $params['email'] = $substre.'*******@'.$epx1[1];

            $time_list = Profile_check::list();

            $params['date_today'] = date("d.m.y H:i:s");

            $params['timezs'] = installationSelected($user_info['time_zone'], $time_list);

            $params['menu'] = \App\Models\Menu::settings();

            return view('settings.general', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }

    /**
     * Общие настройки
     *
     * @return int
     * @throws Exception
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $params['menu'] = \App\Models\Menu::settings();
        $logged = $this->logged();
        if($logged){
            $params['title'] = $lang['settings'].' | Sura';
            return view('settings.settings', $params);
        }

        $params['title'] = $lang['no_infooo'];
        $params['info'] = $lang['not_logged'];
        return view('info.info', $params);
    }
}