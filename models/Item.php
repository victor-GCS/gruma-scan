<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;
use common\models\OrdendecompraSIESA;

/**
 * This is the model class for table "item".
 *
 * @property int $id
 * @property float $item
 * @property string $referencia
 * @property string $descripcion
 * @property int $idCategoria
 * @property int $idSubcategoria
 * @property int|null $idProducto
 * @property int|null $idMarca
 * @property int|null $idTalla
 * @property int|null $idColor
 * @property string|null $codigoProveedor
 * @property string|null $nombreProveedor
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Color $color
 * @property Marca $idMarca0
 * @property Producto $idProducto0
 * @property Talla $talla
 */
class Item extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item';
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
            [['item', 'referencia', 'descripcion', 'idCategoria', 'idSubcategoria'], 'required'],
            [['item'], 'number'],
            [['idCategoria', 'idSubcategoria', 'idProducto', 'idMarca', 'idTalla', 'idColor', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['referencia'], 'string', 'max' => 50],
            [['descripcion', 'nombreProveedor', 'codigoBarras'], 'string', 'max' => 150],
            [['codigoProveedor', 'unidadOrden', 'unidadEmpaque'], 'string', 'max' => 10],
            [['idTalla'], 'exist', 'skipOnError' => true, 'targetClass' => Talla::class, 'targetAttribute' => ['idTalla' => 'id']],
            [['idColor'], 'exist', 'skipOnError' => true, 'targetClass' => Color::class, 'targetAttribute' => ['idColor' => 'id']],
            [['idMarca'], 'exist', 'skipOnError' => true, 'targetClass' => Marca::class, 'targetAttribute' => ['idMarca' => 'id']],
            [['idProducto'], 'exist', 'skipOnError' => true, 'targetClass' => Producto::class, 'targetAttribute' => ['idProducto' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item' => 'Item',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripcion',
            'idCategoria' => 'Id Categoria',
            'idSubcategoria' => 'Id Subcategoria',
            'idProducto' => 'Id Producto',
            'idMarca' => 'Id Marca',
            'idTalla' => 'Id Talla',
            'idColor' => 'Id Color',
            'codigoProveedor' => 'Codigo Proveedor',
            'nombreProveedor' => 'Nombre Proveedor',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'unidadOrden' => 'Unidad Orden',
            'unidadEmpaque' => 'Unidad Empaque',
            'codigoBarras' => 'Código Barras'
        ];
    }

    /**
     * Gets query for [[Color]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getColor()
    {
        return $this->hasOne(Color::class, ['id' => 'idColor']);
    }

    public function getUnidadempaque()
    {
        return $this->hasOne(Unidadempaque::class, ['codigo' => 'unidadEmpaque']);
    }

    public function getUnidadorden()
    {
        return $this->hasOne(Unidadempaque::class, ['codigo' => 'unidadOrden']);
    }

    /**
     * Gets query for [[Marca]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMarca()
    {
        return $this->hasOne(Marca::class, ['id' => 'idMarca']);
    }

    /**
     * Gets query for [[IdProducto0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdProducto0()
    {
        return $this->hasOne(Producto::class, ['id' => 'idProducto']);
    }

    /**
     * Gets query for [[Talla]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTalla()
    {
        return $this->hasOne(Talla::class, ['id' => 'idTalla']);
    }

    public function getSubcategoria()
    {
        return $this->hasOne(Subcategoria::class, ['id' => 'idSubcategoria']);
    }

    public function getCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'idCategoria']);
    }
    public function geInventariogruma($codigoEAN, $bodega)
    {
        return Inventario::findOne(['codigoBarras' => $codigoEAN, 'codigoBodega' => $bodega])->existencia;
    }
    public static function actualizarRegistro(
        $item,
        $referencia,
        $descripcion,
        $categoria,
        $subcategoria,
        $codigotalla,
        $nombretalla,
        $codigocolor,
        $nombrecolor,
        $codigobarras
    ) {

        $modelCategoria = new Categoria();
        $cadena = $categoria;
        $tokens = explode('/', $cadena);
        $modelCategoria->codigoERP = trim($tokens[0]);
        $modelCategoria->nombre = trim($tokens[1]);
        $idcategoria = Categoria::actualizarRegistro($modelCategoria);

        $modelSubcategoria = new Subcategoria();
        $cadena = $subcategoria;
        $tokens = explode('/', $cadena);
        $modelSubcategoria->codigoERP = trim($tokens[0]);
        $modelSubcategoria->nombre = trim($tokens[1]);
        $modelSubcategoria->idCategoria = $idcategoria;
        $idsubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);

        $idtalla = Talla::actualizarRegistro($codigotalla, $nombretalla);
        $idcolor = Color::actualizarRegistro($codigocolor, $nombrecolor);

        if ($codigobarras) {
            $model = Item::findOne(['codigoBarras' => $codigobarras]);
            if ($model == null) {
                $model = new Item();
                $model->codigoBarras = $codigobarras;
            }
            $model->item = $item;
            $model->idTalla = $idtalla;
            $model->idColor = $idcolor;
        } else {
            $model = Item::findOne([
                'item' => $item,
                'idTalla' => $idtalla,
                'idColor' => $idcolor
            ]);

            if ($model == null) {
                $model = new Item();
                $model->item = $item;
                $model->idTalla = $idtalla;
                $model->idColor = $idcolor;
            }
        }

        $model->referencia = $referencia;
        $model->descripcion = $descripcion;
        $model->idCategoria = $idcategoria;
        $model->idSubcategoria = $idsubcategoria;

        if (!$model->save()) {
            var_dump($model->getErrors());
            die("hola");
        }

        return $model->id;
    }

    public static function getListaData()
    {
        $data = Item::find()
            ->select([
                'it.id',
                //'descripcion AS nombre'
                "(CAST(it.item AS VARCHAR) + ' - '  + it.descripcion + ' - ' + col.codigo + ' - ' + tal.codigo) AS nombre"
            ])
            ->alias('it')
            ->join('LEFT JOIN', 'color col', 'it.idColor = col.id')
            ->join('LEFT JOIN', 'talla tal', 'it.idTalla = tal.id')
            ->orderBy('it.item')->asArray()->all();

        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function obtenerPrecioVenta($codigoEAN, $fecha_activacion)
    {

        $resultado = OrdendecompraSIESA::obtenerDatosPrecioVenta($codigoEAN, $fecha_activacion, '001');
        if (!empty($resultado)) {
            foreach ($resultado as $dato) {
                return $dato['f126_precio'];
            }
        }

        return 0;
    }

    public static function generarContenidoSticker($registro, $precio, $x, $y, $labelWidth)
    {
        // --- Ajuste de margen superior ---
        $y += 30; // Mueve todo hacia abajo para evitar que el contenido quede pegado al borde superior

        // Coordenadas y espaciado personalizado
        $codigoY = $y + 40;
        $espacio1 = 40; // espacio entre código de barras y descripción
        $espacio2 = 20; // espacio entre descripción y referencia

        $descripcionY = $codigoY + $espacio1;
        $referenciaY = $descripcionY + $espacio2;
        $itemY = $referenciaY + $espacio2;

        $precioUnidadX = $x + 80; // más a la derecha
        $precioUnidadY = $itemY + 5;

        $lineaFinalY = $itemY + $espacio2;

        // Contenido
        $codigo = preg_replace('/[^0-9A-Z]/', '', trim($registro->codigoBarras));
        $descripcion = str_pad(strtoupper(substr(trim($registro->descripcion), 0, 22)), 22, ' ', STR_PAD_RIGHT);

        // $referencia  = str_pad(strtoupper(substr(trim($registro->referencia), 0, 20)), 20, ' ', STR_PAD_RIGHT);
        $referenciaCompleta = trim($registro->referencia);
        $partes = explode('-', $referenciaCompleta);
        if (isset($partes[1]) && strlen($partes[1]) > 0) {
            $referenciaSoloCodigo = $partes[1];
        } else {
            $referenciaSoloCodigo = $referenciaCompleta;
        }
        $referencia = str_pad(strtoupper(substr($referenciaSoloCodigo, 0, 15)), 15, ' ', STR_PAD_RIGHT);

        $color = str_pad(strtoupper(substr(trim($registro->color->nombre), 0, 10)), 10, ' ', STR_PAD_RIGHT);
        $linea3 = $referencia . $color;

        $item = strtoupper(substr(trim($registro->item), 0, 10));

        $talla = str_pad(strtoupper(substr(trim($registro->talla->nombre ?? ''), 0, 8)), 8, ' ', STR_PAD_RIGHT);
        $tallaTexto = strtoupper(trim($registro->talla->nombre ?? ''));

        // Centrar dentro de 8 caracteres
        $tallaCentrada = str_pad($tallaTexto, 8, ' ', STR_PAD_BOTH);

        // Añadir 3 espacios iniciales
        $tallaBloque = $tallaCentrada;

        $equivalencia = max(1, $registro->equivalencia);
        $preciounidad = $precio / $equivalencia;
        $precioUnidadFormateado = '$ ' . number_format($preciounidad, 0, ',', '.');

        $precioFormateado = '$ ' . number_format($precio, 0, ',', '.');

        // Precio: centrado dentro del bloque de 25 caracteres (375px de ancho)
        $precioCharWidth = 14; // Ajustado visualmente
        $precioBlockWidth = 330; // bloque reservado más conservador
        $precioBlockStart = $x + (15 * 15); // después de los 15 chars de la talla

        $precioTextWidth = strlen($precioFormateado) * $precioCharWidth;
        $precioX = $precioBlockStart + floor(($precioBlockWidth - $precioTextWidth) / 2);

        // Posición fija para el precio
        $precioX = $x + 95; // posición relativa dentro del bloque de 200px

        $codigoTextoX = $x + 5;
        $codigoTextoY = $y + 60;

        // Generar ZPL
        $stickerContent = "
			^FO{$x},{$y}
			^BY1.4,2.8,50
			^B3N,N,60,N,N
			^FD{$codigo}^FS
			
			^FO{$codigoTextoX},{$codigoTextoY}
			^A0N,16,16
			^FD{$codigo}^FS

			^FO{$x},{$descripcionY}
			^A0N,16,16
			^FD{$descripcion}^FS

			^FO{$x},{$referenciaY}
			^A0N,16,16
			^FD{$linea3}^FS
			
			^FO{$x},{$itemY}
			^A0N,16,16
			^FD{$item}^FS
			
			^FO{$precioUnidadX},{$precioUnidadY}
			^A0N,10,10
			^FDUnidad   a  {$precioUnidadFormateado}^FS
			
			^FO{$x},{$lineaFinalY}
			^A0N,28,28
			^FD{$tallaBloque}^FS
			
			^FO{$precioX},{$lineaFinalY}
			^A0N,36,36
			^FD{$precioFormateado}^FS
		";

        // Añadir palabra HERPO en vertical al lado derecho del sticker
        $verticalText = ['H', 'E', 'R', 'P', 'O'];
        $verticalX = $x + $labelWidth - 20; // más cerca del borde
        $verticalYStart = $y + 20;
        $espaciadoVertical = 20;

        foreach ($verticalText as $index => $letra) {
            $letraY = $verticalYStart + ($index * $espaciadoVertical);
            $stickerContent .= "
				^FO{$verticalX},{$letraY}
				^A0N,12,12
				^FD{$letra}^FS
			";
        }

        return $stickerContent;
    }
    public function getEquivalencia()
    {
        $codigo = $this->unidadEmpaque ?? 'UND'; // Si es null, asumir 'UND'
        $modelo = Unidadempaque::findOne(['codigo' => $codigo]);
        return $modelo->equivalencia ?? 1;
    }
    public static function grabarDataDesdeSIESA($item, $color, $talla)
    {

        $resultado = OrdendecompraSIESA::obtenerDatosItem($item, $color, $talla);
        if (!empty($resultado)) {
            foreach ($resultado as $dato) {

                $modelCategoria = new Categoria();
                $modelCategoria->codigoERP = $dato['idCategoria'];
                $modelCategoria->nombre = $dato['categoria'];
                $idcategoria = Categoria::actualizarRegistro($modelCategoria);

                $modelSubcategoria = new Subcategoria();
                $modelSubcategoria->codigoERP = $dato['idSubcategoria'];
                $modelSubcategoria->nombre = $dato['subcategoria'];
                $modelSubcategoria->idCategoria = $idcategoria;
                $idsubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);

                $codigo = $dato['idTalla'];
                $nombre = $dato['talla'];
                $idtalla = Talla::actualizarRegistro($codigo, $nombre);

                $codigo = $dato['idColor'];
                $nombre = $dato['color'];
                $idcolor = Color::actualizarRegistro($codigo, $nombre);

                $modelProducto = new Producto();
                $modelProducto->codigo = $dato['idProducto'];
                $modelProducto->nombre = $dato['producto'];
                $idproducto = Producto::actualizarRegistro($modelProducto);

                $modelMarca = new Marca();
                $modelMarca->codigo = $dato['idMarca'];
                $modelMarca->nombre = $dato['marca'];
                $idmarca = Marca::actualizarRegistro($modelMarca);

                $codigobarras = $dato['codigoBarras'];
                $descripcion = $dato['descripcion'];
                $referencia = $dato['referencia'];
                $codigoproveedor = $dato['idProveedor'];
                $nombreproveedor = $dato['proveedor'];

                $estado = $dato['estadoItem'];
                $unidadempaque = $dato['unidadEmpaque'];
                $unidadorden = $dato['unidadOrden'];

                if ($codigobarras) {
                    $model = Item::findOne(['codigoBarras' => $codigobarras]);
                    if ($model == null) {
                        $model = new Item();
                        $model->codigoBarras = $codigobarras;
                    }
                    $model->item = $item;
                    $model->idTalla = $idtalla;
                    $model->idColor = $idcolor;
                } else {
                    $model = Item::findOne([
                        'item' => $item,
                        'idTalla' => $idtalla,
                        'idColor' => $idcolor
                    ]);

                    if ($model == null) {
                        $model = new Item();
                        $model->item = $item;
                        $model->idTalla = $idtalla;
                        $model->idColor = $idcolor;
                    }
                }

                $model->referencia = $referencia;
                $model->descripcion = $descripcion;
                $model->idCategoria = $idcategoria;
                $model->idSubcategoria = $idsubcategoria;
                $model->idProducto = $idproducto;
                $model->idMarca = $idmarca;
                $model->codigoProveedor = $codigoproveedor;
                $model->nombreProveedor = $nombreproveedor;
                $model->idEstado = $estado;
                $model->unidadEmpaque = $unidadempaque;
                $model->unidadOrden = $unidadorden;

                if (!$model->save()) {
                    var_dump($model->getErrors());
                    die("hola ITEM: " . $model->item);
                }

                $id = $model->id;

                if ($dato['codigoBarras'] == $dato['codigoBarrasPrincipal']) {
                    $idprincipal = $model->id;
                }
            }
        }

        return $idprincipal ? $idprincipal : $id;
    }

    public static function getInventario($codigobarras, $codigobodega)
    {

        $command = \Yii::$app->dbSiesa->createCommand("
                select 
                t400.f400_cant_existencia_1
                -- t131.f131_id, f150_id_co, f150_id, t400.f400_cant_existencia_1
                -- t400.f400_cant_comprometida_1, t400.f400_cant_existencia_2
                from t400_cm_existencia t400
                inner join t150_mc_bodegas t150
                ON t400.f400_rowid_bodega = t150.f150_rowid
                left join [t131_mc_items_barras] t131
                ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
                WHERE 1 = 1
                AND t131.f131_id = '" . $codigobarras .
            "' AND f150_id = '" . $codigobodega . "';");

        // $result = $command->queryAll();

        $existencia = $command->queryScalar();

        return $existencia;
    }
    public static function obtenerDatosItemPorCodigoBarras($codigobarras)
    {
        $command = \Yii::$app->dbsiesa->createCommand("
        SELECT 
            bar.f131_id AS codigoBarras,
            itx.f121_id_barras_principal AS codigoBarrasPrincipal,
            it.f120_id_cia, it.f120_id AS item,
            it.f120_referencia AS referencia,
            it.f120_descripcion AS descripcion,
            it.f120_descripcion_corta AS descripcionCorta,
            it.f120_id_unidad_inventario,
            it.f120_id_unidad_empaque AS unidadEmpaque,
            it.f120_id_unidad_orden AS unidadOrden,
            itx.f121_id_ext1_detalle AS idColor,
            col.f117_descripcion AS color,
            itx.f121_id_ext2_detalle AS idTalla,
            tl.f119_descripcion AS talla,
            TRIM(itcm.f106_id) AS idCategoria,
            TRIM(itcm.f106_descripcion) AS categoria,
            TRIM(itcm1.f106_id) AS idSubcategoria,
            TRIM(itcm1.f106_descripcion) AS subcategoria,
            TRIM(itcm2.f106_id) AS idProducto,
            TRIM(itcm2.f106_descripcion) AS producto,
            TRIM(itcm3.f106_id) AS idMarca,
            TRIM(itcm3.f106_descripcion) AS marca,
            TRIM(itcm4.f106_id) AS idProveedor,
            TRIM(itcm4.f106_descripcion) AS proveedor,
            itx.f121_ind_estado AS idEstadoItem,
            CASE 
                WHEN itx.f121_ind_estado = 1 THEN 'ACTIVO'
                WHEN itx.f121_ind_estado = 0 THEN 'INACTIVO'
                WHEN itx.f121_ind_estado = 2 THEN 'BLOQUEADO'
                ELSE 'DESCONOCIDO'
            END AS estadoItem
        FROM t120_mc_items it                 
        INNER JOIN t121_mc_items_extensiones itx 
            ON itx.f121_rowid_item = it.f120_rowid
        LEFT JOIN t131_mc_items_barras bar 
            ON itx.f121_rowid = bar.f131_rowid_item_ext
        LEFT JOIN t117_mc_extensiones1_detalle col 
            ON it.f120_id_cia = col.f117_id_cia 
            AND itx.f121_id_extension1 = col.f117_id_extension1 
            AND itx.f121_id_ext1_detalle = col.f117_id
        LEFT JOIN t119_mc_extensiones2_detalle tl 
            ON it.f120_id_cia = tl.f119_id_cia 
            AND itx.f121_id_extension2 = tl.f119_id_extension2 
            AND itx.f121_id_ext2_detalle = tl.f119_id
        LEFT JOIN t125_mc_items_criterios itc_categoria 
            ON it.f120_rowid = itc_categoria.f125_rowid_item 
            AND itc_categoria.f125_id_plan = '001'
        LEFT JOIN t106_mc_criterios_item_mayores itcm 
            ON itc_categoria.f125_id_plan = itcm.f106_id_plan 
            AND itc_categoria.f125_id_criterio_mayor = itcm.f106_id
        LEFT JOIN t125_mc_items_criterios itc_subcategoria 
            ON it.f120_rowid = itc_subcategoria.f125_rowid_item 
            AND itc_subcategoria.f125_id_plan = '002'
        LEFT JOIN t106_mc_criterios_item_mayores itcm1 
            ON itc_subcategoria.f125_id_plan = itcm1.f106_id_plan 
            AND itc_subcategoria.f125_id_criterio_mayor = itcm1.f106_id
        LEFT JOIN t125_mc_items_criterios itc_producto 
            ON it.f120_rowid = itc_producto.f125_rowid_item 
            AND itc_producto.f125_id_plan = '005'
        LEFT JOIN t106_mc_criterios_item_mayores itcm2 
            ON itc_producto.f125_id_plan = itcm2.f106_id_plan 
            AND itc_producto.f125_id_criterio_mayor = itcm2.f106_id
        LEFT JOIN t125_mc_items_criterios itc_marca 
            ON it.f120_rowid = itc_marca.f125_rowid_item 
            AND itc_marca.f125_id_plan = '014'
        LEFT JOIN t106_mc_criterios_item_mayores itcm3 
            ON itc_marca.f125_id_plan = itcm3.f106_id_plan 
            AND itc_marca.f125_id_criterio_mayor = itcm3.f106_id
        LEFT JOIN t125_mc_items_criterios itc_proveedor 
            ON it.f120_rowid = itc_proveedor.f125_rowid_item 
            AND itc_proveedor.f125_id_plan = '015'
        LEFT JOIN t106_mc_criterios_item_mayores itcm4 
            ON itc_proveedor.f125_id_plan = itcm4.f106_id_plan 
            AND itc_proveedor.f125_id_criterio_mayor = itcm4.f106_id
        WHERE bar.f131_id = :codigobarras
    ");

        $command->bindValue(':codigobarras', $codigobarras);
        $result = $command->queryOne();

        return $result;
    }


    public static function grabarItem($dato)
    {
        if (!empty($dato)) {
            $item = $dato['item'];

            // CATEGORÍA
            $modelCategoria = new Categoria();
            $modelCategoria->codigoERP = $dato['idCategoria'];
            $modelCategoria->nombre = $dato['categoria'];
            $idcategoria = Categoria::actualizarRegistro($modelCategoria);

            // SUBCATEGORÍA
            $modelSubcategoria = new Subcategoria();
            $modelSubcategoria->codigoERP = $dato['idSubcategoria'];
            $modelSubcategoria->nombre = $dato['subcategoria'];
            $modelSubcategoria->idCategoria = $idcategoria;
            $idsubcategoria = Subcategoria::actualizarRegistro($modelSubcategoria);

            // TALLA
            $codigo = $dato['idTalla'];
            $nombre = $dato['talla'];
            $idtalla = Talla::actualizarRegistrocn($codigo, $nombre);

            // COLOR
            $codigo = $dato['idColor'];
            $nombre = $dato['color'];
            $idcolor = Color::actualizarRegistrocn($codigo, $nombre);

            // PRODUCTO
            $modelProducto = new Producto();
            $modelProducto->codigo = $dato['idProducto'];
            $modelProducto->nombre = $dato['producto'];
            $idproducto = Producto::actualizarRegistro($modelProducto);

            // MARCA
            $modelMarca = new Marca();
            $modelMarca->codigo = $dato['idMarca'];
            $modelMarca->nombre = $dato['marca'];
            $idmarca = Marca::actualizarRegistro($modelMarca);

            // DATOS DEL ITEM
            $codigobarras = $dato['codigoBarras'];
            $descripcion = $dato['descripcion'];
            $referencia = $dato['referencia'];
            $codigoproveedor = $dato['idProveedor'];
            $nombreproveedor = $dato['proveedor'];

            $estado = $dato['estadoItem'];


            $unidadempaque = is_array($dato) ? ($dato['unidadEmpaque'] ?? null) : null;
            $unidadorden = is_array($dato) ? ($dato['unidadOrden'] ?? null) : null;



            // Buscar o crear modelo de Item
            if ($codigobarras) {
                $model = Item::findOne(['codigoBarras' => $codigobarras]);
                if ($model == null) {
                    $model = new Item();
                    $model->codigoBarras = $codigobarras;
                }
                $model->item = $item;
                $model->idTalla = $idtalla;
                $model->idColor = $idcolor;
            } else {
                $model = Item::findOne([
                    'item' => $item,
                    'idTalla' => $idtalla,
                    'idColor' => $idcolor
                ]);

                if ($model == null) {
                    $model = new Item();
                    $model->item = $item;
                    $model->idTalla = $idtalla;
                    $model->idColor = $idcolor;
                }
            }

            $model->referencia = $referencia;
            $model->descripcion = $descripcion;
            $model->idCategoria = $idcategoria;
            $model->idSubcategoria = $idsubcategoria;
            $model->idProducto = $idproducto;
            $model->idMarca = $idmarca;
            $model->codigoProveedor = $codigoproveedor;
            $model->nombreProveedor = $nombreproveedor;
            $model->idEstado = $estado;
            $model->unidadEmpaque = $unidadempaque;
            $model->unidadOrden = $unidadorden;

            if (!$model->save()) {
                var_dump($model->getErrors());
                die("Error ITEM: " . $model->item);
            }

            // Si el código de barras es el principal, hacer algo
            if ($dato['codigoBarras'] == $dato['codigoBarrasPrincipal']) {
                $idprincipal = $model->id;
                // Puedes devolverlo o registrarlo si necesitas
            }

            return true;
        }

        return false;
    }
}
