<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9cd777d3b3c47f57d8cd71255496277e
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Shanhubei\\Swoole\\' => 17,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Shanhubei\\Swoole\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/app',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9cd777d3b3c47f57d8cd71255496277e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9cd777d3b3c47f57d8cd71255496277e::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}