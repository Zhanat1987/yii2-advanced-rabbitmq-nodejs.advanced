<?php

namespace console\controllers;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class BasicController extends \yii\console\Controller
{

    /**
     * запустить сервис RabbitMQ
     * запустить из папки C:\xampp\htdocs\yii2-advanced-rabbitmq-nodejs.advanced\frontend\web\node
     * команду node js/server.js в Git Bash
     * запустить из папки C:\xampp\htdocs\yii2-advanced-rabbitmq-nodejs.advanced
     * команду yii example/test
     * и обновить страницу http://yii2-advanced-rabbitmq-nodejs.frontend/
     * и запустится обновление через каждые 3 секунды страницы
     *
     * полезные ссылки:
     * http://makeomatic.ru/blog/2013/10/16/RabbitMQ/
     * http://saboteur.me/delayed-tasks-rabbitmq/
     * https://github.com/vanbosse/rabbitmq-demo
     * http://vanbosse.be/blog/detail/pub-sub-with-rabbitmq-and-websocket
     * http://rabbitmq.demo/
     * http://localhost:15672
     *
     * // установить пакеты:
     * zhanat@zhanat-530U4E-540U4E:~/sites/yii2-advanced-rabbitmq-nodejs.advanced/
     * frontend/web/node/js$ npm install rabbit.js
     * zhanat@zhanat-530U4E-540U4E:~/sites/yii2-advanced-rabbitmq-nodejs.advanced/
     * frontend/web/node/js$ npm install socket.io
     */
    public function actionIndex()
    {
        // Create a connection with RabbitMQ server.
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // Create and publish the message to the exchange.
        while (true) {
            $data = array(
                'type' => 'update',
                'data' => array(
                    'minutes' => rand(0, 60),
                    'seconds' => rand(0, 60)
                )
            );
            $message = new AMQPMessage(json_encode($data));
            $channel->basic_publish($message, 'updates');
            sleep(3);
        }

        // Close connection.
        $channel->close();
        $connection->close();
    }

}