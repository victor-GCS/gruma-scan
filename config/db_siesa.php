<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlsrv:Server=siesa-m6-sqlsw-distproyeccion.cu94u26q0284.us-east-1.rds.amazonaws.com;Database=UnoEE_DistProyeccion_Real',
    // 'dsn' => 'sqlsrv:Server=ec2-54-87-177-60.compute-1.amazonaws.com;Database=UnoEE_DistProyeccion_Pruebas', //pruebas    
    'username' => 'distproyeccion',
    'password' => 'Distproyeccion$12$%',

    'charset' => 'utf8',

    'attributes' => [
        PDO::ATTR_EMULATE_PREPARES => true,
    ],

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
