<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "grumascanconteomanual".
 *
 * @property int $id
 * @property int $idgrumascanconteo
 * @property int|string $idmarcacion  // BIGINT en SQL Server, Yii lo maneja como string a veces
 * @property int $unidades_manual
 * @property int $unidades_sistema
 * @property int $diferencia
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Grumascanconteo $conteo
 * @property Grumascanmarcacion $marcacion
 */
class Grumascanconteomanual extends ActiveRecord
{
    public static function tableName()
    {
        return 'grumascanconteomanual';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                // DATETIME en SQL Server
                'value' => new Expression('GETDATE()'),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['idgrumascanconteo', 'idmarcacion', 'unidades_manual'], 'required'],
            [['idgrumascanconteo', 'unidades_manual', 'unidades_sistema', 'diferencia', 'created_by', 'updated_by'], 'integer'],
            // BIGINT: puede llegar como string desde PDO sqlsrv
            [['idmarcacion'], 'integer'],

            [['created_at', 'updated_at'], 'safe'],

            // No negativos
            [['unidades_manual', 'unidades_sistema'], 'integer', 'min' => 0],

            // Unicidad por conteo+mueble
            [
                ['idgrumascanconteo', 'idmarcacion'],
                'unique',
                'targetAttribute' => ['idgrumascanconteo', 'idmarcacion'],
                'message' => 'Ya existe una validación manual para este conteo y este número.'
            ],

            // FK
            [
                ['idgrumascanconteo'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Grumascanconteo::class,
                'targetAttribute' => ['idgrumascanconteo' => 'id']
            ],
            [
                ['idmarcacion'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Grumascanmarcacion::class,
                'targetAttribute' => ['idmarcacion' => 'id']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idgrumascanconteo' => 'Conteo',
            'idmarcacion' => 'Número (mueble)',
            'unidades_manual' => 'Unidades manual',
            'unidades_sistema' => 'Unidades sistema',
            'diferencia' => 'Diferencia',
            'created_at' => 'Creado',
            'created_by' => 'Creado por',
            'updated_at' => 'Actualizado',
            'updated_by' => 'Actualizado por',
        ];
    }

    public function getConteo()
    {
        return $this->hasOne(Grumascanconteo::class, ['id' => 'idgrumascanconteo']);
    }

    public function getMarcacion()
    {
        return $this->hasOne(Grumascanmarcacion::class, ['id' => 'idmarcacion']);
    }

    /**
     * Antes de guardar, asegura snapshot y diferencia.
     * Aquí NO usamos save(false). Solo calculamos campos.
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        // Si aún no han seteado unidades_sistema, lo dejas para que lo setee el controlador/servicio.
        // Pero si viene, calculamos diferencia.
        if ($this->unidades_sistema !== null && $this->unidades_manual !== null) {
            $this->diferencia = (int)$this->unidades_manual - (int)$this->unidades_sistema;
        }

        return true;
    }
}
