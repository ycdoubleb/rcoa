<?php

namespace frontend\modules\teamwork;

use common\models\teamwork\CourseLink;
use common\models\teamwork\CourseManage;
use common\models\teamwork\CoursePhase;
use common\models\teamwork\CourseSummary;
use common\models\teamwork\Link;
use common\models\teamwork\Phase;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TeamworkTool{
    /**
     * 获取一周时间
     * @param type $course_id
     * @param type $date
     */
    public function getWeek($course_id, $date)
    {
        //$date = date('Y-m-d');  //当前日期
        $first = 1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $w = date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $now_start = date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $now_end = date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
        //$last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
        //$last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
        $result = CourseSummary::find()->where(['course_id' => $course_id])
                ->andWhere('create_time >="'. $now_start.'"')
                ->andWhere('create_time <="'. $now_end.'"')
                ->one();
        return $result;
    }
    
    /**
     * 复制Phase表数据到CoursePhase表
     * @param type $course_id  课程ID
     */
    public function addCoursePhase($course_id)
    {
        $phase = Phase::find()
                ->with('links')
                ->with('createBy')
                ->all();
        $values = [];
        /** 重组提交的数据为$values数组 */
        foreach($phase as $value)
        {
            $values[] = [
                'course_id' => $course_id,
                'phase_id' => $value->id,
            ];
        }
        
        /** 添加$values数组到表里 */
        Yii::$app->db->createCommand()->batchInsert(CoursePhase::tableName(), 
        [
            'course_id',
            'phase_id',
        ], $values)->execute();
    }
    
    /**
     * 复制Link表数据到CourseLink表
     * @param type $course_id   课程ID
     */
    public function addCourseLink($course_id)
    {
        $link = Link::find()
                ->with('createBy')
                ->with('phase')
                ->with('phaseLinks')
                ->with('phases')
                ->all();
        $values = [];
        /** 重组提交的数据为$values数组 */
        foreach($link as $value)
        {
            $values[] = [
                'course_id' => $course_id,
                'course_phase_id' => $value->phase_id,
                'link_id' => $value->id,
            ];
        }
        
        /** 添加$values数组到表里 */
        Yii::$app->db->createCommand()->batchInsert(CourseLink::tableName(), 
        [
            'course_id',
            'course_phase_id',
            'link_id'
        ], $values)->execute();
    }
    
    /**
     * 获取课程阶段进度
     * @param type $course_id   课程ID
     * @return type
     */
    public function getCoursePhaseProgress($course_id)
    {
        $sql = "SELECT Link.course_id, Phase.phase_id,Phase_Temp.`name`,SUM(total) AS total,SUM(completed) AS completed,(SUM(completed)/SUM(total)) AS progress  
                FROM ccoa_teamwork_course_link AS Link  
                LEFT JOIN ccoa_teamwork_course_phase AS Phase ON Phase.id = Link.course_phase_id  
                LEFT JOIN ccoa_teamwork_phase_template AS Phase_Temp ON Phase.phase_id = Phase_Temp.id  
                WHERE Link.course_id = $course_id AND Link.is_delete = 'N'
                GROUP BY Link.course_phase_id";
        
        $coursePhaseProgress = CoursePhase::findBySql($sql)
                ->with('course')
                ->with('phase')
                //->with('courseLinks')
                ->all();
        return $coursePhaseProgress;
    }
    
    /**
     * 获取所有课程进度
     * @param type $project_id  项目ID
     * @return type $project_id,非null返回项目对应下的所有课程进度, 为null返回所有课程进度
     */
    public function getCourseProgress($project_id = null)
    {
        $project_id = $project_id == null ?  '' : "AND Course.project_id = $project_id"; 
        
        $sql = "SELECT id,Phase_PRO.project_id,Phase_PRO.course_id,(SUM(Phase_PRO.progress)/COUNT(Phase_PRO.progress)) AS progress FROM  
                    (SELECT Course.project_id, Course.course_id, Link.course_id AS id,SUM(total) AS total,SUM(completed) AS completed,(SUM(completed)/SUM(total)) AS progress  
                    FROM ccoa_teamwork_course_link AS Link  
                    LEFT JOIN ccoa_teamwork_course_phase AS Phase ON Phase.id = Link.course_phase_id  
                    LEFT JOIN ccoa_teamwork_phase_template AS Phase_Temp ON Phase.phase_id = Phase_Temp.id
                    LEFT JOIN ccoa_teamwork_course_manage AS Course ON Link.course_id = Course.id
                    WHERE Link.is_delete = 'N' AND Course.`status` = 1 $project_id
                    GROUP BY Link.course_id) AS Phase_PRO 
                GROUP BY id";
        
        $courseProgress = CourseManage::findBySql($sql)
                ->with('course')
                ->with('courseLinks')
                ->with('coursePhases')
                ->with('producers')
                ->with('speakerTeacher')
                ->with('project')
                ->all();
        return $courseProgress;
    }
    
    /**
     * 获取单条课程进度
     * @param type $id
     */
    public function getCourseProgressOne($id)
    {
        $sql = "SELECT Course.*,SUM(total) AS total,SUM(completed) AS completed,(SUM(completed)/SUM(total)) AS progress  
		FROM ccoa_teamwork_course_link AS Link  
		LEFT JOIN ccoa_teamwork_course_phase AS Phase ON Phase.id = Link.course_phase_id  
		LEFT JOIN ccoa_teamwork_phase_template AS Phase_Temp ON Phase.phase_id = Phase_Temp.id
		LEFT JOIN ccoa_teamwork_course_manage AS Course ON Link.course_id = Course.id
		WHERE Link.is_delete = 'N' AND Course.id = $id
		GROUP BY id";
        $courseProgress = CourseManage::findBySql($sql)
                ->with('course')
                ->with('courseLinks')
                ->with('coursePhases')
                ->with('producers')
                ->with('speakerTeacher')
                ->with('project')
                ->one();
       
        return $courseProgress;
    }
    
    
}