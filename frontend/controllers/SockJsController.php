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
 * Class BasicController
 * @package frontend\controllers
 *
 * реализация базового функционала
 * запуск и остановка в фоновом процессе
 * 2-х демонов (nodejs socket и php rabbitmq)
 */
class BasicController extends Controller
{

    public function actionIndex()
    {
        $cache = Yii::$app->cache;
        if (!$cache->get('basicIsStart')) {
            $nodeCommand = 'node ' . Yii::getAlias('@nodejs') .
                DIRECTORY_SEPARATOR . 'server.js';
            $nodeProcess = new Process($nodeCommand);
            $nodePid = $nodeProcess->getPid();
            Yii::$app->session->set('chatNodePid', $nodePid);
            $cache->set('basicIsStart', true, 1800);

            // Create a connection with RabbitMQ server.
            $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
            $channel = $connection->channel();

            // Create a fanout exchange.
            // A fanout exchange broadcasts to all known queues.
            $channel->exchange_declare('updates', 'fanout', false, false, false);

            // Close connection.
            $channel->close();
            $connection->close();
        }
        return $this->render('index');
    }

    public function actionStart()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            try {
                // Запуск демона и получение PID (предполагается,
                // что pid где-то сохраняется после запуска)
                // /usr/bin/php /h
                $command = PHP_BINDIR . '/php ' .
                    Yii::getAlias('@appRoot/yii') . ' basic/index';
                $process = new Process($command);
                $processId = $process->getPid();
                Yii::$app->session->set('processId', $processId);
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
                // Остановка демона
                $process2 = new Process();
                $process2->setPid(Yii::$app->session->get('processId2'));
                $stopped2 = $process2->stop(); // возвращает true или false

                // Остановка демона
                $process = new Process();
                $process->setPid(Yii::$app->session->get('processId'));
                $stopped = $process->stop(); // возвращает true или false

                Yii::$app->cache->delete('basicIsStart');

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

}