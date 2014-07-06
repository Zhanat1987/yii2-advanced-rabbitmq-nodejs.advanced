<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class BasicAsset extends AssetBundle
{

    public $basePath = '@noderoot';
    public $baseUrl = '@node';

    public $css = [
        'css/screen.css',
    ];

    public $js = [
        'js/client.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}