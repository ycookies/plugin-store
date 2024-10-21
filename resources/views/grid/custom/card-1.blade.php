<style>
    .card-grid-box .card-title {
        font-size: 1rem;
    }

    .card-grid-box .card-body, .card-1 .card-footer {
        padding: .75rem !important;
    }

    .card-grid-box .two-line-ellipsis {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 34px;
        font-size: 12px;
    }

    .card-grid-box .grid-selector ul > li {
        margin-right: 3px !important;
    }

    .card-grid-box .grid-selector .select-label {
        width: 60px !important;
        font-size: 16px;
        font-weight: bold;
    }

    .card-grid-box .grid-selector .select-options {
        margin-left: 20px !important;
    }

    .card-grid-box .card-text {
        margin-top: 5px;
        margin-bottom: 0px;
    }

    .card-grid-box .card-img-top {
        display: block;
        min-height: 150px;
        max-height: 161px;
        background: #4db6ac;
    }
</style>

<div class="card-grid-box">

    @include('admin::grid.table-toolbar')

    {!! $grid->renderFilter() !!}

    {!! $grid->renderHeader() !!}

    <div class="card-1" style="margin-top: 20px">

        <div class="row">
            @foreach($grid->rows() as $row)
                <div class="col-md-3">
                    <div class="card">
                        @if($row->is_hot == 1)
                            <div class="ribbon ribbon-top ribbon-bookmark bg-green">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-filled" width="24" height="24"
                                     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z"></path>
                                </svg>
                            </div>
                        @endif
                        <a href="javascript:void(0);" onclick="viewDetail('{{$row->product_name}}','{{$row->product_slug}}','{{$row->package_name}}')">
                            <img src="{{$row->logo}}"
                                 onerror="this.src = 'https://jikeadmin.saishiyun.net/img/example.png'"
                                 class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <div>
                                <a href="javascript:void(0);">
                                    <h5 class="card-title"
                                        onclick="viewDetail('{{$row->product_name}}','{{$row->product_slug}}','{{$row->package_name}}')">
                                        {{$row->product_name}}
                                    </h5>
                                </a>
                                <span class="float-right text-muted f10"><i class="feather icon-download"></i> {{$row->down_num}}</span>
                            </div>
                            <div style="clear: both">

                            </div>
                            <div class="card-text {{ $row->price > 0 ? "text-danger":"text-success" }} "> {{ $row->price > 0 ? '￥'.$row->price :'免费' }}
                                <span style="float: right"> <label
                                            class="text-secondary"> 最新版V{{$row->last_version}}</label>
                            </span>
                                <span style="clear: none"></span>
                            </div>
                            <p class="card-text text-secondary two-line-ellipsis tips" data-title="{{$row->detail}}">{{$row->detail}}</p>
                        </div>
                        <div class="card-footer">
                            <span class="float-none"></span>
                            <span class="text-secondary float-left"><i
                                        class="feather icon-user"></i>{{$row->author}} </span>
                            <span class="float-right text-info">{!! $row->column(Dcat\Admin\Grid\Column::ACTION_COLUMN_NAME) !!}</span>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {!! $grid->renderFooter() !!}

        @include('admin::grid.table-pagination')
    </div>
</div>

<script>
    function viewDetail(product_name, product_slug, package_name) {
        layer.open({
            type: 2,
            shade: [0.8, '#393D49'],
            title: product_name + '(' + product_slug + ') 介绍',
            area: ['65%', '85%'],
            content: '/admin/plugin-store/viewproduct?product_slug=' + product_slug + '&package_name=' + package_name,
        });
    }
</script>