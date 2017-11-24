<?php

use common\widgets\webuploader\WebUploaderAsset;
use wskeee\webuploader\models\Uploadfile;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="webuploader-default-index">
    <div id="uploader" class="container">
        <?php ActiveForm::begin() ?>
        <div class="col-xs-12 col-sm-2" style="text-align: right;">文件上传：</div>
        <div id="uploader-container" class="col-xs-12 col-sm-10">
        </div>
        <?= Html::submitButton('提交',['class' => 'btn btn-default','onclick' => 'return tijiao();']) ?>
        <?php ActiveForm::end() ?>
    </div>
    <?php
    //获取flash上传组件路径
    $swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
    //获取已上传文件
    $files = json_encode(Uploadfile::find()->asArray()->all());
    ?>
    <script type='text/javascript'>
        var uploader;
        window.onload = function () {
            uploader = new Wskeee.Uploader({
                // 文件接收服务端。
                server: '/webuploader/default/upload',
                //检查文件是否存在
                checkFile: '/webuploader/default/check-file',
                //分片合并
                mergeChunks: '/webuploader/default/merge-chunks',
                //flash上传组件
                swf: '<?= $swfpath ?>' + '/Uploader.swf',
                // 上传容器
                container: '#uploader-container',
                //自动上传
                auto: false,
                //每次上传都会传到服务器的固定参数
                formData: {
                    _csrf: "<?= Yii::$app->request->csrfToken ?>",
                    //指定文件上传到的应用
                    app_path: 'mcoline',
                    //debug: 1,
                }
            });
            uploader.addCompleteFiles(<?= $files ?>);
        }
        /**
         * 上传文件完成才可以提交
         * @returns {Wskeee.Uploader.isFinish}
         */
        function tijiao(){
            //uploader,isFinish 是否已经完成所有上传
            //uploader.hasError 是否有上传错误的文件
            
            return uploader.isFinish;
        } 
    </script>
</div>