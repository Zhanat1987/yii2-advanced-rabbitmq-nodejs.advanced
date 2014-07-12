<?php

namespace frontend\controllers;

use Yii;
use yii\base\Exception;
use yii\web\Controller;
use common\components\Process;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use frontend\models\UploadForm;
use yii\web\UploadedFile;

class FileController extends Controller
{

    public function actionIndex()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {

            $files = UploadedFile::getInstances($model, 'file');
            $images = [];
            foreach ($files as $file) {

                $_model = new UploadForm();

                $_model->file = $file;

                if ($_model->validate()) {
                    $filePath = 'uploads' . DIRECTORY_SEPARATOR . $_model->file->baseName .
                        '.' . $_model->file->extension;
                    $_model->file->saveAs($filePath);
                    $images[] = [
                        'source' => Yii::getAlias('@app') . DIRECTORY_SEPARATOR .
                            'web' . DIRECTORY_SEPARATOR . $filePath,
                        'extension' => $_model->file->extension,
                        'name' => $_model->file->baseName,
                    ];
                } else {
                    foreach ($_model->getErrors('file') as $error) {
                        $model->addError('file', $error);
                    }
                }
            }

            if ($model->hasErrors('file')){
                $model->addError(
                    'file',
                    count($model->getErrors('file')) . ' of ' . count($files) .
                        ' files not uploaded'
                );
            } else if ($images) {
                $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
                $channel = $connection->channel();
                $channel->exchange_declare('images', 'direct', false, false, false);
                foreach ($images as $image) {
                    $msg = new AMQPMessage(json_encode($image), ['delivery_mode' => 2]);
                    $channel->basic_publish($msg, 'images', 'images routing key');
                }
                $channel->close();
                $connection->close();
            }

        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionStart()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $nodeCommand = 'node ' . Yii::getAlias('@nodejs') .
                    DIRECTORY_SEPARATOR . 'fileServer.js';
                $nodeProcess = new Process($nodeCommand);
                $nodePid = $nodeProcess->getPid();
                Yii::$app->session->set('fileNodePid', $nodePid);

                $fileCommand = PHP_BINDIR . '/php ' . Yii::getAlias('@appRoot/yii') . " file";
                $fileProcess = new Process($fileCommand);
                $filePid = $fileProcess->getPid();
                Yii::$app->session->set('filePid', $filePid);
                return [
                    'status' => 'ok',
                    'msg' => 'Все ништяк!!!',
                ];
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                ];
            }
        } else {
            throw new BadRequestHttpException(Yii::t('common', "Запрос не ajax'овский!!!"));
        }
    }

    public function actionStop()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $fileNodeProcess = new Process();
                $fileNodeProcess->setPid(Yii::$app->session->get('fileNodePid'));
                $fileNodeProcessStopped = $fileNodeProcess->stop();

                $fileProcess = new Process();
                $fileProcess->setPid(Yii::$app->session->get('filePid'));
                $fileProcessStopped = $fileProcess->stop();

                if ($fileNodeProcessStopped && $fileProcessStopped) {
                    return [
                        'status' => 'ok',
                        'msg' => 'Все ништяк!!!',
                    ];
                }
            } catch (Exception $e) {
                return [
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                ];
            }
            return [
                'status' => 'error',
                'msg' => 'Произошла ошибка!!!',
            ];
        } else {
            throw new BadRequestHttpException(Yii::t('common', "Запрос не ajax'овский!!!"));
        }
    }

}