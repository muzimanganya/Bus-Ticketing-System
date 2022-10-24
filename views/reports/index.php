<?php
/* @var $this yii\web\View */
$this->title = 'Find Report'
?>
<div class="panel panel-info">
    <div class="panel-heading">Select the report you like, the relevant date and click submit to see results.<b>NOTE</b> that reference is required by SOME reports like Client report. It is completely Optional</div>
    <div class="panel-body"><?= $this->render('_findReport', ['model'=>$model]) ?></div>
    <div class="panel-footer"><small><a href='http://hosannahighertech.co.tz'>Created by LAP. Ltd</a>  <a class='pull-right' href='mailto:sales@hosannahighertech.co.tz'></a></small></div>
</div>