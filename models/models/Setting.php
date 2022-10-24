<?php
namespace app\models;

use yii2mod\settings\models\SettingModel;
use yii\behaviors\TimestampBehavior;
use app\models\TenantModel;

class Setting extends SettingModel
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'Setting';
    }
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'updatedAt',
                'value' => time(),
            ],
        ];
    }

    
    public static function getDb()
    {
        return TenantModel::getDb();
    }
}
