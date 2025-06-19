<?php

namespace Dcat\Admin\PluginStore;

use Dcat\Admin\Extend\Setting as Form;
use Dcat\Admin\Widgets\Alert;
use Dcat\Admin\Support\Helper;

class Setting extends Form
{
    // 返回表单弹窗标题
    public function title()
    {
        return '设置 插件市场';
    }

    public function form()
    {
        $alert = Alert::make('默认是使用官方市场，如果你有需求建立归属自己系统的插件市场，可前往申请创建自己的插件市场<a href="https://jikeadmin.saishiyun.net/admin/pluginStore/apply" target="_blank"> 前往</a>','提示')->info();
        $this->html($alert->render());
        $this->text('plugin_store_ssid','插件市场序列号')
            ->default('DUSD3-8JUD9-KLI56-54NJK')
            ->help('默认使用 官方市场序列号')
            ->required();

    }
}
