<?php 

namespace app\modules\v2\helpers;

/**
 * Serialize the Companies array to non standard way
 */

class RuraApiFormatter extends \yii\web\JsonResponseFormatter
{
	public function formatJson($response)
	{
		//var_dump($response->data); die();
		if($response->data!==null)
		{
			//actually do not encode anything if data is string
			if(isset($response->data['fieldName']))
			{
			    $response->content = sprintf("{\"status_code\":%s, \"%s\":%s}",$response->data['status'], $response->data['fieldName'], json_encode($response->data['data'], JSON_FORCE_OBJECT|JSON_NUMERIC_CHECK));
			}
			else
				parent::formatJson($response);
		}
	}
}
