<?php

namespace App\Modules;

use App\Models\Admin;
use thiagoalessio\TesseractOCR\TesseractOCR;

class AdminController extends Module{

    /**
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function main($params)
    {
        $logged = $params['user']['logged'];
        $user_info = $params['user']['user_info'];
        $group = $user_info['user_group'];
        if ($logged == true AND $group == '1'){
//            $tpl = $params['tpl'];

            $modules = Admin::modules();

//            $tpl->load_template('admin/modules.tpl');
//            foreach ($modules as $mod){
//                $tpl->set('{title}', $mod['name']);
//                $tpl->set('{description}', $mod['description']);
//                $tpl->set('{link}', $mod['link']);
//                $tpl->set('{img}', $mod['img']);

//                $tpl->compile('modules');
//            }
            $params['modules'] = $modules;
//            $params['country'] = $all_country;

//            $tpl->load_template('admin/admin.tpl');
//            $tpl->set('{modules}', $tpl->result['modules']);
            //$tpl->set('{country}', $all_country);
//            $tpl->compile('content');
//            $tpl->clear();
//            $params['tpl'] = $tpl;
//            Page::generate($params);
            return view('admin.main', $params);
        }
        return view('info.info', $params);
    }

    /**
     * @param $params
     * @return string
     * @throws \Exception
     */
    public function stats($params): string
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
            return view('info.info', $params);
        }
        return view('info.info', $params);
    }

    public function settings($params): string
    {
        return view('info.info', $params);
    }
    public function dbsettings($params): string
    {
        return view('info.info', $params);
    }
    public function mysettings($params): string
    {
        return view('info.info', $params);
    }
    public function users($params): string
    {
        return view('info.info', $params);
    }
    public function video($params): string
    {
        return view('info.info', $params);
    }
    public function music($params): string
    {
        return view('info.info', $params);
    }
    public function photos($params): string
    {
        return view('info.info', $params);
    }
    public function gifts($params): string
    {
        return view('info.info', $params);
    }
    public function groups($params): string
    {
        return view('info.info', $params);
    }
    public function report($params): string
    {
        return view('info.info', $params);
    }
    public function mail_tpl($params): string
    {
        return view('info.info', $params);
    }
    public function mail($params): string
    {
        return view('info.info', $params);
    }
    public function ban($params): string
    {
        return view('info.info', $params);
    }
    public function search($params): string
    {
        return view('info.info', $params);
    }
    public function static($params): string
    {
        return view('info.info', $params);
    }
    public function logsusers($params): string
    {
        return view('info.info', $params);
    }
    public function country($params): string
    {
        return view('info.info', $params);
    }
    public function city($params): string
    {
        return view('info.info', $params);
    }
    public function ads($params): string
    {
        return view('info.info', $params);
    }

}