<?php


namespace System\Libs;


use System\Classes\Db;

abstract class Model
{

    protected Db $db;

    /**
     * Model constructor.
     *
     * Получение экземпляра класса.
     * Если он уже существует, то возвращается, если его не было,
     * то создаётся и возвращается (паттерн Singleton)
     */
    public function __construct()
    {
//        $this->db = new Db();
        $this->db = Db::getDB();
    }
}