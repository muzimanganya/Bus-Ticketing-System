<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ThirdParty */

$this->title = Yii::t('app', 'New Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Third Parties'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="third-party-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
