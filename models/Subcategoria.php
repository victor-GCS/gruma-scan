<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "subcategoria".
 *
 * @property int $id
 * @property string $nombre
 * @property int $idCategoria
 * @property string|null $codigoERP
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Categoria $categoria
 */
class Subcategoria extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subcategoria';
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
            [['nombre', 'idCategoria'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idCategoria', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre'], 'string', 'max' => 50],
            ['nombre', 'unique', 'message' => 'Nombre Categoría ya está registrado.'],
            [['codigoERP'], 'string', 'max' => 20],
            ['codigoERP', 'unique', 'message' => 'Código ERP ya está registrado.'],  
            [['idCategoria'], 'exist', 'skipOnError' => true, 'targetClass' => Categoria::class, 'targetAttribute' => ['idCategoria' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'idCategoria' => 'Categoría',
            'codigoERP' => 'Código ERP',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Categoria]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'idCategoria']);
    }

    public static function actualizarRegistro ($modelSubcategoria){

        $model = Subcategoria::findOne(['codigoERP' => $modelSubcategoria->codigoERP]);
        if ($model == null){
            $model = new Subcategoria();
            $model->codigoERP = $modelSubcategoria->codigoERP;

            $model->nombre = $modelSubcategoria->nombre;
            $model->idCategoria = $modelSubcategoria->idCategoria;

            $model->save();
        }

        return $model->id;
    }

    public static  function  getListaData(){
        $data = Subcategoria::find()
                        ->select(['id', 'nombre'])
                        ->orderBy('nombre')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static  function  getListaDataCategoriaNombre($categoria){
        $data = Subcategoria::find()
                        ->alias('sub')
                        ->select(['cat.nombre AS id', 'sub.nombre AS name'])
                        ->join('INNER JOIN', 'categoria cat', 'sub.idCategoria = cat.id')
                        ->where(['cat.nombre' => $categoria])
                        ->orderBy(['cat.codigoERP' => SORT_ASC, 'sub.nombre' => SORT_ASC])
                        ->asArray()->all();
    	// $listadata = ArrayHelper::map($data, 'id', 'nombre');

    	return $data;
    }

    public static  function  getListaDataCategoria($categoria_id){
        $data = Subcategoria::find()
                        ->alias('sub')
                        ->select(['sub.id AS id', 'sub.nombre AS name'])
                        ->where(['sub.idCategoria' => $categoria_id])
                        ->asArray()->all();
    	// $listadata = ArrayHelper::map($data, 'id', 'nombre');

    	return $data;
    }
}
