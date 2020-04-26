<?php
namespace App\Modules;

use Sura\Classes\Templates;
use Sura\Libs\Registry;
use App\Modules\Module;

/**
 * Страница заблокирована
 */
class Profile_ban extends Module
{
	public static function index()
    {
        $tpl = new Templates();
        $config = include __DIR__.'/../data/config.php';
        $tpl->dir = __DIR__.'/../templates/'.$config['temp'];

        $user_info = Registry::get('user_info');
		 if($user_info['user_group'] != '1'){
			$tpl->load_template('profile/profile_baned.tpl');
			if($user_info['user_ban_date'])
				$tpl->set('{date}', langdate('j F Y в H:i', $user_info['user_ban_date']));
			else
				$tpl->set('{date}', 'Неограниченно');
			$tpl->compile('main');
			echo str_replace('{theme}', '/templates/'.$config['temp'], $tpl->result['main']);
		 }

        return die();
	}
}
