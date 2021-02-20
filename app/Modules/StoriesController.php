<?php


namespace App\Modules;


use Intervention\Image\ImageManager;
use Sura\Libs\Gramatic;
use Sura\Libs\Request;
use Sura\Libs\Settings;
use Sura\Libs\Tools;

class StoriesController  extends Module
{

    /**
     * Open pop-up box to add stories img
     *
     * @return int
     */
    public function addbox(): int
    {
        //FIXME add logged
        $tpl = $params['tpl'];

        Tools::NoAjaxRedirect();

        $tpl->load_template('stories/main.tpl');

        //$tpl->set('{langs}', $langs);

        $tpl->compile('content');

        return view('info.info', $params);
    }

    /**
     * upload
     *
     */
    public function upload(): int
    {
        //$tpl = $params['tpl'];
        $lang = $this->get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxRedirect();

        if($logged){
            //$act = $_GET['act'];
            $params['title'] = $lang['editmyprofile'].' | Sura';

            $user_id = $user_info['user_id'];
            $upload_dir = __DIR__.'/../../public/uploads/users/';

            //Если нет папок юзера, то создаём её
            if(!is_dir($upload_dir.$user_id)){
                mkdir($upload_dir.$user_id, 0777 );
                chmod($upload_dir.$user_id, 0777 );
                mkdir($upload_dir.$user_id.'/stories', 0777 );
                chmod($upload_dir.$user_id.'/stories', 0777 );
            }

            //Разришенные форматы
            $allowed_files = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

            //Получаем данные о фотографии
            $image_tmp = $_FILES['uploadfile']['tmp_name'];
            $image_name = Gramatic::totranslit($_FILES['uploadfile']['name']); // оригинальное название для оприделения формата
            $server_time = \Sura\Time\Date::time();
            $image_rename = substr(md5($server_time+rand(1,100000)), 0, 15); // имя фотографии
            $image_size = $_FILES['uploadfile']['size']; // размер файла
            $array = explode(".", $image_name);
            $type = end($array); // формат файла

            //Проверям если, формат верный то пропускаем
            if(in_array($type, $allowed_files)){
                if($image_size < 10000000){
                    $res_type = '.'.$type;
                    //upgraded
                    $upload_dir = $upload_dir.$user_id.'/stories/'; // Директория куда загружать
                    if(move_uploaded_file($image_tmp, $upload_dir.$image_rename.$res_type)) {

                        $manager = new ImageManager(array('driver' => 'gd'));

                        //Создание оригинала
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(770, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.'o_'.$image_rename.'.webp', 85);

                        //Создание главной фотографии
                        $image = $manager->make($upload_dir.$image_rename.$res_type)->resize(111, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->save($upload_dir.$image_rename.'.webp', 75);

                        unlink($upload_dir.$image_rename.$res_type);
                        $res_type = '.webp';

                        // Зачем это?
                        //  $image_rename = $db->safesql($image_rename);
                        //  $res_type = $db->safesql($res_type);


                        $config = Settings::load();

                        $url = $config['home_url'].'uploads/users/'.$user_id.'/stories/'.$image_rename.'.webp';

                        //Добавляем в бд
                        $db->query("INSERT INTO `stories` SET user_id = '{$user_id}', url = '{$url}', add_date = '{$server_time}'");


                        $check_stori = $db->super_query("SELECT id FROM `stories_feed` WHERE user_id = '{$user_id}'");
                        if ($check_stori['id'] > 0){
                            $db->query("UPDATE `stories_feed` SET url = '{$url}', add_date = '{$server_time}' WHERE user_id = '{$user_id}'");
                        }else{
                            $db->query("INSERT INTO `stories_feed` SET user_id = '{$user_id}', url = '{$url}', add_date = '{$server_time}'");
                        }

                        //$dbid = $db->insert_id();


                        echo $config['home_url'].'uploads/users/'.$user_id.'/'.$image_rename.$res_type;

                    } else
                        echo 'bad';//BAD_MOVE
                } else
                    echo 'big_size';//BIG_SIZE
            } else
                echo 'bad_format';//BAD_FORMAT
        }
    }

    /**
     * show
     *
     * @return int
     */
    public function show(): int
    {
        $db = $this->db();
//        $user_info = $params['user']['user_info'];
        $user_id = $user_info['user_id'];

        $request = (Request::getRequest()->getGlobal());

        if (isset($request['user'])){
            $user_id = $request['user'];
        }

        Tools::NoAjaxRedirect('/news/');

        $tpl->load_template('stories/show.tpl');

        $stories_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `stories` WHERE user_id = '{$user_id}'");
        echo $stories_count['cnt'];
        if ($stories_count['cnt'] == 0){
            $db->query("DELETE FROM `stories_feed` WHERE user_id = '{$user_id}'");
        }
        $num = 0;
        $progress_full = "";
        for ($i = 0; $i < $stories_count['cnt']; $i++) {
            if ($num === $i) {
                $progress_full .= "<div class=\"l9j\"  id=\"sProgress\"><div class=\"cxb\"  id=\"1sBar\" style=\"transition-duration: 5s; width: 1%;\"></div></div>  ";
            } else
                $progress_full .= "<div class=\"l9j\"><div class=\"cxb\" style=\"transition-duration: 0.1s; width: 100%;\"></div></div>  ";
        }

        //$stories = $db->super_query("SELECT * FROM `stories` WHERE user_id = '{$user_id}' ORDER by `add_date` ");
        $stories = $db->super_query("SELECT * FROM `stories` WHERE user_id = '{$user_id}' ORDER by `add_date` DESC LIMIT {$num}, 1 ");

        //Удаляем историю спустя сутки
//        $server_time = \Sura\Time\Date::time();
//        $online_time = $server_time - 86400;//сутки
//        if ($stories['add_date'] <= $online_time){
//            $db->query("DELETE FROM `stories` WHERE id = '{$stories['id']}'");
//            if ($stories_count['cnt'] == 1){
//                $db->query("DELETE FROM `stories_feed` WHERE user_id = '{$user_id}'");
//            }
//        }

        $stories_url = str_replace('stories/', 'stories/o_', $stories['url']);

        $tpl->set('{progresss}', "<div class=\"kv0 ss_m\">".$progress_full."</div>");
        $tpl->set('{s_url}', $stories_url);
        $tpl->set('{user_id}', $user_id);

        //$tpl->set('{langs}', $langs);

        return view('info.info', $params);
    }

    /**
     * show next
     *
     * @return int
     */
    public function show_next(): int
    {
        $db = $this->db();
//        $user_info = $params['user']['user_info'];
        //$user_id = $user_info['user_id'];

        $path = explode('/', $_SERVER['REQUEST_URI']);
        $user_id = $path[3];

        $num = $path[4];
        //$num--;
        if ($num <= 0) $num = 0;
        Tools::NoAjaxQuery('/news/');

        //$tpl->load_template('stories/show.tpl');

        $stories_count = $db->super_query("SELECT COUNT(*) AS cnt FROM `stories`  WHERE user_id = '{$user_id}'");

        $progress_full = "";
        for ($i = 0; $i < $stories_count['cnt']; $i++){
            if ($num == $i)
                $progress_full .= "<div class=\"l9j\"  id=\"sProgress\"><div class=\"cxb\"  id=\"1sBar\" style=\"transition-duration: 5s; width: 1%;\"></div></div>";
            else
                $progress_full .= "<div class=\"l9j\"><div class=\"cxb\" style=\"width: 100%;\"></div></div>";

            $stories = $db->super_query("SELECT * FROM `stories` WHERE user_id = '{$user_id}' ORDER by `add_date` DESC LIMIT {$num}, 1 ");
            $stories_url = str_replace('stories/', 'stories/o_', $stories['url']);

            //Удаляем историю спустя сутки
            $server_time = \Sura\Time\Date::time();
            $online_time = $server_time - 86400;//сутки
            if ($stories['add_date'] <= $online_time){
                $db->query("DELETE FROM `stories` WHERE id = '{$stories['id']}'");

                $config = Settings::load();
                $stories_file = str_replace($config['home_url'], '', $stories['url']);
                $stories_file2 = str_replace($config['home_url'], '', $stories_url);


                //Директория удаления
                $del_dir = __DIR__.'/../../public/';

                //Удаление фотки с сервера
                if (file_exists($del_dir.$stories_file))
                unlink($del_dir.$stories_file);
                if (file_exists($del_dir.$stories_file2))
                    unlink($del_dir.$stories_file2);


                if ($stories_count['cnt'] == 1){
                    $db->query("DELETE FROM `stories_feed` WHERE user_id = '{$user_id}'");
                }
            }

        }


        if (isset($stories['url'])){
            echo "
        <link media=\"screen\" href=\"/style/style.css\" type=\"text/css\" rel=\"stylesheet\">
        <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css\" integrity=\"sha384-r4NyP46KrjDleawBgD5tp8Y7UzmLA05oM1iAEQ17CSuDqnUK2+k9luXQOfXJCJ4I\" crossorigin=\"anonymous\">
        
        <div class=\"kv0 ss_m\">".$progress_full."</div>
        <div class='row '>
            <div class='col-1 m-auto'>
                <div  class='text-center' onclick=\"prevstori()\">
                    <svg width=\"3em\" height=\"3em\" viewBox=\"0 0 16 16\" class=\"bi bi-arrow-left\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\">
                  <path fill-rule=\"evenodd\" d=\"M5.854 4.646a.5.5 0 0 1 0 .708L3.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0z\"/>
                  <path fill-rule=\"evenodd\" d=\"M2.5 8a.5.5 0 0 1 .5-.5h10.5a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z\"/>
                    </svg>
                </div>
            
            </div>
            <div class='col-10'>
                                    <div class=\"p-2\">
                                    <svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-eye\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\">
                                        <path fill-rule=\"evenodd\" d=\"M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.134 13.134 0 0 0 1.66 2.043C4.12 11.332 5.88 12.5 8 12.5c2.12 0 3.879-1.168 5.168-2.457A13.134 13.134 0 0 0 14.828 8a13.133 13.133 0 0 0-1.66-2.043C11.879 4.668 10.119 3.5 8 3.5c-2.12 0-3.879 1.168-5.168 2.457A13.133 13.133 0 0 0 1.172 8z\"/>
                                        <path fill-rule=\"evenodd\" d=\"M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z\"/>
                                    </svg> 0
                                </div>
                                <img class=\"card-img-top\" src=\"".$stories_url."\" alt=\"\">
            </div>
            <div class='col-1 m-auto'>
                <div class='text-center' onclick=\"nextstori()\">
                    <svg width=\"3em\" height=\"3em\" viewBox=\"0 0 16 16\" class=\"bi bi-arrow-right\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\">
                      <path fill-rule=\"evenodd\" d=\"M10.146 4.646a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L12.793 8l-2.647-2.646a.5.5 0 0 1 0-.708z\"/>
                      <path fill-rule=\"evenodd\" d=\"M2 8a.5.5 0 0 1 .5-.5H13a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 8z\"/>
                    </svg>
                </div >
        
            </div >
        
        </div>
        
                        ";
        }else{
            echo 'exit';
        }

        return view('info.info', $params);
    }

}