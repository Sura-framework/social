<?php


namespace App\Modules;


use Sura\Libs\Db;

class ConsoleController extends Module
{

    /**
     *
     */
    public function ConsoleMain(): void
    {
        print "Usage: php craft <options>\n\n";
        print "Options:\n";
        print " -all\t\t Info\n";
        print " -make:add-user\t\t -make:add-user <name> <lastname> <mail> <pass>\n";
        print " -video convert \t\t convert uploads video\n";
        print " -migrate \t\t create tables db\n";
        print " -d <num>\t Set  <num> (deprecated)\n";
    }

    /**
     * @return int
     */
    public function ConsoleVideo(): int
    {
        $db = Db::getDB();
        // print "Start convert\n";
        if ($_SERVER['argv']['2']=='convert') {
            $row = $db->super_query("SELECT id, video, type FROM `videos_decode` LIMIT 0, 1", true);

            $row_count = count($row);
            if ($row_count > 0) {
                foreach ($row as $key) {
                    $dir_file_name = str_replace('.'.$key['type'], '', $key['video']);
                    $video_convert = false;
                    if (file_exists($dir_file_name.'_240.mp4')) {
                        $db->query("DELETE FROM `videos_decode` WHERE video = '".$key['video']."'");
                        return _e( "End convert\nFinish\n");
                    }else{
                        $video_convert = true;
                    }


                    if ($key['type'] == 'mp4' AND $video_convert == true) {

                        //
                        //240
                        exec('/usr/bin/ffmpeg -y -i '.$key['video'].' -vcodec libx264 -vprofile baseline -preset slow -b:v 250k -maxrate 250k -bufsize 500k -vf scale=320:180 -threads 0 -ab 96k '.$dir_file_name.'_240.mp4');

                        // 720
                        exec('/usr/bin/ffmpeg -y -i '.$key['video'].' -vcodec libx264 -vprofile baseline -preset slow -b:v 1000k -maxrate 500k -bufsize 1000k -vf scale=854:480 -threads 0 -ab 150k '.$dir_file_name.'_720.mp4');


                        if (file_exists($dir_file_name.'_240.mp4')) {
                            $db->query("DELETE FROM `videos_decode` WHERE video = '".$key['video']."'");
                        }
                        // ffmpeg -i inputfile.avi -vcodec libx264 -vprofile high -preset slower -b:v 1000k -vf scale=-1:576 -threads 0 -acodec libvo_aacenc -ab 196k output.mp4

                        // exec('/usr/bin/ffmpeg -y -i '.$key['video'].' -vcodec libx264 -vprofile baseline -preset slow -b:v 250k -maxrate 250k -bufsize 500k -vf scale=320:240 -threads 0 -ab 96k '.$dir_file_name.'_240.mp4');


                        //480
                        //'ffmpeg -i input_file.avi -vcodec libx264 -vprofile high -preset slow -b:v 500k -maxrate 500k -bufsize 1000k -vf scale=-1:480 -threads 0 -acodec libvo_aacenc -b:a 128k output_file.mp4'

                        //480p video for iPads and tablets (480p at 400kbit/s in main profile):
                        //ffmpeg -i inputfile.avi -vcodec libx264 -vprofile main -preset slow -b:v 400k -maxrate 400k -bufsize 800k -vf scale=-1:480 -threads 0 -acodec libvo_aacenc -ab 128k output.mp4

                        return _e( "Convert Finish\n");
//                            die();
                    }
                }
            }else{
                print "Not found new videos\n";
//                    die();
            }

//                  die();
        }else{
            commandline_help();
//                  die();
        }
    }

    /**
     *
     */
    public function ConsoleMake(): void
    {
        $db = Db::getDB();

        $name = $_SERVER['argv']['2'];
        $lastname = $_SERVER['argv']['3'];
        $email = $_SERVER['argv']['4'];

        $pass = password_hash($_SERVER['argv']['5'], PASSWORD_DEFAULT);
        $_IP = '0.0.0.0';
        $hid = $pass . md5(md5($_IP));
        $time = time();

        $db->query("INSERT INTO `users` (`user_email`, `user_password`, `user_name`, `user_lastname`, `user_photo`, `user_wall_id`, `user_birthday`, `user_sex`, `user_day`, `user_month`, `user_year`, `user_country`, `user_city`, `user_reg_date`, `user_lastdate`, `user_group`, `user_hid`, `user_country_city_name`, `user_search_pref`, `user_xfields`, `xfields`, `user_xfields_all`, `user_albums_num`, `user_friends_demands`, `user_friends_num`, `user_last_visit`, `user_fave_num`, `user_pm_num`, `user_notes_num`, `user_subscriptions_num`, `user_videos_num`, `user_wall_num`, `user_status`, `user_privacy`, `user_blacklist_num`, `user_blacklist`, `user_sp`, `user_support`, `user_balance`, `user_lastupdate`, `user_gifts`, `user_public_num`, `user_audio`, `user_msg_type`, `user_delet`, `user_ban`, `user_ban_date`, `user_new_mark_photos`, `user_doc_num`, `user_logged_mobile`, `guests`, `user_cover`, `user_cover_pos`, `balance_rub`, `user_rating`, `invties_pub_num`, `notifications_list`, `user_text`) VALUES ('{$email}', '{$pass}', '{$name}', '{$lastname}', '', 0, '1970-1-1', '1', '1', '1', '1970', '1', '0', '{$time}', '', '0', '{$hid}', 'Россия|', '{$name} {$lastname}', '', '', '', 0, 0, 0, '1587841518', 0, 1, 0, 0, 0, 0, '', 'val_msg|1||val_wall1|1||val_wall2|1||val_wall3|1||val_info|1||', 0, '', '1|0', 0, 0, '1587840016', 0, 0, 0, 1, 0, 0, '', 0, 0, 0, 0, '', '', 0, 0, 0, '', '')");

    }

    /**
     *
     */
    public function ConsoleMigrate()
    {
        $db = Db::getDB();
        $tableSchema1 = include __DIR__.'/../../config/migrations/create.php';
        $tableSchema2 = include __DIR__.'/../../config/migrations/city.php';
        $tableSchema3 = include __DIR__.'/../../config/migrations/country.php';
        $tableSchema4 = include __DIR__.'/../../config/migrations/gifts_list.php';
        $tableSchema5 = include __DIR__.'/../../config/migrations/mail_tpl.php';
        $tableSchema6 = include __DIR__.'/../../config/migrations/alter.php';

        $tableSchema = array_merge($tableSchema1, $tableSchema2, $tableSchema3, $tableSchema4, $tableSchema5, $tableSchema6);

        foreach($tableSchema as $table)
            $db->query($table);

    }
}