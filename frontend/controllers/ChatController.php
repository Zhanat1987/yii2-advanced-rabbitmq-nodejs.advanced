<?php

namespace frontend\controllers;

use Yii;
use yii\base\Exception;
use yii\web\Controller;
use common\components\Process;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Class ChatController
 * @package frontend\controllers
 *
 * реализация базового функционала
 * запуск и остановка в фоновом процессе
 * 2-х демонов (nodejs socket и php rabbitmq)
 */
class ChatController extends Controller
{

    public function actionIndex()
    {
        $cache = Yii::$app->cache;
        if (!$cache->get('chatIsStart')) {
            $chatNodeCommand = 'node ' . Yii::getAlias('@nodejs') .
                DIRECTORY_SEPARATOR . 'chatServer.js';
            $chatNodeProcess = new Process($chatNodeCommand);
            $chatNodePid = $chatNodeProcess->getPid();
            Yii::$app->session->set('chatNodePid', $chatNodePid);
            $cache->set('chatIsStart', true, 1800);

            // Create a connection with RabbitMQ server.
            $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
            $channel = $connection->channel();

            // Create a fanout exchange.
            // A fanout exchange broadcasts to all known queues.
            $channel->exchange_declare('chats', 'fanout', false, false, false);

            // Close connection.
            $channel->close();
            $connection->close();
        }
        return $this->render('index', [
            'username' => Yii::$app->user->isGuest ? '' :
                    Yii::$app->user->identity->username,
        ]);
    }

    public function actionStart()
    {
//        $this->enableCsrfValidation = false;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
//                $name = Yii::$app->request->post('name');
//                $message = Yii::$app->request->post('message');
//                var_dump(Yii::$app->request->post());
//                var_dump(Yii::$app->request->getBodyParams());
                $name = Yii::$app->request->getQueryParam('name');
                $message = Yii::$app->request->getQueryParam('message');
                if ($name && $message) {
//                    $chatCommand = PHP_BINDIR . '/php ' . Yii::getAlias('@appRoot/yii') .
//                        " chat/index --name={$name} --message={$message}";
                    $chatCommand = PHP_BINDIR . '/php ' . Yii::getAlias('@appRoot/yii') .
                        " chat/index {$name} {$message}";
                    $chatProcess = new Process($chatCommand);
                    $chatPid = $chatProcess->getPid();
                    Yii::$app->session->set('chatPid', $chatPid);
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

    public function actionStop()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                $chatNodeProcess = new Process();
                $chatNodeProcess->setPid(Yii::$app->session->get('chatNodePid'));
                $chatNodeProcessStopped = $chatNodeProcess->stop();

                $chatProcess = new Process();
                $chatProcess->setPid(Yii::$app->session->get('chatPid'));
                $chatProcessStopped = $chatProcess->stop();

                if ($chatNodeProcessStopped && $chatProcessStopped) {
                    Yii::$app->cache->delete('chatIsStart');
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