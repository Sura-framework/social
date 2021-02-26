<?php

namespace App\Contracts;


use Exception;

/**
 * Новости
 *
 * Class FeedController
 */
interface FeedInterface
{
    /**
     * Показ предыдущих записей
     *
     * @return int
     * @throws Exception|\Throwable
     */
    public function next(): int;

    /**
     * Вывод новостей
     *
     * @param array $params
     * @return int
     * @throws \Throwable
     */
    public function feed(array $params): int;
}