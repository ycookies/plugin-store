<?php

namespace Dcat\Admin\PluginStore\Http\Controllers;

use Dcat\Admin\Admin;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;



class PluginDevHelperController extends Controller {


    public function index(Content $content) {
        return $content
            ->title('插件扩展包开发')
            ->description('低代码快速开发插件，扩展包')
            ->body($this->main());
    }

    public function main(){
        $tab = Tab::make();
        $tab->add('快速创建扩展包','111','','dev-helper-tab1');
        $tab->add('开发教程','222','','dev-helper-tab2');
        $tab->add('发布扩展包','333','','dev-helper-tab3');
        return $tab->render();
    }

}
