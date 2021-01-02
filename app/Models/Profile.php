<?php

namespace App\Models;

use App\Services\Cache;
use Exception;
use Sura\Libs\Db;

class Profile
{

    private ?Db $db;

    /**
     * Profile constructor.
     */
    public function __construct(
    )
    {
        $this->db = Db::getDB();
    }

    /**
     * @param $id
     * @return array
     */
    public function user_row(int $id) : array
    {
        return $this->db->super_query("SELECT user_name, user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating, user_balance, balance_rub FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function user_xfields(int $id) : array
    {
        return $this->db->super_query("SELECT user_xfields FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param int $id
     * @return array
     */
    public function miniature(int $id) : array
    {
        return $this->db->super_query("SELECT user_photo FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function user_online(int $id) : array
    {
        return $this->db->super_query("SELECT user_last_visit, user_logged_mobile FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function friends(int $id) : array
    {
        return $this->db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public function friends_online_cnt(int $id, $online_time) : array
    {
        return $this->db->super_query("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}' AND subscriptions = 0");
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public function friends_online(int $id, string $online_time) : array
    {
        return $this->db->super_query("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}'  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public function videos_online_cnt(int $id, string $sql_privacy, string $cache_pref_videos) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/videos_num{$cache_pref_videos}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0'", false);
            $value = serialize($row);
            $Cache->set("users/{$id}/videos_num{$cache_pref_videos}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public function videos_online(int $id, string $sql_privacy, string $cache_pref_videos) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/page_videos_user{$cache_pref_videos}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT id, title, add_date, comm_num, photo FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2", true);
            $value = serialize($row);
            $Cache->set("users/{$id}/page_videos_user{$cache_pref_videos}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @param $cache_pref_subscriptions
     * @return array
     */
    public function subscriptions(int $id, string $cache_pref_subscriptions) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/".$cache_pref_subscriptions, $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5", true);
            $value = serialize($row);
            $Cache->set("users/{$id}/".$cache_pref_subscriptions, $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function audio(int $id) : array
    {
        return $this->db->super_query("SELECT id, url, artist, title, duration FROM `audio` WHERE oid = '{$id}' and public = '0' ORDER by `id` DESC LIMIT 0, 3", 1);
    }

    /**
     * @param $id
     * @param $server_time
     * @return array
     */
    public function happy_friends(int $id, $server_time) : array
    {
        return $this->db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '".$id."' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '".date('j', $server_time)."' AND user_month = '".date('n', $server_time)."' ORDER by `user_last_visit` DESC LIMIT 0, 50", 1);
    }

    /**
     * @param int $id
     * @return array
     */
    public function groups(int $id): array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/groups_".$id, $default = null);
            $value = unserialize($row, $options = []);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5", true);
            $value = serialize($row);
            $Cache->set("users/{$id}/groups_".$id, $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function gifts(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/gifts", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT gift FROM `gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 5", true);
            $value = serialize($row);
            $Cache->set("users/{$id}/gifts", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function user_sp(int $id) : array
    {
        return $this->db->super_query("SELECT user_search_pref, user_sp, user_sex FROM `users` WHERE user_id = '{$id}'", false);
    }

    /**
     * @param $id
     * @return array
     */
    public function cnt_rec(int $id) : array
    {
        return $this->db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0", false);
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function check_subscr(int $id, int $user_id) : array
    {
        return $this->db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 1", false);
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function check_fave(int $id, int $user_id) : array
    {
        return $this->db->super_query("SELECT user_id FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_video(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("wall/video{$id}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT video, title, download FROM `videos` WHERE id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set("wall/video{$id}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function row_audio(int $id) : array
    {
        return $this->db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public function row_doc(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("wall/doc{$id}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$id}'", false);
            $value = serialize($row);
            $Cache->set("wall/doc{$id}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function row_vote(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("votes/vote_{$id}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set("votes/vote_{$id}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function vote_check(int $id, int $user_id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$user_id}/votes_check_{$id}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set("users/{$user_id}/votes_check_{$id}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function vote_answer(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("votes/vote_answer_cnt_{$id}", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$id}' GROUP BY answer", true);
            $value = serialize($row);
            $Cache->set("votes/vote_answer_cnt_{$id}", $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public function author_user_id(int $id) : array
    {
        return $this->db->super_query("SELECT author_user_id FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public function user_tell_info(int $user_id, int $type) : array
    {
        $db = Db::getDB();
        if ($type === 1){
            return $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
        }

        $Cache = cache_init(array('type' => 'file'));
        try {
            $value = $Cache->get("user_{$user_id}/wall/group{$user_id}", $default = null);
            $row = unserialize($value);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$user_id}'", false);
            $value = serialize($row);
            $Cache->set("user_{$user_id}/wall/group{$user_id}", $value);
        }
        return $row;
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     */
    public function comments(int $id, int $limit) : array
    {
        return $this->db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` LIMIT {$limit}, 3", true);
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function count_common(int $id, int $user_id) : array
    {
        return $this->db->super_query("SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function mutual(int $id, int $user_id) : array
    {
        return $this->db->super_query("SELECT tb1.friend_id, tb3.user_photo, user_search_pref FROM `users` tb3, `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by rand() LIMIT 0, 3", true);
    }

    /**
     * @param $id
     * @param $albums_privacy
     * @param $type
     * @return array
     */
    public function albums_count(int $id, string $albums_privacy, int $type) : array
    {
        if ($type === 1){
            $Cache = cache_init(array('type' => 'file'));
            try {
                $row = $Cache->get("users/{$id}/albums_cnt_friends", $default = null);
                $value = unserialize($row);
            }catch (Exception $e){
                $db = Db::getDB();
                $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}", false);
                $value = serialize($row);
                $Cache->set("users/{$id}/albums_cnt_friends", $value);
            }
            return $value;
        }

        $Cache = cache_init(array('type' => 'file'));
        try {
            $row = $Cache->get("users/{$id}/albums_cnt_all", $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}", false);
            $value = serialize($row);
            $Cache->set("users/{$id}/albums_cnt_all", $value);
        }
        return $value;
    }

    /**
     * @param int $id
     * @param string $albums_privacy
     * @param $cache_pref
     * @return array|string|null
     */
    public function row_albums(int $id, string $albums_privacy, $cache_pref) : array|string|null
    {
        $Cache = cache_init(array('type' => 'file'));
        try {
            $value = $Cache->get("users/{$id}/albums{$cache_pref}", $default = null);
            $row = unserialize($value);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT SQL_CALC_FOUND_ROWS aid, name, adate, photo_num, cover FROM `albums` WHERE user_id = '{$id}' {$albums_privacy} ORDER by `position` ASC LIMIT 0, 3", true);
            $value = serialize($row);
            $Cache->set("users/{$id}/albums{$cache_pref}", $value);
        }
        return $row;
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public function friend_visit(int $id, int $user_id) : array
    {
        try {
            return $this->db->super_query("UPDATE LOW_PRIORITY `friends` SET views = views+1 WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 0", false);
        }catch (Exception $e){
            return array();
        }
    }
}