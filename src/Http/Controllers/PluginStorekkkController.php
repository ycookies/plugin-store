<?php

namespace Dcat\Admin\PluginStore\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Core\Input\InputPackage;
use Dcat\Admin\Core\Input\Response;
use Dcat\Admin\Exception\BizException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Actions\Form\StoreUserLogout;
use Dcat\Admin\Http\Forms\PluginStoreUserLogin;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Http\Renderable\PluginStoreInstall;
use Dcat\Admin\PluginStore\Repositories\MarketplaceRepository;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\PluginStore\Util\ModuleStoreUtil;
use Dcat\Admin\Traits\DownloadZipTrait;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

//use Dcat\Admin\Http\Forms\PluginStoreInstall;

class PluginStorekkkController extends Controller {
    use DownloadZipTrait;

    public function index(Content $content) {
        return $content
            ->title('插件应用市场')
            ->description('为了系统和数据安全，在线 安装、卸载、升级 模块前请做好代码和数据备份')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {

        $grid = Grid::make(new MarketplaceRepository(), function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->model()->orderBy('is_hot', 'DESC');
            $grid->view('ycookies.plugin-store::grid.custom.card-1');
            $mmmk = <<<HTML
<div class="btn-group" data-toggle="buttons" style="margin: 0 5px">
    <a href="#" class="btn btn-white text-success">
        <i class="zi zi_gift"></i> 免费
    </a>
    <a href="#" class="btn btn-white text-danger">
        <i class="zi zi_yensign"></i> 付费
    </a>
</div>
HTML;
            $grid->tools($mmmk);


            $StoreUser_info = cache()->get('StoreUser_info');
            if (!empty($StoreUser_info)) {
                $options = [
                    '<a href="https://jikeadmin.saishiyun.net/member" target="_blank">个人中心</a>',
                    '<a href="https://jikeadmin.saishiyun.net/member_ms_module_order" target="_blank">订单管理</a> ',
                    Dropdown::DIVIDER,
                    StoreUserLogout::make(),
                ];

                $dropdown = Dropdown::make($options)
                    ->button('<img class="rounded-circle" src="' . $StoreUser_info['avatar'] . '"  width="22"/> ' . $StoreUser_info['username'])// 设置按钮
                    ->buttonClass('btn btn-white  waves-effect'); // 设置按钮样式
                $grid->rightTools($dropdown);
            } else {
                // 登陆 速码邦
                $user_modal = Modal::make()
                    ->title('速码邦')
                    ->body(PluginStoreUserLogin::make())
                    ->button('<button class="btn btn-white text-danger"><i class="feather icon-user"></i> 未登陆&nbsp;&nbsp; </button>');
                $grid->rightTools($user_modal);
            }

            $grid->selector(function (Grid\Tools\Selector $selector) {
                $selector->select('types', '类型:', [
                    '' => '全部',
                    1  => '系统模块',
                    2  => 'Dcat 扩展',
                    3  => 'Laravel扩展',
                    4  => 'php 扩展包',
                    5  => '独立应用',
                    6  => '行业系统',
                ]);
                $selector->select('cats', '分类:', [
                    '' => '全部',
                    1  => '会员',
                    2  => '云存储',
                    3  => '邮箱',
                    4  => '短信',
                    5  => '验证',
                    6  => '系统',
                ]);
            });
            $grid->setActionClass(Grid\Displayers\Actions::class);
            //$grid->setActionClass(TextActions::class);
            $grid->disableFilterButton();
            $grid->disableCreateButton();
            $grid->column('#')
                ->display(function () use ($grid) {
                    return '#' . ($this->_index + 1 + request('per_page', $grid->getPerPage()) * (request('page', 1) - 1));
                })->width(60);
            $grid->column('title', '包名')
                ->display(function ($title) {
                    return <<<HTML
<a target="_blank" href="{$this->home_page}"><i class="feather icon-globe"></i>&nbsp;{$title}</a>
HTML;
                })
                ->width(350);
            $grid->column('detail', '描述');
            $grid->column('version', '当前版本')->width(120);
            $grid->quickSearch(['plugin_name', 'detail'])->placeholder('输入关键词搜索');
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (Admin::extension()->has($actions->row->title)) {
                    $title = '<span class="text-danger"><i class="feather icon-plus"></i> 重新安装&nbsp;&nbsp; <span>';
                } else {
                    $title = '<i class="feather icon-plus"></i> 安装&nbsp;&nbsp;';
                }
                $StoreUser_token = cache()->get('StoreUser_token');
                if (!empty($StoreUser_token)) {
                    $modal = Modal::make()
                        ->lg()
                        ->title('安装插件 ' . $actions->row->plugin_name . ' ' . $actions->row->title . 'V' . $actions->row->version)
                        ->body(PluginStoreInstall::make()->payload($actions->row->toArray()))
                        ->button($title);
                } else {
                    // 登陆 速码邦
                    $user_modal = Modal::make()
                        ->title('速码邦')
                        ->body(PluginStoreUserLogin::make())
                        ->button($title);
                    $modal      = $user_modal;
                }


                $actions->prepend($modal);
                //$actions->prepend(AdminExtensionInstallAction::make($title));

                $actions->disableDelete();
                $actions->disableQuickEdit();
                $actions->disableView();
                $actions->disableEdit();
            });
            /*$grid->filter(function (Grid\Filter $filter) {
                $filter->like('title', '包名');
                $filter->panel()->expand();
            });*/
            $grid->wrap(function (Renderable $view) {
                $tab = Tab::make();
                $tab->add('插件应用市场', $view)->icon('<i class="feather icon-shopping-cart"></i>', true);
                $tab->addLink('已安装扩展', admin_url('auth/extensions'))->icon('<i class="feather icon-align-justify"></i>');

                return $tab;
            });
            $grid->paginate(24);
            $grid->perPages([24,40]);
        });

        //$card = Card::make('',$grid);
        return $grid;
    }

    // 响应完成
    private function doFinish($msgs) {
        $htmls = '<div>\n' .
            '\t\t\t\t<i class="feather icon-check-circle text-success"></i>\n' .
            '\t\t\t\t<span class="text-success">操作已运行完成</span>\n' .
            '\t\t\t</div>';
        return Response::generateSuccessData([
            'output' => $msgs,
            'finish' => true,
        ]);
    }

    // 响应下一步
    private function doNext($command, $step, $msgs = [], $data = []) {
        //$input = InputPackage::buildFromInput();
        //$data  = array_merge($input->getJsonAsInput('param')->all(), $data);
        $datajson    = Request()->get('param');
        $jsonString  = str_replace('&quot;', '"', $datajson);
        $data_arr    = json_decode($jsonString, true);
        $data = array_merge($data_arr,$data);
        return Response::generateSuccessData([
            'output'  => array_map(function ($item) {
                return '<div><i class="feather icon-minus text-white"></i>' . $item . '</div>';
            }, $msgs),
            'command' => $command,
            'step'    => $step,
            'data'    => json_encode($data,JSON_UNESCAPED_UNICODE),
            'finish'  => false,
        ]);
    }

    public function install(Request $request) {
        //AdminPermission::permitCheck('ModuleStoreManage');
        //AdminPermission::demoCheck();
        $token = cache()->get('StoreUser_token');
        $step  = $request->get('step');
        if (empty($token)) {
            return JsonResponse::make()->error('请先登录 速码邦 账号');
        }
        $datajson    = $request->get('param');
        $jsonString  = str_replace('&quot;', '"', $datajson);
        $data_arr    = json_decode($jsonString, true);
        $plugin_name = !empty($data_arr['plugin_name']) ? $data_arr['plugin_name'] : '';
        $title       = !empty($data_arr['title']) ? $data_arr['title'] : '';
        $zip_url     = !empty($data_arr['zip_url']) ? $data_arr['zip_url'] : '';
        $version     = !empty($data_arr['version']) ? $data_arr['version'] : '';
        if (empty($title)) return JsonResponse::make()->error('模板名 不能为空');
        //if (empty($zip_url)) return JsonResponse::make()->error('模板安装包 不能为空');
        // 开始安装
        //$request->merge($data_arr);
        //$msg = $this->handleInstall($request);
        switch ($step) {
            case 'installModule':
                return $this->doFinish([
                    '<span class="text-success">安装完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                ]);
            case 'unpackPackage':
                $package = !empty($data_arr['package']) ? $data_arr['package']:'';
                BizException::throwsIfEmpty('package为空', $package);
                $licenseKey = !empty($data_arr['licenseKey']) ? $data_arr['licenseKey']:'';
                BizException::throwsIfEmpty('licenseKey为空', $licenseKey);
                try {
                    $ret = ModuleStoreUtil::unpackModule($title, $package, $licenseKey);
                } catch (\Exception $e) {
                    ModuleStoreUtil::cleanDownloadedPackage($package);
                    throw $e;
                }
                BizException::throwsIfResponseError($ret);

                return $this->doNext('install', 'installModule', array_merge([
                    '<span class="text-success">压缩包解压完成</span>',
                    '<span class="text-white">开始安装...</span>',
                ]));

            case 'downloadPackage':
                $ret = ModuleStoreUtil::downloadPackage($token, $title, $version);
                BizException::throwsIfResponseError($ret);
                return $this->doNext('install', 'unpackPackage', [
                    '<span class="text-success">获取安装包完成，大小 '.$ret['data']['packageSize'].'</span>',
                    '<span class="text-white">开始解压安装包...</span>'
                ], [
                    'package'    => $ret['data']['package'],
                    'licenseKey' => $ret['data']['licenseKey'],
                ]);

            case 'checkPackage':
                $ret = ModuleStoreUtil::checkPackage($token, $title, $version);
                if (Response::isError($ret)) {
                    return $ret;
                }
                $msgs[] = '<span class="text-white">开始下载安装包...</span>';
                return $this->doNext('install', 'downloadPackage', array_merge([
                    '<span class="text-white">PHP版本: v' . PHP_VERSION . '</span>',
                    '<span class="text-success">预检成功，2个依赖满足要求，安装包大小 1.2M</span>',
                ], $msgs));

                $msgs = [];
                foreach ($ret['data']['requires'] as $require) {
                    $msgs[] = '<span>&nbsp;&nbsp;</span>'
                        . ($require['success']
                            ? '<span class="ub-text-success"><i class="iconfont icon-check"></i> 成功</span>'
                            : '<span class="ub-text-danger"><i class="iconfont icon-warning"></i> 失败</span>')
                        . " <span>$require[name]</span> " . ($require['resolve'] ? " <span>解决：$require[resolve]</span>" : "");
                }
                if ($ret['data']['errorCount'] > 0) {
                    return $this->doFinish(array_merge($msgs, [
                        '<span class="ub-text-danger">预检失败，' . $ret['data']['errorCount'] . '个依赖不满足要求</span>',
                    ]));
                }
                return $this->doNext('install', 'downloadPackage', array_merge([
                    'PHP版本: v' . PHP_VERSION,
                    '<span class="ub-text-success">预检成功，' . count($ret['data']['requires']) . '个依赖满足要求，安装包大小 ' . FileUtil::formatByte($ret['data']['packageSize']) . '</span>',
                    '<span class="ub-text-white">开始下载安装包...</span>'
                ], $request->get('param')));
                break;

            default:
                return $this->doNext('install', 'checkPackage', [
                    '<span class="text-white">开始安装远程 ' . $title . ' V' . $version . '</span>',
                    '<span class="text-white">开始安装前预检...</span>'
                ]);
        }

        echo "<pre>";
        print_r($msg);
        echo "</pre>";
        exit;

        // 检查是否安装成功
        $request->merge([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        // 安装成功
        return JsonResponse::make()->success('安装成功');
        $version = $request->get('data.version');
        $isLocal = $request->get('data.isLocal');

        //BizException::throwsIf('系统模块不能动态设置', ModuleManager::isSystemModule($module));
        //$this->moduleOperateCheck($module);

        if ($isLocal) {
            switch ($step) {
                case 'installModule':
                    $ret = ModuleManager::install($module, true);
                    BizException::throwsIfResponseError($ret);
                    $ret = ModuleManager::enable($module);
                    BizException::throwsIfResponseError($ret);
                    return $this->doFinish([
                        '<span class="ub-text-success">安装完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                    ]);
                default:
                    return $this->doNext('install', 'installModule', [
                        '<span class="ub-text-success">开始安装本地模块 ' . $module . ' V' . $version . '</span>',
                        '<span class="ub-text-white">开始安装..</span>'
                    ]);
            }
        } else {
            switch ($step) {
                case 'installModule':
                    $ret = ModuleManager::install($module, true);
                    if (Response::isError($ret)) {
                        ModuleManager::clean($module);
                        BizException::throws($ret['msg']);
                    }
                    $ret = ModuleManager::enable($module);
                    BizException::throwsIfResponseError($ret);
                    return $this->doFinish([
                        '<span class="ub-text-success">安装完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                    ]);
                case 'unpackPackage':
                    $package = $dataInput->getTrimString('package');
                    BizException::throwsIfEmpty('package为空', $package);
                    $licenseKey = $dataInput->getTrimString('licenseKey');
                    BizException::throwsIfEmpty('licenseKey为空', $licenseKey);
                    try {
                        $ret = ModuleStoreUtil::unpackModule($module, $package, $licenseKey);
                    } catch (\Exception $e) {
                        ModuleStoreUtil::cleanDownloadedPackage($package);
                        throw $e;
                    }
                    BizException::throwsIfResponseError($ret);
                    return $this->doNext('install', 'installModule', array_merge([
                        '<span class="ub-text-success">模块解压完成</span>',
                        '<span class="ub-text-white">开始安装...</span>',
                    ], $ret['data']));
                case 'downloadPackage':
                    $ret = ModuleStoreUtil::downloadPackage($token, $module, $version);
                    BizException::throwsIfResponseError($ret);
                    return $this->doNext('install', 'unpackPackage', [
                        '<span class="ub-text-success">获取安装包完成，大小 ' . FileUtil::formatByte($ret['data']['packageSize']) . '</span>',
                        '<span class="ub-text-white">开始解压安装包...</span>'
                    ], [
                        'package'    => $ret['data']['package'],
                        'licenseKey' => $ret['data']['licenseKey'],
                    ]);
                case 'checkPackage':
                    $ret = ModuleStoreUtil::checkPackage($token, $module, $version);
                    if (Response::isError($ret)) {
                        return $ret;
                    }
                    $msgs = [];
                    foreach ($ret['data']['requires'] as $require) {
                        $msgs[] = '<span>&nbsp;&nbsp;</span>'
                            . ($require['success']
                                ? '<span class="ub-text-success"><i class="iconfont icon-check"></i> 成功</span>'
                                : '<span class="ub-text-danger"><i class="iconfont icon-warning"></i> 失败</span>')
                            . " <span>$require[name]</span> " . ($require['resolve'] ? " <span>解决：$require[resolve]</span>" : "");
                    }
                    if ($ret['data']['errorCount'] > 0) {
                        return $this->doFinish(array_merge($msgs, [
                            '<span class="ub-text-danger">预检失败，' . $ret['data']['errorCount'] . '个依赖不满足要求</span>',
                        ]));
                    }
                    $msgs[] = '<span class="ub-text-white">开始下载安装包...</span>';
                    return $this->doNext('install', 'downloadPackage', array_merge([
                        'PHP版本: v' . PHP_VERSION,
                        '<span class="ub-text-success">预检成功，' . count($ret['data']['requires']) . '个依赖满足要求，安装包大小 ' . FileUtil::formatByte($ret['data']['packageSize']) . '</span>',
                    ], $msgs));
                    break;
                default:
                    return $this->doNext('install', 'checkPackage', [
                        '<span class="ub-text-success">开始安装远程模块 ' . $module . ' V' . $version . '</span>',
                        '<span class="ub-text-white">开始模块安装预检...</span>'
                    ]);
            }
        }
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleInstall(Request $request) {
        $zip_url       = $request->get('zip_url');
        $title         = $request->get('title');
        $zip_name      = substr(
            $zip_url,
            strrpos($zip_url, '/') + 1
        );
        $zip_version   = trim($zip_name, '.zip');
        $extension_dir = storage_path('extensions');
        if (!is_dir($extension_dir)) {
            mkdir($extension_dir);
        }
        $zip_file = $extension_dir . '/' . $title . '-' . $zip_version . '.zip';

        try {
            if (!file_exists($zip_file)) {
                $this->downloadZipFile($zip_url, $zip_file);
                if (!file_exists($zip_file)) {
                    return JsonResponse::make()->error('压缩包下载失败');
                }
            }
            $manager       = Admin::extension();
            $extensionName = $manager->extract($zip_file, true);
            $manager
                ->load()
                ->updateManager()
                ->update($extensionName);

            return JsonResponse::make()->success(implode('<br>', $manager->updateManager()->notes))
                ->refresh();
        } catch (\Throwable $e) {
            Admin::reportException($e);

            return JsonResponse::make()->error($e->getMessage());
        } finally {
            if (!empty($zip_file)) {
                @unlink($zip_file);
            }
        }
    }

    private function moduleOperateCheck($module) {
        BizException::throwsIf('当前环境禁止「模块管理」相关操作', config('env.MS_MODULE_STORE_DISABLE', false));
        $whitelist = config('env.MS_MODULE_WHITELIST', '');
        if (empty($whitelist)) {
            return;
        }
        $whitelist = array_map(function ($v) {
            return trim($v);
        }, explode(',', $whitelist));
        $whitelist = array_filter($whitelist);
        if (empty($whitelist)) {
            return;
        }
        $passed = false;
        foreach ($whitelist as $item) {
            if (ReUtil::isWildMatch($item, $module)) {
                $passed = true;
                break;
            }
        }
        BizException::throwsIf('只允许操作模块:' . join(',', $whitelist), !$passed);
    }

    public function disable() {
        AdminPermission::permitCheck('ModuleStoreManage');
        AdminPermission::demoCheck();
        $input     = InputPackage::buildFromInput();
        $step      = $input->getTrimString('step');
        $dataInput = $input->getJsonAsInput('data');
        $module    = $dataInput->getTrimString('module');
        $version   = $dataInput->getTrimString('version');
        BizException::throwsIfEmpty('module为空', $module);
        BizException::throwsIfEmpty('version为空', $version);
        $this->moduleOperateCheck($module);
        switch ($step) {
            default:
                $ret = ModuleManager::disable($module);
                BizException::throwsIfResponseError($ret);
                return $this->doFinish([
                    '<span class="ub-text-success">禁用成功，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                ]);
        }
    }

    public function enable() {
        AdminPermission::permitCheck('ModuleStoreManage');
        AdminPermission::demoCheck();
        $input     = InputPackage::buildFromInput();
        $step      = $input->getTrimString('step');
        $dataInput = $input->getJsonAsInput('data');
        $module    = $dataInput->getTrimString('module');
        $version   = $dataInput->getTrimString('version');
        BizException::throwsIfEmpty('module为空', $module);
        BizException::throwsIfEmpty('version为空', $version);
        $this->moduleOperateCheck($module);
        switch ($step) {
            default:
                $ret = ModuleManager::enable($module);
                BizException::throwsIfResponseError($ret);
                return $this->doFinish([
                    '<span class="ub-text-success">启动成功，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                ]);
        }
    }

    public function uninstall() {
        AdminPermission::permitCheck('ModuleStoreManage');
        AdminPermission::demoCheck();
        $input     = InputPackage::buildFromInput();
        $step      = $input->getTrimString('step');
        $dataInput = $input->getJsonAsInput('data');
        $module    = $dataInput->getTrimString('module');
        $version   = $dataInput->getTrimString('version');
        $isLocal   = $dataInput->getBoolean('isLocal');
        BizException::throwsIfEmpty('module为空', $module);
        BizException::throwsIfEmpty('version为空', $version);
        BizException::throwsIf('系统模块不能动态设置', ModuleManager::isSystemModule($module));
        $this->moduleOperateCheck($module);
        if ($isLocal) {
            switch ($step) {
                default:
                    $ret = ModuleManager::uninstall($module);
                    BizException::throwsIfResponseError($ret);
                    return $this->doFinish([
                        '<span class="ub-text-success">卸载完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                    ]);
            }
        } else {
            switch ($step) {
                case 'removePackage':
                    $ret = ModuleStoreUtil::removeModule($module, $version);
                    BizException::throwsIfResponseError($ret);
                    return $this->doFinish([
                        '<span class="ub-text-success">卸载完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                    ]);
                default:
                    $ret = ModuleManager::uninstall($module);
                    BizException::throwsIfResponseError($ret);
                    return $this->doNext('uninstall', 'removePackage', [
                        '<span class="ub-text-success">开始卸载 ' . $module . ' V' . $version . '</span>',
                    ]);

            }
        }
    }

    public function upgrade() {
        AdminPermission::permitCheck('ModuleStoreManage');
        AdminPermission::demoCheck();
        $input = InputPackage::buildFromInput();
        $token = $input->getTrimString('token');
        $step  = $input->getTrimString('step');
        BizException::throwsIfEmpty('请先登录ModStartCMS账号', $token);
        $dataInput = $input->getJsonAsInput('data');
        $module    = $dataInput->getTrimString('module');
        $version   = $dataInput->getTrimString('version');
        BizException::throwsIfEmpty('module为空', $module);
        BizException::throwsIfEmpty('version为空', $version);
        $this->moduleOperateCheck($module);

        switch ($step) {
            case 'installModule':
                $ret = ModuleManager::install($module, true);
                BizException::throwsIfResponseError($ret);
                $ret = ModuleManager::enable($module);
                BizException::throwsIfResponseError($ret);
                return $this->doFinish([
                    '<span class="ub-text-success">升级安装完成，请 <a href="javascript:;" onclick="parent.location.reload()">刷新后台</a> 查看最新系统</span>',
                ]);
            case 'unpackPackage':
                $package = $dataInput->getTrimString('package');
                BizException::throwsIfEmpty('package为空', $package);
                $licenseKey = $dataInput->getTrimString('licenseKey');
                BizException::throwsIfEmpty('licenseKey为空', $licenseKey);
                try {
                    $ret = ModuleStoreUtil::unpackModule($module, $package, $licenseKey);
                } catch (\Exception $e) {
                    ModuleStoreUtil::cleanDownloadedPackage($package);
                    throw $e;
                }
                BizException::throwsIfResponseError($ret);
                return $this->doNext('upgrade', 'installModule', array_merge([
                    '<span class="ub-text-success">模块解压完成</span>',
                    '<span class="ub-text-white">开始安装...</span>',
                ], $ret['data']));
            case 'downloadPackage':
                $ret = ModuleStoreUtil::downloadPackage($token, $module, $version);
                BizException::throwsIfResponseError($ret);
                return $this->doNext('upgrade', 'unpackPackage', [
                    '<span class="ub-text-success">获取安装包完成，大小 ' . FileUtil::formatByte($ret['data']['packageSize']) . '</span>',
                    '<span class="ub-text-white">开始解压安装包...</span>'
                ], [
                    'package'    => $ret['data']['package'],
                    'licenseKey' => $ret['data']['licenseKey'],
                ]);
            case 'checkPackage':
                $ret = ModuleStoreUtil::checkPackage($token, $module, $version);
                if (Response::isError($ret)) {
                    return $ret;
                }
                $msgs = [];
                foreach ($ret['data']['requires'] as $require) {
                    $msgs[] = '<span>&nbsp;&nbsp;</span>'
                        . ($require['success']
                            ? '<span class="ub-text-success"><i class="iconfont icon-check"></i> 成功</span>'
                            : '<span class="ub-text-danger"><i class="iconfont icon-warning"></i> 失败</span>')
                        . " <span>$require[name]</span> " . ($require['resolve'] ? " <span>解决：$require[resolve]</span>" : "");
                }
                if ($ret['data']['errorCount'] > 0) {
                    return $this->doFinish(array_merge($msgs, [
                        '<span class="ub-text-danger">预检失败，' . $ret['data']['errorCount'] . '个依赖不满足要求</span>',
                    ]));
                }
                $msgs[] = '<span class="ub-text-white">开始下载安装包...</span>';
                return $this->doNext('upgrade', 'downloadPackage', array_merge([
                    'PHP版本: v' . PHP_VERSION,
                    '<span class="ub-text-success">预检成功，' . count($ret['data']['requires']) . '个依赖满足要求，安装包大小 ' . FileUtil::formatByte($ret['data']['packageSize']) . '</span>',
                ], $msgs));
            default:
                return $this->doNext('upgrade', 'checkPackage', [
                    '<span class="ub-text-success">开始升级到远程模块 ' . $module . ' V' . $version . '</span>',
                    '<span class="ub-text-white">开始模块安装预检...</span>'
                ]);
        }
    }


    public function config(AdminConfigBuilder $builder, $module) {
        AdminPermission::permitCheck('ModuleStoreManage');
        $basic = ModuleManager::getModuleBasic($module);
        $builder->useDialog();
        $builder->pageTitle(htmlspecialchars($basic['title']) . ' ' . L('Module Config'));
        $builder->layoutHtml('<div class="ub-alert danger"><i class="iconfont icon-warning"></i> 本页面包含的配置仅供开发使用，设置不当可能会导致系统功能异常</div>');
        $moduleInfo = ModuleManager::getInstalledModuleInfo($module);
        BizException::throwsIfEmpty('Module config error', $basic['config']);
        foreach ($basic['config'] as $key => $callers) {
            $field = null;
            if (!isset($moduleInfo['config'][$key])) {
                $moduleInfo['config'][$key] = null;
            }
            foreach ($callers as $caller) {
                $name = array_shift($caller);
                if (null === $field) {
                    array_unshift($caller, $key);
                    $field = call_user_func([$builder, $name], ...$caller);
                } else {
                    call_user_func([$field, $name], ...$caller);
                }
            }
        }
        return $builder->perform(RepositoryUtil::itemFromArray($moduleInfo['config']), function (Form $form) use ($module, $moduleInfo) {
            AdminPermission::demoCheck();
            if ($moduleInfo['isSystem']) {
                ModuleManager::saveSystemOverwriteModuleConfig($module, $form->dataForming());
            } else {
                ModuleManager::saveUserInstalledModuleConfig($module, $form->dataForming());
            }
            return Response::generate(0, '保存成功', null, CRUDUtil::jsDialogClose());
        });
    }
}
