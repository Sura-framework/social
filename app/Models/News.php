<?php

declare(strict_types=1);

namespace App\Models;


use Exception;
use Sura\Libs\Db;
use Sura\Libs\Model;

class News
{
	
	/**
	 * @var Db|null
	 */
	private static ?Db $db;
	private \Sura\Database\Connection $database;
	
	public function __construct()
	{
		self::$db = Db::getDB();
		$this->database = Model::getDB();
	}
	
	/**
	 * @param int $user_id
	 * @param int $page
	 * @param int $limit
	 * @return array
	 * @throws \Throwable
	 */
	public function load_news(int $user_id, int $page, int $limit = 20): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `news` tb1 WHERE tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2
                WHERE user_id = '{$user_id}' AND tb1.action_type IN (1,2,3) AND subscriptions != 2) 
            OR 
                tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2 
                WHERE user_id = '{$user_id}' AND tb1.action_type = 11 AND subscriptions = 2) 
            AND tb1.action_type IN (1,2,3,11)	ORDER BY tb1.action_time DESC LIMIT {$page}, 20", 1);
	}
	
	public function load_news_profile(int $user_id, int $page, int $limit = 20): array
	{
		return $this->database->fetchAll("SELECT ac_id, ac_user_id, for_user_id, action_text, action_time, action_type, obj_id, answer_text, link
            FROM news WHERE ac_user_id = ? AND action_type = 1", (array)$user_id);
	}
	
	/**
	 * @param $user_id
	 * @param $type
	 * @return array
	 */
	public function row_type11(int $user_id, int $type): array
	{
		try {
			if ($type == 1) {
				$res = $this->database->fetch("SELECT user_search_pref, user_last_visit, user_logged_mobile, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = ?", (array)$user_id);
			} else {
                $res = $this->database->fetch("SELECT title, photo, comments FROM `communities` WHERE id = ?", (array)$user_id);
			}
			if (empty($res)){
                throw new Exception('err');
            }
			return $res;

		} catch (Exception $e) {
			return array();
		}
		
	}
	
	/**
	 * @param $user_id
	 * @return array
	 */
	public function friend_info(int $user_id): array
	{
		return $this->database->fetch("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function wall_info(int $id): array
	{
		return $this->database->fetch("SELECT id, author_user_id, for_user_id, text, add_date, tell_uid, tell_date, type, public, attach, tell_comm, fast_comm_id FROM `wall` WHERE id = '{$id}'");
	}
	
	/**
	 * @param $id
	 * @return array
	 * @throws \Throwable
	 */
	public function video_info(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		$key = $id . '/wall/video' . $id;
		
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		
		if ($value == null) {
			$row = $this->database->fetchAll("SELECT video, title FROM `videos` WHERE id = ?", (array)$id);
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id int
	 * @return array
	 */
	public function audio_info(int $id): array
	{
		return $this->database->fetch("SELECT artist, title, url FROM `audio` WHERE oid = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function doc_info(int $id): array
	{
		return $this->database->fetch("SELECT dname, dsize FROM `doc` WHERE did = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 * @throws \Throwable
	 */
	public function vote_info(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		$value = $cache->load("{$id}/votes/vote_{$id}");
		if ($value == null) {
			$db = Db::getDB();
			$row = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'", false);
			$value = serialize($row);
			$cache->save("{$id}/votes/vote_{$id}", $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 * @throws \Throwable
	 */
	public function vote_info_check(int $id, int $user_id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		$value = $cache->load("user_{$id}/votes/check{$user_id}_{$id}");
		if ($value == null) {
			$db = Db::getDB();
			$row = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'", false);
			$value = serialize($row);
			$cache->save("user_{$id}/votes/check{$user_id}_{$id}", $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @return array
	 * @throws \Throwable
	 */
	public function vote_info_answer(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'votes');
		$value = $cache->load("vote_answer_cnt_{$id}");
		if ($value == null) {
			$row = $this->database->fetchAll("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = ? GROUP BY answer", (array)$id);
			$value = serialize($row);
			$cache->save("vote_answer_cnt_{$id}", $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $user_id
	 * @param $type
	 * @return array
	 */
	public function user_tell_info(int $user_id, int $type): array
	{
		$db = Db::getDB();
		if ($type == 1) {
			return $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
		} else {
			return $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$user_id}'", false);
		}
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function likes_info(int $id): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT id, author_user_id, for_user_id, text, add_date, tell_uid, tell_date, type, public, attach, tell_comm FROM `wall` WHERE id = '{$id}'");
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function delete(int $id): array
	{
		$db = Db::getDB();
		return $db->super_query("DELETE FROM `news` WHERE ac_id = '{$id}'");
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function rec_info(int $id): array
	{
		$res = $this->database->fetch("SELECT fasts_num, likes_num, likes_users, tell_uid, tell_date, type, public, attach, tell_comm FROM `wall` WHERE id = ?", (array)$id);
		if ($res['fasts_num'] < 3) {
			$res['fasts_num'] = '';
		}
		return $res;
	}
	
	/**
	 * @param $id
	 * @param $limit
	 * @return array
	 */
	public function comments(int $id, int $limit): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` LIMIT {$limit}, 3", 1);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function rec_info_groups(int $id): array
	{
		$res = $this->database->fetch("SELECT fasts_num, likes_num, likes_users, attach, tell_uid, tell_date, tell_comm, public FROM `communities_wall` WHERE id = ?", (array)$id);
		if ($res['fasts_num'] < 3) {
			$res['fasts_num'] = '';
		}
		return $res;
	}
	
}