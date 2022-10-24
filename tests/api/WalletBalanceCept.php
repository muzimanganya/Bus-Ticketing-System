<?php 

#get user oltranz
$user = \app\modules\mobile\models\Company::find()->where(['name'=>'oltranz'])->one();
$user->updateAttributes (['is_active' => 1]);

$I = new ApiTester($scenario);

//walet active check balance
$I->wantTo('Check Balance for the company');
$I->sendGet('api/balance', ['access-token' =>$user->token]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>true]);
$I->seeResponseContainsJson(['previous_balance'=>$user->previous_balance, 'current_balance'=>$user->current_balance]);

//wallet suspended
$user->updateAttributes (['is_active' => 0]);

$I->wantTo('Wallet suspension');
$I->sendGet('api/balance', ['access-token' =>$user->token]);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>false, 'message' => 'Wallet is locked']);

