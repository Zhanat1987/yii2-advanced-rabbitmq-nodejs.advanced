<?php

namespace console\controllers;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class ChatController extends \yii\console\Controller
{

    public function actionIndex($name, $message)
    {
        // Create a connection with RabbitMQ server.
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // Create and publish the message to the exchange.
        $data = array(
            'type' => 'chat',
            'data' => array(
                'name' => $name,
                'message' => $message,
                'dateTime' => date('d/m/Y H:i:s'),
            )
        );
        $message = new AMQPMessage(json_encode($data));
        $channel->basic_publish($message, 'chats');

        // Close connection.
        $channel->close();
        $connection->close();
    }

}