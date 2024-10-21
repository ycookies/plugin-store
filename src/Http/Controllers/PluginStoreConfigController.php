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



class PluginStoreConfigController extends Controller {


    public function index(Content $content) {
        return $content
            ->title('市场设置')
            ->description('')
            ->body($this->main());
    }

    public function main(){
        $tab = Tab::make();
        $tab->add('配置','111','','store-config-tab1');
        return $tab->render();
    }

}
