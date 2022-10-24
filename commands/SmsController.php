<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\TenantModel;
use Yii;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SmsController extends Controller
{
    /**
     * Sends SMS via commandline. Put it on cron
     * @param string $message the message to be echoed.
     */
    public function actionIndex()
    {
        $dbs = ['VOLCANO', 'volcano_burundi', 'volcano_uganda'];
        foreach($dbs as $db)
        {
            $connection = new \yii\db\Connection([
                'dsn' =>"mysql:host=localhost;dbname=$db",
                'username' => 'root',
                'password' => '@volcano#',
            ]);
            $connection->open(); 
            $smses = $connection->createCommand('SELECT * FROM SMS WHERE status="pending" AND created_by<>"1"')->queryAll();
            $prefix = $connection->createCommand('SELECT value FROM Setting WHERE `section`="sms" AND `key` ="prefix"')->queryScalar();
            foreach($smses as $sms) 
            {
                $to = $sms['customer'];
                $prefixes = ['250', '+250', '254', '+254', '255', '+255', '256', '+256', '257', '+257'];
                if(!in_array(substr($to, 0, 3), $prefixes))
                {
                    if($prefix)
                    {
                        if(substr($to, 0, 1)=='0')//number starts with 0
                            $to = substr($to, 1);
                        $to = $prefix.$to;
                    }
                    else 
                        continue; //cannot send unprefixed number with no prefix set
                }

            
                 if(Yii::$app->sms->send($to, $sms['message']))
                 {
                    $connection->createCommand()->update('SMS', ['status' => 'sent'], ['id'=>$sms['id']])->execute();
                 }
                 else
                 {
                    $connection->createCommand()->update('SMS', ['status' => 'failed'], ['id'=>$sms['id']])->execute();
				 }
            }
        }
        
    }
}
