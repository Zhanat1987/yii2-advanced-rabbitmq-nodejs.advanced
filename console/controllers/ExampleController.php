<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.07.14
 * Time: 19:28
 */

namespace console\controllers;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Yii;
use yii\helpers\Console;
use yii\log\Logger;

class ExampleController extends \yii\console\Controller
{
    // The command "yii example/create test" will call "actionCreate('test')"
    public function actionCreate($name) {}

    // The command "yii example/index city" will call "actionIndex('city', 'name')"
    // The command "yii example/index city id" will call "actionIndex('city', 'id')"
    public function actionIndex($category, $order = 'name') {}

    // The command "yii example/add test" will call "actionAdd(['test'])"
    // The command "yii example/add test1,test2" will call "actionAdd(['test1', 'test2'])"
    public function actionAdd(array $name) {}

    /**
     * https://github.com/yiisoft/yii2/issues/1324
     */
    public function actionRun()
    {
        Yii::info(1, 1);
        Yii::error('test', 'test');
        echo 12345;
    }

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
    public function actionTest()
    {
        // Create a connection with RabbitMQ server.
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // Create a fanout exchange.
        // A fanout exchange broadcasts to all known queues.
        $channel->exchange_declare('updates', 'fanout', false, false, false);

        $counter = 100;
        // Create and publish the message to the exchange.
//        while (--$counter)
        while (true)
        {
            $data = array(
                'type' => 'update',
                'data' => array(
                    'minutes' => rand(0, 60),
                    'seconds' => rand(0, 60)
                )
            );
            $message = new AMQPMessage(json_encode($data));
            $channel->basic_publish($message, 'updates');
            sleep(1);
        }

        // Close connection.
        $channel->close();
        $connection->close();
    }

}