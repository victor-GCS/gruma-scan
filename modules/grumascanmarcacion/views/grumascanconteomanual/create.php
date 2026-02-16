<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteomanual $model */

$this->title = 'Create Grumascanconteomanual';
$this->params['breadcrumbs'][] = ['label' => 'Grumascanconteomanuals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteomanual-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
