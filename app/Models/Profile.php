<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Sura\Libs\Model;

class Profile
{
	
	private \Sura\Database\Connection $database;
	
	/**
	 * Profile constructor.
	 */
	public function __construct()
	{
		$this->database = Model::getDB();
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function user_row(int $id): array
	{
		return $this->database->fetch("SELECT user_name, user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating, user_balance, balance_rub FROM `users` WHERE user_id =  ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function user_xfields(int $id): array
	{
		return $this->database->fetch("SELECT user_xfields FROM `users` WHERE user_id = ?", (array)$id);
	}
	
	/**
	 * @param int $id
	 * @return array
	 */
	public function miniature(int $id): array
	{
		return $this->database->fetch("SELECT user_photo FROM `users` WHERE user_id = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function user_online(int $id): array
	{
		return $this->database->fetch("SELECT user_last_visit, user_logged_mobile FROM `users` WHERE user_id = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function friends(int $id): array
	{
		return $this->database->fetchAll('SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = ? AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 2', (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $online_time
	 * @return array
	 */
	public function friends_online_cnt(int $id, $online_time): array
	{
		return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = ? AND tb1.user_last_visit >= ? AND subscriptions = 0", (array)$id, (array)$online_time);
	}
	
	/**
	 * @param $id
	 * @param $online_time
	 * @return array
	 */
	public function friends_online(int $id, int $online_time): array
	{
		return $this->database->fetchAll("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = ? AND tb1.user_last_visit >= ?  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 2", (array)$id, (array)$online_time);
	}
	
	/**
	 * @param $id
	 * @param $sql_privacy
	 * @param $cache_pref_videos
	 * @return array
	 * @throws \Throwable
	 */
	public function videos_online_cnt(int $id, string $sql_privacy, string $cache_pref_videos): array
	{
//        $dir = resolve('app')->get('path.base');
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = $id . '/videos_num' . $cache_pref_videos;
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		
		if ($value == null) {
			$row = $this->database->fetch("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = ? '{$sql_privacy}' AND public_id = '0'", (array)$id);
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @param $sql_privacy
	 * @param $cache_pref_videos
	 * @return array
	 * @throws \Throwable
	 */
	public function videos_online(int $id, string $sql_privacy, string $cache_pref_videos): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = $id . '/page_videos_user' . $cache_pref_videos;
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT id, title, add_date, comm_num, photo FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2", true);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @param $cache_pref_subscriptions
	 * @return array
	 * @throws \Throwable
	 */
	public function subscriptions(int $id, string $cache_pref_subscriptions): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = $id . '/' . $cache_pref_subscriptions;
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetchAll("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = ? AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function audio(int $id): array
	{
		return $this->database->fetchAll("SELECT id, url, artist, title, duration FROM `audio` WHERE oid = ? and public = '0' ORDER by `id` DESC LIMIT 0, 3", (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $server_time
	 * @return array
	 */
	public function happy_friends(int $id, $server_time): array
	{
		return $this->database->fetch("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = ? AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '" . date('j', $server_time) . "' AND user_month = '" . date('n', $server_time) . "' ORDER by `user_last_visit` DESC LIMIT 0, 50", (array)$id);
	}
	
	/**
	 * @param int $id
	 * @return array
	 * @throws \Throwable
	 */
	public function groups(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = $id . '/groups';
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetchAll("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = ? AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
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
	public function gifts(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = $id . '/gifts';
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetchALL("SELECT gift FROM `gifts` WHERE uid = ? ORDER by `gdate` DESC LIMIT 0, 5", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function user_sp(int $id): array
	{
		return $this->database->fetch("SELECT user_search_pref, user_sp, user_sex FROM `users` WHERE user_id = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function cnt_rec(int $id): array
	{
		return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `wall` WHERE for_user_id = ? AND author_user_id = ? AND fast_comm_id = 0", (array)$id, (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 */
	public function check_subscr(int $id, int $user_id): array
	{
		return $this->database->fetch("SELECT user_id FROM `friends` WHERE user_id = ? AND friend_id = ? AND subscriptions = 1", (array)$user_id, (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 */
	public function check_fave(int $id, int $user_id): array
	{
		return $this->database->fetch("SELECT user_id FROM `fave` WHERE user_id = ? AND fave_id = ?", (array)$user_id, (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 * @throws \Throwable
	 */
	public function row_video(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'wall');
		
		$key = "wall/video{$id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT video, title, download FROM `videos` WHERE id = ?", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function row_audio(int $id): array
	{
		return $this->database->fetch("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = ?", (array)$id);
	}
	
	/**
	 * @param $id
	 * @return array
	 * @throws \Throwable
	 */
	public function row_doc(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'wall');
		
		$key = "wall/doc{$id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT dname, dsize FROM `doc` WHERE did = ?", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
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
	public function row_vote(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'votes');
		
		$key = "votes/vote_{$id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT title, answers, answer_num FROM `votes` WHERE id = ?", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
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
	public function vote_check(int $id, int $user_id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = "{$user_id}/votes_check_{$id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = ? AND vote_id = ?", (array)$user_id, (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
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
	public function vote_answer(int $id): array
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'votes');
		
		$key = "votes/vote_answer_cnt_{$id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = ? GROUP BY answer", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @return array
	 */
	public function author_user_id(int $id): array
	{
		return $this->database->fetch("SELECT author_user_id FROM `wall` WHERE id = ?", (array)$id);
	}
	
	/**
	 * @param $user_id
	 * @param $type
	 * @return array
	 * @throws \Throwable
	 */
	public function user_tell_info(int $user_id, int $type): array
	{
		if ($type === 1) {
			return $this->database->fetch("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
		}
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = "{$user_id}/wall/group{$user_id}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT title, photo FROM `communities` WHERE id = ?", (array)$user_id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @param $limit
	 * @return array
	 */
	public function comments(int $id = 1, int $limit = 1): array
	{
		return $this->database->fetchAll("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = ? ORDER by `add_date` LIMIT {$limit}, 3", (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 */
	public function count_common(int $id, int $user_id): array
	{
		return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = ? AND tb2.friend_id = ? AND tb1.subscriptions = 0 AND tb2.subscriptions = 0", (array)$user_id, (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 */
	public function mutual(int $id, int $user_id): array
	{
		return $this->database->fetchAll("SELECT tb1.friend_id, tb3.user_photo, user_search_pref FROM `users` tb3, `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = ? AND tb2.friend_id = ? AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by rand() LIMIT 0, 3", (array)$user_id, (array)$id);
	}
	
	/**
	 * @param $id
	 * @param $albums_privacy
	 * @param $type
	 * @return array
	 * @throws \Throwable
	 */
	public function albums_count(int $id, string $albums_privacy, int $type): array
	{
		if ($type === 1) {
			$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
			$cache = new \Sura\Cache\Cache($storage, 'users');
			
			$key = "{$id}/albums_cnt_friends";
			$value = $cache->load($key, function (&$dependencies) {
				$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
			});
			if ($value == null) {
				$row = $this->database->fetch("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = ? {$albums_privacy}", (array)$id);
				
				$value = serialize($row);
				$cache->save($key, $value);
			} else {
				$row = unserialize($value, $options = []);
			}
			return $row;
		}
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = "{$id}/albums_cnt_all";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetch("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = ? {$albums_privacy}", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param int $id
	 * @param string $albums_privacy
	 * @param $cache_pref
	 * @return array|string|null
	 * @throws \Throwable
	 */
	public function row_albums(int $id, string $albums_privacy, $cache_pref): array|string|null
	{
		$storage = new \Sura\Cache\Storages\MemcachedStorage('localhost');
		$cache = new \Sura\Cache\Cache($storage, 'users');
		
		$key = "{$id}/albums{$cache_pref}";
		$value = $cache->load($key, function (&$dependencies) {
			$dependencies[\Sura\Cache\Cache::EXPIRE] = '20 minutes';
		});
		if ($value == null) {
			$row = $this->database->fetchAll("SELECT SQL_CALC_FOUND_ROWS aid, name, adate, photo_num, cover FROM `albums` WHERE user_id = ? {$albums_privacy} ORDER by `position` ASC LIMIT 0, 3", (array)$id);
			
			$value = serialize($row);
			$cache->save($key, $value);
		} else {
			$row = unserialize($value, $options = []);
		}
		return $row;
	}
	
	/**
	 * @param $id
	 * @param $user_id
	 * @return array
	 */
	public function friend_visit(int $id, int $user_id): array
	{
		try {
			return $this->database->fetch("UPDATE LOW_PRIORITY `friends` SET views = views+1 WHERE user_id = ? AND friend_id = ? AND subscriptions = 0", (array)$user_id, (array)$id);
		} catch (Exception $e) {
			return array();
		}
	}
}