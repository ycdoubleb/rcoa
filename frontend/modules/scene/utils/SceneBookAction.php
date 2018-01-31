<?php

namespace frontend\modules\scene\utils;

use common\models\expert\Expert;
use common\models\scene\SceneActionLog;
use common\models\scene\SceneAppraise;
use common\models\scene\SceneBook;
use common\models\scene\SceneBookUser;
use common\models\scene\SceneSite;
use common\models\User;
use frontend\modules\scene\utils\SceneBookAction;
use wskeee\framework\models\Item;
use wskeee\framework\models\ItemType;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


class SceneBookAction 
{
   
    /**
     * 初始化类变量
     * @var SceneBookAction 
     */
    private static $instance = null;
    
    /**
     * 获取单例
     * @return SceneBookAction
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new SceneBookAction();
        }
        return self::$instance;
    }
    
    /**
     * 创建任务
     * @param SceneBook $model
     * @param array $post
     * @throws Exception
     */
    public function CreateSceneBook($model, $post)
    {
        $notice = SceneBookNotice::getInstance();
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($post != null){
                $results = $this->saveDayMultiPeriodSceneBook($model, $post);
                $this->isExistSceneBookUser($model, $post);
                $this->saveSceneBookUser($results, $post);
                foreach ($results as $book_id) {
                    $this->saveSceneActionLog([
                        'action' => '创建','title'=>'创建预约','content'=>'无','book_id'=> $book_id
                    ]);
                }
                $notice->sendShootLeaderNotification($model, '新增-'.$model->course->name, 'scene/_create_scene_book_html');
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 更新任务
     * @param SceneBook $model
     * @param array $post
     * @throws Exception
     */
    public function UpdateSceneBook($model, $post)
    {
        $content = '';
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        if($newAttr != null){
            $oldModel = SceneBook::findOne(['id' => $oldAttr['id']]);            
            //修改内容
            $content .= "修改了以下内容：\n\r".
                (isset($newAttr['course_id']) ? "课程名称：【旧】{$oldModel->course->name}>>【新】{$model->course->name}，\n\r" : null).
                (isset($newAttr['lession_time']) ? "课时：【旧】{$oldAttr['lession_time']}>>【新】{$newAttr['lession_time']}，\n\r" : null).
                (isset($newAttr['content_type']) ? "内容类型：【旧】{$oldAttr['content_type']}>>【新】{$newAttr['content_type']}，\n\r" : null).
                (isset($newAttr['is_photograph']) ? "是否拍照：【旧】".($oldAttr['is_photograph'] ? "需要" : "不需要").">>【新】".($newAttr['is_photograph'] ? "需要" : "不需要")."，\n\r" : null).
                (isset($newAttr['camera_count']) ? "机位数：【旧】{$oldAttr['camera_count']}>>【新】{$newAttr['camera_count']}，\n\r" : null).
                (isset($newAttr['start_time']) ? "开始时间：【旧】{$oldAttr['start_time']}>>【新】{$newAttr['start_time']}，\n\r" : null).
                (isset($newAttr['teacher_id']) ? "老师：【旧】{$oldModel->teacher->user->nickname}>>【新】{$model->teacher->user->nickname}，\n\r" : null).
                (isset($newAttr['booker_id']) ? "预约人：【旧】{$oldModel->booker->nickname}>>【新】{$model->booker->nickname}，\n\r" : null);
        }
        //获取接洽人
        $contacterUser = $this->getOldNewSceneUser($oldAttr['id'], ArrayHelper::getValue($post, 'SceneBookUser.user_id'));
        $oldBookUser = implode('、', ArrayHelper::getColumn($contacterUser['oldBookUser'], 'nickname'));
        $newBookUser = implode('、', ArrayHelper::getColumn($contacterUser['newBookUser'], 'nickname'));
        $content .= $oldBookUser != $newBookUser ? "接洽人：【旧】{$oldBookUser}>>【新】{$newBookUser}" : null;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->isExistSceneBookUser($model, $post);
                $this->saveSceneBookUser($model->id, $post);
                $this->saveSceneActionLog([
                    'action' => '修改','title'=>'修改预约','content'=> $content == null ? '无' : $content,
                    'book_id'=> $model->id
                ]);
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 指派任务
     * @param SceneBook $model
     * @param array $post
     * @throws Exception
     */
    public function AssignSceneBook($model, $post)
    {
        $contacter = [];$oldShootMan = [];$newShootMan = [];
        $notice = SceneBookNotice::getInstance();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        //获取接洽人
        $contacterUser = $this->getOldNewSceneUser($oldAttr['id'], ArrayHelper::getValue($post, 'SceneBookUser.user_id'));
        //获取摄影师
        $shootManUser = $this->getOldNewSceneUser($oldAttr['id'], ArrayHelper::getValue($post, 'SceneBookUser.user_id'), 2);
        $oldBookUser = implode('、', ArrayHelper::getColumn($shootManUser['oldBookUser'], 'nickname'));
        $newBookUser = implode('、', ArrayHelper::getColumn($shootManUser['newBookUser'], 'nickname'));
        $content = $oldBookUser != '' ? 
                "修改了以下内容：\n\r【旧摄影师】{$oldBookUser}，\n\r【新摄影师】{$newBookUser}" : "新增：{$newBookUser}";
        //组装接洽人用户
        foreach ($contacterUser['oldBookUser'] as $items){
            $contacter[]= [
                'nickname' => $items['nickname']."（{$items['phone']}）",
                'guid' => $items['guid'],
                'email' => $items['email']
            ];
        }
        //组装旧摄影师用户
        foreach ($shootManUser['oldBookUser'] as $items) {
            $oldShootMan[] = [
                'nickname' => $items['nickname']."（{$items['phone']}）",
                'guid' => $items['guid'],
                'email' => $items['email']
            ];
        }
        //组装新摄影师用户
        foreach ($shootManUser['newBookUser'] as $items) {
            $newShootMan[] = [
                'nickname' => $items['nickname']."（{$items['phone']}）",
                'guid' => $items['guid'],
                'email' => $items['email']
            ];
        }        
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->isExistSceneBookUser($model, $post, 2);
                $this->saveSceneBookUser($model->id, $post, 2);
                $this->saveSceneActionLog([
                    'action' => '指派','title'=> $oldBookUser == '' ? '新增指派' : '修改指派', 
                    'content'=> $content,'book_id'=> $model->id
                ]);
                if($oldShootMan == null){
                    $notice->sendAssignSceneBookUserNotification($model, $contacter, $oldShootMan, $newShootMan, '指派-'.$model->course->name, 'scene/_assign_scene_book_html');
                }else{
                    $notice->sendAssignSceneBookUserNotification($model, $contacter, $oldShootMan, $newShootMan, '更改指派-'.$model->course->name, 'scene/_change_assign_scene_book_html');
                }
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 转让任务
     * @param SceneBook $model
     * @param array $post
     * @throws Exception
     */
    public function TransferSceneBook($model, $post)
    {
        $notice = SceneBookNotice::getInstance();
        $content = ArrayHelper::getValue($post, 'content');
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveSceneActionLog([
                    'action' => '转让','title'=> '转让申请', 'content'=> $content,'book_id'=> $model->id
                ]);
                $notice->sendTransferBookerNotification($model, $content, '申请转让-'.$model->course->name, 'scene/_transfer_scene_book_html');
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 预约转让
     * @param SceneBook $model
     * @throws Exception
     */
    public function ReceiveSceneBook($model, $oldBooker)
    {
        $notice = SceneBookNotice::getInstance();
        $oldBookerName = $oldBooker->nickname."（{$oldBooker->phone}）";
        $sceneUser = $this->getOldNewSceneUser($model->id, null, null);
        $users = [
            'guid' => ArrayHelper::getColumn($sceneUser['oldBookUser'], 'guid'),
            'email' => ArrayHelper::getColumn($sceneUser['oldBookUser'], 'email'),
        ];
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveSceneActionLog([
                    'action' => '转让','title'=> '转让成功', 'content'=> '无','book_id'=> $model->id
                ]);
                $notice->sendReceiveSceneBookUserNotification($model, $oldBookerName, $users, '预约转让-'.$model->course->name, 'scene/_receive_scene_book_html');
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 取消转让任务
     * @param SceneBook $model
     * @param array $post
     * @throws Exception
     */
    public function CancelTransferSceneBook($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
    /**
     * 创建评价
     * @param array $post
     * @throws Exception
     */
    public function CreateSceneAppraise($post)
    {
        $book_id = ArrayHelper::getValue($post, 'SceneAppraise.book_id');
        $bookModel = SceneBook::findOne($book_id);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($post != null){
                $results = $this->saveSceneAppraise($post);
                //设置【接洽人】【摄影师】都评价后【状态】为【已完成】
                if(count($results) >= 6){
                    $bookModel->status = SceneBook::STATUS_COMPLETED;
                    if($bookModel->save()){
                        $this->saveSceneActionLog([
                            'action' => '结束','title'=> '任务完成', 'content'=> '无',
                            'book_id'=> $bookModel->id
                        ]);
                    }
                }else{
                    $bookModel->status = SceneBook::STATUS_APPRAISE;
                    if($bookModel->save()){
                        $this->saveSceneActionLog([
                            'action' => '评价','title'=> '新增评价', 'content'=> '无',
                            'book_id'=> $book_id
                        ]);
                    }
                }
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            throw new NotFoundHttpException("操作失败！".$ex->getMessage());
        }
    }
    
   /**
     * 添加留言操作
     * @throws Exception
     */
    public function CreateSceneMsg($model, $post)
    {
        $model->title = $model->book->course->name;
        $model->content = ArrayHelper::getValue($post, 'content');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            
            if($model->save()){
                
            }else
                throw new Exception($model->getErrors());
            
            $trans->commit();  //提交事务
            return true;
            //Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            //Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 获取旧新预约用户
     * @param string $old_book_id           
     * @param array $post_user_id           
     * @param integer $role             角色：1接洽人，2摄影师
     * @return array
     */
    protected function getOldNewSceneUser($old_book_id, $post_user_id = null, $role = 1)
    {
        $oldBookUser = [];
        $newBookUser = [];
        //旧预约用户
        $oldBookUser = (new Query())->select([
                'SceneBookUser.user_id', 'SceneBookUser.is_primary',
                'User.nickname', 'User.guid', 'User.phone', 'User.email'
            ])->from(['SceneBookUser' => SceneBookUser::tableName()])
            ->leftJoin(['User' => User::tableName()], 'User.id = SceneBookUser.user_id')
            ->where(['SceneBookUser.book_id' => $old_book_id])
            ->andWhere(['SceneBookUser.is_delete' => 0])
            ->andFilterWhere(['SceneBookUser.role' => $role])
            ->orderBy(['sort_order' => SORT_ASC])->all();
        if($post_user_id != null){
            //新预约用户
            $newBookUser = (new Query())->select(['User.nickname', 'User.guid', 'User.phone', 'User.email'])
                ->from(['User' => User::tableName()])
                ->where(['User.id' => $post_user_id])->all();
        }
        
        return [
            'oldBookUser' => $oldBookUser,
            'newBookUser' => $newBookUser
        ];
    }

    /**
     * 保存一天多选时段预约任务
     * @param SceneBook $model
     * @param array $post
     * @return array
     */
    public function saveDayMultiPeriodSceneBook($model, $post)
    {
        $values = [];
        $message = '以下所选的场次已被预约：';
        $multi_period = json_decode(ArrayHelper::getValue($post, 'SceneBook.multi_period'));
        $sceneBooks = ArrayHelper::getValue($post, 'SceneBook');
        if(!isset($sceneBooks['is_photograph'])){
            $sceneBooks['is_photograph'] = 0;
        }
        if(count($multi_period) > 1){
            //$model->status = SceneBook::STATUS_DEFAULT;
            $model->delete();
            $results = $this->isDayExistSceneBook($model, $post);
            foreach ($multi_period as $timeIndex) {
                $initial  = [
                    'id' => md5($model->site_id + $model->date + $timeIndex + rand(1,10000)),
                    'site_id' => $model->site_id,
                    'date' => $model->date,
                    'time_index' => $timeIndex,
                    'start_time' => SceneBook::$startTimeIndexMap[$timeIndex]
                ];
                unset($sceneBooks['multi_period']);
                unset($sceneBooks['start_time']);
                $values[] = array_merge($initial, array_merge($sceneBooks, [
                    'created_by' => \Yii::$app->user->id, 'created_at' => time(), 'updated_at' => time()
                ]));
            }
            
            
            //如果存在已被预约的信息，返回被预约的信息
            if($results != null){
                $timeIndexMap = SceneBook::$timeIndexMap;
                foreach ($results as $value) {
                    $message .= "\r\n场地：{$value['name']}；时间：{$value['date']} {$timeIndexMap[$value['time_index']]}；"
                        ."预约人：{$value['nickname']}（{$value['phone']}）";
                }
                throw new NotFoundHttpException("操作失败！".$message);
            }
            //添加$values数组到表里
            Yii::$app->db->createCommand()
                ->batchInsert(SceneBook::tableName(), array_keys($values[0]), $values)->execute();
                        
            //查询保存后的id
            $query = (new Query())->select(['SceneBook.id'])->from(['SceneBook' => SceneBook::tableName()]);
            $query->where([
                'SceneBook.site_id' => $model->site_id,
                'SceneBook.date' => $model->date,
                'SceneBook.time_index' => $multi_period
            ]);
            
            return ArrayHelper::getColumn($query->all(), 'id');
        } else {
            $model->save();
            return [$model->id];
        }
    }
    
    /**
     * 保存场景接洽人or摄影师用户
     * @param integer|array $book_id
     * @param array $post
     * @param integer $role                 角色：1接洽人，2摄影师
     */
    public function saveSceneBookUser($book_id, $post, $role = 1)
    {
        $values = [];
        $bookIds = !is_array($book_id) ? [$book_id] : $book_id;
        $user_ids = ArrayHelper::getValue($post, 'SceneBookUser.user_id');      //用户id
        //组装保存场景预约任务用户数据
        if($user_ids !== null){
            foreach ($bookIds as $book_id){
                foreach ($user_ids as $index => $user_id) {
                    $values[] = ['book_id' => $book_id,'role' => $role,
                        'user_id' => $user_id,'is_primary' => $index == 0 ? 1 : 0,
                        'sort_order' => $index,'is_delete' => 0,
                        'created_at' => time(),'updated_at' => time(),
                    ];
                }
            }
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            Yii::$app->db->createCommand()
                ->delete(SceneBookUser::tableName(), ['book_id' => $bookIds, 'role' => $role])->execute();
            if($bookIds != null && $user_ids != null){
                Yii::$app->db->createCommand()
                    ->batchInsert(SceneBookUser::tableName(), array_keys($values[0]), $values)->execute();
            }
            
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
        }
    }
    
    /**
     * 保存场景接洽人or摄影师用户评价
     * @param array $post
     */
    public function saveSceneAppraise($post)
    {
        $values = [];
        $appraise = ArrayHelper::getValue($post, 'SceneAppraise');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(isset($appraise['q_id'])){
                foreach ($appraise['q_id'] as $value) {
                    $values[] = [
                        'book_id' => $appraise['book_id'],'role' => $appraise['role'],
                        'q_id' => $value,'q_value' => $appraise['q_value'][$value],
                        'index' => $appraise['index'][$value],'user_id' => $appraise['user_id'],
                        'user_value' => $appraise['user_value'][$value],
                        'created_at' => time(),'updated_at' => time(),
                    ];
                }
                Yii::$app->db->createCommand()
                    ->batchInsert(SceneAppraise::tableName(), array_keys($values[0]), $values)->execute();
            }
            
            $trans->commit();  //提交事务
            $results = SceneAppraise::findAll(['book_id' => $appraise['book_id']]);
            return ArrayHelper::getColumn($results, 'q_value');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
        }
    }
    
    /**
     * 保存操作记录
     * $params[
     *   'action' => '动作',
     *   'title' => '标题',
     *   'content' => '内容',
     *   'created_by' => '创建者',
     *   'course_id' => '课程id',
     *   'relative_id' => '相关id'
     * ]
     * @param array $params                                   
     */
    public function saveSceneActionLog($params=null)
    {
         
        $action = ArrayHelper::getValue($params, 'action');                                 //动作
        $title = ArrayHelper::getValue($params, 'title');                                   //标题  
        $content = ArrayHelper::getValue($params, 'content');                               //内容
        $created_by = ArrayHelper::getValue($params, 'created_by', Yii::$app->user->id);    //创建者
        $course_id = ArrayHelper::getValue($params, 'book_id');                             //课程id
        
        //values数组
        $values = [
            'action' => $action,'title' => $title,'content' => $content,
            'created_by' => $created_by,'book_id' => $course_id,
            'created_at' => time(),'updated_at' => time(),
        ];
        /** 添加$values数组到表里 */
        Yii::$app->db->createCommand()->insert(SceneActionLog::tableName(), $values)->execute();
    }

    /**
     * 获取一天是否已经有存在的时段预约
     * @param SceneBook $model
     * @param array $post
     * @return array
     */
    public function isDayExistSceneBook($model, $post)
    {
        $values = [];
        $notStatus = [SceneBook::STATUS_DEFAULT, SceneBook::STATUS_BOOKING, SceneBook::STATUS_CANCEL];
        $multiPperiod = json_decode(ArrayHelper::getValue($post, 'SceneBook.multi_period'));
        $query = (new Query())->select(['SceneSite.name', 'SceneBook.date', 'SceneBook.time_index', 'User.nickname', 'User.phone'])
            ->from(['SceneBook' => SceneBook::tableName()]);
        $query->leftJoin(['SceneSite' => SceneSite::tableName()], 'SceneSite.id = SceneBook.site_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = SceneBook.booker_id');
        $query->where([
            'SceneBook.site_id' => $model->site_id,
            'SceneBook.date' => $model->date,
            'SceneBook.time_index' => $multiPperiod,
        ]);
        $query->andWhere(['NOT IN', 'SceneBook.status', $notStatus]);
        
        $results = $query->all();
        if($results != null){
            foreach ($results as $value) {
                if(in_array($value['time_index'], $multiPperiod))
                    $values[$value['time_index']] = $value;
            }
        }
        return $values;
    }
    
    /**
     * 判断同一时段不同场地是否有接洽人or摄影师存在
     * @param SceneBook $model
     * @param array $post
     * @param integer $role                         角色：1接洽人，2摄影师
     * @throws NotFoundHttpException
     */
    public function isExistSceneBookUser($model, $post, $role = 1)
    {
        $values = [];
        $message = '以下所选的【'.SceneBookUser::$roleName[$role].'】已存在其它预约时段：';
        //多时段选项
        $multiPperiod = json_decode(ArrayHelper::getValue($post, 'SceneBook.multi_period'));
        //提交的接洽人or摄影师用户
        $user_ids = ArrayHelper::getValue($post, 'SceneBookUser.user_id');
        //查询在同一时段是否已有相同的接洽人or摄影师
        $query = (new Query())->select([
            'SceneSite.name', 'SceneBook.date', 'SceneBook.time_index', 
            'BookerUser.nickname AS booker_name', 'BookerUser.phone AS booker_phone',
            'SceneBookUser.user_id', 'User.nickname'
            //'GROUP_CONCAT(DISTINCT SceneBookUser.user_id SEPARATOR \',\') as user_id',
            //'GROUP_CONCAT(DISTINCT User.nickname SEPARATOR \',\') as user_name'
        ])->from(['SceneBook' => SceneBook::tableName()]);
        //关联场地查询
        $query->leftJoin(['SceneSite' => SceneSite::tableName()], 'SceneSite.id = SceneBook.site_id');
        //关联场景预约用户查询
        $query->leftJoin(['SceneBookUser' => SceneBookUser::tableName()], 
               'SceneBookUser.book_id = SceneBook.id AND SceneBookUser.role = '.$role);
        //关联用户查询
        $query->leftJoin(['BookerUser' => User::tableName()], 'BookerUser.id = SceneBook.booker_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = SceneBookUser.user_id');
        //条件查询
        $query->where([
            'SceneBook.date' => $model->date,
            'SceneBook.time_index' => $multiPperiod
        ]);
        $results = $query->all();  //查询结果
        //循环判断已经存在的预约用户信息
        foreach ($results as $value) {
            foreach($user_ids as $user_id){
                if($value['user_id'] == $user_id){
                    $values[$value['nickname']] = $value;
                }
            }
            unset($values[$value['nickname']]['user_id']);
            unset($values[$value['nickname']]['nickname']);
        }
        //如果存在已被预约的用户信息，返回被预约的信息
        if($values != null){
            $timeIndexMap = SceneBook::$timeIndexMap;
            foreach ($values as $index => $item) {
                $message .= "\r\n{$index}：【场地：{$item['name']}；时间：{$item['date']} {$timeIndexMap[$item['time_index']]}；"
                            ."预约人：{$item['booker_name']}（{$item['booker_phone']}）】";
            }
            throw new NotFoundHttpException("操作失败！".$message);
        }
    }


    /**
     * 获取已存在的场景评价
     * @param string $book_id
     * @return array
     */
    public static function getExistSceneAppraise($book_id)
    {
        $results = [];
        $query = (new Query())->select(['role','q_id', 'user_value'])
            ->from(SceneAppraise::tableName())->where(['book_id' => $book_id]);
        
        foreach ($query->all() as $value){
            $results[$value['role']][$value['q_id']] = [
                'user_value' => $value['user_value'],
            ];
        }
        
        return $results;
    }
}
