<?php
declare(strict_types=1);

namespace App\Libs;

use Sura\Libs\Model;

class Stories
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
	 * @param string $story_id
	 * @return array
	 */
	public function get_story(string $story_id = ''): array
	{
		return $this->database->fetch("SELECT * FROM `stories` WHERE id = '{$story_id}'");
	}
	
	/**
	 * @return array
	 */
	public function get_all_stories(): array
	{
		return $this->database->fetchAll("SELECT * FROM `stories` ORDER by `id` DESC LIMIT 0, 5");
	}
	
	/**
	 * @param string $story_id
	 * @param int $num_last_stories
	 * @return mixed
	 */
	public function get_stories(string $story_id = '', int $num_last_stories = 5): array
	{
		$limit = $num_last_stories;
		return $this->database->fetchAll("SELECT * FROM `stories` ORDER by `time` DESC LIMIT {$story_id}, {$limit}");
	}
	
	public function get_single_story(int $story_id): array
	{
		return $this->database->fetch("SELECT * FROM `stories` WHERE id = '{$story_id}'");
	}
}