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



}