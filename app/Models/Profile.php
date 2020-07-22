<?php

namespace App\Models;

use Sura\Libs\Db;

class Profile
{
    /**
     * @param $id
     * @return array
     */
    public static function user_row($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_name, user_id, user_search_pref, user_country_city_name, user_birthday, user_xfields, user_xfields_all, user_city, user_country, user_photo, user_friends_num, user_notes_num, user_subscriptions_num, user_wall_num, user_albums_num, user_last_visit, user_videos_num, user_status, user_privacy, user_sp, user_sex, user_gifts, user_public_num, user_audio, user_delet, user_ban_date, xfields, user_logged_mobile , user_cover, user_cover_pos, user_rating FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function user_online($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_last_visit, user_logged_mobile FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function friends($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public static function friends_online_cnt($id, $online_time)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}' AND subscriptions = 0");
    }

    /**
     * @param $id
     * @param $online_time
     * @return array
     */
    public static function friends_online($id, $online_time)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.user_id, user_country_city_name, user_search_pref, user_birthday, user_photo FROM `users` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$id}' AND tb1.user_last_visit >= '{$online_time}'  AND subscriptions = 0 ORDER by rand() DESC LIMIT 0, 6", 1);
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public static function videos_online_cnt($id, $sql_privacy, $cache_pref_videos)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0'", false, "user_{$id}/videos_num{$cache_pref_videos}");
    }

    /**
     * @param $id
     * @param $sql_privacy
     * @param $cache_pref_videos
     * @return array
     */
    public static function videos_online($id, $sql_privacy, $cache_pref_videos)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT id, title, add_date, comm_num, photo FROM `videos` WHERE owner_user_id = '{$id}' {$sql_privacy} AND public_id = '0' ORDER by `add_date` DESC LIMIT 0,2", 1, "user_{$id}/page_videos_user{$cache_pref_videos}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function subscriptions($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_country_city_name, user_status FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.user_id AND tb1.subscriptions = 1 ORDER by `friends_date` DESC LIMIT 0,5", 1);
    }

    /**
     * @param $id
     * @return array
     */
    public static function audio($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT id, url, artist, title, duration FROM `audio` WHERE oid = '{$id}' and public = '0' ORDER by `id` DESC LIMIT 0, 3", 1);
    }

    /**
     * @param $id
     * @param $server_time
     * @return array
     */
    public static function happy_friends($id, $server_time)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.friend_id, tb2.user_search_pref, user_photo, user_birthday FROM `friends` tb1, `users` tb2 WHERE tb1.user_id = '".$id."' AND tb1.friend_id = tb2.user_id  AND subscriptions = 0 AND user_day = '".date('j', $server_time)."' AND user_month = '".date('n', $server_time)."' ORDER by `user_last_visit` DESC LIMIT 0, 50", 1);
    }

    /**
     * @param $id
     * @return array
     */
    public static function groups($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.friend_id, tb2.id, title, photo, adres, status_text FROM `friends` tb1, `communities` tb2 WHERE tb1.user_id = '{$id}' AND tb1.friend_id = tb2.id AND tb1.subscriptions = 2 ORDER by `traf` DESC LIMIT 0, 5", 1, "groups/".$id);
    }

    /**
     * @param $id
     * @return array
     */
    public static function gifts($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT gift FROM `gifts` WHERE uid = '{$id}' ORDER by `gdate` DESC LIMIT 0, 5", 1, "user_{$id}/gifts");
    }

    /**
     * @param $id
     * @return array
     */
    public static function user_sp($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_search_pref, user_sp, user_sex FROM `users` WHERE user_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function cnt_rec($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE for_user_id = '{$id}' AND author_user_id = '{$id}' AND fast_comm_id = 0");
    }

    /**
     * @param $id
     * @param $user_id
     */
    public static function check_subscr($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_id FROM `friends` WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 1");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function check_fave($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_id FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function row_video($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT video, title, download FROM `videos` WHERE id = '{$id}'", false, "wall/video{$id}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function row_audio($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT id, oid, artist, title, url, duration FROM `audio` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function row_doc($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '{$id}'", false, "wall/doc{$id}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function row_vote($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'\", false, \"votes/vote_{$id}");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function vote_check($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$id}'\", false, \"votes/check{$user_id}_{$id}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function vote_answer($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$id}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function author_user_id($id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT author_user_id FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public static function user_tell_info($user_id, $type)
    {
        $db = Db::getDB();
        if ($type == 1){
            return $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
        }else{
            return $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$user_id}'\", false, \"wall/group{$user_id}");
        }
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     */
    public static function comments($id, $limit)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` ASC LIMIT {$limit}, 3", 1);
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function count_common($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function mutual($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.friend_id, tb3.user_photo, user_search_pref FROM `users` tb3, `friends` tb1 INNER JOIN `friends` tb2 ON tb1.friend_id = tb2.user_id WHERE tb1.user_id = '{$user_id}' AND tb2.friend_id = '{$id}' AND tb1.subscriptions = 0 AND tb2.subscriptions = 0 AND tb1.friend_id = tb3.user_id ORDER by rand() LIMIT 0, 3", 1);
    }

    /**
     * @param $id
     * @param $albums_privacy
     * @param $type
     * @return array
     */
    public static function albums_count($id, $albums_privacy, $type)
    {
        $db = Db::getDB();
        if ($type == 1){
            return $db->super_query("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}", false, "user_{$id}/albums_cnt_friends");
        }else{
            return $db->super_query("SELECT COUNT(*) AS cnt FROM `albums` WHERE user_id = '{$id}' {$albums_privacy}", false, "user_{$id}/albums_cnt_all");
        }
    }

    /**
     * @param $id
     * @param $albums_privacy
     * @param $cache_pref
     * @return array
     */
    public static function row_albums($id, $albums_privacy, $cache_pref)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT aid, name, adate, photo_num, cover FROM `albums` WHERE user_id = '{$id}' {$albums_privacy} ORDER by `position` ASC LIMIT 0, 4", 1, "user_{$id}/albums{$cache_pref}");
    }

    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function friend_visit($id, $user_id)
    {
        $db = Db::getDB();
        return $db->super_query("UPDATE LOW_PRIORITY `friends` SET views = views+1 WHERE user_id = '{$user_id}' AND friend_id = '{$id}' AND subscriptions = 0");
    }
}