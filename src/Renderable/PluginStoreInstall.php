<?php

namespace Dcat\Admin\PluginStore\Renderable;

use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\PluginStore\Widgets\InstallTerminal;

class PluginStoreInstall extends LazyRenderable
{

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
