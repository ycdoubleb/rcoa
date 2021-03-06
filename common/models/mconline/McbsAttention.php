<?php

namespace common\models\mconline;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%mcbs_attention}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $course_id
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property McbsCourse $course                             板书课程
 */
class McbsAttention extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mcbs_attention}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors() 
    {
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
            [['user_id', 'course_id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['user_id'], 'string', 'max' => 36],
            [['course_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 获取板书课程
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(McbsCourse::className(), ['id' => 'course_id']);
    }
}
