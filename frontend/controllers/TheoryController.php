<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;

class TheoryController extends Controller
{

    public function actionRabbitmq()
    {
        return $this->render('rabbitmq');
    }

    public function actionRabbitmqLinks()
    {
        return $this->render('rabbitmq-links');
    }

    public function actionDaemonLinks()
    {
        return $this->render('daemon-links');
    }

    public function actionPhpDaemon()
    {
        return $this->render('php-daemon');
    }

    public function actionStomp()
    {
        return $this->render('stomp');
    }

    public function actionStompLinks()
    {
        return $this->render('stomp-links');
    }

    public function actionSockJsClient()
    {
        return $this->render('sock-js-client');
    }

    public function actionSockJsClientLinks()
    {
        return $this->render('sock-js-client-links');
    }

    public function actionReactPhp()
    {
        return $this->render('react-php');
    }

    public function actionReactPhpLinks()
    {
        return $this->render('react-php-links');
    }

}