<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteomanual $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="grumascanconteomanual-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idmarcacion')->textInput([
        'autofocus' => true,
        'maxlength' => true,
        'placeholder' => 'Escanee o digite el número del mueble (marcación)',
    ]) ?>

    <?= $form->field($model, 'unidades_manual')->input('number', [
        'min' => 0,
        'step' => 1,
        'placeholder' => 'Unidades contadas manualmente',
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar auditoría', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>