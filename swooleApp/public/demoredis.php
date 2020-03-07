<?php
$r = new Redis();
$r->connect('127.0.0.1',6379);
$result = $r->keys("*");

$result2 = $r->get("mailerlist");

var_dump($result);

var_dump($result2);

