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
            'rules' => [
                '/chat/start/<name:\w+>/<message:\w+>' => '/chat/start',
                '<controller:\w+>/<action:(update|view|delete)>/<id:\d+>' =>
                    '<controller>/<action>',
            ],
        ],
        'consoleRunner' => [
            'class' => 'vova07\console\ConsoleRunner',
            'file' => '@appRoot/yii' // or an absolute path to console file
        ]
    ],
];
