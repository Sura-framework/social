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

use System\Libs\Cache;
use System\Libs\Page;
use System\Libs\Registry;
use System\Models\Admin;
use thiagoalessio\TesseractOCR\TesseractOCR;

class AdminController extends Module{

    public function main($params)
    {
        $logged = $params['user']['logged'];
        $user_info = $params['user']['user_info'];
        $group = $user_info['user_group'];
        if ($logged == true AND $group == '1'){
            $tpl = $params['tpl'];

            $modules = Admin::modules();
//            var_dump($modules);

            $tpl->load_template('admin/modules.tpl');
            foreach ($modules as $mod){
                $tpl->set('{title}', $mod['name']);
                $tpl->set('{description}', $mod['description']);
                $tpl->set('{link}', $mod['link']);
                $tpl->set('{img}', $mod['img']);

                $tpl->compile('modules');
            }


            $tpl->load_template('admin/admin.tpl');
            $tpl->set('{modules}', $tpl->result['modules']);
            //$tpl->set('{country}', $all_country);
            $tpl->compile('content');
            $tpl->clear();
            $params['tpl'] = $tpl;
            Page::generate($params);
        }
        return true;
    }

    public function stats($params)
    {
        $logged = $params['user']['logged'];
        $user_info = $params['user']['user_info'];
        $group = $user_info['user_group'];
        if ($logged == true AND $group == '1'){
            $tpl = $params['tpl'];

            $db = $this->db();
            $users = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_users`");
            $albums = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_albums`");
            $attach = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_attach`");
            $audio = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_audio`");
            $groups = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_communities`");
            //$clubs = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_clubs`");
            $groups_wall = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_communities_wall`");
            $invites = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_invites`");
            $notes = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_notes`");
            $videos = $db->super_query("SELECT COUNT(*) AS cnt FROM `vii_videos`");

            //Баланс
            //SELECT user_id, SUM(user_balance) AS user_balance FROM `vii_users` GROUP BY user_id
            $balance_full = $db->super_query("SELECT SUM(user_balance) AS user_balance FROM `vii_users` ");
//var_dump($balance_full);
            $tpl->load_template('admin/stats.tpl');
            //$tpl->set('{modules}', $tpl->result['modules']);

            $tpl->set('{users}', $users['cnt']);
            $tpl->set('{balance_full}', $balance_full['user_balance']);

            //$tpl->set('{country}', $all_country);
            $tpl->compile('content');
            $tpl->clear();
            $params['tpl'] = $tpl;
            Page::generate($params);
        }
        return true;
    }

}