<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TenantMapping".
 *
 * @property string $tenant
 * @property string $database
 */
class ThirdParty extends \yii\db\ActiveRecord
{
    public $logoFile;
    public $logoPath;
    public $logoUrl = '@web/uploads/logo/';

    public function init()
    {
        parent::init();
        $this->logoPath = Yii::getAlias('@webroot/uploads/logo/');

        if (!file_exists($this->logoPath)) {
            mkdir($this->logoPath, 0777, true);
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TenantMapping';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tenant', 'database'], 'required'],
            [['tenant', 'database', 'logo'], 'string', 'max' => 100],
            [['logoFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tenant' => Yii::t('app', 'Company'),
            'database' => Yii::t('app', 'Database Name'),
            'logoFile'=>'Company\'s Logo (48x48)'
        ];
    }

    public function upload()
    {
        //if it is update, use same path and overwrite things. Else, just make new name
        if(!$this->getIsNewRecord())
        {
            unlink($this->logoPath . $this->logo);
        }
        
        $this->logo = hash('sha256', microtime()).'.'.$this->logoFile->extension;
        //base path
        $this->logoFile->saveAs($this->logoPath . $this->logo );
        return true;
    }
}
