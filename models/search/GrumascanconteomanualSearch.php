<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Grumascanconteomanual;

class GrumascanconteomanualSearch extends Grumascanconteomanual
{
    public function rules()
    {
        return [
            [['id', 'idgrumascanconteo', 'unidades_manual', 'unidades_sistema', 'diferencia'], 'integer'],
            [['idmarcacion', 'created_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Grumascanconteomanual::find()
            ->joinWith(['conteo', 'marcacion']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'grumascanconteomanual.id' => $this->id,
            'grumascanconteomanual.idgrumascanconteo' => $this->idgrumascanconteo,
            'grumascanconteomanual.unidades_manual' => $this->unidades_manual,
            'grumascanconteomanual.unidades_sistema' => $this->unidades_sistema,
            'grumascanconteomanual.diferencia' => $this->diferencia,
        ]);

        // BIGINT puede venir como string
        if ($this->idmarcacion !== null && $this->idmarcacion !== '') {
            $query->andWhere(['grumascanconteomanual.idmarcacion' => $this->idmarcacion]);
        }

        if (!empty($this->created_at)) {
            // filtro simple por "contiene"
            $query->andWhere(['like', 'CONVERT(VARCHAR(19), grumascanconteomanual.created_at, 120)', $this->created_at]);
        }

        return $dataProvider;
    }
}
