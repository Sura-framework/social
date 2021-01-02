<?php

namespace App\Modules;

use App\Services\Cache;
use Exception;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Tools;

class BalanceController extends Module{

    /**
     * @param $params
     * @return string
     */
    public function code($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $requests = Request::getRequest();
        $request = ($requests->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';

            $code=$request['code'];
            $res = $db->super_query("SELECT COUNT(*) FROM `codes` WHERE code = '{$code}' LIMIT 1");
            $row = $db->super_query("SELECT * FROM `codes` WHERE code = '{$code}' LIMIT 1");
            if($res['COUNT(*)'] !=0){
                if($row['activate'] == 0 AND $row['user_id'] == 0){
                    $db->super_query("UPDATE `users` SET user_balance=user_balance+'{$row['fbm']}', balance_rub=balance_rub+'{$row['rub']}', user_rating=user_rating+'{$row['rating']}' WHERE user_id='{$user_id}'");
                    $db->super_query("UPDATE `codes` SET activate = 1, user_id ='{$user_id}' WHERE code='{$code}'");
                    return _e('ok');
                } else {
                    return _e('2');
                }

            } else {
                return _e('1');
            }
        }
    }

    /**
     * Страница приглашения дурга
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function invite($params): string
    {
        $lang = $this->get_langs();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
            $params['uid'] = $user_id;
            $params['site'] = 'https://'.$_SERVER['HTTP_HOST'].'/u';

            $params['menu'] = \App\Models\Menu::settings();

            return view('balance.invite', $params);
        }
        return view('info.info', $params);
    }

    /**
     *  Страница приглашённых друзей
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function invited($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';

            $sql_ = $db->super_query("SELECT tb1.ruid, tb2.user_name, user_search_pref, user_birthday, user_last_visit, user_photo, user_logged_mobile FROM `invites` tb1, `users` tb2 WHERE tb1.uid = '{$user_id}' AND tb1.ruid = tb2.user_id", 1);

            if($sql_){
//                $tpl->load_template('balance/invitedUser.tpl');
                foreach($sql_ as $row){
                    $user_country_city_name = explode('|', $row['user_country_city_name']);
//                    $tpl->set('{country}', );
                    $row['country'] = $user_country_city_name[0];
                    if($user_country_city_name[1]){
//                        $tpl->set('{city}', ', );
                        $row['city'] = '.$user_country_city_name[1]';
                    }
                    else{
//                       $tpl->set('{city}', '');
                        $row['city'] = '';
                    }

//                    $tpl->set('{user-id}', );
                    $row['user_id'] = $row['ruid'];
//                        $tpl->set('{name}', );
                    $row['name'] = $row['user_search_pref'];

                    if($row['user_photo']){
//                        $tpl->set('{ava}', );
                        $row['ava'] = '/uploads/users/'.$row['ruid'].'/100_'.$row['user_photo'];
                    }
                    else{
//                        $tpl->set('{ava}', ;
                        $row['ava'] = '/images/100_no_ava.png';
                    }

                    //Возраст юзера
                    $user_birthday = explode('-', $row['user_birthday']);
//                    $tpl->set('{age}', );
                    $row['age'] = user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

                    $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
//                    $tpl->set('{online}', );
                    $row['online'] = $online;

//                    $tpl->compile('info');
                }
                $params['invited'] = $sql_;
            } else{
                $params['invited'] = false;
            }
            $params['menu'] = \App\Models\Menu::settings();

            return view('balance.invited', $params);
        }
        return view('info.info', $params);
    }

    /**
     * @param $params
     * @return bool
     */
    public function payment($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
//            $mobile_speedbar = $lang['balance'];


            $owner = $db->super_query("SELECT balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/payment.tpl');

            if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else $tpl->set('{ava}', "/images/no_ava_50.png");

            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{text-rub}', Gramatic::declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей')));
            $tpl->set('{user-id}', $user_info['user_id']);

            $config = Settings::loadsettings();

            $tpl->set('{sms_number}', $config['sms_number']);

            $tpl->compile('content');



            $params['tpl'] = $tpl;
//            Page::generate($params);
            return true;
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function payment_2($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
//            $mobile_speedbar = $lang['balance'];

            $config = Settings::loadsettings();

            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/payment_2.tpl');

            if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else $tpl->set('{ava}', "/images/no_ava_50.png");

            $tpl->set('{balance}', $owner['user_balance']);
            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{cost}', $config['cost_balance']);

            $tpl->compile('content');



            $params['tpl'] = $tpl;
//            Page::generate($params);
            return true;
        }
    }

    /**
     * @param $params
     */
    public function ok_payment($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $requests = Request::getRequest();
        $request = ($requests->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
//            $mobile_speedbar = $lang['balance'];

            $num = (int)$request['num'];
            if($num <= 0) $num = 0;

            $config = Settings::loadsettings();

            $resCost = $num * $config['cost_balance'];

            //Выводим тек. баланс юзера (руб.)
            $owner = $db->super_query("SELECT balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            if($owner['balance_rub'] >= $resCost){

                $db->query("UPDATE `users` SET user_balance = user_balance + '{$num}', balance_rub = balance_rub - '{$resCost}' WHERE user_id = '{$user_id}'");

            } else
                echo '1';

            exit();
        }
    }

    /**
     * Вывод текущего счета
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function index($params): string
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $user_id = $id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';

            $Cache = cache_init(array('type' => 'file'));
            try {
                $value = $Cache->get("users/{$id}/balance", $default = null);
                $row = $owner = unserialize($value);
            }catch (Exception $e){
                $dir = __DIR__.'/../cache/users/'.$id.'/';
                if(!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $row = $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");

                $value = serialize($row);

                $Cache->set("users/{$id}/balance", $value);
            }

//            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            $params['ubm'] = $owner['user_balance'];
            $params['rub'] = $owner['balance_rub'];
            $params['text_rub'] = Gramatic::declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей'));

            $params['menu'] = \App\Models\Menu::settings();

            return view('balance.main', $params);
        } else {
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}