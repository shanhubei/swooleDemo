<?php 

require dirname(__DIR__) . '/vendor/autoload.php';

use Shanhubei\Swoole\Chat;

$opt = [
    'daemonize' => false
];
$ws = new Chat($opt);
$ws->start();
