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
$path = '/Volumes/EXDATA/mp3/eason';

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

    // 只处理mp3文件
    if(!strstr($mime, "audio/mpeg")) {
        echo "It's not a mp3 file:" . $mime . "\n";
        continue;
    }

    $filePath = $object->getPathInfo()->getPathname();
    $fileName = $object->getFilename();
    // echo $filePath . "\n";

    $mp3 = $tagger->open($fileFullPath);
    // 简体转拼音
    $artistPinyin = Aw\PinYin::convert($mp3->getArtist());
    // 用于作为文件名前缀
    $artistPinyins = Aw\PinYin::convert($mp3->getArtist(), 'first', '');
    $albumPinyin = Aw\PinYin::convert($mp3->getAlbum());
    $titlePinyin = Aw\PinYin::convert($mp3->getTitle());

    $artistPinyins = 'eason';
    $artistPinyin = $artistPinyins;

    $newFileName = $artistPinyins . '-' . str_replace(' ', '', $titlePinyin) . '.mp3';

    echo "Artist: {$mp3->getArtist()} >> {$artistPinyin} >> {$artistPinyins}\n";
    echo "Album: {$mp3->getAlbum()} >> {$albumPinyin}\n";
    echo "Title: {$mp3->getTitle()} >> {$titlePinyin}\n";

    $mp3->setAlbum($albumPinyin);
    $mp3->setTitle($titlePinyin);
    // $mp3->setArtist($artistPinyin);
    $mp3->setArtist('eason');
    $mp3->save();

    echo "{$fileName} >> {$newFileName}\n";
    echo $filePath . '/' . $fileName . "\n";
    echo $filePath . '/' . $newFileName . "\n";
    rename($filePath . '/' . $fileName, $filePath . '/' . $newFileName);

}

function recursiveDirectoryIterator($path) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
        yield $file;
    }
}

