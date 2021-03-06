<?php

use frontend\modules\scene\assets\SceneAsset;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Scene}-{Detail}', [
            'Scene' => Yii::t('app', 'Scene'),
            'Detail' => Yii::t('app', 'Detail'),
        ]);

?>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=r2OdCIhHY8ZEY4fZQG7DGjl1nAIVoH0a"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.js"></script>
<link rel="stylesheet" href="http://api.map.baidu.com/library/SearchInfoWindow/1.5/src/SearchInfoWindow_min.css" />

<div class="scene-manage-view container">
    <div class="introduce col-lg-12">
        <div class="scene-img col-lg-7">
            <img src="<?= $sceneData['img_path'] ?>">
        </div>
        <div class="scene-info col-lg-5">
            <div class="info-content">
                <div class="scene-name"><?= $sceneData['name'] ?></div>
                <div class="scene-nature"><span class="span">性质：</span>
                    <div class="bg-color <?= ($sceneData['op_type'] == 1) ? 'add-red' : 'add-blue' ?>">
                        <font><?= ($sceneData['op_type'] == 1) ? '自营' : '合作' ?></font>
                    </div>
                </div>
                <div class="scene-area"><span>区域：</span><font><?= $sceneData['area'] ?></font></div>
                <div class="scene-type"><span>内容：</span><font><?= $sceneData['content_type'] ?></font></div>
                <div class="scene-price"><span>价格：</span><font>￥<?= $sceneData['price'] ?>/小时</font></div>
                <div class="scene-num"><span>总预约：</span><font><?= $registerNum ?> 次</font></div>
                <div class="scene-contact"><span>联系人：</span><font><?= $sceneData['contact'] ?></font></div>
                <div class="scene-address"><span>地址：</span><font><?= $sceneData['address'] ?></font></div>
                <div class="scene-des"><span>简介：</span><font><?= $sceneData['des'] ?></font></div>
            </div>
        </div>
    </div>

    <div class="scene-details col-lg-12">
        <div class="tablist">
            <ul>
                <li class="active">
                    <a href="#details" onclick="tabClick($(this));return false;">
                        <i class="details"></i><em>详情</em>
                    </a>
                </li>
                <li class="none">
                    <a href="#scene-map" onclick="tabClick($(this));return false;">
                        <i class="scene-map"></i><em>位置</em>
                    </a>
                </li>
            </ul>
        </div>
        <div class="tabcontent">
            <div id="details" class="tabpane show">
                <div class="resource">
                    <?php
                        $content = $sceneData['content'];
                        //设置img中src的前缀(常量-后台网址)
                        $imgPrefix = WEB_ADMIN_ROOT;
                        //用正则查找内容中的所有img标签的规则
                        $imgRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
                        //批量给img标签中src增加前缀
                        $content_img = preg_replace($imgRule, '<img src="'.$imgPrefix.'${1}" style="max-width:100%">', $content);
                        echo $content_img;
                    ?>
                </div>
            </div>
            <div id="scene-map" class="tabpane">
                <div id="allmap" class="allmap"></div>
            </div>
        </div>
    </div>

</div>
<?php
$map_x = $sceneData['X(location)'];                 //经度
$map_y = $sceneData['Y(location)'];                 //纬度
$map_img = $sceneData['img_path'];                  //图片路径
$map_contact = $sceneData['contact'];               //联系人
$map_address = $sceneData['address'];               //地址
$map_des = $sceneData['des'];                       //简介

$js = <<<JS
        
    //单击切换标签
    window.tabClick = function (elem) {
        $(elem).parent().siblings("li").removeClass("active");
        $(elem).parent("li").addClass("active");
        var idName = $(elem).attr("href");
        $(idName).siblings("div").animate({opacity: 0}, 300).removeClass("show");
        $(idName).animate({opacity: 1}, 250).addClass("show");
    };
        
    /** 百度地图设置 */   
    var map = new BMap.Map("allmap");
    var point = new BMap.Point($map_x,$map_y);      //地图初始位置
    map.centerAndZoom(point, 16);                   //初始化地图，设置中心点坐标和地图级别
    
    var content = '<div style="margin:0;line-height:20px;padding:2px;">' +
                    '<img src="$map_img" alt="" style="float:right;zoom:1;overflow:hidden;width:100px;height:100px;margin-left:3px;"/>' +
                    '地址：$map_address<br/>联系人：$map_contact<br/>简介：$map_des' +
                '</div>';
    //创建检索信息窗口对象
    var searchInfoWindow = null;
    searchInfoWindow = new BMapLib.SearchInfoWindow(map, content, {
                    title  : "地址信息",         //标题
                    width  : 310,               //宽度
                    height : 105,               //高度
                    panel  : "panel",           //检索结果面板
                    enableAutoPan : true,       //自动平移
                    searchTypes   :[
                            BMAPLIB_TAB_SEARCH,   //周边检索
                            BMAPLIB_TAB_TO_HERE,  //到这里去
                            BMAPLIB_TAB_FROM_HERE //从这里出发
                    ]
            });
    var marker = new BMap.Marker(point);            //创建标注
    marker.addEventListener("click", function(e){
        searchInfoWindow.open(marker);
    });
    map.addOverlay(marker);                         //将标注添加到地图中
    map.addEventListener("resize",function(){  //增加地图加载完成监听事件
        if($("#scene-map").hasClass('show')){
            //将地图中心点移动到定位的这个点位置
            map.panTo(point); 
        }             
    }); 
    map.addEventListener("dragend",function(){  //增加地图加载完成监听事件
        //将地图中心点移动到定位的这个点位置
        setTimeout(function(){
            map.panTo(point); 
        },10);           
    }); 
    var top_left_navigation = new BMap.NavigationControl(); //左上角，添加默认缩放平移控件
    map.addControl(top_left_navigation);
JS;
$this->registerJs($js, View::POS_READY);
?>
<?php
SceneAsset::register($this);
