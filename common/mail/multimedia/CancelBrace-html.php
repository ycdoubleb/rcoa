<?php

use common\models\multimedia\MultimediaTask;
use yii\helpers\Html;

/**
 *  您好！{任务所属团队名称}取消了任务支撑请求!
    等级 ：{等级}
    课程名 ：{课程名称}
    任务名称 ：{任务名称}
    需求时间 ：{需求时间}

 */

 /* @var $model MultimediaTask */

?>

<div class="mail-new-multimedia">
    
    <p>您好！<b><?= Html::encode($model->createTeam->name) ?>&nbsp;</b>取消了任务支撑请求！</p>

    <p><b>等级</b>：
        <?php 
            if($model->level == MultimediaTask::LEVEL_URGENT)
                echo '<span style="color:red">'.Html::encode(MultimediaTask::$levelName[$model->level]).'</span>';
            else 
               echo Html::encode(MultimediaTask::$levelName[$model->level]);
        ?>
    </p>
    
    <p><b>课程名</b>：<?= Html::encode($model->course->name) ?></p>
    
    <p><b>任务名称</b>：<?= Html::encode($model->name) ?></p>
    
    <p><b>需求时间</b>：<span style="color:red"><?= Html::encode($model->plan_end_time) ?></span></p>
    
    <?= Html::a('马上查看', 
            Yii::$app->urlManager->createAbsoluteUrl(['/multimedia/default/view','id' => $model->id]), 
            [   
                'class'=>'btn btn-default', 
                'target'=>'_blank'
            ]) ?>
</div>

