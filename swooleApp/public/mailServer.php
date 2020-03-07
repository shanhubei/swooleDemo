<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use Shanhubei\Swoole\Mail;


$config = [
    'smtp_server' => 'smtp.163.com',
    'username' => 'chenzhao_635@163.com',
    'password' => 'VPRflBl4BmNORwdp',// SMTP 密码/口令
    'secure' => 'ssl', //Enable TLS encryption, `ssl` also accepted
    'port' => 465, // tcp邮件服务器端口
];
$server = new Mail($config);
$server->start();
