<?php

use yii\widgets\ActiveForm;
use frontend\assets\SockJSFileAsset;

SockJSFileAsset::register($this);

$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);

if ($model->hasErrors()) { //it is necessary to see all the errors for all the files.
    echo '<pre>';
    print_r($model->getErrors());
    echo '</pre>';
}
?>
<?= $form->field($model, 'file[]')->fileInput(['multiple' => '']) ?>
    <button>Submit</button>
<?php ActiveForm::end(); ?>
<br />
<button type="button" class="btn btn-primary fileStart">
    Начать
</button>
<br /><br />
<button type="button" class="btn btn-danger fileStop">
    Остановить
</button>
<div class="fileDiv">
    <p>
        Загруженные изображения:
    </p>
</div>
<div class="files"></div>
<div class="success"></div>