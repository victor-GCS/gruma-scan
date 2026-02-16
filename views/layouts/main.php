<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use kartik\icons\Icon;

/**
 * Assets base
 */
AppAsset::register($this);
\yii\bootstrap5\BootstrapAsset::register($this);
\yii\bootstrap5\BootstrapPluginAsset::register($this);
Icon::map($this);

/**
 * FIX DEFINITIVO PARA COLLAPSE EN MÓVIL
 * Evita que CSS externos (AdminLTE / site.css / otros)
 * vuelvan a mostrar el menú automáticamente
 */
$this->registerCss(<<<CSS
/* MOBILE: ocultar SIEMPRE cuando no esté .show */
.navbar-collapse.collapse:not(.show) {
    display: none !important;
}

/* MOBILE: mostrar solo cuando Bootstrap agrega .show */
.navbar-collapse.collapse.show {
    display: block !important;
}

/* DESKTOP (md+): navbar-expand-md debe verse siempre */
@media (min-width: 768px) {
    .navbar-expand-md .navbar-collapse.collapse {
        display: flex !important;
    }
}

/* Separación horizontal del menú colapsado en móvil */
@media (max-width: 767.98px) {
    .navbar-collapse.collapse.show {
        padding-left: 16px;
        padding-right: 16px;
    }

    .navbar-nav {
        width: 100%;
    }

    .navbar-nav .nav-item {
        padding-left: 8px;
    }

    .navbar-nav .nav-link {
        padding-left: 8px;
    }
}
CSS);

/**
 * Meta / CSRF
 */
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header id="header">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl'   => Yii::$app->homeUrl,
            'options'    => [
                'class' => 'navbar-dark bg-dark fixed-top navbar-expand-md',
            ],
        ]);

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav ms-auto'],
            'items' => [
                ['label' => 'Home',   'url' => ['/site/index']],
                ['label' => 'Marcar', 'url' => ['/grumascanmarcacion/grumascanmarcacion/usar-sticker']],
                ['label' => 'Scan',   'url' => ['/grumascanmarcacion/grumascanconteo/index']],
                ['label' => 'Auditar', 'url' => ['/grumascanmarcacion/grumascanconteomanual/create']],

                Yii::$app->user->isGuest
                    ? ['label' => 'Login', 'url' => ['/site/login']]
                    : '<li class="nav-item">'
                    . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                    . Html::submitButton(
                        'Logout (' . Html::encode(Yii::$app->user->identity->username) . ')',
                        ['class' => 'nav-link btn btn-link p-0']
                    )
                    . Html::endForm()
                    . '</li>',
            ],
        ]);

        NavBar::end();
        ?>
    </header>

    <main id="main" class="flex-shrink-0" role="main" style="padding-top:70px;">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>

            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer id="footer" class="mt-auto py-3 bg-light">
        <div class="container">
            <div class="row text-muted">
                <div class="col-md-6 text-center text-md-start">
                    &copy; Victor <?= date('Y') ?>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <?= Yii::powered() ?>
                </div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>