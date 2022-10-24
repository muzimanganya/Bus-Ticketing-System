<?php 
$I = new ApiTester($scenario);

#get user oltranz
$user = \app\modules\mobile\models\Company::find()->where(['name'=>'oltranz'])->one();
$user->updateAttributes (['is_active' => 1]);


$I->wantTo('Book a Ticket with Invalid JSON');
$I->amBearerAuthenticated($user->token);

$I->sendPOST('api/reserve', '{,}');
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>false,'message'=>'invalid JSON Request']);

//div rwanda burundi and intl
$json_missing_from = '{"division":"rwanda", "start":"KIGALI",	"end":"KAMPALA",	"date": "09-07-2017",	"time":"21H00", 	"route": "2", 	"currency":"UGS",	"discount": "0",	"pos": "81600479",	"seat": "0",	"customer": { "number":"0717601803", "name":"Stefan II", "passport":"P1234",  "from_nation":"Rwanda", "to_nation":"Uganda", "nationality":"Congolese", "gender":"1", "age":"21"	}}';

$I->wantTo('Book a Ticket with Missing From');
$I->amBearerAuthenticated($user->token);
$I->sendPOST('api/reserve', $json_missing_from);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>false,'message'=>'invalid JSON Request2']);

/*

$json_ok = '{"start":"KIGALI",	"end":"KAMPALA",	"date": "09-07-2017",	"time":"21H00", 	"route": "2", 	"currency":"UGS",	"discount": "0",	"pos": "81600479",	"seat": "0",	"customer": { "number":"0717601803", "name":"Stefan II", "passport":"P1234",  "from_nation":"Rwanda", "to_nation":"Uganda", "nationality":"Congolese", "gender":"1", "age":"21"	}}';

*/
