<?php

namespace Dcat\Admin\PluginStore\Widgets;

use Dcat\Admin\Widgets\Widget;
use Dcat\Admin\Widgets\WidgetCommon;

class InstallTerminal extends Widget {
    use WidgetCommon;
    /**
     * @var string
     */
    protected $view = 'ycookies.plugin-store::install';

    /**
     * @var array
     */
    protected $items = [];

    protected $paramjson = '';


    /**
     * Collapse constructor.
     */
    public function __construct() {
        $this->id('Terminal-box-' . uniqid());
        $this->class('box-group');
        $this->style('margin-bottom: 20px');
    }

    /**
     * Add item.
     *
     * @param string $title
     * @param string $content
     *
     * @return $this
     */
    public function param($param) {
        $param_arr       = [
            'id'           => $param['id'],
            'product_name' => $param['product_name'],
            'package_name' => $param['package_name'],
            'version'      => $param['version'],
        ];
        $this->items     = $param;
        $this->paramjson = json_encode($param_arr, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultVariables() {
        return [
            'id'         => $this->id,
            'items'      => $this->items,
            'paramjson'  => $this->paramjson,
            'attributes' => $this->formatAttributes(),
        ];
    }

}
