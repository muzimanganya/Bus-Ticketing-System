<?php
namespace app\models;

use yii\base\Model;
use Yii;

class UploadPricing extends Model
{
    public $file;
    public $route;
    
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv', 'checkExtensionByMimeType'=>false],
            [['route'], 'integer'],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Pricing File'),
        ];
    }
     
    public function readFile()
    {
        $file = $this->file->tempName;
        return array_map('str_getcsv', file($file));
    }
}
