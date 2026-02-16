<?php

namespace app\modules\grumascanmarcacion\controllers;

use app\models\Grumascanconteo;
use Yii;
use app\models\Grumascanconteomanual;
use app\models\search\GrumascanconteomanualSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GrumascanconteomanualController implements the CRUD actions for Grumascanconteomanual model.
 */
class GrumascanconteomanualController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Grumascanconteomanual models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanconteomanualSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanconteomanual model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Grumascanconteomanual model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Grumascanconteomanual();
        $model->loadDefaultValues();

        if ($this->request->isPost) {

            if (!$model->load($this->request->post())) {
                Yii::$app->session->setFlash('error', 'No se recibieron datos.');
                return $this->render('create', ['model' => $model]);
            }

            // Normaliza/valida mínimos
            $idMarcacion = $model->idmarcacion; // bigint (puede venir string)
            $unidadesManual = (int)$model->unidades_manual;

            if ($idMarcacion === null || $idMarcacion === '' || $unidadesManual < 0) {
                Yii::$app->session->setFlash('error', 'Marcación y unidades manuales son obligatorias.');
                return $this->render('create', ['model' => $model]);
            }

            // 1) Buscar conteo real para esa marcación (en tu caso: SOLO terminado)
            $conteoReal = Grumascanconteo::find()
                ->where(['idmarcacion' => $idMarcacion])
                ->andWhere(['in', 'idestado', [1]])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if (!$conteoReal) {
                Yii::$app->session->setFlash(
                    'error',
                    "La marcación {$idMarcacion} no tiene un conteo terminado. No se puede auditar."
                );
                return $this->render('create', ['model' => $model]);
            }

            // 2) Evitar duplicado (si ya existe auditoría)
            $exists = Grumascanconteomanual::find()
                ->where([
                    'idgrumascanconteo' => (int)$conteoReal->id,
                    'idmarcacion' => $idMarcacion,
                ])
                ->exists();

            if ($exists) {
                Yii::$app->session->setFlash(
                    'warning',
                    "Ya existe auditoría manual para la marcación {$idMarcacion} en el conteo #{$conteoReal->id}."
                );
                return $this->render('create', ['model' => $model]); // no sale
            }

            // 3) Calcular unidades sistema
            $unidadesSistema = (int)$conteoReal->totalunidades;
            $diferencia = (int)$unidadesManual - (int)$unidadesSistema;

            // 4) Si hay diferencia, NO guardar
            if ($diferencia !== 0) {
                Yii::$app->session->setFlash(
                    'error',
                    "NO se guardó. Diferencia detectada. Manual: {$unidadesManual} | Sistema: {$unidadesSistema} | Diferencia: {$diferencia}. Validar y, si aplica, volver a contar."
                );

                // (Opcional) Mostrar valores calculados en el form sin guardar:
                $model->unidades_sistema = $unidadesSistema;
                $model->diferencia = $diferencia;
                $model->idgrumascanconteo = (int)$conteoReal->id;

                return $this->render('create', ['model' => $model]);
            }

            // 5) Llenar campos que NO se piden en el form
            $model->idgrumascanconteo = (int)$conteoReal->id;
            $model->unidades_sistema  = $unidadesSistema;
            $model->diferencia        = 0;

            // 6) Guardar seguro
            if (!$model->save()) {
                Yii::$app->session->setFlash(
                    'error',
                    'No se pudo guardar: ' . json_encode($model->getFirstErrors())
                );
                return $this->render('create', ['model' => $model]);
            }

            Yii::$app->session->setFlash(
                'success',
                "Auditoría guardada OK. Manual: {$unidadesManual} = Sistema: {$unidadesSistema}."
            );

            // Quedarse en create y limpiar para siguiente registro
            $model = new Grumascanconteomanual();
            $model->loadDefaultValues();

            return $this->render('create', ['model' => $model]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }



    /**
     * Updates an existing Grumascanconteomanual model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Grumascanconteomanual model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Grumascanconteomanual model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanconteomanual the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanconteomanual::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
