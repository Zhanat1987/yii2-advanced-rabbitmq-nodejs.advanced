<?php

use yii\helpers\Html;
use frontend\assets\ChatAsset;

ChatAsset::register($this);
?>
<div class="row">
    <br />
    <?php echo Html::beginForm(); ?>
    <?php
    echo Html::input(
        'text',
        'username',
        $username,
        [
            'class' => 'form-control chatName',
            'placeholder' => $username ? : 'Имя пользователя',
            'username' => $username,
        ]
    );
    ?>
    <br />
    <?php
    echo Html::textarea(
        'message',
        '',
        [
            'class' => 'form-control chatMessage',
            'placeholder' => 'Сообщение',
        ]
    );
    ?>
    <br />
    <?php
    echo Html::button(
        'Отправить',
        [
            'class' => 'btn btn-success chatButton',
        ]
    );
    ?>
    <br />
    <br />
    <?php
    echo Html::button(
        'Остановить',
        [
            'class' => 'btn btn-danger chatStop',
        ]
    );
    ?>
    <?php echo Html::endForm(); ?>
    <br />
</div>
<div class="chatDiv">
    <p>
        Сообщения в чате:
    </p>
</div>
<div class="chat"></div>