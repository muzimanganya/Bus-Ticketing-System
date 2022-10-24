<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'cards' => [
            'class' => 'app\modules\cards\Module',
        ],
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
        ],
        'settings' => [
            'class' => 'yii2mod\settings\Module',
            'controllerMap' => [
                'default' => [
                    'class' => 'yii2mod\settings\controllers\DefaultController',
                    'searchClass' => [
                        'class' => 'app\models\SettingSearch',
                        'pageSize' => 25
                    ],
                    'modelClass' => 'app\models\Setting',
                    'indexView' => '@app/views/settings/index.php',
                ]
            ]
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module',
            'downloadAction' => 'gridview/export/download'
        ]
    ],
    'components' => [
        'sms' => [
            'class' => 'app\components\SMS',
            'username' => 'lap.ltd',
            'password' => 'lapltd@20!8',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages', // if advanced application, set @frontend/messages
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        //'main' => 'main.php',
                    ],
                ],
            ],
        ],
        'settings' => [
            'class' => 'yii2mod\settings\components\Settings',
            'cache' => null,
            'modelClass' => 'app\models\Setting',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'l5Vr6TwZ7Rkh6RhBxPHpj2_xGb9m9qsz',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.lapexpress.co.rw',
                'username' => 'wallet@lapexpress.co.rw',
                'password' => 'wallet@2018',
                'port' => '587',
                'encryption' => 'tls',
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'rules' => [],
        ],
    ],
    'params' => $params,
    //force login
    'as beforeRequest' => [
        'class' => 'yii\filters\AccessControl',
        'except' => [
            'v1/*',
            'v2/*',
            'mobile/*',
            'cards/*',
        ],
        'rules' => [
            [
                'actions' => ['login', 'error'],
                'allow' => true,
            ],
            [

                'allow' => true,
                'roles' => ['@'],
            ],
        ],
    ],

    'on beforeAction' => function ($event) {
        Yii::$app->setTimeZone('Africa/Kigali'); //TDL convert it to value from server
    },
    'on afterRequest' => function ($event) {
        if (Yii::$container->has('db_tenant')) {
            $db = Yii::$container->get('db_tenant');
            if (!empty($db)) {
                $db->close();
            }
        }
    },
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
