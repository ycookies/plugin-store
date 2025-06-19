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
            'uri'   => 'plugin-store/index',
            'icon'  => 'fa fa-fw fa-cubes',
        ]
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
