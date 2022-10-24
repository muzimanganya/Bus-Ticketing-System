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
class ReconciliationController extends Controller
{
    /**
     * Sends SMS via commandline. Put it on cron
     * @param string $message the message to be echoed.
     */
    public function actionSeats()
    {
	Yii::$app->db->transaction(function($conn) {	
            $dbs = ['DIFFERENT', 'KIVUBELT_db', 'NILE_DB', 'matunda_db', 'select_db', 'stella_db', 'volcano_burundi', 'volcano_uganda'];
            foreach($dbs as $db)
            {
            	$conn->createCommand("DELETE FROM {$db}.ReservedSeats WHERE status='EX' ORDER BY dept_date DESC LIMIT 500")->execute();
            }
        });
    }
       
    public function actionClean()
    {
	Yii::$app->db->transaction(function($conn) {
             $dbs = ['DIFFERENT', 'KIVUBELT_db', 'NILE_DB', 'matunda_db', 'select_db', 'stella_db', 'volcano_burundi', 'volcano_uganda'];
            foreach($dbs as $db)
            {
                $year = date('Y');
                $month = intval(date('m'))-1;
                $affected = $conn->createCommand("DELETE  FROM {$db}.ReservedSeats WHERE YEAR(dept_date) < {$year} OR (YEAR(dept_date) = {$year} AND MONTH(dept_date) < {$month}) LIMIT 500")->execute();
                Yii::error("{$db} - Affected {$affected}");
           }
        });
    }
} 
