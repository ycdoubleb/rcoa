<?php

namespace mconline\modules\mcbs\controllers;

use common\models\mconline\McbsActionLog;
use common\models\mconline\McbsCourse;
use common\models\mconline\McbsCourseActivity;
use common\models\mconline\McbsCourseBlock;
use common\models\mconline\McbsCourseChapter;
use common\models\mconline\McbsCoursePhase;
use common\models\mconline\McbsCourseSection;
use common\models\mconline\McbsCourseUser;
use common\models\mconline\searchs\McbsActionLogSearch;
use common\models\mconline\searchs\McbsCourseBlockSearch;
use common\models\mconline\searchs\McbsCourseChapterSearch;
use common\models\mconline\searchs\McbsCoursePhaseSearch;
use common\models\mconline\searchs\McbsCourseSectionSearch;
use common\models\mconline\searchs\McbsCourseUserSearch;
use common\models\User;
use mconline\modules\mcbs\utils\McbsAction;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CourseMakeController implements the CRUD actions for McbsCourse model.
 */
class CourseMakeController extends Controller
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
     * Lists all McbsCourseUser models.
     * @return mixed
     */
    public function actionHelpmanIndex($course_id)
    {
        $searchModel = new McbsCourseUserSearch();
      
        return $this->renderAjax('helpman-index', [
            'dataProvider' => $searchModel->search(['course_id'=>$course_id]),
        ]);
    }

    /**
     * Creates a new McbsCourseUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateHelpman($course_id)
    {
        $model = new McbsCourseUser(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateHelpman($model, Yii::$app->request->post());
            return $this->redirect(['default/view', 'id' => $course_id]);
        } else {
            return $this->renderAjax('create-helpman', [
                'model' => $model,
                'helpmans' => $this->getHelpManList($course_id,$model->course->create_by),
            ]);
        }
    }

    /**
     * Updates an existing McbsCourseUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateHelpman($id)
    {
        $model = McbsCourseUser::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateHelpman($model);
            return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('update-helpman', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing McbsCourseUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteHelpman($id)
    {
        $model = McbsCourseUser::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteHelpman($model);
            return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('delete-helpman',[
                'model' => $model
            ]);
        }
    }

    /**
     * Lists all CouserFrame.
     * @return mixed
     */
    public function actionCouframeIndex($course_id)
    {
        $phaseSearch = new McbsCoursePhaseSearch();
        $blockSearch = new McbsCourseBlockSearch();
        $chapterSearch = new McbsCourseChapterSearch();
        $sectionSearch = new McbsCourseSectionSearch();
        
        return $this->renderAjax('couframe-index', [
            'course_id' => $course_id,
            'dataCouphase' => $phaseSearch->search(['course_id'=>$course_id]),
            'dataCoublock' => $blockSearch->search(['course_id'=>$course_id]),
            'dataCouchapter' => $chapterSearch->search(['course_id'=>$course_id]),
            'dataCousection' => $sectionSearch->search(['course_id'=>$course_id]),
        ]);
    }
    
    /**
     * Creates a new McbsCoursePhase model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCouphase($course_id)
    {
        $model = new McbsCoursePhase(['id' => md5(rand(1,10000) + time()), 'course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Phase'),$course_id);
            return $this->redirect(['default/view', 'id' => $course_id]);
        } else {
            return $this->renderAjax('create-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Phase')
            ]);
        }
    }
    
    /**
     * Updates an existing McbsCoursePhase model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateCouphase($id)
    {
        $model = McbsCoursePhase::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateCouFrame($model,Yii::t('app', 'Phase'),$model->course_id);
            return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('update-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Phase')
            ]);
        }
    }

    /**
     * Deletes an existing McbsCoursePhase model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteCouphase($id)
    {
        $model = McbsCoursePhase::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Phase'),$model->course_id);
            return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('delete-couframe',[
                'model' => $model,
                'title' => Yii::t('app', 'Phase')
            ]);
        }
    }
    
    /**
     * Creates a new McbsCourseBlock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCoublock($phase_id)
    {
        $model = new McbsCourseBlock(['id' => md5(rand(1,10000) + time()), 'phase_id' => $phase_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Block'),$model->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->phase->course_id]);
        } else {
            return $this->renderAjax('create-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Block')
            ]);
        }
    }
    
    /**
     * Updates an existing McbsCourseBlock model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateCoublock($id)
    {
        $model = McbsCourseBlock::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateCouFrame($model,Yii::t('app', 'Block'),$model->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->phase->course_id]);
        } else {
            return $this->renderAjax('update-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Block')
            ]);
        }
    }

    /**
     * Deletes an existing McbsCourseBlock model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteCoublock($id)
    {
        $model = McbsCourseBlock::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Block'),$model->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->phase->course_id]);
        } else {
            return $this->renderAjax('delete-couframe',[
                'model' => $model,
                'title' => Yii::t('app', 'Block')
            ]);
        }
    }
    
    /**
     * Creates a new McbsCourseChapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCouchapter($block_id)
    {
        $model = new McbsCourseChapter(['id' => md5(rand(1,10000) + time()), 'block_id' => $block_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Chapter'),$model->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->block->phase->course_id]);
        } else {
            return $this->renderAjax('create-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Chapter')
            ]);
        }
    }
    
    /**
     * Updates an existing McbsCourseChapter model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateCouchapter($id)
    {
        $model = McbsCourseChapter::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateCouFrame($model,Yii::t('app', 'Chapter'),$model->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->block->phase->course_id]);
        } else {
            return $this->renderAjax('update-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Chapter')
            ]);
        }
    }

    /**
     * Deletes an existing McbsCourseChapter model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteCouchapter($id)
    {
        $model = McbsCourseChapter::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Chapter'),$model->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->block->phase->course_id]);
        } else {
            return $this->renderAjax('delete-couframe',[
                'model' => $model,
                'title' => Yii::t('app', 'Chapter')
            ]);
        }
    }
    
    /**
     * Creates a new McbsCourseSection model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCousection($chapter_id)
    {
        $model = new McbsCourseSection(['id' => md5(rand(1,10000) + time()), 'chapter_id' => $chapter_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Section'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->renderAjax('create-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Section')
            ]);
        }
    }
    
    /**
     * Updates an existing McbsCourseSection model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateCousection($id)
    {
        $model = McbsCourseSection::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateCouFrame($model,Yii::t('app', 'Section'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->renderAjax('update-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Section')
            ]);
        }
    }

    /**
     * Deletes an existing McbsCourseSection model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteCousection($id)
    {
        $model = McbsCourseSection::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Section'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->renderAjax('delete-couframe',[
                'model' => $model,
                'title' => Yii::t('app', 'Section')
            ]);
        }
    }
    
    /**
     * Displays a single McbsCourseActivity model.
     * @param string $id
     * @return mixed
     */
    public function actionCouactivityView($id)
    {
        return $this->renderAjax('activity-view', [
            'model' => McbsCourseActivity::findOne($id),
        ]);
    }
    
    /**
     * Creates a new McbsCourseActivity model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateCouactivity($section_id)
    {
        $model = new McbsCourseActivity(['id' => md5(rand(1,10000) + time()), 'section_id' => $section_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Activity'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->render('create-activity', [
                'model' => $model,
                'title' => Yii::t('app', 'Activity')
            ]);
        }
    }
    
    /**
     * Updates an existing McbsCourseActivity model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateCouactivity($id)
    {
        $model = McbsCourseSection::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->UpdateCouFrame($model,Yii::t('app', 'Section'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->renderAjax('update-couframe', [
                'model' => $model,
                'title' => Yii::t('app', 'Section')
            ]);
        }
    }

    /**
     * Deletes an existing McbsCourseActivity model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteCouactivity($id)
    {
        $model = McbsCourseSection::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            McbsAction::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Section'),$model->chapter->block->phase->course_id);
            return $this->redirect(['default/view', 'id' => $model->chapter->block->phase->course_id]);
        } else {
            return $this->renderAjax('delete-couframe',[
                'model' => $model,
                'title' => Yii::t('app', 'Section')
            ]);
        }
    }

    /**
     * Lists all McbsActionLog models.
     * @return mixed
     */
    public function actionLogIndex($course_id,$relative_id=null,$page=null)
    {
        $searchModel = new McbsActionLogSearch();
        
        return $this->renderAjax('log-index', [
            'course_id' => $course_id,
            'relative_id' => $relative_id,
            'page' => $page,
            'dataProvider' => $searchModel->search(['course_id'=>$course_id,'relative_id'=>$relative_id,'page'=>$page]),
        ]);
    }
    
    /**
     * Displays a single McbsActionLog model.
     * @param string $id
     * @return mixed
     */
    public function actionLogView($id)
    {
        return $this->renderAjax('log-view', [
            'model' => McbsActionLog::findOne($id),
        ]);
    }
    
    /**
     * Finds the McbsCourse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return McbsCourse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findMcbsCourseModel($id)
    {
        if (($model = McbsCourse::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    /**
     * 获取所有协助人员
     * @param string $user_id                           用户id
     * @return array
     */
    public  function getHelpManList($course_id, $user_id)
    {
        //查找已添加的协作人员
        $courUsers = (new Query())->select(['user_id'])
                ->from(McbsCourseUser::tableName())->where(['course_id' => $course_id])
                ->all();
        $courUserIds = ArrayHelper::getColumn($courUsers, 'user_id');
        
        //合并创建者和已添加的协作人员
        $userIds = array_merge([$user_id],$courUserIds);
        //查找所有可以添加的协作人员
        $users = (new Query())->select(['id', 'nickname'])
                ->from(User::tableName())->where(['NOT IN','id',$userIds])
                ->all();
        
        return ArrayHelper::map($users, 'id', 'nickname');
    }
}
