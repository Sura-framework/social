<?php
/* 
	Appointment: Баланс
	File: balance.php 
	Author: f0rt1 
	Engine: Vii Engine
	Copyright: NiceWeb Group (с) 2011
	e-mail: niceweb@i.ua
	URL: http://www.niceweb.in.ua/
	ICQ: 427-825-959
	Данный код защищен авторскими правами
*/

namespace System\Modules;

use System\Libs\Gramatic;
use System\Libs\Langs;
use System\Libs\Page;
use System\Libs\Registry;
use System\Libs\Tools;

class BalanceController extends Module{

    public function code($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            Tools::NoAjaxQuery();

            $code=$_POST['code'];
            $res = $db->super_query("SELECT COUNT(*) FROM `".PREFIX."_codes` WHERE code = '{$code}' LIMIT 1");
            $row = $db->super_query("SELECT * FROM `".PREFIX."_codes` WHERE code = '{$code}' LIMIT 1");
            if($res['COUNT(*)'] !=0){
                if($row['activate'] == 0 AND $row['user_id'] == 0){
                    $db->super_query("UPDATE `".PREFIX."_users` SET user_balance=user_balance+'{$row['fbm']}', balance_rub=balance_rub+'{$row['rub']}', user_rating=user_rating+'{$row['rating']}' WHERE user_id='{$user_id}'");
                    $db->super_query("UPDATE `".PREFIX."_codes` SET activate = 1, user_id ='{$user_id}' WHERE code='{$code}'");
                    echo 'ok';
                } else echo '2';

            } else echo '1';

            exit();
        }
    }
    public function invite($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            $tpl->load_template('balance/invite.tpl');
            $tpl->set('{uid}', $user_id);
            $tpl->compile('content');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }
    public function invited($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            $tpl->load_template('balance/invited.tpl');
            $tpl->compile('info');
            $sql_ = $db->super_query("SELECT tb1.ruid, tb2.user_name, user_search_pref, user_birthday, user_last_visit, user_photo, user_logged_mobile FROM `".PREFIX."_invites` tb1, `".PREFIX."_users` tb2 WHERE tb1.uid = '{$user_id}' AND tb1.ruid = tb2.user_id", 1);
            if($sql_){
                $tpl->load_template('balance/invitedUser.tpl');
                foreach($sql_ as $row){
                    $user_country_city_name = explode('|', $row['user_country_city_name']);
                    $tpl->set('{country}', $user_country_city_name[0]);

                    if($user_country_city_name[1])
                        $tpl->set('{city}', ', '.$user_country_city_name[1]);
                    else
                        $tpl->set('{city}', '');

                    $tpl->set('{user-id}', $row['ruid']);
                    $tpl->set('{name}', $row['user_search_pref']);

                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$row['ruid'].'/100_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/100_no_ava.png');

                    //Возраст юзера
                    $user_birthday = explode('-', $row['user_birthday']);
                    $tpl->set('{age}', user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]));

                    $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                    $tpl->set('{online}', $online);

                    $tpl->compile('content');
                }
            } else
                msgbox('', '<br /><br />Вы еще никого не приглашали.<br /><br /><br />', 'info_2');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
    public function payment($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            NoAjaxQuery();

            $owner = $db->super_query("SELECT balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/payment.tpl');

            if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else $tpl->set('{ava}', "/images/no_ava_50.png");

            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{text-rub}', Gramatic::declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей')));
            $tpl->set('{user-id}', $user_info['user_id']);

            $config = include __DIR__.'/../data/config.php';

            $tpl->set('{sms_number}', $config['sms_number']);

            $tpl->compile('content');

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }
    public function payment_2($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            NoAjaxQuery();
            $config = include __DIR__.'/../data/config.php';

            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/payment_2.tpl');

            if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else $tpl->set('{ava}', "/images/no_ava_50.png");

            $tpl->set('{balance}', $owner['user_balance']);
            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{cost}', $config['cost_balance']);

            $tpl->compile('content');

            Tools::AjaxTpl($tpl);

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }
    public function ok_payment($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            NoAjaxQuery();

            $num = intval($_POST['num']);
            if($num <= 0) $num = 0;

            $config = include __DIR__.'/../data/config.php';

            $resCost = $num * $config['cost_balance'];

            //Выводим тек. баланс юзера (руб.)
            $owner = $db->super_query("SELECT balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            if($owner['balance_rub'] >= $resCost){

                $db->query("UPDATE `".PREFIX."_users` SET user_balance = user_balance + '{$num}', balance_rub = balance_rub - '{$resCost}' WHERE user_id = '{$user_id}'");

            } else
                echo '1';

            exit();
        }
    }

    /**
     * Вывод текущего счета
     */
    public function index($params)
    {
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['balance'];
            $mobile_speedbar = $lang['balance'];

            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `".PREFIX."_users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/main.tpl');

            $tpl->set('{ubm}', $owner['user_balance']);
            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{text-rub}', Gramatic::declOfNum($owner['balance_rub'], array('рубль', 'рубля', 'рублей')));

            $tpl->compile('content');
            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}