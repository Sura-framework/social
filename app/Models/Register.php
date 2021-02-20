<?php

declare(strict_types=1);

namespace App\Models;

use Sura\Libs\Db;

class Register
{
	
	/**
	 * @param $user_email
	 * @return array
	 */
	public static function check_email(string $user_email): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = '{$user_email}'");
	}
	
	/**
	 * @param $user_country
	 * @return array
	 */
	public static function country_info(int $user_country): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT name FROM `country` WHERE id = '" . $user_country . "'");
	}
	
	/**
	 * @param $user_city
	 * @return array
	 */
	public static function city_info(int $user_city): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT name FROM `city` WHERE id = '" . $user_city . "'");
	}
	
}