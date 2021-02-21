<?php

namespace App\Modules;

use Sura\Libs\Request;

class LoadcityController extends Module{

    /**
     *  Загрузка городов
     * @throws \Throwable
     */
    public function index(): int
    {
        //$lang = langs::get_langs();
//        $db = $this->db();

        $request = (Request::getRequest()->getGlobal());

        $country_id = (int)$request['country'];

        echo '<option value="0">- Выбрать -</option>';

        if($country_id > 0){
            echo   (new \App\Libs\Support)->allCity($country_id, 1);
        }

        echo '<script type="text/javascript">$(\'#load_mini\').hide();</script>'; //!NB

        return 1;
    }
}