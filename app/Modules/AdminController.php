<?php

namespace App\Modules;

use App\Models\Admin;
use thiagoalessio\TesseractOCR\TesseractOCR;

class AdminController extends Module{

    /**
     * @param $params
     * @return bool
     */
    public function main($params)
    {
        $logged = $params['user']['logged'];
        $user_info = $params['user']['user_info'];
        $group = $user_info['user_group'];
        if ($logged == true AND $group == '1'){
//            $tpl = $params['tpl'];

            $modules = Admin::modules();
//            var_dump($modules);

//            $tpl->load_template('admin/modules.tpl');
            foreach ($modules as $mod){
//                $tpl->set('{title}', $mod['name']);
//                $tpl->set('{description}', $mod['description']);
//                $tpl->set('{link}', $mod['link']);
//                $tpl->set('{img}', $mod['img']);

//                $tpl->compile('modules');
            }


//            $tpl->load_template('admin/admin.tpl');
//            $tpl->set('{modules}', $tpl->result['modules']);
            //$tpl->set('{country}', $all_country);
//            $tpl->compile('content');
//            $tpl->clear();
//            $params['tpl'] = $tpl;
//            Page::generate($params);
        }
        return true;
    }

    /**
     * @param $params
     * @return bool
     */
    public function stats($params)
    {
        $logged = $params['user']['logged'];
        $user_info = $params['user']['user_info'];
        $group = $user_info['user_group'];
        if ($logged == true AND $group == '1'){
//            $tpl = $params['tpl'];

            $db = $this->db();
            $users = $db->super_query("SELECT COUNT(*) AS cnt FROM `users`");
            $albums = $db->super_query("SELECT COUNT(*) AS cnt FROM `albums`");
            $attach = $db->super_query("SELECT COUNT(*) AS cnt FROM `attach`");
            $audio = $db->super_query("SELECT COUNT(*) AS cnt FROM `audio`");
            $groups = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities`");
            //$clubs = $db->super_query("SELECT COUNT(*) AS cnt FROM `clubs`");
            $groups_wall = $db->super_query("SELECT COUNT(*) AS cnt FROM `communities_wall`");
            $invites = $db->super_query("SELECT COUNT(*) AS cnt FROM `invites`");
//            $notes = $db->super_query("SELECT COUNT(*) AS cnt FROM `notes`");
            $videos = $db->super_query("SELECT COUNT(*) AS cnt FROM `videos`");

            //Баланс
            //SELECT user_id, SUM(user_balance) AS user_balance FROM `users` GROUP BY user_id
            $balance_full = $db->super_query("SELECT SUM(user_balance) AS user_balance FROM `users` ");

//            $tpl->load_template('admin/stats.tpl');
            //$tpl->set('{modules}', $tpl->result['modules']);

//            $tpl->set('{users}', $users['cnt']);
//            $tpl->set('{balance_full}', $balance_full['user_balance']);

            //$tpl->set('{country}', $all_country);
//            $tpl->compile('content');
//            $tpl->clear();
//            $params['tpl'] = $tpl;
//            Page::generate($params);
        }
        return true;
    }

}