<?php 
$I = new ApiTester($scenario);
$I->wantTo('Login with Correct credentials');
$I->sendPOST('api/login', ['username' => 'oltranz', 'password' => '123456']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>true]);

//Fail Login
$I->wantTo('Login with Wrong credentials');
$I->sendPOST('api/login', ['username' => 'wrong', 'password' => 'wrong-password']);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContainsJson(['success'=>false]);
