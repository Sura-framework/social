<?php

namespace App\Modules;


use Gregwar\Captcha\CaptchaBuilder;
use Sura\Libs\Status;

class AntibotController extends Module
{
	
	public function index(): void
	{
		session_start();
		
		$builder = new CaptchaBuilder();
		
		/** Generate the image */
		$builder->build();
		
		/** Captcha phrase */
		$_SESSION['s_code'] = $builder->getPhrase();
		
		header('Content-type: image/jpeg');
		/** Outputs the image */
		$builder->output();
	}
	
	/**
	 *  проверка капчи
	 * @throws \JsonException
	 */
	public static function code(): int
	{
		session_start();
		if ($_GET['user_code'] == $_SESSION['s_code']) {
			$status = Status::OK;
		}else{
			$status = Status::NOT_DATA;
		}
		return _e_json(array(
			'status' => $status,
		) );
	}
}