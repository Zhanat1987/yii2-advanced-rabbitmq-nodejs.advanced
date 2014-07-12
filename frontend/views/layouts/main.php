<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\widgets\Alert;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'My Company',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
            ]);
            $menuItems = [
                ['label' => 'Home', 'url' => ['/site/index']],
                ['label' => 'Contact', 'url' => ['/site/contact']],
                [
                    'label' => 'Node Js',
                    'items' => [
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'Node Js' . '</li>',
                        ['label' => 'Basic', 'url' => ['/basic/index']],
                        ['label' => 'Chat', 'url' => ['/chat/index']],
                        ['label' => 'Uploading Files', 'url' => ['/file/index']],
                    ]
                ],
                [
                    'label' => 'SockJs-Client',
                    'items' => [
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'SockJs-Client' . '</li>',
                        ['label' => 'Basic', 'url' => ['/sock-js/index']],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'Examples' . '</li>',
                        ['label' => 'Temp Queue', 'url' => ['/sock-js/temp-queue']],
                        ['label' => 'Echo', 'url' => ['/sock-js/echo']],
                        ['label' => 'Send', 'url' => ['/sock-js/send']],
                        ['label' => 'Send 2', 'url' => ['/sock-js/send2']],
                        ['label' => 'Uploading Files', 'url' => ['/sock-js/file']],
                    ]
                ],
                [
                    'label' => 'Теория',
                    'items' => [
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'RabbitMQ' . '</li>',
                        ['label' => 'Теория', 'url' => ['/theory/rabbitmq']],
                        ['label' => 'Ссылки', 'url' => ['/theory/rabbitmq-links']],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'Демон-процессы' . '</li>',
                        ['label' => 'Ссылки', 'url' => ['/theory/daemon-links']],
                        ['label' => 'phpDaemon', 'url' => ['/theory/php-daemon']],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">' . 'Stomp' . '</li>',
                        ['label' => 'Теория', 'url' => ['/theory/stomp']],
                        ['label' => 'Ссылки', 'url' => ['/theory/stomp-links']],
                    ]
                ],
            ];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
        ?>

        <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
