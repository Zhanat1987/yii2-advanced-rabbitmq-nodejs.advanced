<?php

Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('noderoot', dirname(dirname(__DIR__)) . '/frontend/web/node');
Yii::setAlias('node', 'http://' . $_SERVER["HTTP_HOST"] . '/node');
Yii::setAlias('appRoot', dirname(dirname(__DIR__)));
Yii::setAlias('nodejs', dirname(dirname(__DIR__)) . '/frontend/web/node/js');