<?php

namespace Dcat\Admin\PluginStore;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class PluginStoreServiceProvider extends ServiceProvider
{
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

    protected $menu = [
        [
            'title' => '插件服务市场',
            'uri'   => '',
            'icon'  => 'fa fa-fw fa-cubes vic-00DD00',
        ],
        [
            'parent' => '插件服务市场', // 指定父级菜单
            'title'  => "市场中心",
            'icon'   => 'feather icon-align-justify',
            'uri'    => 'plugin-store/index',
        ],
        [
            'parent' => '插件服务市场', // 指定父级菜单
            'title'  => "插件开发",
            'icon'   => 'feather icon-cpu',
            'uri'    => 'plugin-store/dev-helper',
        ],
        [
            'parent' => '插件服务市场', // 指定父级菜单
            'title'  => "市场设置",
            'icon'   => 'feather icon-settings',
            'uri'    => 'plugin-store/setting',
        ],
    ];

	public function register()
	{
		//
	}

	public function init()
	{
		parent::init();

		//
		
	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
