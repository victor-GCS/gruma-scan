<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\GrumascanconteomanualSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanconteomanual-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idgrumascanconteo') ?>

    <?= $form->field($model, 'idmarcacion') ?>

    <?= $form->field($model, 'unidades_manual') ?>

    <?= $form->field($model, 'unidades_sistema') ?>

    <?php // echo $form->field($model, 'diferencia') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
