<?php

namespace deadmantfa\mm\widgets;

use yii\bootstrap4\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\InputWidget;

class MediaManagerInputModal extends InputWidget
{

    /**
     * @var string
     */
    public $modalTitle = 'Media Manager';

    /**
     * @var string
     */
    public $inputId;

    /**
     * @var array
     */
    public $inputOptions = ['class' => 'form-control'];

    /**
     * @var string
     */
    public $buttonLabel = 'Browse';

    /**
     * @var array
     */
    public $buttonOptions = ['class' => 'btn btn-primary'];

    /**
     * MM options
     * @var array
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->hasModel()) {
            $this->inputId = Html::getInputId($this->model, $this->attribute);
        } else {
            $this->inputId = $this->getId() . '-input';
            $this->inputOptions = array_merge($this->inputOptions, [
                'id' => $this->inputId,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->renderInputGroup();
        echo $this->renderModal();
        $this->registerClientScript();
    }

    /**
     * @return string
     */
    public function renderInputGroup()
    {
        $buttonIcon = '<i class="fa fa-fw fa-folder-open" aria-hidden="true"></i>';
        $buttonLabel = $buttonIcon . ' ' . $this->buttonLabel;
        $button = Html::button($buttonLabel, array_merge($this->buttonOptions, [
            'data-toggle' => 'modal',
            'data-target' => '#' . $this->getModalId(),
        ]));
        $button = Html::tag('span', $button, ['class' => 'input-group-btn']);

        $input = $this->renderInput();
        return Html::tag('div', $input . $button, ['class' => 'input-group']);
    }

    /**
     * @return string
     */
    public function getModalId()
    {
        return $this->getId() . '-modal';
    }

    /**
     * @return string
     */
    public function renderInput()
    {
        $input = '';
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
        } else {
            $input = Html::textInput($this->name, $this->value, $this->inputOptions);
        }
        return $input;
    }

    /**
     * @return string
     */
    public function renderModal()
    {
        return <<<HTML
            <div class="modal fade" id="{$this->getModalId()}" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{$this->modalTitle}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="{$this->getId()}"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
HTML;
    }

    /**
     * Register js
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        MediaManagerAsset::register($view);

        $options = array_merge($this->clientOptions, [
            'el' => '#' . $this->getId(),
            'input' => [
                'el' => '#' . $this->inputId,
                'multiple' => false,
            ],
            'onSelect' => new JsExpression("function(e) { $('#{$this->getModalId()}').modal('hide'); }"),
        ]);

        $varName = str_replace('-', '_', $this->getId());
        $options = Json::encode($options);
        $js = <<<JS
            var {$varName};
            $('#{$this->getModalId()}')
                .on('show.bs.modal', function (e) {
                    {$varName} = new MM({$options})
                }).on('hide.bs.modal', function (e) {
                    {$varName}.destroy();
                });
JS;
        $view->registerJs($js, View::POS_END);
    }

}
