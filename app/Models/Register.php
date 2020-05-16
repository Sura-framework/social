<?php


namespace App\Models;

use Sura\Classes\Db;

class Register
{

    public static function check_email($user_email)
    {
        $db = Db::getDB();
        return $db->super_query("SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = '{$user_email}'");
    }

    public static function country_info($user_country)
    {
        $db = Db::getDB();
        $db->super_query("SELECT name FROM `country` WHERE id = '" . $user_country . "'");
    }
    public static function city_info($user_city)
    {
        $db = Db::getDB();
        $db->super_query("SELECT name FROM `city` WHERE id = '" . $user_city . "'");
    }

}