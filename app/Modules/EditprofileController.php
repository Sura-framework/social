<?php

namespace App\Modules;

use Exception;
use Sura\Libs\Cache;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Settings;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Sura\Libs\Validation;

class EditprofileController extends Module{

    /**
     * Загрузка фотографии
     */
    //TODO json output
    public function upload($params){
        //$tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            $metatags['title'] = $lang['editmyprofile'];

            $user_id = $user_info['user_id'];
            $upload_dir = __DIR__.'/../../public/uploads/users/';

            //Если нет папок юзера, то создаём её
            if(!is_dir($upload_dir.$user_id)){
                @mkdir($upload_dir.$user_id, 0777 );
                @chmod($upload_dir.$user_id, 0777 );
                @mkdir($upload_dir.$user_id.'/albums', 0777 );
                @chmod($upload_dir.$user_id.'/albums', 0777 );
            }

            //Разришенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Получаем данные о фотографии
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = intval($_SERVER['REQUEST_TIME']);
            $image_rename = substr(md5($server_time+rand(1,100000)), 0, 15); // имя фотографии
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $image_name);
            $type = end($array); // формат файла

            //Проверям если, формат верный то пропускаем
            if(in_array($type, $allowed_files)){
                if($image_size < 5000000){
                    $res_type = '.'.$type;
                    //upgraded
                    $upload_dir = $upload_dir.$user_id.'/'; // Директория куда загружать
                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)) {

                        $manager = new ImageManager(array('driver' => 'gd'));

                        //Создание оригинала
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.'o_'.$image_rename.'.webp', 85);

                        //Создание главной фотографии
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(200, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.$image_rename.'.webp', 75);

                        //Создание уменьшеной копии 50х50
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(50, 50);
                        $image->save($upload_dir.'50_'.$image_rename.'.webp', 85);

                        //Создание уменьшеной копии 100х100
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(100, 100);
                        $image->save($upload_dir.'100_'.$image_rename.'.webp', 90);

                        unlink($upload_dir.$image_rename.$res_type);
                        $res_type = '.webp';

                        // Зачем это?
                        //  $image_rename = $db->safesql($image_rename);
                        //  $res_type = $db->safesql($res_type);

                        //Добавляем на стену
                        $row = $db->super_query("SELECT user_sex FROM `users` WHERE user_id = '{$user_id}'");
                        if($row['user_sex'] == 2)
                            $sex_text = 'обновила';
                        else
                            $sex_text = 'обновил';

                        $wall_text = "<div class=\"profile_update_photo\"><a href=\"\" onClick=\"Photo.Profile(\'{$user_id}\', \'{$image_rename}{$res_type}\'); return false\"><img src=\"/uploads/users/{$user_id}/o_{$image_rename}{$res_type}\" style=\"margin-top:3px\"></a></div>";

                        $db->query("INSERT INTO `wall` SET author_user_id = '{$user_id}', for_user_id = '{$user_id}', text = '{$wall_text}', add_date = '{$server_time}', type = '{$sex_text} фотографию на странице:'");
                        $db_id = $db->insert_id();

                        $db->query("UPDATE `users` SET user_wall_num = user_wall_num+1 WHERE user_id = '{$user_id}'");

                        //Добавляем в ленту новостей
                        $db->query("INSERT INTO `news` SET ac_user_id = '{$user_id}', action_type = 1, action_text = '{$wall_text}', obj_id = '{$db_id}', action_time = '{$server_time}'");

                        //Обновляем имя фотки в бд
                        $db->query("UPDATE `users` SET user_photo = '{$image_rename}{$res_type}', user_wall_id = '{$db_id}' WHERE user_id = '{$user_id}'");

                        $config = Settings::loadsettings();

                        echo $config['home_url'].'uploads/users/'.$user_id.'/'.$image_rename.$res_type;

                        Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
                        Cache::mozg_clear_cache();
                        //json!
                    } else
                        echo 'bad';
                } else
                    echo 'big_size';
            } else
                echo 'bad_format';

//            die();
        }
    }

    /**
     * Удаление фотографии
     * @param $params
     */
    //TODO json output
    public function del_photo($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $user_id = $user_info['user_id'];
            $uploaddir = __DIR__.'/../../public/uploads/users/'.$user_id.'/';
            $row = $db->super_query("SELECT user_photo, user_wall_id FROM `users` WHERE user_id = '{$user_id}'");
            if($row['user_photo']){
                $check_wall_rec = $db->super_query("SELECT COUNT(*) AS cnt FROM `wall` WHERE id = '{$row['user_wall_id']}'");
                if($check_wall_rec['cnt']){
                    $update_wall = ", user_wall_num = user_wall_num-1";
                    $db->query("DELETE FROM `wall` WHERE id = '{$row['user_wall_id']}'");
                    $db->query("DELETE FROM `news` WHERE obj_id = '{$row['user_wall_id']}'");
                }

                $db->query("UPDATE `users` SET user_photo = '', user_wall_id = '' {$update_wall} WHERE user_id = '{$user_id}'");

                unlink($uploaddir.$row['user_photo']);
                unlink($uploaddir.'50_'.$row['user_photo']);
                unlink($uploaddir.'100_'.$row['user_photo']);
                unlink($uploaddir.'o_'.$row['user_photo']);
                unlink($uploaddir.'130_'.$row['user_photo']);

                Cache::mozg_clear_cache_file('user_'.$user_id.'/profile_'.$user_id);
                Cache::mozg_clear_cache();
            }
            //json!
//            die();
        }
    }

    /**
     * Страница загрузки главной фотографии
     * @param $params
     * @return string
     * @throws Exception
     */
    //TODO add nlade template
    public function load_photo($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        //$db = $this->db();
        //$user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            $params['title'] = $lang['editmyprofile'].' | Sura';

            //Tools::NoAjaxQuery();
//            $tpl->load_template('load_photo.tpl');
//            $tpl->compile('content');
//            Tools::AjaxTpl($tpl);
        }

//        $params['tpl'] = $tpl;
//        Page::generate($params);
//        return true;
        return view('profile.load_photo', $params);
    }

    /**
     * Сохранение основых данных
     * @param $params
     */
    //TODO json output
    public function save_general($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $post_user_sex = intval($_POST['sex']);
            if($post_user_sex == 1 OR $post_user_sex == 2)
                $user_sex = $post_user_sex;
            else
                $user_sex = false;

            $user_day = intval($_POST['day']);
            $user_month = intval($_POST['month']);
            $user_year = intval($_POST['year']);
            $user_country = intval($_POST['country']);
            $user_city = intval($_POST['city']);
            $user_birthday = $user_year.'-'.$user_month.'-'.$user_day;

            if($user_sex){
                $post_sp = intval($_POST['sp']);
                if($post_sp >= 1 AND $post_sp <= 7)
                    $sp = $post_sp;
                else
                    $sp = false;

                if($sp){
                    $sp_val = intval($_POST['sp_val']);
                    $user_sp = $sp.'|'.$sp_val;
                }
            }

            if($user_country > 0){
                $country_info = $db->super_query("SELECT name FROM `country` WHERE id = '".$user_country."'");
                $city_info = $db->super_query("SELECT name FROM `city` WHERE id = '".$user_city."'");

                $user_country_city_name = $country_info['name'].'|'.$city_info['name'];
            } else {
                $user_city = 0;
                $user_country = 0;
                $user_country_city_name = '';
            }

            $db->query("UPDATE `users` SET user_sex = '{$user_sex}', user_day = '{$user_day}', user_month = '{$user_month}', user_year = '{$user_year}', user_country = '{$user_country}', user_city = '{$user_city}', user_country_city_name = '{$user_country_city_name}', user_birthday = '{$user_birthday}', user_sp = '{$user_sp}' WHERE user_id = '{$user_info['user_id']}'");

            Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);
            Cache::mozg_clear_cache();

            echo 'ok';

            die();
        }
    }

    /**
     * Сохранение контактов
     * @param $params
     */
    //TODO json output
    public function save_contact($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $xfields = array();
            $xfields['vk'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['vk'], 0, 200))));
            $xfields['od'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['od'], 0, 200))));
            $xfields['phone'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['phone'], 0, 200))));
            $xfields['skype'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['skype'], 0, 200))));
            $xfields['fb'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['fb'], 0, 200))));
            $xfields['icq'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['icq'], 0, 200))));
            $xfields['site'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['site'], 0, 200))));

            $xfieldsdata = '';
            foreach($xfields as $name => $value){
                $value = str_replace("|", "&#124;", $value);
                if(strlen($value) > 0)
                    $xfieldsdata .= $name.'|'.$value.'||';
            }

            $db->query("UPDATE `users` SET user_xfields = '{$xfieldsdata}' WHERE user_id = '{$user_info['user_id']}'");

            Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);

            echo 'ok';

            die();
        }
    }

    /**
     * Сохранение интересов
     * @param $params
     */
    //TODO json output
    public function save_interests($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $xfields = array();
            $xfields['activity'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['activity'], 0, 5000))));
            $xfields['interests'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['interests'], 0, 5000))));
            $xfields['myinfo'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['myinfo'], 0, 5000))));
            $xfields['music'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['music'], 0, 5000))));
            $xfields['kino'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['kino'], 0, 5000))));
            $xfields['books'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['books'], 0, 5000))));
            $xfields['games'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['games'], 0, 5000))));
            $xfields['quote'] = $db->safesql(Validation::ajax_utf8(htmlspecialchars(substr($_POST['quote'], 0, 5000))));

            $xfieldsdata = '';
            foreach($xfields as $name => $value){
                $value = str_replace("|", "&#124;", $value);
                if(strlen($value) > 0)
                    $xfieldsdata .= $name.'|'.$value.'||';
            }

            $db->query("UPDATE `users` SET user_xfields_all = '{$xfieldsdata}' WHERE user_id = '{$user_info['user_id']}'");

            Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);

            echo 'ok';

//            die();
        }
    }

    /**
     * Сохранение доп.полей
     * @param $params
     */
    //TODO json output
    public function save_xfields($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $xfields = profileload();

            $postedxfields = $_POST['xfields'];

            $newpostedxfields = array();
            $xfieldsid = '';
            $xfieldsdata = xfieldsdataload($xfieldsid);

            foreach($xfields as $name => $value){

                $newpostedxfields[$value[0]] = $postedxfields[$value[0]];

                if($value[2] == "select"){
                    $options = explode("\r\n", $value[3]);

                    $newpostedxfields[$value[0]] = $options[$postedxfields[$value[0]]].'|1';
                }

            }

            $postedxfields = $newpostedxfields;

            foreach($postedxfields as $xfielddataname => $xfielddatavalue){

                if(!$xfielddatavalue){
                    continue;
                }

                $expxfielddatavalue = explode('|', $xfielddatavalue);

                if($expxfielddatavalue[1])
                    $xfielddatavalue = str_replace('|1', '', Validation::textFilter($xfielddatavalue));
                else
                    $xfielddatavalue = Validation::ajax_utf8(Validation::textFilter($xfielddatavalue));

                $xfielddataname = $db->safesql($xfielddataname);

                if(isset($xfielddatavalue) AND !empty($xfielddatavalue)){
                    $xfielddataname = str_replace("|", "&#124;", $xfielddataname);
                    $xfielddatavalue = str_replace("|", "&#124;", $xfielddatavalue);
                    $filecontents[] = "$xfielddataname|$xfielddatavalue";
                }
            }

            if($filecontents)
                $filecontents = implode( "||", $filecontents);
            else
                $filecontents = '';

            $db->query("UPDATE `users` SET xfields = '{$filecontents}' WHERE user_id = '{$user_info['user_id']}'");

            Cache::mozg_clear_cache_file('user_'.$user_info['user_id'].'/profile_'.$user_info['user_id']);

//            exit;
        }
    }

    /**
     * Страница Редактирование контактов
     * @param $params
     * @return string
     * @throws Exception
     */
    //TODO add Blade template
    public function contact($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $user_speedbar = $lang['editmyprofile'].' &raquo; '.$lang['editmyprofile_contact'];
//            $tpl->load_template('profile/editprofile.tpl');
            $row = $db->super_query("SELECT user_xfields FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            $xfields = xfieldsdataload($row['user_xfields']);
//            $tpl->set('{vk}', );
            $params['vk'] = stripslashes($xfields['vk']);
//            $tpl->set('{od}', );
            $params['od'] = stripslashes($xfields['od']);
//            $tpl->set('{fb}', );
            $params['fb'] = stripslashes($xfields['fb']);
//            $tpl->set('{skype}', );
            $params['skype'] = stripslashes($xfields['skype']);
//            $tpl->set('{icq}', );
            $params['icq'] = stripslashes($xfields['icq']);
//            $tpl->set('{phone}', );
            $params['phone'] = stripslashes($xfields['phone']);
//            $tpl->set('{site}', );
            $params['site'] = stripslashes($xfields['site']);
//            $tpl->set_block("'\\[general\\](.*?)\\[/general\\]'si","");
            $params['general'] = false;
//            $tpl->set_block("'\\[interests\\](.*?)\\[/interests\\]'si","");
            $params['interests'] = false;
//            $tpl->set_block("'\\[xfields\\](.*?)\\[/xfields\\]'si","");
            $params['xfields'] = false;
//            $tpl->set('[contact]', '');
//            $tpl->set('[/contact]', '');
            $params['contact'] = true;
//            $tpl->compile('content');
//            $tpl->clear();

            return view('profile.contacts', $params);
        }
    }

    /**
     * Страница Редактирование интересов
     * @param $params
     * @return string
     * @throws Exception
     */
    public function interests($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $user_speedbar = $lang['editmyprofile'].' &raquo; '.$lang['editmyprofile_interests'];
//            $tpl->load_template('profile/editprofile.tpl');
            $row = $db->super_query("SELECT user_xfields_all FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            $xfields = xfieldsdataload($row['user_xfields_all']);
//            $tpl->set('{activity}',  );
            $params['activity'] = stripslashes($xfields['activity']);
//            $tpl->set('{interests}', );
            $params['interests'] = stripslashes($xfields['interests']);
//            $tpl->set('{myinfo}', );
            $params['myinfo'] = stripslashes($xfields['myinfo']);
//            $tpl->set('{music}', );
            $params['music'] = stripslashes($xfields['music']);
//            $tpl->set('{kino}', );
            $params['kino'] = stripslashes($xfields['kino']);
//            $tpl->set('{books}', );
            $params['books'] = stripslashes($xfields['books']);
//            $tpl->set('{games}', );
            $params['games'] = stripslashes($xfields['games']);
//            $tpl->set('{quote}', );
            $params['quote'] = stripslashes($xfields['quote']);
//            $tpl->set_block("'\\[contact\\](.*?)\\[/contact\\]'si","");
            $params['contact'] = false;
//            $tpl->set_block("'\\[general\\](.*?)\\[/general\\]'si","");
            $params['general'] = false;
//            $tpl->set_block("'\\[xfields\\](.*?)\\[/xfields\\]'si","");
            $params['xfields'] = false;
//            $tpl->set('[interests]', '');
//            $tpl->set('[/interests]', '');
            $params['interests'] = true;
//            $tpl->compile('content');
//            $tpl->clear();

            return view('profile.interests', $params);
        }
    }

    /**
     * Страница Редактирование доп.полей
     */
/*    public function all($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $user_speedbar = $lang['editmyprofile'].' &raquo; Другое';
            $tpl->load_template('profile/editprofile.tpl');

            $xfields = profileload();

            $row = $db->super_query("SELECT xfields FROM `users` WHERE user_id = '".$user_info['user_id']."'");

            $xfieldsdata = xfieldsdataload($row['xfields']);

            foreach($xfields as $name => $value){

                $fieldvalue = $xfieldsdata[$value[0]];
                $fieldvalue = stripslashes($fieldvalue);

                $output .= "<div class=\"texta\">{$value[1]}:</div>";

                $for_js_list .= "'xfields[{$value[0]}]': $('#{$value[0]}').val(), ";

                if($value[2] == "textarea"){

                    $output .= '<textarea id="'.$value[0].'" class="inpst" style="width:300px;height:50px;">'.myBrRn($fieldvalue).'</textarea>';

                } elseif($value[2] == "text"){

                    $output .= '<input type="text" id="'.$value[0].'" class="inpst" maxlength="100" value="'.$fieldvalue.'" style="width:300px;" />';

                } elseif($value[2] == "select"){

                    $output .= '<select class="inpst" id="'.$value[0].'">';
                    $output .= '<option value="">- Не выбрано -</option>';

                    foreach(explode("\r\n", $value[3]) AS $index => $value){

                        $value = str_replace("'", "&#039;", $value);
                        $output .= "<option value=\"$index\"" . ($fieldvalue == $value ? " selected" : "") . ">$value</option>\r\n";

                    }

                    $output .= '</select>';

                }

                $output .= '<div class="mgclr"></div>';

            }

            $for_js_list = substr($for_js_list, 0, (strlen($for_js_list)-2));

            $tpl->set('{xfields}', $output);
            $tpl->set('{for-js-list}', $for_js_list);

            $tpl->set_block("'\\[contact\\](.*?)\\[/contact\\]'si","");
            $tpl->set_block("'\\[general\\](.*?)\\[/general\\]'si","");
            $tpl->set_block("'\\[interests\\](.*?)\\[/interests\\]'si","");
            $tpl->set('[xfields]', '');
            $tpl->set('[/xfields]', '');
            $tpl->compile('content');
            $tpl->clear();

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }*/

    /**
     * Страница миниатюры
     * @param $params
     */
    public function miniature($params){
//        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';
            $row = $db->super_query("SELECT user_photo FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            if($row['user_photo']){
//                $tpl->load_template('miniature/main.tpl');
//                $tpl->set('{user-id}', );
                $params['user_id'] = $user_info['user_id'];
//                $tpl->set('{ava}', );
                $params['ava'] = $row['user_photo'];
                return view('profile.miniature', $params);
            } else
                echo '1';
        }
    }

    /**
     * Сохранение миниатюры
     * @param $params
     */
    public function miniature_save($params){
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $row = $db->super_query("SELECT user_photo FROM `users` WHERE user_id = '{$user_info['user_id']}'");

            $i_left = intval($_POST['i_left']);
            $i_top = intval($_POST['i_top']);
            $i_width = intval($_POST['i_width']);
            $i_height = intval($_POST['i_height']);

            $upload_dir = __DIR__."/../../public/uploads/users/{$user_info['user_id']}/";
            $image_rename = $row['user_photo'];

            if($row['user_photo'] AND $i_width >= 100 AND $i_height >= 100 AND $i_left >= 0 AND $i_height >= 0){
                $manager = new ImageManager(array('driver' => 'gd'));

//                $image = $manager->make($upload_dir.$image_rename);
//                $image->save($upload_dir.$image_rename, 90);
//                $image->crop($i_width, $i_height, $i_left, $i_top);

//                $image_name = str_replace(".webp", "", $image_rename);

                $image = $manager->make($upload_dir.$image_rename)->resize(100, 100);
                $image->save($upload_dir.'100_'.$image_rename, 90);
                $image = $manager->make($upload_dir.'100_'.$image_rename);
                $image->crop(100, 100, $i_left, $i_top);

                $image = $manager->make($upload_dir.$image_rename)->resize(50, 50);
                $image->save($upload_dir.'50_'.$image_rename, 90);
                $image = $manager->make($upload_dir.'50_'.$image_rename);
                $image->crop(50, 50, $i_left, $i_top);

                echo $user_info['user_id'];
            } else
                echo 'err';
//            exit();
        }else{
            echo 'err: not logged';
//            exit();
        }
    }

    /**
     * Загрузка обложки
     */
/*    public function upload_cover($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

//            Tools::NoAjaxQuery();

            //Получаем данные о файле
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = intval($_SERVER['REQUEST_TIME']);
            $image_rename = substr(md5($server_time+rand(1,100000)), 0, 20); // имя файла
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $type = end(explode(".", $image_name)); // формат файла

            $max_size = 1024 * 7000;

            //Проверка размера
            if($image_size <= $max_size){
                //Разришенные форматы
                $allowed_files = explode(', ', 'jpg, jpeg, jpe, png, gif');
                //Проверям если, формат верный то пропускаем
                if(in_array(strtolower($type), $allowed_files)){
                    $res_type = strtolower('.'.$type);
                    $upload_dir = __DIR__."/../../public/uploads/users/{$user_info['user_id']}/";
                    $rImg = $upload_dir.$image_rename.$res_type;
                    $rImg_c = $upload_dir.'c_'.$image_rename.$res_type;
                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)){
                        //imagick gd
                        $manager = new ImageManager(array('driver' => 'gd'));
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(960, 540);
                        $image->save($upload_dir.$image_rename.'.webp', 90);
                        unlink($upload_dir.$image_rename.$res_type);

                        $res_type = '.webp';
                        $rImg = $upload_dir.$image_rename.'.webp';


                        //Выводим и удаляем пред. обложку
                        $row = $db->super_query("SELECT user_cover FROM `users` WHERE user_id = '{$user_info['user_id']}'");
                        if($row){
                            unlink($upload_dir.$row['user_cover']);
                        }
                        $imgData = getimagesize($rImg);
                        $rImgsData = round($imgData[1] / ($imgData[0] / 960));

                        //Обновдяем обложку в базе
                        $pos = round(($rImgsData / 2) - 100);
                        if($rImgsData <= 230){
                            $rImgsData = 230;
                            $pos = 0;
                        }
                        $db->query("UPDATE `users` SET user_cover = '{$image_rename}{$res_type}', user_cover_pos = '{$pos}' WHERE user_id = '{$user_info['user_id']}'");
                        echo $user_info['user_id'].'/'.$image_rename.$res_type.'|'.$rImgsData;

                        //Чистим кеш
                        Cache::mozg_clear_cache_file("user_{$user_info['user_id']}/profile_{$user_info['user_id']}");
                    }
                } else
                    echo 2;
            } else
                echo 1;
            exit();
        }
    }*/

    /**
     * Сохранение новой позиции обложки
     */
/*    public function savecoverpos($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

//            Tools::NoAjaxQuery();

            $pos = intval($_POST['pos']);
            if($pos < 0) $pos = 0;

            $db->query("UPDATE `users` SET user_cover_pos = '{$pos}' WHERE user_id = '{$user_info['user_id']}'");

            //Чистим кеш
            Cache::mozg_clear_cache_file("user_{$user_info['user_id']}/profile_{$user_info['user_id']}");

            exit();
        }
    }*/

    /**
     * Удаление обложки
     */
/*    public function delcover($params){
        $tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

//            Tools::NoAjaxQuery();

            //Выводим и удаляем пред. обложку
            $row = $db->super_query("SELECT user_cover FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            if($row){

                $upDir = __DIR__."/../../uploads/users/{$user_info['user_id']}/";
                @unlink($upDir.$row['user_cover']);

            }

            $db->query("UPDATE `users` SET user_cover_pos = '', user_cover = '' WHERE user_id = '{$user_info['user_id']}'");

            //Чистим кеш
            Cache::mozg_clear_cache_file("user_{$user_info['user_id']}/profile_{$user_info['user_id']}");

            exit();
        }
    }*/

    /**
     * Страница Редактирование основное
     * @param $params
     */
    public function index($params){
//        $tpl = $params['tpl'];

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

//            $user_speedbar = $lang['editmyprofile'].' &raquo; '.$lang['editmyprofile_genereal'];

//            $tpl->load_template('profile/editprofile.tpl');

            $row = $db->super_query("SELECT user_name, user_lastname, user_sex, user_day, user_month, user_year, user_country, user_city, user_sp FROM `users` WHERE user_id = '{$user_info['user_id']}'");

//            $tpl->set('{name}', );
            $params['name'] = $row['user_name'];
//            $tpl->set('{lastname}', );
            $params['lastname'] = $row['user_lastname'];
//            $tpl->set('{sex}', );
            $params['sex'] = installationSelected($row['user_sex'], '<option value="1">мужской</option><option value="2">женский</option>');
//            $tpl->set('{user-day}', );
            $params['user_day'] = installationSelected($row['user_day'], '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>');
//            $tpl->set('{user-month}', );
            $params['user_month'] = installationSelected($row['user_month'], '<option value="1">Января</option><option value="2">Февраля</option><option value="3">Марта</option><option value="4">Апреля</option><option value="5">Мая</option><option value="6">Июня</option><option value="7">Июля</option><option value="8">Августа</option><option value="9">Сентября</option><option value="10">Октября</option><option value="11">Ноября</option><option value="12">Декабря</option>');
//            $tpl->set('{user-year}', );
            $params['user_year'] = installationSelected($row['user_year'], '<option value="1930">1930</option><option value="1931">1931</option><option value="1932">1932</option><option value="1933">1933</option><option value="1934">1934</option><option value="1935">1935</option><option value="1936">1936</option><option value="1937">1937</option><option value="1938">1938</option><option value="1939">1939</option><option value="1940">1940</option><option value="1941">1941</option><option value="1942">1942</option><option value="1943">1943</option><option value="1944">1944</option><option value="1945">1945</option><option value="1946">1946</option><option value="1947">1947</option><option value="1948">1948</option><option value="1949">1949</option><option value="1950">1950</option><option value="1951">1951</option><option value="1952">1952</option><option value="1953">1953</option><option value="1954">1954</option><option value="1955">1955</option><option value="1956">1956</option><option value="1957">1957</option><option value="1958">1958</option><option value="1959">1959</option><option value="1960">1960</option><option value="1961">1961</option><option value="1962">1962</option><option value="1963">1963</option><option value="1964">1964</option><option value="1965">1965</option><option value="1966">1966</option><option value="1967">1967</option><option value="1968">1968</option><option value="1969">1969</option><option value="1970">1970</option><option value="1971">1971</option><option value="1972">1972</option><option value="1973">1973</option><option value="1974">1974</option><option value="1975">1975</option><option value="1976">1976</option><option value="1977">1977</option><option value="1978">1978</option><option value="1979">1979</option><option value="1980">1980</option><option value="1981">1981</option><option value="1982">1982</option><option value="1983">1983</option><option value="1984">1984</option><option value="1985">1985</option><option value="1986">1986</option><option value="1987">1987</option><option value="1988">1988</option><option value="1989">1989</option><option value="1990">1990</option><option value="1991">1991</option><option value="1992">1992</option><option value="1993">1993</option><option value="1994">1994</option><option value="1995">1995</option><option value="1996">1996</option><option value="1997">1997</option><option value="1998">1998</option><option value="1999">1999</option><option value="2000">2000</option><option value="2001">2001</option><option value="2002">2002</option><option value="2003">2003</option><option value="2004">2004</option><option value="2005">2005</option><option value="2006">2006</option><option value="2007">2007</option>');

                //################## Загружаем Страны ##################//
            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `name` ASC", true, "country", true);
            $all_country = '';
            foreach($sql_country as $row_country){
                $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
            }

//            $tpl->set('{country}', );
            $params['country'] = installationSelected($row['user_country'], $all_country);

                //################## Загружаем Города ##################//
            $sql_city = $db->super_query("SELECT id, name FROM `city` WHERE id_country = '{$row['user_country']}' ORDER by `name` ASC", true, "country_city_".$row['user_country'], true);
            $all_city = '';
            foreach($sql_city as $row2){
                $all_city .= '<option value="'.$row2['id'].'">'.stripslashes($row2['name']).'</option>';
            }

//            $tpl->set('{city}', );
            $params['city'] = installationSelected($row['user_city'], $all_city);

            $user_sp = explode('|', $row['user_sp']);
            if($user_sp[1]){
                $rowSp = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '{$user_sp[1]}'");
//                $tpl->set('{sp-name}', );
//                $tpl->set_block("'\\[sp\\](.*?)\\[/sp\\]'si","");
                $params['sp_name'] = $rowSp['user_search_pref'];
                $params['sp'] = false;

                if($row['user_sex'] == 1){
                    if($user_sp[0] == 2)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Подруга:';
                    elseif($user_sp[0] == 3)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Невеста:';
                    else if($user_sp[0] == 4)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Жена:';
                    else if($user_sp[0] == 5)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Любимая:';
                    else
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Партнёр:';
                } else {
                    if($user_sp[0] == 2)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Друг:';
                    elseif($user_sp[0] == 3)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Жених:';
                    else if($user_sp[0] == 4)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Муж:';
                    else if($user_sp[0] == 5)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Любимый:';
                    else
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Партнёр:';
                }
            } else {
//                $tpl->set('[sp]', '');
//                $tpl->set('[/sp]', '');
                $params['sp'] = true;
            }

            if($row['user_sex'] == 2){
//                $tpl->set('[user-m]', '');
//                $tpl->set('[/user-m]', '');
                $params['user_m'] = true;
//                $tpl->set_block("'\\[user-w\\](.*?)\\[/user-w\\]'si","");
                $params['user_w'] = false;
            } elseif($row['user_sex'] == 1){
//                $tpl->set('[user-w]', '');
//                $tpl->set('[/user-w]', '');
                $params['user_w'] = true;
//                $tpl->set_block("'\\[user-m\\](.*?)\\[/user-m\\]'si","");
                $params['user_m'] = false;
            } else {
//                $tpl->set('[sp-all]', '');
//                $tpl->set('[/sp-all]', '')
                $params['sp_all'] = true;
//                $tpl->set('[user-m]', '');
//                $tpl->set('[/user-m]', '');
                $params['user_m'] = true;
//                $tpl->set('[user-w]', '');
//                $tpl->set('[/user-w]', '');
                $params['user_w'] = true;
            }

//            $tpl->copy_template = str_replace("[instSelect-sp-{$user_sp[0]}]", 'selected', $tpl->copy_template);
//            $params['instSelect_sp_'.$user_sp['0']] =
//            $tpl->set_block("'\\[instSelect-(.*?)\\]'si","");
//            $params['instSelect'] = false;

//            $tpl->set_block("'\\[contact\\](.*?)\\[/contact\\]'si","");
            $params['contact'] = false;
//            $tpl->set_block("'\\[interests\\](.*?)\\[/interests\\]'si","");
            $params['interests'] = false;
//            $tpl->set_block("'\\[xfields\\](.*?)\\[/xfields\\]'si","");
            $params['xfields'] = false;
//            $tpl->set('[general]', '');
//            $tpl->set('[/general]', '');
            $params['general'] = true;
//            $tpl->compile('content');
//            $tpl->clear();

            return view('profile.main', $params);
        } else {
//            $user_speedbar = 'Информация';
//            msgbox('', $lang['not_logged'], 'info');
        }

//        $params['tpl'] = $tpl;
//        Page::generate($params);
//        return true;
    }

    /**
     * Модальнок окно Редактирование основное
     * @param $params
     * @return bool
     * @throws Exception
     */
    public function box($params){
//        $tpl = $params['tpl'];

        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            $params['title'] = $lang['editmyprofile'].' | Sura';

            //$user_speedbar = $lang['editmyprofile'].' &raquo; '.$lang['editmyprofile_genereal'];

//            $tpl->load_template('profile/modal_editprofile.tpl');

            $row = $db->super_query("SELECT user_name, user_lastname, user_sex, user_day, user_month, user_year, user_country, user_city, user_sp, user_xfields FROM `users` WHERE user_id = '{$user_info['user_id']}'");

//            $tpl->set('{name}', $row['user_name']);
            $params['name'] =
//            $tpl->set('{lastname}', $row['user_lastname']);
            $params['lastname'] =
//            $tpl->set('{sex}', installationSelected($row['user_sex'], '<option value="1">мужской</option><option value="2">женский</option>'));
            $params['sex'] =
//            $tpl->set('{user-day}', installationSelected($row['user_day'], '<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>'));
            $params['user_day'] =
//            $tpl->set('{user-month}', installationSelected($row['user_month'], '<option value="1">Января</option><option value="2">Февраля</option><option value="3">Марта</option><option value="4">Апреля</option><option value="5">Мая</option><option value="6">Июня</option><option value="7">Июля</option><option value="8">Августа</option><option value="9">Сентября</option><option value="10">Октября</option><option value="11">Ноября</option><option value="12">Декабря</option>'));
            $params['user_month'] =
//            $tpl->set('{user-year}', installationSelected($row['user_year'], '<option value="1930">1930</option><option value="1931">1931</option><option value="1932">1932</option><option value="1933">1933</option><option value="1934">1934</option><option value="1935">1935</option><option value="1936">1936</option><option value="1937">1937</option><option value="1938">1938</option><option value="1939">1939</option><option value="1940">1940</option><option value="1941">1941</option><option value="1942">1942</option><option value="1943">1943</option><option value="1944">1944</option><option value="1945">1945</option><option value="1946">1946</option><option value="1947">1947</option><option value="1948">1948</option><option value="1949">1949</option><option value="1950">1950</option><option value="1951">1951</option><option value="1952">1952</option><option value="1953">1953</option><option value="1954">1954</option><option value="1955">1955</option><option value="1956">1956</option><option value="1957">1957</option><option value="1958">1958</option><option value="1959">1959</option><option value="1960">1960</option><option value="1961">1961</option><option value="1962">1962</option><option value="1963">1963</option><option value="1964">1964</option><option value="1965">1965</option><option value="1966">1966</option><option value="1967">1967</option><option value="1968">1968</option><option value="1969">1969</option><option value="1970">1970</option><option value="1971">1971</option><option value="1972">1972</option><option value="1973">1973</option><option value="1974">1974</option><option value="1975">1975</option><option value="1976">1976</option><option value="1977">1977</option><option value="1978">1978</option><option value="1979">1979</option><option value="1980">1980</option><option value="1981">1981</option><option value="1982">1982</option><option value="1983">1983</option><option value="1984">1984</option><option value="1985">1985</option><option value="1986">1986</option><option value="1987">1987</option><option value="1988">1988</option><option value="1989">1989</option><option value="1990">1990</option><option value="1991">1991</option><option value="1992">1992</option><option value="1993">1993</option><option value="1994">1994</option><option value="1995">1995</option><option value="1996">1996</option><option value="1997">1997</option><option value="1998">1998</option><option value="1999">1999</option><option value="2000">2000</option><option value="2001">2001</option><option value="2002">2002</option><option value="2003">2003</option><option value="2004">2004</option><option value="2005">2005</option><option value="2006">2006</option><option value="2007">2007</option>'));
            $params['user_year'] =

                //################## Загружаем Страны ##################//
            $sql_country = $db->super_query("SELECT * FROM `country` ORDER by `name` ASC", true, "country", true);
            $all_country = '';
            foreach($sql_country as $row_country){
                $all_country .= '<option value="'.$row_country['id'].'">'.stripslashes($row_country['name']).'</option>';
            }

//            $tpl->set('{country}', );
            $params['country'] = installationSelected($row['user_country'], $all_country);

                //################## Загружаем Города ##################//
            $sql_city = $db->super_query("SELECT id, name FROM `city` WHERE id_country = '{$row['user_country']}' ORDER by `name` ASC", true, "country_city_".$row['user_country'], true);
            $all_city = '';
            foreach($sql_city as $row2){
                $all_city .= '<option value="'.$row2['id'].'">'.stripslashes($row2['name']).'</option>';
            }

//            $tpl->set('{city}', );
            $params['city'] = installationSelected($row['user_city'], $all_city);

            $user_sp = explode('|', $row['user_sp']);
            if($user_sp[1]){
                $rowSp = $db->super_query("SELECT user_search_pref FROM `users` WHERE user_id = '{$user_sp[1]}'");
//                $tpl->set('{sp-name}', );
                $params['sp_name'] = $rowSp['user_search_pref'];
//                $tpl->set_block("'\\[sp\\](.*?)\\[/sp\\]'si","");
                $params['sp'] = false;

                if($row['user_sex'] == 1){
                    if($user_sp['0'] == 2)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Подруга:';
                    elseif($user_sp['0'] == 3)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Невеста:';
                    else if($user_sp['0'] == 4)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Жена:';
                    else if($user_sp['0'] == 5)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Любимая:';
                    else
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Партнёр:';
                } else {
                    if($user_sp['0'] == 2)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Друг:';
                    elseif($user_sp['0'] == 3)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Жених:';
                    else if($user_sp['0'] == 4)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Муж:';
                    else if($user_sp['0]' == 5)
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Любимый:';
                    else
//                        $tpl->set('{sp-text}', );
                        $params['sp_text'] = 'Партнёр:';
                }
            } else {
//                $tpl->set('[sp]', '');
//                $tpl->set('[/sp]', '');
                $params['sp'] = true;
            }

            if($row['user_sex'] == 2){
//                $tpl->set('[user-m]', '');
//                $tpl->set('[/user-m]', '');
                $params['user_m'] = true;
//                $tpl->set_block("'\\[user-w\\](.*?)\\[/user-w\\]'si","");
                $params['user_w'] = false;
            } elseif($row['user_sex'] == 1){
//                $tpl->set('[user-w]', '');
//                $tpl->set('[/user-w]', '');
                $params['user_w'] = true;
//                $tpl->set_block("'\\[user-m\\](.*?)\\[/user-m\\]'si","");
                $params['user_m'] = false;
            } else {
//                $tpl->set('[sp-all]', '');
//                $tpl->set('[/sp-all]', '');
                $params['sp_all'] = true;
//                $tpl->set('[user-m]', '');
//                $tpl->set('[/user-m]', '');
                $params['user_m'] = true;
//                $tpl->set('[user-w]', '');
//                $tpl->set('[/user-w]', '');
                $params['user_w'] = true;
            }

//            $tpl->copy_template = str_replace("[instSelect-sp-{$user_sp[0]}]", 'selected', $tpl->copy_template);
            $params['instSelect_sp_'.$user_sp[0]] =
//            $tpl->set_block("'\\[instSelect-(.*?)\\]'si","");
//            $params['ttt'] =

                //$row = $db->super_query("SELECT user_xfields FROM `users` WHERE user_id = '{$user_info['user_id']}'");
            $xfields = xfieldsdataload($row['user_xfields']);

//            $tpl->set('{vk}', );
            $params['vk'] = stripslashes($xfields['vk']);
//            $tpl->set('{od}', );
            $params['od'] = stripslashes($xfields['od']);
//            $tpl->set('{fb}', );
            $params['fb'] = stripslashes($xfields['fb']);
//            $tpl->set('{skype}', );
            $params['skype'] = stripslashes($xfields['skype']);
//            $tpl->set('{icq}', );
            $params['icq'] = stripslashes($xfields['icq']);
//            $tpl->set('{phone}', );
            $params['phone'] = stripslashes($xfields['phone']);
//            $tpl->set('{site}', );
            $params['site'] = stripslashes($xfields['site']);

//            $tpl->set('{activity}', );
            $params['activity'] = stripslashes($xfields['activity']);
//            $tpl->set('{interests}', );
            $params['interests'] = stripslashes($xfields['interests']);
//            $tpl->set('{myinfo}', );
            $params['myinfo'] = stripslashes($xfields['myinfo']);
//            $tpl->set('{music}', );
            $params['music'] = stripslashes($xfields['music']);
//            $tpl->set('{kino}', );
            $params['kino'] = stripslashes($xfields['kino']);
//            $tpl->set('{books}', );
            $params['books'] = stripslashes($xfields['books']);
//            $tpl->set('{games}', );
            $params['games'] = stripslashes($xfields['games']);
//            $tpl->set('{quote}', );
            $params['quote'] = stripslashes($xfields['quote']);

//            $tpl->compile('content');
//            $tpl->clear();
            return view('profile.edit_box', $params);
        } else {
//            $user_speedbar = 'Информация';
//            msgbox('', $lang['not_logged'], 'info');
            $params['title'] = $lang['no_infooo'];
            $params['info'] = $lang['not_logged'];
            return view('info.info', $params);
        }
    }
}