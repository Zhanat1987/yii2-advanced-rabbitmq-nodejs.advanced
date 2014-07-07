<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class ChatAsset extends AssetBundle
{

    public $basePath = '@noderoot';
    public $baseUrl = '@node';

    public $css = [
        'css/chat.css',
    ];

    public $js = [
        'js/chatClient.js',
        'js/chat.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}