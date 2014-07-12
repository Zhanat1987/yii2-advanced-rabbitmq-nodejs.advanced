<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class SockJsAsset extends AssetBundle
{

    public $basePath = '@sockjsroot';
    public $baseUrl = '@sockjs';

    public $css = [
        'css/basic.css',
    ];

    public $js = [
        'js/sockjs-0.3.min.js',
        'js/stomp.js',
//        'js/basic.js',
        'js/basic2.js',
//        'js/basic3.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}