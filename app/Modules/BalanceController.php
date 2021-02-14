<?php
declare(strict_types=1);
namespace App\Modules;

use Exception;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Status;
use Sura\Libs\Tools;

class BalanceController extends Module{

    /**
     * @return int
     * @throws \JsonException
     */
    public function code(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';

            $code=$request['code'];
            $res = $db->super_query("SELECT COUNT(*) FROM `codes` WHERE code = '{$code}' LIMIT 1");
            if($res['COUNT(*)'] !=0){
                $row = $db->super_query("SELECT * FROM `codes` WHERE code = '{$code}' LIMIT 1");
                if($row['activate'] == 0 AND $row['user_id'] == 0){
                    $db->super_query("UPDATE `users` SET user_balance=user_balance+'{$row['fbm']}', balance_rub=balance_rub+'{$row['rub']}', user_rating=user_rating+'{$row['rating']}' WHERE user_id='{$user_id}'");
                    $db->super_query("UPDATE `codes` SET activate = 1, user_id ='{$user_id}' WHERE code='{$code}'");
                    $status = Status::TTT;
                }else{
                    $status = Status::NOT_FOUND;
                }
            }else{
                $status = Status::NOT_FOUND;
            }
        }else{
            $status = Status::BAD_LOGGED;
        }
        return _e_json(array(
            'status' => $status,
        ) );
    }

    /**
     * Страница приглашения дурга
     *
     * @return int
     * @throws Exception
     */
    public function invite(): int
    {
        $lang = $this->get_langs();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
            $params['uid'] = $user_id;
            $params['site'] = 'https://'.$_SERVER['HTTP_HOST'].'/u';

            $params['menu'] = \App\Models\Menu::settings();

            return view('balance.invite', $params);
        }
        $params = array();
        return view('info.info', $params);
    }

    /**
     *  Страница приглашённых друзей
     *
     * @return int
     * @throws Exception
     */
    public function invited(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

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
                    $row['age'] = \App\Libs\Profile::user_age($user_birthday[0], $user_birthday[1], $user_birthday[2]);

                    $online = \App\Libs\Profile::Online($row['user_last_visit'], $row['user_logged_mobile']);
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
        $params = array();
        return view('info.info', $params);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function payment(){
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

            $config = Settings::load();

            $tpl->set('{sms_number}', $config['sms_number']);

            $tpl->compile('content');



            $params['tpl'] = $tpl;
//            Page::generate();
            return true;
        }
    }

    /**
     * @return bool
     * @deprecated
     */
    public function payment_2(){
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

            $config = Settings::load();

            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            $tpl->load_template('balance/payment_2.tpl');

            if($user_info['user_photo']) $tpl->set('{ava}', "/uploads/users/{$user_info['user_id']}/50_{$user_info['user_photo']}");
            else $tpl->set('{ava}', "/images/no_ava_50.png");

            $tpl->set('{balance}', $owner['user_balance']);
            $tpl->set('{rub}', $owner['balance_rub']);
            $tpl->set('{cost}', $config['cost_balance']);

            $tpl->compile('content');



            $params['tpl'] = $tpl;
//            Page::generate();
            return true;
        }
    }

    /**
     * @param $params
     * @deprecated
     */
    public function ok_payment(){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $request = (Request::getRequest()->getGlobal());

        if($logged){
            $user_id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';
//            $mobile_speedbar = $lang['balance'];

            $num = (int)$request['num'];
            if($num <= 0) $num = 0;

            $config = Settings::load();

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
     * @return int
     */
    public function index(): int
    {
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $user_id = $id = $user_info['user_id'];
            $params['title'] = $lang['balance'].' | Sura';

            $storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
            $cache = new \Sura\Cache\Cache($storage, 'users');

            try {
                $value = $cache->load("{$id}/balance");
                $row = $owner = unserialize($value, $options = []);
            }catch (Exception $e){
                $dir = __DIR__.'/../cache/users/'.$id.'/';
                if(!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }

                $row = $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");
                $value = serialize($row);
                $cache->save("{$id}/balance", $value);
            } catch (\Throwable $e) {

            }

            $owner = $db->super_query("SELECT user_balance, balance_rub FROM `users` WHERE user_id = '{$user_id}'");

            $params['ubm'] = $owner['user_balance'];
            $params['rub'] = $owner['balance_rub'];
            $params['text_rub'] = Gramatic::declOfNum((int)$owner['balance_rub'], array('рубль', 'рубля', 'рублей'));

            $params['menu'] = \App\Models\Menu::settings();

            return view('balance.main', $params);
        }

        $params['title'] = $lang['no_infooo'];
        $params['info'] = $lang['not_logged'];
        return view('info.info', $params);
    }
}