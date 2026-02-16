<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "producto".
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Item[] $items
 */
class Producto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'producto';
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
            [['codigo', 'nombre'], 'required'],
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
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::class, ['idProducto' => 'id']);
    }

    public static function actualizarRegistro ($modelProducto){

        $model = Producto::findOne(['codigo' => $modelProducto->codigo]);
        if ($model == null){
            $model = new Producto();
            $model->codigo = $modelProducto->codigo;
        }
        $model->nombre = $modelProducto->nombre;
        $model->save();

        return $model->id;
    }

    public static  function  getListaData(){
        $data = Producto::find()
                        ->select(['id', 'nombre'])
                        ->orderBy('nombre')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
