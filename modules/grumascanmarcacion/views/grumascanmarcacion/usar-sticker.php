<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Bodegas;
use kartik\select2\Select2;

/** @var $this yii\web\View */
/** @var $model app\models\forms\GrumascanMarcacionUseForm */

$this->title = 'Asignar marcación';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="grumascan-usar-sticker">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'enableAjaxValidation' => true,
        'validationUrl' => ['validar-usar-sticker'],
        // Opcional: para que valide también al cambiar (escáner suele disparar change)
        'validateOnChange' => true,
        'validateOnBlur' => true,
    ]); ?>


    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'codigo')->textInput([
                'autofocus' => true,
                'placeholder' => 'Escanee el sticker...',
            ]) ?>
        </div>

        <div class="col-md-6">
            <?=
            $form->field($model, 'idbodega')->widget(Select2::classname(), [
                'data' => Bodegas::getListaData(),
                // 'value' => $model->idbodega, // Usa lo que ya venga cargado desde el modelo
                'options' => [
                    'placeholder' => 'Seleccionar bodega...',
                    'multiple' => false,
                    'required' => true
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);

            ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true, 'required' => true]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true, 'required' => true]) ?>
        </div>
    </div>

    <div class="form-group" style="margin-top:10px;">
        <?= Html::submitButton('Asignar', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>