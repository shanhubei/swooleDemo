<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Shanhubei\Swoole\Uploader;

$opt = [
    'daemonize' => false
];
$ws = new Uploader($opt);
$ws->start();
