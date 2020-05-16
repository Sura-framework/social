<?php

namespace App\Modules;

use Sura\Classes\Templates;
use Sura\Libs\Registry;
use App\Modules\Module;

/**
 * Страница удалена
 */
class Profile_delet extends Module
{
	public static function index()
    {
        $tpl = new Templates();
        $config = include __DIR__.'/../data/config.php';
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $config = include __DIR__.'/../data/config.php';

        $user_info = Registry::get('user_info');
		 if($user_info['user_group'] != '1'){
			$tpl->load_template('profile_deleted.tpl');
			$tpl->compile('main');
			echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
		 }

        return die();
	}
}