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
 * This is the model class for table "color".
 *
 * @property int $id
 * @property string|null $codigo
 * @property string|null $nombre
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Color extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'color';
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
            [['created_by', 'updated_by'], 'integer'],
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
        ];
    }

    public static function actualizarRegistro($codigo, $nombre)
    {

        $model = Color::findOne(['codigo' => $codigo]);
        if ($model == null) {
            $model = new Color();
            $model->codigo = $codigo;
            $model->nombre = $nombre;
        }
        $model->save();

        return $model->id;
    }

    public static function getListaData()
    {
        $data = Color::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function actualizarRegistroSiesa($codigo)
    {
        $resultado = OrdendecompraSIESA::obtenerColorPorCodigo($codigo);

        if (!empty($resultado)) {

            $model = Color::findOne(['codigo' => $codigo]);

            if ($model == null) {
                $model = new Color();
                $model->codigo = $codigo;
                $model->nombre = $resultado['nombre']; // 🔹 Se obtiene correctamente desde la consulta
            } else {
                $model->nombre = $resultado['nombre']; // 🔹 Actualiza si ya existe
            }

            $model->save();

            return $model;
        }

        return 'NO existe en Siesa';
    }

    public function getUsuarioCreador()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    public function getUsuarioActualiza()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
    public static function actualizarRegistrocn($codigo, $nombre)
    {

        $model = Color::findOne(['codigo' => $codigo]);
        if ($model == null) {
            $model = new Color();
            $model->codigo = $codigo;
            $model->nombre = $nombre;
        }
        $model->save();

        return $model->id;
    }
}
