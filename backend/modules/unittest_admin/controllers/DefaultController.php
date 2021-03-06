<?php

namespace backend\modules\unittest_admin\controllers;

use common\models\team\TeamCategory;
use wskeee\ee\EeManager;
use wskeee\rbac\RbacManager;
use wskeee\team\TeamMemberTool;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `unittest` module
 */
class DefaultController extends Controller {

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {

        return $this->render('index');
    }

    /**
     * 打印php
     */
    public function actionPhpinfo() {
        phpinfo();
        exit;
    }

    /**
     * 邮件测试
     */
    public function actionMailTest() {
        
        return $this->render('mail-html');
        //主题 
        $subject = "邮件测试！";
        //查找所有摄影组长
        //所有摄影师组长ee
        $receivers_ee = '101463731,101463735';

        //所有摄影师组长邮箱地址
        $receivers_mail = ['heyangchao@eenet.com', 'wskeee@163.com'];
        //var_dump($receivers_mail);exit;
        //发送ee消息 
        EeManager::seedEe($receivers_ee, $subject, 'ee测试，测试！');

        //var_dump(\Yii::$app->mailer);
        //发送邮件消息 
        $mail = Yii::$app->mailer->compose()
                ->setTo($receivers_mail)
                ->setSubject($subject)
                ->setTextBody('邮件测试！！！')
                ->send();
        echo $mail ? '成功！' : '失败！';
    }

    /**
     * 获取角色下的所有用户
     * @param type $itemName
     */
    public function actionGetUserByRole($itemName) {
        /* @var $authManager RbacManager */
        $authManager = Yii::$app->authManager;
        //var_dump(\yii\helpers\ArrayHelper::map($authManager->getItemUsers($itemName), 'id', 'nickname'));
        var_dump($authManager->getItemUserList($itemName));
    }
    
    /**
     * 测试团队管理
     */
    public function actionTestTeam(){
        TeamMemberTool::getInstance()->invalidateCache();
        TeamMemberTool::getInstance()->loadFromCache();
        
        echo '<h2>获取所有分类 getCategorys()</h2>';
        $categorys = TeamMemberTool::getInstance()->getCategorys();
        var_dump($categorys);
        
        echo '<h2>获取产品中心分类 getCategoryById(TeamCategory::TYPE_PRODUCT_CENTER)</h2>';
        $product_center_category = TeamMemberTool::getInstance()->getCategoryById(TeamCategory::TYPE_PRODUCT_CENTER);
        var_dump($product_center_category);
        
        echo '<h2>获取课程中心开发团队 getTeamsByCategoryId(TeamCategory::TYPE_CCOA_DEV_TEAM)</h2>';
        $dev_teams = TeamMemberTool::getInstance()->getTeamsByCategoryId(TeamCategory::TYPE_CCOA_DEV_TEAM);
        var_dump($dev_teams);
        
        echo '<h2>获取团队1数据 getTeamById(1)</h2>';
        $team1 = TeamMemberTool::getInstance()->getTeamById(1);
        var_dump($team1);
        
        echo '<h2>获取团队1的成员数据 getTeamMembersByTeamId(1)</h2>';
        $teammembers = TeamMemberTool::getInstance()->getTeamMembersByTeamId(1);
        var_dump($teammembers);
        
        echo '<h2>获取团队成员id为[1,2]的成员详细 getTeammemberById([1,2])</h2>';
        $someTeammembers = TeamMemberTool::getInstance()->getTeammemberById([1,2]);
        var_dump($someTeammembers);
        
        echo '<h2>获取用户所在团队 getUserTeam(\'d9d4f1ffd12afbe22e80fe1d6020c7c9\')</h2>';
        var_dump(TeamMemberTool::getInstance()->getUserTeam('d9d4f1ffd12afbe22e80fe1d6020c7c9'));
        
        echo '<h2>获取指定分类下用户所在团队 getUserTeam(\'d9d4f1ffd12afbe22e80fe1d6020c7c9\',TeamCategory::TYPE_CCOA_DEV_TEAM)</h2>';
        var_dump(TeamMemberTool::getInstance()->getUserTeam('d9d4f1ffd12afbe22e80fe1d6020c7c9',TeamCategory::TYPE_CCOA_DEV_TEAM));
        
        echo '<h2>获取用户所有团成员 getUserTeamMembers(\'36aa1fcd1f89849aede1e63aec86a7b8\')</h2>';
        var_dump(TeamMemberTool::getInstance()->getUserTeamMembers('36aa1fcd1f89849aede1e63aec86a7b8'));
        //var_dump($categorys,$product_center_category,$dev_teams,$team1,$teammanbers,$someTeammembers);
        
        echo '<h2>id=1的【团队】的所有【团队成员】 getTeamMembersByTeamId(1)</h2>';
        var_dump(TeamMemberTool::getInstance()->getTeamMembersByTeamId(1));
        echo '<h3>检查用户是否属于团队 isContaineForTeam(\'36aa1fcd1f89849aede1e63aec86a7b8\', 1)</h3>';
        var_dump(TeamMemberTool::getInstance()->isContaineForTeam('36aa1fcd1f89849aede1e63aec86a7b8', 1));
        echo '<h3>检查用户是否属于团队 isContaineForTeam(\'f79b8baa143765a6dc9812c249aadd59\', 1)</h3>';
        var_dump(TeamMemberTool::getInstance()->isContaineForTeam('f79b8baa143765a6dc9812c249aadd59', 1));
        
        echo '<h2>【分类id】=3的所有【团队】 getTeamsByCategoryId(\'product_center\')</h2>';
        var_dump(TeamMemberTool::getInstance()->getTeamsByCategoryId('product_center'));
        echo '<h3>检查用户（翁二娣不属于产品中心）是否属于团队 isContaineForCategory(\'36aa1fcd1f89849aede1e63aec86a7b8\', \'product_center\')</h3>';
        var_dump(TeamMemberTool::getInstance()->isContaineForCategory('36aa1fcd1f89849aede1e63aec86a7b8', 'product_center'));
        echo '<h3>检查用户（周篆霞属于产品中心）是否属于团队 isContaineForCategory(\'ef3c21bbe97e1e9e95f2a4c46ec198fa\', \'product_center\')</h3>';
        var_dump(TeamMemberTool::getInstance()->isContaineForCategory('ef3c21bbe97e1e9e95f2a4c46ec198fa', 'product_center'));
    }
}
