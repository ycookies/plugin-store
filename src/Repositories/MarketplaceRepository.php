<?php

namespace Dcat\Admin\PluginStore\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Dcat\Admin\Repositories\Repository;

use Dcat\Admin\Models\Marketplace;
use Dcat\Admin\Grid;
use Dcat\Admin\Core\Util\CurlUtil;
use Dcat\Admin\PluginStore\Util\ModuleStoreUtil;


class MarketplaceRepository extends Repository
{
    /**
     * Model.
     *
     * @var string
     */
    //protected $eloquentClass = Marketplace::class;

    /**
     * Get the grid data.
     *
     * @param Grid\Model $model
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection|array
     */
    public function get(Grid\Model $model)
    {
        $page     = $model->getCurrentPage();
        $pageSize = $model->getPerPage();
        $request = request();
        info($request->all());
        $where = [
            'pageNum' => 1,
            'pageSize' => 10,
            'startTime' => date('Y-m-d H:i:s',strtotime('-30 days')),
            'endTime' => date('Y-m-d 23:59:59'),
        ];
        if($request->has('price')){

            $where['price'] = $request->price;
        }
        if($request->has('_search_')){
            $where['search'] = $request->_search_;
        }
        if($request->has('_selector')){
            foreach ($request->_selector as $key => $values){
                $where[$key] = $values;
            }

        }
        /*$mk = $model->getQueries();
        foreach (collect($mk)->toArray() as $key => $querie){
            if(!empty($querie['method']) && $querie['method'] == 'where'){
                $arguments = $querie['arguments'];
                foreach ($arguments as $wheres) {
                    $where['']
                }

            }
        }*/
        //info(collect($mk)->toArray());
        // 获取排序字段
        $orderType = $model->getSort();

        // 获取"scope"筛选值
        //$city = $model->filter()->input($model->filter()->getScopeQueryName(), '广州');

        // 如果设置了其他过滤器字段，也可以通过“input”方法获取值，如：
        //$title = $model->filter()->input('title');

        //info($model->se);
        /*$mk = $model->getQueries();
        echo "<pre>";
        print_r(collect($mk)->toArray());
        echo "</pre>";
        exit;*/

        $res = ModuleStoreUtil::remoteModuleData($where,$page,$pageSize);

        //$collection = $this->all($page,$pageSize)->forPage($page, $pageSize);
        $collection = [];
        $total = 0;
        if(!empty($res['data']['records'])){
            $collection = collect($res['data']['records']);
        }
        if(!empty($res['data']['total'])){
            $total = $res['data']['total'];
        }

        return $model->makePaginator(
            $total,
            $collection
        );
    }

    protected function all($page,$pageSize)
    {
        //$hotel_id = \Dcat\Admin\Admin::user()->hotel_id;
        //$service = new ParkingService($hotel_id);
        $data = [
            'pageNum' => 1,
            'pageSize' => 10,
            'startTime' => date('Y-m-d H:i:s',strtotime('-30 days')),
            'endTime' => date('Y-m-d 23:59:59'),
        ];
        $res = ModuleStoreUtil::remoteModuleData([],$page,$pageSize);

        //$res = $service->sendapi('yunpark/thirdInterface/getChargeInfo',$data);
        //$res = json_decode($res,true);
        if(empty($res['data']['records'])){
            return collect([]);
        }
        return collect($res['data']['records']);
    }
}
