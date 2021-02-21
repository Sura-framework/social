<?php

declare(strict_types=1);

namespace App\Models;

use Sura\Database\Connection;
use Sura\Libs\Model;

class Register
{

    private Connection $database;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->database = Model::getDB();
    }

	/**
	 * @param $user_email
	 * @return array
	 */
	public function check_email(string $user_email): array
	{
        return $this->database->fetch("SELECT COUNT(*) AS cnt FROM `users` WHERE user_email = ?", (array)$user_email);
    }
	
	/**
	 * @param $user_country
	 * @return array
	 */
	public function country_info(int $user_country): array
	{
		return $this->database->fetch("SELECT name FROM `country` WHERE id = ?", (array)$user_country);
	}
	
	/**
	 * @param $user_city
	 * @return array
	 */
	public function city_info(int $user_city): array
	{
		return $this->database->fetch("SELECT name FROM `city` WHERE id = ?", (array)$user_city);
	}
}
