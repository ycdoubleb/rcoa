<?php

use common\models\scene\SceneBook;
use yii\helpers\Html;

/* 标题：拍摄-取消预约
 * 内容：
   您好！该拍摄预约已更新，请知悉。
   地点时间 ：【｛场地｝】｛时间｝
   修改内容 ： 有更改的内容

   马上查看(连接到任务详细页)
 */

 /* @var $model SceneBook */

?>
<style>
    div {margin: 20px 0;}
    .gray {color: #999999;}
    .normal {color: #333333;}
    .highlight {color: #f75830;}
    .title {color: #999;font-weight: bold;}
    .link-btn{display: inline-block;padding: 10px 20px;background-color: #FF6600;color: #FFF}
</style>
<div class="highlight">
    <span class="title">地点时间：</span>
    <?= Html::encode("【{$model->sceneSite->name}】".$model->date.SceneBook::$timeIndexMap[$model->time_index].$model->start_time."(".Yii::t('rcoa', 'Week ' . date('D', strtotime($model->date))).")") ?></div>

<div class="normal">
    <span class="title">课程名：</span>
    <?= Html::encode($model->course->name) ?></div>

<div class="highlight">
    <span class="title">修改内容：</span>
    <?= Html::encode(
            $content['site_name'].$content['date'].$content['time_index'].$content['start_time'].
            $content['course_name'].$content['lession_time'].$content['content_type'].$content['is_photograph'].
            $content['camera_count'].$content['teacher_id'].$content['booker_id'].$content['contacter']
        ) ?></div>

<a href="<?=$link?>" target="_black"><span class="link-btn">点击查看</span></a>