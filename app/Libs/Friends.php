<?php

declare(strict_types=1);

namespace App\Libs;


use Sura\Libs\Model;
use Sura\Libs\Registry;

class Friends
{

    private \Sura\Database\Connection $database;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->database = Model::getDB();
    }

	/**
	 * check user to friends
	 * @param int $for_user_id
	 * @param int $from_user_id
	 * @return bool
	 */
	public function CheckFriends(int $for_user_id, int $from_user_id = 0): bool
	{
		if ($from_user_id == 0) {
			$user_info = Registry::get('user_info');
			$from_user_id = $user_info['user_id'];
		}
		$check = $this->database->fetch("SELECT user_id FROM `friends` WHERE friend_id = ? AND user_id = ? AND subscriptions = 0", (array)$for_user_id, (array)$from_user_id);
		if ($check) {
			return true;
		}
		return false;
	}
	
	/**
	 * @param int $bad_user_id
	 * @param int $user_id
	 * @return bool
	 */
	public function CheckBlackList(int $bad_user_id, int $user_id = 0): bool
	{
		if ($user_id == 0) {
			$user_info = Registry::get('user_info');
			$user_id = $user_info['user_id'];
		}
		if ($user_id !== $bad_user_id) {
			$row_blacklist = $this->database->fetch("SELECT id FROM `users_blacklist` WHERE users = '{$bad_user_id}|{$user_id}'");
			if ($row_blacklist) {
				return true;
			}
		}
		return false;
	}
}