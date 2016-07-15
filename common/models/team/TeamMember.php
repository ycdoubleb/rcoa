<?php

namespace common\models\team;

use common\models\teamwork\CourseManage;
use common\models\teamwork\CourseProducer;
use common\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%team_member}}".
 *
 * @property integer $team_id       团队ID
 * @property string $u_id           用户ID
 * @property string $is_leader      是否为队长
 * @property array $is_leaders      队长 or 队员
 * @property integer $index         索引
 * @property string $position       职位
 *
 * @property Team $team                         获取团队
 * @property User $u                            获取用户
 * @property CourseManage[] $courseManages      获取所有课程管理
 * @property CourseProducer[] $courseProducers  获取所有资源制作人
 * @property CourseManage[] $courses            获取所有课程
 */
class TeamMember extends ActiveRecord
{
    /** 队长 */
    const TEAMLEADER = 'Y';
    /** 队员 */
    const TEAMMEMBER = 'N';
    
    /** 队长 or 队员 */
    public $is_leaders = [
        self::TEAMLEADER => '队长',
        self::TEAMMEMBER => '队员'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%team_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['team_id', 'u_id'], 'required'],
            [['index'], 'integer'],
            [['u_id'], 'string', 'max' => 36],
            [['is_leader'], 'string', 'max' => 4],
            [['position'], 'string', 'max' => 60],
            [['team_id'], 'exist', 'skipOnError' => true, 'targetClass' => Team::className(), 'targetAttribute' => ['team_id' => 'id']],
            [['u_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['u_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'team_id' => Yii::t('rcoa/team', 'Team ID'),
            'u_id' => Yii::t('rcoa/team', 'U ID'),
            'is_leader' => Yii::t('rcoa/team', 'Is Leader'),
            'index' => Yii::t('rcoa', 'Index'),
            'position' => Yii::t('rcoa/team', 'Position'),
        ];
    }

   /**
     * @return ActiveQuery
     */
    public function getTeam()
    {
        return $this->hasOne(Team::className(), ['id' => 'team_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getU()
    {
        return $this->hasOne(User::className(), ['id' => 'u_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCourseManages()
    {
        return $this->hasMany(CourseManage::className(), ['weekly_editors_people' => 'u_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCourseProducers()
    {
        return $this->hasMany(CourseProducer::className(), ['producer' => 'u_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasMany(CourseManage::className(), ['id' => 'course_id'])->viaTable('{{%teamwork_course_producer}}', ['producer' => 'u_id']);
    }
}
