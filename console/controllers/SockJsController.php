<?php

namespace console\controllers;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use common\components\ImagesHelper;

class SockJsController extends \yii\console\Controller
{

    /**
     * @param $routingKey
     * @param $messageText
     *
     * php yii sock-js/emit
     * php yii sock-js/emit "anonymous.info routing Key" "Hello World! message Text"
     */
    public function actionEmit($routingKey = null, $messageText = null)
    {
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('topic_logs', 'topic', false, false, false);

//        $routing_key = $argv[1];
        $routing_key = $routingKey;
        if(empty($routing_key)) $routing_key = "anonymous.info";
//        $data = implode(' ', array_slice($argv, 2));
        $data = $messageText;
        if(empty($data)) $data = "Hello World!";

        $msg = new AMQPMessage($data);

        $channel->basic_publish($msg, 'topic_logs', $routing_key);

        echo " [x] Sent ",$routing_key,':',$data," \n";

        $channel->close();
        $connection->close();
    }

    /**
     * @param $bindingKeys
     *
     * php yii sock-js/receive
     * php yii sock-js/receive bindingKeys
     */
    public function actionReceive($bindingKeys = null)
    {
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('topic_logs', 'topic', false, false, false);

        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

//        $binding_keys = array_slice($argv, 1);
        $binding_keys = $bindingKeys;
        if( empty($binding_keys )) {
//            file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
            file_put_contents('php://stderr', "Usage: $bindingKeys [binding_key]\n");
            exit(1);
        }

        foreach($binding_keys as $binding_key) {
            $channel->queue_bind($queue_name, 'topic_logs', $binding_key);
        }

        echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";

        $callback = function($msg){
            echo ' [x] ',$msg->delivery_info['routing_key'], ':', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     *
     * To receive all the logs:
    php yii sock-js/receive "#"
    To receive all logs from the facility "kern":
    php yii sock-js/receive "kern.*"
    Or if you want to hear only about "critical" logs:
    php yii sock-js/receive "*.critical"
    You can create multiple bindings:
    php yii sock-js/receive "kern.*" "*.critical"
    And to emit a log with a routing key "kern.critical" type:
    php yii sock-js/emit "kern.critical" "A critical kernel error"
     */

    public function actionFile()
    {
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('images', 'direct', false, false, false);
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queue_name, 'images', 'images routing key');

        $channel2 = $connection->channel();
        $channel2->exchange_declare('files', 'fanout', false, false, false);

        $callback = function ($msg) use ($channel2) {
            $data = json_decode($msg->body);
            $width = ImagesHelper::width($data->source);
            $height = ImagesHelper::height($data->source);

            // создание миниатюры изображения
            $thumbWidth = 207; // ширина будущего изображения в px
            $thumbHeight = $thumbWidth * $height / $width; // высота будущего изображения в px
            if ($fixResizedImage = ImagesHelper::resize($data->source, $thumbWidth, $thumbHeight)) {
                // сохранение полученного изображения
                $widthThumb = ImagesHelper::save($fixResizedImage, $data->source, 'w_207_',
                    $data->extension);
            }

            // создание миниатюры изображения
            $thumbHeight = 207; // высота будущего изображения в px
            $thumbWidth = $thumbHeight * $width / $height; // ширина будущего изображения в px
            if ($fixResizedImage2 = ImagesHelper::resize($data->source, $thumbWidth, $thumbHeight)) {
                // сохранение полученного изображения
                $heightThumb = ImagesHelper::save($fixResizedImage2, $data->source, 'h_207_',
                    $data->extension);
            }
            $image = [
                'type' => 'file',
                'data' => [
                    'name' => $data->name,
                    'widthThumb' => DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR .
                        'w_207_' . $data->name . '.' . $data->extension,
                    'heightThumb' => DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR .
                        'h_207_' . $data->name . '.' . $data->extension,
                ]
            ];
            $msg = new AMQPMessage(json_encode($image));
            $channel2->basic_publish($msg, 'files');
        };
        /*
         * 4-й - false - по ум-ю (то есть все сообщ-я хранятся в очереди всегда,
         * даже после прочтения), если true - то сообщ-е уд-ся из очереди сразу после прочтения
         */
        /**/
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

}