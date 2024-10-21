<?php

namespace Dcat\Admin\PluginStore\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Core\Input\InputPackage;
use Dcat\Admin\Core\Input\Response;
use Dcat\Admin\Exception\BizException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\PluginStore\Actions\StoreUserLogout;
use Dcat\Admin\PluginStore\Renderable\PluginStoreUserLogin;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\PluginStore\Renderable\PluginStoreInstall;
use Dcat\Admin\PluginStore\Repositories\MarketplaceRepository;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\PluginStore\Util\ModuleStoreUtil;
use Dcat\Admin\Traits\DownloadZipTrait;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Tab;
use Dcat\Admin\Widgets\Card;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dcat\Admin\PluginStore\Renderable\ReleasesTable;
use Dcat\Admin\DcatplusDemo\Http\Controllers\Renderable\UserTable;
use Dcat\Admin\Widgets\Tooltip;
use Dcat\Admin\PluginStore\Repositories\MarketplaceReleases;
use Dcat\Admin\PluginStore\Widgets\InstallTerminal;
use Dcat\Admin\Widgets\Markdown;
use Dcat\Admin\Widgets\Timeline;
//use Illuminate\Mail\Markdown;

use Dcat\Admin\PluginStore\Composer\ComposerAdapter;
use Symfony\Component\Console\Input\StringInput;
use Illuminate\Contracts\Container\Container;
use Composer\Console\Application;
use Dcat\Admin\PluginStore\OutputLogger;
use Dcat\Admin\PluginStore\Paths;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class PluginStoreController extends Controller {
    use DownloadZipTrait;
    public $composer;
//$this->composer = $composer;
    //$oupot = $this->composer->runExe('require ycookies/kefubar:1.0.2',null,true);
    /*echo "<pre>";
    var_dump(['123',$oupot]);
    echo "</pre>";
    exit;*/


    public function index(Content $content,ComposerAdapter $composer) {

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
        \Dcat\Admin\Widgets\Tooltip::make('.tips')->purple();
        $grid = Grid::make(new MarketplaceRepository(), function (Grid $grid) {
            $request = request();
            $where = [];
            $price_full_active = '';
            $price_free_active = '';
            $price_piad_active = '';
            if($request->has('price')){
                if($request->price == 'free'){
                    $price_free_active = 'active';
                }else{
                    $price_piad_active = 'active';
                }
                $where[] = ['price','=',$request->price];
            }else{
                $price_full_active = 'active';
            }
            if($request->has('_search_')){
                $where[] = ['search','=',$request->_search_];
            }
            if($request->has('_selector')){
                foreach ($request->_selector as $key => $values){
                    $where[] = [$key,'=',$values];
                }

            }
            $grid->disableRowSelector();
            $grid->model()->where($where)->orderBy('is_hot', 'DESC');
            $grid->view('ycookies.plugin-store::grid.custom.card-1');
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->disableFilterButton();
            $grid->disableCreateButton();
            /*$grid->column('detail', '描述');
            $grid->column('last_version', '当前版本')->width(120);*/
            $grid->quickSearch(['product_name', 'detail'])->placeholder('输入关键词搜索');
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if (Admin::extension()->has($actions->row->package_name)) {
                    $title = '<span class="text-danger tips" data-title="重新安装最新版本"><i class="feather icon-plus"></i>重新安装&nbsp; <span>';
                } else {
                    $title = '<span class="tips" data-title="安装最新版本"> <i class="feather icon-plus"></i>安装&nbsp;</span>';
                }
                $StoreUser_token = cache()->get('StoreUser_token');
                // 登陆 速码邦
                $user_modal = Modal::make()
                    ->staticBackdrop()
                    ->title('速码邦')
                    ->body(PluginStoreUserLogin::make())
                    ->button($title);
                if (!empty($StoreUser_token)) {
                    $modal = Modal::make()
                        ->lg()
                        ->staticBackdrop()
                        ->title('安装插件 ' . $actions->row->product_name . ' ' . $actions->row->package_name . 'V' . $actions->row->last_version)
                        ->body(PluginStoreInstall::make()->payload($actions->row->toArray()))
                        ->button($title);
                    $actions->prepend($modal);
                } else {
                    $actions->prepend($user_modal);
                }





                // 第二个 modal
                /*$modal2 = Modal::make('查看环境要求','444');
                $modal2->title('查看环境要求');
                $modal2->style('z-index:1052 !important' ,true);
                $modal2->sm();
                $modal2->button('查看');*/

                if (!empty($StoreUser_token)) {
                    // 第一个 modal
                    $modal1 = Modal::make();
                    $modal1->title('安装 ' . $actions->row->product_name . '(' . $actions->row->package_name . ') 其它版本');
                    $modal1->lg();
                    //$modal1->body($modal2->render())
                    $modal1->body((new ReleasesTable())->payload(['package_name' => $actions->row->package_name,'product_name'=>$actions->row->product_name,'product_slug'=>$actions->row->product_slug]))
                        ->button('<span class="tips" data-title="安装其它版本"> <i class="fa fa-sort-desc" style="font-size: 20px"></i> &nbsp;&nbsp;</span>');
                    $actions->prepend($modal1);
                }else{
                    $user_modal1 = Modal::make()
                        ->staticBackdrop()
                        ->title('速码邦')
                        ->body(PluginStoreUserLogin::make())
                        ->button('<span class="tips" data-title="安装其它版本"> <i class="fa fa-sort-desc" style="font-size: 20px"></i> &nbsp;&nbsp;</span>');
                    $actions->prepend($user_modal1);
                }
                //


                //$actions->prepend(AdminExtensionInstallAction::make($title));

                $actions->disableDelete();
                $actions->disableQuickEdit();
                $actions->disableView();
                $actions->disableEdit();
            });
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
            
            $weburl = $request->url();
            $mmmk = <<<HTML
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
            $grid->tools($mmmk);

            //$grid->tools($modal);

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

        $product_name = !empty($data_arr['product_name']) ? $data_arr['product_name'] : '';
        $title       = !empty($data_arr['package_name']) ? $data_arr['package_name'] : '';
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

    // 查看 版本 所需环境要求
    public function viewTagsRequire(Content $content){
        $request = request();
        $package_name = $request->get('package_name');
        $version_num = $request->get('version_num');
        $tags_require = $request->get('tags_require');
        $tags_require = json_decode(urldecode($tags_require),true);

        $require_tr = '';
        foreach ($tags_require as $key=> $item) {

            $require_tr .= '<tr>
      <td class="bg-light">'.$key.'</td>
      <td>'.$item.'</td> 
      <td>
      <i class="feather icon-check text-success"></i>  
      <i class="feather icon-x text-danger"></i>
      </td>
    </tr>';
        }

        $htmls = <<<HTML
<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">环境名称</th>
      <th scope="col">要求</th>
      <th scope="col">是否满足</th>
    </tr>
  </thead>
  <tbody>
    {$require_tr}
  </tbody>
</table>
HTML;


        $card = Card::make('环境要求',$htmls)->withHeaderBorder();
        $view_html = '<div style="margin: 10px 50px">'.$card->render().'</div>';
        return $content->full()->body($view_html);
    }

    // 安装扩展包基本版本
    public function packageVersionInstall(Content $content){
        $request = request();
        $id = $request->get('id');
        $product_name = $request->get('product_name');
        $package_name = $request->get('package_name');
        $version_num = $request->get('version_num');
        $tags_require = $request->get('tags_require');
        $token = cache()->get('StoreUser_token');
        $step  = $request->get('step');


        $terminal = InstallTerminal::make();
        $terminal->param(['id'=>$id,'product_name'=>$product_name,'package_name'=> $package_name,'version'=>$version_num]);
        $crad = Card::make('',$terminal->render())->withHeaderBorder();
        $htmls = '<div style="margin: 10px 20px"> '.$crad->render().' </div>';
        return $content->full()->body($htmls);

    }

    // 插件产品详情
    public function viewproduct(Content $content){
        $request = request();
        $id = $request->get('id');
        $package_name = $request->get('package_name');
        $package_name = $request->get('package_name');
        $ret = ModuleStoreUtil::remoteProductDeitalData($package_name);

         $detail = !empty($ret['data']) ? $ret['data']:[];
        $tags_list = !empty($detail['tags_list'])?$detail['tags_list']:[];
        $timeline = Timeline::make();
        if(!empty($tags_list)){
            foreach ($tags_list as $key => $itemm) {
                $timeline->add($itemm['release_date'],$itemm['version_name'],'-',$itemm['version_info']);
            }
        }

        // 效果截图
        $jietu = '';
        if(!empty($detail['product_pc_img'])){
            $product_pc_img = explode(',',$detail['product_pc_img']);
            foreach ($product_pc_img as $img) {
                $jietu .= '<img src="'.$img.'" width="100%"><br/>';
            }
        }
        if(!empty($detail['product_moblie_img'])){
            $product_moblie_img = explode(',',$detail['product_moblie_img']);
            foreach ($product_moblie_img as $img1) {
                $jietu .= '<img src="'.$img1.'" width="100%"><br/>';
            }
        }
        if(empty($jietu)){
            $jietu = '<div style="margin-top: 20px;text-align: center"> <h3>暂无内容</h3></div>';
        }

        $tab = Tab::make();
        $tab->add('详情说明',Markdown::make($detail['contents']));
        $tab->add('效果截图',$jietu);
        $tab->add('使用文档',Markdown::make($detail['docs_contents']));
        $tab->add('更新日志',$timeline->render());
        $crad = Card::make('',$tab->render())->withHeaderBorder();
        $htmls = '<div style="margin: 10px 20px"> '.$crad->render().' </div>';
        $tags_require_html = '';
        if(!empty($detail['tags_require'])){
            foreach ($detail['tags_require'] as $key => $value) {
                $tags_require_html .= '<div class="card-text"><small class="text-muted">'.$key.' : '.$value.' </small></div>';
            }

        }
        return $content->full()
            ->row(admin_view('ycookies.plugin-store::product_detail',compact('detail','tags_require_html')))
            ->row($htmls);
    }
}
