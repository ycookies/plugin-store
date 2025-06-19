<?php

namespace Dcat\Admin\PluginStore\Actions\Forms;


use Dcat\Admin\PluginStore\Widgets\InstallTerminal;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class PluginStoreInstallForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function render()
    {
        $terminal = InstallTerminal::make();

        $terminal->param($this->payload);
        //$datas = $list_group->ajax('https://demo.saishiyun.net/api/dcatplus/newlist',['type'=>1,'page'=>1],'POST');
        /*if(empty($datas['data'])){
            return '获取远程数据失败';
        }else{
            foreach ($datas['data'] as $key => $items){
                $list_group->add($items['title'],$items['datetime'],$items['link']);
            }
        }*/
        return $terminal->render();
    }
}
