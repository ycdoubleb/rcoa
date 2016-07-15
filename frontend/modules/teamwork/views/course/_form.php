<?php

use common\models\teamwork\CourseManage;
use kartik\datecontrol\DateControl;
use kartik\widgets\Select2;
use kartik\widgets\TouchSpin;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model CourseManage */
/* @var $form ActiveForm */

?>

<div class="course-manage-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'course-manage-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n<div class=\"col-lg-10 col-md-10\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label',
                'style'=>[
                    'color'=>'#999999',
                    'font-weight'=>'normal', 
                    'padding-left' => 0,
                    'padding-right' => 0,
                ]
            ],  
        ], 
    ]); ?>

    <?= $form->field($model, 'project.item_type_id')->textInput([
        'value' => $model->project->itemType->name, 'disabled' => 'disabled'
    ]) ?>
    
    <?= $form->field($model, 'project.item_id')->textInput([
        'value' => $model->project->item->name, 'disabled' => 'disabled'
    ]) ?>
    
    <?= $form->field($model, 'project.item_child_id')->textInput([
        'value' => $model->project->itemChild->name, 'disabled' => 'disabled'
    ]) ?>
    
    <?= $form->field($model, 'course_id')->widget(Select2::classname(), [
        'data' => $courses, 'options' => ['placeholder' => '请选择...']
    ]) ?>
    
    <?= $form->field($model, 'teacher')->widget(Select2::classname(), [
        'data' => $teachers, 'options' => ['placeholder' => '请选择...']
    ]) ?>
    
    <?= $form->field($model, 'lession_time')->widget(TouchSpin::classname(),  [
            'pluginOptions' => [
                'placeholder' => '学时 ...',
                'min' => 1,
            ],
    ])?>
    
    <?= $form->field($model, 'weekly_editors_people')->widget(Select2::classname(), [
        'data' => $weeklyEditors, 'options' => ['placeholder' => '请选择...']
    ]) ?>
    
    <?php
        echo Html::beginTag('div', ['class' => 'form-group field-itemmanage-forecast_time has-success']);
            echo Html::beginTag('label', [
                    'class' => 'col-lg-1 col-md-1 control-label', 
                    'style' => 'color: #999999; font-weight: normal; padding-left: 0; padding-right: 0;',
                    'for' => 'coursemanage-plan_start_time'
                ]).Yii::t('rcoa/teamwork', 'Plan Start Time').Html::endTag('label');
            echo Html::beginTag('div', ['class' => 'col-sm-4']);
                echo DateControl::widget([
                    'name' => 'CourseManage[plan_start_time]',
                    'value' => $model->isNewRecord ? date('Y-m-d H:i', time()) : $model->plan_start_time, 
                    'type'=> DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'yyyy-MM-dd H:i',
                    'saveFormat' => 'yyyy-MM-dd H:i',
                    'ajaxConversion'=> true,
                    'autoWidget' => true,
                    'readonly' => true,
                    'options' => [
                        'pluginOptions' => [
                            'autoclose' => true,
                        ],
                    ],
                ]);
                echo Html::beginTag('div', ['class' => 'col-lg-10 col-md-10']).Html::beginTag('div', ['class' => 'help-block']).Html::endTag('div').Html::endTag('div');
            echo Html::endTag('div');
        echo Html::endTag('div');
    ?>
    
    <?php
        echo Html::beginTag('div', ['class' => 'form-group field-itemmanage-forecast_time has-success']);
            echo Html::beginTag('label', [
                    'class' => 'col-lg-1 col-md-1 control-label', 
                    'style' => 'color: #999999; font-weight: normal; padding-left: 0; padding-right: 0;',
                    'for' => 'coursemanage-plan_end_time'
                ]).Yii::t('rcoa/teamwork', 'Plan End Time').Html::endTag('label');
            echo Html::beginTag('div', ['class' => 'col-sm-4']);
                echo DateControl::widget([
                    'name' => 'CourseManage[plan_end_time]',
                    'value' => $model->isNewRecord ? date('Y-m-d H:i', time()) : $model->plan_end_time, 
                    'type'=> DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'yyyy-MM-dd H:i',
                    'saveFormat' => 'yyyy-MM-dd H:i',
                    'ajaxConversion'=> true,
                    'autoWidget' => true,
                    'readonly' => true,
                    'options' => [
                        'pluginOptions' => [
                            'autoclose' => true,
                        ],
                    ],
                ]);
                echo Html::beginTag('div', ['class' => 'col-lg-10 col-md-10']).Html::beginTag('div', ['class' => 'help-block']).Html::endTag('div').Html::endTag('div');
            echo Html::endTag('div');
        echo Html::endTag('div');
    ?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 4]) ?>
    
    <?= $form->field($model, 'path')->textInput(['placeholder' => '存储服务器路径...']) ?>
    
    <?php
        echo Html::beginTag('div', ['class' => 'form-group field-courseproducer-producer has-success']);
             echo Html::beginTag('label', [
                 'class' => 'col-lg-1 col-md-1 control-label',
                 'style' => 'color: #999999; font-weight: normal; padding-left: 0; padding-right: 0;',
                 'for' => 'courseproducer-producer'
                ]).'资源制作人'.Html::endTag('label');
             echo Html::beginTag('div', ['class' => 'col-lg-10 col-md-10']);
                echo Select2::widget([
                    'name' => 'producer',
                    'value' => array_keys($producer),
                    'data' => $producerList,
                    'options' => [
                        'placeholder' => 'Select a state ...',
                        'multiple' => true,
                    ],
                    'pluginOptions' => [
                        //'templateResult' => new JsExpression('format'),
                        //'templateSelection' => new JsExpression('format'),
                        'escapeMarkup' => new JsExpression("function(m) { return m; }"),
                        'allowClear' => true
                    ],
                    'pluginEvents' => [
                        'change' => "function() { log($(this)); }",
                    ],
                ]);
                
             echo Html::endTag('div');           
             echo Html::beginTag('div', ['class' => 'col-lg-10 col-md-10']).Html::beginTag('div', ['class' => 'help-block']).
                    Html::endTag('div').Html::endTag('div');
        echo Html::endTag('div');

    ?>
    
    <?php ActiveForm::end(); ?>

</div>


<?php
$url = Yii::$app->urlManager->baseUrl . '/images/flags/';
$format = 
<<< SCRIPT
    function format(state) {
        if (!state.id) return state.text; // optgroup
        src = '$url' +  state.id.toLowerCase() + '.png'
        return '<img class="flag" src="' + src + '"/>' + state.text;
    }
SCRIPT;
    //$this->registerJs($format, View::POS_HEAD);
$js = 
<<<JS
    function log(value){
        var option = value.children().children()
        console.log(option);
        $('<option/>').appendTo($("#coursemanage-weekly_editors_people"));
        
        $(option).each(function(index, element){
            if(element.selected == true && element.defaultSelected == false)
                $('<option>').val(element.value).text(element.text).appendTo($("#coursemanage-weekly_editors_people"));
        });
        
       
    }
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>
