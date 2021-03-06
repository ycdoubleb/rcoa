<?php

namespace frontend\modules\scene\controllers;

use common\models\scene\SceneBook;
use common\models\scene\SceneSite;
use common\models\scene\SceneSiteDisable;
use common\models\scene\searchs\SceneSiteDisableSearch;
use common\models\scene\searchs\SceneSiteSearch;
use wskeee\rbac\RbacManager;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * SceneManage controller for the `scene` module
 */
class SceneManageController extends Controller
{
    public $layout = 'scene';
    
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
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }
    
    /**
     * 场地列表
     * Renders View the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $search = new SceneSiteSearch();
        $sceneItem = $search->dataSearceh($params,$this->isAdmin());             //场地搜索的结果
        
        return $this->render('index',[
            'sceneItem' => $sceneItem,
        ]);
    }
    
    /**
     * 场地详情
     * Renders View the index view for the module
     * @return string
     */
    public function actionView($id)
    {
        return $this->render('view',[
            'sceneData' => $this->getSceneData($id),
            'registerNum' => $this->getRegisterNum($id),
        ]);
    }
    
    /**
     * 跳转到禁用场地视图
     * Renders View the index view for the module
     * @return string
     */
    public function actionDisable()
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new SceneSiteDisableSearch();
        $sceneSite = $this->getSceneSite();
        $firstSite = array_keys(reset($sceneSite));       //获取场景的第一个场地
        $results = $searchModel->searchModel($params, $firstSite);
        
        return $this->render('disable',[
            'filter' => $results['filters'],
            'dataProvider' => $results['data'],
            'holidays' => $results['holidays'],                                         //节假日标识
            'books' => $this->getBooksItem(ArrayHelper::getValue($params, 'site_id')),  //已约场次
            'sceneSite' => $sceneSite,
            'firstSite' => $firstSite,
        ]);
    }

    /**
     * 禁用该日期时段下的场地
     * @param integer $site_id      场地ID
     * @param type $date            日期
     * @param integer $time_index   时段
     * @return mixed
     */
    public function actionSiteDisable()
    {
        $params = Yii::$app->request->queryParams;
        $site_id = ArrayHelper::getValue($params, 'site_id');
        $date = ArrayHelper::getValue($params, 'date');

        $bookModel = $this->findBookModel();
        if ($bookModel != null) {
            throw new ServerErrorHttpException('禁用失败！该时段的场地已被预约！！');
        }

        $model = $this->findModel();
        $model->is_disable = 1;
        $model->save();

        return $this->redirect(['disable', 'site_id' => $site_id, 'date' => $date]);

    }
    
    /**
     * 启用该日期时段下的场地
     * @param integer $site_id      场地ID
     * @param type $date            日期
     * @param integer $time_index   时段
     * @return mixed
     */
    public function actionSiteEnable()
    {
        $params = Yii::$app->request->queryParams;
        $site_id = ArrayHelper::getValue($params, 'site_id');
        $date = ArrayHelper::getValue($params, 'date');

        $model = $this->findModel();
        $model->is_disable = 0;
        $model->save();

        return $this->redirect(['disable', 'site_id' => $site_id, 'date' => $date]);

    }
    
    /**
     * Finds the SceneBook model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @return SceneBook the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findBookModel()
    {
        $notStatus = [SceneBook::STATUS_DEFAULT, SceneBook::STATUS_CANCEL];
        $model = SceneBook::find()
            ->where(Yii::$app->request->queryParams)        //过滤场地时间时段
            ->andWhere(['NOT IN', 'status', $notStatus])    //过滤已取消和未预约的数据
            ->one();
        
        if ($model !== null) {
            return $model;
        }
    }
    
     /**
     * Finds the SceneSiteDisable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @return SceneSiteDisable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel()
    {
        $model = SceneSiteDisable::findOne(Yii::$app->request->queryParams);
        if ($model !== null) {
            return $model;
        }else{
            return new SceneSiteDisable(Yii::$app->request->queryParams);
        }
    }
    
    /**
     * 获取场地的被预约次数
     * @return string
     */
    public function getRegisterNum($id)
    {
        $notStatus = [SceneBook::STATUS_DEFAULT, SceneBook::STATUS_CANCEL];
        $query = SceneBook::find();
        //添加查询条件
        $query->andFilterWhere(['NOT IN', 'status', $notStatus]);
        $query->andFilterWhere(['site_id' => $id]);
        //计算预约次数
        $registerNum = count($query->all());

        return $registerNum;
    }

    /**
     * 查询场地信息
     * @param integer $id
     * @return array
     */
    public function getSceneData($id)
    {
        $sceneData = (new Query())
                ->select(['SceneSite.id', 'SceneSite.name', 'SceneSite.op_type', 'SceneSite.area', 'SceneSite.price',
                        'SceneSite.contact', 'SceneSite.address', 'SceneSite.des', 'SceneSite.content',
                        'SceneSite.content_type', 'SceneSite.img_path', 'X(location)', 'Y(location)'])
                ->from(['SceneSite' => SceneSite::tableName()])
                ->where(['id' => $id])
                ->one();

        return $sceneData;
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
        $query->filterWhere([
            'id' => $site_id,
            'is_publish' => 1,
        ]);
        //只有场地管理人才可以禁用/启用,超级管理员除外\
        if(!$this->isAdmin()){
            $query->andFilterWhere([
                'manager_id' => \Yii::$app->user->id,   
            ]);
        }
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
     * 查询该日期时段下的场地是否有预约
     * @param integer $site_id
     * @return array
     */
    protected  function getBooksItem($site_id)
    {
        $notStatus = [SceneBook::STATUS_DEFAULT, SceneBook::STATUS_CANCEL];
        
        $query = (new Query())->select(['id', 'date', 'time_index'])
             ->from(SceneBook::tableName());
        $query->andFilterWhere(['site_id' => $site_id]);                //过滤场地
        $query->andFilterWhere(['NOT IN', 'status', $notStatus]);       //过滤已取消和未预约的数据
        $query->orderBy(['date' => SORT_ASC, 'time_index' => SORT_ASC]);
        
        $bookItems = [];
        foreach ($query->all() as $book) {
            $bookItems[$book['date']][$book['time_index']] = $book['id'];
        }
        
        return $bookItems;
    }
    
    /**
     * 检查是否为管理员
     */
    private function isAdmin(){
        /* @var $rbacManager RbacManager */
        $rbacManager = \Yii::$app->authManager;
        return $rbacManager->isAdmin('r_admin');
    }
}
