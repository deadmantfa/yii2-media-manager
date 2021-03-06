<?php

namespace deadmantfa\mm;

use deadmantfa\mm\components\FileSystem;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\Cors;

class Module extends \yii\base\Module
{

    public $controllerNamespace = 'deadmantfa\mm\controllers';

    /**
     * @var Custom filesystem
     */
    public $fs;

    /**
     * Filesystem component name
     * @var string
     */
    public $fsComponent = 'fs';

    /**
     * Directory separator
     * @var string
     */
    public $directorySeparator = '/';

    /**
     * api controller options
     * @var array
     */
    public $apiOptions = [
        'cors' => false,
    ];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws \yii\base\InvalidParamException
     */
    public function init()
    {
        parent::init();
        Yii::setAlias('@mm', __DIR__);

        if (!isset(Yii::$app->{$this->fsComponent})) {
            throw new InvalidConfigException('Incorrect Configuration');
        }

        $this->fs = new FileSystem([
            'fs' => Yii::$app->{$this->fsComponent},
            'directorySeparator' => $this->directorySeparator,
        ]);
    }

    /**
     * @return array|bool
     */
    public function getCorsOptions()
    {
        if (isset($this->apiOptions['cors'])) {
            if (is_array($this->apiOptions['cors'])) {
                return [
                    'class' => Cors::class,
                    'cors' => $this->apiOptions['cors'],
                ];
            }

            return $this->apiOptions['cors'] ? true : false;
        }
    }

}
