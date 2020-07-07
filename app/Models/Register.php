<?php


namespace App\Models;

use Sura\Libs\Db;

class Register
{

    /**
     * @param $user_email
     * @return array|mixed|string[]
     */
    public static function check_email($user_email)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = '{$user_email}'");
    }

    /**
     * @param $user_country
     * @return array|mixed|string[]
     */
    public static function country_info($user_country)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT name FROM `country` WHERE id = '" . $user_country . "'");
    }

    /**
     * @param $user_city
     * @return array|mixed|string[]
     */
    public static function city_info($user_city)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT name FROM `city` WHERE id = '" . $user_city . "'");
    }

}