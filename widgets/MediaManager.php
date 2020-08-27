<?php

namespace deadmantfa\mm\widgets;

use yii\base\Widget;
use yii\bootstrap4\Html;
use yii\helpers\Json;
use yii\web\View;

class MediaManager extends Widget
{

    /**
     * MM options
     * @var array
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo Html::tag('div', '', ['id' => $this->getId()]);
        $this->registerClientScript();
    }

    /**
     * Register js
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        MediaManagerAsset::register($view);

        $options = $this->clientOptions;
        $id = $this->getId();
        $options['el'] = "#$id";
        $options = Json::encode($options);
        $view->registerJs("new MM($options);", View::POS_END);
    }

}
