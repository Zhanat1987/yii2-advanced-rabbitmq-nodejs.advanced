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

class SockJsController extends Controller
{

    public function actionIndex()
    {

        return $this->render('index');
    }

    public function actionTempQueue()
    {
        return $this->render('temp-queue');
    }

    public function actionEcho()
    {
        return $this->render('echo');
    }

    public function actionSend()
    {
        $loop = \React\EventLoop\Factory::create();
        $factory = new \React\Stomp\Factory($loop);
        $client = $factory->createClient(array('vhost' => '/', 'login' => 'guest', 'passcode' => 'guest'));

        $client
            ->connect()
            ->then(function ($client) use ($loop) {
                $i = 0;

                $loop->addPeriodicTimer(1, function () use (&$i, $client, $loop) {
                    if ($i == 1) {
                        $client->send('/topic/test', "single message #$i");
                        ++$i;
                    } else {
                        $loop->stop();
                    }
                });
            }, function (\Exception $e) {
                echo sprintf("Could not connect: %s\n", $e->getMessage());
            });

        $loop->run();
        return Yii::$app->user->identity->id;
    }

    public function actionSend2()
    {
        set_time_limit(3);
        $loop = \React\EventLoop\Factory::create();
        $factory = new \React\Stomp\Factory($loop);
        $client = $factory->createClient(array('vhost' => '/', 'login' => 'guest', 'passcode' => 'guest'));

        $client
            ->connect()
            ->then(function ($client) use ($loop) {
                $client->subscribe('/topic/test', function ($frame) {
                    echo "Message received: {$frame->body}\n";
                });

                $loop->addPeriodicTimer(1, function () use ($client) {
                    $client->send('/topic/test', 'test message');
                });

            });

        $loop->run();

        return true;
    }

    public function actionFile()
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
                        'user' => Yii::$app->user->identity->id,
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

        return $this->render('file', [
            'model' => $model,
        ]);
    }

    public function actionFileStart()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $fileCommand = PHP_BINDIR . '/php ' . Yii::getAlias('@appRoot/yii') . "
                sock-js/file";
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

    public function actionFileStop()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {

                $fileProcess = new Process();
                $fileProcess->setPid(Yii::$app->session->get('filePid'));
                $fileProcessStopped = $fileProcess->stop();

                if ($fileProcessStopped) {
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