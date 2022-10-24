<?php

/**
 * @var string $content
 * @var \yii\web\View $this
 */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$bundle = yiister\gentelella\assets\Asset::register($this);
$user = Yii::$app->user->identity;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="<?= Yii::$app->charset ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="nav-md">
    <?php $this->beginBody(); ?>
    <div class="container body">

        <div class="main_container">

            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">

                    <div class="navbar nav_title" style="border: 0;">
                        <a href="#" class="site_title"><?= Html::img(Yii::$app->user->identity->logo) ?> <span>Express</span></a>
                    </div>
                    <div class="clearfix"></div>

                    <br />

                    <!-- sidebar menu -->
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

                        <div class="menu_section">
                            <h3>System Menu</h3>
                            <?=
                            \yiister\gentelella\widgets\Menu::widget(
                                [
                                    "items" => [
                                        ["label" => "Home", "url" => ["/"], "icon" => "home", 'visible' => $user->isAdmin(),],
                                        [
                                            'visible' => $user->isAdmin(),
                                            "label" => "Reports",
                                            "icon" => "pie-chart",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "Find Report", "url" => ["/reports/index"]],
                                                ["label" => "Planned Route", "url" => ["/reports/planned"]],
                                                ["label" => "Daily Sales", "url" => ["/reports/sales"]],
                                                ["label" => "Monthly Sales", "url" => ["/reports/monthly-sales"]],
                                                ["label" => "User Sales", "url" => ["/reports/user-sales"], 'visible' => $user->isManager()],
                                                ["label" => "Trend Charts", "url" => ["/reports/trends"], 'visible' => $user->isManager()],
                                                ["label" => "Booking", "url" => ["/reports/booking"]],
                                                ["label" => "Promotion", "url" => ["/reports/promotion"]],
                                                ["label" => "Customer Report", "url" => ["/reports/customer"]],
                                                ["label" => "Bus Report", "url" => ["/reports/bus-report"], 'visible' => $user->isSuperAdmin()],
                                                ["label" => "System Logs", "url" => ["/reports/logs"], 'visible' => $user->isSuperAdmin()],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "System Users",
                                            "icon" => "user",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Staffs", "url" => ["/staffs/index"]],
                                                ["label" => "New Staff", "url" => ["/staffs/create"]],
                                                ["label" => "All Customers", "url" => ["/customers/index"], 'visible' => $user->isManager()],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "Cards",
                                            "icon" => "id-card",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Cards", "url" => ["/route-cards/cards"]],
                                            ],
                                        ],

                                        ["label" => "Cards", "url" => ["/route-cards/cards"], "icon" => "id-card", 'visible' => $user->isAdmin(),],
                                        ["label" => "Bookings", "url" => ["/reports/booking"], "icon" => "id-card", 'visible' => $user->isMobile(),],



                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "Route Cards",
                                            "icon" => "id-card",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Cards", "url" => ["/route-cards/index"]],
                                                ["label" => "Add Card", "url" => ["/route-cards/create"]],
                                                ["label" => "Card Logs", "url" => ["/card-logs/index"]],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "Wallets",
                                            "icon" => "money",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Wallets", "url" => ["/wallets/index"]],
                                                ["label" => "Top up", "url" => ["/wallets/topup"]],
                                                ["label" => "Wallet Logs", "url" => ["/wallet-logs/index"]],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isAdmin(),
                                            "label" => "Buses",
                                            "icon" => "bus",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Buses", "url" => ["/buses/index"]],
                                                ["label" => "New Bus", "url" => ["/buses/create"]],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "Routes",
                                            "icon" => "exchange",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Routes", "url" => ["/routes/index"]],
                                                ["label" => "New Route", "url" => ["/routes/create"]],
                                                ["label" => "Boarding Times", "url" => ["/boarding-times/index"]],
                                                ["label" => "Add Boarding Time", "url" => ["/boarding-times/create"]],
                                                ["label" => "New Stop", "url" => ["/stops/create"]],
                                                ["label" => "All Stops", "url" => ["/stops/index"]],
                                                ["label" => "Pricing", "url" => ["/pricing/index"]],
                                                ["label" => "Pricing Changes", "url" => ["/pricing/list"]],
                                                ["label" => "Add Price", "url" => ["/pricing/create"]],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isAdmin(),
                                            "label" => "Planned Routes",
                                            "icon" => "calendar",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "View All", "url" => ["/planned-routes/index"]],
                                                ["label" => "Plan Route", "url" => ["/planned-routes/create"]],
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isAdmin(),
                                            "label" => "POS Machines",
                                            "icon" => "mobile",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "View All", "url" => ["/poses/index"]],
                                                ["label" => "Add POS", "url" => ["/poses/create"]],
                                            ],
                                        ],

                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "Tickets",
                                            "icon" => "list",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "Manage", "url" => ["/tickets/index"]],
                                                ["label" => "Deleted", "url" => ["/tickets/deleted"]],
                                                ["label" => "Plan Template", "url" => ["/plan-templates"]],
                                            ],
                                        ],
                                        ["label" => "Prices", "url" => ["/pricing/list"], "icon" => "home", 'visible' => $user->isAdmin(),],

                                        [
                                            'visible' => $user->isSuperAdmin(),
                                            "label" => "System Settings",
                                            "icon" => "gear",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "View All", "url" => ["/settings/default/index"]],
                                                ["label" => "Add Setting", "url" => ["/settings/default/create"]],
                                                ["label" => "All Third Parties", "url" => ["/third-parties/index"]],
                                                ["label" => "Add Third Party", "url" => ["/third-parties/create"]]
                                            ],
                                        ],
                                        [
                                            'visible' => $user->isAdmin(),
                                            "label" => "Customers",
                                            "icon" => "user",
                                            "url" => "#",
                                            "items" => [
                                                ["label" => "All Customers", "url" => ["/customers/index"]],

                                            ],
                                        ],
                                    ],
                                ]
                            )
                            ?>
                        </div>

                    </div>
                    <!-- /sidebar menu -->

                    <!-- /menu footer buttons -->
                    <div class="sidebar-footer hidden-small">
                        <a data-toggle="tooltip" data-placement="top" title="Settings">
                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                        </a>
                        <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                            <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
                        </a>
                        <a data-toggle="tooltip" data-placement="top" title="Lock">
                            <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                        </a>

                        <?= Html::a('<span class="glyphicon glyphicon-off" aria-hidden="true"></span>', ['/site/logout'], [
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'title' => 'Logout',
                            'class' => 'btn-link'
                        ]) ?>
                    </div>
                    <!-- /menu footer buttons -->
                </div>
            </div>

            <!-- top navigation -->
            <div class="top_nav">

                <div class="nav_menu">
                    <nav class="" role="navigation">
                        <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                        </div>

                        <ul class="nav navbar-nav navbar-right">
                            <li class="">
                                <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <?= Html::img('@web/images/user.png') ?><?= Yii::$app->user->identity->name ?>
                                    <span class=" fa fa-angle-down"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-usermenu pull-right">
                                    <li><?= Html::a('Profile', ['/staffs/profile']) ?></li>
                                    <li><?= Html::a('<i class="fa fa-sign-out pull-right"></i> Log Out', ['/site/logout']) ?></li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
            <!-- /top navigation -->

            <!-- page content -->
            <div class="right_col" role="main">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]) ?>
                <div class="clearfix"></div>
                <?= Alert::widget() ?>

                <?= $content ?>
            </div>
            <!-- /page content -->
            <!-- footer content -->
            <footer>
                <div class="pull-right">
                    Copyright &copy; <?= date('Y') ?> LAP Ltd.
                </div>
                <div class="clearfix"></div>
            </footer>
            <!-- /footer content -->
        </div>

    </div>

    <div id="custom_notifications" class="custom-notifications dsp_none">
        <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
        </ul>
        <div class="clearfix"></div>
        <div id="notif-group" class="tabbed_notifications"></div>
    </div>
    <!-- /footer content -->
    <?php $this->endBody(); ?>
</body>

</html>
<?php $this->endPage(); ?>