<?php

/* @var $this View */
/* @var $content string */

use common\models\System;
use common\models\User;
use frontend\assets\AppAsset;
use kartik\widgets\AlertBlock;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\web\View;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '课程中心工作平台',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'style' => 'float:left !important;',
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        [
            'label' => '首页', 
            'url' => ['/site/index'],
        ],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = [
            'label' => '登录', 
            'url' => ['/site/login'], 
        ];
    } else {
        $system = System::find()->all();
        $user = User::findOne(Yii::$app->user->id);
        foreach ($system as $key => $value) {
            $menuItems[] = [
                'label' => $value->name, 
                'url' => 
                    $value->isjump == 0  ? $value->module_link : 
                    $value->module_link.'?userId='.$user->id.'&userName='.$user->username.'&timeStamp='.(time()*1000).'&sign='.strtoupper(md5($user->id.$user->username.(time()*1000).'eeent888888rms999999')),
                'linkOptions' => [
                    'target'=> $value->isjump == 0 ? '': "_black",
                    'title' => $value->module_link != '#' ? $value->name : '即将上线',
                ]
            ];
        }
        $menuItems[] = '<li><img class=".img-responsive"  src="'.Yii::$app->user->identity->avatar.'" width="30" height="30" style="margin-top:10px;"></li>';
        $menuItems[] = [
            'label' => '登出 (' . Yii::$app->user->identity->username . ')',
            'url' => ['/site/logout'],
            'linkOptions' => ['data-method' => 'post'],
            'options' => [
                'style' => 'margin-left:-12px;'
            ],
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>
    
    <div class="content">
        <?php
            echo AlertBlock::widget([
                'useSessionFlash' => TRUE,
                'type' => AlertBlock::TYPE_GROWL,
                'delay' => 0
            ]);
        ?>
        <?= $content ?>
    </div>
    <!--
    
    <div class="container">
        
    </div>
    -->
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; 广州远程教育中心有限公司 </p>

       
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
