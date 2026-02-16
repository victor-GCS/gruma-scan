<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use kartik\icons\Icon;

$this->title = 'GRUMALOG Scan';
?>

<div class="site-index">

    <div class="text-center mt-5 mb-5">
        <h1 class="display-5">
            <?= Icon::show('barcode') ?>
            GRUMALOG Scan
        </h1>

        <p class="lead text-muted">
            Sistema de conteo operativo
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">
                        <?= Icon::show('fa-solid fa-barcode') ?>
                        Marcar Stikers
                    </h3>

                    <p class="card-text text-muted">
                        Agregar a un stiker bodega, seccion y ubicacion.
                    </p>

                    <?= Html::a(
                        Icon::show('play') . ' Ver ',
                        ['/grumascanmarcacion/grumascanmarcacion/usar-sticker'],
                        ['class' => 'btn btn-success btn-lg w-100']
                    ) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">
                        <?= Icon::show('clipboard-list') ?>
                        Conteo
                    </h3>

                    <p class="card-text text-muted">
                        Ver conteos de mercancía por ubicación.
                    </p>

                    <?= Html::a(
                        Icon::show('play') . ' Ver ',
                        ['/grumascanmarcacion/grumascanconteo/index'],
                        ['class' => 'btn btn-success btn-lg w-100']
                    ) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">
                        <?= Icon::show('fa-solid fa-bug') ?>
                        Conteo manual
                    </h3>

                    <p class="card-text text-muted">
                        Auditar conteo por marcacion.
                    </p>

                    <?= Html::a(
                        Icon::show('play') . ' Ver ',
                        ['/grumascanmarcacion/grumascanconteomanual/create'],
                        ['class' => 'btn btn-success btn-lg w-100']
                    ) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="card-title">
                        <?= Icon::show('user') ?>
                        Usuario
                    </h3>

                    <p class="card-text text-muted">
                        Sesión activa: <strong><?= Html::encode(Yii::$app->user->identity->username ?? '') ?></strong>
                    </p>

                    <?= Html::a(
                        Icon::show('sign-out-alt') . ' Cerrar sesión',
                        ['/site/logout'],
                        [
                            'class' => 'btn btn-outline-danger w-100',
                            'data-method' => 'post'
                        ]
                    ) ?>
                </div>
            </div>
        </div>

    </div>
</div>