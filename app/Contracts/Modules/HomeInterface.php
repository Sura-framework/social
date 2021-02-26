<?php

namespace App\Contracts\Modules;

interface HomeInterface
{

    public function index(array $params): int;

    public function login(array $params): int;

    public function Test(array $params): int;


}