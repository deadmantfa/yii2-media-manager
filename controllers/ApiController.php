<?php

namespace deadmantfa\mm\controllers;

use deadmantfa\mm\models\UploadForm;
use Yii;
use yii\base\Model;
use yii\filters\ContentNegotiator;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 */
class ApiController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [
            'cn' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
        ];

        $corsOptions = $this->module->getCorsOptions();
        if ($corsOptions) {
            $behaviors['cors'] = $corsOptions;
        }

        return $behaviors;
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionList($path = '', $recursive = false)
    {
        if (Yii::$app->request->method === 'OPTIONS') {
            //Yii::$app->response->headers->set('Allow', 'GET');
            return true;
        }

        $fs = $this->module->fs;
        $path = $fs->normalizePath($path);
        if (!empty($path) && !$fs->has($path)) {
            throw new NotFoundHttpException();
        }

        return $fs->listContents($path, $recursive);
    }

    /**
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionUpload()
    {
        $request = Yii::$app->getRequest();
        if ($request->method === 'OPTIONS') {
            //Yii::$app->response->headers->set('Allow', 'POST');
            return true;
        }

        $model = new UploadForm();
        if ($request->isPost) {
            $model->path = $request->post('path');
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->upload()) {
                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);
                return true;
            }
            return $this->serializeModelErrors($model);
        }
        throw new BadRequestHttpException();
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        Yii::$app->getResponse()->setStatusCode(422, 'Data Validation Failed.');

        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * @param $path
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownload($path)
    {
        $fs = $this->module->fs;
        $path = $fs->normalizePath($path);

        if ($fs->has($path)) {
            $metas = $fs->getMetaData($path);
            if (is_array($metas) && isset($metas['type'])) {
                if ($metas['type'] === 'file' && $stream = $fs->readStream($path)) {
                    $response = Yii::$app->getResponse();
                    $attachmentName = preg_replace('#^.*/#', '', $path);
                    return $response->sendStreamAsFile($stream, $attachmentName);
                }
                throw new BadRequestHttpException('Invalid path.');
            }
        }

        throw new NotFoundHttpException('The file does not exists.');
    }

}
