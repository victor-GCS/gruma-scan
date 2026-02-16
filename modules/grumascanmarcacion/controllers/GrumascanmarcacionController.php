<?php

namespace app\modules\grumascanmarcacion\controllers;

use app\models\Grumascanmarcacion;
use app\models\search\GrumascanmarcacionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;
// use appra;
use app\models\Impresoraspaxarbodega as impresora;
use app\models\forms\GrumascanMarcacionPrintForm;
use common\components\MarcacionStickerPrinter;
use app\models\forms\GrumascanMarcacionUseForm;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;


/**
 * GrumascanmarcacionController implements the CRUD actions for Grumascanmarcacion model.
 */
class GrumascanmarcacionController extends Controller
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
                        'validar-usar-sticker' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'], // solo logueados
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Grumascanmarcacion models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanmarcacionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanmarcacion model.
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
     * Creates a new Grumascanmarcacion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Grumascanmarcacion();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Grumascanmarcacion model.
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
     * Deletes an existing Grumascanmarcacion model.
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
     * Finds the Grumascanmarcacion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanmarcacion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanmarcacion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionIndeximprecion()
    {
        $form = new GrumascanMarcacionPrintForm();

        // Traer impresoras (ajusta campos: id/nombre/tipo/ip/puerto/recurso)
        $printers = Impresora::getListaData();

        return $this->render('indeximprecion', [
            'model' => $form,
            'printers' => $printers,
        ]);
    }

    public function actionPrint()
    {
        $form = new GrumascanMarcacionPrintForm();

        // Traer impresoras para re-render en caso de error
        $printers = Impresora::getListaData();

        if (!$form->load(Yii::$app->request->post()) || !$form->validate()) {
            Yii::$app->session->setFlash('error', 'Datos inválidos para impresión.');
            return $this->render('indeximprecion', [
                'model' => $form,
                'printers' => $printers,
            ]);
        }

        $printer = Impresora::findOne($form->printer_id);
        if (!$printer) {
            Yii::$app->session->setFlash('error', 'Impresora no encontrada.');
            return $this->redirect(['indeximprecion']);
        }

        $usuario = Yii::$app->user->identity->username ?? 'N/A';

        $db = Yii::$app->db;
        $ids = [];

        /** @var Transaction $tx */
        $tx = $db->beginTransaction();
        try {
            // Insertar N filas y capturar IDs
            for ($i = 0; $i < (int)$form->cantidad; $i++) {
                $m = new Grumascanmarcacion();
                $m->idbodega = $form->idbodega;
                $m->ubicacion = $form->ubicacion;
                $m->seccion = $form->seccion;

                if (!$m->save(false)) {
                    throw new \RuntimeException('No fue posible insertar marcación.');
                }
                $ids[] = (int)$m->id;
            }

            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error creando stickers: ' . $e->getMessage());
            return $this->redirect(['indeximprecion']);
        }

        // Construir payload ZPL/EPL
        $tipo = $printer->tipo; // 'ip' / 'recurso' / 'epl' en tu BD puede variar
        // Si en tu BD el tipo 'epl' va aparte, adapta lógica:
        $esEpl = ($printer->tipo === 'epl');

        $payload = MarcacionStickerPrinter::buildPayload($ids, $usuario, $esEpl ? 'epl' : 'zpl');

        // Enviar a impresora usando tu método (lo dejo aquí integrado simple)
        $config = [
            'tipo' => $printer->tipo,   // 'ip' o 'recurso' según tu tabla
            'ip' => $printer->ip ?? null,
            'puerto' => $printer->puerto ?? 9100,
            'recurso' => $printer->recurso ?? null,
        ];

        $resp = $this->enviarImpresora($payload, $config);

        if (($resp['status'] ?? 'error') === 'success') {
            Yii::$app->session->setFlash(
                'success',
                'Impresión enviada. Stickers: ' . count($ids) . ' | Impresora: ' . ($printer->nombre ?? $printer->bodega->nombre)
                    . ' | ip: '   . ($printer->ip)
            );
        } else {
            Yii::$app->session->setFlash(
                'error',
                'Error imprimiendo: ' . ($resp['message'] ?? 'Error desconocido')
            );
        }

        return $this->redirect(['indeximprecion']);
    }

    /**
     * Reutiliza tu implementación. Pégala tal cual y ajústala si hace falta.
     */
    private function enviarImpresora($zpl, $config)
    {
        $tipo = $config['tipo'];
        $ip = $config['ip'] ?? null;
        $puerto = $config['puerto'] ?? 9100;
        $recurso = $config['recurso'] ?? null;

        Yii::$app->session->removeAllFlashes();

        if ($tipo == 'ip') {
            $socket = @fsockopen($ip, $puerto, $errno, $errstr, 10);
            if (!$socket) {
                $msg = "No se pudo conectar a la impresora. Ip: $ip:$puerto Error: $errstr ($errno)";
                Yii::error($msg, __METHOD__);
                return ['status' => 'error', 'message' => $msg];
            }
            fwrite($socket, $zpl);
            fclose($socket);
            return ['status' => 'success', 'message' => "Impresión enviada correctamente a $ip:$puerto."];
        }

        if ($tipo === 'recurso') {
            $tempDir = Yii::getAlias('@app') . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $tempFile = $tempDir . '/zpl_' . uniqid() . '.tmp';
            file_put_contents($tempFile, $zpl);

            $command = sprintf(
                'print %s /D:%s "%s"',
                $ip,
                '\\\\' . str_replace('\\', '\\\\', ltrim($recurso, '\\')),
                $tempFile
            );

            exec($command, $output, $returnVar);
            @unlink($tempFile);

            if ($returnVar !== 0) {
                $msg = "Error al imprimir en $recurso: " . implode("\n", $output);
                Yii::error($msg, __METHOD__);
                return ['status' => 'error', 'message' => $msg];
            }
            return ['status' => 'success', 'message' => "Impresión enviada a $recurso, correctamente!"];
        }

        return ['status' => 'error', 'message' => 'Tipo de impresora no reconocido.'];
    }
    public function actionUsarSticker()
    {
        $form = new GrumascanMarcacionUseForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $model = $form->getMarcacionModel();
            if (!$model) {
                Yii::$app->session->setFlash('error', 'Sticker no existe.');
                return $this->refresh();
            }

            $model->idbodega  = $form->idbodega;
            $model->ubicacion = $form->ubicacion;
            $model->seccion   = $form->seccion;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sticker asignado correctamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al asignar el sticker.');
            }

            return $this->refresh();
        }

        return $this->render('usar-sticker', [
            'model' => $form,
        ]);
    }
    public function actionValidarUsarSticker()
    {
        $model = new GrumascanMarcacionUseForm();
        $model->scenario = 'ajax-codigo';

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        throw new BadRequestHttpException('Solicitud inválida.');
    }
}
