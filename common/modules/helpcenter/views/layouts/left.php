<?php

use common\modules\helpcenter\controllers\DefaultController;
use common\widgets\Menu;

$menus = DefaultController::getMenu();

?>
<aside class="main-sidebar" style="padding-top: 0px">
    <section class="sidebar">
        <?php
            $menuItems = [
                ['label' => '目录', 'options' => ['class' => 'header']],
            ];
            foreach ($menus as $items) {
                $menuItems[] = $items;
            }
            echo Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                        'items' => $menuItems
                    ]
            )
        ?>
    </section>
</aside>