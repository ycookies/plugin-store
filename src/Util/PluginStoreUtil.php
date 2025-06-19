<?php

namespace Dcat\Admin\PluginStore\Util;

use Dcat\Admin\PluginStore\Composer\ComposerAdapter;
use Dcat\Admin\PluginStore\Renderable\PluginStoreUserLogin;
use Dcat\Admin\PluginStore\Renderable\ReleasesTable;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\PluginStore\Actions\StoreUserLogout;
//
class PluginStoreUtil {
    public $composer;

    // 进行 composer 安装
    public function composerInstall(ComposerAdapter $composer) {
        $this->composer = $composer;
        $oupot          = $this->composer->runExe('require ycookies/kefubar:1.0.2', null, true);
        echo "<pre>";
        var_dump(['123', $oupot]);
        echo "</pre>";
        exit;
    }

    public static function login($title) {
        // 登陆 速码邦
        $modalid = 'plugin-store-userLogin-modal';
        $modal   = Modal::make()
            ->staticBackdrop()
            ->id($modalid)
            ->title('登陆 速码邦插件市场 ')
            ->body(PluginStoreUserLogin::make()->payload(['modalid' => $modalid]))
            ->button($title);
        return $modal;
    }

    public static function installOtherVersion($actions) {
        $modal1 = Modal::make();
        $modal1->title('安装 ' . $actions->row->product_name . '(' . $actions->row->package_name . ') 其它版本');
        $modal1->lg();
        $modal1->body((new ReleasesTable())->payload(['package_name' => $actions->row->package_name, 'product_name' => $actions->row->product_name, 'product_slug' => $actions->row->product_slug]))
            ->button('<span class="tips" data-title="安装其它版本"> <i class="fa fa-sort-desc" style="font-size: 20px"></i> &nbsp;&nbsp;</span>');
        return $modal1;
    }

    public static function groupBtn($weburl, $price_full_active, $price_free_active, $price_piad_active) {
        $html = <<<HTML
            <div class="btn-group" data-toggle="buttons" style="margin: 0 5px">
            <a href="{$weburl}" class="btn btn-white {$price_full_active}">
                    <i class="fa fa-reorder"></i> 全部
                </a>
                <a href="{$weburl}?price=free" class="btn btn-white text-succes {$price_free_active}">
                    <i class="fa fa-gift"></i> 免费
                </a>
                <a href="{$weburl}?price=piad" class="btn btn-white text-danger {$price_piad_active}">
                    <i class="fa fa-cny"></i> 付费
                </a>
            </div>
HTML;
        return $html;
    }

    public static function loginToolsBar(){
        $StoreUser_info = cache()->get('StoreUser_info_'.Admin::user()->id);
        $options = [
            '<a href="/admin/plugin-store/order/index" >订单管理</a> ',
            Dropdown::DIVIDER,
            StoreUserLogout::make(),
        ];

        $dropdown = Dropdown::make($options)
            ->button('<img class="rounded-circle" src="' . $StoreUser_info['avatar'] . '"  width="22"/> ' . $StoreUser_info['username'])// 设置按钮
            ->buttonClass('btn btn-white  waves-effect'); // 设置按钮样式

        return $dropdown;
    }


}
