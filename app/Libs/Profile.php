<?php

declare(strict_types=1);

namespace App\Libs;

use Sura\Libs\Gramatic;
use Sura\Libs\Langs;
use Sura\Libs\Settings;
use Sura\Time\Date;

class Profile
{
	/**
	 * @param $user_year
	 * @param $user_month
	 * @param $user_day
	 * @return false|string
	 */
	public static function user_age($user_year, $user_month, $user_day): false|string
	{
		$server_time = Date::time();
		
		$current_year = date('Y', $server_time);
		$current_month = date('n', $server_time);
		$current_day = date('j', $server_time);
		
		$current_str = strtotime($current_year . '-' . $current_month . '-' . $current_day);
		$current_user = strtotime($current_year . '-' . $user_month . '-' . $user_day);
		
		if ($current_str >= $current_user)
			$user_age = $current_year - $user_year;
		else
			$user_age = $current_year - $user_year - 1;
		
		if ($user_month and $user_day) {
			$titles = array('год', 'года', 'лет');
			return $user_age . ' ' . Gramatic::declOfNum($user_age, $titles);
		} else
			return false;
	}
	
	/**
	 * @param $time
	 * @param false $mobile
	 * @return string
	 */
	public static function Online($time, $mobile = false): string
	{
		$lang = langs::get_langs();
		$config = Settings::load();
		$server_time = (int)$_SERVER['REQUEST_TIME'];
		$online_time = $server_time - $config['online_time'];
		
		//Если человек сидит с мобильнйо версии
		if ($mobile) {
			$mobile_icon = '<img src="/images/spacer.gif" class="mobile_online"  alt=""/>';
		} else {
			$mobile_icon = '';
		}
		
		if ($time >= $online_time) {
			return $lang['online'] . $mobile_icon;
		}
		return '';
	}
}
