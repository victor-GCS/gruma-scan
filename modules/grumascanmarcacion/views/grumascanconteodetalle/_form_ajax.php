<?php

use app\widgets\Alert;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteodetalle $model */
/** @var app\models\Grumascanconteo $modelConteo */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\search\GrumascanconteodetalleSearch $searchModel */
/** @var int $totalUnidades */
/** @var int $totalItems */
/** @var string|null $ultimo_ean */

$baseUrl = Yii::$app->request->baseUrl;
$this->title = 'Conteo #' . $modelConteo->id;
?>

<div id="conteo-root" data-base-url="<?= Html::encode($baseUrl) ?>">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

        <div class="d-flex flex-row align-items-baseline flex-wrap gap-2">
            <h3 class="me-2 mb-0">Conteo #<?= Html::encode($modelConteo->id) ?></h3>
            <h3 class="me-2 mb-0">Marcación #<?= Html::encode($modelConteo->idmarcacion) ?></h3>
            <h3 class="mb-0"><?= Html::encode($modelConteo->usuario->username) ?></h3>
        </div>

        <div class="d-flex gap-2">
            <?= Html::button('Finalizar conteo', [
                'type' => 'button',
                'class' => 'btn btn-danger',
                'id' => 'btn-finalizar-conteo',
                'data-url' => Url::to(['finalizar-conteo', 'idgrumascanconteo' => $modelConteo->id]),
                'data-redirect' => Url::to(['//grumascanmarcacion/grumascanconteo/create']),

            ]) ?>
        </div>

        <div class="d-flex gap-2">
            <?= Html::button('Cambiar cantidad (admin)', [
                'type' => 'button',
                'class' => 'btn btn-outline-primary',
                'id' => 'btn-cambiar-cantidad',
                'data-validar-url' => Url::to(['validar-clave-admin']),
            ]) ?>
        </div>

    </div>



    <?php Pjax::begin(['id' => 'alert-pjax-container']); ?>
    <?= Alert::widget() ?>
    <?php Pjax::end(); ?>

    <?php $form = ActiveForm::begin(['id' => 'form-conteo']); ?>

    <div class="row">
        <div class="col-4">
            <?= $form->field($model, 'idgrumascanconteo')
                ->label('Id Bodega')
                ->textInput([
                    'disabled' => true,
                    'value' => $modelConteo->marcacion->bodega->codigo ?? 'no tiene asignado',
                ]) ?>

        </div>
        <div class="col-4">

            <?= $form->field($model, 'idgrumascanconteo')
                ->label('Ubicacion')
                ->textInput([
                    'disabled' => true,
                    'value' => $modelConteo->marcacion->ubicacion ?? 'no tiene asignado',
                ]) ?>

        </div>
        <div class="col-4">
            <?= $form->field($model, 'idgrumascanconteo')
                ->label('Seccion')
                ->textInput([
                    'disabled' => true,
                    'value' => $modelConteo->marcacion->seccion ?? 'no tiene asignado',
                ]) ?>
        </div>

        <div class="col-12">
            <?= Html::label('EAN', 'codigo_barras', ['class' => 'form-label']) ?>
            <?= Html::textInput('codigoBarras', '', [
                'id' => 'codigo_barras',
                'class' => 'form-control',
                'autofocus' => true,
                'maxlength' => 14,
                'minlength' => 13,
                'data-procesar-url' => Url::to(['procesar-formulario', 'idgrumascanconteo' => $modelConteo->id]),
            ]) ?>
        </div>
    </div>

    <div class="row mt-2 align-items-end d-none" id="ocultarInput">
        <div class="col-3">
            <?= Html::label('Cantidad', 'cantidad_entrada', ['class' => 'form-label']) ?>
            <?= Html::input('number', 'cantidadEntrada', 1, [
                'id' => 'cantidad_entrada',
                'disabled' => true,
                'class' => 'form-control',
                'min' => 1,
            ]) ?>
            <!-- <div class="form-text">Por defecto es 1. Solo admin puede habilitar cambio para 1 escaneo.</div> -->
        </div>

        <div class="col-3">
            <?= Html::label('Total unidades', 'total_unidades', ['class' => 'form-label']) ?>
            <?= Html::textInput('total_unidades', $totalUnidades, ['class' => 'form-control', 'id' => 'total_unidades', 'disabled' => true]) ?>
        </div>

        <div class="col-3">
            <?= Html::label('Total ítems', 'total_items', ['class' => 'form-label']) ?>
            <?= Html::textInput('total_items', $totalItems, ['class' => 'form-control', 'id' => 'total_items', 'disabled' => true]) ?>
        </div>

        <div class="col-3">
            <?= Html::label('Último EAN', 'ultimo_ean', ['class' => 'form-label']) ?>
            <?= Html::textInput('ultimo_ean', $ultimo_ean, ['class' => 'form-control', 'id' => 'ultimo_ean', 'disabled' => true]) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <hr>

    <?php Pjax::begin(['id' => 'pjax-container']); ?>

    <?= GridView::widget([
        'responsiveWrap' => false,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'tableOptions' => ['class' => 'table table-bordered table-striped'],

        // ✅ Toolbar superior
        'toolbar' => [
            [
                'content' =>
                Html::a('🗑 Borrar todo', ['borrar-todo', 'idgrumascanconteo' => $modelConteo->id], [
                    'class' => 'btn btn-danger',
                    'title' => 'Borrar todos los registros del conteo',
                    'data' => [
                        'confirm' => '¿Seguro que deseas borrar TODOS los registros de este conteo? Esta acción no se puede deshacer.',
                        'method' => 'post',     // ✅ Yii lo manda como POST nativo
                        'pjax' => '0',          // evita que intente pjax en este click
                    ],
                ]),
            ],
        ],

        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => false,
        ],


        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // ✅ Papelera por fila (estilo ejemplo: link con ícono, sin borde)
            [
                'header' => '',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width:40px; text-align:center;'],
                'value' => function ($m) use ($modelConteo) {

                    $item = (string)($m['item'] ?? '');
                    $idColor = (int)($m['idcolor'] ?? 0);
                    $idTalla = (int)($m['idtalla'] ?? 0);

                    $colorNombre = (string)($m['color_nombre'] ?? 'NA');
                    $tallaNombre = (string)($m['talla_nombre'] ?? 'NA');

                    $cantidad = (int)($m['cantidad'] ?? 0);

                    return Html::a(
                        '🗑',
                        'javascript:void(0);',
                        [
                            'title' => 'Eliminar unidades de este SKU',
                            'class' => 'text-danger fw-bold btn-eliminar-sku',
                            'style' => 'text-decoration:none; font-size:18px;',
                            'data-url' => Url::to(['eliminar-unidades-sku', 'idgrumascanconteo' => $modelConteo->id]),
                            'data-item' => $item,
                            'data-idcolor' => $idColor,
                            'data-idtalla' => $idTalla,
                            'data-color' => $colorNombre,
                            'data-talla' => $tallaNombre,
                            'data-max' => $cantidad,
                            'data-pjax' => '0',
                        ]
                    );
                }
            ],

            [
                'attribute' => 'item',
                'label' => 'Item',
                'value' => fn($m) => $m['item'] ?? '-',
            ],
            [
                'label' => 'EAN',
                'value' => fn($m) => $m['eans'] ?? '-',
            ],
            [
                'attribute' => 'color_nombre',
                'label' => 'Color',
                'value' => fn($m) => $m['color_nombre'] ?? 'NA',
            ],
            [
                'attribute' => 'talla_nombre',
                'label' => 'Talla',
                'value' => fn($m) => $m['talla_nombre'] ?? 'NA',
            ],
            [
                'attribute' => 'cantidad',
                'label' => 'Cantidad',
                'format' => ['decimal', 0],
                'pageSummary' => true,
                'value' => fn($m) => (int)($m['cantidad'] ?? 0),
            ],
            [
                'attribute' => 'total_unidades',
                'label' => 'Total Unidades',
                'format' => ['decimal', 0],
                'pageSummary' => true,
                'value' => fn($m) => (int)($m['total_unidades'] ?? 0),
            ],


        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<!-- Modal Admin -->
<div class="modal fade" id="modalClaveAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clave de administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="clave_admin_input">Ingrese la clave</label>
                <input type="password" id="clave_admin_input" class="form-control" autocomplete="off">
                <div class="form-text">Esto habilita la cantidad solo para el próximo escaneo.</div>
                <div class="text-danger mt-2 d-none" id="clave_admin_error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-validar-clave">Validar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar SKU -->
<div class="modal fade" id="modalEliminarSku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar unidades del SKU</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <div class="fw-semibold" id="eliminar_sku_titulo">SKU</div>
                    <div class="text-muted small" id="eliminar_sku_info"></div>
                </div>

                <label class="form-label" for="eliminar_sku_input">¿Selecciona la cantidad que deseas eliminar?</label>
                <input type="number" id="eliminar_sku_input" class="form-control" min="1" step="1" value="1">

                <div class="form-text">
                    Máximo disponible en este SKU: <span id="eliminar_sku_max">0</span>
                </div>

                <div class="text-danger mt-2 d-none" id="eliminar_sku_error"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar-sku">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalFinalizarConteo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar conteo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que deseas finalizar este conteo? Ya no podrás seguir escaneando.
                <div class="text-danger mt-2 d-none" id="finalizar_conteo_error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-finalizar">Sí, finalizar</button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/conteo.js',
    ['depends' => [\app\assets\AppAsset::class]]
);
?>