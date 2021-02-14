<?php

namespace App\Modules;

use App\Models\Admin;

class AdminController extends Module
{
	
	/**
	 * @throws \Exception
	 */
	public function main(): int
	{
		$params = array();
		$logged = $this->logged();
		$user_info = $this->user_info();
		$group = $user_info['user_group'];
		if ($logged == true and $group == '1') {
			$modules = Admin::modules();
			$params['modules'] = $modules;
			return view('admin.main', $params);
		}
		return view('info.info', $params);
	}
	
	/**
	 * @return int
	 */
	public function stats(): int
	{
		$logged = $params['user']['logged'];
		$user_info = $params['user']['user_info'];
		$group = $user_info['user_group'];
		if ($logged == true and $group == '1') {
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
//            Page::generate();
			return view('info.info', $params);
		}
		return view('info.info', $params);
	}
	
	public function settings($params): int
	{
		return view('info.info', $params);
	}
	
	public function dbsettings($params): int
	{
		return view('info.info', $params);
	}
	
	public function mysettings($params): int
	{
		return view('info.info', $params);
	}
	
	public function users($params): int
	{
		return view('info.info', $params);
	}
	
	public function video($params): int
	{
		return view('info.info', $params);
	}
	
	public function music($params): int
	{
		return view('info.info', $params);
	}
	
	public function photos($params): int
	{
		return view('info.info', $params);
	}
	
	public function gifts($params): int
	{
		return view('info.info', $params);
	}
	
	public function groups($params): int
	{
		return view('info.info', $params);
	}
	
	public function report($params): int
	{
		return view('info.info', $params);
	}
	
	public function mail_tpl($params): int
	{
		return view('info.info', $params);
	}
	
	public function mail($params): int
	{
		return view('info.info', $params);
	}
	
	public function ban($params): int
	{
		return view('info.info', $params);
	}
	
	public function search($params): int
	{
		return view('info.info', $params);
	}
	
	public function static($params): int
	{
		return view('info.info', $params);
	}
	
	public function logsusers($params): int
	{
		return view('info.info', $params);
	}
	
	public function country($params): int
	{
		return view('info.info', $params);
	}
	
	public function city($params): int
	{
		return view('info.info', $params);
	}
	
	public function ads($params): int
	{
		return view('info.info', $params);
	}
	
}