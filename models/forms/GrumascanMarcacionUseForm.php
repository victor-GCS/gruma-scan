<?php

namespace app\models\forms;

use yii\base\Model;
use app\models\Grumascanmarcacion;

class GrumascanMarcacionUseForm extends Model
{
    public $codigo;     // ID del sticker
    public $idbodega;
    public $ubicacion;
    public $seccion;

    /** @var Grumascanmarcacion|null */
    private $_marcacion = null;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['default'] = ['codigo', 'idbodega', 'ubicacion', 'seccion'];
        $scenarios['ajax-codigo'] = ['codigo'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            // En ajax solo pedimos codigo
            [['codigo'], 'required', 'message' => 'No puede ir vacío', 'on' => ['ajax-codigo']],

            // En submit normal pedimos todo
            [['codigo', 'idbodega', 'ubicacion', 'seccion'], 'required', 'message' => 'No puede ir vacío', 'on' => ['default']],

            [['codigo', 'idbodega'], 'integer'],
            [['ubicacion', 'seccion'], 'string', 'max' => 50],

            // Valida existencia y reuso del sticker (aplica en ambos escenarios)
            ['codigo', 'validateSticker'],
        ];
    }

    public function validateSticker($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $id = (int)$this->codigo;
        if ($id <= 0) {
            $this->addError($attribute, 'Código de sticker inválido.');
            return;
        }

        $this->_marcacion = Grumascanmarcacion::findOne(['id' => $id]);
        if (!$this->_marcacion) {
            $this->addError($attribute, 'Sticker no existe.');
            return;
        }

        $used = ($this->_marcacion->idbodega !== null
            && !empty($this->_marcacion->ubicacion)
            && !empty($this->_marcacion->seccion)
        );

        if ($used) {
            $this->addError($attribute, 'Sticker ya fue utilizado.');
        }
    }

    public function getMarcacionModel(): ?Grumascanmarcacion
    {
        return $this->_marcacion;
    }
}
