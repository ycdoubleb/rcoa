<?php

namespace frontend\modules\teamwork\controllers;

use common\models\teamwork\CourseSummary;
use frontend\modules\teamwork\TeamworkTool;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * SummaryController implements the CRUD actions for CourseSummary model.
 */
class SummaryController extends Controller
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
             //access验证是否有登录
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all CourseSummary models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CourseSummary::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CourseSummary model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CourseSummary model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CourseSummary();
        /* @var $twTool TeamworkTool */
        $twTool = Yii::$app->get('twTool');
        $params = Yii::$app->request->queryParams;
        $model->course_id = $params['course_id'];
        $model->create_time = date('Y-m-d', time());
        $result = $twTool->getWeek($model->course_id, $model->create_time);
        
        if(!empty($result))
            return $this->redirect(['update', 'course_id' => $model->course_id, 'create_time' => $result->create_time]);
        
        if(!$model->course->getIsNormal() || !$twTool->getIsLeader())
            throw new NotAcceptableHttpException('只有队长 or 状态为正常才可以【创建总结】');
         
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['course/view', 'id' => $model->course_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CourseSummary model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($course_id, $create_time = null)
    {
        if($create_time == null)
            return $this->redirect(['create', 'course_id' => $course_id]);
        
         /* @var $twTool TeamworkTool */
        $twTool = Yii::$app->get('twTool');
        $model = $this->findModel($course_id, $create_time);
         
        if(!$model->course->getIsNormal() || !$twTool->getIsLeader())
            throw new NotAcceptableHttpException('只有队长 or 状态为正常才可以【编辑总结】');
       
        if ($model->load(Yii::$app->request->post())) {
            $model->save(false, ['content', 'updated_at']);
            return $this->redirect(['course/view', 'id' => $model->course_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CourseSummary model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CourseSummary model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CourseSummary the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($course_id, $create_time)
    {
        $model = CourseSummary::findOne(['course_id' => $course_id, 'create_time' => $create_time]);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}