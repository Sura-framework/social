<?php


namespace App\Libs;


use Sura\Libs\Db;

class Stories
{

    /**
     * @param string $story_id
     * @return array
     */
    public static function get_story($story_id = '')
    {
        $db = Db::getDB();
        return $db->super_query("SELECT * FROM `stories` WHERE id = '{$story_id}'");
    }

    /**
     * @return array
     */
    public static function get_all_stories()
    {
        $db = Db::getDB();
        return $db->super_query("SELECT * FROM `stories` ORDER by `id` DESC LIMIT 0, 5", 1);
    }

    /**
     * @param string $story_id
     * @param null $num_last_stories
     * @return mixed
     */
    public static function get_stories($story_id = '', $num_last_stories = null)
    {
        $limit = $num_last_stories;

        if($num_last_stories == null)
        {
            $limit = 5;
        }

        $db = Db::getDB();
        return $db->super_query("SELECT * FROM `stories` ORDER by `time` DESC LIMIT {$story_id}, {$limit}", 1);
    }

    public static function get_single_story($story_id)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT * FROM `stories` WHERE id = '{$story_id}'");

    }
}