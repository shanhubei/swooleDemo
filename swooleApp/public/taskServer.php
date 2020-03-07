<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Shanhubei\Swoole\Task;

$opt = [
    'daemonize' => false
];
$ser = new Task($opt);
$ser->start();