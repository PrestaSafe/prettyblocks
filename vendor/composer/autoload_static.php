<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit91bef8b4cf2575be79bd0ace2cf017b1
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
        ),
        'P' => 
        array (
            'PrestaSafe\\PrettyBlocks\\Interface\\' => 34,
            'PrestaSafe\\PrettyBlocks\\Fields\\' => 31,
            'PrestaSafe\\PrettyBlocks\\Core\\' => 29,
            'PrestaSafe\\PrettyBlocks\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'PrestaSafe\\PrettyBlocks\\Interface\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/prettyblocks/interface',
        ),
        'PrestaSafe\\PrettyBlocks\\Fields\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/prettyblocks/fields',
        ),
        'PrestaSafe\\PrettyBlocks\\Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes/prettyblocks/core',
        ),
        'PrestaSafe\\PrettyBlocks\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'BlockPresenter' => __DIR__ . '/../..' . '/classes/BlockPresenter.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FieldFormatter' => __DIR__ . '/../..' . '/classes/FieldFormatter.php',
        'FieldUpdator' => __DIR__ . '/../..' . '/classes/FieldUpdator.php',
        'HelperBuilder' => __DIR__ . '/../..' . '/classes/HelperBuilder.php',
        'PrestaSafe\\PrettyBlock\\Interface\\FieldInterface' => __DIR__ . '/../..' . '/classes/prettyblocks/interface/FieldInterface.php',
        'PrestaSafe\\PrettyBlocks\\Fields\\FileUpload' => __DIR__ . '/../..' . '/classes/prettyblocks/fields/FileUpload.php',
        'PrestaSafe\\PrettyBlocks\\PrettyBlock\\Core\\FieldCore' => __DIR__ . '/../..' . '/classes/prettyblocks/core/FieldCore.php',
        'PrettyBlocksCompiler' => __DIR__ . '/../..' . '/classes/PrettyBlockCompiler.php',
        'PrettyBlocksModel' => __DIR__ . '/../..' . '/classes/PrettyBlocksModel.php',
        'StateFormatter' => __DIR__ . '/../..' . '/classes/StateFormatter.php',
        'TplSettings' => __DIR__ . '/../..' . '/classes/TplSettings.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit91bef8b4cf2575be79bd0ace2cf017b1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit91bef8b4cf2575be79bd0ace2cf017b1::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit91bef8b4cf2575be79bd0ace2cf017b1::$classMap;

        }, null, ClassLoader::class);
    }
}
