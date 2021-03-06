<?php

namespace common\models\mconline\searchs;

use common\models\mconline\McbsMessage;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * McbsMessageSearch represents the model behind the search form about `common\models\mconline\McbsMessage`.
 */
class McbsMessageSearch extends McbsMessage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'reply_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'content', 'created_by', 'course_id', 'activity_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        
        $this->course_id = ArrayHelper::getValue($params, 'course_id');             //课程id
        $this->activity_id = ArrayHelper::getValue($params, 'activity_id');         //活动id
        $this->created_by = ArrayHelper::getValue($params, 'created_by');             //创建者
        $this->reply_id = ArrayHelper::getValue($params, 'reply_id');               //回复id
        
        $query = McbsMessage::find()->select(['McbsMessage.*','User.nickname','User.avatar'])
                ->from(['McbsMessage'=> McbsMessage::tableName()]);

        // add conditions that should always apply here

        /*$dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }*/
        
        $query->leftJoin(['User'=> User::tableName()],'User.id = created_by');

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'course_id' => $this->course_id,
            'activity_id' => $this->activity_id,
            'created_by' => $this->created_by,
            'reply_id' => $this->reply_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content]);

        $query->orderBy(['created_at' => SORT_ASC]);
        
        return $query->asArray()->all();
    }
}