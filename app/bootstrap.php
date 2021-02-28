<?php
declare(strict_types=1);

use Sura\Console;
use Sura\Exception\SuraException;
use Sura\Libs\Auth;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Time\Zone;


if (PHP_SAPI === 'cli') {
    $app = new Console(__DIR__ . '/../');
} else {
    $app = new App\Application(dirname(__DIR__));

    $requests = Request::getRequest();
    $requests->setGlobal();

    if (isset($_POST["PHPSESSID"])) {
        session_id($_POST["PHPSESSID"]);
    }
    session_start();

    $user = Auth::index();
    $config = Settings::load();
    if (!$config['home_url']) {
        throw SuraException::Error('Sura not installed. Please install');
    }
    if ($config['offline'] == "yes") {
        App\Modules\OfflineController::index();
    }

    if (Registry::get('logged')) {
        Zone::zone($user['user_info']['time_zone']);
    }

    $app->make('app');
    $app->handle();
}
