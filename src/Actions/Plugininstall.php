<?php

namespace Dcat\Admin\PluginStore\Actions;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Table;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Admin\Renderable\UserTable;
//use Dcat\Admin\PluginStore\Widgets\InstallTerminal;
use Dcat\Admin\PluginStore\Actions\Forms\PluginStoreInstallForm;
class Plugininstall extends Action
{
    /**
     * 按钮标题
     *
     * @var string
     */
    protected $title = '安装';

    /**
     * @var string
     */
    public $modalId;

    public function modalId($id){
        $this->modalId = $id;
        return $this;
    }
    /**
     * 处理当前动作的请求接口，如果不需要请直接删除
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        return $this->response()
            //->success('查询成功')
            ->html('-');
    }

    /**
     * 处理响应的HTML字符串，附加到弹窗节点中
     *
     * @return string
     */
    protected function handleHtmlResponse()
    {
        return <<<'JS'
function (target, html, data) {
    var $modal = $(target.data('target'));
    // $modal.find('.modal-body').html(html)
    $modal.modal('show')
} 
JS;
    }

    /**
     * 设置HTML标签的属性
     *
     * @return void
     */
    protected function setupHtmlAttributes()
    {
        // 添加class
        //$this->addHtmlClass('btn btn-primary');

        // 保存弹窗的ID
        $this->setHtmlAttribute('data-target', '#'.$this->modalId);

        parent::setupHtmlAttributes();
    }


    protected function userTable(){
        $installForm = PluginStoreInstallForm::make();
        $json_arr = json_decode($this->getKey(),true);
        $modal = Modal::make();
        $modal->lg();
        $modal->title('安装插件 ' . $json_arr['product_name']. ' ' . $json_arr['package_name']. 'V' . $json_arr['version']);
        $modal->id($this->modalId);
        $modal->body($installForm::make()->payload($json_arr));
        return $modal->render();
    }

    /**
     * 设置按钮的HTML，这里我们需要附加上弹窗的HTML
     *
     * @return string|void
     */
    public function html()
    {
        // 按钮的html
        $html = parent::html();
        return $html.$this->userTable();
    }

    /**
     * 确认弹窗信息，如不需要可以删除此方法
     *
     * @return string|void
     */
    public function confirm()
    {
        return ['你确认要安装吗?', '确认后开始安装'];
    }

    /**
     * 动作权限判断，返回false则表示无权限，如果不需要可以删除此方法
     *
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * 通过这个方法可以设置动作发起请求时需要附带的参数，如果不需要可以删除此方法
     *
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}