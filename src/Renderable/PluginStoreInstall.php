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
        return $terminal->render();
    }
}
