<?php
/**
 * Josh - A PHP Framework.
 *
 * @author   Semen Alekseev <semyon492@ya.ru>
 */

$start = microtime(true);
$mem_start = memory_get_usage();

ini_set("allow_url_fopen", true);

require __DIR__.'/../vendor/autoload.php';

require __DIR__ . '/../app/bootstrap.php';

$request_method = $_SERVER['REQUEST_METHOD'];


 if($request_method !== 'POST' AND !isset($_POST['ajax']) ) {
     $memory = memory_get_usage() - $mem_start;
     $time = microtime(true) - $start;
     $i = 0;
     while (floor($memory / 1024) > 0) {
         $i++;
         $memory /= 1024;
     }
     $name = array('байт', 'КБ', 'МБ');
     $memory = round($memory, 2) . ' ' . $name[$i];
     echo '<script type="text/javascript">window.onload = function() {
    console.log(\'Скрипт выполнялся '.$time.' сек.('.$memory.')\')
 };</script>';
 }
?>