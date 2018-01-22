<?php

namespace common\models\scene\searchs;

use common\models\scene\SceneBook;
use wskeee\utils\DateUtil;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * SceneBookSearch represents the model behind the search form about `common\models\scene\SceneBook`.
 */
class SceneBookSearch extends SceneBook
{
    /**
     * 开始日期
     * @var string 
     */
    private $date_start;
    /**
     * 结束日期
     * @var string 
     */
    private $date_end;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date', 'start_time', 'remark', 'teacher_id', 'booker_id', 'created_by'], 'safe'],
            [['site_id', 'time_index', 'status', 'business_id', 'level_id', 'profession_id', 'course_id', 'lession_time', 'content_type', 'shoot_mode', 'is_photograph', 'camera_count', 'is_transfer', 'created_at', 'updated_at', 'ver'], 'integer'],
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
        $this->date = ArrayHelper::getValue($params, 'date');                         //时间段
        $query = SceneBook::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'time_index' => $this->time_index,
            'status' => $this->status,
            'business_id' => $this->business_id,
            'level_id' => $this->level_id,
            'profession_id' => $this->profession_id,
            'course_id' => $this->course_id,
            'lession_time' => $this->lession_time,
            'content_type' => $this->content_type,
            'shoot_mode' => $this->shoot_mode,
            'is_photograph' => $this->is_photograph,
            'camera_count' => $this->camera_count,
            'is_transfer' => $this->is_transfer,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ver' => $this->ver,
        ]);
        
        //按时间段搜索
        if($this->date != null){
            $this->date = explode(" - ", $this->date);
            $query->andFilterWhere(['between', 'date', $this->date[0], $this->date[1]]);
        }

        $query->andFilterWhere(['like', 'start_time', $this->start_time])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'teacher_id', $this->teacher_id])
            ->andFilterWhere(['like', 'booker_id', $this->booker_id])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
    
    
    /**
     * 
     * @param type $se array(start=>周起始时间，end=>周结束时间 )
     * @return array 一周拍摄预约数据
    */
    public function searchModel($params, $firstSite)
    {
        $this->date = ArrayHelper::getValue($params, 'date', date('Y-m-d'));                         //日期
        $this->date_switch = ArrayHelper::getValue($params, 'date_switch', 'month');                 //月 or 周
        $hasDo = $this->date_switch == 'month';
        $date = $hasDo ? DateUtil::getMonthSE($this->date) : DateUtil::getWeekSE($this->date);
        
        $this->site_id = ArrayHelper::getValue($params, 'site_id', reset($firstSite));              //场景id
        $this->date_switch = ArrayHelper::getValue($params, 'date_switch', 'month');                //月 or 周
        $this->date_start = ArrayHelper::getValue($date, 'start');                                  //开始日期               
        $this->date_end = ArrayHelper::getValue($date, 'end');                                      //结束日期
        //查询预约任务数据
        $results = $this->searchSceneBook();
        //创建空的日期数据
        $dateDatas = $hasDo ? $this->searchMonth() : $this->searchWeek();
       
        $startIndex = 0;
        foreach ($results as $model) {
            for ($i = $startIndex, $len = count($dateDatas); $i < $len; $i++) {
                if ($dateDatas[$i]->date === $model->date && $dateDatas[$i]->time_index === $model->time_index){
                    $dateDatas[$i] = $model;
                    $startIndex = $i + 1;
                    break;
                }
            }
        }
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $dateDatas,
            'sort' => [
                'attributes' => ['date', 'time_index'],
            ],
            'pagination' => [
                'pageSize' =>21,
            ],
        ]);
        
        return [
            'filters' => $params,
            'data' => $dataProvider,
        ];
    }
    
    /**
     * 一个月拍摄预约数据     
     * @return SceneBookSearch
     */
    private function searchMonth()
    {
        //创建一个月空数据
        $monthdatas = [];
        $dateArray = explode('-', date('Y-m', strtotime($this->date_end)));
        $startWeek = 0;        //从星期天开始为0
        $start = date('w', strtotime("first monday of $this->date_start"));           //当月从星期几天始
        $end = cal_days_in_month(CAL_GREGORIAN, $dateArray[1], $dateArray[0]);        //当月的天数        
        $mday = 1;          //第几天
        for ($i = 0, $len = ceil((intval($start) + $end) / 7); $i < $len; $i++){
            for($d = 0;  $d < 7; $d++){
                $nowday = 7 * $i + $d + $startWeek;
                if($nowday >= $start && $mday <= $end){
                    for ($index = 0; $index < 3; $index++){
                        $monthdatas[] = new SceneBookSearch([
                            'id' => md5($this->site_id + date('Y-m-d', strtotime($dateArray[0].'-'.$dateArray[1].'-'.($mday))) + $index + rand(1,10000)),
                            'site_id' => $this->site_id,
                            'date' => date('Y-m-d', strtotime($dateArray[0].'-'.$dateArray[1].'-'.($mday))),
                            'time_index' => $index,
                            'date_switch' => $this->date_switch,
                        ]);
                    }
                    $mday++;
                }
            }
        }
        
        return $monthdatas;
    }

    /**
     * 一周拍摄预约数据     
     * @return SceneBookSearch
     */
    private function searchWeek()
    {
        
//        $indexOffsetTimes = [
//            '9 hours',
//            '14 hours',
//            '18 hours',
//        ];
        //创建一周空数据
        $weekdatas = [];
        for ($i = 0, $len = 7; $i < $len; $i++) {
            for ($index = 0; $index < 3; $index++) {
                $weekdatas[] = new SceneBookSearch([
                    'id' => md5($this->site_id + date('Y-m-d', strtotime($this->date_start . ' +' . ($i) . 'days ')) + $index + rand(1,10000)),
                    'site_id' => $this->site_id,
                    'date' => date('Y-m-d', strtotime($this->date_start . ' +' . ($i) . 'days ')),
                    'time_index' => $index,
                    'date_switch' => $this->date_switch,
                ]);
            }
        }
        
        return $weekdatas;
    }
    
    /**
     * 查询预约任务数据
     * @return Query
     */
    private function searchSceneBook() 
    {
        
        $query = SceneBookSearch::find();
        //添加查询条件
        $query->andFilterWhere(['between', 'date', $this->date_start, $this->date_end]);
        $query->andFilterWhere(['site_id' => $this->site_id]);
        
        //排序
        $query->orderBy(['date' => SORT_ASC, 'time_index' => SORT_ASC]);
        
        return $query->all();
    }
    
}