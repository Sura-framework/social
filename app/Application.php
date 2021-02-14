<?php
declare(strict_types=1);

namespace App;

use Sura\Application as Core_app;

class Application extends Core_app
{
    public function __construct(string|null $basePath = null)
    {
        if (!$basePath) {
            $basePath = str_replace('app', '', __DIR__);
        }
        parent::__construct($basePath);
    }
}