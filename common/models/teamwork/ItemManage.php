<?php

namespace common\models\teamwork;

use common\models\team\TeamMember;
use common\models\User;
use wskeee\framework\models\Item;
use wskeee\framework\models\ItemType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%teamwork_item_manage}}".
 *
 * @property integer $id                ID
 * @property integer $item_type_id      项目类别
 * @property integer $item_id           项目
 * @property integer $item_child_id     子项目
 * @property string $create_by          创建者
 * @property integer $created_at        创建时间
 * @property string $forecast_time      预计上线时间
 * @property string $real_carry_out     实际完成时间
 * @property integer $progress          当前进度
 * @property integer $status            状态
 * @property string $background         项目背景
 * @property string $use                项目用途
 *
 * @property CourseManage[] $courseManages      获取所有课程
 * @property User $createBy                     获取创建人
 * @property TeamMember $teamMember             获取获取团队成员
 * @property Item $itemChild                    获取子项目
 * @property Item $item                         获取项目
 * @property ItemType $itemType                 获取项目类别
 */
class ItemManage extends ActiveRecord
{
    /** 暂停 */
    const STATUS_TIME_OUT = 0;
    /** 正常 */
    const STATUS_NORMAL = 1;
    /** 完成 */
    const STATUS_CARRY_OUT = 99;
    
    /** 状态名 */
    public $statusName = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_CARRY_OUT => '完成',
        self::STATUS_TIME_OUT => '暂停',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teamwork_item_manage}}';
    }
    
    public function behaviors() {
        return [
            TimestampBehavior::className()
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_type_id',  'item_id', 'item_child_id'], 'required'],
            [['item_type_id', 'item_id', 'item_child_id', 'created_at', 'progress', 'status'], 'integer'],
            [['create_by'], 'string', 'max' => 36],
            [['forecast_time', 'real_carry_out'], 'string', 'max' => 60],
            [['background', 'use'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rcoa/teamwork', 'ID'),
            'item_type_id' => Yii::t('rcoa/teamwork', 'Item Type'),
            'item_id' => Yii::t('rcoa/teamwork', 'Item'),
            'item_child_id' => Yii::t('rcoa/teamwork', 'Item Child'),
            'create_by' => Yii::t('rcoa', 'Create By'),
            'created_at' => Yii::t('rcoa/teamwork', 'Created At'),
            'forecast_time' => Yii::t('rcoa/teamwork', 'Forecast Time'),
            'real_carry_out' => Yii::t('rcoa/teamwork', 'Real Carry Out'),
            'progress' => Yii::t('rcoa/teamwork', 'Now Progress'),
            'status' => Yii::t('rcoa', 'Status'),
            'background' => Yii::t('rcoa/teamwork', 'Background'),
            'use' => Yii::t('rcoa/teamwork', 'Use'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCourseManages()
    {
        return $this->hasMany(CourseManage::className(), ['project_id' => 'id']);
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
    public function getTeamMember()
    {
        return $this->hasOne(TeamMember::className(), ['u_id' => 'create_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getItemChild()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_child_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getItemType()
    {
        return $this->hasOne(ItemType::className(), ['id' => 'item_type_id']);
    }
    
    /**
     * 获取状态是否为【正常】
     */
    public function getIsNormal()
    {
        return $this->status == self::STATUS_NORMAL;
    }
    
    /**
     * 获取状态是否为【暂停】
     */
    public function getIsTimeOut()
    {
        return $this->status == self::STATUS_TIME_OUT;
    }
    
    /**
     * 获取状态是否为【完成】
     */
    public function getIsCarryOut()
    {
        return $this->status == self::STATUS_CARRY_OUT;
    }
    
    /**
     * 获取状态名称
     */
    public function getStatusName()
    {
        return $this->statusName[$this->status];
    }
    
    /**
     * 获取课程时长总和
     * @return type
     */
    public function getCourseLessionTimesSum()
    {
        $courses = [];
        foreach ($this->courseManages as $value) 
            $courses[] =  $value->lession_time;
        
        return $courses;
    }

    /**
     * 获取当前用户是否为【队长】
     * @return boolean  true为是
     */
    public function getIsLeader()
    {
        //查出成员表里面所有队长
        $isLeader = TeamMember::findAll(['u_id' => \Yii::$app->user->id]);
        
        if(!empty($isLeader) || isset($isLeader)){
            foreach ($isLeader as $value){
                if($value->is_leader == 'Y')
                    return true;
            }
        }
        return false;
    }
    
    /**
     * 获取该条项目下所有课程是否为【完成】状态
     * @param ItemManage $model
     * @return boolean  true 为是
     */
    public function getIsCoursesStatus()
    {
        /* @var $model ItemManage */
        foreach ($this->courseManages as $value) {
            if($value->status == self::STATUS_CARRY_OUT)
                return true;
        }
        return false;
    }
}
