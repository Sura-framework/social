<?php

declare(strict_types=1);

namespace App\Models;


use Sura\Libs\Model;

class Search
{
	
	private \Sura\Database\Connection $database;
	
	/**
	 * Profile constructor.
	 */
	public function __construct()
	{
		$this->database = Model::getDB();
	}
}