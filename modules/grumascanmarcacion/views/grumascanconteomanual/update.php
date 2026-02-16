<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteomanual $model */

$this->title = 'Update Grumascanconteomanual: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Grumascanconteomanuals', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="grumascanconteomanual-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
