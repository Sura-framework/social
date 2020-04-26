<?php
namespace App\Modules;

use Sura\Classes\Templates;
use Sura\Libs\Registry;
use App\Modules\Module;

/**
 * Временное отключение сайта
 */
class OfflineController extends Module
{
	
	public static function index()
	{
        $tpl = new Templates();
        $config = include __DIR__.'/../data/config.php';
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

		// if($user_info['user_group'] != '1'){
			$tpl->load_template('offline.tpl');

			$config['offline_msg'] = str_replace('&quot;', '"', stripslashes($config['offline_msg']));
			$tpl->set('{reason}', nl2br($config['offline_msg']));
			$tpl->compile('main');
			echo $tpl->result['main'];
	}
}
