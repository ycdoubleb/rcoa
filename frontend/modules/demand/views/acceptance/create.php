<?php

use common\models\demand\DemandAcceptance;
use frontend\modules\demand\assets\DemandAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;


/* @var $this View */
/* @var $model DemandAcceptance */

$this->title = Yii::t('rcoa/demand', 'Create Demand Acceptance');
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa/demand', 'Demand Acceptances'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="title">
    <div class="container">
        <?= Breadcrumbs::widget([
            'options' => ['class' => 'breadcrumb'],
            'homeLink' => [
                'label' => Yii::t('rcoa/demand', 'Demand Tasks'),
                'url' => ['task/index'],
            ],
            'links' => [
                [
                    'label' => $model->demandTask->course->name,
                    'url' => ['task/view', 'id' => $model->demand_task_id]
                ],
                [
                    'label' => Yii::t('rcoa/demand', 'Demand Acceptances'),
                ],
            ]
        ]);?>
    </div>
</div>

<div class="container demand-acceptance-create has-title">

    <?= $this->render('_form', [
        'model' => $model,
        'delivery' => $delivery,
        'wdArrays' => $wdArrays,
        'percentage' => $percentage,
    ]) ?>

</div>

<div class="controlbar">
    <div class="container">
        <?= Html::a(Yii::t('rcoa', 'Back'), ['task/view', 'id' => $model->demand_task_id], ['class' => 'btn btn-default']) ?>
        <?= Html::a(Yii::t('rcoa', '提交'), 'javascript:;',  ['id'=>'submit', 'class' => 'btn btn-info']) ?>
    </div>
</div>

<?php
$js = 
<<<JS
    $('#submit').click(function(){
        $('#demand-acceptance-form').submit();
    });
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    DemandAssets::register($this);
?>
