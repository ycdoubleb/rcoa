<?php

use common\models\scene\SceneAppraise;
use common\models\scene\SceneBookUser;
use frontend\modules\scene\assets\SceneAsset;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model SceneAppraise */

$this->title = Yii::t('app', 'Create Scene Appraise');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Scene Appraises'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scene-appraise-create scene-appraise">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php
                    $roleName = '';
                    foreach($roleSubjects['role'] as $role){
                        $roleName .= '【'.SceneBookUser::$roleName[$role].'】';
                    }
                ?>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode("对{$roleName}评价") ?></h4>
            </div>
            <div class="modal-body scene" style="max-height: 500px; overflow-y: auto">
                <?= $this->render('_form', [
                    'model' => $model,
                    'roleSubjects' => $roleSubjects,
                    'appraiseResults' => $appraiseResults
                ]) ?>
            </div>
            <div class="modal-footer">
                <?php
                 
                    foreach($roleSubjects['role'] as $key => $role){
                        if($key > 0) continue;
                        if(count($appraiseResults['results']) > 0 && isset($appraiseResults['results'][$role])){
                            echo Html::button(Yii::t('app', 'Close'), ['id'=>'close','class'=>'btn btn-danger',
                                    'data-dismiss'=>'modal','aria-label'=>'Close']);
                        }else{
                            echo Html::button(Yii::t('app', 'Submit'), ['id'=>'submitsave','class'=>'btn btn-primary',
                                'data-dismiss'=>'modal','aria-label'=>'Close','onclick' => 'submitsave();']);
                        }
                    }   
                    
                ?>
            </div>
        </div>
    </div>
</div>

<?php

$js = 
<<<JS
    
    //提交表单
    window.submitsave = function(){
        $("#scene-book-form").submit();
    };
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    SceneAsset::register($this);
?>