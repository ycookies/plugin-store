<?php

namespace Dcat\Admin\PluginStore\Actions;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Form\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Dcat\Admin\Widgets\Table;
use Dcat\Admin\Admin;
use Dcat\Admin\Core\Util\CurlUtil;
use Dcat\Admin\Actions\Action;

class StoreUserLogout extends Action
{
    /**
     * @return string
     */
    protected $title = '退出登陆';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        cache()->forget('StoreUser_info_'.Admin::user()->id);
        cache()->forget('StoreUser_token_'.Admin::user()->id);
        cache()->forget('StoreUser_memberUserId_'.Admin::user()->id);
        return $this->response()->success('已退出登陆')->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        return ['现在要退出吗', '退出后不可安装扩展'];
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
