<?php

namespace console\controllers;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use common\components\ImagesHelper;

class FileController extends \yii\console\Controller
{

    public function actionIndex()
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