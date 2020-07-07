<?php

namespace App\Modules;

use Sura\Libs\Templates;
use Sura\Libs\Registry;
use App\Modules\Module;
use Sura\Libs\Settings;

/**
 * Временное отключение сайта
 */
class OfflineController extends Module
{
	
	public static function index()
	{
        $tpl = new Templates();
        $config = Settings::loadsettings();
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

		// if($user_info['user_group'] != '1'){
			$tpl->load_template('offline.tpl');

			$config['offline_msg'] = str_replace('&quot;', '"', stripslashes($config['offline_msg']));
			$tpl->set('{reason}', nl2br($config['offline_msg']));
			$tpl->compile('main');
			echo $tpl->result['main'];
	}
}
