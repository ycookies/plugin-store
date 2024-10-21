<?php

namespace Dcat\Admin\PluginStore\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Dcat\Admin\Repositories\Repository;

use Dcat\Admin\Models\Marketplace;
use Dcat\Admin\Grid;
use Dcat\Admin\Core\Util\CurlUtil;
use Dcat\Admin\PluginStore\Util\ModuleStoreUtil;


class MarketplaceReleases extends Repository
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
        $getQueries = collect($model->getQueries())->toArray();
        $package_name = !empty($getQueries[0]['arguments'][0]['package_name']) ? $getQueries[0]['arguments'][0]['package_name'] :'';

        $res = ModuleStoreUtil::remoteReleasesData($package_name,$page,$pageSize);

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
