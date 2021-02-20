<?php
declare(strict_types=1);

use Sura\Console;
use Sura\Exception\SuraException;
use Sura\Libs\Auth;
use Sura\Libs\Profile_check;
use Sura\Libs\Registry;
use Sura\Libs\Request;
use Sura\Libs\Settings;


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
//$requests->unsetGlobal();
//    $server = $requests->server;

    $user = Auth::index();
    $config = Settings::load();
    if (!$config['home_url']) {
        throw SuraException::Error('Sura not installed. Please install');
    }
    if ($config['offline'] == "yes") {
        App\Modules\OfflineController::index();
    }

    if (Registry::get('logged')) {
//        if ($user['user_info']['user_delet'] == 1) {
//            App\Modules\ProfileController::delete();
//        }
//        $server_time = Tools::time();
//        if($user['user_info']['user_ban_date'] >= $server_time OR $user['user_info']['user_ban_date'] == '0') {
//            App\Modules\ProfileController::ban();
//        }

        \Sura\Time\Zone::zone($user['user_info']['time_zone']);
    }

    $app->make('app');
    $app->handle();
}
