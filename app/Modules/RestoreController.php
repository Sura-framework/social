<?php

namespace App\Modules;

use Sura\Libs\Mail;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class RestoreController extends Module{

    /**
     * Отправка данных на почту на воостановления
     *
     * @param $params
     */
    public function send($params){
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if(!$logged){
            $params['title'] = $lang['restore_title'].' | Sura';

            $request = (Request::getRequest()->getGlobal());
//
            $email = Validation::ajax_utf8($request['email']);
            $check = $db->super_query("SELECT user_name FROM `users` WHERE user_email = '{$email}'");

            $server_time = \Sura\Libs\Tools::time();

            if($check){
                //Удаляем все предыдущие запросы на воостановление
                $db->query("DELETE FROM `restore` WHERE email = '{$email}'");

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                $rand_lost = '';
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $hash = md5($server_time.$email.rand(0, 100000).$rand_lost.$check['user_name']);

                $_IP = $_SERVER['REMOTE_ADDR'];

                //Вставляем в базу
                $db->query("INSERT INTO `restore` SET email = '{$email}', hash = '{$hash}', ip = '{$_IP}'");

                //Отправляем письмо на почту для воостановления
//                include_once __DIR__.'/../Classes/mail.php';

                $config = Settings::loadsettings();

                $mail = new Mail($config);
                $message = <<<HTML
                        Здравствуйте, {$check['user_name']}.
                        
                        Чтобы сменить ваш пароль, пройдите по этой ссылке:
                        {$config['home_url']}restore?act=prefinish&h={$hash}
                        
                        Мы благодарим Вас за участие в жизни нашего сайта.
                        
                        {$config['home_url']}
                        HTML;
                $mail->send($email, $lang['lost_subj'], $message);
            }
        }
    }

    /**
     *  Страница смены пароля
     *
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function prefinish($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if(!$logged){
            $params['title'] = $lang['restore_title'].' | Sura';

            $request = (Request::getRequest()->getGlobal());

            $hash = $db->safesql(Validation::strip_data($request['h']));
            $row = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$hash}' AND ip = '{$_IP}'");
            if($row){
                $info = $db->super_query("SELECT user_name FROM `users` WHERE user_email = '{$row['email']}'");
//                $tpl->load_template('restore/prefinish.tpl');
//                $tpl->set('{name}', $info['user_name']);

                $salt = "abchefghjkmnpqrstuvwxyz0123456789";
                $rand_lost = 0;
                for($i = 0; $i < 15; $i++){
                    $rand_lost .= $salt[rand(0, 33)];
                }
                $server_time = \Sura\Libs\Tools::time();

                $newhash = md5($server_time.$row['email'].rand(0, 100000).$rand_lost);
//                $tpl->set('{hash}', $newhash);
                $db->query("UPDATE `restore` SET hash = '{$newhash}' WHERE email = '{$row['email']}'");

//                $tpl->compile('content');
                return view('info.info', $params);
            }
                $speedbar = $lang['no_infooo'];
                msg_box($lang['restore_badlink'], 'info');


            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    /**
     * Смена пароля
     *
     * @param $params
     */
    public function finish($params){
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');

        Tools::NoAjaxRedirect();

        if(!$logged){
            $params['title'] = $lang['restore_title'].' | Sura';

            $request = (Request::getRequest()->getGlobal());
//
            $hash = $db->safesql(Validation::strip_data($request['hash']));
            $row = $db->super_query("SELECT email FROM `restore` WHERE hash = '{$hash}' AND ip = '{$_IP}'");
            if($row){

                $request['new_pass'] = Validation::ajax_utf8($request['new_pass']);
                $request['new_pass2'] = Validation::ajax_utf8($request['new_pass2']);

                $new_pass = md5(md5($request['new_pass']));
                $new_pass2 = md5(md5($request['new_pass2']));

                if(strlen($new_pass) >= 6 AND $new_pass == $new_pass2){
                    $db->query("UPDATE `users` SET user_password = '{$new_pass}' WHERE user_email = '{$row['email']}'");
                    $db->query("DELETE FROM `restore` WHERE email = '{$row['email']}'");
                }
            }
        }
    }

    /**
     * Восстановление доступа к странице
     *
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function index($params): string
    {
        $lang = $this->get_langs();
//        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if(!$logged){
            $params['title'] = $lang['restore_title'].' | Sura';

            $data  = array();
            $data['title'] = $lang['restore_title'].' | Sura';
            return view('restore.main', array("title"=>$data['title']));
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }

    }

    /**
     * Проверка данных на воостановления
     *
     * @param $params
     */
    public function next($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        Tools::NoAjaxRedirect();

        if(!$logged) {
            $meta_tags['title'] = $lang['restore_title'];

            $request = (Request::getRequest()->getGlobal());

            $email = Validation::ajax_utf8($request['email']);
            $check = $db->super_query("SELECT user_id, user_search_pref, user_photo FROM `users` WHERE user_email = '{$email}'");
            if($check){
                if($check['user_photo'])
                    $check['user_photo'] = "/uploads/users/{$check['user_id']}/50_{$check['user_photo']}";
                else
                    $check['user_photo'] = "/images/no_ava_50.png";

                echo $check['user_search_pref']."|".$check['user_photo'];
            } else
                echo 'no_user';

        }
    }
}