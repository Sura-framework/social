<?php

declare(strict_types=1);

namespace App\Models;


use Sura\Database\Connection;
use Sura\Libs\Model;

class Search
{
	
	private Connection $database;
	
	/**
	 * Profile constructor.
	 */
	public function __construct()
	{
		$this->database = Model::getDB();
	}
}