<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class FileAsset extends AssetBundle
{

    public $basePath = '@noderoot';
    public $baseUrl = '@node';

    public $css = [
        'css/file.css',
    ];

    public $js = [
        'js/fileClient.js',
        'js/file.js',
    ];

    public $depends = [
        'frontend\assets\AppAsset',
    ];

}