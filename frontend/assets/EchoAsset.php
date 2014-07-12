<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class EchoAsset extends AssetBundle
{

    public $basePath = '@sockjsroot';
    public $baseUrl = '@sockjs';

    public $css = [
        'css/echo.css',
    ];

    public $js = [
        'js/sockjs-0.3.min.js',
        'js/stomp.js',
        'js/echo.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}