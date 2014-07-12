<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class TempQueueAsset extends AssetBundle
{

    public $basePath = '@sockjsroot';
    public $baseUrl = '@sockjs';

    public $css = [
        'css/temp-queue.css',
    ];

    public $js = [
        'js/sockjs-0.3.min.js',
        'js/stomp.js',
        'js/temp-queue.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}