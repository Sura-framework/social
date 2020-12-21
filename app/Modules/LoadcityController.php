<?php

namespace App\Modules;

use Sura\Libs\Tools;

class LoadcityController extends Module{

    /**
     *
     */
    public function index()
    {
        //$lang = langs::get_langs();
        $db = $this->db();

        Tools::NoAjaxQuery();

        $country_id = intval($_POST['country']);

        echo '<option value="0">- Выбрать -</option>';

        if($country_id > 0){
            $sql_ = $db->super_query("SELECT id, name FROM `city` WHERE id_country = '{$country_id}' ORDER by `name` ", 1);
            foreach($sql_ as $row2)
                echo '<option value="'.$row2['id'].'">'.stripslashes($row2['name']).'</option>';
        }

        echo '<script type="text/javascript">$(\'#load_mini\').hide();</script>'; //!NB
    }
}