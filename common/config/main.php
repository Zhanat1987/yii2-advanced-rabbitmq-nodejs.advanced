<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'consoleRunner' => [
            'class' => 'vova07\console\ConsoleRunner',
            'file' => '@appRoot/yii' // or an absolute path to console file
        ]
    ],
];
