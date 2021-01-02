<?php

namespace App\Contracts\Modules;

interface HomeInterface
{

    public function index(array $params): string;

    public function login(array $params): string;

    public function Test(array $params): void;


}