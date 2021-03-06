<?php

namespace common\models\demand;

use common\models\team\Team;
use common\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%demand_task_auditor}}".
 *
 * @property integer $team_id               团队ID
 * @property string  $u_id                  用户ID
 * 
 * @property Team $team                     获取团队
 * @property User $taskUser                 获取用户
 */
class DemandTaskAuditor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%demand_task_auditor}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['team_id', 'u_id'], 'required'],
            [['team_id'], 'integer'],
            [['u_id'], 'string', 'max' => 36],
            [['team_id', 'u_id'], 'unique', 'targetAttribute' => ['team_id', 'u_id'], 'message' => 'The combination of Team ID and U ID has already been taken.'],
            [['team_id'], 'exist', 'skipOnError' => true, 'targetClass' => Team::className(), 'targetAttribute' => ['team_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'team_id' => Yii::t('rcoa/demand', 'Team ID'),
            'u_id' => Yii::t('rcoa/demand', 'U ID'),
        ];
    }

    /**
     * 获取团队
     * @return ActiveQuery
     */
    public function getTeam()
    {
        return $this->hasOne(Team::className(), ['id' => 'team_id']);
    }
    
    /**
     * 获取用户
     * @return ActiveQuery
     */
    public function getTaskUser()
    {
        return $this->hasOne(User::className(), ['id' => 'u_id']);
    }
}
