<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class TenantModel extends ActiveRecord
{
    public static function getDb()
    {
        Yii::$container->setSingleton('db_tenant', [
            'class'=>'yii\db\Connection',
            'dsn' => str_replace('volcano_shared', Yii::$app->user->identity->tenant_db, Yii::$app->db->dsn),
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            // Duration of schema cache.
            'schemaCacheDuration' => 3600,//one hour
            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ]);
        return  Yii::$container->get('db_tenant');
    }
}
