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
use Dcat\Admin\Core\Util\CurlUtil;
use Illuminate\Http\Client\Response;

// 插件市场订单管理
class PluginStoreOrderController extends Controller {

    public function index(Content $content) {
        return $content
            ->title('插件应用市场 - 订单列表')
            ->description('')
            ->breadcrumb(
                ['text' => '插件市场', 'url' => admin_url('/plugin-store/index')],
                ['text' => '订单管理', 'url' => '#']
            )
            ->body($this->grid());
    }

    // 订单详情
    public function grid(){

        return '待建设';
    }

    // 订单列表
    public function orderDetail(Content $content,$id){

        return $content
            ->title('订单详情')
            ->description('')
            ->breadcrumb(
                ['text' => '插件市场', 'url' => admin_url('/plugin-store/index')],
                ['text' => '订单详情', 'url' => '#']
            )
            ->body('待建设');
    }

}
