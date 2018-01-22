<?php

namespace frontend\modules\scene\controllers;

use common\models\expert\Expert;
use common\models\scene\SceneBook;
use common\models\scene\SceneBookUser;
use common\models\scene\SceneMessage;
use common\models\scene\SceneSite;
use common\models\scene\searchs\SceneAppraiseSearch;
use common\models\scene\searchs\SceneBookSearch;
use common\models\scene\searchs\SceneMessageSearch;
use common\models\User;
use frontend\modules\scene\utils\SceneBookAction;
use wskeee\framework\FrameworkManager;
use wskeee\framework\models\ItemType;
use wskeee\rbac\RbacManager;
use wskeee\rbac\RbacName;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SceneBookController implements the CRUD actions for SceneBook model.
 */
class SceneBookController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SceneBook models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SceneBookSearch();
        $sceneSite = $this->getSceneSite();
        $firstSite = array_keys(reset($sceneSite));       //获取场景的第一个场地
        $results = $searchModel->searchModel(Yii::$app->request->queryParams, $firstSite);
        
        return $this->render('index', [
            //'searchModel' => $searchModel,
            'filter' => $results['filters'],
            'dataProvider' => $results['data'],
            'sceneSite' => $sceneSite,
            'firstSite' => $firstSite,
            'sceneBookUser' => $this->getSceneBookUser(ArrayHelper::getColumn($results['data']->allModels, 'id')),
        ]);
    }

    /**
     * Displays a single SceneBook model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $msgSearch = new SceneMessageSearch();
        $appraiseSearch = new SceneAppraiseSearch();
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $msgSearch->search(['book_id' => $id]),
            'sceneBookUser' => $this->getSceneBookUser($id),
            'msgNum' => count($this->getSceneMessage($id)),
            'isRole' => $this->isOwnSceneBookRole($id),
            'appraiseResult' => $appraiseSearch->search(['book_id' => $id]),
        ]);
    }

    /**
     * Creates a new SceneBook model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SceneBook(array_merge(Yii::$app->request->queryParams, ['created_by' => \Yii::$app->user->id]));
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            SceneBookAction::getInstance()->CreateSceneBook($model, Yii::$app->request->post());
            return $this->redirect(array_merge(['index'], Yii::$app->request->queryParams));
        } else {
            return $this->render('create', [
                'model' => $model,
                'business' => $this->getBasicDataBusiness(),
                'levels' => $this->getBasicDataLevel(),
                'professions' => !$model->level_id ? $this->getBasicDataItem($model->level_id) : [],
                'courses' => !$model->profession_id ? $this->getBasicDataItem($model->profession_id) : [],
                'teachers' => $this->getExpert(),
                'contentTypeMap' => $this->getSceneSite($model->site_id),
                'createSceneBookUser' => $this->getCreateSceneBookUser($model),
                'existSceneBookUser' => $this->getExistSceneBookUser($model),
            ]);
        }
    }

    /**
     * Updates an existing SceneBook model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            SceneBookAction::getInstance()->UpdateSceneBook($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'business' => $this->getBasicDataBusiness(),
                'levels' => $this->getBasicDataLevel(),
                'professions' => $this->getBasicDataItem($model->level_id),
                'courses' => $this->getBasicDataItem($model->profession_id),
                'teachers' => $this->getExpert(),
                'contentTypeMap' => $this->getSceneSite($model->site_id),
                'createSceneBookUser' => $this->getCreateSceneBookUser($model),
                'existSceneBookUser' => $this->getExistSceneBookUser($model),
            ]);
        }
    }
    
    /**
     * Assign an existing SceneBook model.
     * If assign is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionAssign($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            SceneBookAction::getInstance()->AssignSceneBook($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('assign', [
                'model' => $model,
                'createSceneBookUser' => $this->getShootManUser($model),
                'existSceneBookUser' => $this->getExistSceneBookUser($model, 2),
            ]);
        }
    }

    /**
     * Deletes an existing SceneBook model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Lists all SceneMessage models.
     * @return mixed
     */
    public function actionMsgIndex()
    {
        $searchModel = new SceneMessageSearch();
        
        return $this->renderAjax('_msg_index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
    }
    
    /**
     * Creates a new SceneMessage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param string $book_id
     * @return mixed
     */
    public function actionCreateMsg($book_id)
    {
        $model = new SceneMessage(['book_id' => $book_id]);
        $model->loadDefaultValues();
        
        $num = 0;
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $result = SceneBookAction::getInstance()->CreateSceneMsg($model, Yii::$app->request->post());
            
            return [
                'code'=> $result ? 200 : 404,
                'num' => $result ? $num + 1: $num,
                'message' => ''
            ];
        } else {
            return $this->goBack(['scene-book/view', 'id' => $model->book_id]);
        }
    }
    
    
    /**
     * 获取老师信息
     * @param string $user_id
     * @return array
     */
    public function actionTeacherSearch($user_id)
    {
        if(\Yii::$app->request->isPost){
            \Yii::$app->getResponse()->format = 'json';
            $query = (new Query())->select(['Expert.personal_image', 'User.phone', 'User.email'])
                ->from(['Expert' => Expert::tableName()]);
            $query->leftJoin(['User' => User::tableName()], 'User.id = Expert.u_id');
            $query->where(['Expert.u_id' => $user_id]);
            
            return [
                'code' =>  200,
                'data'=> $query->one(),
                'message' => '访问成功！',
            ];
        }
            
        return [
            'code' =>  404,
            'data'=> [],
            'message' => '访问失败！',
        ];
    }
    
    /**
     * Finds the SceneBook model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return SceneBook the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SceneBook::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    /**
     * 获取场景场地
     * @param integer $site_id
     * @return array
     */
    public function getSceneSite($site_id = null)
    {
        $query = (new Query())->select(['id', 'name', 'area', 'content_type'])
            ->from(SceneSite::tableName());
        $query->filterWhere(['id' => $site_id]);
        $results = $query->all();
        
        if($site_id == null){
            return ArrayHelper::map($results, 'id', 'name', 'area');
        }else {
            $contentTypeMap = [];
            $content_type = isset($results[0]) ? ArrayHelper::getValue($results[0], 'content_type') : "";
            $contents = explode(',', $content_type);
            foreach ($contents as $value) {
                $contentTypeMap[$value] = $value;
            }
           
            return $contentTypeMap;
        }
    }
    
    /**
     * 获取行业
     * @return array
     */
    protected function getBasicDataBusiness()
    {
        $business = ItemType::find()->all();
        return ArrayHelper::map($business, 'id','name');
    }
    
    /**
     * 获取层次/类型
     * @return array
     */
    protected function getBasicDataLevel()
    {
        /* @var $fwManager FrameworkManager */
        $fwManager = Yii::$app->get('fwManager');
        return ArrayHelper::map($fwManager->getColleges(), 'id', 'name');
    }
    
    
    /**
     * 获取专业/工种 or 课程
     * @param integer $itemId
     * @return array
     */
    protected function getBasicDataItem($itemId)
    {
        /* @var $fwManager FrameworkManager */
        $fwManager = Yii::$app->get('fwManager');
        return ArrayHelper::map($fwManager->getChildren($itemId), 'id', 'name');
    }  
   
    /**
     * 获取专家库
     * @return array
     */
    protected function getExpert()
    {
        $expert = Expert::find()->with('user')->all();
        return ArrayHelper::map($expert, 'u_id','user.nickname');
    }
    
    /**
     * 获取预约人和接洽人用户列表
     * @param SceneBook $model
     * @return array
     */
    protected function getCreateSceneBookUser($model)
    {
        $bookUser = [];
        $query = (new Query())->select(['id', 'nickname'])
            ->from(['User' => User::tableName()]);
        $query->where(['NOT IN', 'id', array_keys($this->getExpert())]);
        $query->andWhere(['status' => 10]);
        
        $bookUsers = $this->getSceneBookUser($model->id);
        if(isset($bookUsers[$model->id])){
            foreach ($bookUsers[$model->id] as $user) {
                if($user['role'] == 1)
                    $bookUser[$user['id']] = $user['nickname'];
            }
        }
        
        $createUser = ArrayHelper::map($query->all(), 'id', 'nickname');
        $existUser = $this->getExistSceneBookUser($model);
        
        return ArrayHelper::merge($bookUser, array_diff($createUser, $existUser));
    }
    
    /**
     * 获取摄影师用户列表
     * @param SceneBook $model
     * @return array
     */
    protected function getShootManUser($model)
    {
        $bookUser = [];
        /* @var $rbacManager RbacManager */
        $rbacManager = Yii::$app->authManager;
        //获取角色为摄影师的所有用户
        $roleUser = $rbacManager->getItemUsers(RbacName::ROLE_SHOOT_MAN);
        
        $bookUsers = $this->getSceneBookUser($model->id);
        if(isset($bookUsers[$model->id])){
            foreach ($bookUsers[$model->id] as $user) {
                if($user['role'] == 2)
                    $bookUser[$user['id']] = $user['nickname'];
            }
        }
        $createUser = ArrayHelper::map($roleUser, 'id', 'nickname');
        $existUser = $this->getExistSceneBookUser($model, 2);
        
        return ArrayHelper::merge($bookUser, array_diff($createUser, $existUser));
    }
    
    /**
     * 获取同一时间段已存在场景预约用户
     * @param SceneBook $model
     * @param integer $role
     * @return array
     */
    protected function getExistSceneBookUser($model, $role = 1)
    {
        //查询同一时间段所有数据
        $sceneBook = (new Query())->select(['SceneBook.id'])
            ->from(['SceneBook' => SceneBook::tableName()]);
        $sceneBook->where(['between', 'SceneBook.date', $model->date, date('Y-m-d',strtotime('+1 days'.$model->date))]);
        $sceneBook->andWhere(['SceneBook.time_index' => $model->time_index]);
        //查询同一时间段是否已存在指派用户数据
        $query = (new Query())->select(['User.id AS user_id', 'User.nickname'])
            ->from(['SceneBookUser' => SceneBookUser::tableName()]);
        $query->leftJoin(['User' => User::tableName()], 'User.id = SceneBookUser.user_id AND User.status = 10');
        $query->where(['book_id' => $sceneBook, 'role' => $role]);
        $query->orderBy(['SceneBookUser.sort_order' => SORT_ASC]);
        
        return ArrayHelper::map($query->all(), 'user_id', 'nickname');
    }
    
    /**
     * 获取场景预约任务的所有接洽人or摄影师
     * @param string|array $book_id
     * @return array
     */
    protected function getSceneBookUser($book_id)
    {
        $results = [];
        $query = (new Query())->select([
            'SceneBookUser.book_id', 'SceneBookUser.role', 'User.id', 'User.nickname','SceneBookUser.is_primary', 'User.phone'
        ])->from(['SceneBookUser' => SceneBookUser::tableName()]);
        $query->leftJoin(['User' => User::tableName()], 'User.id = SceneBookUser.user_id AND User.status = 10');
        $query->where(['SceneBookUser.book_id' => $book_id, 'SceneBookUser.is_delete' => 0]);
        $query->groupBy('SceneBookUser.id');
        $query->orderBy(['SceneBookUser.sort_order' => SORT_ASC]);
        //组装返回的预约任务用户信息
        foreach ($query->all() as $value) {
            $book_id = $value['book_id'];
            unset($value['book_id']);
            $results[$book_id][] = $value;
        }
       
        return $results;
    }
    
    /**
     * 获取留言
     * @param string $book_id
     * @return array
     */
    protected function getSceneMessage($book_id)
    {
        $query = (new Query())->from(SceneMessage::tableName());
        $query->where(['book_id' => $book_id]);
        
        return $query->all();
    }
    
    /**
     * 判断是否当前自己匹配的预约角色
     * @param string $book_id
     * @return boolean|array
     */
    protected function isOwnSceneBookRole($book_id)
    {
        $query = (new Query())->select(['SceneBookUser.role', 'SceneBookUser.user_id'])
            ->from(['SceneBookUser' => SceneBookUser::tableName()]);
        $query->where([
            'SceneBookUser.book_id' => $book_id, 
            'SceneBookUser.is_primary' => 1,
            'SceneBookUser.is_delete' => 0,
        ]);
        $query->orderBy(['SceneBookUser.sort_order' => SORT_ASC]);
        $results = $query->all();
        $user_ids = ArrayHelper::getColumn($results, 'user_id');
        //判断当前用户是否存在数组里
        if(in_array(Yii::$app->user->id, $user_ids)){
            foreach ($results as $value) {
                if($value['user_id'] != Yii::$app->user->id){
                    return $value;
                }
            }
        } else {
            return false;
        }
    }
}