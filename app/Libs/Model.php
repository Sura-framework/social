<?php
declare(strict_types=1);

namespace App\Libs;

use Sura\Libs\Db;

abstract class Model
{
	/**
	 * Model constructor.
	 *
	 * Получение экземпляра класса.
	 * Если он уже существует, то возвращается, если его не было,
	 * то создаётся и возвращается (паттерн Singleton)
	 * @param Db $db
	 */
	public function __construct(protected Db $db)
	{
		$this->$db = Db::getDB();
	}
}