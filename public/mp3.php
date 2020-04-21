<?php 

require_once __DIR__ . '/../vendor/autoload.php';

// $getID3 = new getID3;
// $ThisFileInfo = $getID3->analyze(__DIR__.'/uploads/audio_tmp/b9d5da4129899e6.mp3');

// var_dump($ThisFileInfo);

// echo '<br><br>';

// $ThisFileInfo2 = $getID3->analyze(__DIR__.'/uploads/audio_tmp/99fd4b51d692638.mp3');

// var_dump($ThisFileInfo2);


$tagger = new \duncan3dc\MetaAudio\Tagger;
$tagger->addDefaultModules();

$mp3 = $tagger->open(__DIR__.'/uploads/audio_tmp/99fd4b51d692638.mp3');

echo "Artist: {$mp3->getArtist()}\r\n<br>";
echo "Album: {$mp3->getAlbum()}\n<br>";
echo "Year: {$mp3->getYear()}\n<br>";
echo "Track No: {$mp3->getTrackNumber()}\n<br>";
echo "Title: {$mp3->getTitle()}\n<br>";

?>