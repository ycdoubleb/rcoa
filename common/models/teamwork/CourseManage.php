<?php

namespace common\models\teamwork;

use common\models\teamwork\ItemManage;
use common\models\User;
use wskeee\framework\models\Item;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%teamwork_course_manage}}".
 *
 * @property integer $id                ID
 * @property integer $project_id        项目Id
 * @property integer $course_id         课程Id
 * @property string $teacher            主讲教师
 * @property integer $lession_time      学时
 * @property string $create_by          创建者
 * @property integer $created_at        创建于
 * @property string $plan_start_time    计划开始时间
 * @property string $plan_end_time      计划完成时间
 * @property string $real_carry_out     实际完成时间
 * @property integer $progress          当前进度
 * @property integer $status            状态
 * @property string $des                描述
 *
 * @property User $createBy                 获取创建者
 * @property Item $course                   获取课程
 * @property User $speakerTeacher           获取主讲讲师
 * @property ItemManage $project            获取项目
 * @property CourseSummary $courseSummary   获取课程总结
 */
class CourseManage extends ActiveRecord
{
   
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teamwork_course_manage}}';
    }
    
    public function behaviors() {
        return [
            TimestampBehavior::className('created_at')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id',  'course_id', 'teacher'], 'required'],
            [['project_id', 'course_id', 'lession_time', 'created_at', 'progress', 'status'], 'integer'],
            [['teacher', 'create_by'], 'string', 'max' => 36],
            [['plan_start_time', 'plan_end_time', 'real_carry_out'], 'string', 'max' => 60],
            [['plan_start_time', 'plan_end_time'], 'required'],
            [['des'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rcoa/teamwork', 'ID'),
            'project_id' => Yii::t('rcoa/teamwork', 'Project ID'),
            'course_id' => Yii::t('rcoa/teamwork', 'Course ID'),
            'teacher' => Yii::t('rcoa/teamwork', 'Teacher'),
            'lession_time' => Yii::t('rcoa/teamwork', 'Lession Time'),
            'create_by' => Yii::t('rcoa', 'Create By'),
            'created_at' => Yii::t('rcoa/teamwork', 'Created At'),
            'plan_start_time' => Yii::t('rcoa/teamwork', 'Plan Start Time'),
            'plan_end_time' => Yii::t('rcoa/teamwork', 'Plan End Time'),
            'real_carry_out' => Yii::t('rcoa/teamwork', 'Real Carry Out'),
            'progress' => Yii::t('rcoa/teamwork', 'Progress'),
            'status' => Yii::t('rcoa', 'Status'),
            'des' => Yii::t('rcoa/teamwork', 'Des'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCreateBy()
    {
        return $this->hasOne(User::className(), ['id' => 'create_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Item::className(), ['id' => 'course_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourseSummary()
    {
        return $this->hasOne(CourseSummary::className(), ['course_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSpeakerTeacher()
    {
        return $this->hasOne(User::className(), ['id' => 'teacher']);
    }

    /**
     * @return ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(ItemManage::className(), ['id' => 'project_id']);
    }
    
}
