<?php


namespace App\Models;


use Exception;
use Sura\Libs\Db;

class News
{

    /**
     * @var Db|null
     */
    private static ?Db $db;

    public function __construct()
    {
        self::$db = Db::getDB();
    }

    /**
     * @param int $user_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function load_news(int $user_id, int  $page, int $limit = 20) : array
    {
        $db = Db::getDB();
        try {
            return  $db->super_query("SELECT tb1.ac_id, ac_user_id, action_text, action_time, action_type, obj_id, answer_text, link FROM `news` tb1 WHERE tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2 
                WHERE user_id = '{$user_id}' AND tb1.action_type IN (1,2,3) AND subscriptions != 2) 
            OR 
                tb1.ac_user_id IN (SELECT tb2.friend_id FROM `friends` tb2 
                WHERE user_id = '{$user_id}' AND tb1.action_type = 11 AND subscriptions = 2) 
            AND tb1.action_type IN (1,2,3,11)	ORDER BY tb1.action_time DESC LIMIT {$page}, {$limit}", true);
        }catch (Exception $e){
            return array();
        }
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public static function row_type11(int $user_id, int $type) : array
    {
        $db = Db::getDB();
        try {

//            throw new Exception('err');

            if ($type == 1){
                $res = $db->super_query("SELECT user_search_pref, user_last_visit, user_logged_mobile, user_photo, user_sex, user_privacy FROM `users` WHERE user_id = '{$user_id}'");
            }else{
                $res = $db->super_query("SELECT title, photo, comments FROM `communities` WHERE id = '{$user_id}'");
            }

            return $res;
//            var_dump($res);
//            exit();


        }catch (Exception $e){
            return array();
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
        $Cache = cache_init(array('type' => 'file'));
        $key = "user_{$id}/wall/video{$id}";

        try {
            $value = $Cache->get($key, $default = null);
            $row = unserialize($value);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT video, title FROM `videos` WHERE id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set($key, $value);
        }
        return $row;
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
        $Cache = cache_init(array('type' => 'file'));
        $key = "user_{$id}/votes/vote_{$id}";

        try {
            $row = $Cache->get($key, $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT title, answers, answer_num FROM `votes` WHERE id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set($key, $value);
        }
        return $value;
    }


    /**
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function vote_info_check(int $id, int $user_id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        $key = "user_{$id}/votes/check{$user_id}_{$id}";

        try {
            $row = $Cache->get($key, $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `votes_result` WHERE user_id = '{$user_id}' AND vote_id = '{$id}'", false);
            $value = serialize($row);
            $Cache->set($key, $value);
        }
        return $value;
    }

    /**
     * @param $id
     * @return array
     */
    public static function vote_info_answer(int $id) : array
    {
        $Cache = cache_init(array('type' => 'file'));
        $key = "votes/vote_answer_cnt_{$id}";

        try {
            $row = $Cache->get($key, $default = null);
            $value = unserialize($row);
        }catch (Exception $e){
            $db = Db::getDB();
            $row = $db->super_query("SELECT answer, COUNT(*) AS cnt FROM `votes_result` WHERE vote_id = '{$id}' GROUP BY answer", true);
            $value = serialize($row);
            $Cache->set($key, $value);
        }
        return $value;
    }

    /**
     * @param $user_id
     * @param $type
     * @return array
     */
    public static function user_tell_info(int $user_id, int $type) : array
    {
        if ($type == 1){
            $db = Db::getDB();
            return $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
        }else{
            $Cache = cache_init(array('type' => 'file'));
            $key = "user_{$user_id}/wall/group{$user_id}";
            try {
                $row = $Cache->get($key, $default = null);
                $value = unserialize($row);
            }catch (Exception $e){
                $db = Db::getDB();
                $row = $db->super_query("SELECT title, photo FROM `communities` WHERE id = '{$user_id}'", false);
                $value = serialize($row);
                $Cache->set($key, $value);
            }
            return $value;
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
        $res =  $db->super_query("SELECT fasts_num, likes_num, likes_users, tell_uid, tell_date, type, public, attach, tell_comm FROM `wall` WHERE id = '{$id}'", false);
        if ($res['fasts_num'] < 3){
            $res['fasts_num'] = '';
        }
        return $res;
    }

    /**
     * @param $id
     * @param $limit
     * @return array
     */
    public static function comments(int $id, int $limit) : array
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.id, author_user_id, text, add_date, tb2.user_photo, user_search_pref FROM `wall` tb1, `users` tb2 WHERE tb1.author_user_id = tb2.user_id AND tb1.fast_comm_id = '{$id}' ORDER by `add_date` LIMIT {$limit}, 3", 1);
    }

    /**
     * @param $id
     * @return array
     */
    public static function rec_info_groups(int $id) : array
    {
        $db = Db::getDB();
        $res = $db->super_query("SELECT fasts_num, likes_num, likes_users, attach, tell_uid, tell_date, tell_comm, public FROM `communities_wall` WHERE id = '{$id}'");
        if ($res['fasts_num'] < 3){
            $res['fasts_num'] = '';
        }
        return $res;
    }

}