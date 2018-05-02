<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\worksystem\WorksystemTaskType */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa/worksystem', 'Worksystem Task Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="worksystem-task-type-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('rcoa', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('rcoa', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('rcoa/worksystem', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'icon',
            'des:ntext',
            'index',
            [
                'attribute' => 'is_delete',
                'value' => $model->is_delete == 0 ? '否' : '是',
            ],
            'created_at:date',
            'updated_at:date',
        ],
    ]) ?>

</div>
