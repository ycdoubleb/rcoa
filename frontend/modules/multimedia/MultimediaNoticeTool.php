<?php
namespace frontend\modules\multimedia;

use common\models\multimedia\MultimediaAssignTeam;
use common\models\multimedia\MultimediaProducer;
use common\models\multimedia\MultimediaTask;
use common\wskeee\job\JobManager;
use wskeee\ee\EeManager;
use Yii;
use yii\helpers\ArrayHelper;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class MultimediaNoticeTool {
   
    /**
     * 获取团队指派人
     * @param type $team        团队Id
     * @return type
     */
    public function getAssignPerson($teamId)
    {
        /* @var $assignPerson MultimediaAssignTeam */
        $assignPerson = MultimediaAssignTeam::find()
                        ->where(['team_id' => $teamId])
                        ->one();
        $assignUser = [
            'u_id' => $assignPerson->u_id,
            'ee' => $assignPerson->assignUser->ee,
            'email' => $assignPerson->assignUser->email
        ];
        return $assignUser;
    }
    
    /**
     * 获取制作人
     * @param type $taskId      任务ID
     * @return type
     */
    public function getProducer($taskId)
    {
        $producers = MultimediaProducer::find()
                    ->where(['task_id' => $taskId])
                    ->with('producer')
                    ->with('task')
                    ->all();
        $producer = [];
        foreach ($producers as $value) {
            /* @var $value MultimediaProducer */
            $producer[] = [
                'u_id' => $value->u_id,
                'name' => $value->producer->u->nickname,
                'ee' => $value->producer->u->ee,
                'email' => $value->producer->u->email
            ];
        }
        return $producer;
    }

    /**
     * 给所在团队指派人 发送 ee通知 email
     * @param type $model
     * @param type $mode        标题模式
     * @param type $views       视图
     */
    public function sendAssignPersonNotification($model, $mode, $views){
        /* @var $model MultimediaTask */
        $assignPerson = $this->getAssignPerson($model->make_team);
        //传进view 模板参数
        $params = [
            'model' => $model,
        ];
        //主题 
        $subject = "多媒体-".$mode;
        //团队指派人ee
        $assignPerson_ee = ArrayHelper::getValue($assignPerson, 'ee');
        //团队指派人邮箱地址
        $assignPerson_email = ArrayHelper::getValue($assignPerson, 'email');
        //发送ee消息 
        EeManager::sendEeByView($views, $params, $assignPerson_ee, $subject);
        //发送邮件消息 
        /*Yii::$app->mailer->compose($views, $params)
            ->setTo($receivers_mail)
            ->setSubject($subject)
            ->send();*/
    }
    
    /**
     * 给创建者 发送 ee通知 email
     * @param type $model
     * @param type $mode        标题模式
     * @param type $views       视图
     */
    public  function sendCreateByNotification($model, $mode, $views){
        /* @var $model MultimediaTask */
        $producer = ArrayHelper::getColumn($this->getProducer($model->id), 'name');
        //传进view 模板参数
        $params = [
            'model' => $model,
            'producer' => $producer,
        ];
        //主题
        $subject = "多媒体-".$mode;
        //查找编导ee和mail 
        $createBy_ee = $model->createBy->ee;
        $createBy_mail = $model->createBy->email;
         //发送ee消息
        EeManager::sendEeByView($views, $params,$createBy_ee, $subject);
        //发送邮件消息
        /*Yii::$app->mailer->compose($views, $params)
            ->setTo($shootBooker_mail)
            ->setSubject($subject)
            ->send();*/
    }
    
    /**
     * 给制作人 发送 ee通知 email
     * @param type $model
     * @param type $mode        标题模式
     * @param type $views       视图
     */
    public  function sendProducerNotification($model, $mode, $views){
        /* @var $model MultimediaTask */
        $producers = $this->getProducer($model->id);
        //传进view 模板参数 
         $params = [
            'model' => $model,
        ];
        //主题 
        $subject = "拍摄-".$mode."-".$model->fwCourse->name;
        //查找接洽人ee和mail 
        $producer_ee = array_filter(ArrayHelper::getColumn($producers, 'ee'));
        $producer_mail = array_filter(ArrayHelper::getColumn($producers, 'email'));
        //发送ee消息
        EeManager::sendEeByView($views, $params,$producer_ee, $subject);
        //发送邮件消息 
        /*Yii::$app->mailer->compose($views, $params)
            ->setTo($shootContacter_mail)
            ->setSubject($subject)
            ->send();*/
    }
    
    /**
     * jobManager 添加用户任务通知关联
     * @param type $model
     * @param type $post
     */
    public function saveJobManager($model){
        /* @var $jobManager JobManager */
        $jobManager = Yii::$app->get('jobManager');
        /* @var $model MultimediaTask */
        $assignPerson = $this->getAssignPerson($model->make_team);
        $assignPersonId = ArrayHelper::getValue($assignPerson, 'u_id');
        
        //创建job表任务
        $jobManager->createJob(10, $model->id, $model->name, 
                '/multimedia/default/view?id='.$model->id, $model->getStatusName(), $model->progress);
        //添加通知
        $jobManager->addNotification(10, $model->id, [$model->create_by, $assignPersonId]);
    }
    
    /**
     * 设置指派摄影师用户任务通知关联
     * @param type $model
     * @param type $post
     */
    public  function setAssignNotification($model, $post){
        /* @var $jobManager JobManager */
        $jobManager = Yii::$app->get('jobManager');
        /* @var $model MultimediaTask */
        $producer = $this->getProducer($model->id);
        $producerId = array_filter(ArrayHelper::getColumn($producer, 'id'));
       
        //更新任务通知表
        $jobManager->updateJob(10, $model->id, ['progress'=> $model->progress, 'status' => $model->getStatusName()]); 
        //清空用户任务通知关联
        $jobManager->removeNotification(10, $model->id, $producerId);
        //添加用户任务通知关联
        $jobManager->addNotification(10, $model->id, $post);
    }
    
    /**
     * jobManager 取消用户任务通知关联
     * @param type $model
     */
    public  function cancelJobManager($model){
        /* @var $jobManager JobManager */
        $jobManager = Yii::$app->get('jobManager');
        /* @var $model MultimediaTask */
        $assignPerson = $this->getAssignPerson($model->make_team);
        $assignPersonId = ArrayHelper::getValue($assignPerson, 'u_id');
        $producer = $this->getProducer($model->id);
        $producerId = array_filter(ArrayHelper::getColumn($producer, 'id'));
        //全并两个数组的值
        $jobUserAll = ArrayHelper::merge([$model->create_by, $assignPersonId], $producerId);
        
        //修改job表任务
        $jobManager->updateJob(10,$model->id,['progress'=> $model->progress, 'status'=>$model->getStatusName()]); 
        //修改通知
        $jobManager->cancelNotification(10, $model->id, $jobUserAll);
    }
}