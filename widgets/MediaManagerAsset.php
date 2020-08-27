<?php

namespace deadmantfa\yii2\mm\widgets;

use yii\web\AssetBundle;

class MediaManagerAsset extends AssetBundle
{

    public $sourcePath = '@vendor/deadmantfa/yii2-media-manager/assets/mm';
    public $css = [
        'mm.min.css',
    ];
    public $js = [
        'mm.min.js',
    ];
    public $depends = [
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];

}
