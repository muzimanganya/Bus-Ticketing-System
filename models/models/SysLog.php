<?php

namespace app\models;

use Yii;
use app\models\TenantModel;

/**
 * This is the model class for table "SysLogs".
 *
 * @property integer $id
 * @property string $category
 * @property string $reference
 * @property string $comment
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 */
class SysLog extends TenantModel
{
    const LOG_CAT_BUS_CHANGE = 'BC';
    const LOG_CAT_CAPACITY_CHANGE = 'CC';
    const LOG_CAT_ROUTE_CANCEL = 'RC';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'SysLogs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'reference', 'comment', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['category'], 'string', 'max' => 45],
            [['reference'], 'string', 'max' => 500],
            [['comment'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category' => Yii::t('app', 'Category'),
            'reference' => Yii::t('app', 'Reference'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}
