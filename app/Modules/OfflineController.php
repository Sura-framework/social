<?php

namespace App\Modules;

use Sura\Libs\Settings;

/**
 * Временное отключение сайта
 */
class OfflineController extends Module
{

    /**
     * offline
     */
	public static function index(): int
    {
        $tpl = new Templates();
        $config = Settings::load();
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

		// if($user_info['user_group'] != '1'){
			$tpl->load_template('offline.tpl');

			$config['offline_msg'] = str_replace('&quot;', '"', stripslashes($config['offline_msg']));
			$tpl->set('{reason}', nl2br($config['offline_msg']));
			$tpl->compile('main');
			echo $tpl->result['main'];
        return view('info.info', $params);
	}
}
