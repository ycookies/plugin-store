<?php


namespace Dcat\Admin\PluginStore\Util;

use Dcat\Admin\Misc\Zipper\Zipper;
use Illuminate\Support\Facades\Cache;
use Dcat\Admin\Core\Exception\BizException;
use Dcat\Admin\Core\Input\InputPackage;
use Dcat\Admin\Core\Input\Response;
use Dcat\Admin\Core\Util\CurlUtil;
use Dcat\Admin\Core\Util\FileUtil;
use Dcat\Admin\Core\Util\VersionUtil;
//use ModStart\ModStart;
use ModStart\Module\ModuleManager;
use Dcat\Admin\Admin;
use  Composer\Semver\Semver;
class ModuleStoreUtil
{
    const REMOTE_BASE = 'https://jikeadmin.saishiyun.net';
    // const REMOTE_BASE = 'http://org.demo.soft.host';

    public static function remoteModuleData($where = [],$page = 1,$page_size = 32)
    {

        $app = 'cms';
        $memberUserId = cache()->get('memberUserId');
        //$StoreUser_info = cache()->get('StoreUser_info');
        $apiToken = cache()->get('StoreUser_token');
        $basedata = [
            'app' => $app,
            'api_token' => $apiToken,
            'page' => $page,
            'page_size' => $page_size,
        ];
        $basedata = array_merge($basedata,$where);
        $ret = CurlUtil::getJSONData(self::REMOTE_BASE . '/api/plugin-store-server/getRepository', $basedata);
        return $ret;
        return Cache::remember('PluginStore_Modules:' . $memberUserId, 60, function () use ($apiToken,$where,$page,$page_size) {
            $app = 'cms';
            /*if (class_exists('\App\Constant\AppConstant')) {
                $app = \App\Constant\AppConstant::APP;
            }*/
            $ret = CurlUtil::getJSONData(self::REMOTE_BASE . '/api/plugin-store-server/getRepository', [
                'app' => $app,
                'api_token' => $apiToken,
                'page' => $page,
                'page_size' => $page_size,
            ]);
            return $ret;
        });
    }

    public static function remoteReleasesData($package_name,$page = 1,$page_size = 20)
    {

        $app = 'cms';
        $memberUserId = cache()->get('memberUserId');
        //$StoreUser_info = cache()->get('StoreUser_info');
        $apiToken = cache()->get('StoreUser_token');

        $ret = CurlUtil::getJSONData(self::REMOTE_BASE . '/api/plugin-store-server/getReposReleases', [
            'app' => $app,
            'api_token' => $apiToken,
            'package_name' => $package_name,
            'page' => $page,
            'page_size' => $page_size,
        ]);
        return $ret;
    }

    public static function remoteProductDeitalData($package_name)
    {

        $app = 'cms';
        $memberUserId = cache()->get('memberUserId');
        //$StoreUser_info = cache()->get('StoreUser_info');
        $apiToken = cache()->get('StoreUser_token');

        $ret = CurlUtil::getJSONData(self::REMOTE_BASE . '/api/plugin-store-server/getRepositoryDetail', [
            'app' => $app,
            'api_token' => $apiToken,
            'package_name' => $package_name,
        ]);
        return $ret;
    }


    public static function all()
    {
        $storeConfig = [
            'disable' => config('env.MS_MODULE_STORE_DISABLE', false),
        ];
        $result = self::remoteModuleData();
        $categories = [];
        if (!empty($result['data']['categories'])) {
            $categories = $result['data']['categories'];
        }
        $types = [];
        if (!empty($result['data']['types'])) {
            $types = $result['data']['types'];
        }
        $modules = [];
        if (!empty($result['data']['modules'])) {
            foreach ($result['data']['modules'] as $remote) {
                $remote['_isLocal'] = false;
                $remote['_isInstalled'] = false;
                $remote['_isEnabled'] = false;
                $remote['_localVersion'] = null;
                $remote['_isSystem'] = false;
                $remote['_hasConfig'] = false;
                $modules[$remote['name']] = $remote;
            }
        }
        foreach (ModuleManager::listModules() as $m => $config) {
            $info = ModuleManager::getModuleBasic($m);
            if (isset($modules[$m])) {
                $modules[$m]['_isInstalled'] = $config['isInstalled'];
                $modules[$m]['_isEnabled'] = $config['enable'];
                $modules[$m]['_localVersion'] = $info['version'];
                $modules[$m]['_isSystem'] = $config['isSystem'];
                $modules[$m]['_hasConfig'] = !empty($info['config']);
            } else {
                $modules[$m] = [
                    'id' => 0,
                    'name' => $m,
                    'title' => $info['title'],
                    'cover' => null,
                    'categoryId' => null,
                    'latestVersion' => $info['version'],
                    'releases' => [],
                    'url' => null,
                    'isFee' => false,
                    'priceSuper' => null,
                    'priceSuperEnable' => false,
                    'priceYear' => null,
                    'priceYearEnable' => false,
                    'description' => $info['description'],
                    '_isLocal' => true,
                    '_isInstalled' => $config['isInstalled'],
                    '_isEnabled' => $config['enable'],
                    '_localVersion' => $info['version'],
                    '_isSystem' => $config['isSystem'],
                    '_hasConfig' => !empty($info['config']),
                ];
            }
        }
        return [
            'storeConfig' => $storeConfig,
            'categories' => $categories,
            'types' => $types,
            'modules' => array_values($modules),
        ];
    }

    public static function baseRequest($api, $data, $token)
    {
        return CurlUtil::postJSONBody(self::REMOTE_BASE . $api, $data, [
            'header' => [
                'api-token' => $token,
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        ]);
    }

    public static function checkPackage($token, $package_name, $version)
    {
        $ret = self::baseRequest('/api/plugin-store-server/plugin_info', [
            'package_name' => $package_name,
            'version' => $version,
        ], $token);
        if ($ret['code'] && isset($ret['data']['buyCode'])) {
            return $ret;
        }
        BizException::throwsIfResponseError($ret);
        $config = $ret['data']['config'];
        $packageSize = $ret['data']['packageSize'];
        $requires = [];
        // 检查有没有安装核心
        /*if (!empty($config['modstartVersion'])) {
            $require = [
                'name' => "<a href='https://modstart.com/download' class='ub-text-white tw-underline' target='_blank'>MSCore</a>:" . htmlspecialchars($config['modstartVersion']),
                'success' => '',//VersionUtil::match(ModStart::$version, $config['modstartVersion']),
                'resolve' => null,
            ];
            if (!$require['success']) {
                $require['resolve'] = '请使用 MSCore' . $config['modstartVersion'] . ' 的版本';
            }
            $requires[] = $require;
        }*/
        if (!empty($config['require'])) {
            // 检查是否已经安装了依赖
            foreach ($config['require'] as $require) {
                list($m, $v) = VersionUtil::parse($require);
                $require = [
                    'name' => "<a href='https://modstart.com/m/$m' class='ub-text-white tw-underline' target='_blank'>$m</a>:" . htmlspecialchars($v),
                    'success' => true,
                    'resolve' => null,
                ];
                //ModuleManager::isModuleInstalled($m);

                if (Admin::extension()->has($m)) {
                    $basic = ModuleManager::getModuleBasic($m);
                    BizException::throwsIfEmpty("获取模块 $m 信息失败", $basic);
                    $require['success'] = VersionUtil::match($basic['version'], $v);
                    if (!$require['success']) {
                        $require['resolve'] = "请使用版本 " . htmlspecialchars($v) . " 的模块 <a href='https://modstart.com/m/$m' class='ub-text-white tw-underline' target='_blank'>$m</a>";
                    }
                } else {
                    $require['success'] = false;
                    $require['resolve'] = "请先安装 $require[name] <a href='https://modstart.com/m/$m' class='ub-text-white tw-underline' target='_blank'>[点击查看]</a>";
                }
                $requires[] = $require;
            }
        }
        if (empty($config['env'])) {
            $config['env'] = ['laravel5'];
        }
        $env = Admin::extension()->getEnv();
        BizException::throwsIf(
            '安装此插件,Laravel版本应是'.implode(',',$config['env']),
            !in_array($env, $config['env'])
        );

        return Response::generateSuccessData([
            'requires' => $requires,
            'errorCount' => count(array_filter($requires, function ($o) {
                return !$o['success'];
            })),
            'packageSize' => $packageSize,
        ]);
    }

    // 下载扩展包
    public static function downloadPackage($token, $package_name, $version)
    {
        $ret = self::baseRequest('/api/plugin-store-server/plugin_package', [
            'package_name' => $package_name,
            'version' => $version,
        ], $token);

        BizException::throwsIfResponseError($ret);
        $package = $ret['data']['package'];
        $packageMd5 = $ret['data']['packageMd5'];
        $licenseKey = $ret['data']['licenseKey'];
        $data = CurlUtil::getRaw($package);
        BizException::throwsIfEmpty('安装包获取失败:'.$package, $data);
        $zipTemp = FileUtil::generateLocalTempPath('zip');
        file_put_contents($zipTemp, $data);
        //BizException::throwsIf('文件MD5校验失败', md5_file($zipTemp) != $packageMd5);
        return Response::generateSuccessData([
            'package' => $zipTemp,
            'licenseKey' => $licenseKey,
            'packageSize' => filesize($zipTemp),
        ]);
    }

    public static function cleanDownloadedPackage($package)
    {
        FileUtil::safeCleanLocalTemp($package);
    }

    // 把插件压缩包解压到指定目录
    public static function unpackModule($title, $package, $licenseKey)
    {
        $results = [];
        BizException::throwsIf('文件不存在 ' . $package, empty($package) || !file_exists($package));
        $ret = FileUtil::filePathWritableCheck(['dcat-admin-extensions/._write_check_']);
        BizException::throwsIfResponseError($ret);

        //  $title ycookies.api-tester
        $title_arr = explode('/',$title);
        $module = str_replace('.','/',$title);
        $moduleDir = base_path('dcat-admin-extensions/' . $module);

        if (file_exists($moduleDir)) {
            $moduleBackup = '_delete_.' . date('Ymd_His');
            BizException::throwsIf('模块目录 dcat-admin-extensions/' . $module . ' 不正常，请手动删除', !is_dir($moduleDir));

            $moduleBackupDir = base_path("dcat-admin-extensions/".$title_arr[0]."/".$moduleBackup.$title_arr[1]);
            try {
                rename($moduleDir, $moduleBackupDir);
            } catch (\Exception $e) {
                BizException::throws("备份模块 $module 到 $moduleBackup 失败（确保模块中所有文件和目录已关闭）");
            }
            BizException::throwsIf('备份模块旧文件失败', !file_exists($moduleBackupDir));
            $results[] = "备份模块 $module 到 $moduleBackup";
        }
        BizException::throwsIf('模块目录 dcat-admin-extensions/' . $module . ' 不正常，请手动删除', file_exists($moduleDir));
        $zipper = new Zipper();
        $zipper->make($package);
        if ($zipper->contains($module . '/config.json')) {
            $zipper->folder($module . '');
        }
        $zipper->extractTo($moduleDir);
        $zipper->close();
        //BizException::throwsIf('解压失败', !file_exists($moduleDir . '/config.json'));
        file_put_contents($moduleDir . '/license.json', json_encode([
            'licenseKey' => $licenseKey,
        ]));
        // 删除下载的临时安装包
        self::cleanDownloadedPackage($package);

        info('启用扩展-'.$title);
        Admin::extension()->load()->updateManager()->update($title);
        // 启用扩展
        Admin::extension()->enable($title);

        return Response::generateSuccessData($results);
    }

    public static function removeModule($module, $version)
    {
        $moduleDir = base_path('module/' . $module);
        BizException::throwsIf('模块目录不存在 ', !file_exists($moduleDir));
        BizException::throwsIf('模块目录 module/' . $module . ' 不正常，请手动删除', !is_dir($moduleDir));
        $moduleBackup = '_delete_.' . date('Ymd_His') . '.' . $module;
        $moduleBackupDir = base_path("module/$moduleBackup");
        try {
            rename($moduleDir, $moduleBackupDir);
        } catch (\Exception $e) {
            BizException::throws("移除模块 $module 到 $moduleBackup 失败，请确保模块 $module 中没有文件正在被使用");
        }
        BizException::throwsIf('模块目录备份失败', !file_exists($moduleBackupDir));
        return Response::generateSuccessData([]);
    }

    // 获取 扩展包的版本号
    public function getPackageVersion($package_name){
        // 获取已安装扩展包信息
        $installedPackages = json_decode(file_get_contents(base_path('vendor/composer/installed.json')), true);
        // 指定要获取版本号的扩展包名称
        $packageName = $package_name;
        // 查找指定扩展包的版本号
        $packageVersion = '';
        if(!empty($installedPackages['packages'])){
            foreach ($installedPackages['packages'] as $package) {
                if ($package['name'] === $packageName) {
                    $packageVersion = $package['version'];
                    break;
                }
            }
        }
        return $packageVersion;
    }

    // 比较扩展包的版本是否符号要求
    public function comparePackageVersion(){

    }
}
