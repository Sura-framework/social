<?php

namespace Sura\Models;

use Sura\Classes\Db;
use Sura\Libs\Registry;

class Profile
{
	function __construct($foo = null)
	{
		$db = Db::getDB();
	}

	public static function user_row($id)
	{
		return $db->super_query("SELECT user_name, user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating FROM `users` WHERE user_id = '{$id}'");
	}

	public static function user_online($id)
	{
		return $db->super_query("SELECT user_last_visit, user_logged_mobile FROM `users` WHERE user_id = '{$id}'");
	}

	public static function friends($id)
	{
		return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
	}	

	public static function friends_online_cnt($id, $online_time)
	{
		return $db->super_query("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}' AND subscriptions = 0");
	}		

	public static function friends_online($id, $online_time)
	{
		return $db->super_query("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}'  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
	}	

	public static function videos_online_cnt($id, $sql_privacy, $cache_pref_videos)
	{
		return $db->super_query("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0'", false, "user_{$id}/videos_num{$cache_pref_videos}");
	}		

	public static function videos_online($id, $sql_privacy, $cache_pref_videos)
	{
		return $db->super_query("SELECT id, title, add_date, comm_num, photo FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2", 1, "user_{$id}/page_videos_user{$cache_pref_videos}");
	}

	public function subscriptions($id)
	{
		return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5", 1);
	}	

	public function audio($id)
	{
		return $db->super_query("SELECT id, url, artist, title, duration FROM `audio` WHERE oid = '{$id}' and public = '0' ORDER by `id` DESC LIMIT 0, 3", 1);
	}	

	public function happy_friends($id, $server_time)
	{
		return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '".$id."' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '".date('j', $server_time)."' AND user_month = '".date('n', $server_time)."' ORDER by `user_last_visit` DESC LIMIT 0, 50", 1);
	}

	public function groups($id)
	{
		return $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5", 1, "groups/".$id);
	}

	public function gifts($id)
	{
		return $db->super_query("SELECT gift FROM `gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 5", 1, "user_{$id}/gifts");
	}

	public function user_sp($id)
	{
		return $db->super_query("SELECT user_search_pref, user_sp, user_sex FROM `users` WHERE user_id = '{$user_sp[1]}'");
	}

	public function cnt_rec($id)
	{
		return $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
	}

	public function check_subscr($id, $user_id)
	{
		$db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 1");
	}

	public function check_fave($id, $user_id)
	{
		return $db->super_query("SELECT user_id FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$id}'");
	}

	public function row_video($id)
	{
		return $db->super_query("SELECT video, title, download FROM `videos` WHERE id = '{$id}'", false, "wall/video{$id}");
	}

	public function row_audio($id)
	{
		return $db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$id}'");
	}

	public function row_doc($id)
	{
		return $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$id}'", false, "wall/doc{$id}");
	}
}