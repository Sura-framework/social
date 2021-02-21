<?php
declare(strict_types=1);

namespace App\Libs;

use Sura\Libs\Db;

class Antispam
{
	/** @var array|int[] Лимиты на день */
	protected static array $max_int = array(1 => 40,#max заявок в друзья
		2 => 40,#max сообщений не друзьям
		3 => 100,#max записей на стену /wall
		4 => 100,#max одинаковых текстовых данных /identical
		5 => 300,#max комментариев к записям на стенах людей и сообществ /comments
		6 => 2,#max сообществ за день
		7 => 1, 8 => 1, 9 => 1,//bugs
		10 => 1, 11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 1, 16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1,);
	
	/**
	 * spam check
	 *
	 * @param int $type
	 * @param int $user_id
	 * @param string|false $text
	 * @return bool
	 */
	public static function Check(int $type, int $user_id, string|false $text = false): bool
	{
		$db = Db::getDB();
		$server_time = (int)$_SERVER['REQUEST_TIME'];
		$text = $text ? md5($text) : '';
		/* Типы
			1 - Друзья
			2 - Сообщения не друзьям
			3 - Записей на стену
			4 - Проверка на одинаковый текст
			5 - Комментарии к записям (стены групп/людей)
			6 - groups
			7 - photo
			8 - audio
			9 - bugs
			10 - Distinguish
			11 - doc
			12 - fave
			13 - gifts
			14 - forum
			15 - rating
			16 - report
			17 - repost
			18 - restore
			19 - edit name
			20 - stories
			21 - альбомы
		*/
		/** @var  $antiDate -Анти спам дата */
		$antiDate = strtotime(date('Y-m-d', $server_time));
		$check = $db->super_query("SELECT COUNT(*) AS cnt FROM `antispam` WHERE act = '{$type}' AND user_id = '{$user_id}' AND date = '{$antiDate}' AND txt = '{$text}'");
		/** Если кол-во, логов больше, то ставим блок */
		if ($check['cnt']) {
			if ($check['cnt'] >= self::$max_int[$type]) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * @param int $type
	 * @param int $user_id
	 * @param string|false $text
	 * @return bool
	 */
	public static function LogInsert(int $type, int $user_id, string|false $text = false): bool
	{
		$db = Db::getDB();
		$server_time = (int)$_SERVER['REQUEST_TIME'];
		$text = $text ? md5($text) : '';
		//Анти спам дата
		$antiDate = strtotime(date('Y-m-d', $server_time));
		$res = $db->query("INSERT INTO `antispam` SET act = '{$type}', user_id = '{$user_id}', date = '{$antiDate}', txt = '{$text}'");
		if ($res) {
			return true;
		}
		return false;
	}
}

