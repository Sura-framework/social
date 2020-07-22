<?php


namespace App\Models;


use Sura\Libs\Db;

class News
{
    /**
     * @param $user_id
     * @param $page_cnt
     * @return array
     */
    public static function load_news(int $user_id, int  $page_cnt) : array
    {
        $db = Db::getDB();
        return  $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `news` tb1 WHERE tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2 
                WHERE user_id = '{$user_id}' AND tb1.action_type IN (1,2,3) AND subscriptions != 2) 
            OR 
                tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2 
                WHERE user_id = '{$user_id}' AND tb1.action_type = 11 AND subscriptions = 2) 
            AND tb1.action_type IN (1,2,3,11)	ORDER BY tb1.action_time DESC LIMIT {$page_cnt}, 20", 1);
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public static function row_type11(int $user_id, int $type) : array
    {
        $db = Db::getDB();
        if ($type == 1){
            return $db->super_query("SELECT user_search_pref, user_last_visit, user_logged_mobile, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = '{$user_id}'");
        }else{
            return $db->super_query("SELECT title, photo, comments FROM `communities` WHERE id = '{$user_id}'");
        }
    }

    /**
     * @param $user_id
     * @return array
     */
    public static function friend_info(int $user_id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function wall_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT id, author_user_id, for_user_id, text, add_date, tell_uid, tell_date, type, public, attach, tell_comm, fast_comm_id FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function video_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT video, title FROM `videos` WHERE id = '{$id}'", false, "wall/video{$id}");
    }

    /**
     * @param $id int
     * @return array
     */
    public static function audio_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT artist, title, url FROM `audio` WHERE oid = '".$id."'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function doc_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT dname, dsize FROM `doc` WHERE did = '".$id."'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function vote_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'\", false, \"votes/vote_{$id}");
    }


    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function vote_info_check(int $id, int $user_id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$id}'\", false, \"votes/check{$user_id}_{$id}");
    }

    /**
     * @param $id
     * @return array
     */
    public static function vote_info_answer(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$id}' GROUP BY answer", 1, "votes/vote_answer_cnt_{$id}");
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public static function user_tell_info(int $user_id, int $type) : array
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
     * @return array
     */
    public static function likes_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT id, author_user_id, for_user_id, text, add_date, tell_uid, tell_date, type, public, attach, tell_comm FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function delete(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("DELETE FROM `news` WHERE ac_id = '{$id}'");
    }

    /**
     * @param $id
     * @return array
     */
    public static function rec_info(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT fasts_num, likes_num, likes_users, tell_uid, tell_date, type, public, attach, tell_comm FROM `wall` WHERE id = '{$id}'");
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     */
    public static function comments(int $id, int $limit) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` ASC LIMIT {$limit}, 3", 1);
    }

    /**
     * @param $id
     * @return array
     */
    public static function rec_info_groups(int $id) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT fasts_num, likes_num, likes_users, attach, tell_uid, tell_date, tell_comm, public FROM `communities_wall` WHERE id = '{$id}'");
    }

}