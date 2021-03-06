<?php

use common\models\worksystem\WorksystemTask;
use frontend\modules\worksystem\assets\WorksystemAssets;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model WorksystemTask */

$this->title = Yii::t('rcoa/worksystem', 'Worksystem Tasks');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="worksystem worksystem-task-create_assign">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">选择制作人</h4>
            </div>
            <div class="modal-body">

                <?php $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'worksystem-task-form',
                        'class' => 'form-horizontal',
                    ],
                ]);?>

                <?= $form->field($model, 'external_team', [
                    'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",
                    'labelOptions' => [
                        'class' => 'col-lg-2 col-md-2 control-label form-label',
                        'style' => [
                            'padding-left' => '0',
                            'display' =>  count($teams) > 1 ? 'block' : 'none'
                         ]
                    ]
                ])->dropDownList($teams, ['prompt' => '请选择制作团队...','style ' => count($teams) > 1 ? 'display:block' : 'display:none']) ?>
                
                <div class="form-group field-worksystemoperation-des">
                    <label class="col-lg-2 col-md-2 control-label" style="color: #999999; font-weight: normal; padding-right: 0;" for="worksystemproducer-team_member_id">
                        <?= Yii::t('rcoa/worksystem', 'Producer') ?>
                    </label>
                    <div class="col-lg-10 col-md-10">
                        <?php
                            echo Select2::widget([
                                'id' => 'worksystemproducer-team_member_id',
                                'name' => 'WorksystemProducer[team_member_id][]',
                                'value' => '',
                                'data' => [],
                                'options' => [
                                    'placeholder' => '请选择制作人...',
                                //'multiple' => true
                                ],
                                'toggleAllSettings' => [
                                    'selectLabel' => '<i class="glyphicon glyphicon-ok-circle"></i> 添加全部',
                                    'unselectLabel' => '<i class="glyphicon glyphicon-remove-circle"></i> 取消全部',
                                    'selectOptions' => ['class' => 'text-success'],
                                    'unselectOptions' => ['class' => 'text-danger'],
                                ],
                                'pluginOptions' => [
                                    'tags' => false,
                                    'maximumInputLength' => 10,
                                    'allowClear' => true,
                                ],
                            ])
                        ?>
                    </div>
                    <div class="col-lg-10 col-md-10"><div class="help-block"></div></div>
                </div>

                <?= Html::activeHiddenInput($model, 'status', ['value' => WorksystemTask::STATUS_TOSTART]) ?>
                <?= Html::activeHiddenInput($model, 'progress', ['value' => WorksystemTask::$statusProgress[WorksystemTask::STATUS_TOSTART]]) ?>

                <?php ActiveForm::end(); ?>

            </div>
            <div class="modal-footer">
                <button id="submit-save" class="btn btn-primary" data-dismiss="modal" aria-label="Close">确认</button>
            </div>
        </div>
    </div>

</div>

<?php
$teamMember = json_encode($producerList);
$teamId = array_keys($teams)[0];
$js = <<<JS
    var teamMember = $teamMember;
    $("#worksystemtask-external_team").val($teamId);
    producerList($("#worksystemtask-external_team"));
    $("#worksystemtask-external_team").change(function(){
        producerList($(this));
    });    
    //下拉选择制作人
    function producerList (dom){
        $("#worksystemproducer-team_member_id").html("");
        var timeId = dom.val();
        $('<option/>').val('').text("请选择...").appendTo($("#worksystemproducer-team_member_id"));
        $.each(teamMember,function(){
            if(this['team_id'] == timeId){
                $('<option>').val(this['id']).text(this['nickname']).appendTo($("#worksystemproducer-team_member_id"));
            }
        });
    }
        
    //提交表单 
    $('#submit-save').click(function()
    {
        $('#worksystem-task-form').submit();
    });
        
JS;
$this->registerJs($js, View::POS_READY);
?>

