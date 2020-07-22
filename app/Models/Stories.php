<?php


namespace App\Models;


use Sura\Libs\Db;

class Stories
{

    public static function all($user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT tb1.user_id, url, add_date FROM `stories_feed` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$user_id}' ORDER by add_date DESC LIMIT 0, 6", 1);
    }

    public static function get($user_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT * FROM `stories_feed` WHERE user_id = '{$user_id}' ORDER by `add_date` ");
    }
}