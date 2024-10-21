<?php

namespace Dcat\Admin\PluginStore\Renderable;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\PluginStore\Repositories\MarketplaceReleases;
use Dcat\Admin\DcatplusDemo\Http\Controllers\Forms\UserProfile;
use Dcat\Admin\Widgets\Modal;
class ReleasesTable extends LazyRenderable
{
    public function grid(): Grid
    {

        Admin::script(
            <<<SCRIPT
$('.preview-tags-require').click(function () {
    var package_name = $(this).attr('data-package_name');
    var version_num = $(this).attr('data-version_num');
    var tags_require = $(this).attr('data-tags_require');
    layer.open({
        type: 2,
        shade: [0.8, '#393D49'],
        title: '查看 环境要求',
        area: ['65%', '80%'],
        content: '/admin/plugin-store/viewTagsRequire?package_name='+package_name+'&version_num='+version_num+'&tags_require='+tags_require,
    });
});

$('.package-version-install').click(function () {
    var product_name = $(this).attr('data-product_name');
    var package_name = $(this).attr('data-package_name');
    var version_num = $(this).attr('data-version_num');
    layer.open({
        type: 2,
        shade: [0.8, '#393D49'],
        title: '安装插件 '+ product_name + '('+ package_name +') V'+version_num,
        area: ['65%', '80%'],
        content: '/admin/plugin-store/package-version-install?package_name='+package_name+'&version_num='+version_num,
    });
});
SCRIPT

        );
        $package_name = $this->payload['package_name'];
        return Grid::make(new MarketplaceReleases(), function (Grid $grid) use ($package_name) {
            //$grid->column('id', 'ID')->sortable();
            $grid->model()->where(['package_name'=> $package_name]);
            $grid->setKeyName($package_name);
            $grid->disableRowSelector();
            $grid->column('version_name','版本号');
            $grid->column('version_info','描述')->limit(15);
            $grid->column('release_date','发行时间');
            $grid->column('tags_require','环境要求')->display(function (){
                return '<div class="preview-tags-require" data-tags_require="'.urlencode($this->tags_require).'" data-package_name="'.$this->package_name.'" data-version_num="'.$this->version_num.'">查看</div>';
            });
            $grid->paginate(10);
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->actions(function ($actions) {
                $product_name = $actions->row->product_name;
                $package_name = $actions->row->package_name;
                $version_num = $actions->row->version_num;
                $package_install = '<div class="package-version-install text-info" data-product_name="'.$product_name.'" data-package_name="'.$package_name.'" data-version_num="'.$version_num.'"><i class="feather icon-plus"></i>安装&nbsp;</div>';
                $actions->append($package_install);
                // 去掉删除
                $actions->disableView();
                $actions->disableDelete();
                // 去掉编辑
                $actions->disableEdit();
            });
            $grid->disableToolbar();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('username')->width(4);
                $filter->like('name')->width(4);
            });
        });
    }
}
