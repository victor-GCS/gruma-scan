<?php

namespace app\models;

use common\models\OrdendecompraSIESA;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use common\models\User;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "talla".
 *
 * @property int $id
 * @property string|null $codigo
 * @property string|null $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Talla extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'talla';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by', 'orden'], 'integer'],
            [['codigo'], 'string', 'max' => 50],
            [['nombre'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Actualizado',
            'updated_by' => 'Actualizado por',
            'orden' => 'orden',
        ];
    }

    public static function actualizarRegistro($codigo, $nombre)
    {

        $model = Talla::findOne(['codigo' => $codigo]);
        if ($model == null) {
            $model = new Talla();
            $model->codigo = $codigo;
            $model->nombre = $nombre;
        }

        $model->save();
        return $model->id;
    }

    public static function getListaData()
    {
        $data = Talla::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
    public function getUsuarioCreador()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    public function getUsuarioActualiza()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public static function actualizarRegistroSiesa($codigo)
    {
        $resultado = OrdendecompraSIESA::obtenerTallaPorCodigo($codigo);

        if (!empty($resultado)) {

            $model = Talla::findOne(['codigo' => $codigo]);

            if ($model == null) {
                $model = new Talla();
                $model->codigo = $codigo;
                $model->nombre = $resultado['nombre']; // 🔹 Se obtiene correctamente desde la consulta
                $model->orden = self::obtenerNuevoOrden(); // 🔹 Se asigna el nuevo orden
            } else {
                $model->nombre = $resultado['nombre']; // 🔹 Actualiza si ya existe
            }

            $model->save();

            return $model;
        }

        return 'NO existe en Siesa';
    }
    public static function obtenerNuevoOrden()
    {
        return (int) Talla::find()->max('orden') + 1;
    }

    public static function actualizarRegistrocn($codigo, $nombre)
    {

        $model = Talla::findOne(['codigo' => $codigo]);
        if ($model == null) {
            $model = new Talla();
            $model->codigo = $codigo;
            $model->nombre = $nombre;
        }

        $model->save();
        return $model->id;
    }
}
