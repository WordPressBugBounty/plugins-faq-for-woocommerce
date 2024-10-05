<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit82953e47d3b6152d9cc7304c6a96190b
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'Optemiz\\PluginTracker\\' => 22,
        ),
        'A' => 
        array (
            'Appsero\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Optemiz\\PluginTracker\\' => 
        array (
            0 => __DIR__ . '/..' . '/optemiz/plugin-tracker/includes',
        ),
        'Appsero\\' => 
        array (
            0 => __DIR__ . '/..' . '/appsero/client/src',
        ),
    );

    public static $classMap = array (
        'Appsero\\Client' => __DIR__ . '/..' . '/appsero/client/src/Client.php',
        'Appsero\\Insights' => __DIR__ . '/..' . '/appsero/client/src/Insights.php',
        'Appsero\\License' => __DIR__ . '/..' . '/appsero/client/src/License.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Optemiz\\PluginTracker\\Insights' => __DIR__ . '/..' . '/optemiz/plugin-tracker/includes/Insights.php',
        'Optemiz\\PluginTracker\\JsonWebToken' => __DIR__ . '/..' . '/optemiz/plugin-tracker/includes/JsonWebToken.php',
        'Optemiz\\PluginTracker\\Tracker' => __DIR__ . '/..' . '/optemiz/plugin-tracker/includes/Tracker.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit82953e47d3b6152d9cc7304c6a96190b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit82953e47d3b6152d9cc7304c6a96190b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit82953e47d3b6152d9cc7304c6a96190b::$classMap;

        }, null, ClassLoader::class);
    }
}
