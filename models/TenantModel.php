<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class TenantModel extends ActiveRecord
{
    public static function getDb()
    {
        if(!Yii::$container->has('db_tenant'))
        {
            $tenant_db = 'set_tenant_db_in_DI_Container';
            if(Yii::$app->user->isGuest)
                $tenant_db = Yii::$app->params['tenant_db'];
            else
                $tenant_db = Yii::$app->user->identity->tenant_db;
            
            Yii::$container->set('db_tenant', [
                'class'=>'yii\db\Connection',
                'dsn' => str_replace('volcano_shared', $tenant_db, Yii::$app->db->dsn),
                'username' => Yii::$app->db->username,
                'password' => Yii::$app->db->password,
                'charset' => 'utf8',
                'enableSchemaCache' => true,
                // Duration of schema cache.
                'schemaCacheDuration' => 3600,//one hour
                // Name of the cache component used to store schema information
                'schemaCache' => 'cache',
            ]);
        }
        return  Yii::$container->get('db_tenant');
    }

    public static function resetTenantDatabase()
    {
        //self::setTenantDatabase();
    }

    public static function setTenantDatabase($db = null)
    {
        Yii::$container->set('db_tenant', [
            'class'=>'yii\db\Connection',
            'dsn' => str_replace('volcano_shared', empty($db) ? Yii::$app->user->identity->tenant_db : $db, Yii::$app->db->dsn),
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => 'utf8',
            'enableSchemaCache' => false,
            // Duration of schema cache.
            'schemaCacheDuration' => 3600,//one hour
            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ]);
    }
}
