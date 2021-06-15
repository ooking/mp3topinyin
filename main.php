<?php
require 'vendor/autoload.php';

/**
 * 中文转拼音 (utf8版,gbk转utf8也可用)
 * @param string $str utf8字符串
 * @param string $ret_format 返回格式 [all:全拼音|first:首字母|one:仅第一字符首字母]
 * @param string $placeholder 无法识别的字符占位符
 * @param string $separator 中文之间的分隔符
 * @param string $allow_chars 允许的非中文字符
 * @return string 拼音字符串
 function convert($str, $ret_format = 'all', $separator = ' ', $placeholder = '_', $allow_chars = '/[a-zA-Z\d ]/')
 */

// mp3 tag
$tagger = new \duncan3dc\MetaAudio\Tagger;
$tagger->addDefaultModules();

// 处理的目录
// @todo 通过命令行输入目录
$path = '请输入目录';

$totalFiles = 0;

$fileObjs = recursiveDirectoryIterator($path);
// $object is SplFileInfo object
foreach($fileObjs as $object){
    if($object->isDir()) continue;
    $fileFullPath = $object->getPathname();
    $totalFiles++;

    // echo $filePath . "\n";

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $object->getPathname());

    $filePath = $object->getPathInfo()->getPathname();
    $fileName = $object->getFilename();

    // 只处理mp3文件
    if(!strstr($mime, "audio/mpeg")) {
        echo $filePath . "\n";
        echo "It's not a mp3 file:" . $mime . "\n";
        continue;
    }

    $mp3 = $tagger->open($fileFullPath);

    $artist = $mp3->getArtist();
    $album = $mp3->getAlbum();
    $title = $mp3->getTitle();
    $artistFirst = $artist;

    $changeFlag = false;

    if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $artist) > 0) {
        // 简体转拼音
        $artist = Aw\PinYin::convert($artist);
        // 用于作为文件名前缀
        $artistFirst = Aw\PinYin::convert($mp3->getArtist(), 'first', '');
        $changeFlag = true;
    }
    
    if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $album) > 0) {
        $album = Aw\PinYin::convert($album);
        $changeFlag = true;
    }

    
    if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $title) > 0) {
        $title = Aw\PinYin::convert($title);
        $changeFlag = true;
    }

    if(!$changeFlag) continue;

    $newFileName = $artistFirst . '-' . str_replace(' ', '', $title) . '.mp3';

    echo "Artist: {$mp3->getArtist()} >> {$artist} >> {$artistFirst}\n";
    echo "Album: {$mp3->getAlbum()} >> {$album}\n";
    echo "Title: {$mp3->getTitle()} >> {$title}\n";
    
    $mp3->setAlbum($album);
    $mp3->setTitle($title);
    $mp3->setArtist($artist);
    $mp3->save();

    echo "{$fileName} >> {$newFileName}\n";
    echo $filePath . '/' . $fileName . "\n";
    echo $filePath . '/' . $newFileName . "\n";
    
    echo "------------------------------\n";

    rename($filePath . '/' . $fileName, $filePath . '/' . $newFileName);

}

function recursiveDirectoryIterator($path) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
        yield $file;
    }
}