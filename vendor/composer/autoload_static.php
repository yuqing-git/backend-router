<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitffb35591efeb54a63c86e334e362e587
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Br\\' => 3,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Br\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/yuqing/br',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitffb35591efeb54a63c86e334e362e587::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitffb35591efeb54a63c86e334e362e587::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
