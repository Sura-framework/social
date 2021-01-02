<?php
/*
 * Copyright (c) 2021. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Contracts\Modules;


use Exception;

/**
 * Новости
 *
 * Class FeedController
 */
interface FeedInterface
{
    /**
     * предыдущие новости
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function next($params): string;

    /**
     * новости
     *
     * @param $params
     * @return string
     * @throws Exception
     */
    public function feed($params): string;
}