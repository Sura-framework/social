<?php
declare(strict_types=1);

namespace App\Libs;


use Sura\Libs\Db;

class Stories
{
	
	/**
	 * @param string $story_id
	 * @return array
	 */
	public static function get_story(string $story_id = ''): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT * FROM `stories` WHERE id = '{$story_id}'");
	}
	
	/**
	 * @return array
	 */
	public static function get_all_stories(): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT * FROM `stories` ORDER by `id` DESC LIMIT 0, 5", 1);
	}
	
	/**
	 * @param string $story_id
	 * @param int $num_last_stories
	 * @return mixed
	 */
	public static function get_stories(string $story_id = '', int $num_last_stories = 5): array
	{
		$limit = $num_last_stories;
		
		$db = Db::getDB();
		return $db->super_query("SELECT * FROM `stories` ORDER by `time` DESC LIMIT {$story_id}, {$limit}", 1);
	}
	
	public static function get_single_story(int $story_id): array
	{
		$db = Db::getDB();
		return $db->super_query("SELECT * FROM `stories` WHERE id = '{$story_id}'");
		
	}
}