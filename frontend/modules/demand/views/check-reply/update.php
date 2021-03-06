<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\demand\DemandCheckReply */

$this->title = Yii::t('rcoa/demand', 'Update {modelClass}: ', [
    'modelClass' => 'Demand Check Reply',
]) . $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa/demand', 'Demand Check Replies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('rcoa/demand', 'Update');
?>
<div class="demand-check-reply-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
