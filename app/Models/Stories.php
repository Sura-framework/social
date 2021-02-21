<?php

declare(strict_types=1);

namespace App\Models;

use Sura\Database\Connection;
use Sura\Libs\Model;

class Stories
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
	 * @param $user_id
	 * @return array
	 */
	public function all(int $user_id): array
	{
		return $this->database->fetchAll("SELECT tb1.user_id, url, add_date FROM `stories_feed` tb1, `friends` tb2 WHERE tb1.user_id = tb2.friend_id AND tb2.user_id = '{$user_id}' ORDER by add_date DESC LIMIT 0, 6");
	}
	
	/**
	 * @param $user_id
	 * @return array
	 */
	public function get(int $user_id): array
	{
		return $this->database->fetch("SELECT * FROM `stories_feed` WHERE user_id = '{$user_id}' ORDER by `add_date` ");
	}
}
