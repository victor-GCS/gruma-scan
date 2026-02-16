<?php

use app\models\Grumascanconteomanual;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\GrumascanconteomanualSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Grumascanconteomanuals';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteomanual-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Grumascanconteomanual', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'idgrumascanconteo',
            'idmarcacion',
            'unidades_manual',
            'unidades_sistema',
            //'diferencia',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Grumascanconteomanual $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
