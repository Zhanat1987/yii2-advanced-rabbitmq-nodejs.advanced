<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class SockJSFileAsset extends AssetBundle
{

    public $basePath = '@sockjsroot';
    public $baseUrl = '@sockjs';

    public $css = [
        'css/file.css',
    ];

    public $js = [
        'js/sockjs-0.3.min.js',
        'js/stomp.js',
        'js/file.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}